<?php
/**
 * Happy FAQs → AI FAQs, as a React page.
 *
 * Uses the same AJAX actions as the previous screen: ffw_ai_search_sources,
 * ffw_ai_generate, ffw_ai_insert and ffw_ai_improve, and Pro's bulk and
 * fill-gaps actions when Pro is active.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_App_AI class.
 */
class FFW_App_AI {

    const SLUG = 'ai-faqs';

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
        if ( ! class_exists( 'FAQ_Woocommerce_AI' ) || ! class_exists( 'FAQ_Woocommerce_AI_FAQs' ) ) {
            return $pages;
        }
        $pages[ self::SLUG ] = array(
            'title'  => __( 'AI FAQs', 'faq-for-woocommerce' ),
            'script' => FFW_PLUGIN_URL . '/assets/admin/js/pages/ai.js',
            'style'  => FFW_PLUGIN_URL . '/assets/admin/css/pages/ai.css',
            'data'   => array( $this, 'data' ),
        );
        return $pages;
    }

    /**
     * Installed languages, with the site language first.
     *
     * @return array
     */
    protected static function languages() {
        if ( ! function_exists( 'wp_get_available_translations' ) ) {
            require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        }
        $names     = function_exists( 'wp_get_available_translations' ) ? wp_get_available_translations() : array();
        $installed = get_available_languages();
        $out       = array(
            array(
                'value' => '',
                'label' => 'English (United States)',
            ),
        );
        foreach ( $installed as $locale ) {
            $out[] = array(
                'value' => $locale,
                'label' => isset( $names[ $locale ]['native_name'] ) ? $names[ $locale ]['native_name'] : $locale,
            );
        }
        return $out;
    }

    /**
     * Boot data.
     *
     * @return array
     */
    public function data() {
        $base   = FAQ_Woocommerce_AI_FAQs::script_data();
        $locale = get_locale();

        $cats  = array();
        $terms = taxonomy_exists( 'ffw-category' ) ? get_terms(
            array(
                'taxonomy'   => 'ffw-category',
                'hide_empty' => false,
            )
        ) : array();
        foreach ( is_array( $terms ) ? $terms : array() as $term ) {
            $cats[] = array(
                'value' => (string) $term->term_id,
                'label' => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
            );
        }

        /**
         * Data for the Fill gaps tab, provided by Happy FAQs Pro.
         *
         * Keys: drafts (int), auto (bool), next (string), last (array),
         * reviewUrl (string), perRequest (int).
         *
         * @since 3.0.0
         * @param array|null $gaps Gaps data, or null when not available.
         */
        $gaps = apply_filters( 'ffw_ai_app_gaps', null );

        return $base + array(
            'enabled'      => FAQ_Woocommerce_AI::is_enabled(),
            'languages'    => self::languages(),
            'language'     => in_array( $locale, get_available_languages(), true ) ? $locale : '',
            'faqCats'      => $cats,
            'gaps'         => $gaps,
            // Older Pro versions draw the Fill gaps tab themselves.
            'gapsLegacy'   => null === $gaps && has_action( 'ffw_ai_gaps_panel' ),
            'classicUrl'   => admin_url( 'edit.php?post_type=ffw&page=ai-faqs&ffw_classic=1' ),
            'aiSettings'   => admin_url( 'edit.php?post_type=ffw&page=woocommerce-faq#ai' ),
            'manager'      => admin_url( 'edit.php?post_type=ffw&page=ffw-faqs' ),
            'proActive'    => ffw_is_pro_activated(),
        );
    }
}

new FFW_App_AI();
