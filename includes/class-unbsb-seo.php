<?php
/**
 * SEO class - Schema.org, Open Graph, Twitter Cards
 *
 * @package Unbelievable_Salon_Booking
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO class
 */
class UNBSB_SEO {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_head', array( $this, 'output_schema_markup' ), 5 );
		add_action( 'wp_head', array( $this, 'output_open_graph' ), 5 );
	}

	/**
	 * Schema.org markup output
	 */
	public function output_schema_markup() {
		// Only run on pages that have the booking form.
		global $post;

		if ( ! $post || ! has_shortcode( $post->post_content, 'unbsb_booking_form' ) ) {
			return;
		}

		$schema = $this->get_local_business_schema();

		if ( ! empty( $schema ) ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . '</script>' . "\n";
		}

		// Services schema.
		$services_schema = $this->get_services_schema();

		if ( ! empty( $services_schema ) ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $services_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . '</script>' . "\n";
		}
	}

	/**
	 * LocalBusiness Schema
	 *
	 * @return array
	 */
	public function get_local_business_schema() {
		$company_name    = get_option( 'unbsb_company_name', get_bloginfo( 'name' ) );
		$company_phone   = get_option( 'unbsb_company_phone', '' );
		$company_address = get_option( 'unbsb_company_address', '' );
		$company_email   = get_option( 'unbsb_admin_email', get_option( 'admin_email' ) );
		$business_type   = get_option( 'unbsb_seo_business_type', 'BeautySalon' );
		$logo_url        = get_option( 'unbsb_seo_logo_url', '' );
		$price_range     = get_option( 'unbsb_seo_price_range', '₺₺' );

		// Social media.
		$social_facebook  = get_option( 'unbsb_social_facebook', '' );
		$social_instagram = get_option( 'unbsb_social_instagram', '' );
		$social_twitter   = get_option( 'unbsb_social_twitter', '' );

		$schema = array(
			'@context' => 'https://schema.org',
			'@type'    => $business_type,
			'name'     => $company_name,
			'url'      => home_url(),
		);

		// Logo.
		if ( ! empty( $logo_url ) ) {
			$schema['logo']  = $logo_url;
			$schema['image'] = $logo_url;
		}

		// Phone.
		if ( ! empty( $company_phone ) ) {
			$schema['telephone'] = $company_phone;
		}

		// Email.
		if ( ! empty( $company_email ) ) {
			$schema['email'] = $company_email;
		}

		// Address.
		if ( ! empty( $company_address ) ) {
			$schema['address'] = array(
				'@type'          => 'PostalAddress',
				'streetAddress'  => $company_address,
				'addressCountry' => get_option( 'unbsb_seo_country', 'TR' ),
			);

			$city = get_option( 'unbsb_seo_city', '' );
			if ( ! empty( $city ) ) {
				$schema['address']['addressLocality'] = $city;
			}

			$postal_code = get_option( 'unbsb_seo_postal_code', '' );
			if ( ! empty( $postal_code ) ) {
				$schema['address']['postalCode'] = $postal_code;
			}
		}

		// Price range.
		if ( ! empty( $price_range ) ) {
			$schema['priceRange'] = $price_range;
		}

		// Working hours.
		$opening_hours = $this->get_opening_hours_spec();
		if ( ! empty( $opening_hours ) ) {
			$schema['openingHoursSpecification'] = $opening_hours;
		}

		// Social media.
		$same_as = array();
		if ( ! empty( $social_facebook ) ) {
			$same_as[] = $social_facebook;
		}
		if ( ! empty( $social_instagram ) ) {
			$same_as[] = $social_instagram;
		}
		if ( ! empty( $social_twitter ) ) {
			$same_as[] = $social_twitter;
		}
		if ( ! empty( $same_as ) ) {
			$schema['sameAs'] = $same_as;
		}

		// Reservation action.
		$schema['potentialAction'] = array(
			'@type'  => 'ReserveAction',
			'target' => array(
				'@type'          => 'EntryPoint',
				'urlTemplate'    => get_permalink(),
				'actionPlatform' => array(
					'http://schema.org/DesktopWebPlatform',
					'http://schema.org/MobileWebPlatform',
				),
			),
			'result' => array(
				'@type' => 'Reservation',
				'name'  => __( 'Booking', 'unbelievable-salon-booking' ),
			),
		);

		return apply_filters( 'unbsb_local_business_schema', $schema );
	}

	/**
	 * Services Schema
	 *
	 * @return array
	 */
	public function get_services_schema() {
		$service_model = new UNBSB_Service();
		$services      = $service_model->get_active();

		if ( empty( $services ) ) {
			return array();
		}

		$company_name    = get_option( 'unbsb_company_name', get_bloginfo( 'name' ) );
		$currency_symbol = get_option( 'unbsb_currency', 'TRY' );

		$items = array();

		foreach ( $services as $service ) {
			$items[] = array(
				'@type'       => 'Service',
				'name'        => $service->name,
				'description' => ! empty( $service->description ) ? $service->description : $service->name,
				'provider'    => array(
					'@type' => 'LocalBusiness',
					'name'  => $company_name,
				),
				'offers'      => array(
					'@type'         => 'Offer',
					'price'         => ( ! empty( $service->discounted_price ) && floatval( $service->discounted_price ) < floatval( $service->price ) )
						? $service->discounted_price
						: $service->price,
					'priceCurrency' => $currency_symbol,
				),
			);
		}

		return array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'name'            => __( 'Our Services', 'unbelievable-salon-booking' ),
			'numberOfItems'   => count( $items ),
			'itemListElement' => $items,
		);
	}

	/**
	 * Working hours schema
	 *
	 * @return array
	 */
	private function get_opening_hours_spec() {
		$staff_model = new UNBSB_Staff();
		$staff       = $staff_model->get_active();

		if ( empty( $staff ) ) {
			return array();
		}

		// Get the first staff member's hours (as general business hours).
		global $wpdb;
		$table = $wpdb->prefix . 'unbsb_working_hours';

		$first_staff_id = $staff[0]->id;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$hours = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . $table . ' WHERE staff_id = %d AND is_working = 1 ORDER BY day_of_week ASC',
				$first_staff_id
			)
		);

		if ( empty( $hours ) ) {
			return array();
		}

		$day_map = array(
			0 => 'Sunday',
			1 => 'Monday',
			2 => 'Tuesday',
			3 => 'Wednesday',
			4 => 'Thursday',
			5 => 'Friday',
			6 => 'Saturday',
		);

		$specs = array();

		foreach ( $hours as $hour ) {
			$specs[] = array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => $day_map[ $hour->day_of_week ],
				'opens'     => substr( $hour->start_time, 0, 5 ),
				'closes'    => substr( $hour->end_time, 0, 5 ),
			);
		}

		return $specs;
	}

	/**
	 * Open Graph and Twitter Cards output
	 */
	public function output_open_graph() {
		global $post;

		if ( ! $post || ! has_shortcode( $post->post_content, 'unbsb_booking_form' ) ) {
			return;
		}

		$company_name = get_option( 'unbsb_company_name', get_bloginfo( 'name' ) );
		$description  = get_option( 'unbsb_seo_description', __( 'Book online - fast and easy!', 'unbelievable-salon-booking' ) );
		$logo_url     = get_option( 'unbsb_seo_logo_url', '' );
		$page_url     = get_permalink();
		$page_title   = get_the_title() . ' - ' . $company_name;

		// Open Graph.
		echo '<meta property="og:type" content="website" />' . "\n";
		echo '<meta property="og:title" content="' . esc_attr( $page_title ) . '" />' . "\n";
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\n";
		echo '<meta property="og:url" content="' . esc_url( $page_url ) . '" />' . "\n";
		echo '<meta property="og:site_name" content="' . esc_attr( $company_name ) . '" />' . "\n";
		echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '" />' . "\n";

		if ( ! empty( $logo_url ) ) {
			echo '<meta property="og:image" content="' . esc_url( $logo_url ) . '" />' . "\n";
		}

		// Twitter Cards.
		echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $page_title ) . '" />' . "\n";
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '" />' . "\n";

		if ( ! empty( $logo_url ) ) {
			echo '<meta name="twitter:image" content="' . esc_url( $logo_url ) . '" />' . "\n";
		}

		$twitter_handle = get_option( 'unbsb_social_twitter_handle', '' );
		if ( ! empty( $twitter_handle ) ) {
			echo '<meta name="twitter:site" content="@' . esc_attr( $twitter_handle ) . '" />' . "\n";
		}
	}
}
