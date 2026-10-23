<?php
/**
 * Happy FAQs → Customer Questions, as a React page.
 *
 * Without Pro this is a short tour of the feature. Happy FAQs Pro (with
 * FFW_PRO_REACT_QUESTIONS) puts its questions manager on the same page.
 * Older Pro versions keep the previous list screen.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_App_Questions class.
 */
class FFW_App_Questions {

    const SLUG      = 'ffw-customer-questions';
    const POST_TYPE = 'ffw_customer_qna';

    /**
     * Hooks.
     */
    public function __construct() {
        add_filter( 'ffw_admin_app_pages', array( $this, 'register' ), 10 );
        add_action( 'admin_menu', array( $this, 'menu' ), 30 );
        add_action( 'load-edit.php', array( $this, 'redirect_list' ) );
        add_filter( 'ffw_admin_nav_items', array( $this, 'nav' ) );
    }

    /**
     * Whether this page handles Customer Questions.
     *
     * @return bool
     */
    public static function active() {
        return ! ffw_is_pro_activated() || defined( 'FFW_PRO_REACT_QUESTIONS' );
    }

    /**
     * Page URL.
     *
     * @return string
     */
    public static function url() {
        return admin_url( 'edit.php?post_type=ffw&page=' . self::SLUG );
    }

    /**
     * Swap the menu link to the list for this page.
     *
     * @return void
     */
    public function menu() {
        if ( ! self::active() ) {
            return;
        }
        remove_submenu_page( 'edit.php?post_type=ffw', 'edit.php?post_type=' . self::POST_TYPE );
        add_submenu_page(
            'edit.php?post_type=ffw',
            __( 'Customer Questions', 'faq-for-woocommerce' ),
            __( 'Customer Questions', 'faq-for-woocommerce' ),
            'manage_options',
            self::SLUG,
            '__return_null'
        );
    }

    /**
     * Open this page instead of the questions list.
     *
     * @return void
     */
    public function redirect_list() {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        if ( ! self::active() || ! isset( $_GET['post_type'] ) || self::POST_TYPE !== $_GET['post_type'] || ! empty( $_GET['ffw_classic'] ) ) {
            return;
        }
        $status = isset( $_GET['post_status'] ) ? sanitize_key( wp_unslash( $_GET['post_status'] ) ) : '';
        // phpcs:enable
        wp_safe_redirect( self::url() . ( $status ? '#/' . $status : '' ) );
        exit;
    }

    /**
     * Header link.
     *
     * @param array $items Nav items.
     * @return array
     */
    public function nav( $items ) {
        if ( ! self::active() ) {
            return $items;
        }
        foreach ( $items as $index => $item ) {
            if ( 'questions' === $item['slug'] ) {
                $items[ $index ]['url']   = self::url();
                $items[ $index ]['pages'] = array( self::SLUG );
            }
        }
        return $items;
    }

    /**
     * The teaser page (Pro replaces it with its manager).
     *
     * @param array $pages Pages.
     * @return array
     */
    public function register( $pages ) {
        if ( ! self::active() || ffw_is_pro_activated() ) {
            return $pages;
        }
        $pages[ self::SLUG ] = array(
            'title'  => __( 'Customer Questions', 'faq-for-woocommerce' ),
            'script' => FFW_PLUGIN_URL . '/assets/admin/js/pages/questions-teaser.js',
            'style'  => FFW_PLUGIN_URL . '/assets/admin/css/pages/questions.css',
            'data'   => static function () {
                return array( 'proUrl' => FFW_PRO_URL );
            },
        );
        return $pages;
    }
}

new FFW_App_Questions();
