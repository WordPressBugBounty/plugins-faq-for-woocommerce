<?php
/**
 * Happy FAQs → Analytics (free), as a React page.
 *
 * Shows the numbers the free plugin records (answer views and helpful
 * votes) and a preview of Pro analytics. Happy FAQs Pro replaces this page
 * with its own when it defines FFW_PRO_ANALYTICS.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_App_Analytics class.
 */
class FFW_App_Analytics {

    const SLUG = 'ffw-analytics';

    /**
     * Hooks.
     */
    public function __construct() {
        add_filter( 'ffw_admin_app_pages', array( $this, 'register' ), 5 );
    }

    /**
     * Register the page, unless Pro draws its own.
     *
     * @param array $pages Pages.
     * @return array
     */
    public function register( $pages ) {
        if ( defined( 'FFW_PRO_ANALYTICS' ) ) {
            return $pages;
        }
        $pages[ self::SLUG ] = array(
            'title'  => __( 'Analytics', 'faq-for-woocommerce' ),
            'script' => FFW_PLUGIN_URL . '/assets/admin/js/pages/analytics-teaser.js',
            'style'  => FFW_PLUGIN_URL . '/assets/admin/css/pages/analytics.css',
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
        $options = get_option( 'ffw_general_settings' );
        $faqs    = wp_count_posts( 'ffw' );

        return array(
            'needsUpdate' => ffw_is_pro_activated(),
            'pluginsUrl'  => admin_url( 'plugins.php' ),
            'settingsUrl' => admin_url( 'edit.php?post_type=ffw&page=woocommerce-faq#general' ),
            'manager'     => admin_url( 'edit.php?post_type=ffw&page=ffw-faqs' ),
            'votesOn'     => is_array( $options ) && isset( $options['ffw_enable_helpful_votes'] ) && '1' === (string) $options['ffw_enable_helpful_votes'],
            'published'   => isset( $faqs->publish ) ? (int) $faqs->publish : 0,
            'views'       => FFW_App_Dashboard::meta_sum( '_ffw_views' ),
            'yes'         => FFW_App_Dashboard::meta_sum( '_ffw_helpful_yes' ),
            'no'          => FFW_App_Dashboard::meta_sum( '_ffw_helpful_no' ),
            'top'         => FFW_App_Dashboard::faq_list(
                array(
                    'posts_per_page' => 8,
                    'meta_key'       => '_ffw_views', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'orderby'        => 'meta_value_num',
                    'order'          => 'DESC',
                )
            ),
        );
    }
}

new FFW_App_Analytics();
