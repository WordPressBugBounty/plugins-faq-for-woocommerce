<?php
/**
 * Happy FAQs → Dashboard, as a React page.
 *
 * Read-only: counts, a setup checklist, top and recent FAQs, and the plan
 * comparison.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_App_Dashboard class.
 */
class FFW_App_Dashboard {

    const SLUG = 'ffw-dashboard';

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
        $pages[ self::SLUG ] = array(
            'title'  => __( 'Dashboard', 'faq-for-woocommerce' ),
            'script' => FFW_PLUGIN_URL . '/assets/admin/js/pages/dashboard.js',
            'style'  => FFW_PLUGIN_URL . '/assets/admin/css/pages/dashboard.css',
            'data'   => array( $this, 'data' ),
        );
        return $pages;
    }

    /**
     * Sum of a numeric post meta over published FAQs.
     *
     * @param string $key Meta key.
     * @return int
     */
    public static function meta_sum( $key ) {
        global $wpdb;
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COALESCE( SUM( CAST( pm.meta_value AS UNSIGNED ) ), 0 ) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND p.post_type = 'ffw' AND p.post_status = 'publish'",
                $key
            )
        );
    }

    /**
     * FAQs for a list.
     *
     * @param array $args Query args.
     * @return array
     */
    public static function faq_list( $args ) {
        $query = new WP_Query(
            array_merge(
                array(
                    'post_type'      => 'ffw',
                    'post_status'    => 'publish',
                    'posts_per_page' => 5,
                    'no_found_rows'  => true,
                ),
                $args
            )
        );
        $items = array();
        foreach ( $query->posts as $post ) {
            $yes     = (int) get_post_meta( $post->ID, '_ffw_helpful_yes', true );
            $no      = (int) get_post_meta( $post->ID, '_ffw_helpful_no', true );
            $items[] = array(
                'id'       => (int) $post->ID,
                'title'    => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
                'views'    => (int) get_post_meta( $post->ID, '_ffw_views', true ),
                'helpful'  => $yes + $no > 0 ? (int) round( 100 * $yes / ( $yes + $no ) ) : null,
                'votes'    => $yes + $no,
                'modified' => sprintf(
                    /* translators: %s: time ago */
                    __( '%s ago', 'faq-for-woocommerce' ),
                    human_time_diff( get_post_modified_time( 'U', true, $post ), time() )
                ),
            );
        }
        return $items;
    }

    /**
     * Boot data.
     *
     * @return array
     */
    public function data() {
        global $wpdb;

        require_once FFW_ABSPATH . '/includes/admin/dashboard-features.php';

        $options = get_option( 'ffw_general_settings' );
        $options = is_array( $options ) ? $options : array();
        $woo     = class_exists( 'WooCommerce' );
        $pro     = ffw_is_pro_activated();

        $faqs       = wp_count_posts( 'ffw' );
        $published  = isset( $faqs->publish ) ? (int) $faqs->publish : 0;
        $drafts     = isset( $faqs->draft ) ? (int) $faqs->draft : 0;
        $categories = (int) wp_count_terms( array( 'taxonomy' => 'ffw-category', 'hide_empty' => false ) );
        $views      = self::meta_sum( '_ffw_views' );
        $yes        = self::meta_sum( '_ffw_helpful_yes' );
        $no         = self::meta_sum( '_ffw_helpful_no' );

        $questions = 0;
        if ( post_type_exists( 'ffw_customer_qna' ) ) {
            $counts    = wp_count_posts( 'ffw_customer_qna' );
            $questions = isset( $counts->pending ) ? (int) $counts->pending : 0;
        }

        $products = 0;
        if ( $woo ) {
            $products = (int) $wpdb->get_var(
                "SELECT COUNT( DISTINCT pm.post_id ) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = 'ffw_product_faq_post_ids' AND pm.meta_value NOT IN ( '', 'a:0:{}' ) AND p.post_type = 'product' AND p.post_status = 'publish'"
            );
        }

        $help_center = (int) $wpdb->get_var(
            "SELECT pm.post_id FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '_ffw_help_center' AND p.post_status IN ( 'publish', 'draft', 'private' ) ORDER BY pm.post_id DESC LIMIT 1"
        );

        $faq_page  = isset( $options['ffw_faq_page'] ) ? (int) $options['ffw_faq_page'] : 0;
        $manager   = admin_url( 'edit.php?post_type=ffw&page=ffw-faqs' );
        $settings  = admin_url( 'edit.php?post_type=ffw&page=woocommerce-faq' );
        $votes_on  = isset( $options['ffw_enable_helpful_votes'] ) && '1' === (string) $options['ffw_enable_helpful_votes'];

        $steps = array(
            array(
                'id'    => 'faq',
                'title' => __( 'Write your first FAQ', 'faq-for-woocommerce' ),
                'text'  => __( 'Questions customers ask you most, answered once.', 'faq-for-woocommerce' ),
                'done'  => $published > 0,
                'url'   => $manager . '#/new',
                'cta'   => __( 'Add FAQ', 'faq-for-woocommerce' ),
            ),
            array(
                'id'    => 'page',
                'title' => __( 'Put your FAQs on a page', 'faq-for-woocommerce' ),
                'text'  => __( 'Build a Help Center page, or pick a FAQ page in Settings.', 'faq-for-woocommerce' ),
                'done'  => $faq_page > 0 || $help_center > 0,
                'url'   => admin_url( 'edit.php?post_type=ffw&page=ffw-help-center' ),
                'cta'   => __( 'Build a page', 'faq-for-woocommerce' ),
            ),
        );
        if ( $woo ) {
            $steps[] = array(
                'id'    => 'product',
                'title' => __( 'Add FAQs to a product', 'faq-for-woocommerce' ),
                'text'  => __( 'Answer product questions right on the product page.', 'faq-for-woocommerce' ),
                'done'  => $products > 0,
                'url'   => admin_url( 'edit.php?post_type=product' ),
                'cta'   => __( 'Open products', 'faq-for-woocommerce' ),
            );
        }
        $steps[] = array(
            'id'    => 'style',
            'title' => __( 'Make it yours', 'faq-for-woocommerce' ),
            'text'  => __( 'Pick a layout, an icon and your colors.', 'faq-for-woocommerce' ),
            'done'  => ! empty( $options['ffw_icon_set'] ) && 'default' !== $options['ffw_icon_set'] || ! empty( $options['ffw_question_text_color'] ) || ( isset( $options['ffw_layout'] ) && '1' !== (string) $options['ffw_layout'] ),
            'url'   => $settings . '#style',
            'cta'   => __( 'Open Style', 'faq-for-woocommerce' ),
        );
        $steps[] = array(
            'id'    => 'votes',
            'title' => __( 'Learn what works', 'faq-for-woocommerce' ),
            'text'  => __( 'Turn on "Was this helpful?" votes to see which answers help.', 'faq-for-woocommerce' ),
            'done'  => $votes_on,
            'url'   => $settings . '#general',
            'cta'   => __( 'Turn on votes', 'faq-for-woocommerce' ),
        );

        $table = array();
        foreach ( ffw_dashboard_feature_table() as $group ) {
            $rows = array();
            foreach ( $group['rows'] as $row ) {
                $rows[] = array(
                    'title' => $row[0],
                    'text'  => $row[1],
                    'free'  => (bool) $row[2],
                );
            }
            $table[] = array(
                'title' => $group['title'],
                'text'  => $group['text'],
                'emoji' => $group['emoji'],
                'rows'  => $rows,
            );
        }

        $user = wp_get_current_user();

        return array(
            'user'        => $user->display_name ? $user->display_name : $user->user_login,
            'pro'         => $pro,
            'woo'         => $woo,
            'stats'       => array(
                'published'  => $published,
                'drafts'     => $drafts,
                'categories' => $categories,
                'views'      => $views,
                'yes'        => $yes,
                'no'         => $no,
                'questions'  => $questions,
                'products'   => $products,
            ),
            'votesOn'     => $votes_on,
            'steps'       => $steps,
            'top'         => $published ? self::faq_list(
                array(
                    'meta_key' => '_ffw_views', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                    'orderby'  => 'meta_value_num',
                    'order'    => 'DESC',
                )
            ) : array(),
            'recent'      => $published || $drafts ? self::faq_list(
                array(
                    'post_status' => array( 'publish', 'draft', 'pending' ),
                    'orderby'     => 'modified',
                    'order'       => 'DESC',
                )
            ) : array(),
            'table'       => $table,
            'whyPro'      => ffw_dashboard_why_pro(),
            'urls'        => array(
                'manager'     => $manager,
                'settings'    => $settings,
                'helpCenter'  => admin_url( 'edit.php?post_type=ffw&page=ffw-help-center' ),
                'patterns'    => admin_url( 'edit.php?post_type=ffw&page=ffw-patterns' ),
                'shortcodes'  => admin_url( 'edit.php?post_type=ffw&page=ffw-shortcodes' ),
                'ai'          => admin_url( 'edit.php?post_type=ffw&page=ai-faqs' ),
                'analytics'   => admin_url( 'edit.php?post_type=ffw&page=ffw-analytics' ),
                'questions'   => admin_url( 'edit.php?post_type=ffw_customer_qna' ),
                'review'      => FFW_REVIEW_URL,
                'videos'      => FFW_VIDEOS_URL,
                'docs'        => FFW_DOC_URL,
                'support'     => FFW_SUPPORT_URL,
                'helpCenterPage' => $help_center ? get_permalink( $help_center ) : '',
            ),
        );
    }
}

new FFW_App_Dashboard();
