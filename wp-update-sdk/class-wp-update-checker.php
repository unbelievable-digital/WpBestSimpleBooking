<?php
/**
 * WP Update Checker
 *
 * Checks a remote server for plugin updates and integrates
 * with the WordPress update system.
 *
 * @package WP_Update_SDK
 * @version 1.0.0
 */

if ( ! class_exists( 'WP_Update_Checker' ) ) {

	class WP_Update_Checker {

		/**
		 * Remote server URL.
		 *
		 * @var string
		 */
		private $update_url;

		/**
		 * Full path to the main plugin file.
		 *
		 * @var string
		 */
		private $plugin_file;

		/**
		 * Plugin slug on the update server.
		 *
		 * @var string
		 */
		private $plugin_slug;

		/**
		 * Plugin basename (e.g. "plugin-dir/plugin-file.php").
		 *
		 * @var string
		 */
		private $plugin_basename;

		/**
		 * How often to check (in hours).
		 *
		 * @var int
		 */
		private $check_interval;

		/**
		 * Transient key for caching.
		 *
		 * @var string
		 */
		private $cache_key;

		/**
		 * Constructor.
		 *
		 * @param string $update_url  Remote update server URL.
		 * @param string $plugin_file Full path to the main plugin file (__FILE__).
		 * @param string $plugin_slug Plugin slug on the update server.
		 * @param int    $check_hours Check interval in hours. Default 12.
		 */
		public function __construct( $update_url, $plugin_file, $plugin_slug, $check_hours = 12 ) {
			$this->update_url      = trailingslashit( $update_url );
			$this->plugin_file     = $plugin_file;
			$this->plugin_slug     = $plugin_slug;
			$this->plugin_basename = plugin_basename( $plugin_file );
			$this->check_interval  = $check_hours;
			$this->cache_key       = 'wp_update_checker_' . md5( $this->plugin_slug );

			// Hook into WordPress update system.
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
			add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
			add_action( 'upgrader_process_complete', array( $this, 'clear_cache' ), 10, 2 );

			// Show update message on plugins page.
			add_action( 'in_plugin_update_message-' . $this->plugin_basename, array( $this, 'update_message' ), 10, 2 );
		}

		/**
		 * Check the remote server for an available update.
		 *
		 * @param object $transient The update_plugins transient.
		 * @return object Modified transient.
		 */
		public function check_for_update( $transient ) {
			if ( empty( $transient->checked ) ) {
				return $transient;
			}

			$remote = $this->get_remote_data();

			if ( false === $remote || ! isset( $remote->version ) ) {
				return $transient;
			}

			$current_version = isset( $transient->checked[ $this->plugin_basename ] )
				? $transient->checked[ $this->plugin_basename ]
				: '0.0.0';

			if ( version_compare( $remote->version, $current_version, '>' ) ) {
				$update              = new stdClass();
				$update->slug        = $this->plugin_slug;
				$update->plugin      = $this->plugin_basename;
				$update->new_version = $remote->version;
				$update->url         = isset( $remote->homepage ) ? $remote->homepage : '';
				$update->package     = isset( $remote->download_url ) ? $remote->download_url : '';
				$update->tested      = isset( $remote->tested ) ? $remote->tested : '';
				$update->requires    = isset( $remote->requires ) ? $remote->requires : '';
				$update->requires_php = isset( $remote->requires_php ) ? $remote->requires_php : '';

				if ( isset( $remote->icons ) ) {
					$update->icons = (array) $remote->icons;
				}
				if ( isset( $remote->banners ) ) {
					$update->banners = (array) $remote->banners;
				}

				$transient->response[ $this->plugin_basename ] = $update;
			} else {
				// No update available — add to no_update list.
				$no_update              = new stdClass();
				$no_update->slug        = $this->plugin_slug;
				$no_update->plugin      = $this->plugin_basename;
				$no_update->new_version = $remote->version;
				$no_update->url         = isset( $remote->homepage ) ? $remote->homepage : '';

				$transient->no_update[ $this->plugin_basename ] = $no_update;
			}

			return $transient;
		}

		/**
		 * Provide plugin information for the "View Details" popup.
		 *
		 * @param false|object|array $result The result object or array.
		 * @param string             $action The API action being performed.
		 * @param object             $args   Plugin API arguments.
		 * @return false|object
		 */
		public function plugin_info( $result, $action, $args ) {
			if ( 'plugin_information' !== $action ) {
				return $result;
			}

			if ( ! isset( $args->slug ) || $this->plugin_slug !== $args->slug ) {
				return $result;
			}

			$remote = $this->get_plugin_info_data();

			if ( false === $remote ) {
				// Fallback to check-update data.
				$remote = $this->get_remote_data();
			}

			if ( false === $remote ) {
				return $result;
			}

			$info              = new stdClass();
			$info->name        = isset( $remote->name ) ? $remote->name : $this->plugin_slug;
			$info->slug        = $this->plugin_slug;
			$info->version     = isset( $remote->version ) ? $remote->version : '';
			$info->author      = isset( $remote->author ) ? $remote->author : '';
			$info->author_profile = isset( $remote->author_profile ) ? $remote->author_profile : '';
			$info->homepage    = isset( $remote->homepage ) ? $remote->homepage : '';
			$info->requires    = isset( $remote->requires ) ? $remote->requires : '';
			$info->tested      = isset( $remote->tested ) ? $remote->tested : '';
			$info->requires_php = isset( $remote->requires_php ) ? $remote->requires_php : '';
			$info->download_link = isset( $remote->download_url )
				? $remote->download_url
				: $this->update_url . 'api/v1/plugin/' . $this->plugin_slug . '/download';
			$info->last_updated = isset( $remote->last_updated ) ? $remote->last_updated : '';

			if ( isset( $remote->sections ) ) {
				$info->sections = (array) $remote->sections;
			}
			if ( isset( $remote->banners ) ) {
				$info->banners = (array) $remote->banners;
			}
			if ( isset( $remote->icons ) ) {
				$info->icons = (array) $remote->icons;
			}

			return $info;
		}

		/**
		 * Fetch detailed plugin info from the info endpoint.
		 *
		 * @return object|false Plugin info data or false on failure.
		 */
		private function get_plugin_info_data() {
			$cache_key = $this->cache_key . '_info';
			$cached    = get_transient( $cache_key );

			if ( false !== $cached ) {
				return $cached;
			}

			$url = $this->update_url . 'api/v1/plugin/' . $this->plugin_slug . '/info';

			$response = wp_remote_get(
				$url,
				array(
					'timeout'   => 15,
					'sslverify' => true,
				)
			);

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				return false;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body );

			if ( empty( $data ) ) {
				return false;
			}

			set_transient( $cache_key, $data, $this->check_interval * HOUR_IN_SECONDS );

			return $data;
		}

		/**
		 * Show an extra message on the plugins page when an update is available.
		 *
		 * @param array  $plugin_data Plugin data.
		 * @param object $response    Update response.
		 */
		public function update_message( $plugin_data, $response ) {
			$remote = $this->get_remote_data();

			if ( false !== $remote && ! empty( $remote->update_notice ) ) {
				echo '<br><strong>' . esc_html( $remote->update_notice ) . '</strong>';
			}
		}

		/**
		 * Clear the cached remote data after an update.
		 *
		 * @param object $upgrader WP_Upgrader instance.
		 * @param array  $options  Upgrade options.
		 */
		public function clear_cache( $upgrader, $options ) {
			if ( 'update' === $options['action'] && 'plugin' === $options['type'] ) {
				delete_transient( $this->cache_key );
			}
		}

		/**
		 * Fetch update data from the remote server (with caching).
		 *
		 * API Endpoints:
		 *   Check Update: POST {update_url}api/v1/check-update
		 *   Plugin Info:  GET  {update_url}api/v1/plugin/{slug}/info
		 *   Download:     GET  {update_url}api/v1/plugin/{slug}/download
		 *
		 * @return object|false Remote data or false on failure.
		 */
		private function get_remote_data() {
			$cached = get_transient( $this->cache_key );

			if ( false !== $cached ) {
				return $cached;
			}

			$url = $this->update_url . 'api/v1/check-update';

			// Include current version and site info for the server.
			$plugin_data     = get_plugin_data( $this->plugin_file );
			$current_version = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '0.0.0';

			$response = wp_remote_post(
				$url,
				array(
					'timeout'   => 15,
					'sslverify' => true,
					'body'      => array(
						'slug'        => $this->plugin_slug,
						'version'     => $current_version,
						'site_url'    => home_url(),
						'wp_version'  => get_bloginfo( 'version' ),
						'php_version' => PHP_VERSION,
					),
				)
			);

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				// Cache failure for 1 hour to avoid hammering the server.
				set_transient( $this->cache_key, false, HOUR_IN_SECONDS );
				return false;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body );

			if ( empty( $data ) || ! isset( $data->version ) ) {
				set_transient( $this->cache_key, false, HOUR_IN_SECONDS );
				return false;
			}

			// Set download URL if not provided.
			if ( empty( $data->download_url ) ) {
				$data->download_url = $this->update_url . 'api/v1/plugin/' . $this->plugin_slug . '/download';
			}

			set_transient( $this->cache_key, $data, $this->check_interval * HOUR_IN_SECONDS );

			return $data;
		}
	}
}
