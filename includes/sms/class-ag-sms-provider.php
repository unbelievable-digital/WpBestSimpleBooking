<?php
/**
 * Abstract SMS Provider sınıfı
 *
 * @package Appointment_General
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract SMS Provider
 */
abstract class AG_SMS_Provider {

	/**
	 * Provider adı
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * API URL
	 *
	 * @var string
	 */
	protected $api_url = '';

	/**
	 * SMS gönder
	 *
	 * @param string $phone   Telefon numarası.
	 * @param string $message Mesaj.
	 *
	 * @return array Success durumu ve mesaj.
	 */
	abstract public function send( $phone, $message );

	/**
	 * Bakiye sorgula
	 *
	 * @return array Bakiye bilgisi veya hata.
	 */
	abstract public function get_balance();

	/**
	 * Kimlik bilgilerini doğrula
	 *
	 * @return bool
	 */
	abstract public function validate_credentials();

	/**
	 * Provider adını getir
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Telefon numarasını formatla
	 *
	 * @param string $phone Telefon numarası.
	 *
	 * @return string Formatlanmış numara.
	 */
	protected function format_phone( $phone ) {
		// Sadece rakamları al
		$phone = preg_replace( '/[^0-9]/', '', $phone );

		// Türkiye için +90 ekle
		if ( strlen( $phone ) === 10 && substr( $phone, 0, 1 ) === '5' ) {
			$phone = '90' . $phone;
		} elseif ( strlen( $phone ) === 11 && substr( $phone, 0, 1 ) === '0' ) {
			$phone = '9' . $phone;
		}

		return $phone;
	}

	/**
	 * Mesajı Türkçe karakterlerden temizle (SMS uyumluluğu için)
	 *
	 * @param string $message Mesaj.
	 *
	 * @return string Temizlenmiş mesaj.
	 */
	protected function sanitize_message( $message ) {
		$turkish = array( 'ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç' );
		$latin   = array( 'i', 'g', 'u', 's', 'o', 'c', 'I', 'G', 'U', 'S', 'O', 'C' );

		return str_replace( $turkish, $latin, $message );
	}

	/**
	 * Hata logla
	 *
	 * @param string $message Hata mesajı.
	 * @param mixed  $data    Ek veri.
	 */
	protected function log_error( $message, $data = null ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( sprintf( '[AG SMS - %s] %s: %s', $this->name, $message, wp_json_encode( $data ) ) );
		}
	}

	/**
	 * Başarı response oluştur
	 *
	 * @param string $message_id Mesaj ID.
	 * @param string $message    Mesaj.
	 *
	 * @return array
	 */
	protected function success_response( $message_id = '', $message = '' ) {
		return array(
			'success'    => true,
			'message_id' => $message_id,
			'message'    => $message,
		);
	}

	/**
	 * Hata response oluştur
	 *
	 * @param string $error_code Hata kodu.
	 * @param string $message    Hata mesajı.
	 *
	 * @return array
	 */
	protected function error_response( $error_code = '', $message = '' ) {
		return array(
			'success'    => false,
			'error_code' => $error_code,
			'message'    => $message,
		);
	}
}
