<?php
/**
 * NetGSM SMS Provider class
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NetGSM SMS Provider
 */
class UNBSB_SMS_NetGSM extends UNBSB_SMS_Provider {

	/**
	 * Provider name
	 *
	 * @var string
	 */
	protected $name = 'NetGSM';

	/**
	 * API URL
	 *
	 * @var string
	 */
	protected $api_url = 'https://api.netgsm.com.tr/sms/send/get/';

	/**
	 * Balance query URL
	 *
	 * @var string
	 */
	protected $balance_url = 'https://api.netgsm.com.tr/balance/list/get/';

	/**
	 * Username
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Password
	 *
	 * @var string
	 */
	private $password;

	/**
	 * Sender ID
	 *
	 * @var string
	 */
	private $sender;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->username = get_option( 'unbsb_sms_netgsm_username', '' );
		// Use encrypted password storage.
		$this->password = UNBSB_Encryption::get_option( 'unbsb_sms_netgsm_password', '' );
		$this->sender   = get_option( 'unbsb_sms_netgsm_sender', '' );
	}

	/**
	 * Send SMS
	 *
	 * @param string $phone   Phone number.
	 * @param string $message Message.
	 *
	 * @return array
	 */
	public function send( $phone, $message ) {
		if ( empty( $this->username ) || empty( $this->password ) ) {
			return $this->error_response( 'credentials_missing', __( 'NetGSM credentials are missing.', 'unbelievable-salon-booking' ) );
		}

		$phone   = $this->format_phone( $phone );
		$message = $this->sanitize_message( $message );

		$params = array(
			'usercode'  => $this->username,
			'password'  => $this->password,
			'gsmno'     => $phone,
			'message'   => $message,
			'msgheader' => $this->sender,
			'dil'       => 'TR',
		);

		$url = add_query_arg( $params, $this->api_url );

		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 30,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			$this->log_error( 'API Request Failed', $response->get_error_message() );
			return $this->error_response( 'api_error', $response->get_error_message() );
		}

		$body = wp_remote_retrieve_body( $response );
		$code = trim( $body );

		return $this->parse_send_response( $code );
	}

	/**
	 * Query balance
	 *
	 * @return array
	 */
	public function get_balance() {
		if ( empty( $this->username ) || empty( $this->password ) ) {
			return $this->error_response( 'credentials_missing', __( 'NetGSM credentials are missing.', 'unbelievable-salon-booking' ) );
		}

		$params = array(
			'usercode' => $this->username,
			'password' => $this->password,
			'stession' => 0,
		);

		$url = add_query_arg( $params, $this->balance_url );

		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 30,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $this->error_response( 'api_error', $response->get_error_message() );
		}

		$body = wp_remote_retrieve_body( $response );

		// NetGSM balance response: TL|Credit format.
		$parts = explode( '|', trim( $body ) );

		if ( count( $parts ) >= 2 ) {
			return array(
				'success' => true,
				'balance' => array(
					'tl'     => floatval( $parts[0] ),
					'credit' => intval( $parts[1] ),
				),
				'message' => sprintf(
					/* translators: 1: TL amount, 2: Credit count */
					__( 'Balance: %1$s TL, %2$d Credits', 'unbelievable-salon-booking' ),
					number_format( floatval( $parts[0] ), 2, ',', '.' ),
					intval( $parts[1] )
				),
			);
		}

		// Error code check.
		return $this->parse_error_code( $body );
	}

	/**
	 * Validate credentials
	 *
	 * @return bool
	 */
	public function validate_credentials() {
		$result = $this->get_balance();
		return ! empty( $result['success'] );
	}

	/**
	 * Parse SMS send response
	 *
	 * @param string $code Response code.
	 *
	 * @return array
	 */
	private function parse_send_response( $code ) {
		// Success: Returns 20 character bulk_id.
		if ( strlen( $code ) >= 10 && is_numeric( substr( $code, 0, 10 ) ) ) {
			return $this->success_response( $code, __( 'SMS sent successfully.', 'unbelievable-salon-booking' ) );
		}

		// Error codes.
		return $this->parse_error_code( $code );
	}

	/**
	 * Parse error code
	 *
	 * @param string $code Error code.
	 *
	 * @return array
	 */
	private function parse_error_code( $code ) {
		$code = trim( $code );

		$errors = array(
			'20'  => __( 'Message sending failed: Invalid message.', 'unbelievable-salon-booking' ),
			'30'  => __( 'Invalid username or password.', 'unbelievable-salon-booking' ),
			'40'  => __( 'Account is not defined.', 'unbelievable-salon-booking' ),
			'50'  => __( 'Account is unauthorized or suspended.', 'unbelievable-salon-booking' ),
			'51'  => __( 'Campaign limit exceeded.', 'unbelievable-salon-booking' ),
			'60'  => __( 'Invalid sender ID.', 'unbelievable-salon-booking' ),
			'70'  => __( 'Invalid parameter or character.', 'unbelievable-salon-booking' ),
			'80'  => __( 'Query error.', 'unbelievable-salon-booking' ),
			'85'  => __( 'Same SMS was sent multiple times.', 'unbelievable-salon-booking' ),
			'100' => __( 'System error.', 'unbelievable-salon-booking' ),
			'101' => __( 'System error.', 'unbelievable-salon-booking' ),
		);

		$message = isset( $errors[ $code ] )
			? $errors[ $code ]
			/* translators: %s: Error code */
			: sprintf( __( 'Unknown error: %s', 'unbelievable-salon-booking' ), $code );

		$this->log_error(
			'SMS Error',
			array(
				'code'    => $code,
				'message' => $message,
			)
		);

		return $this->error_response( $code, $message );
	}

	/**
	 * Send test SMS
	 *
	 * @param string $phone Phone number.
	 *
	 * @return array
	 */
	public function send_test( $phone ) {
		$message = sprintf(
			/* translators: %s: Company name */
			__( 'This is a test message. %s booking system SMS integration is working successfully.', 'unbelievable-salon-booking' ),
			get_option( 'unbsb_company_name', get_bloginfo( 'name' ) )
		);

		return $this->send( $phone, $message );
	}
}
