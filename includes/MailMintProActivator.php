<?php
/**
 * Fired during plugin activation
 *
 * @link       https://https://coderex.co
 * @since      1.0.0
 *
 * @package    MailMintPro
 * @subpackage MailMintPro/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    MailMintPro
 * @subpackage MailMintPro/includes
 * @author     Code Rex Engineering Team <support@coderex.co>
 */
class MailMintProActivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$is_free_active = apply_filters( 'mail_mint_free_active', false );
		if ( !$is_free_active ) {
			return;
		}
		if ( self::is_new_install() ) {
			self::install();
		}
		self::update_mint_version();
	}


	/**
	 * Install MailMint pro.
	 *
	 * @return void
	 */
	public static function install() {
		if ( self::requires_install() ) {
			require_once MAIL_MINT_PRO_DIR_PATH . 'app/Database/Upgrade.php';
			$upgrade = new \MailMintPro\Mint\DataBase\ProUpgrade();
			$upgrade->maybe_upgrade();
		}
	}


	/**
	 * Require Install.
	 *
	 * @return bool
	 */
	public static function requires_install() {
		return is_null( get_option( 'mail_mint_pro_version', null ) );
	}

	/**
	 * Flash Version.
	 *
	 * @return void
	 */
	public static function flush_versions() {
		wp_cache_delete( 'mail_mint_pro_db_version' );
		wp_cache_delete( 'mail_mint_pro_version' );
	}



	/**
	 * Check if new install or not
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public static function is_new_install() {
		return is_null( get_option( 'mail_mint_pro_version', null ) );
	}


	/**
	 * Update MailMint versions
	 *
	 * @since 1.0.0
	 */
	public static function update_mint_version() {
		if ( defined( 'MAIL_MINT_PRO_VERSION' ) ) {
			update_option( 'mail_mint_pro_version', MAIL_MINT_PRO_VERSION, false );
		}
	}
}
