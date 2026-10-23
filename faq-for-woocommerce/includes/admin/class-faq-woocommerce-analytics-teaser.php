<?php
/**
 * Happy FAQs → Analytics preview for the free plugin.
 *
 * Happy FAQs Pro provides the real Analytics screen (and defines
 * FFW_PRO_ANALYTICS). Without it, this shows a blurred sample.
 *
 * @package FAQ_Woocommerce
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FAQ_Woocommerce_Analytics_Teaser class.
 */
class FAQ_Woocommerce_Analytics_Teaser {

    const PAGE = 'ffw-analytics';

    /**
     * Constructor.
     */
    public function __construct() {
        if ( defined( 'FFW_PRO_ANALYTICS' ) ) {
            return;
        }

        add_action( 'admin_menu', array( $this, 'register_page' ), 25 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Happy FAQs → Analytics.
     *
     * @return void
     */
    public function register_page() {
        add_submenu_page(
            'edit.php?post_type=ffw',
            __( 'FAQ Analytics', 'faq-for-woocommerce' ),
            __( 'Analytics', 'faq-for-woocommerce' ),
            'manage_options',
            self::PAGE,
            array( $this, 'render_page' ),
            0
        );
    }

    /**
     * Styles for this screen only.
     *
     * @return void
     */
    public function enqueue_assets() {
        if ( ! isset( $_GET['page'] ) || self::PAGE !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $css = '/assets/admin/css/ffw-analytics-teaser.css';
        wp_enqueue_style( 'ffw-analytics-teaser', FFW_PLUGIN_URL . $css, array(), FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $css ) );
    }

    /**
     * Sample chart line through the given values, in a 600×160 box.
     *
     * @param array $values Values.
     * @param int   $max    Top of the scale.
     * @return string SVG path data.
     */
    public static function sample_path( $values, $max ) {
        $count  = count( $values );
        $points = array();
        foreach ( array_values( $values ) as $i => $value ) {
            $points[] = ( $i ? 'L' : 'M' ) . round( $i / ( $count - 1 ) * 600, 1 ) . ' ' . round( 160 - $value / $max * 150, 1 );
        }

        return implode( ' ', $points );
    }

    /**
     * Render the preview.
     *
     * @return void
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // An older Pro version is active: it just needs an update.
        $needs_update = function_exists( 'ffw_is_pro_activated' ) && ffw_is_pro_activated();

        include FFW_FILE_DIR . '/views/analytics/teaser.php';
    }
}

return new FAQ_Woocommerce_Analytics_Teaser();
