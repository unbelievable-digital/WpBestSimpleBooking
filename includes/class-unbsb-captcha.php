<?php
/**
 * CAPTCHA Helper - reCAPTCHA v3 and hCaptcha support
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CAPTCHA class
 */
class UNBSB_Captcha {

	/**
	 * Provider types
	 */
	const PROVIDER_NONE      = 'none';
	const PROVIDER_RECAPTCHA = 'recaptcha';
	const PROVIDER_HCAPTCHA  = 'hcaptcha';

	/**
	 * reCAPTCHA verify URL
	 *
	 * @var string
	 */
	private static $recaptcha_verify_url = 'https://www.google.com/recaptcha/api/siteverify';

	/**
	 * hCaptcha verify URL
	 *
	 * @var string
	 */
	private static $hcaptcha_verify_url = 'https://hcaptcha.com/siteverify';

	/**
	 * Check if CAPTCHA is enabled
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$provider = self::get_provider();
		return self::PROVIDER_NONE !== $provider && self::has_keys();
	}

	/**
	 * Get current provider
	 *
	 * @return string
	 */
	public static function get_provider() {
		return get_option( 'unbsb_captcha_provider', self::PROVIDER_NONE );
	}

	/**
	 * Check if keys are configured
	 *
	 * @return bool
	 */
	public static function has_keys() {
		$site_key   = self::get_site_key();
		$secret_key = self::get_secret_key();

		return ! empty( $site_key ) && ! empty( $secret_key );
	}

	/**
	 * Get site key
	 *
	 * @return string
	 */
	public static function get_site_key() {
		return get_option( 'unbsb_captcha_site_key', '' );
	}

	/**
	 * Get secret key
	 *
	 * @return string
	 */
	public static function get_secret_key() {
		return UNBSB_Encryption::get_option( 'unbsb_captcha_secret_key', '' );
	}

	/**
	 * Get minimum score for reCAPTCHA v3
	 *
	 * @return float
	 */
	public static function get_min_score() {
		return (float) get_option( 'unbsb_captcha_min_score', 0.5 );
	}

	/**
	 * Enqueue CAPTCHA scripts
	 */
	public static function enqueue_scripts() {
		if ( ! self::is_enabled() ) {
			return;
		}

		$provider = self::get_provider();
		$site_key = self::get_site_key();

		if ( self::PROVIDER_RECAPTCHA === $provider ) {
			// reCAPTCHA v3.
			wp_enqueue_script(
				'google-recaptcha',
				'https://www.google.com/recaptcha/api.js?render=' . esc_attr( $site_key ),
				array(),
				'3.0',
				true
			);

			// Inline script to get token.
			wp_add_inline_script(
				'google-recaptcha',
				'
				window.unbsbGetCaptchaToken = function(action) {
					return new Promise(function(resolve, reject) {
						grecaptcha.ready(function() {
							grecaptcha.execute("' . esc_js( $site_key ) . '", {action: action})
								.then(function(token) {
									resolve(token);
								})
								.catch(function(error) {
									reject(error);
								});
						});
					});
				};
				'
			);
		} elseif ( self::PROVIDER_HCAPTCHA === $provider ) {
			// hCaptcha.
			wp_enqueue_script(
				'hcaptcha',
				'https://js.hcaptcha.com/1/api.js',
				array(),
				'1.0',
				true
			);
		}
	}

	/**
	 * Render CAPTCHA widget (for hCaptcha)
	 *
	 * @return string
	 */
	public static function render_widget() {
		if ( ! self::is_enabled() ) {
			return '';
		}

		$provider = self::get_provider();
		$site_key = self::get_site_key();

		if ( self::PROVIDER_HCAPTCHA === $provider ) {
			return '<div class="h-captcha" data-sitekey="' . esc_attr( $site_key ) . '"></div>';
		}

		// reCAPTCHA v3 is invisible, just add hidden input.
		if ( self::PROVIDER_RECAPTCHA === $provider ) {
			return '<input type="hidden" name="captcha_token" id="unbsb-captcha-token" value="">';
		}

		return '';
	}

	/**
	 * Verify CAPTCHA response
	 *
	 * @param string $token CAPTCHA token/response.
	 *
	 * @return bool|WP_Error
	 */
	public static function verify( $token ) {
		if ( ! self::is_enabled() ) {
			return true; // CAPTCHA disabled, skip verification.
		}

		if ( empty( $token ) ) {
			// Log suspicious activity.
			if ( class_exists( 'UNBSB_Security_Logger' ) ) {
				UNBSB_Security_Logger::log_spam( 'Missing CAPTCHA token' );
			}
			return new WP_Error(
				'captcha_missing',
				__( 'CAPTCHA verification is required.', 'unbelievable-salon-booking' )
			);
		}

		$provider   = self::get_provider();
		$secret_key = self::get_secret_key();

		if ( self::PROVIDER_RECAPTCHA === $provider ) {
			return self::verify_recaptcha( $token, $secret_key );
		} elseif ( self::PROVIDER_HCAPTCHA === $provider ) {
			return self::verify_hcaptcha( $token, $secret_key );
		}

		return true;
	}

	/**
	 * Verify reCAPTCHA v3 token
	 *
	 * @param string $token      CAPTCHA token.
	 * @param string $secret_key Secret key.
	 *
	 * @return bool|WP_Error
	 */
	private static function verify_recaptcha( $token, $secret_key ) {
		$response = wp_remote_post(
			self::$recaptcha_verify_url,
			array(
				'timeout' => 10,
				'body'    => array(
					'secret'   => $secret_key,
					'response' => $token,
					'remoteip' => self::get_client_ip(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			// Log error but allow submission (fail open).
			if ( class_exists( 'UNBSB_Security_Logger' ) ) {
				UNBSB_Security_Logger::log(
					'captcha_error',
					'reCAPTCHA API error: ' . $response->get_error_message(),
					array(),
					'error'
				);
			}
			return true; // Fail open to not block legitimate users.
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['success'] ) ) {
			$error_codes = isset( $body['error-codes'] ) ? implode( ', ', $body['error-codes'] ) : 'unknown';

			if ( class_exists( 'UNBSB_Security_Logger' ) ) {
				UNBSB_Security_Logger::log_spam( 'reCAPTCHA failed: ' . $error_codes );
			}

			return new WP_Error(
				'captcha_failed',
				__( 'CAPTCHA verification failed.', 'unbelievable-salon-booking' )
			);
		}

		// Check score for reCAPTCHA v3.
		$min_score = self::get_min_score();
		$score     = isset( $body['score'] ) ? (float) $body['score'] : 1.0;

		if ( $score < $min_score ) {
			if ( class_exists( 'UNBSB_Security_Logger' ) ) {
				UNBSB_Security_Logger::log_spam( 'reCAPTCHA score too low: ' . $score );
			}

			return new WP_Error(
				'captcha_score_low',
				__( 'Security verification failed. Please try again.', 'unbelievable-salon-booking' )
			);
		}

		return true;
	}

	/**
	 * Verify hCaptcha token
	 *
	 * @param string $token      CAPTCHA token.
	 * @param string $secret_key Secret key.
	 *
	 * @return bool|WP_Error
	 */
	private static function verify_hcaptcha( $token, $secret_key ) {
		$response = wp_remote_post(
			self::$hcaptcha_verify_url,
			array(
				'timeout' => 10,
				'body'    => array(
					'secret'   => $secret_key,
					'response' => $token,
					'remoteip' => self::get_client_ip(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			// Log error but allow submission (fail open).
			if ( class_exists( 'UNBSB_Security_Logger' ) ) {
				UNBSB_Security_Logger::log(
					'captcha_error',
					'hCaptcha API error: ' . $response->get_error_message(),
					array(),
					'error'
				);
			}
			return true;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body['success'] ) ) {
			$error_codes = isset( $body['error-codes'] ) ? implode( ', ', $body['error-codes'] ) : 'unknown';

			if ( class_exists( 'UNBSB_Security_Logger' ) ) {
				UNBSB_Security_Logger::log_spam( 'hCaptcha failed: ' . $error_codes );
			}

			return new WP_Error(
				'captcha_failed',
				__( 'CAPTCHA verification failed.', 'unbelievable-salon-booking' )
			);
		}

		return true;
	}

	/**
	 * Get client IP address
	 *
	 * @return string
	 */
	private static function get_client_ip() {
		$headers = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'REMOTE_ADDR',
		);

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
				if ( strpos( $ip, ',' ) !== false ) {
					$ips = explode( ',', $ip );
					$ip  = trim( $ips[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '';
	}

	/**
	 * Get provider options for settings
	 *
	 * @return array
	 */
	public static function get_provider_options() {
		return array(
			self::PROVIDER_NONE      => __( 'Disabled', 'unbelievable-salon-booking' ),
			self::PROVIDER_RECAPTCHA => __( 'Google reCAPTCHA v3', 'unbelievable-salon-booking' ),
			self::PROVIDER_HCAPTCHA  => __( 'hCaptcha', 'unbelievable-salon-booking' ),
		);
	}
}
