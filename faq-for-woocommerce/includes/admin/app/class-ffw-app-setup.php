<?php
/**
 * Happy FAQs setup wizard, as a React page.
 *
 * Uses the wizard's AJAX actions (ffw_setup_create_faqs,
 * ffw_setup_install_extras, ffw_setup_finish) and its saved state.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_App_Setup class.
 */
class FFW_App_Setup {

    /**
     * Hooks.
     */
    public function __construct() {
        add_filter( 'ffw_admin_app_pages', array( $this, 'register' ) );
    }

    /**
     * Register the page.
     *
     * @param array $pages Pages.
     * @return array
     */
    public function register( $pages ) {
        if ( ! class_exists( 'FAQ_Woocommerce_Setup_Wizard' ) || ! method_exists( 'FAQ_Woocommerce_Setup_Wizard', 'samples' ) ) {
            return $pages;
        }
        $pages[ FAQ_Woocommerce_Setup_Wizard::PAGE ] = array(
            'title'  => __( 'Happy FAQs Setup', 'faq-for-woocommerce' ),
            'parent' => 'options.php',
            'script' => FFW_PLUGIN_URL . '/assets/admin/js/pages/setup.js',
            'style'  => FFW_PLUGIN_URL . '/assets/admin/css/pages/setup.css',
            'data'   => array( $this, 'data' ),
        );
        return $pages;
    }

    /**
     * Boot data.
     *
     * @return array
     */
    public function data() {
        $state  = FAQ_Woocommerce_Setup_Wizard::get_state();
        $extras = array();
        foreach ( FAQ_Woocommerce_Setup_Wizard::get_extras() as $key => $extra ) {
            $extras[] = array(
                'key'         => $key,
                'title'       => $extra['title'],
                'description' => $extra['description'],
            );
        }
        $woo = ffw_is_woocommerce_active();

        return array(
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( FAQ_Woocommerce_Setup_Wizard::NONCE ),
            'hasWoo'     => $woo,
            'elementor'  => did_action( 'elementor/loaded' ) > 0,
            'goal'       => 'product' === $state['goal'] && $woo ? 'product' : 'general',
            'samples'    => FAQ_Woocommerce_Setup_Wizard::samples(),
            'extras'     => $extras,
            'canInstall' => current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' ),
            'urls'       => array(
                'dashboard' => admin_url( 'edit.php?post_type=ffw&page=ffw-dashboard' ),
                'manager'   => admin_url( 'edit.php?post_type=ffw&page=ffw-faqs' ),
                'patterns'  => admin_url( 'edit.php?post_type=ffw&page=ffw-patterns' ),
                'settings'  => admin_url( 'edit.php?post_type=ffw&page=woocommerce-faq' ),
                'helpCenter' => admin_url( 'edit.php?post_type=ffw&page=ffw-help-center' ),
                'docs'      => FFW_DOC_URL,
            ),
        );
    }
}

new FFW_App_Setup();
