<?php
/**
 * Promo code model class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Promo code class
 */
class UNBSB_Promo_Code {

	/**
	 * Database instance
	 *
	 * @var UNBSB_Database
	 */
	private $db;

	/**
	 * Table name
	 *
	 * @var string
	 */
	private $table = 'promo_codes';

	/**
	 * Usage table name
	 *
	 * @var string
	 */
	private $usage_table = 'promo_code_usage';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = new UNBSB_Database();
	}

	/**
	 * Create promo code
	 *
	 * @param array $data Promo code data.
	 *
	 * @return int|false
	 */
	public function create( $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->insert( $this->table, $sanitized );
	}

	/**
	 * Update promo code
	 *
	 * @param int   $id   Promo code ID.
	 * @param array $data Promo code data.
	 *
	 * @return int|false
	 */
	public function update( $id, $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->update( $this->table, $sanitized, array( 'id' => $id ) );
	}

	/**
	 * Delete promo code
	 *
	 * @param int $id Promo code ID.
	 *
	 * @return int|false
	 */
	public function delete( $id ) {
		// First delete usage records.
		$this->db->delete( $this->usage_table, array( 'promo_code_id' => $id ) );

		return $this->db->delete( $this->table, array( 'id' => $id ) );
	}

	/**
	 * Get promo code
	 *
	 * @param int $id Promo code ID.
	 *
	 * @return object|null
	 */
	public function get( $id ) {
		return $this->db->get_by_id( $this->table, $id );
	}

	/**
	 * Get all promo codes
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	public function get_all( $args = array() ) {
		$defaults = array(
			'where'   => array(),
			'orderby' => 'id',
			'order'   => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		return $this->db->get_all( $this->table, $args );
	}

	/**
	 * Get active promo codes
	 *
	 * @return array
	 */
	public function get_active() {
		return $this->get_all(
			array(
				'where' => array( 'status' => 'active' ),
			)
		);
	}

	/**
	 * Get promo code by code (case-insensitive)
	 *
	 * @param string $code Promo code string.
	 *
	 * @return object|null
	 */
	public function get_by_code( $code ) {
		global $wpdb;

		$table_name = $this->db->table( $this->table );
		$code       = strtoupper( sanitize_text_field( $code ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $table_name . ' WHERE code = %s', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$code
			)
		);
	}

	/**
	 * Get usage count for a promo code
	 *
	 * @param int $promo_code_id Promo code ID.
	 *
	 * @return int
	 */
	public function get_usage_count( $promo_code_id ) {
		return $this->db->count( $this->usage_table, array( 'promo_code_id' => $promo_code_id ) );
	}

	/**
	 * Get usage count for a promo code by a specific customer
	 *
	 * @param int    $promo_code_id Promo code ID.
	 * @param string $customer_email Customer email.
	 *
	 * @return int
	 */
	public function get_customer_usage_count( $promo_code_id, $customer_email ) {
		return $this->db->count(
			$this->usage_table,
			array(
				'promo_code_id' => $promo_code_id,
				'customer_email' => $customer_email,
			)
		);
	}

	/**
	 * Record promo code usage
	 *
	 * @param int    $promo_code_id  Promo code ID.
	 * @param int    $booking_id     Booking ID.
	 * @param string $customer_email Customer email.
	 * @param float  $discount_amount Discount amount applied.
	 *
	 * @return int|false
	 */
	public function record_usage( $promo_code_id, $booking_id, $customer_email, $discount_amount ) {
		return $this->db->insert(
			$this->usage_table,
			array(
				'promo_code_id'   => absint( $promo_code_id ),
				'booking_id'      => absint( $booking_id ),
				'customer_email'  => sanitize_email( $customer_email ),
				'discount_amount' => floatval( $discount_amount ),
			)
		);
	}

	/**
	 * Validate a promo code
	 *
	 * Checks code existence, status, date range, usage limits,
	 * first-time-only restriction, minimum services, minimum amount,
	 * and service/category applicability.
	 *
	 * @param string $code           Promo code string.
	 * @param string $customer_email Customer email.
	 * @param array  $service_ids    Array of service IDs.
	 * @param float  $total_amount   Total order amount.
	 *
	 * @return true|WP_Error
	 */
	public function validate( $code, $customer_email, $service_ids, $total_amount ) {
		global $wpdb;

		$promo = $this->get_by_code( $code );

		// 1. Code exists and is active.
		if ( ! $promo || 'active' !== $promo->status ) {
			return new WP_Error(
				'promo_invalid',
				__( 'This promo code is invalid or inactive.', 'unbelievable-salon-booking' )
			);
		}

		// 2. Date range check.
		$today = current_time( 'Y-m-d' );

		if ( ! empty( $promo->start_date ) && $today < $promo->start_date ) {
			return new WP_Error(
				'promo_not_started',
				__( 'This promo code is not yet valid.', 'unbelievable-salon-booking' )
			);
		}

		if ( ! empty( $promo->end_date ) && $today > $promo->end_date ) {
			return new WP_Error(
				'promo_expired',
				__( 'This promo code has expired.', 'unbelievable-salon-booking' )
			);
		}

		// 3. Max uses (overall).
		if ( $promo->max_uses > 0 ) {
			$usage_count = $this->get_usage_count( $promo->id );
			if ( $usage_count >= (int) $promo->max_uses ) {
				return new WP_Error(
					'promo_limit_reached',
					__( 'This promo code has reached its usage limit.', 'unbelievable-salon-booking' )
				);
			}
		}

		// 4. Max uses per customer.
		if ( $promo->max_uses_per_customer > 0 ) {
			$customer_usage = $this->get_customer_usage_count( $promo->id, $customer_email );
			if ( $customer_usage >= (int) $promo->max_uses_per_customer ) {
				return new WP_Error(
					'promo_limit_reached',
					__( 'You have already used this promo code the maximum number of times.', 'unbelievable-salon-booking' )
				);
			}
		}

		// 5. First-time customers only.
		if ( ! empty( $promo->first_time_only ) ) {
			$prefix       = $wpdb->prefix . 'unbsb_';
			$table_name   = $prefix . 'bookings';

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$existing_bookings = (int) $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM ' . $table_name . " WHERE customer_email = %s AND status NOT IN ('cancelled')", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$customer_email
				)
			);

			if ( $existing_bookings > 0 ) {
				return new WP_Error(
					'promo_first_time_only',
					__( 'This promo code is only valid for first-time customers.', 'unbelievable-salon-booking' )
				);
			}
		}

		// 6. Minimum services.
		if ( $promo->min_services > 0 && count( $service_ids ) < (int) $promo->min_services ) {
			return new WP_Error(
				'promo_min_services',
				sprintf(
					/* translators: %d: minimum number of services required */
					__( 'This promo code requires at least %d service(s).', 'unbelievable-salon-booking' ),
					(int) $promo->min_services
				)
			);
		}

		// 7. Minimum order amount.
		if ( $promo->min_order_amount > 0 && (float) $total_amount < (float) $promo->min_order_amount ) {
			return new WP_Error(
				'promo_min_amount',
				sprintf(
					/* translators: %s: minimum order amount */
					__( 'This promo code requires a minimum order amount of %s.', 'unbelievable-salon-booking' ),
					number_format_i18n( (float) $promo->min_order_amount, 2 )
				)
			);
		}

		// 8. Applicable services.
		$applicable_services = $this->decode_json_field( $promo->applicable_services );

		if ( ! empty( $applicable_services ) ) {
			$applicable_services = array_map( 'absint', $applicable_services );
			$service_ids_int     = array_map( 'absint', $service_ids );
			$matching            = array_intersect( $service_ids_int, $applicable_services );

			if ( empty( $matching ) ) {
				return new WP_Error(
					'promo_not_applicable',
					__( 'This promo code is not applicable to the selected services.', 'unbelievable-salon-booking' )
				);
			}
		}

		// 9. Applicable categories.
		$applicable_categories = $this->decode_json_field( $promo->applicable_categories );

		if ( ! empty( $applicable_categories ) ) {
			$applicable_categories = array_map( 'absint', $applicable_categories );
			$prefix                = $wpdb->prefix . 'unbsb_';
			$table_name            = $prefix . 'services';

			// Get category IDs for the selected services.
			$placeholders = implode( ',', array_fill( 0, count( $service_ids ), '%d' ) );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$category_ids = $wpdb->get_col(
				$wpdb->prepare(
					'SELECT DISTINCT category_id FROM ' . $table_name . ' WHERE id IN (' . $placeholders . ')', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					array_map( 'absint', $service_ids )
				)
			);

			$category_ids = array_map( 'absint', $category_ids );
			$matching     = array_intersect( $category_ids, $applicable_categories );

			if ( empty( $matching ) ) {
				return new WP_Error(
					'promo_not_applicable',
					__( 'This promo code is not applicable to the selected service categories.', 'unbelievable-salon-booking' )
				);
			}
		}

		return true;
	}

	/**
	 * Calculate discount amount
	 *
	 * @param object $promo_code    Promo code object from DB.
	 * @param array  $services_data Array of service objects with price and discounted_price properties.
	 * @param float  $total_amount  Total order amount.
	 *
	 * @return float
	 */
	public function calculate_discount( $promo_code, $services_data, $total_amount ) {
		$total_amount = (float) $total_amount;

		if ( 'percentage' === $promo_code->discount_type ) {
			$discount = $total_amount * ( (float) $promo_code->discount_value / 100 );

			return min( $discount, $total_amount );
		}

		if ( 'fixed_amount' === $promo_code->discount_type ) {
			return min( (float) $promo_code->discount_value, $total_amount );
		}

		if ( 'cheapest_free' === $promo_code->discount_type ) {
			$cheapest_price = null;

			foreach ( $services_data as $service ) {
				$effective_price = $this->get_effective_price( $service );

				if ( null === $cheapest_price || $effective_price < $cheapest_price ) {
					$cheapest_price = $effective_price;
				}
			}

			if ( null !== $cheapest_price ) {
				return (float) $cheapest_price;
			}
		}

		return 0.0;
	}

	/**
	 * Get all promo codes with usage counts
	 *
	 * @return array
	 */
	public function get_all_with_usage() {
		global $wpdb;

		$prefix      = $wpdb->prefix . 'unbsb_';
		$promo_table = $prefix . $this->table;
		$usage_table = $prefix . $this->usage_table;

		$sql = "SELECT p.*, COALESCE(u.usage_count, 0) AS usage_count
			FROM {$promo_table} p
			LEFT JOIN (
				SELECT promo_code_id, COUNT(*) AS usage_count
				FROM {$usage_table}
				GROUP BY promo_code_id
			) u ON p.id = u.promo_code_id
			ORDER BY p.id DESC";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql );
	}

	/**
	 * Sanitize data
	 *
	 * @param array $data Raw data.
	 *
	 * @return array
	 */
	private function sanitize_data( $data ) {
		$sanitized = array();

		if ( isset( $data['code'] ) ) {
			$sanitized['code'] = strtoupper( sanitize_text_field( $data['code'] ) );
		}

		if ( isset( $data['description'] ) ) {
			$sanitized['description'] = sanitize_textarea_field( $data['description'] );
		}

		if ( isset( $data['discount_type'] ) ) {
			$sanitized['discount_type'] = in_array( $data['discount_type'], array( 'percentage', 'fixed_amount', 'cheapest_free' ), true )
				? $data['discount_type']
				: 'percentage';
		}

		if ( isset( $data['discount_value'] ) ) {
			$sanitized['discount_value'] = floatval( $data['discount_value'] );
		}

		if ( isset( $data['first_time_only'] ) ) {
			$sanitized['first_time_only'] = absint( $data['first_time_only'] );
		}

		if ( isset( $data['min_services'] ) ) {
			$sanitized['min_services'] = absint( $data['min_services'] );
		}

		if ( isset( $data['min_order_amount'] ) ) {
			$sanitized['min_order_amount'] = floatval( $data['min_order_amount'] );
		}

		if ( isset( $data['max_uses'] ) ) {
			$sanitized['max_uses'] = absint( $data['max_uses'] );
		}

		if ( isset( $data['max_uses_per_customer'] ) ) {
			$sanitized['max_uses_per_customer'] = absint( $data['max_uses_per_customer'] );
		}

		if ( array_key_exists( 'applicable_services', $data ) ) {
			if ( is_array( $data['applicable_services'] ) && ! empty( $data['applicable_services'] ) ) {
				$sanitized['applicable_services'] = wp_json_encode( array_map( 'absint', $data['applicable_services'] ) );
			} else {
				$sanitized['applicable_services'] = null;
			}
		}

		if ( array_key_exists( 'applicable_categories', $data ) ) {
			if ( is_array( $data['applicable_categories'] ) && ! empty( $data['applicable_categories'] ) ) {
				$sanitized['applicable_categories'] = wp_json_encode( array_map( 'absint', $data['applicable_categories'] ) );
			} else {
				$sanitized['applicable_categories'] = null;
			}
		}

		if ( array_key_exists( 'start_date', $data ) ) {
			$sanitized['start_date'] = ( '' !== $data['start_date'] && null !== $data['start_date'] )
				? sanitize_text_field( $data['start_date'] )
				: null;
		}

		if ( array_key_exists( 'end_date', $data ) ) {
			$sanitized['end_date'] = ( '' !== $data['end_date'] && null !== $data['end_date'] )
				? sanitize_text_field( $data['end_date'] )
				: null;
		}

		if ( isset( $data['status'] ) ) {
			$sanitized['status'] = in_array( $data['status'], array( 'active', 'inactive' ), true )
				? $data['status']
				: 'active';
		}

		return $sanitized;
	}

	/**
	 * Decode a JSON field value
	 *
	 * @param string|null $value JSON string or null.
	 *
	 * @return array
	 */
	private function decode_json_field( $value ) {
		if ( empty( $value ) ) {
			return array();
		}

		$decoded = json_decode( $value, true );

		if ( ! is_array( $decoded ) ) {
			return array();
		}

		return $decoded;
	}

	/**
	 * Get effective price for a service
	 *
	 * Uses discounted_price if it is set and valid, otherwise falls back to price.
	 *
	 * @param object $service Service object with price and discounted_price properties.
	 *
	 * @return float
	 */
	private function get_effective_price( $service ) {
		if ( isset( $service->discounted_price ) && null !== $service->discounted_price && '' !== $service->discounted_price && (float) $service->discounted_price > 0 ) {
			return (float) $service->discounted_price;
		}

		return (float) $service->price;
	}
}
