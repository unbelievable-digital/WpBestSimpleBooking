<?php
/**
 * REST API sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API sınıfı
 */
class AG_REST_API {

	/**
	 * API namespace
	 *
	 * @var string
	 */
	private $namespace = 'ag/v1';

	/**
	 * REST rotalarını kaydet
	 */
	public function register_routes() {
		// Categories
		register_rest_route( $this->namespace, '/categories', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_categories' ),
			'permission_callback' => '__return_true',
		) );

		// Services
		register_rest_route( $this->namespace, '/services', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_services' ),
			'permission_callback' => '__return_true',
		) );

		// Services grouped by category
		register_rest_route( $this->namespace, '/services/grouped', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_services_grouped' ),
			'permission_callback' => '__return_true',
		) );

		// Services by staff
		register_rest_route( $this->namespace, '/services/staff/(?P<staff_id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_services_by_staff' ),
			'permission_callback' => '__return_true',
		) );

		// Staff
		register_rest_route( $this->namespace, '/staff', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_staff' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $this->namespace, '/staff/(?P<service_id>\d+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_staff_by_service' ),
			'permission_callback' => '__return_true',
		) );

		// Available slots
		register_rest_route( $this->namespace, '/slots', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_available_slots' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'staff_id'   => array(
					'required' => true,
					'type'     => 'integer',
				),
				'service_id' => array(
					'required' => true,
					'type'     => 'integer',
				),
				'date'       => array(
					'required' => true,
					'type'     => 'string',
				),
			),
		) );

		// Available days
		register_rest_route( $this->namespace, '/available-days', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_available_days' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'staff_id'   => array(
					'required' => true,
					'type'     => 'integer',
				),
				'service_id' => array(
					'required' => true,
					'type'     => 'integer',
				),
				'month'      => array(
					'required' => true,
					'type'     => 'string',
				),
			),
		) );

		// Bookings
		register_rest_route( $this->namespace, '/bookings', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'create_booking' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $this->namespace, '/bookings/(?P<token>[a-zA-Z0-9]+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_booking_by_token' ),
			'permission_callback' => '__return_true',
		) );

		// Booking cancel
		register_rest_route( $this->namespace, '/bookings/(?P<token>[a-zA-Z0-9]+)/cancel', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'cancel_booking' ),
			'permission_callback' => '__return_true',
		) );

		// Booking reschedule
		register_rest_route( $this->namespace, '/bookings/(?P<token>[a-zA-Z0-9]+)/reschedule', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'reschedule_booking' ),
			'permission_callback' => '__return_true',
		) );

		// Booking available slots for reschedule
		register_rest_route( $this->namespace, '/bookings/(?P<token>[a-zA-Z0-9]+)/available-slots', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_booking_available_slots' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'date' => array(
					'required' => true,
					'type'     => 'string',
				),
			),
		) );

		// Admin endpoints
		register_rest_route( $this->namespace, '/admin/bookings', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'admin_get_bookings' ),
			'permission_callback' => array( $this, 'admin_permission_check' ),
		) );

		register_rest_route( $this->namespace, '/admin/calendar-events', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'admin_get_calendar_events' ),
			'permission_callback' => array( $this, 'admin_permission_check' ),
		) );
	}

	/**
	 * Kategorileri getir
	 *
	 * @return WP_REST_Response
	 */
	public function get_categories() {
		$category_model = new AG_Category();
		$categories     = $category_model->get_active();

		return rest_ensure_response( $categories );
	}

	/**
	 * Servisleri getir
	 *
	 * @return WP_REST_Response
	 */
	public function get_services() {
		$service_model = new AG_Service();
		$services      = $service_model->get_active();

		return rest_ensure_response( $services );
	}

	/**
	 * Kategorilere gore gruplu servisleri getir
	 *
	 * @return WP_REST_Response
	 */
	public function get_services_grouped() {
		$service_model = new AG_Service();
		$grouped       = $service_model->get_grouped_by_category( true );

		return rest_ensure_response( $grouped );
	}

	/**
	 * Personele göre servisleri getir
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_services_by_staff( $request ) {
		$staff_id      = $request->get_param( 'staff_id' );
		$service_model = new AG_Service();
		$services      = $service_model->get_by_staff( $staff_id );

		return rest_ensure_response( $services );
	}

	/**
	 * Personeli getir
	 *
	 * @return WP_REST_Response
	 */
	public function get_staff() {
		$staff_model = new AG_Staff();
		$staff       = $staff_model->get_active();

		return rest_ensure_response( $staff );
	}

	/**
	 * Servise göre personeli getir
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_staff_by_service( $request ) {
		$service_id  = $request->get_param( 'service_id' );
		$staff_model = new AG_Staff();
		$staff       = $staff_model->get_by_service( $service_id );

		return rest_ensure_response( $staff );
	}

	/**
	 * Müsait slotları getir
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_available_slots( $request ) {
		$staff_id   = $request->get_param( 'staff_id' );
		$service_id = $request->get_param( 'service_id' );
		$date       = $request->get_param( 'date' );

		$calendar = new AG_Calendar();
		$slots    = $calendar->get_available_slots( $staff_id, $service_id, $date );

		return rest_ensure_response( $slots );
	}

	/**
	 * Müsait günleri getir
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function get_available_days( $request ) {
		$staff_id   = $request->get_param( 'staff_id' );
		$service_id = $request->get_param( 'service_id' );
		$month      = $request->get_param( 'month' );

		$calendar = new AG_Calendar();
		$days     = $calendar->get_available_days( $staff_id, $service_id, $month );

		return rest_ensure_response( $days );
	}

	/**
	 * Randevu oluştur
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_booking( $request ) {
		$data = $request->get_json_params();

		// Honeypot kontrolü
		if ( ! empty( $data['website'] ) ) {
			return new WP_Error( 'spam_detected', __( 'Spam tespit edildi.', 'appointment-general' ), array( 'status' => 400 ) );
		}

		$booking_model = new AG_Booking();
		$result        = $booking_model->create( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$booking = $booking_model->get_with_details( $result );

		return rest_ensure_response( array(
			'success' => true,
			'message' => __( 'Randevunuz başarıyla oluşturuldu.', 'appointment-general' ),
			'booking' => $booking,
		) );
	}

	/**
	 * Token ile randevu getir
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_booking_by_token( $request ) {
		$token         = $request->get_param( 'token' );
		$booking_model = new AG_Booking();
		$booking       = $booking_model->get_by_token( $token );

		if ( ! $booking ) {
			return new WP_Error( 'not_found', __( 'Randevu bulunamadı.', 'appointment-general' ), array( 'status' => 404 ) );
		}

		$booking = $booking_model->get_with_details( $booking->id );

		return rest_ensure_response( $booking );
	}

	/**
	 * Randevuyu iptal et
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function cancel_booking( $request ) {
		$token = $request->get_param( 'token' );
		$data  = $request->get_json_params();

		$reason = isset( $data['reason'] ) ? sanitize_textarea_field( $data['reason'] ) : '';

		// Booking Manager yükle.
		if ( ! class_exists( 'AG_Booking_Manager' ) ) {
			require_once AG_PLUGIN_DIR . 'includes/class-ag-booking-manager.php';
		}

		$booking_manager = new AG_Booking_Manager();
		$result          = $booking_manager->cancel_booking( $token, $reason );

		if ( ! $result['success'] ) {
			return new WP_Error(
				'cancel_failed',
				$result['message'],
				array( 'status' => 400 )
			);
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => $result['message'],
		) );
	}

	/**
	 * Randevuyu yeniden planla
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
				__( 'Tarih ve saat gerekli.', 'appointment-general' ),
				array( 'status' => 400 )
			);
		}

		// Booking Manager yükle.
		if ( ! class_exists( 'AG_Booking_Manager' ) ) {
			require_once AG_PLUGIN_DIR . 'includes/class-ag-booking-manager.php';
		}

		$booking_manager = new AG_Booking_Manager();
		$result          = $booking_manager->reschedule_booking( $token, $new_date, $new_time, $new_staff_id );

		if ( ! $result['success'] ) {
			return new WP_Error(
				'reschedule_failed',
				$result['message'],
				array( 'status' => 400 )
			);
		}

		return rest_ensure_response( array(
			'success' => true,
			'message' => $result['message'],
			'booking' => $result['booking'],
		) );
	}

	/**
	 * Randevu için müsait slotları getir (yeniden planlama için)
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_booking_available_slots( $request ) {
		$token = $request->get_param( 'token' );
		$date  = $request->get_param( 'date' );

		$booking_model = new AG_Booking();
		$booking       = $booking_model->get_by_token( $token );

		if ( ! $booking ) {
			return new WP_Error(
				'not_found',
				__( 'Randevu bulunamadı.', 'appointment-general' ),
				array( 'status' => 404 )
			);
		}

		// Booking Manager yükle.
		if ( ! class_exists( 'AG_Booking_Manager' ) ) {
			require_once AG_PLUGIN_DIR . 'includes/class-ag-booking-manager.php';
		}

		$booking_manager = new AG_Booking_Manager();
		$slots           = $booking_manager->get_available_slots_for_reschedule( $booking, $date );

		return rest_ensure_response( array(
			'success' => true,
			'data'    => $slots,
		) );
	}

	/**
	 * Admin: Randevuları getir
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function admin_get_bookings( $request ) {
		$booking_model = new AG_Booking();

		$args = array(
			'date_from' => $request->get_param( 'date_from' ),
			'date_to'   => $request->get_param( 'date_to' ),
			'staff_id'  => $request->get_param( 'staff_id' ),
			'status'    => $request->get_param( 'status' ),
			'limit'     => $request->get_param( 'limit' ) ?: 50,
			'offset'    => $request->get_param( 'offset' ) ?: 0,
		);

		$bookings = $booking_model->get_all( array_filter( $args ) );

		return rest_ensure_response( $bookings );
	}

	/**
	 * Admin: Takvim eventlerini getir
	 *
	 * @param WP_REST_Request $request Request.
	 *
	 * @return WP_REST_Response
	 */
	public function admin_get_calendar_events( $request ) {
		$calendar = new AG_Calendar();

		$start    = $request->get_param( 'start' );
		$end      = $request->get_param( 'end' );
		$staff_id = $request->get_param( 'staff_id' );

		$events = $calendar->get_calendar_events( $start, $end, $staff_id );

		return rest_ensure_response( $events );
	}

	/**
	 * Admin yetki kontrolü
	 *
	 * @return bool
	 */
	public function admin_permission_check() {
		return current_user_can( 'manage_options' ) || current_user_can( 'ag_manage_bookings' );
	}
}
