<?php
/**
 * MailMintPro Class for Update pro plugin
 *
 * @author [MRM Team]
 * @email [support@rextheme.com]
 * @create date 2022-08-09 11:03:17
 * @modify date 2022-08-09 11:03:17
 * @package /app
 */

namespace MailMintPro;

/**
 * MailMintPro Class for Update pro plugin
 */
class MailMintProUpdater {
	/**
	 * API Url.
	 *
	 * @var $api_url
	 */
	public $api_url;

	/**
	 *  Plugin Slug.
	 *
	 * @var $slug
	 */
	private $slug;

	/**
	 *
	 * Plugin Name.
	 *
	 * @var $plugin
	 */
	public $plugin;

	/**
	 * API version.
	 *
	 * @var float $API_VERSION
	 */
	public $API_VERSION; // phpcs:ignore

	/**
	 * Constructor Method
	 *
	 * @param string $api_url Plugin api.
	 * @param string $slug plugin slug.
	 * @param string $plugin plugin name.
	 */
	public function __construct( $api_url, $slug, $plugin ) {
		$this->api_url     = $api_url;
		$this->slug        = $slug;
		$this->plugin      = $plugin;
		$this->API_VERSION = 1.1; // phpcs:ignore

		// Take over the update check.
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_plugin_update' ) );

		// Take over the Plugin info screen.
		add_filter( 'plugins_api', array( $this, 'plugins_api_call' ), 10, 3 );
	}

	/**
	 * Check if any update is pending.
	 *
	 * @param string $checked_data Get Checking data.
	 * @return mixed
	 */
	public function check_for_plugin_update( $checked_data ) {
		if ( !is_object( $checked_data ) || !isset( $checked_data->response ) ) {
			return $checked_data;
		}

		$request_string = $this->prepare_request( 'plugin_update' );

		if ( false === $request_string ) {
			return $checked_data;
		}

		global $wp_version;

		// Start checking for an update.
		$request_uri = $this->api_url . '?' . http_build_query( $request_string, '', '&' );

		// check if cached.
		$data = get_site_transient( 'mailmint-check_for_plugin_update_' . md5( $request_uri ) );

		if ( isset( $_GET['force-check'] ) && $_GET['force-check'] == '1' ) { // phpcs:ignore
			$data = false;
		}

		if ( false === $data ) {
			$data = wp_remote_get(
				$request_uri,
				array(
					'timeout'    => 20,
					'user-agent' => 'WordPress/' . $wp_version . '; mailmintpro/' . MAIL_MINT_PRO_VERSION . '; ' . MAIL_MINT_PRO_INSTANCE,
				)
			);

			if ( is_wp_error( $data ) || 200 !== $data['response']['code'] ) {
				return $checked_data;
			}
			set_site_transient( 'mailmint-check_for_plugin_update_' . md5( $request_uri ), $data, 60 * 60 * 4 );
		}

		$response_block = json_decode( $data['body'] );

		if ( !is_array( $response_block ) || count( $response_block ) < 1 ) {
			return $checked_data;
		}

		// retrieve the last message within the $response_block.
		$response_block = $response_block[ count( $response_block ) - 1 ];
		$response       = isset( $response_block->message ) ? $response_block->message : '';

		if ( is_object( $response ) && !empty( $response ) ) {
			$response                                = $this->postprocess_response( $response );
			$checked_data->response[ $this->plugin ] = $response;
		}
		return $checked_data;
	}

	/**
	 * API call for plugin update notice.
	 *
	 * @param object $def Get Differance.
	 * @param object $action Action for plugin update.
	 * @param array  $args Request argument .
	 * return WP_Error|mixed|void|\WP_Error.
	 */
	public function plugins_api_call( $def, $action, $args ) {
		if ( !is_object( $args ) || !isset( $args->slug ) || $args->slug !== $this->slug ) {
			return $def;
		}

		$request_string = $this->prepare_request( $action, $args );
		if ( false === $request_string ) {
			return new \WP_Error( 'plugins_api_failed', __( 'An error occour when try to identify the pluguin.', 'mailmint-pro' ) . '&lt;/p> &lt;p>&lt;a href=&quot;?&quot; onclick=&quot;document.location.reload(); return false;&quot;>' . __( 'Try again', 'mailmint-pro' ) . '&lt;/a>' );
		}

		$request_uri = $this->api_url . '?' . http_build_query( $request_string, '', '&' );
		$data        = wp_remote_get( $request_uri );

		if ( is_wp_error( $data ) || 200 !== $data['response']['code'] ) {
			return new WP_Error( 'plugins_api_failed', __( 'An Unexpected HTTP Error occurred during the API request.', 'mailmint-pro' ) . '&lt;/p> &lt;p>&lt;a href=&quot;?&quot; onclick=&quot;document.location.reload(); return false;&quot;>' . __( 'Try again', 'mailmint-pro' ) . '&lt;/a>', $data->get_error_message() ); // phpcs ignore.
		}

		$response_block = json_decode( $data['body'] );

		// retrieve the last message within the $response_block.
		$response_block = $response_block[ count( $response_block ) - 1 ];
		$response       = $response_block->message;

		if ( is_object( $response ) && !empty( $response ) ) {
			$response = $this->postprocess_response( $response );
			return $response;
		}
	}

	/**
	 * Post process response of the api.
	 * response
	 *
	 * @param array $response Process response.
	 * @return mixed
	 */
	private function postprocess_response( $response ) {
		// include slug and plugin data.
		$response->slug   = $this->slug;
		$response->plugin = $this->plugin;

		// if sections are being set.
		if ( isset( $response->sections ) ) {
			$response->sections = (array) $response->sections;
		}

		// if banners are being set.
		if ( isset( $response->banners ) ) {
			$response->banners = (array) $response->banners;
		}

		// if icons being set, convert to array.
		if ( isset( $response->icons ) ) {
			$response->icons = (array) $response->icons;
		}

		return $response;
	}

	/**
	 * Prepare param for API request.
	 *
	 * @param object $action Prepare action for update.
	 * @param array  $args Get argument.
	 * @return array
	 */
	public function prepare_request( $action, $args = array() ) {
		global $wp_version;
		$licence_key = get_site_option( 'mail_mint_license_key' );
		return array(
			'woo_sl_action'     => $action,
			'version'           => MAIL_MINT_PRO_VERSION,
			'product_unique_id' => MAIL_MINT_PRO_PRODUCT_ID,
			'licence_key'       => $licence_key,
			'domain'            => MAIL_MINT_PRO_INSTANCE,
			'wp-version'        => $wp_version,
			'api_version'       => $this->API_VERSION, //phpcs:ignore
		);
	}
}
