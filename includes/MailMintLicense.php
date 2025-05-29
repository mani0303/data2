<?php


namespace MailMintPro;

use MintMailPro\Mint_Pro_Helper;

class MailMintLicense {

	public function __construct() {
		add_action( 'admin_init', array( $this, 'licence_management_operations' ) );
		add_action( 'admin_notices', array( $this, 'licensing_admin_notices' ) );
	}


	/**
	 * license management operations
	 */
	public function licence_management_operations() {
		if ( isset( $_POST['mail_mint_pro_license_activate'] ) ) {
			$this->do_license_operation( 'activate' );
		} elseif ( isset( $_POST['mail_mint_pro_license_deactivate'] ) ) {
			$this->do_license_operation( 'deactivate' );
		}
	}


	/**
	 * license checking
	 *
	 * @param string $operation_name
	 */
	private function do_license_operation( $operation_name = 'activate' ) {
		if ( !check_admin_referer( 'mail_mint_pro_licensing_nonce', 'mail_mint_pro_licensing_nonce' ) ) {
			return;
		}

		$license_key = isset( $_POST['mail_mint_license_key'] ) ? sanitize_key( trim( $_POST['mail_mint_license_key'] ) ) : '';

		if ( strlen( $license_key ) > 40 ) {
			$license_key     = get_option( 'mail_mint_license_key', '' );
			$decrypt_license = Mint_Pro_Helper::decrypt_key( $_POST['mail_mint_license_key'] );

			if ( $license_key == $decrypt_license ) {
				$license_key = get_option( 'mail_mint_license_key', '' );
			}
		}
		$args = array(
			'woo_sl_action'     => $operation_name,
			'licence_key'       => $license_key,
			'product_unique_id' => MAIL_MINT_PRO_PRODUCT_ID,
			'domain'            => MAIL_MINT_PRO_INSTANCE,
			'license_url'       => MAIL_MINT_PRO_API_URL,
		);

		$request_uri = MAIL_MINT_PRO_API_URL . '?' . http_build_query( $args );
		$data        = wp_remote_get( $request_uri );

		if ( is_wp_error( $data ) || $data['response']['code'] != 200 ) {
			$message  = __( 'An error occurred, please try again.' );
			$base_url = admin_url( 'admin.php?page=mail-mint-licence' );
			$redirect = add_query_arg(
				array(
					'mail_mint_activation_pro' => 'false',
					'message'                  => urlencode( $message ),
				),
				$base_url
			);
			wp_redirect( $redirect );
			exit();
		}

		$data_body = json_decode( $data['body'] );

		foreach ( $data_body as $data ) {
			if ( isset( $data->status ) && $data->status == 'success' ) {
				if ( $data->status_code == 's100' || $data->status_code == 's101' ) {
					$message        = $data->message;
					$license_status = $data->licence_status;
					$license_start  = isset( $data->licence_start ) ? $data->licence_start : '';
					$license_end    = isset( $data->licence_expire ) ? $data->licence_expire : '';

					// save the license
					$licence_data = array(
						'key'        => $license_key,
						'last_check' => time(),
						'start_date' => $license_start,
						'end_date'   => $license_end,
					);
					update_option( 'mail_mint_license_key', $license_key );
					update_option( 'mail_mint_pro_licence_data', $licence_data );
					update_option( 'mail_mint_pro_is_premium', 'yes' );
					update_option( 'mail_mint_pro_license_status', $license_status );
				} elseif ( $data->status_code == 's201' ) {
					$message = $data->message;
					// save the license
					$licence_data = array(
						'key'        => '',
						'last_check' => time(),
						'start_date' => '',
						'end_date'   => '',
					);

					update_option( 'mail_mint_pro_licence_data', $licence_data );
					update_option( 'mail_mint_pro_is_premium', 'no' );
					update_option( 'mail_mint_pro_license_status', 'deactivate' );
				} elseif ( $data->status_code == 'e002' || $data->status_code == 'e104' || $data->status_code == 'e211' ) {
					$message = $data->message;

					// save the license
					$licence_data = array(
						'key'        => '',
						'last_check' => time(),
						'start_date' => '',
						'end_date'   => '',
					);

					update_option( 'mail_mint_pro_licence_data', $licence_data );
					update_option( 'mail_mint_pro_is_premium', 'no' );
					update_option( 'mail_mint_pro_license_status', 'deactivate' );
				}
			} else {
				$message      = $data->message;
				$licence_data = array(
					'key'        => '',
					'last_check' => time(),
					'start_date' => '',
					'end_date'   => '',
				);
				update_option( 'mail_mint_pro_licence_data', $licence_data );
				update_option( 'mail_mint_pro_is_premium', 'no' );
				update_option( 'mail_mint_pro_license_status', 'deactivate' );
			}
		}
		$base_url = admin_url( 'admin.php?page=mail-mint-licence' );
		$redirect = add_query_arg(
			array(
				'mail_mint_activation_pro' => 'true',
				'message'                  => urlencode( $message ),
			),
			$base_url
		);
		wp_redirect( $redirect );
		exit();
	}


	/**
	 * show notice on license activation
	 */
	public function licensing_admin_notices() {
		if ( isset( $_GET['mail_mint_activation_pro'] ) && ! empty( $_GET['message'] ) ) {
			switch ( $_GET['mail_mint_activation_pro'] ) {
				case 'false':
					$message = urldecode( $_GET['message'] );
					?>
					<div class="notice notice-error">
						<p><?php echo $message; ?></p>
					</div>
					<?php
					break;
				case 'true':
					$message = urldecode( $_GET['message'] );
					?>
					<div class="notice notice-success"">
					<p><?php echo $message; ?></p>
					</div>
					<?php
					break;
				default:
					break;
			}
		}
	}

}
