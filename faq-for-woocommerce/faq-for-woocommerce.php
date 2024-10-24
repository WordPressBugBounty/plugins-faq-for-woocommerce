<?php
/**
 * Plugin Name: XPlainer - Product FAQs for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/faq-for-woocommerce/
 * Description: This plugin shows faqs question and answers for woocommerce products with comment, FAQ schema and AI support.
 * Version: 1.7.5
 * Author: Optemiz
 * Author URI: https://optemiz.com/
 * Text Domain: faq-for-woocommerce
 * Domain Path: /i18n/languages/
 *
 * WP Requirement & Test
 * Requires at least: 4.4
 * Tested up to: 6.6
 * Requires PHP: 5.6
 *
 * WC Requirement & Test
 * WC requires at least: 3.2
 * WC tested up to: 9.0
 *
 * @package FAQ_Woocommerce
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'FFW_VERSION' ) ) {
	/**
	 * Plugin Version
	 * @var string
	 * @since 1.0.0
	 */
	define( 'FFW_VERSION', '1.7.5' );
}

if ( ! defined( 'FFW_FILE' ) ) {
    define( 'FFW_FILE', __FILE__ );
}

if ( ! defined( 'FFW_BASENAME' ) ) {
    define( 'FFW_BASENAME', plugin_basename(__FILE__) );
}

if ( ! defined( 'FFW_FILE_DIR' ) ) {
    define( 'FFW_FILE_DIR', dirname( __FILE__ ) );
}

if ( ! defined( 'FFW_PLUGIN_URL' ) ) {
    define( 'FFW_PLUGIN_URL', plugins_url( '', FFW_FILE ) );
}

if ( ! defined( 'FFW_FILTERED_PLUGIN_URL' ) ) {
    define( 'FFW_FILTERED_PLUGIN_URL', apply_filters('ffw_filter_plugin_url', FFW_PLUGIN_URL ) );
}

if ( ! defined( 'FFW_PRO_URL' ) ) {
    define( 'FFW_PRO_URL', "https://optemiz.com/xplainer" );
}

if ( ! defined( 'FFW_FREE_URL' ) ) {
    define( 'FFW_FREE_URL', 'https://wordpress.org/plugins/faq-for-woocommerce/' );
}

if ( ! defined( 'FFW_SITE_URL' ) ) {
    define( 'FFW_SITE_URL', 'https://optemiz.com' );
}

if ( ! defined( 'FFW_REVIEW_URL' ) ) {
    define( 'FFW_REVIEW_URL', 'https://wordpress.org/support/plugin/faq-for-woocommerce/reviews' );
}

if ( ! defined( 'FFW_DOC_URL' ) ) {
    define( 'FFW_DOC_URL', 'https://optemiz.com/docs/faq-for-woocommerce/' );
}

if ( ! defined( 'FFW_REVIEW_REQUEST_URL' ) ) {
    define( 'FFW_REVIEW_REQUEST_URL', 'https://wordpress.org/support/plugin/faq-for-woocommerce/reviews/#new-post' );
}

if ( ! defined( 'FFW_VIDEOS_URL' ) ) {
    define( 'FFW_VIDEOS_URL', 'https://www.youtube.com/watch?v=Dhav-GJY14k&list=PLrEy7YvtgxPhl75Cjd8JeGMeiv_knlPgA' );
}

if ( ! defined( 'FFW_SUPPORT_URL' ) ) {
    define( 'FFW_SUPPORT_URL', apply_filters("ffw_filter_support_url", 'https://optemiz.com/submit-ticket') );
}

if ( ! defined( 'FFW_FACEBOOK_URL' ) ) {
    define( 'FFW_FACEBOOK_URL', 'https://www.facebook.com/Optemiz' );
}

if ( ! defined( 'FFW_LINKEDIN_URL' ) ) {
    define( 'FFW_LINKEDIN_URL', 'https://www.linkedin.com/company/optemiz/' );
}

if ( ! defined( 'FFW_MIN_WC_VERSION' ) ) {
    /**
     * Minimum WooCommerce Version Supported
     *
     * @var string
     * @since 1.0.0
     */
    define( 'FFW_MIN_WC_VERSION', '3.2' );
}


//autoload file.
require_once __DIR__ . '/vendor/autoload.php';

// Include the main FAQ_Woocommerce class.
include_once FFW_FILE_DIR . '/includes/class-faq-woocommerce.php';

/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function appsero_init_tracker_faq_for_woocommerce() {

    $client = new Appsero\Client( 'c989292b-3c3e-454a-a216-cc0df37fb6d9', 'XPlainer – Product FAQ for WooCommerce', __FILE__ );

    // Active insights
    $client->insights()->init();

    $opt_tracker             = new Optemiz\PluginTracker\Tracker();
    $opt_tracker->api_url    = 'https://optemiz.com';
    $opt_tracker->slug       = 'faq-for-woocommerce';
    $opt_tracker->plugin_base_path = 'faq-for-woocommerce/faq-for-woocommerce.php';
    
    $opt_tracker->insights   = new Optemiz\PluginTracker\Insights();
    $opt_tracker->insights->client   = $client;
    $opt_tracker->execute();

}
appsero_init_tracker_faq_for_woocommerce();

/**
 * Returns the main instance of FAQ_Woocommerce.
 *
 * @since  1.0.0
 * @return FAQ_Woocommerce
 */
function FAQ_Woocommerce_init() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
    return FAQ_Woocommerce::instance();
}

// Global for backwards compatibility.
$GLOBALS['faq_woocommerce_init'] = FAQ_Woocommerce_init();


/**
 * HPOS compatability.
 *
 * @since  1.4.14
 * @return void
 */
function ffw_hpos_compatibility() {
    if ( class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }

add_action( 'before_woocommerce_init', 'ffw_hpos_compatibility' );