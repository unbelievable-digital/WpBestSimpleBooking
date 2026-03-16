<?php
/**
 * Database helper class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database class
 */
class UNBSB_Database {

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
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb   = $wpdb;
		$this->prefix = $wpdb->prefix . 'unbsb_';
	}

	/**
	 * Return table name
	 *
	 * @param string $table Table name.
	 *
	 * @return string
	 */
	public function table( $table ) {
		return $this->prefix . $table;
	}

	/**
	 * Insert operation
	 *
	 * @param string $table Table name.
	 * @param array  $data  Data.
	 *
	 * @return int|false
	 */
	public function insert( $table, $data ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $this->wpdb->insert( $this->table( $table ), $data );

		if ( false === $result ) {
			return false;
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Update operation
	 *
	 * @param string $table Table name.
	 * @param array  $data  Data.
	 * @param array  $where Where conditions.
	 *
	 * @return int|false
	 */
	public function update( $table, $data, $where ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $this->wpdb->update( $this->table( $table ), $data, $where );
	}

	/**
	 * Delete operation
	 *
	 * @param string $table Table name.
	 * @param array  $where Where conditions.
	 *
	 * @return int|false
	 */
	public function delete( $table, $where ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $this->wpdb->delete( $this->table( $table ), $where );
	}

	/**
	 * Get single row
	 *
	 * @param string $table  Table name.
	 * @param int    $id     ID.
	 * @param string $output Output type.
	 *
	 * @return object|array|null
	 */
	public function get_by_id( $table, $id, $output = OBJECT ) {
		$table_name = $this->table( $table );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				'SELECT * FROM ' . $table_name . ' WHERE id = %d',
				$id
			),
			$output
		);
	}

	/**
	 * Get all rows
	 *
	 * @param string $table   Table name.
	 * @param array  $args    Arguments.
	 * @param string $output  Output type.
	 *
	 * @return array
	 */
	public function get_all( $table, $args = array(), $output = OBJECT ) {
		$defaults = array(
			'where'   => array(),
			'orderby' => 'id',
			'order'   => 'ASC',
			'limit'   => 0,
			'offset'  => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$table_name = $this->table( $table );
		$sql        = 'SELECT * FROM ' . $table_name;

		// Where conditions.
		if ( ! empty( $args['where'] ) ) {
			$conditions = array();
			foreach ( $args['where'] as $key => $value ) {
				$conditions[] = $this->wpdb->prepare( esc_sql( $key ) . ' = %s', $value );
			}
			$sql .= ' WHERE ' . implode( ' AND ', $conditions );
		}

		// Sorting.
		$sql .= sprintf(
			' ORDER BY %s %s',
			esc_sql( $args['orderby'] ),
			esc_sql( $args['order'] )
		);

		// Limit.
		if ( $args['limit'] > 0 ) {
			$sql .= $this->wpdb->prepare( ' LIMIT %d OFFSET %d', $args['limit'], $args['offset'] );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $this->wpdb->get_results( $sql, $output );
	}

	/**
	 * Get count
	 *
	 * @param string $table Table name.
	 * @param array  $where Where conditions.
	 *
	 * @return int
	 */
	public function count( $table, $where = array() ) {
		$table_name = $this->table( $table );
		$sql        = 'SELECT COUNT(*) FROM ' . $table_name;

		if ( ! empty( $where ) ) {
			$conditions = array();
			foreach ( $where as $key => $value ) {
				$conditions[] = $this->wpdb->prepare( esc_sql( $key ) . ' = %s', $value );
			}
			$sql .= ' WHERE ' . implode( ' AND ', $conditions );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $this->wpdb->get_var( $sql );
	}

	/**
	 * Run raw query
	 *
	 * @deprecated 1.0.1 Use prepared_query() instead for security.
	 *
	 * @param string $sql SQL query.
	 *
	 * @return array
	 */
	private function query( $sql ) {
		_doing_it_wrong(
			__METHOD__,
			esc_html__( 'Direct SQL queries are deprecated. Use prepared_query() instead.', 'unbelievable-salon-booking' ),
			'1.0.1'
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $this->wpdb->get_results( $sql );
	}

	/**
	 * Run prepared query
	 *
	 * @param string $sql  SQL query.
	 * @param array  $args Arguments.
	 *
	 * @return array
	 */
	public function prepared_query( $sql, $args = array() ) {
		if ( ! empty( $args ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $this->wpdb->prepare( $sql, $args );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $this->wpdb->get_results( $sql );
	}

	/**
	 * Get last error
	 *
	 * @return string
	 */
	public function last_error() {
		return $this->wpdb->last_error;
	}
}
