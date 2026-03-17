<?php
/**
 * REST API class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API class
 */
class UNBSB_REST_API {

	/**
	 * API namespace
	 *
	 * @var string
	 */
	private $namespace = 'unbsb/v1';

	/**
	 * Register REST routes
	 */
	public function register_routes() {
		// Categories.
		register_rest_route(
			$this->namespace,
			'/categories',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_categories' ),
				'permission_callback' => '__return_true',
			)
		);

		// Services.
		register_rest_route(
			$this->namespace,
			'/services',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_services' ),
				'permission_callback' => '__return_true',
			)
		);

		// Services grouped by category.
		register_rest_route(
			$this->namespace,
			'/services/grouped',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_services_grouped' ),
				'permission_callback' => '__return_true',
			)
		);

		// Services by staff.
		register_rest_route(
			$this->namespace,
			'/services/staff/(?P<staff_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_services_by_staff' ),
				'permission_callback' => '__return_true',
			)
		);

		// Staff.
		register_rest_route(
			$this->namespace,
			'/staff',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_staff' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$this->namespace,
			'/staff/(?P<service_id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_staff_by_service' ),
				'permission_callback' => '__return_true',
			)
		);

		// Available slots.
		register_rest_route(
			$this->namespace,
			'/slots',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_available_slots' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'staff_id'   => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'service_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'date'       => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => array( $this, 'validate_date_format' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Available days.
		register_rest_route(
			$this->namespace,
			'/available-days',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_available_days' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'staff_id'   => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'service_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'month'      => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => array( $this, 'validate_month_format' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Bookings (rate limited).
		register_rest_route(
			$this->namespace,
			'/bookings',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_booking' ),
				'permission_callback' => array( 'UNBSB_Rate_Limiter', 'create_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/bookings/(?P<token>[a-zA-Z0-9]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_booking_by_token' ),
				'permission_callback' => array( 'UNBSB_Rate_Limiter', 'token_permission' ),
			)
		);

		// Booking cancel (rate limited).
		register_rest_route(
			$this->namespace,
			'/bookings/(?P<token>[a-zA-Z0-9]+)/cancel',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'cancel_booking' ),
				'permission_callback' => array( 'UNBSB_Rate_Limiter', 'cancel_permission' ),
			)
		);

		// Booking reschedule (rate limited).
		register_rest_route(
			$this->namespace,
			'/bookings/(?P<token>[a-zA-Z0-9]+)/reschedule',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'reschedule_booking' ),
				'permission_callback' => array( 'UNBSB_Rate_Limiter', 'cancel_permission' ),
			)
		);

		// Booking available slots for reschedule.
		register_rest_route(
			$this->namespace,
			'/bookings/(?P<token>[a-zA-Z0-9]+)/available-slots',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_booking_available_slots' ),
				'permission_callback' => array( 'UNBSB_Rate_Limiter', 'token_permission' ),
				'args'                => array(
					'date' => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => array( $this, 'validate_date_format' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Admin endpoints.
		register_rest_route(
			$this->namespace,
			'/admin/bookings',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'admin_get_bookings' ),
				'permission_callback' => array( $this, 'admin_permission_check' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/admin/calendar-events',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'admin_get_calendar_events' ),
				'permission_callback' => array( $this, 'admin_permission_check' ),
			)
		);

		// Export all data.
		register_rest_route(
			$this->namespace,
			'/admin/export',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'admin_export_data' ),
				'permission_callback' => array( $this, 'admin_manage_options_check' ),
			)
		);

		// Import data.
		register_rest_route(
			$this->namespace,
			'/admin/import',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'admin_import_data' ),
				'permission_callback' => array( $this, 'admin_manage_options_check' ),
				'args'                => array(
					'mode' => array(
						'required'          => false,
						'type'              => 'string',
						'default'           => 'merge',
						'enum'              => array( 'merge', 'replace' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Data summary (table counts).
		register_rest_route(
			$this->namespace,
			'/admin/export/summary',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'admin_export_summary' ),
				'permission_callback' => array( $this, 'admin_manage_options_check' ),
			)
		);

		// Staff portal — earnings.
		register_rest_route(
			$this->namespace,
			'/staff-portal/earnings',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_staff_portal_earnings' ),
				'permission_callback' => array( $this, 'staff_portal_permission' ),
				'args'                => array(
					'period' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Staff portal — payments.
		register_rest_route(
			$this->namespace,
			'/staff-portal/payments',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_staff_portal_payments' ),
				'permission_callback' => array( $this, 'staff_portal_permission' ),
				'args'                => array(
					'date_from' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'date_to' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Staff portal — performance.
		register_rest_route(
			$this->namespace,
			'/staff-portal/performance',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_staff_portal_performance' ),
				'permission_callback' => array( $this, 'staff_portal_permission' ),
				'args'                => array(
					'date_from' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'date_to' => array(
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Admin — staff payments CRUD.
		register_rest_route(
			$this->namespace,
			'/admin/staff/(?P<staff_id>\d+)/payments',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_admin_staff_payments' ),
					'permission_callback' => array( $this, 'admin_permission' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'create_admin_staff_payment' ),
					'permission_callback' => array( $this, 'admin_permission' ),
				),
			)
		);

		// Admin — delete staff payment.
		register_rest_route(
			$this->namespace,
			'/admin/staff/(?P<staff_id>\d+)/payments/(?P<payment_id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_admin_staff_payment' ),
				'permission_callback' => array( $this, 'admin_permission' ),
			)
		);

		// Admin — staff earnings.
		register_rest_route(
			$this->namespace,
			'/admin/staff/(?P<staff_id>\d+)/earnings',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_admin_staff_earnings' ),
				'permission_callback' => array( $this, 'admin_permission' ),
			)
		);
	}

	/**
	 * Get categories
	 *
	 * @return WP_REST_Response
	 */
	public function get_categories() {
		$category_model = new UNBSB_Category();
		$categories     = $category_model->get_active();

		return rest_ensure_response( $categories );
	}

	/**
	 * Get services
	 *
	 * @return WP_REST_Response
	 */
	public function get_services() {
		$service_model = new UNBSB_Service();
		$services      = $service_model->get_active();

		return rest_ensure_response( $services );
	}

	/**
	 * Get services grouped by category
	 *
	 * @return WP_REST_Response
	 */
	public function get_services_grouped() {
		$service_model = new UNBSB_Service();
		$grouped       = $service_model->get_grouped_by_category( true );

		return rest_ensure_response( $grouped );
	}

	/**
	 * Get services by staff
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_services_by_staff( $request ) {
		$staff_id      = $request->get_param( 'staff_id' );
		$service_model = new UNBSB_Service();
		$services      = $service_model->get_by_staff( $staff_id );

		return rest_ensure_response( $services );
	}

	/**
	 * Get staff
	 *
	 * @return WP_REST_Response
	 */
	public function get_staff() {
		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get_active();

		return rest_ensure_response( $this->sanitize_staff_for_public( $staff ) );
	}

	/**
	 * Get staff by service
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_staff_by_service( $request ) {
		$service_id  = $request->get_param( 'service_id' );
		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get_by_service( $service_id );

		return rest_ensure_response( $this->sanitize_staff_for_public( $staff ) );
	}

	/**
	 * Get available slots
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_available_slots( $request ) {
		$staff_id   = $request->get_param( 'staff_id' );
		$service_id = $request->get_param( 'service_id' );
		$date       = $request->get_param( 'date' );

		$calendar = new UNBSB_Calendar();
		$slots    = $calendar->get_available_slots( $staff_id, $service_id, $date );

		return rest_ensure_response( $slots );
	}

	/**
	 * Get available days
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_available_days( $request ) {
		$staff_id   = $request->get_param( 'staff_id' );
		$service_id = $request->get_param( 'service_id' );
		$month      = $request->get_param( 'month' );

		$calendar = new UNBSB_Calendar();
		$days     = $calendar->get_available_days( $staff_id, $service_id, $month );

		return rest_ensure_response( $days );
	}

	/**
	 * Create booking
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_booking( $request ) {
		$data = $request->get_json_params();

		// Honeypot check.
		if ( ! empty( $data['website'] ) ) {
			return new WP_Error( 'spam_detected', __( 'Spam detected.', 'unbelievable-salon-booking' ), array( 'status' => 400 ) );
		}

		$booking_model = new UNBSB_Booking();
		$result        = $booking_model->create( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$booking = $booking_model->get_with_details( $result );

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Your booking has been created successfully.', 'unbelievable-salon-booking' ),
				'booking' => $booking,
			)
		);
	}

	/**
	 * Get booking by token
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_booking_by_token( $request ) {
		$token         = $request->get_param( 'token' );
		$booking_model = new UNBSB_Booking();
		$booking       = $booking_model->get_by_token( $token );

		if ( ! $booking ) {
			return new WP_Error( 'not_found', __( 'Booking not found.', 'unbelievable-salon-booking' ), array( 'status' => 404 ) );
		}

		$booking = $booking_model->get_with_details( $booking->id );

		return rest_ensure_response( $booking );
	}

	/**
	 * Cancel booking
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function cancel_booking( $request ) {
		$token = $request->get_param( 'token' );
		$data  = $request->get_json_params();

		$reason = isset( $data['reason'] ) ? sanitize_textarea_field( $data['reason'] ) : '';

		$booking_manager = new UNBSB_Booking_Manager();
		$result          = $booking_manager->cancel_booking( $token, $reason );

		if ( ! $result['success'] ) {
			return new WP_Error(
				'cancel_failed',
				$result['message'],
				array( 'status' => 400 )
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => $result['message'],
			)
		);
	}

	/**
	 * Reschedule booking
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function reschedule_booking( $request ) {
		$token = $request->get_param( 'token' );
		$data  = $request->get_json_params();

		$new_date     = isset( $data['new_date'] ) ? sanitize_text_field( $data['new_date'] ) : '';
		$new_time     = isset( $data['new_time'] ) ? sanitize_text_field( $data['new_time'] ) : '';
		$new_staff_id = isset( $data['new_staff_id'] ) ? absint( $data['new_staff_id'] ) : null;

		if ( empty( $new_date ) || empty( $new_time ) ) {
			return new WP_Error(
				'missing_params',
				__( 'Date and time are required.', 'unbelievable-salon-booking' ),
				array( 'status' => 400 )
			);
		}

		$booking_manager = new UNBSB_Booking_Manager();
		$result          = $booking_manager->reschedule_booking( $token, $new_date, $new_time, $new_staff_id );

		if ( ! $result['success'] ) {
			return new WP_Error(
				'reschedule_failed',
				$result['message'],
				array( 'status' => 400 )
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => $result['message'],
				'booking' => $result['booking'],
			)
		);
	}

	/**
	 * Get available slots for booking (for rescheduling)
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_booking_available_slots( $request ) {
		$token = $request->get_param( 'token' );
		$date  = $request->get_param( 'date' );

		$booking_model = new UNBSB_Booking();
		$booking       = $booking_model->get_by_token( $token );

		if ( ! $booking ) {
			return new WP_Error(
				'not_found',
				__( 'Booking not found.', 'unbelievable-salon-booking' ),
				array( 'status' => 404 )
			);
		}

		$booking_manager = new UNBSB_Booking_Manager();
		$slots           = $booking_manager->get_available_slots_for_reschedule( $booking, $date );

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $slots,
			)
		);
	}

	/**
	 * Admin: Get bookings
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function admin_get_bookings( $request ) {
		$booking_model = new UNBSB_Booking();

		$limit  = $request->get_param( 'limit' );
		$offset = $request->get_param( 'offset' );

		$args = array(
			'date_from' => $request->get_param( 'date_from' ),
			'date_to'   => $request->get_param( 'date_to' ),
			'staff_id'  => $request->get_param( 'staff_id' ),
			'status'    => $request->get_param( 'status' ),
			'limit'     => ! empty( $limit ) ? $limit : 50,
			'offset'    => ! empty( $offset ) ? $offset : 0,
		);

		$bookings = $booking_model->get_all( array_filter( $args ) );

		return rest_ensure_response( $bookings );
	}

	/**
	 * Admin: Get calendar events
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function admin_get_calendar_events( $request ) {
		$calendar = new UNBSB_Calendar();

		$start    = $request->get_param( 'start' );
		$end      = $request->get_param( 'end' );
		$staff_id = $request->get_param( 'staff_id' );

		$events = $calendar->get_calendar_events( $start, $end, $staff_id );

		return rest_ensure_response( $events );
	}

	/**
	 * Admin: Export all plugin data
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function admin_export_data( $request ) {
		$exporter = new UNBSB_Export_Import();
		$data     = $exporter->export();

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$filename = 'unbsb-export-' . gmdate( 'Y-m-d' ) . '.json';
		$response = new WP_REST_Response( $data );
		$response->header( 'Content-Disposition', 'attachment; filename=' . $filename );
		$response->header( 'Content-Type', 'application/json; charset=utf-8' );

		return $response;
	}

	/**
	 * Admin: Import plugin data
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function admin_import_data( $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error(
				'unbsb_invalid_nonce',
				__( 'Security check failed.', 'unbelievable-salon-booking' ),
				array( 'status' => 403 )
			);
		}

		$params = $request->get_json_params();
		$mode   = $request->get_param( 'mode' );

		if ( empty( $mode ) || ! in_array( $mode, array( 'merge', 'replace' ), true ) ) {
			$mode = 'merge';
		}

		$json = '';

		if ( ! empty( $params['data'] ) ) {
			// Data sent as JSON object in request body.
			$json = wp_json_encode( $params['data'] );
		} elseif ( ! empty( $params['json'] ) ) {
			// Data sent as JSON string.
			$json = $params['json'];
		} else {
			// Check for file upload.
			$files = $request->get_file_params();
			if ( ! empty( $files['file'] ) && UPLOAD_ERR_OK === $files['file']['error'] ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$json = file_get_contents( $files['file']['tmp_name'] );
			}
		}

		if ( empty( $json ) ) {
			return new WP_Error(
				'unbsb_missing_data',
				__( 'No import data provided. Send JSON data in the request body or upload a file.', 'unbelievable-salon-booking' ),
				array( 'status' => 400 )
			);
		}

		$exporter = new UNBSB_Export_Import();
		$result   = $exporter->import( $json, $mode );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Data imported successfully.', 'unbelievable-salon-booking' ),
				'result'  => $result,
			)
		);
	}

	/**
	 * Admin: Get export data summary
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function admin_export_summary( $request ) {
		$exporter = new UNBSB_Export_Import();
		$summary  = $exporter->get_data_summary();

		if ( is_wp_error( $summary ) ) {
			return $summary;
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $summary,
			)
		);
	}

	/**
	 * Get staff portal earnings
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_staff_portal_earnings( $request ) {
		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get_by_user_id( get_current_user_id() );

		if ( ! $staff ) {
			return new WP_Error( 'no_staff', __( 'Staff record not found.', 'unbelievable-salon-booking' ), array( 'status' => 404 ) );
		}

		$period = $request->get_param( 'period' );

		$summary = $staff_model->get_earnings_summary( $staff->id );
		$detail  = $staff_model->get_earnings_detail( $staff->id, $period );

		return rest_ensure_response( array(
			'summary' => $summary,
			'detail'  => $detail,
		) );
	}

	/**
	 * Get staff portal payments
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_staff_portal_payments( $request ) {
		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get_by_user_id( get_current_user_id() );

		if ( ! $staff ) {
			return new WP_Error( 'no_staff', __( 'Staff record not found.', 'unbelievable-salon-booking' ), array( 'status' => 404 ) );
		}

		$date_from = $request->get_param( 'date_from' );
		$date_to   = $request->get_param( 'date_to' );

		$payments = $staff_model->get_payments( $staff->id, $date_from, $date_to );

		return rest_ensure_response( $payments );
	}

	/**
	 * Get staff portal performance
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_staff_portal_performance( $request ) {
		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get_by_user_id( get_current_user_id() );

		if ( ! $staff ) {
			return new WP_Error( 'no_staff', __( 'Staff record not found.', 'unbelievable-salon-booking' ), array( 'status' => 404 ) );
		}

		$date_from = $request->get_param( 'date_from' ) ?: wp_date( 'Y-m-01' );
		$date_to   = $request->get_param( 'date_to' ) ?: wp_date( 'Y-m-t' );

		$metrics      = $staff_model->get_performance_metrics( $staff->id, $date_from, $date_to );
		$top_services = $staff_model->get_top_services( $staff->id, $date_from, $date_to );
		$trend        = $staff_model->get_monthly_trend( $staff->id );

		return rest_ensure_response( array(
			'metrics'      => $metrics,
			'top_services' => $top_services,
			'trend'        => $trend,
		) );
	}

	/**
	 * Get admin staff payments
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_admin_staff_payments( $request ) {
		$staff_model = new UNBSB_Staff();
		$staff_id    = absint( $request->get_param( 'staff_id' ) );
		$date_from   = $request->get_param( 'date_from' );
		$date_to     = $request->get_param( 'date_to' );

		$summary  = $staff_model->get_earnings_summary( $staff_id );
		$payments = $staff_model->get_payments( $staff_id, $date_from, $date_to );

		return rest_ensure_response( array(
			'summary'  => $summary,
			'payments' => $payments,
		) );
	}

	/**
	 * Create admin staff payment
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_admin_staff_payment( $request ) {
		$staff_model = new UNBSB_Staff();
		$staff_id    = absint( $request->get_param( 'staff_id' ) );

		$amount         = floatval( $request->get_param( 'amount' ) );
		$payment_date   = sanitize_text_field( $request->get_param( 'payment_date' ) ?: wp_date( 'Y-m-d' ) );
		$payment_method = sanitize_text_field( $request->get_param( 'payment_method' ) );
		$notes          = sanitize_textarea_field( $request->get_param( 'notes' ) );

		if ( $amount <= 0 ) {
			return new WP_Error( 'invalid_amount', __( 'Amount must be greater than zero.', 'unbelievable-salon-booking' ), array( 'status' => 400 ) );
		}

		$result = $staff_model->record_payment( $staff_id, $amount, $payment_date, $payment_method, $notes, get_current_user_id() );

		if ( ! $result ) {
			return new WP_Error( 'payment_failed', __( 'Failed to record payment.', 'unbelievable-salon-booking' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'success' => true, 'id' => $result ) );
	}

	/**
	 * Delete admin staff payment
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_admin_staff_payment( $request ) {
		$staff_model = new UNBSB_Staff();
		$staff_id    = absint( $request->get_param( 'staff_id' ) );
		$payment_id  = absint( $request->get_param( 'payment_id' ) );

		$deleted = $staff_model->delete_payment( $payment_id, $staff_id );

		if ( ! $deleted ) {
			return new WP_Error( 'delete_failed', __( 'Failed to delete payment.', 'unbelievable-salon-booking' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'success' => true ) );
	}

	/**
	 * Get admin staff earnings
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_admin_staff_earnings( $request ) {
		$staff_model = new UNBSB_Staff();
		$staff_id    = absint( $request->get_param( 'staff_id' ) );
		$period      = $request->get_param( 'period' );

		$summary = $staff_model->get_earnings_summary( $staff_id );
		$detail  = $staff_model->get_earnings_detail( $staff_id, $period );

		return rest_ensure_response( array(
			'summary' => $summary,
			'detail'  => $detail,
		) );
	}

	/**
	 * Strip private fields from staff data for public endpoints.
	 *
	 * @param array $staff_list Array of staff objects.
	 *
	 * @return array Sanitized staff list with only public fields.
	 */
	private function sanitize_staff_for_public( $staff_list ) {
		return array_map(
			function ( $staff ) {
				return (object) array(
					'id'         => $staff->id,
					'name'       => $staff->name,
					'bio'        => $staff->bio,
					'avatar_url' => $staff->avatar_url,
					'status'     => $staff->status,
					'sort_order' => $staff->sort_order,
				);
			},
			$staff_list
		);
	}

	/**
	 * Admin permission check
	 *
	 * @return bool
	 */
	public function admin_permission_check() {
		return current_user_can( 'manage_options' ) || current_user_can( 'unbsb_manage_bookings' );
	}

	/**
	 * Admin manage_options permission check (for export/import)
	 *
	 * @return bool
	 */
	public function admin_manage_options_check() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Staff portal permission check
	 *
	 * @return bool
	 */
	public function staff_portal_permission() {
		return current_user_can( 'unbsb_view_own_bookings' );
	}

	/**
	 * Admin permission check (manage_options only)
	 *
	 * @return bool
	 */
	public function admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Validate date format (Y-m-d)
	 *
	 * @param string          $value   Date value.
	 * @param WP_REST_Request $request Request object.
	 * @param string          $param   Parameter name.
	 *
	 * @return bool|WP_Error
	 */
	public function validate_date_format( $value, $request, $param ) {
		// Check format Y-m-d (e.g., 2026-01-27).
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return new WP_Error(
				'rest_invalid_param',
				sprintf(
					/* translators: %s: parameter name */
					__( 'Invalid date format (%s). Format must be: YYYY-MM-DD.', 'unbelievable-salon-booking' ),
					$param
				),
				array( 'status' => 400 )
			);
		}

		// Validate it's a real date.
		$parts = explode( '-', $value );
		if ( ! checkdate( (int) $parts[1], (int) $parts[2], (int) $parts[0] ) ) {
			return new WP_Error(
				'rest_invalid_param',
				__( 'Invalid date value.', 'unbelievable-salon-booking' ),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Validate month format (Y-m)
	 *
	 * @param string          $value   Month value.
	 * @param WP_REST_Request $request Request object.
	 * @param string          $param   Parameter name.
	 *
	 * @return bool|WP_Error
	 */
	public function validate_month_format( $value, $request, $param ) {
		// Check format Y-m (e.g., 2026-01).
		if ( ! preg_match( '/^\d{4}-\d{2}$/', $value ) ) {
			return new WP_Error(
				'rest_invalid_param',
				sprintf(
					/* translators: %s: parameter name */
					__( 'Invalid month format (%s). Format must be: YYYY-MM.', 'unbelievable-salon-booking' ),
					$param
				),
				array( 'status' => 400 )
			);
		}

		// Validate month is 01-12.
		$parts = explode( '-', $value );
		$month = (int) $parts[1];
		if ( $month < 1 || $month > 12 ) {
			return new WP_Error(
				'rest_invalid_param',
				__( 'Invalid month value (must be between 1-12).', 'unbelievable-salon-booking' ),
				array( 'status' => 400 )
			);
		}

		return true;
	}
}
