<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://getwpfunnels.com/email-marketing-automation-mail-mint/
 * @since             1.0.0
 * @package           MailMintPro
 *
 * @wordpress-plugin
 * Plugin Name:       Email Marketing Automation - Mail Mint (Pro)
 * Plugin URI:        https://getwpfunnels.com/email-marketing-automation-mail-mint/
 * Description:       Power up 🔥 your funnels using exclusive email sequences, advanced email automation ⚙️, and visual integration with WPFunnels.
 * Version:           1.9.0
 * Author:            WPFunnels Team
 * Author URI:        https://getwpfunnels.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mailmint-pro
 * Domain Path:       /languages
 */
update_option('mail_mint_license_key', 'GPL001122334455AA6677BB8899CC000');
update_option('mail_mint_pro_licence_data', ['key' => 'GPL001122334455AA6677BB8899CC000', 'last_check' => time(), 'start_date' => time(), 'end_date' => '27.06.2052',] );
update_option('mail_mint_pro_is_premium', 'yes' );
update_option('mail_mint_pro_license_status', 'active' );

// If this file is called directly, abort.
use MintMail\Mint\MintMailProDependency;
use MailMintPro\MailMintProUpdater;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 *
 * @since 1.0.0
 */
define( 'MAIL_MINT_PRO_VERSION', '1.9.0' );
define( 'MAILMINT_PRO', 'mailmintpro' );
define( 'MAIL_MINT_PRO_DB_VERSION', '1.0.1' );
define( 'MAIL_MINT_DEV_MODE', false );
define( 'MAIL_MINT_PLUGIN_NAME', 'mail-mint-pro' );
define( 'MAIL_MINT_FILE', __FILE__ );
define( 'MAIL_MINT_FILE_DIR', __DIR__ );
define( 'MAIL_MINT_PRO_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'MAIL_MINT_BASE_NAME', plugin_basename( MAIL_MINT_FILE ) );
define( 'MAIL_MINT_UPLOAD_DIR', WP_CONTENT_DIR . '/uploads/mailmint-pro/' );
define( 'MAIL_MINT_UPLOAD_URL', WP_CONTENT_URL . '/uploads/mailmint-pro/' );
define( 'MAIL_MINT_IMPORT_DIR', WP_CONTENT_DIR . '/uploads/mailmint-pro/import' );
define( 'MAIL_MINT_EXPORT_DIR', WP_CONTENT_DIR . '/uploads/mailmint-pro/exports' );
define( 'MAIL_MINT_DIR_URL', plugins_url( '/', __FILE__ ) );

$protocol = ( !empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] || !empty( $_SERVER['SERVER_PORT'] ) && 443 === $_SERVER['SERVER_PORT'] ) ? 'https://' : 'http://';
define( 'MAIL_MINT_PRO_INSTANCE', str_replace( $protocol, '', get_bloginfo( 'wpurl' ) ) );

// the url where the WooCommerce Software License plugin is being installed.
define( 'MAIL_MINT_PRO_API_URL', 'http://useraccount.getwpfunnels.com/' );

// the Software Unique ID as defined within product admin page.
define( 'MAIL_MINT_PRO_PRODUCT_ID', 'MM' );

/**
 * ABANDONED_CART_SCHEDULER constant.
 *
 * This constant defines the name for the abandoned cart scheduler.
 *
 * @since 1.5.0
 */
if ( ! defined( 'ABANDONED_CART_SCHEDULER' ) ) {
	define( 'ABANDONED_CART_SCHEDULER', 'mint_abandoned_cart_scheduler' );
}

/**
 * CART_CREATION_SCHEDULER constant.
 *
 * This constant defines the name for the abandoned cart scheduler.
 *
 * @since 1.5.0
 */
if ( ! defined( 'CART_CREATION_SCHEDULER' ) ) {
	define( 'CART_CREATION_SCHEDULER', 'mint_cart_creation_scheduler' );
}

/**
 * Define the constant MINT_ABANDONED_CART_GROUP if not already defined.
 *
 * This constant represents the group name for the abandoned cart functionality in the Mint system.
 * It is used to differentiate the abandoned cart functionality from other components or features.
 *
 * @since 1.5.0
 */
if ( !defined( 'MINT_ABANDONED_CART_GROUP' ) ) {
	define( 'MINT_ABANDONED_CART_GROUP', 'mint_abandoned_cart' );
}

if ( !defined( 'MAILMINT_RECURRING_CAMPAIGN_SCHEDULE' ) ) {
	define( 'MAILMINT_RECURRING_CAMPAIGN_SCHEDULE', 'mailmint_recurring_schedule' );
}

if ( !defined( 'MAILMINT_PROCESS_CUSTOMER_WINBACK_AUTOMATION' ) ) {
	define( 'MAILMINT_PROCESS_CUSTOMER_WINBACK_AUTOMATION', 'mailmint_process_customer_winback_automation' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/MailMintProActivator.php
 */
function activate_mail_mint_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/MailMintProActivator.php';
	MailMintProActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/MailMintProDeactivator.php
 */
function deactivate_mail_mint_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/MailMintProDeactivator.php';
	MailMintProDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mail_mint_pro' );
register_deactivation_hook( __FILE__, 'deactivate_mail_mint_pro' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/MailMintPro.php';
require plugin_dir_path( __FILE__ ) . 'includes/MintMailProDependency.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mail_mint_pro() {
	if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$installed_plugins = get_plugins();

	if ( !isset( $installed_plugins['mail-mint/mail-mint.php'] ) || !is_plugin_active( 'mail-mint/mail-mint.php' ) ) {
		MintMailProDependency::deactivate_self( 'mail-mint-pro/mail-mint-pro.php' );
	}
	new MintMailProDependency( 'mail-mint/mail-mint.php', MAIL_MINT_FILE, '1.0.0', 'mailmint-pro' );
	$plugin = new MailMintPro();
	$plugin->run();
}
run_mail_mint_pro();


/**
 * Register pro plugin updater class
 */
function mail_mint_pro_run_updater() {
	new MailMintProUpdater( MAIL_MINT_PRO_API_URL, 'mail-mint-pro', 'mail-mint-pro/mail-mint-pro.php' );
}
add_action( 'after_setup_theme', 'mail_mint_pro_run_updater' );
