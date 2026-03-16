<?php
/**
 * Export/Import class
 *
 * Handles exporting and importing all plugin data as JSON.
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export/Import class
 */
class UNBSB_Export_Import {

	/**
	 * WPDB instance
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * Table prefix
	 *
	 * @var string
	 */
	private $prefix;

	/**
	 * Export format version for forward compatibility.
	 *
	 * @var string
	 */
	const EXPORT_FORMAT_VERSION = '1.0';

	/**
	 * Tables to export in dependency order.
	 *
	 * @var array
	 */
	private $tables = array(
		'categories',
		'services',
		'staff',
		'staff_services',
		'working_hours',
		'breaks',
		'holidays',
		'customers',
		'bookings',
		'booking_services',
		'sms_templates',
		'email_templates',
		'promo_codes',
		'promo_code_usage',
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb   = $wpdb;
		$this->prefix = $wpdb->prefix . 'unbsb_';
	}

	/**
	 * Export all plugin data as JSON.
	 *
	 * @return array|WP_Error Export data array or WP_Error on failure.
	 */
	public function export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'unbsb_unauthorized',
				__( 'You do not have permission to export data.', 'unbelievable-salon-booking' )
			);
		}

		$memory_check = $this->check_memory_limit();
		if ( is_wp_error( $memory_check ) ) {
			return $memory_check;
		}

		$export_data = array(
			'meta' => array(
				'plugin_version'        => UNBSB_VERSION,
				'export_format_version' => self::EXPORT_FORMAT_VERSION,
				'export_date'           => current_time( 'mysql' ),
				'site_url'              => get_site_url(),
				'site_name'             => get_bloginfo( 'name' ),
				'wp_version'            => get_bloginfo( 'version' ),
				'php_version'           => PHP_VERSION,
			),
		);

		// Export tables.
		$export_data['tables'] = array();
		foreach ( $this->tables as $table ) {
			$table_name = $this->prefix . $table;

			if ( ! $this->table_exists( $table_name ) ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$rows = $this->wpdb->get_results(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$table_name} ORDER BY id ASC",
				ARRAY_A
			);

			$export_data['tables'][ $table ] = ( null === $rows ) ? array() : $rows;
		}

		// Export settings.
		$export_data['settings'] = $this->export_settings();

		return $export_data;
	}

	/**
	 * Export plugin settings from wp_options.
	 *
	 * @return array
	 */
	private function export_settings() {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$options = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT option_name, option_value FROM {$this->wpdb->options} WHERE option_name LIKE %s",
				'unbsb\_%'
			),
			ARRAY_A
		);

		$settings = array();
		if ( $options ) {
			foreach ( $options as $option ) {
				$settings[ $option['option_name'] ] = $option['option_value'];
			}
		}

		return $settings;
	}

	/**
	 * Generate JSON string from export data.
	 *
	 * @return string|WP_Error JSON string or WP_Error on failure.
	 */
	public function export_json() {
		$data = $this->export();

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$json = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );

		if ( false === $json ) {
			return new WP_Error(
				'unbsb_export_encode_error',
				__( 'Failed to encode export data as JSON.', 'unbelievable-salon-booking' )
			);
		}

		return $json;
	}

	/**
	 * Import plugin data from JSON string.
	 *
	 * @param string $json JSON string.
	 * @param string $mode Import mode: 'merge' or 'replace'.
	 *
	 * @return array|WP_Error Result array with counts or WP_Error on failure.
	 */
	public function import( $json, $mode = 'merge' ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'unbsb_unauthorized',
				__( 'You do not have permission to import data.', 'unbelievable-salon-booking' )
			);
		}

		if ( ! in_array( $mode, array( 'merge', 'replace' ), true ) ) {
			return new WP_Error(
				'unbsb_invalid_mode',
				__( 'Invalid import mode. Use "merge" or "replace".', 'unbelievable-salon-booking' )
			);
		}

		$memory_check = $this->check_memory_limit();
		if ( is_wp_error( $memory_check ) ) {
			return $memory_check;
		}

		$data = json_decode( $json, true );

		if ( null === $data || JSON_ERROR_NONE !== json_last_error() ) {
			return new WP_Error(
				'unbsb_invalid_json',
				/* translators: %s: JSON error message */
				sprintf( __( 'Invalid JSON data: %s', 'unbelievable-salon-booking' ), json_last_error_msg() )
			);
		}

		$validation = $this->validate_import_data( $data );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$result = array(
			'tables'   => array(),
			'settings' => 0,
			'mode'     => $mode,
		);

		// Run import inside a transaction.
		$this->wpdb->query( 'START TRANSACTION' );

		try {
			// In replace mode, delete all tables in reverse dependency order first.
			if ( 'replace' === $mode && ! empty( $data['tables'] ) ) {
				$reversed = array_reverse( $this->tables );
				foreach ( $reversed as $table ) {
					if ( empty( $data['tables'][ $table ] ) ) {
						continue;
					}

					$table_name = $this->prefix . $table;

					if ( $this->table_exists( $table_name ) ) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						$this->wpdb->query( "DELETE FROM {$table_name}" );
					}
				}
			}

			// Import tables.
			if ( ! empty( $data['tables'] ) ) {
				foreach ( $this->tables as $table ) {
					if ( empty( $data['tables'][ $table ] ) ) {
						continue;
					}

					$table_name = $this->prefix . $table;

					if ( ! $this->table_exists( $table_name ) ) {
						continue;
					}

					$count = $this->import_table( $table, $data['tables'][ $table ], $mode );

					if ( is_wp_error( $count ) ) {
						$this->wpdb->query( 'ROLLBACK' );
						return $count;
					}

					$result['tables'][ $table ] = $count;
				}
			}

			// Import settings.
			if ( ! empty( $data['settings'] ) ) {
				$result['settings'] = $this->import_settings( $data['settings'], $mode );
			}

			$this->wpdb->query( 'COMMIT' );
		} catch ( \Exception $e ) {
			$this->wpdb->query( 'ROLLBACK' );
			return new WP_Error(
				'unbsb_import_error',
				/* translators: %s: error message */
				sprintf( __( 'Import failed: %s', 'unbelievable-salon-booking' ), $e->getMessage() )
			);
		}

		return $result;
	}

	/**
	 * Import data for a single table.
	 *
	 * @param string $table Table name without prefix.
	 * @param array  $rows  Row data.
	 * @param string $mode  Import mode.
	 *
	 * @return int|WP_Error Number of rows imported or WP_Error.
	 */
	private function import_table( $table, $rows, $mode ) {
		$table_name = $this->prefix . $table;

		$columns = $this->get_table_columns( $table_name );
		if ( empty( $columns ) ) {
			return new WP_Error(
				'unbsb_import_table_error',
				/* translators: %s: table name */
				sprintf( __( 'Could not read columns for table: %s', 'unbelievable-salon-booking' ), $table )
			);
		}

		$imported = 0;
		foreach ( $rows as $row ) {
			$sanitized = $this->sanitize_row( $row, $columns );

			if ( 'merge' === $mode && isset( $sanitized['id'] ) ) {
				$existing = $this->row_exists( $table_name, $sanitized['id'] );

				if ( $existing ) {
					$id = $sanitized['id'];
					unset( $sanitized['id'] );

					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$this->wpdb->update( $table_name, $sanitized, array( 'id' => $id ) );
					++$imported;
					continue;
				}
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$this->wpdb->insert( $table_name, $sanitized );
			++$imported;
		}

		return $imported;
	}

	/**
	 * Import plugin settings.
	 *
	 * @param array  $settings Settings array.
	 * @param string $mode     Import mode.
	 *
	 * @return int Number of settings imported.
	 */
	private function import_settings( $settings, $mode ) {
		if ( 'replace' === $mode ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$this->wpdb->query(
				$this->wpdb->prepare(
					"DELETE FROM {$this->wpdb->options} WHERE option_name LIKE %s",
					'unbsb\_%'
				)
			);
		}

		$count = 0;
		foreach ( $settings as $name => $value ) {
			// Only allow unbsb_ prefixed options.
			if ( 0 !== strpos( $name, 'unbsb_' ) ) {
				continue;
			}

			$name  = sanitize_key( $name );
			$value = sanitize_text_field( (string) $value );
			update_option( $name, $value );
			++$count;
		}

		return $count;
	}

	/**
	 * Validate import data structure.
	 *
	 * @param array $data Import data.
	 *
	 * @return true|WP_Error
	 */
	private function validate_import_data( $data ) {
		if ( ! is_array( $data ) ) {
			return new WP_Error(
				'unbsb_invalid_data',
				__( 'Import data must be an object.', 'unbelievable-salon-booking' )
			);
		}

		// Meta is required.
		if ( empty( $data['meta'] ) || ! is_array( $data['meta'] ) ) {
			return new WP_Error(
				'unbsb_missing_meta',
				__( 'Import data is missing the "meta" section. This does not appear to be a valid export file.', 'unbelievable-salon-booking' )
			);
		}

		$required_meta = array( 'plugin_version', 'export_format_version', 'export_date' );
		foreach ( $required_meta as $key ) {
			if ( empty( $data['meta'][ $key ] ) ) {
				return new WP_Error(
					'unbsb_missing_meta_field',
					/* translators: %s: meta field name */
					sprintf( __( 'Import data is missing required meta field: %s', 'unbelievable-salon-booking' ), $key )
				);
			}
		}

		// At least one of tables or settings must exist.
		if ( empty( $data['tables'] ) && empty( $data['settings'] ) ) {
			return new WP_Error(
				'unbsb_empty_import',
				__( 'Import data contains no tables or settings to import.', 'unbelievable-salon-booking' )
			);
		}

		// Validate tables section.
		if ( ! empty( $data['tables'] ) ) {
			if ( ! is_array( $data['tables'] ) ) {
				return new WP_Error(
					'unbsb_invalid_tables',
					__( 'The "tables" section must be an object.', 'unbelievable-salon-booking' )
				);
			}

			// Check that table names are valid.
			$allowed_tables = array_flip( $this->tables );
			foreach ( $data['tables'] as $table_name => $rows ) {
				if ( ! isset( $allowed_tables[ $table_name ] ) ) {
					return new WP_Error(
						'unbsb_unknown_table',
						/* translators: %s: table name */
						sprintf( __( 'Unknown table in import data: %s', 'unbelievable-salon-booking' ), $table_name )
					);
				}

				if ( ! is_array( $rows ) ) {
					return new WP_Error(
						'unbsb_invalid_table_data',
						/* translators: %s: table name */
						sprintf( __( 'Table data must be an array: %s', 'unbelievable-salon-booking' ), $table_name )
					);
				}
			}
		}

		// Validate settings section.
		if ( ! empty( $data['settings'] ) && ! is_array( $data['settings'] ) ) {
			return new WP_Error(
				'unbsb_invalid_settings',
				__( 'The "settings" section must be an object.', 'unbelievable-salon-booking' )
			);
		}

		return true;
	}

	/**
	 * Check if PHP memory limit is sufficient for operation.
	 *
	 * @return true|WP_Error
	 */
	private function check_memory_limit() {
		$memory_limit = $this->get_memory_limit_bytes();

		// Unable to determine limit, proceed anyway.
		if ( -1 === $memory_limit ) {
			return true;
		}

		$memory_usage = memory_get_usage( true );
		$available    = $memory_limit - $memory_usage;

		// Require at least 32MB free.
		$required = 32 * 1024 * 1024;

		if ( $available < $required ) {
			return new WP_Error(
				'unbsb_memory_limit',
				sprintf(
					/* translators: 1: available memory in MB, 2: required memory in MB */
					__( 'Insufficient memory available. Available: %1$sMB, Required: %2$sMB. Please increase PHP memory_limit.', 'unbelievable-salon-booking' ),
					round( $available / ( 1024 * 1024 ), 1 ),
					round( $required / ( 1024 * 1024 ), 1 )
				)
			);
		}

		return true;
	}

	/**
	 * Get PHP memory limit in bytes.
	 *
	 * @return int Memory limit in bytes, or -1 for unlimited.
	 */
	private function get_memory_limit_bytes() {
		$limit = ini_get( 'memory_limit' );

		if ( '-1' === $limit ) {
			return -1;
		}

		$limit = trim( $limit );
		$value = (int) $limit;
		$unit  = strtolower( substr( $limit, -1 ) );

		switch ( $unit ) {
			case 'g':
				$value *= 1024 * 1024 * 1024;
				break;
			case 'm':
				$value *= 1024 * 1024;
				break;
			case 'k':
				$value *= 1024;
				break;
		}

		return $value;
	}

	/**
	 * Check if a table exists in the database.
	 *
	 * @param string $table_name Full table name.
	 *
	 * @return bool
	 */
	private function table_exists( $table_name ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $this->wpdb->get_var(
			$this->wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
		);

		return ( null !== $result );
	}

	/**
	 * Get column names for a table.
	 *
	 * @param string $table_name Full table name.
	 *
	 * @return array Column names.
	 */
	private function get_table_columns( $table_name ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$columns = $this->wpdb->get_col( "SHOW COLUMNS FROM {$table_name}" );

		return ( null === $columns ) ? array() : $columns;
	}

	/**
	 * Check if a row exists by ID.
	 *
	 * @param string $table_name Full table name.
	 * @param int    $id         Row ID.
	 *
	 * @return bool
	 */
	private function row_exists( $table_name, $id ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $this->wpdb->get_var(
			$this->wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT id FROM {$table_name} WHERE id = %d",
				$id
			)
		);

		return ( null !== $result );
	}

	/**
	 * Columns that contain multiline TEXT data and should use sanitize_textarea_field().
	 *
	 * @var array
	 */
	private $textarea_columns = array(
		'description',
		'bio',
		'notes',
		'internal_notes',
		'content',
		'message',
		'provider_response',
		'applicable_services',
		'applicable_categories',
	);

	/**
	 * Sanitize a row, keeping only columns that exist in the target table.
	 *
	 * @param array $row     Row data.
	 * @param array $columns Valid column names.
	 *
	 * @return array Sanitized row.
	 */
	private function sanitize_row( $row, $columns ) {
		$valid_columns    = array_flip( $columns );
		$textarea_columns = array_flip( $this->textarea_columns );
		$sanitized        = array();

		foreach ( $row as $key => $value ) {
			if ( ! isset( $valid_columns[ $key ] ) ) {
				continue;
			}

			$key = sanitize_key( $key );

			if ( null === $value ) {
				$sanitized[ $key ] = null;
			} elseif ( isset( $textarea_columns[ $key ] ) ) {
				$sanitized[ $key ] = sanitize_textarea_field( (string) $value );
			} else {
				$sanitized[ $key ] = sanitize_text_field( (string) $value );
			}
		}

		return $sanitized;
	}

	/**
	 * Get a summary of data counts for each table.
	 *
	 * @return array|WP_Error Array of table => count or WP_Error.
	 */
	public function get_data_summary() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'unbsb_unauthorized',
				__( 'You do not have permission to view data summary.', 'unbelievable-salon-booking' )
			);
		}

		$summary = array();
		foreach ( $this->tables as $table ) {
			$table_name = $this->prefix . $table;

			if ( ! $this->table_exists( $table_name ) ) {
				$summary[ $table ] = 0;
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$count = $this->wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

			$summary[ $table ] = (int) $count;
		}

		return $summary;
	}
}
