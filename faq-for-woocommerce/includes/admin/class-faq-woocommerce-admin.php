<?php
/**
 * FAQ Woocommerce Admin
 *
 * @class    FAQ_Woocommerce_Admin
 * @package  FAQ_Woocommerce\Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * FAQ_Woocommerce_Admin class.
 */
class FAQ_Woocommerce_Admin {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'includes' ) );
        add_action( 'init', array( $this, 'shutdown' ));
        add_filter( 'plugin_action_links_' . FFW_BASENAME,  array( $this, 'ffw_plugin_action_links' ) );

        add_filter( 'admin_footer', array( $this, 'admin_styles' ), 1 );
        //add_filter( 'update_footer', array( $this, 'admin_footer_version'), '', 9999 );
    }

    /**
     * Include any classes we need within admin.
     */
    public function includes() {
        include_once dirname( __FILE__ ) . '/faq-woocommerce-admin-functions.php';
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-admin-menus.php';
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-dashboard.php';
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-admin-notices.php';
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-admin-assets.php';
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-onboarding.php';
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-setup-wizard.php';
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-analytics-teaser.php';
        include_once dirname( __FILE__ ) . '/class-ffw-metaboxes.php';

        //Setting page
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-settings.php';

        //AI FAQs file.
        include_once dirname( __FILE__ ) . '/class-faq-woocommerce-ai-faqs.php';

        //Suggest Plugin file.
        include_once dirname( __FILE__ ) . '/class-ffw-suggest-plugins.php';
    }

    /**
     * After all files loaded
     */
    public function shutdown() {
        $options = get_option( 'ffw_general_settings' );
        $options = ! empty( $options ) ? $options : [];
        $ffw_counter = isset( $options['ffw_hide_faq_number_for_product'] ) ? $options['ffw_hide_faq_number_for_product'] : "1";

        //remove filter to hide faq count column from product list table
        if( isset($ffw_counter) & "2" === $ffw_counter ) {
            remove_filter( 'manage_product_posts_columns', 'ffw_set_custom_faq_count_column' );
        }

    }

    /**
     * Admin custom styles.
     *
     * @since  1.7.6
     * @return string
     */
    public function admin_styles() {
        ?>
        <style>
            .menu-icon-ffw .wp-menu-image:before {
                color: #fad54d !important;
            }
        </style>
        <?php
    }


    /**
     * Filter plugin action links
     *
     * @since  1.3.16
     * @param  array  $links List of existing plugin action links.
     * @return array         List of modified plugin action links
     */
    public function ffw_plugin_action_links($links) {
        $links = array_merge( array(
            '<a href="' . esc_url( 'https://happydevs.net/docs/faq-for-woocommerce/' ) . '">' . esc_html__( 'Documentation', 'faq-for-woocommerce' ) . '</a>'
        ), $links );

        return $links;
    }

    public function admin_footer_version() {

    	/* translators: %s: plugin version */
    	$footer_version = sprintf( esc_html__( 'Version: %s', 'faq-for-woocommerce' ), esc_html( FFW_VERSION ) );

    	echo '<span class="ffw-admin-footer-version">' . $footer_version . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.

    	return;
    }
}

return new FAQ_Woocommerce_Admin();
