<?php
/**
 * FAQ Woocommerce Public
 *
 * @class    FAQ_Woocommerce_Public
 * @package  FAQ_Woocommerce\Public
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * FAQ_Woocommerce_Public class.
 */
class FAQ_Woocommerce_Public {

    /**
     * Get settings option.
     *
     * @since 1.0.0
     */
    public $options = [];

    /**
     * Instance.
     *
     * The instance will be created if it does not exist yet.
     *
     * @return self The main instance.
     * @since  1.0.0
     */
    public static function instance(): self
    {
        static $instance = null;
        if (is_null($instance) ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        $options = get_option( 'ffw_general_settings' );
		$this->options = ! empty( $options ) ? $options : [];

        // Hooks.
        add_action( 'wp_enqueue_scripts', array( $this, 'ffw_public_styles' ), 999 );
        add_action( 'wp_enqueue_scripts', array( $this, 'ffw_public_scripts' ), true );
        add_action( 'init', array( $this, 'includes' ) );
    }

    /**
     * Include any classes we need within admin.
     */
    public function includes() {
        //include_once dirname( __FILE__ ) . '/faq-woocommerce-admin-functions.php';

        //Comment page
        include_once FFW_FILE_DIR . '/includes/class-ffw-walker-comment.php';

        //Schema page
        include_once dirname(__FILE__) . '/class-faq-woocommerce-schema.php';
    }

    /**
     * Enqueue styles.
     */
    public function ffw_public_styles() {

        // Register template styles.
        wp_register_style( 'ffw_classic_styles', FFW_PLUGIN_URL . '/assets/public/css/ffw-classic.min.css', array(), FFW_PLUGIN_VERSION );
        wp_register_style( 'ffw_whitish_styles', FFW_PLUGIN_URL . '/assets/public/css/ffw-whitish.min.css', array(), FFW_PLUGIN_VERSION );
        wp_register_style( 'ffw_trip_styles', FFW_PLUGIN_URL . '/assets/public/css/ffw-trip.min.css', array(), FFW_PLUGIN_VERSION );
        wp_register_style( 'ffw_pop_styles', FFW_PLUGIN_URL . '/assets/public/css/ffw-pop.min.css', array(), FFW_PLUGIN_VERSION );
        wp_register_style( 'ffw_basic_styles', FFW_PLUGIN_URL . '/assets/public/css/ffw-basic.css', array(), FFW_PLUGIN_VERSION );

        // public style
        wp_register_style( 'ffw_public_styles', FFW_PLUGIN_URL . '/assets/public/css/style.min.css', array(), FFW_PLUGIN_VERSION );

        // do_action("ffw_register_template_js", $layout);

        // Add RTL support for admin styles.
        //wp_style_add_data( 'ffw_admin_menu_styles', 'rtl', 'replace' );

    }


    /**
     * Enqueue scripts.
     */
    public function ffw_public_scripts() {
        global $wp_query, $post;

		//get options.
        $options = $this->options;
		$layout = isset( $options['ffw_layout'] ) ? (int) $options['ffw_layout'] : 1;

        // Register scripts.
        wp_register_script( 'ffw_public_js', FFW_PLUGIN_URL . '/assets/public/js/faq-woocommerce-public.min.js', array( 'jquery' ), FFW_PLUGIN_VERSION, true );
        wp_register_script( 'ffw_classic_js', FFW_PLUGIN_URL . '/assets/public/js/ffw-classic.min.js', array( 'jquery' ), FFW_PLUGIN_VERSION, true );
        wp_register_script( 'ffw_pop_js', FFW_PLUGIN_URL . '/assets/public/js/ffw-pop.min.js', array( 'jquery' ), FFW_PLUGIN_VERSION, true );
        wp_register_script( 'ffw_basic_js', FFW_PLUGIN_URL . '/assets/public/js/ffw-basic.js', array( 'jquery' ), FFW_PLUGIN_VERSION, true );

		// enqueue template JS files.
        wp_enqueue_script( 'ffw_public_js' );
        wp_localize_script( 'ffw_public_js', 'ffw_public_js', [
            'ajaxurl'               => admin_url( 'admin-ajax.php' ),
            'nonce'                 => wp_create_nonce( 'ffw_public_js' ),
        ] );

		if ( 4 === $layout ) {
			wp_enqueue_script( 'ffw_pop_js' );
		}elseif ( 5 === $layout ) {
			wp_enqueue_script( 'ffw_basic_js' );
		}else{
            wp_enqueue_script( 'ffw_classic_js' );
        }

        do_action("ffw_enqueue_template_js", $layout);
    }

}

return new FAQ_Woocommerce_Public();
