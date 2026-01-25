<?php
/**
 * NetGSM SMS Provider sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NetGSM SMS Provider
 */
class AG_SMS_NetGSM extends AG_SMS_Provider {

	/**
	 * Provider adı
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
	 * Bakiye sorgu URL
	 *
	 * @var string
	 */
	protected $balance_url = 'https://api.netgsm.com.tr/balance/list/get/';

	/**
	 * Kullanıcı adı
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Şifre
	 *
	 * @var string
	 */
	private $password;

	/**
	 * Gönderen ID
	 *
	 * @var string
	 */
	private $sender;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->username = get_option( 'ag_sms_netgsm_username', '' );
		$this->password = get_option( 'ag_sms_netgsm_password', '' );
		$this->sender   = get_option( 'ag_sms_netgsm_sender', '' );
	}

	/**
	 * SMS gönder
	 *
	 * @param string $phone   Telefon numarası.
	 * @param string $message Mesaj.
	 *
	 * @return array
	 */
	public function send( $phone, $message ) {
		if ( empty( $this->username ) || empty( $this->password ) ) {
			return $this->error_response( 'credentials_missing', __( 'NetGSM kimlik bilgileri eksik.', 'appointment-general' ) );
		}

		$phone   = $this->format_phone( $phone );
		$message = $this->sanitize_message( $message );

		$params = array(
			'usercode' => $this->username,
			'password' => $this->password,
			'gsmno'    => $phone,
			'message'  => $message,
			'msgheader' => $this->sender,
			'dil'      => 'TR',
		);

		$url = add_query_arg( $params, $this->api_url );

		$response = wp_remote_get( $url, array(
			'timeout' => 30,
			'sslverify' => true,
		) );

		if ( is_wp_error( $response ) ) {
			$this->log_error( 'API Request Failed', $response->get_error_message() );
			return $this->error_response( 'api_error', $response->get_error_message() );
		}

		$body = wp_remote_retrieve_body( $response );
		$code = trim( $body );

		return $this->parse_send_response( $code );
	}

	/**
	 * Bakiye sorgula
	 *
	 * @return array
	 */
	public function get_balance() {
		if ( empty( $this->username ) || empty( $this->password ) ) {
			return $this->error_response( 'credentials_missing', __( 'NetGSM kimlik bilgileri eksik.', 'appointment-general' ) );
		}

		$params = array(
			'usercode' => $this->username,
			'password' => $this->password,
			'stession' => 0,
		);

		$url = add_query_arg( $params, $this->balance_url );

		$response = wp_remote_get( $url, array(
			'timeout' => 30,
			'sslverify' => true,
		) );

		if ( is_wp_error( $response ) ) {
			return $this->error_response( 'api_error', $response->get_error_message() );
		}

		$body = wp_remote_retrieve_body( $response );

		// NetGSM balance response: TL|Kontör şeklinde
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
					__( 'Bakiye: %1$s TL, %2$d Kontör', 'appointment-general' ),
					number_format( floatval( $parts[0] ), 2, ',', '.' ),
					intval( $parts[1] )
				),
			);
		}

		// Hata kodu kontrolü
		return $this->parse_error_code( $body );
	}

	/**
	 * Kimlik bilgilerini doğrula
	 *
	 * @return bool
	 */
	public function validate_credentials() {
		$result = $this->get_balance();
		return ! empty( $result['success'] );
	}

	/**
	 * SMS gönderim yanıtını parse et
	 *
	 * @param string $code Yanıt kodu.
	 *
	 * @return array
	 */
	private function parse_send_response( $code ) {
		// Başarılı: 20 karakterli bulk_id döner
		if ( strlen( $code ) >= 10 && is_numeric( substr( $code, 0, 10 ) ) ) {
			return $this->success_response( $code, __( 'SMS başarıyla gönderildi.', 'appointment-general' ) );
		}

		// Hata kodları
		return $this->parse_error_code( $code );
	}

	/**
	 * Hata kodunu parse et
	 *
	 * @param string $code Hata kodu.
	 *
	 * @return array
	 */
	private function parse_error_code( $code ) {
		$code = trim( $code );

		$errors = array(
			'20'  => __( 'Mesaj gönderme başarısız: Geçersiz mesaj.', 'appointment-general' ),
			'30'  => __( 'Kullanıcı adı veya şifre hatalı.', 'appointment-general' ),
			'40'  => __( 'Hesap tanımlı değil.', 'appointment-general' ),
			'50'  => __( 'Hesap yetkisiz veya askıda.', 'appointment-general' ),
			'51'  => __( 'Kampanya limiti aşıldı.', 'appointment-general' ),
			'60'  => __( 'Geçersiz gönderen ID.', 'appointment-general' ),
			'70'  => __( 'Geçersiz parametre veya karakter.', 'appointment-general' ),
			'80'  => __( 'Sorgulama hatası.', 'appointment-general' ),
			'85'  => __( 'Aynı SMS birden fazla kez gönderildi.', 'appointment-general' ),
			'100' => __( 'Sistem hatası.', 'appointment-general' ),
			'101' => __( 'Sistem hatası.', 'appointment-general' ),
		);

		$message = isset( $errors[ $code ] )
			? $errors[ $code ]
			: sprintf( __( 'Bilinmeyen hata: %s', 'appointment-general' ), $code );

		$this->log_error( 'SMS Error', array( 'code' => $code, 'message' => $message ) );

		return $this->error_response( $code, $message );
	}

	/**
	 * Test SMS gönder
	 *
	 * @param string $phone Telefon numarası.
	 *
	 * @return array
	 */
	public function send_test( $phone ) {
		$message = sprintf(
			/* translators: %s: Company name */
			__( 'Bu bir test mesajidir. %s randevu sistemi SMS entegrasyonu basariyla calisıyor.', 'appointment-general' ),
			get_option( 'ag_company_name', get_bloginfo( 'name' ) )
		);

		return $this->send( $phone, $message );
	}
}
