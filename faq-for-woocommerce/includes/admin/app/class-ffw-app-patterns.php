<?php
/**
 * Happy FAQs → Patterns, as a React page.
 *
 * Same patterns, previews and "Create page" links as the previous gallery.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_App_Patterns class.
 */
class FFW_App_Patterns {

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
        if ( ! class_exists( 'FAQ_Woocommerce_Patterns' ) ) {
            return $pages;
        }
        $pages[ FAQ_Woocommerce_Patterns::PAGE ] = array(
            'title'  => __( 'Patterns', 'faq-for-woocommerce' ),
            'script' => FFW_PLUGIN_URL . '/assets/admin/js/pages/patterns.js',
            'style'  => FFW_PLUGIN_URL . '/assets/admin/css/pages/patterns.css',
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
        $styles = array(
            'cards'   => __( 'Cards', 'faq-for-woocommerce' ),
            'minimal' => __( 'Minimal', 'faq-for-woocommerce' ),
            'soft'    => __( 'Soft', 'faq-for-woocommerce' ),
        );

        $items = array();
        foreach ( FAQ_Woocommerce_Patterns::get_patterns() as $slug => $pattern ) {
            $style = '';
            if ( preg_match( '/is-style-ffw-([a-z]+)/', $pattern['content'], $match ) && isset( $styles[ $match[1] ] ) ) {
                $style = $styles[ $match[1] ];
            }
            $items[] = array(
                'slug'        => $slug,
                'title'       => $pattern['title'],
                'description' => isset( $pattern['description'] ) ? $pattern['description'] : '',
                'group'       => isset( $pattern['group'] ) ? $pattern['group'] : 'general',
                'keywords'    => isset( $pattern['keywords'] ) ? array_values( (array) $pattern['keywords'] ) : array(),
                'style'       => $style,
                'preview'     => FAQ_Woocommerce_Patterns::get_preview_url( $slug ),
                // wp_nonce_url() escapes & for HTML; the page needs the plain URL.
                'create'      => html_entity_decode( FAQ_Woocommerce_Patterns::get_create_url( $slug ), ENT_QUOTES, 'UTF-8' ),
                'content'     => $pattern['content'],
            );
        }

        return array(
            'patterns' => $items,
            'groups'   => FAQ_Woocommerce_Patterns::get_groups(),
        );
    }
}

new FFW_App_Patterns();
