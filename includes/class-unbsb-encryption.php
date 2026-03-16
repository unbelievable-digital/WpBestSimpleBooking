<?php
/**
 * Encryption Helper - Encrypt/decrypt sensitive data
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Encryption class
 */
class UNBSB_Encryption {

	/**
	 * Encryption method
	 *
	 * @var string
	 */
	private static $method = 'aes-256-cbc';

	/**
	 * Get encryption key
	 *
	 * @return string
	 */
	private static function get_key() {
		// Use WordPress AUTH_KEY or SECURE_AUTH_KEY as base.
		if ( defined( 'AUTH_KEY' ) && AUTH_KEY ) {
			return hash( 'sha256', AUTH_KEY . 'unbsb_encryption' );
		}

		if ( defined( 'SECURE_AUTH_KEY' ) && SECURE_AUTH_KEY ) {
			return hash( 'sha256', SECURE_AUTH_KEY . 'unbsb_encryption' );
		}

		// Fallback (not recommended but prevents fatal error).
		return hash( 'sha256', 'unbsb_default_key_' . get_site_url() );
	}

	/**
	 * Encrypt a string
	 *
	 * @param string $plaintext Plain text to encrypt.
	 *
	 * @return string|false Encrypted string (base64 encoded) or false on failure.
	 */
	public static function encrypt( $plaintext ) {
		if ( empty( $plaintext ) ) {
			return '';
		}

		// Check if OpenSSL is available.
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			// Fallback: simple obfuscation (not secure but better than plain text).
			return 'obf_' . base64_encode( strrev( $plaintext ) );
		}

		$key = self::get_key();
		$iv  = openssl_random_pseudo_bytes( openssl_cipher_iv_length( self::$method ) );

		$encrypted = openssl_encrypt( $plaintext, self::$method, $key, 0, $iv );

		if ( false === $encrypted ) {
			return false;
		}

		// Prepend IV to encrypted data.
		return 'enc_' . base64_encode( $iv . $encrypted );
	}

	/**
	 * Decrypt a string
	 *
	 * @param string $ciphertext Encrypted string (base64 encoded).
	 *
	 * @return string|false Decrypted string or false on failure.
	 */
	public static function decrypt( $ciphertext ) {
		if ( empty( $ciphertext ) ) {
			return '';
		}

		// Check if it's our obfuscated format.
		if ( strpos( $ciphertext, 'obf_' ) === 0 ) {
			return strrev( base64_decode( substr( $ciphertext, 4 ) ) );
		}

		// Check if it's our encrypted format.
		if ( strpos( $ciphertext, 'enc_' ) !== 0 ) {
			// Not encrypted, return as-is (backward compatibility).
			return $ciphertext;
		}

		// Check if OpenSSL is available.
		if ( ! function_exists( 'openssl_decrypt' ) ) {
			return false;
		}

		$key  = self::get_key();
		$data = base64_decode( substr( $ciphertext, 4 ) );

		if ( false === $data ) {
			return false;
		}

		$iv_length = openssl_cipher_iv_length( self::$method );
		$iv        = substr( $data, 0, $iv_length );
		$encrypted = substr( $data, $iv_length );

		$decrypted = openssl_decrypt( $encrypted, self::$method, $key, 0, $iv );

		return $decrypted;
	}

	/**
	 * Check if a value is encrypted
	 *
	 * @param string $value Value to check.
	 *
	 * @return bool
	 */
	public static function is_encrypted( $value ) {
		return strpos( $value, 'enc_' ) === 0 || strpos( $value, 'obf_' ) === 0;
	}

	/**
	 * Save encrypted option
	 *
	 * @param string $option_name  Option name.
	 * @param string $value        Value to encrypt and save.
	 * @param bool   $autoload     Whether to autoload. Default true.
	 *
	 * @return bool
	 */
	public static function save_option( $option_name, $value, $autoload = true ) {
		$encrypted = self::encrypt( $value );

		if ( false === $encrypted ) {
			return false;
		}

		return update_option( $option_name, $encrypted, $autoload );
	}

	/**
	 * Get decrypted option
	 *
	 * @param string $option_name Option name.
	 * @param mixed  $default     Default value.
	 *
	 * @return string
	 */
	public static function get_option( $option_name, $default = '' ) {
		$value = get_option( $option_name, $default );

		if ( empty( $value ) || $value === $default ) {
			return $default;
		}

		$decrypted = self::decrypt( $value );

		if ( false === $decrypted ) {
			return $default;
		}

		return $decrypted;
	}
}
