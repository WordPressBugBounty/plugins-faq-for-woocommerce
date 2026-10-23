<?php
/**
 * Happy FAQs → Help Center Page, as a React page.
 *
 * The builder posts to the same admin-post action as before, so the page
 * is built, saved and opened exactly like the previous screen did it.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_App_Help_Center class.
 */
class FFW_App_Help_Center {

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
        if ( ! class_exists( 'FAQ_Woocommerce_Help_Center' ) ) {
            return $pages;
        }
        $pages[ FAQ_Woocommerce_Help_Center::PAGE ] = array(
            'title'  => __( 'Help Center Page', 'faq-for-woocommerce' ),
            'script' => FFW_PLUGIN_URL . '/assets/admin/js/pages/help-center.js',
            'style'  => FFW_PLUGIN_URL . '/assets/admin/css/pages/help-center.css',
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
        $existing = array();
        $ids      = get_posts(
            array(
                'post_type'      => 'page',
                'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
                'posts_per_page' => 10,
                'fields'         => 'ids',
                'meta_key'       => '_ffw_help_center', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                'orderby'        => 'modified',
                'order'          => 'DESC',
            )
        );
        foreach ( $ids as $id ) {
            $existing[] = array(
                'id'       => (int) $id,
                'title'    => html_entity_decode( get_the_title( $id ), ENT_QUOTES, 'UTF-8' ),
                'status'   => get_post_status( $id ),
                'view'     => (string) get_permalink( $id ),
                'edit'     => (string) get_edit_post_link( $id, 'raw' ),
                'modified' => sprintf(
                    /* translators: %s: time ago */
                    __( '%s ago', 'faq-for-woocommerce' ),
                    human_time_diff( (int) get_post_modified_time( 'U', true, $id ), time() )
                ),
            );
        }

        return array(
            'defaults'   => FAQ_Woocommerce_Help_Center::defaults(),
            'proSearch'  => FAQ_Woocommerce_Help_Center::has_pro_search(),
            'proForm'    => FAQ_Woocommerce_Help_Center::has_pro_form(),
            'categories' => count( FAQ_Woocommerce_Help_Center::categories() ),
            'faqCount'   => (int) wp_count_posts( 'ffw' )->publish,
            'preview'    => FAQ_Woocommerce_Help_Center::preview_url(),
            'action'     => admin_url( 'admin-post.php' ),
            'createAction' => FAQ_Woocommerce_Help_Center::CREATE_ACTION,
            'nonce'      => wp_create_nonce( FAQ_Woocommerce_Help_Center::CREATE_ACTION ),
            'canPublish' => current_user_can( 'publish_pages' ),
            'existing'   => $existing,
            'manager'    => admin_url( 'edit.php?post_type=ffw&page=ffw-faqs' ),
        );
    }
}

new FFW_App_Help_Center();
