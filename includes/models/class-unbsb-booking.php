<?php
/**
 * Booking model class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Booking class
 */
class UNBSB_Booking {

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
	private $table = 'bookings';

	/**
	 * Valid statuses
	 *
	 * @var array
	 */
	private $valid_statuses = array( 'pending', 'confirmed', 'cancelled', 'completed', 'no_show' );

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = new UNBSB_Database();
	}

	/**
	 * Create booking
	 *
	 * @param array $data Booking data.
	 *
	 * @return int|WP_Error
	 */
	public function create( $data ) {
		// Multi-service check.
		$is_multi_service = ! empty( $data['service_ids'] );
		$service_ids      = array();

		if ( $is_multi_service ) {
			// Decode if JSON string.
			if ( is_string( $data['service_ids'] ) ) {
				$service_ids = json_decode( $data['service_ids'], true );
			} else {
				$service_ids = $data['service_ids'];
			}

			// Valid array check.
			if ( ! is_array( $service_ids ) || empty( $service_ids ) ) {
				return new WP_Error(
					'invalid_services',
					__( 'Invalid service list.', 'unbelievable-salon-booking' )
				);
			}

			// Assign first service as main service_id (backward compatibility).
			$data['service_id'] = $service_ids[0];
		}

		// Check required fields.
		$required = array( 'service_id', 'staff_id', 'customer_name', 'customer_email', 'booking_date', 'start_time' );

		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new WP_Error(
					'missing_field',
					/* translators: %s: Field name */
					sprintf( __( '%s field is required.', 'unbelievable-salon-booking' ), $field )
				);
			}
		}

		// Conflict check.
		if ( $this->has_conflict( $data ) ) {
			return new WP_Error(
				'booking_conflict',
				__( 'Another booking exists in this time slot.', 'unbelievable-salon-booking' )
			);
		}

		$sanitized = $this->sanitize_data( $data );

		// Generate token.
		$sanitized['token'] = $this->generate_token();

		// Calculate duration and price in multi-service mode.
		$service_model = new UNBSB_Service();
		$staff_model   = new UNBSB_Staff();

		// Validate staff-service relationship.
		$staff_service_ids = array_map( 'absint', $staff_model->get_services( $sanitized['staff_id'] ) );

		if ( $is_multi_service ) {
			// Verify all selected services are offered by this staff member.
			foreach ( $service_ids as $sid ) {
				if ( ! in_array( absint( $sid ), $staff_service_ids, true ) ) {
					return new WP_Error(
						'invalid_staff_service',
						__( 'One or more selected services are not available for this staff member.', 'unbelievable-salon-booking' )
					);
				}
			}

			$total_duration = 0;
			$total_price    = 0;
			$services_data  = array();

			foreach ( $service_ids as $index => $sid ) {
				$service = $service_model->get( absint( $sid ) );
				if ( $service ) {
					// Check for staff custom price/duration.
					$custom = $staff_model->get_service_custom_data( $sanitized['staff_id'], absint( $sid ) );

					$effective_duration = ( $custom && null !== $custom->custom_duration )
						? intval( $custom->custom_duration )
						: intval( $service->duration );

					if ( $custom && null !== $custom->custom_price ) {
						$effective_price = floatval( $custom->custom_price );
					} elseif ( ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price ) ) {
						$effective_price = floatval( $service->discounted_price );
					} else {
						$effective_price = floatval( $service->price );
					}

					$total_duration += $effective_duration;
					$total_price    += $effective_price;

					$services_data[] = array(
						'service_id' => absint( $sid ),
						'staff_id'   => $sanitized['staff_id'],
						'price'      => $effective_price,
						'duration'   => $effective_duration,
						'sort_order' => $index,
					);

					// Add buffer_after of the last service.
					if ( count( $service_ids ) - 1 === $index ) {
						$total_duration += $service->buffer_after;
					}
				}
			}

			$sanitized['total_duration'] = $total_duration;
			$sanitized['price']          = $total_price;

			// Calculate end time.
			$start                 = strtotime( $sanitized['start_time'] );
			$sanitized['end_time'] = gmdate( 'H:i:s', $start + ( $total_duration * 60 ) );
		} elseif ( empty( $sanitized['end_time'] ) ) {
			// Verify the service is offered by this staff member.
			if ( ! in_array( absint( $sanitized['service_id'] ), $staff_service_ids, true ) ) {
				return new WP_Error(
					'invalid_staff_service',
					__( 'The selected service is not available for this staff member.', 'unbelievable-salon-booking' )
				);
			}

			// Single service - check for staff custom price/duration.
			$service = $service_model->get( $sanitized['service_id'] );

			if ( $service ) {
				$custom = $staff_model->get_service_custom_data( $sanitized['staff_id'], $sanitized['service_id'] );

				$effective_duration = ( $custom && null !== $custom->custom_duration )
					? intval( $custom->custom_duration )
					: intval( $service->duration );

				$duration              = $effective_duration + $service->buffer_after;
				$start                 = strtotime( $sanitized['start_time'] );
				$sanitized['end_time'] = gmdate( 'H:i:s', $start + ( $duration * 60 ) );

				if ( $custom && null !== $custom->custom_price ) {
					$sanitized['price'] = floatval( $custom->custom_price );
				} elseif ( ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price ) ) {
					$sanitized['price'] = floatval( $service->discounted_price );
				} else {
					$sanitized['price'] = floatval( $service->price );
				}

				$sanitized['total_duration'] = $duration;
			}
		}

		// Allow filtering the booking price.
		if ( isset( $sanitized['price'] ) ) {
			$sanitized['price'] = apply_filters(
				'unbsb_filter_booking_price',
				$sanitized['price'],
				$sanitized['service_id'] ?? 0,
				$sanitized['staff_id']
			);
		}

		// Promo code validation and discount application.
		if ( ! empty( $data['promo_code'] ) ) {
			$promo_model    = new UNBSB_Promo_Code();
			$promo_code_str = sanitize_text_field( $data['promo_code'] );
			$customer_email = $sanitized['customer_email'];
			$promo_total    = $sanitized['price'] ?? 0;

			// Determine service IDs for validation.
			$promo_service_ids = $is_multi_service ? $service_ids : array( $sanitized['service_id'] );

			$validation = $promo_model->validate( $promo_code_str, $customer_email, $promo_service_ids, $promo_total );

			if ( true === $validation ) {
				$promo = $promo_model->get_by_code( $promo_code_str );

				// Get services data for discount calculation.
				$promo_services_data = array();
				foreach ( $promo_service_ids as $psid ) {
					$ps = $service_model->get( absint( $psid ) );
					if ( $ps ) {
						$promo_services_data[] = $ps;
					}
				}

				$discount_amount = $promo_model->calculate_discount( $promo, $promo_services_data, $promo_total );

				if ( $discount_amount > 0 ) {
					$sanitized['promo_code_id']   = $promo->id;
					$sanitized['discount_amount']  = $discount_amount;
					$sanitized['price']            = max( 0, $promo_total - $discount_amount );
				}
			}
			// If validation fails, we proceed without the discount (server-side safety).
		}

		// Create or get customer (skip if customer_id already provided).
		if ( empty( $sanitized['customer_id'] ) ) {
			$customer_model = new UNBSB_Customer();
			$customer       = $customer_model->find_or_create(
				array(
					'name'  => $sanitized['customer_name'],
					'email' => $sanitized['customer_email'],
					'phone' => $sanitized['customer_phone'] ?? '',
				)
			);

			if ( $customer ) {
				$sanitized['customer_id'] = $customer->id;
			}
		}

		// Auto-confirm if enabled and no explicit status was provided.
		if ( 'yes' === get_option( 'unbsb_auto_confirm', 'no' ) && empty( $data['status'] ) ) {
			$sanitized['status'] = 'confirmed';
		}

		do_action( 'unbsb_before_booking_created', $sanitized );

		$booking_id = $this->db->insert( $this->table, $sanitized );

		if ( $booking_id ) {
			// Save to booking_services table if multi-service.
			if ( $is_multi_service && ! empty( $services_data ) ) {
				$booking_service = new UNBSB_Booking_Service();
				$booking_service->add_multiple( $booking_id, $services_data );
			}

			do_action( 'unbsb_after_booking_created', $booking_id, $sanitized );

			// Record promo code usage.
			if ( ! empty( $sanitized['promo_code_id'] ) && ! empty( $sanitized['discount_amount'] ) ) {
				$promo_model = new UNBSB_Promo_Code();
				$promo_model->record_usage(
					$sanitized['promo_code_id'],
					$booking_id,
					$sanitized['customer_email'],
					$sanitized['discount_amount']
				);
			}

			// Security logging.
			if ( class_exists( 'UNBSB_Security_Logger' ) ) {
				UNBSB_Security_Logger::log_booking_created( $booking_id, $sanitized );
			}
		}

		return $booking_id;
	}

	/**
	 * Update booking
	 *
	 * @param int   $id   Booking ID.
	 * @param array $data Booking data.
	 *
	 * @return int|false
	 */
	public function update( $id, $data ) {
		$sanitized = $this->sanitize_data( $data );

		return $this->db->update( $this->table, $sanitized, array( 'id' => $id ) );
	}

	/**
	 * Update booking status
	 *
	 * @param int    $id     Booking ID.
	 * @param string $status New status.
	 *
	 * @return bool
	 */
	public function update_status( $id, $status ) {
		if ( ! in_array( $status, $this->valid_statuses, true ) ) {
			return false;
		}

		$booking    = $this->get( $id );
		$old_status = $booking ? $booking->status : '';

		$result = $this->db->update(
			$this->table,
			array( 'status' => $status ),
			array( 'id' => $id )
		);

		if ( $result ) {
			do_action( 'unbsb_booking_status_changed', $id, $status, $old_status );

			// Security logging for cancellations.
			if ( 'cancelled' === $status && class_exists( 'UNBSB_Security_Logger' ) ) {
				UNBSB_Security_Logger::log_booking_cancelled( $id, 'Status changed from ' . $old_status );
			}
		}

		return (bool) $result;
	}

	/**
	 * Delete booking
	 *
	 * @param int $id Booking ID.
	 *
	 * @return int|false
	 */
	public function delete( $id ) {
		return $this->db->delete( $this->table, array( 'id' => $id ) );
	}

	/**
	 * Get booking
	 *
	 * @param int $id Booking ID.
	 *
	 * @return object|null
	 */
	public function get( $id ) {
		return $this->db->get_by_id( $this->table, $id );
	}

	/**
	 * Get booking by token
	 *
	 * @param string $token Token.
	 *
	 * @return object|null
	 */
	public function get_by_token( $token ) {
		global $wpdb;

		$table = $wpdb->prefix . 'unbsb_bookings';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . $table . ' WHERE token = %s',
				sanitize_text_field( $token )
			)
		);
	}

	/**
	 * Get booking with details
	 *
	 * @param int $id Booking ID.
	 *
	 * @return object|null
	 */
	public function get_with_details( $id ) {
		global $wpdb;

		$bookings_table = $wpdb->prefix . 'unbsb_bookings';
		$services_table = $wpdb->prefix . 'unbsb_services';
		$staff_table    = $wpdb->prefix . 'unbsb_staff';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$booking = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT b.*,
					s.name as service_name, s.duration as service_duration, s.color as service_color,
					st.name as staff_name, st.email as staff_email, st.phone as staff_phone
				FROM ' . $bookings_table . ' b
				LEFT JOIN ' . $services_table . ' s ON b.service_id = s.id
				LEFT JOIN ' . $staff_table . ' st ON b.staff_id = st.id
				WHERE b.id = %d',
				$id
			)
		);

		if ( $booking ) {
			// Get multi-service list.
			$booking_service = new UNBSB_Booking_Service();
			$services        = $booking_service->get_by_booking( $id );

			if ( ! empty( $services ) ) {
				$booking->services = $services;

				// Also add service names.
				$service_names = array();
				foreach ( $services as $svc ) {
					if ( ! empty( $svc->service_name ) ) {
						$service_names[] = $svc->service_name;
					}
				}
				$booking->services_list = implode( ', ', $service_names );
			} else {
				// Backward compatibility for single service.
				$booking->services      = array();
				$booking->services_list = $booking->service_name;
			}
		}

		return $booking;
	}

	/**
	 * Get bookings
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	public function get_all( $args = array() ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'unbsb_';

		$defaults = array(
			'staff_id'   => 0,
			'service_id' => 0,
			'status'     => '',
			'date_from'  => '',
			'date_to'    => '',
			'search'     => '',
			'orderby'    => 'booking_date',
			'order'      => 'ASC',
			'limit'      => 0,
			'offset'     => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$sql = "SELECT b.*,
				s.name as service_name, s.color as service_color,
				st.name as staff_name
			FROM {$prefix}bookings b
			LEFT JOIN {$prefix}services s ON b.service_id = s.id
			LEFT JOIN {$prefix}staff st ON b.staff_id = st.id
			WHERE 1=1";

		$prepare_args = array();

		if ( $args['staff_id'] ) {
			$sql           .= ' AND b.staff_id = %d';
			$prepare_args[] = $args['staff_id'];
		}

		if ( $args['service_id'] ) {
			$sql           .= ' AND b.service_id = %d';
			$prepare_args[] = $args['service_id'];
		}

		if ( $args['status'] ) {
			$sql           .= ' AND b.status = %s';
			$prepare_args[] = $args['status'];
		}

		if ( $args['date_from'] ) {
			$sql           .= ' AND b.booking_date >= %s';
			$prepare_args[] = $args['date_from'];
		}

		if ( $args['date_to'] ) {
			$sql           .= ' AND b.booking_date <= %s';
			$prepare_args[] = $args['date_to'];
		}

		if ( $args['search'] ) {
			$sql           .= ' AND (b.customer_name LIKE %s OR b.customer_email LIKE %s)';
			$like           = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$prepare_args[] = $like;
			$prepare_args[] = $like;
		}

		$sql .= sprintf(
			' ORDER BY b.%s %s',
			esc_sql( $args['orderby'] ),
			esc_sql( $args['order'] )
		);

		if ( $args['limit'] > 0 ) {
			$sql           .= ' LIMIT %d OFFSET %d';
			$prepare_args[] = $args['limit'];
			$prepare_args[] = $args['offset'];
		}

		if ( ! empty( $prepare_args ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql = $wpdb->prepare( $sql, $prepare_args );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql );
	}

	/**
	 * Get bookings by date
	 *
	 * @param string $date     Date (Y-m-d).
	 * @param int    $staff_id Staff ID.
	 *
	 * @return array
	 */
	public function get_by_date( $date, $staff_id = 0 ) {
		$args = array(
			'date_from' => $date,
			'date_to'   => $date,
			'orderby'   => 'start_time',
		);

		if ( $staff_id ) {
			$args['staff_id'] = $staff_id;
		}

		return $this->get_all( $args );
	}

	/**
	 * Get bookings by customer email
	 *
	 * @param string $email Customer email.
	 *
	 * @return array
	 */
	public function get_by_email( $email ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'unbsb_';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT b.*, s.name as service_name, st.name as staff_name
				FROM {$prefix}bookings b
				LEFT JOIN {$prefix}services s ON b.service_id = s.id
				LEFT JOIN {$prefix}staff st ON b.staff_id = st.id
				WHERE b.customer_email = %s
				ORDER BY b.booking_date DESC, b.start_time DESC",
				sanitize_email( $email )
			)
		);
	}

	/**
	 * Get bookings by staff and date range
	 *
	 * @param int    $staff_id   Staff ID.
	 * @param string $date_from  Start date.
	 * @param string $date_to    End date.
	 *
	 * @return array
	 */
	public function get_by_staff_and_date_range( $staff_id, $date_from, $date_to ) {
		return $this->get_all(
			array(
				'staff_id'  => $staff_id,
				'date_from' => $date_from,
				'date_to'   => $date_to,
				'orderby'   => 'booking_date',
			)
		);
	}

	/**
	 * Check booking conflict
	 *
	 * @param array $data       Booking data.
	 * @param int   $exclude_id Booking ID to exclude.
	 *
	 * @return bool
	 */
	public function has_conflict( $data, $exclude_id = 0 ) {
		global $wpdb;

		// Calculate end time.
		$end_time = $data['end_time'] ?? '';

		if ( empty( $end_time ) ) {
			// First check total_duration (multi-service).
			if ( ! empty( $data['total_duration'] ) ) {
				$start    = strtotime( $data['start_time'] );
				$end_time = gmdate( 'H:i:s', $start + ( absint( $data['total_duration'] ) * 60 ) );
			} else {
				// Existing logic for single service.
				$service_model = new UNBSB_Service();
				$service       = $service_model->get( $data['service_id'] );

				if ( $service ) {
					$start    = strtotime( $data['start_time'] );
					$duration = $service->duration + $service->buffer_after;
					$end_time = gmdate( 'H:i:s', $start + ( $duration * 60 ) );
				}
			}
		}

		$table = $wpdb->prefix . 'unbsb_bookings';

		$sql = $wpdb->prepare(
			'SELECT COUNT(*) FROM ' . $table . "
			WHERE staff_id = %d
			AND booking_date = %s
			AND status NOT IN ('cancelled')
			AND (
				(start_time <= %s AND end_time > %s)
				OR (start_time < %s AND end_time >= %s)
				OR (start_time >= %s AND end_time <= %s)
			)",
			$data['staff_id'],
			$data['booking_date'],
			$data['start_time'],
			$data['start_time'],
			$end_time,
			$end_time,
			$data['start_time'],
			$end_time
		);

		if ( $exclude_id ) {
			$sql .= $wpdb->prepare( ' AND id != %d', $exclude_id );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql ) > 0;
	}

	/**
	 * Get booking count
	 *
	 * @param string $status Status filter.
	 *
	 * @return int
	 */
	public function count( $status = '' ) {
		$where = array();

		if ( $status ) {
			$where['status'] = $status;
		}

		return $this->db->count( $this->table, $where );
	}

	/**
	 * Get today's booking count
	 *
	 * @return int
	 */
	public function count_today() {
		global $wpdb;

		$table = $wpdb->prefix . 'unbsb_bookings';
		$today = current_time( 'Y-m-d' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . $table . " WHERE booking_date = %s AND status NOT IN ('cancelled')",
				$today
			)
		);
	}

	/**
	 * Generate token
	 *
	 * @return string
	 */
	private function generate_token() {
		return wp_generate_password( 32, false );
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

		if ( isset( $data['service_id'] ) ) {
			$sanitized['service_id'] = absint( $data['service_id'] );
		}

		if ( isset( $data['staff_id'] ) ) {
			$sanitized['staff_id'] = absint( $data['staff_id'] );
		}

		if ( isset( $data['customer_id'] ) ) {
			$sanitized['customer_id'] = absint( $data['customer_id'] );
		}

		if ( isset( $data['customer_name'] ) ) {
			$sanitized['customer_name'] = sanitize_text_field( $data['customer_name'] );
		}

		if ( isset( $data['customer_email'] ) ) {
			$sanitized['customer_email'] = sanitize_email( $data['customer_email'] );
		}

		if ( isset( $data['customer_phone'] ) ) {
			$sanitized['customer_phone'] = sanitize_text_field( $data['customer_phone'] );
		}

		if ( isset( $data['booking_date'] ) ) {
			$sanitized['booking_date'] = sanitize_text_field( $data['booking_date'] );
		}

		if ( isset( $data['start_time'] ) ) {
			$sanitized['start_time'] = sanitize_text_field( $data['start_time'] );
		}

		if ( isset( $data['end_time'] ) ) {
			$sanitized['end_time'] = sanitize_text_field( $data['end_time'] );
		}

		if ( isset( $data['price'] ) ) {
			$sanitized['price'] = floatval( $data['price'] );
		}

		if ( isset( $data['status'] ) ) {
			$sanitized['status'] = in_array( $data['status'], $this->valid_statuses, true )
				? $data['status']
				: 'pending';
		}

		if ( isset( $data['notes'] ) ) {
			$sanitized['notes'] = sanitize_textarea_field( $data['notes'] );
		}

		if ( isset( $data['internal_notes'] ) ) {
			$sanitized['internal_notes'] = sanitize_textarea_field( $data['internal_notes'] );
		}

		if ( isset( $data['total_duration'] ) ) {
			$sanitized['total_duration'] = absint( $data['total_duration'] );
		}

		if ( isset( $data['promo_code_id'] ) ) {
			$sanitized['promo_code_id'] = absint( $data['promo_code_id'] );
		}

		if ( isset( $data['discount_amount'] ) ) {
			$sanitized['discount_amount'] = floatval( $data['discount_amount'] );
		}

		if ( isset( $data['paid_amount'] ) ) {
			$sanitized['paid_amount'] = floatval( $data['paid_amount'] );
		}

		if ( isset( $data['payment_method'] ) ) {
			$sanitized['payment_method'] = in_array( $data['payment_method'], array( 'cash', 'card', 'transfer' ), true )
				? $data['payment_method']
				: null;
		}

		return $sanitized;
	}
}
