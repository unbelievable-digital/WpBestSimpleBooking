<?php
/**
 * Public class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public class
 */
class UNBSB_Public {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Constructor
	 *
	 * @param string $version Plugin version.
	 */
	public function __construct( $version ) {
		$this->version = $version;
	}

	/**
	 * Enqueue public CSS
	 */
	public function enqueue_styles() {
		wp_register_style(
			'unbsb-public',
			UNBSB_PLUGIN_URL . 'public/css/unbsb-public.css',
			array(),
			$this->version
		);

		// Inject appearance CSS variables.
		$primary = sanitize_hex_color( get_option( 'unbsb_appearance_primary_color', '#6366f1' ) );
		$accent  = sanitize_hex_color( get_option( 'unbsb_appearance_accent_color', '#10b981' ) );
		$radius  = get_option( 'unbsb_appearance_border_radius', 'rounded' );
		$font    = get_option( 'unbsb_appearance_font_size', 'medium' );

		$radius_map = array(
			'square'  => '0px',
			'rounded' => '12px',
			'pill'    => '50px',
		);
		$font_map = array(
			'small'  => '13px',
			'medium' => '15px',
			'large'  => '17px',
		);

		$radius_val = isset( $radius_map[ $radius ] ) ? $radius_map[ $radius ] : '12px';
		$font_val   = isset( $font_map[ $font ] ) ? $font_map[ $font ] : '15px';

		$inline_css = ":root {
			--unbsb-primary: {$primary};
			--unbsb-accent: {$accent};
			--unbsb-radius: {$radius_val};
			--unbsb-font-size: {$font_val};
		}";

		wp_add_inline_style( 'unbsb-public', $inline_css );
	}

	/**
	 * Enqueue public JS
	 */
	public function enqueue_scripts() {
		wp_register_script(
			'unbsb-public',
			UNBSB_PLUGIN_URL . 'public/js/unbsb-public.js',
			array(),
			$this->version,
			true
		);

		wp_register_script(
			'unbsb-booking-manage',
			UNBSB_PLUGIN_URL . 'public/js/unbsb-booking-manage.js',
			array(),
			$this->version,
			true
		);

		wp_localize_script(
			'unbsb-public',
			'unbsbPublic',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'restUrl'    => rest_url( 'unbsb/v1/' ),
				'nonce'      => wp_create_nonce( 'unbsb_public_nonce' ),
				'strings'    => array(
					'select_service'  => __( 'Select a service', 'unbelievable-salon-booking' ),
					'select_staff'    => __( 'Select a staff member', 'unbelievable-salon-booking' ),
					'select_date'     => __( 'Select a date', 'unbelievable-salon-booking' ),
					'select_time'     => __( 'Select a time', 'unbelievable-salon-booking' ),
					'loading'         => __( 'Loading...', 'unbelievable-salon-booking' ),
					'no_slots'        => __( 'No available time slots for this date.', 'unbelievable-salon-booking' ),
					'no_services'     => __( 'No services found for this staff member.', 'unbelievable-salon-booking' ),
					'no_staff'        => __( 'No staff available.', 'unbelievable-salon-booking' ),
					'booking_success' => __( 'Your booking has been created successfully!', 'unbelievable-salon-booking' ),
					'booking_error'   => __( 'An error occurred. Please try again.', 'unbelievable-salon-booking' ),
					'required_fields' => __( 'Please fill in all required fields.', 'unbelievable-salon-booking' ),
					'book_now'        => __( 'Book Now', 'unbelievable-salon-booking' ),
	'minute_short'    => __( 'min', 'unbelievable-salon-booking' ),
					// Staff availability.
					'nearest_available' => __( 'Nearest available:', 'unbelievable-salon-booking' ),
					'no_available_slots' => __( 'No available slots this week', 'unbelievable-salon-booking' ),
					'any_staff'       => __( 'Any Staff', 'unbelievable-salon-booking' ),
					'any_staff_desc'  => __( 'We\'ll assign the first available staff member', 'unbelievable-salon-booking' ),
					'more_dates'      => __( 'More dates', 'unbelievable-salon-booking' ),
					// Step labels.
					'step_service'    => __( 'Service', 'unbelievable-salon-booking' ),
					'step_staff'      => __( 'Staff', 'unbelievable-salon-booking' ),
					'step_datetime'   => __( 'Date/Time', 'unbelievable-salon-booking' ),
					'step_info'       => __( 'Details', 'unbelievable-salon-booking' ),
					// Summary labels.
					'services'        => __( 'Services', 'unbelievable-salon-booking' ),
					'service'         => __( 'Service', 'unbelievable-salon-booking' ),
					'staff'           => __( 'Staff', 'unbelievable-salon-booking' ),
					'date'            => __( 'Date', 'unbelievable-salon-booking' ),
					'time'            => __( 'Time', 'unbelievable-salon-booking' ),
					'duration'        => __( 'Duration', 'unbelievable-salon-booking' ),
					'total'           => __( 'Total', 'unbelievable-salon-booking' ),
					'discount'        => __( 'Discount', 'unbelievable-salon-booking' ),
					'promo_applied'   => __( 'Promo code applied!', 'unbelievable-salon-booking' ),
					'promo_removed'   => __( 'Promo code removed.', 'unbelievable-salon-booking' ),
					'promo_invalid'   => __( 'Invalid promo code.', 'unbelievable-salon-booking' ),
					'enter_promo'     => __( 'Enter promo code', 'unbelievable-salon-booking' ),
					'apply'           => __( 'Apply', 'unbelievable-salon-booking' ),
					'remove'          => __( 'Remove', 'unbelievable-salon-booking' ),
					'promo_login_required' => __( 'You must be logged in to use a promo code.', 'unbelievable-salon-booking' ),
					'login'           => __( 'Log In', 'unbelievable-salon-booking' ),
					// Calendar.
					'month_names'     => array(
						__( 'January', 'unbelievable-salon-booking' ),
						__( 'February', 'unbelievable-salon-booking' ),
						__( 'March', 'unbelievable-salon-booking' ),
						__( 'April', 'unbelievable-salon-booking' ),
						__( 'May', 'unbelievable-salon-booking' ),
						__( 'June', 'unbelievable-salon-booking' ),
						__( 'July', 'unbelievable-salon-booking' ),
						__( 'August', 'unbelievable-salon-booking' ),
						__( 'September', 'unbelievable-salon-booking' ),
						__( 'October', 'unbelievable-salon-booking' ),
						__( 'November', 'unbelievable-salon-booking' ),
						__( 'December', 'unbelievable-salon-booking' ),
					),
					'day_names'       => array(
						__( 'Mon', 'unbelievable-salon-booking' ),
						__( 'Tue', 'unbelievable-salon-booking' ),
						__( 'Wed', 'unbelievable-salon-booking' ),
						__( 'Thu', 'unbelievable-salon-booking' ),
						__( 'Fri', 'unbelievable-salon-booking' ),
						__( 'Sat', 'unbelievable-salon-booking' ),
						__( 'Sun', 'unbelievable-salon-booking' ),
					),
				),
				'currency'   => array(
					'symbol'   => get_option( 'unbsb_currency_symbol', '₺' ),
					'position' => get_option( 'unbsb_currency_position', 'after' ),
					'code'     => get_option( 'unbsb_currency', 'TRY' ),
				),
				'metaPixel'  => array(
					'enabled' => 'yes' === get_option( 'unbsb_meta_pixel_enabled', 'no' ) && '' !== get_option( 'unbsb_meta_pixel_id', '' ),
					'events'  => get_option( 'unbsb_meta_pixel_events', array() ),
				),
				'dateFormat' => get_option( 'unbsb_date_format', 'd.m.Y' ),
				'timeFormat' => get_option( 'unbsb_time_format', 'H:i' ),
				'captcha'    => array(
					'enabled'  => class_exists( 'UNBSB_Captcha' ) && UNBSB_Captcha::is_enabled(),
					'provider' => class_exists( 'UNBSB_Captcha' ) ? UNBSB_Captcha::get_provider() : 'none',
				),
				'isLoggedIn' => is_user_logged_in(),
				'loginUrl'   => wp_login_url( get_permalink() ),
			)
		);
	}

	/**
	 * Register shortcodes
	 */
	public function register_shortcodes() {
		add_shortcode( 'unbsb_booking_form', array( $this, 'booking_form_shortcode' ) );
		add_shortcode( 'unbsb_services', array( $this, 'services_shortcode' ) );
		add_shortcode( 'unbsb_staff_list', array( $this, 'staff_list_shortcode' ) );
		add_shortcode( 'unbsb_manage_booking', array( $this, 'manage_booking_shortcode' ) );
		add_shortcode( 'unbsb_account', array( $this, 'account_shortcode' ) );
	}

	/**
	 * Booking form shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function booking_form_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'service'  => 0,
				'staff'    => 0,
				'category' => 0,
			),
			$atts
		);

		// Enqueue styles and scripts.
		wp_enqueue_style( 'unbsb-public' );
		wp_enqueue_script( 'unbsb-public' );

		// Enqueue CAPTCHA scripts.
		if ( class_exists( 'UNBSB_Captcha' ) ) {
			UNBSB_Captcha::enqueue_scripts();
		}

		// Get services, categories and staff.
		$service_model  = new UNBSB_Service();
		$category_model = new UNBSB_Category();
		$staff_model    = new UNBSB_Staff();

		$services   = $service_model->get_with_categories();
		$categories = $category_model->get_active();
		$staff      = $staff_model->get_active();

		ob_start();
		include UNBSB_PLUGIN_DIR . 'public/partials/booking-form.php';
		$output = ob_get_clean();

		// Pass services data and flow config as part of the localized unbsbPublic object is not possible
		// because wp_localize_script was already called. Use wp_add_inline_script on the enqueue hook instead.
		// Store data for later output via wp_footer.
		$services_json    = wp_json_encode( $services );
		$flow_config_json = wp_json_encode(
			array(
				'mode'           => $flow_mode,
				'steps'          => $current_steps,
				'stepNumbers'    => $step_numbers,
				'totalSteps'     => $total_steps,
				'hasServiceStep' => $has_service_step,
				'hasStaffStep'   => $has_staff_step,
				'multiService'   => $multi_service,
			)
		);

		// Print inline script directly via wp_footer since wp_add_inline_script may be too late for shortcode context.
		add_action(
			'wp_footer',
			function () use ( $services_json, $flow_config_json ) {
				echo '<script id="unbsb-booking-data">' . "\n";
				echo 'var unbsbServicesData = ' . $services_json . ';' . "\n";
				echo 'var unbsbFlowConfig = ' . $flow_config_json . ';' . "\n";
				echo '</script>' . "\n";
			},
			5
		);

		return $output;
	}

	/**
	 * Services shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function services_shortcode( $atts ) {
		$atts = shortcode_atts( array(), $atts );

		wp_enqueue_style( 'unbsb-public' );

		$service_model = new UNBSB_Service();
		$services      = $service_model->get_active();

		ob_start();
		include UNBSB_PLUGIN_DIR . 'public/partials/services-list.php';
		return ob_get_clean();
	}

	/**
	 * Staff list shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function staff_list_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'show_services' => 'no',
			),
			$atts
		);

		wp_enqueue_style( 'unbsb-public' );

		$staff_model   = new UNBSB_Staff();
		$service_model = new UNBSB_Service();

		$staff = $staff_model->get_active();

		ob_start();
		include UNBSB_PLUGIN_DIR . 'public/partials/staff-list.php';
		return ob_get_clean();
	}

	/**
	 * Manage booking shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function manage_booking_shortcode( $atts ) {
		$atts = shortcode_atts( array(), $atts );

		// Token is taken from URL.
		wp_enqueue_style( 'unbsb-public' );
		wp_enqueue_script( 'unbsb-public' );
		wp_enqueue_script( 'unbsb-booking-manage' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public token-based access, no nonce needed.
		$manage_token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

		wp_localize_script(
			'unbsb-booking-manage',
			'unbsbManageBooking',
			array(
				'token'      => $manage_token,
				'restUrl'    => rest_url( 'unbsb/v1/' ),
				'timeFormat' => get_option( 'unbsb_time_format', 'H:i' ),
				'strings'    => array(
					'loading'      => __( 'Loading...', 'unbelievable-salon-booking' ),
					'selectTime'   => __( 'Select a time', 'unbelievable-salon-booking' ),
					'noSlots'      => __( 'No available slots', 'unbelievable-salon-booking' ),
					'error'        => __( 'An error occurred.', 'unbelievable-salon-booking' ),
					'rescheduled'  => __( 'Your booking has been updated!', 'unbelievable-salon-booking' ),
					'cancelled'    => __( 'Your booking has been cancelled!', 'unbelievable-salon-booking' ),
				),
			)
		);

		ob_start();
		include UNBSB_PLUGIN_DIR . 'public/partials/booking-manage.php';
		return ob_get_clean();
	}

	/**
	 * AJAX: Get available slots
	 */
	public function ajax_get_available_slots() {
		check_ajax_referer( 'unbsb_public_nonce', 'nonce' );

		$staff_id       = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;
		$service_id     = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;
		$date           = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$total_duration = isset( $_POST['total_duration'] ) ? absint( $_POST['total_duration'] ) : 0;

		if ( ! $staff_id || ! $service_id || ! $date ) {
			wp_send_json_error( __( 'Missing parameters.', 'unbelievable-salon-booking' ) );
		}

		$calendar = new UNBSB_Calendar();

		// Use total_duration for multi-service.
		if ( $total_duration > 0 ) {
			$slots = $calendar->get_available_slots_by_duration( $staff_id, $date, $total_duration );
		} else {
			$slots = $calendar->get_available_slots( $staff_id, $service_id, $date );
		}

		wp_send_json_success( $slots );
	}

	/**
	 * AJAX: Create booking
	 */
	public function ajax_create_booking() {
		check_ajax_referer( 'unbsb_public_nonce', 'nonce' );

		// Honeypot check.
		if ( ! empty( $_POST['website'] ) ) {
			if ( class_exists( 'UNBSB_Security_Logger' ) ) {
				UNBSB_Security_Logger::log_spam( 'Honeypot triggered' );
			}
			wp_send_json_error( __( 'Spam detected.', 'unbelievable-salon-booking' ) );
		}

		// CAPTCHA validation.
		if ( class_exists( 'UNBSB_Captcha' ) && UNBSB_Captcha::is_enabled() ) {
			$captcha_token = isset( $_POST['captcha_token'] ) ? sanitize_text_field( wp_unslash( $_POST['captcha_token'] ) ) : '';
			// hCaptcha response.
			if ( empty( $captcha_token ) ) {
				$captcha_token = isset( $_POST['h-captcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['h-captcha-response'] ) ) : '';
			}

			$captcha_result = UNBSB_Captcha::verify( $captcha_token );
			if ( is_wp_error( $captcha_result ) ) {
				wp_send_json_error( $captcha_result->get_error_message() );
			}
		}

		$data = array(
			'service_id'     => isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0,
			'staff_id'       => isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0,
			'booking_date'   => isset( $_POST['booking_date'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_date'] ) ) : '',
			'start_time'     => isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '',
			'customer_name'  => isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '',
			'customer_email' => isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '',
			'customer_phone' => isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '',
			'notes'          => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '',
		);

		// Multi-service support.
		if ( ! empty( $_POST['service_ids'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$service_ids = wp_unslash( $_POST['service_ids'] );

			// Decode if JSON string.
			if ( is_string( $service_ids ) ) {
				$decoded = json_decode( $service_ids, true );
				if ( is_array( $decoded ) ) {
					$service_ids = array_map( 'absint', $decoded );
				}
			} elseif ( is_array( $service_ids ) ) {
				$service_ids = array_map( 'absint', $service_ids );
			}

			if ( ! empty( $service_ids ) ) {
				$data['service_ids'] = $service_ids;
				$data['service_id']  = $service_ids[0]; // First service as primary.
			}
		}

		// Total duration (for multi-service).
		if ( ! empty( $_POST['total_duration'] ) ) {
			$data['total_duration'] = absint( $_POST['total_duration'] );
		}

		// Promo code - requires login.
		if ( ! empty( $_POST['promo_code'] ) ) {
			if ( ! is_user_logged_in() ) {
				wp_send_json_error( __( 'You must be logged in to use a promo code.', 'unbelievable-salon-booking' ) );
			}

			$data['promo_code'] = sanitize_text_field( wp_unslash( $_POST['promo_code'] ) );

			// Override email with logged-in user's email for reliable tracking.
			$current_user          = wp_get_current_user();
			$data['customer_email'] = $current_user->user_email;
		}

		$booking_model = new UNBSB_Booking();
		$result        = $booking_model->create( $data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		$booking = $booking_model->get_with_details( $result );

		wp_send_json_success(
			array(
				'message' => __( 'Your booking has been created successfully!', 'unbelievable-salon-booking' ),
				'booking' => $booking,
			)
		);
	}

	/**
	 * AJAX: Validate promo code
	 */
	public function ajax_validate_promo_code() {
		check_ajax_referer( 'unbsb_public_nonce', 'nonce' );

		$code           = isset( $_POST['promo_code'] ) ? sanitize_text_field( wp_unslash( $_POST['promo_code'] ) ) : '';
		$customer_email = isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '';
		$total_amount   = isset( $_POST['total_amount'] ) ? floatval( $_POST['total_amount'] ) : 0;

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$service_ids_raw = isset( $_POST['service_ids'] ) ? wp_unslash( $_POST['service_ids'] ) : '';

		if ( empty( $code ) ) {
			wp_send_json_error( __( 'Please enter a promo code.', 'unbelievable-salon-booking' ) );
		}

		// Promo codes require login.
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'You must be logged in to use a promo code.', 'unbelievable-salon-booking' ) );
		}

		// Use logged-in user's email for reliable tracking.
		$current_user   = wp_get_current_user();
		$customer_email = $current_user->user_email;

		// Parse service IDs.
		$service_ids = array();
		if ( is_string( $service_ids_raw ) ) {
			$decoded = json_decode( $service_ids_raw, true );
			if ( is_array( $decoded ) ) {
				$service_ids = array_map( 'absint', $decoded );
			}
		} elseif ( is_array( $service_ids_raw ) ) {
			$service_ids = array_map( 'absint', $service_ids_raw );
		}

		// Single service fallback.
		if ( empty( $service_ids ) && ! empty( $_POST['service_id'] ) ) {
			$service_ids = array( absint( $_POST['service_id'] ) );
		}

		$promo_model = new UNBSB_Promo_Code();
		$validation  = $promo_model->validate( $code, $customer_email, $service_ids, $total_amount );

		if ( is_wp_error( $validation ) ) {
			wp_send_json_error( $validation->get_error_message() );
		}

		$promo = $promo_model->get_by_code( $code );

		// Get services data for discount calculation.
		$service_model  = new UNBSB_Service();
		$services_data  = array();

		foreach ( $service_ids as $sid ) {
			$service = $service_model->get( $sid );
			if ( $service ) {
				$services_data[] = $service;
			}
		}

		$discount_amount = $promo_model->calculate_discount( $promo, $services_data, $total_amount );

		wp_send_json_success(
			array(
				'promo_code_id'   => $promo->id,
				'discount_type'   => $promo->discount_type,
				'discount_value'  => $promo->discount_value,
				'discount_amount' => $discount_amount,
				'new_total'       => max( 0, $total_amount - $discount_amount ),
				/* translators: %s: formatted discount amount */
				'message'         => sprintf( __( 'Promo code applied! You save %s', 'unbelievable-salon-booking' ), number_format_i18n( $discount_amount, 2 ) ),
			)
		);
	}

	/**
	 * AJAX: Get staff by service
	 */
	public function ajax_get_staff_for_service() {
		check_ajax_referer( 'unbsb_public_nonce', 'nonce' );

		$service_id = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;

		if ( ! $service_id ) {
			wp_send_json_error( __( 'Service ID is required.', 'unbelievable-salon-booking' ) );
		}

		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get_by_service( $service_id );

		wp_send_json_success( $this->sanitize_staff_for_public( $staff ) );
	}

	/**
	 * AJAX: Get all staff
	 */
	public function ajax_get_all_staff() {
		check_ajax_referer( 'unbsb_public_nonce', 'nonce' );

		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get_active();

		wp_send_json_success( $this->sanitize_staff_for_public( $staff ) );
	}

	/**
	 * AJAX: Get services by staff
	 */
	public function ajax_get_services_for_staff() {
		check_ajax_referer( 'unbsb_public_nonce', 'nonce' );

		$staff_id = isset( $_POST['staff_id'] ) ? absint( $_POST['staff_id'] ) : 0;

		if ( ! $staff_id ) {
			wp_send_json_error( __( 'Staff ID is required.', 'unbelievable-salon-booking' ) );
		}

		$service_model = new UNBSB_Service();
		$services      = $service_model->get_by_staff( $staff_id );

		wp_send_json_success( $services );
	}

	/**
	 * Account shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function account_shortcode( $atts ) {
		$atts = shortcode_atts( array(), $atts );

		wp_enqueue_style( 'unbsb-public' );
		wp_enqueue_script( 'unbsb-public' );

		wp_localize_script(
			'unbsb-public',
			'unbsbAccount',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'unbsb_auth_nonce' ),
				'isLoggedIn' => is_user_logged_in(),
				'strings'    => array(
					'password_mismatch' => __( 'Passwords do not match.', 'unbelievable-salon-booking' ),
					'ok'                => __( 'OK', 'unbelievable-salon-booking' ),
					'error'             => __( 'Error', 'unbelievable-salon-booking' ),
					'connection_error'  => __( 'Connection error', 'unbelievable-salon-booking' ),
				),
			)
		);

		ob_start();
		include UNBSB_PLUGIN_DIR . 'public/partials/account.php';
		return ob_get_clean();
	}

	/**
	 * AJAX: Customer login
	 */
	public function ajax_customer_login() {
		check_ajax_referer( 'unbsb_auth_nonce', 'nonce' );

		$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$password = isset( $_POST['password'] ) ? $_POST['password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $email ) || empty( $password ) ) {
			wp_send_json_error( __( 'Please fill in all required fields.', 'unbelievable-salon-booking' ) );
		}

		$user = wp_signon(
			array(
				'user_login'    => $email,
				'user_password' => $password,
				'remember'      => true,
			),
			is_ssl()
		);

		if ( is_wp_error( $user ) ) {
			wp_send_json_error( __( 'Invalid email or password.', 'unbelievable-salon-booking' ) );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Login successful. Redirecting...', 'unbelievable-salon-booking' ),
			)
		);
	}

	/**
	 * AJAX: Customer register
	 */
	public function ajax_customer_register() {
		check_ajax_referer( 'unbsb_auth_nonce', 'nonce' );

		$name             = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email            = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$phone            = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		$password         = isset( $_POST['password'] ) ? $_POST['password'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$password_confirm = isset( $_POST['password_confirm'] ) ? $_POST['password_confirm'] : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $name ) || empty( $email ) || empty( $password ) ) {
			wp_send_json_error( __( 'Please fill in all required fields.', 'unbelievable-salon-booking' ) );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( __( 'Please enter a valid email address.', 'unbelievable-salon-booking' ) );
		}

		if ( strlen( $password ) < 6 ) {
			wp_send_json_error( __( 'Password must be at least 6 characters.', 'unbelievable-salon-booking' ) );
		}

		if ( $password !== $password_confirm ) {
			wp_send_json_error( __( 'Passwords do not match.', 'unbelievable-salon-booking' ) );
		}

		if ( email_exists( $email ) ) {
			wp_send_json_error( __( 'This email is already registered.', 'unbelievable-salon-booking' ) );
		}

		// Create WP user with unbsb_customer role.
		$user_id = wp_insert_user(
			array(
				'user_login'   => $email,
				'user_email'   => $email,
				'user_pass'    => $password,
				'display_name' => $name,
				'role'         => 'unbsb_customer',
			)
		);

		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( $user_id->get_error_message() );
		}

		// Create customer record linked to WP user.
		$customer_model = new UNBSB_Customer();
		$customer_model->find_or_create(
			array(
				'name'    => $name,
				'email'   => $email,
				'phone'   => $phone,
				'user_id' => $user_id,
			)
		);

		// Auto-login the user.
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );

		wp_send_json_success(
			array(
				'message' => __( 'Registration successful. Redirecting...', 'unbelievable-salon-booking' ),
			)
		);
	}

	/**
	 * AJAX: Get nearest available slots for staff members
	 */
	public function ajax_get_staff_nearest_slots() {
		check_ajax_referer( 'unbsb_public_nonce', 'nonce' );

		$service_id     = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;
		$total_duration = isset( $_POST['total_duration'] ) ? absint( $_POST['total_duration'] ) : 0;

		// Parse service_ids for multi-service.
		$service_ids = array();
		if ( ! empty( $_POST['service_ids'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$raw_ids = wp_unslash( $_POST['service_ids'] );
			if ( is_string( $raw_ids ) ) {
				$decoded = json_decode( $raw_ids, true );
				if ( is_array( $decoded ) ) {
					$service_ids = array_map( 'absint', $decoded );
				} else {
					// Comma-separated fallback: "1,3,5".
					$service_ids = array_map( 'absint', explode( ',', $raw_ids ) );
				}
			} elseif ( is_array( $raw_ids ) ) {
				$service_ids = array_map( 'absint', $raw_ids );
			}
		}

		// Remove zero/invalid IDs.
		$service_ids = array_values( array_filter( $service_ids ) );

		// Single service fallback.
		if ( empty( $service_ids ) && $service_id ) {
			$service_ids = array( $service_id );
		}

		if ( empty( $service_ids ) ) {
			wp_send_json_error( __( 'Service ID is required.', 'unbelievable-salon-booking' ) );
		}

		// Parse optional staff_ids filter.
		$staff_ids = array();
		if ( ! empty( $_POST['staff_ids'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$raw_staff = wp_unslash( $_POST['staff_ids'] );
			if ( is_array( $raw_staff ) ) {
				$staff_ids = array_map( 'absint', $raw_staff );
			}
		}

		$staff_model   = new UNBSB_Staff();
		$service_model = new UNBSB_Service();
		$calendar      = new UNBSB_Calendar();

		// Get relevant staff: filtered or all who offer these services.
		if ( ! empty( $staff_ids ) ) {
			$all_staff = array();
			foreach ( $staff_ids as $sid ) {
				$s = $staff_model->get( $sid );
				if ( $s && 'active' === $s->status ) {
					$all_staff[] = $s;
				}
			}
		} else {
			// Get staff that offer the first service (intersection for multi-service is handled below).
			$all_staff = $staff_model->get_by_service( $service_ids[0] );
		}

		// For multi-service, filter to staff who offer ALL selected services.
		if ( count( $service_ids ) > 1 ) {
			$all_staff = array_filter(
				$all_staff,
				function ( $staff_member ) use ( $staff_model, $service_ids ) {
					$staff_services = array_map( 'absint', $staff_model->get_services( $staff_member->id ) );
					foreach ( $service_ids as $sid ) {
						if ( ! in_array( $sid, $staff_services, true ) ) {
							return false;
						}
					}
					return true;
				}
			);
			$all_staff = array_values( $all_staff );
		}

		// Calculate total_duration from services if not provided.
		if ( $total_duration <= 0 ) {
			$total_duration = 0;
			foreach ( $service_ids as $sid ) {
				$svc = $service_model->get( $sid );
				if ( $svc ) {
					$total_duration += intval( $svc->duration );
				}
			}
		}

		$is_multi   = count( $service_ids ) > 1;
		$today      = current_time( 'Y-m-d' );
		$max_days   = 7;
		$max_slots  = 6;
		$result     = array();

		foreach ( $all_staff as $staff_member ) {
			$nearest_date  = null;
			$nearest_slots = array();

			for ( $d = 0; $d < $max_days; $d++ ) {
				$check_date = gmdate( 'Y-m-d', strtotime( $today . " +{$d} days" ) );

				if ( $is_multi ) {
					$slots = $calendar->get_available_slots_by_duration( $staff_member->id, $check_date, $total_duration );
				} else {
					$slots = $calendar->get_available_slots( $staff_member->id, $service_ids[0], $check_date );
				}

				if ( ! empty( $slots ) ) {
					$nearest_date  = $check_date;
					$nearest_slots = array_slice( array_column( $slots, 'start' ), 0, $max_slots );
					break;
				}
			}

			$formatted_date = null;
			if ( $nearest_date ) {
				$timestamp      = strtotime( $nearest_date );
				$formatted_date = date_i18n( get_option( 'date_format', 'j F, l' ), $timestamp );
			}

			$result[] = array(
				'staff_id'               => $staff_member->id,
				'staff_name'             => $staff_member->name,
				'avatar_url'             => $staff_member->avatar_url ?? '',
				'bio'                    => $staff_member->bio ?? '',
				'nearest_date'           => $nearest_date,
				'nearest_date_formatted' => $formatted_date,
				'slots'                  => $nearest_slots,
			);
		}

		wp_send_json_success( $result );
	}

	/**
	 * Output Meta Pixel base code in wp_head.
	 */
	public function render_meta_pixel() {
		if ( 'yes' !== get_option( 'unbsb_meta_pixel_enabled', 'no' ) ) {
			return;
		}

		$pixel_id = get_option( 'unbsb_meta_pixel_id', '' );
		if ( empty( $pixel_id ) ) {
			return;
		}

		$pixel_events = get_option( 'unbsb_meta_pixel_events', array() );
		$track_page_view = isset( $pixel_events['page_view'] ) && 'yes' === $pixel_events['page_view'];
		?>
		<!-- Meta Pixel Code - Unbelievable Salon Booking -->
		<script>
		!function(f,b,e,v,n,t,s)
		{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};
		if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
		n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t,s)}(window, document,'script',
		'https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '<?php echo esc_js( $pixel_id ); ?>');
		<?php if ( $track_page_view ) : ?>
		fbq('track', 'PageView');
		<?php endif; ?>
		</script>
		<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr( $pixel_id ); ?>&ev=PageView&noscript=1"/></noscript>
		<!-- End Meta Pixel Code -->
		<?php
	}

	/**
	 * Strip private fields from staff data for public responses.
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
}
