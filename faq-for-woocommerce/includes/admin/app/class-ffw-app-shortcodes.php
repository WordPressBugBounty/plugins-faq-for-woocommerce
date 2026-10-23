<?php
/**
 * Happy FAQs → Shortcodes and Templates, as React pages.
 *
 * Shortcodes: a builder for [ffw_template] with a live preview, and the
 * reference list (ffw_admin_shortcodes_list still filters it).
 * Templates: the layout gallery; choosing one saves ffw_layout, the same
 * key the previous screen saved.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_App_Shortcodes class.
 */
class FFW_App_Shortcodes {

    /**
     * Hooks.
     */
    public function __construct() {
        add_filter( 'ffw_admin_app_pages', array( $this, 'register' ) );
        add_action( 'rest_api_init', array( $this, 'routes' ) );
    }

    /**
     * Register the pages.
     *
     * @param array $pages Pages.
     * @return array
     */
    public function register( $pages ) {
        $pages['ffw-shortcodes'] = array(
            'title'  => __( 'Shortcodes', 'faq-for-woocommerce' ),
            'script' => FFW_PLUGIN_URL . '/assets/admin/js/pages/shortcodes.js',
            'style'  => FFW_PLUGIN_URL . '/assets/admin/css/pages/shortcodes.css',
            'data'   => array( $this, 'shortcodes_data' ),
        );
        $pages['ffw-templates'] = array(
            'title'  => __( 'Templates', 'faq-for-woocommerce' ),
            'script' => FFW_PLUGIN_URL . '/assets/admin/js/pages/templates.js',
            'style'  => FFW_PLUGIN_URL . '/assets/admin/css/pages/shortcodes.css',
            'data'   => array( $this, 'templates_data' ),
        );
        return $pages;
    }

    /**
     * REST routes.
     *
     * @return void
     */
    public function routes() {
        register_rest_route(
            FAQ_Woocommerce_Admin_App::NS,
            '/lookup',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'lookup' ),
                'permission_callback' => static function () {
                    return current_user_can( 'edit_posts' );
                },
                'args'                => array(
                    'type'   => array(
                        'type' => 'string',
                        'enum' => array( 'faq', 'product', 'group' ),
                    ),
                    'search' => array( 'type' => 'string' ),
                    'ids'    => array( 'type' => 'string' ),
                ),
            )
        );

        register_rest_route(
            FAQ_Woocommerce_Admin_App::NS,
            '/layout',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'save_layout' ),
                'permission_callback' => static function () {
                    return current_user_can( 'manage_options' );
                },
                'args'                => array(
                    'layout' => array(
                        'type'     => 'integer',
                        'required' => true,
                    ),
                ),
            )
        );
    }

    /* ------------------------------------------------------------------
     * Shared.
     * ------------------------------------------------------------------ */

    /**
     * Layouts: number, name, Pro only.
     *
     * @return array
     */
    public static function layouts() {
        $free  = array( 'Classic', 'Whitish', 'Trip', 'Pop', 'Basic' );
        $names = apply_filters( 'ffw_filter_template_names', $free );
        $pro   = ffw_is_pro_activated();

        $out = array();
        foreach ( array_values( (array) $names ) as $index => $name ) {
            $image = '/assets/admin/images/template-' . $name . '.png';
            $out[] = array(
                'id'          => $index + 1,
                'name'        => $name,
                'locked'      => ! $pro && ! in_array( $name, $free, true ),
                'image'       => file_exists( FFW_FILE_DIR . $image ) ? FFW_PLUGIN_URL . $image : '',
            );
        }
        return $out;
    }

    /**
     * Current layout number.
     *
     * @return int
     */
    protected static function current_layout() {
        $options = get_option( 'ffw_general_settings' );
        return is_array( $options ) && ! empty( $options['ffw_layout'] ) ? (int) $options['ffw_layout'] : 1;
    }

    /**
     * FAQ categories with counts.
     *
     * @return array
     */
    protected static function categories() {
        $terms = get_terms(
            array(
                'taxonomy'   => 'ffw-category',
                'hide_empty' => false,
            )
        );
        $out = array();
        if ( is_array( $terms ) ) {
            foreach ( $terms as $term ) {
                $out[] = array(
                    'id'    => (int) $term->term_id,
                    'name'  => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
                    'count' => (int) $term->count,
                );
            }
        }
        return $out;
    }

    /* ------------------------------------------------------------------
     * Data.
     * ------------------------------------------------------------------ */

    /**
     * Shortcodes page data.
     *
     * @return array
     */
    public function shortcodes_data() {
        $shortcodes = array(
            array(
                'code'        => '[ffw_template]',
                'title'       => esc_html__( 'Display all FAQs', 'faq-for-woocommerce' ),
                'description' => esc_html__( 'Shows every published FAQ using the layout from Settings. Paste this on any page, or select an FAQs page in Settings to add it automatically.', 'faq-for-woocommerce' ),
            ),
            array(
                'code'        => '[ffw_template faq_cat="12"]',
                'title'       => esc_html__( 'Display FAQs by FAQ category', 'faq-for-woocommerce' ),
                'description' => esc_html__( 'Show FAQs from one or more FAQ categories. Copy the category shortcode from the FAQ Categories screen. Example: 12 is a FAQ category ID.', 'faq-for-woocommerce' ),
            ),
            array(
                'code'        => '[ffw_template filter="true"]',
                'title'       => esc_html__( 'Display FAQs with a category filter', 'faq-for-woocommerce' ),
                'description' => esc_html__( 'Adds buttons above the FAQs so visitors can show one FAQ category at a time. Works with any other attribute, for example faq_ids or template. Needs FAQs from at least two categories.', 'faq-for-woocommerce' ),
            ),
            array(
                'code'        => '[ffw_template source="popular"]',
                'title'       => esc_html__( 'Display the most viewed FAQs', 'faq-for-woocommerce' ),
                'description' => esc_html__( 'Shows the 5 FAQs whose answers are opened most, then the most helpful ones. Add limit="10" for more, or faq_cat="12" to stay within one category.', 'faq-for-woocommerce' ),
            ),
            array(
                'code'        => '[ffw_template source="recent"]',
                'title'       => esc_html__( 'Display the newest FAQs', 'faq-for-woocommerce' ),
                'description' => esc_html__( 'Shows the 5 most recently published FAQs. Add order_by="modified" to list recently updated FAQs instead.', 'faq-for-woocommerce' ),
            ),
            array(
                'code'        => '[ffw_template per_page="5"]',
                'title'       => esc_html__( 'Show 5 FAQs, then a "Show more" button', 'faq-for-woocommerce' ),
                'description' => esc_html__( 'Keeps long lists short. Search, the category filter and FAQ schema still include every FAQ. Use limit="5" instead to show only 5 FAQs in total.', 'faq-for-woocommerce' ),
            ),
            array(
                'code'        => '[ffw_template faq_ids="12,15"]',
                'title'       => esc_html__( 'Display selected FAQs', 'faq-for-woocommerce' ),
                'description' => esc_html__( 'Show specific FAQs by ID. Use a comma-separated list. Example: 12 and 15 are FAQ post IDs.', 'faq-for-woocommerce' ),
            ),
            array(
                'code'        => '[ffw_template dynamic_post=true]',
                'title'       => esc_html__( 'Display FAQs on the current product', 'faq-for-woocommerce' ),
                'description' => esc_html__( 'Shows FAQs assigned to the product being viewed. Use this on a product template. WooCommerce must be active.', 'faq-for-woocommerce' ),
            ),
            array(
                'code'        => '[ffw_template id=20]',
                'title'       => esc_html__( 'Display FAQs for a product ID', 'faq-for-woocommerce' ),
                'description' => esc_html__( 'Show FAQs assigned to a specific product anywhere on the site. Example: id=20 is the product ID.', 'faq-for-woocommerce' ),
            ),
            array(
                'code'        => '[ffw_template cat_ids="32,33"]',
                'title'       => esc_html__( 'Display FAQs by FAQ categories (legacy)', 'faq-for-woocommerce' ),
                'description' => esc_html__( 'Same as faq_cat. 32 and 33 are FAQ category IDs. Prefer faq_cat for new pages.', 'faq-for-woocommerce' ),
            ),
        );

        $icons = array();
        foreach ( FAQ_Woocommerce_Icons::choices() as $id => $label ) {
            $icons[] = array(
                'value' => $id,
                'label' => $label,
            );
        }

        return array(
            'reference'  => array_values( (array) apply_filters( 'ffw_admin_shortcodes_list', $shortcodes ) ),
            'categories' => self::categories(),
            'layouts'    => self::layouts(),
            'layout'     => self::current_layout(),
            'icons'      => $icons,
            'woo'        => class_exists( 'WooCommerce' ),
            'groups'     => post_type_exists( 'ffw_group' ),
            'pro'        => ffw_is_pro_activated(),
            'preview'    => FFW_App_Preview::base_url(),
            'manager'    => admin_url( 'edit.php?post_type=ffw&page=ffw-faqs' ),
            'faqCount'   => (int) wp_count_posts( 'ffw' )->publish,
        );
    }

    /**
     * Templates page data.
     *
     * @return array
     */
    public function templates_data() {
        return array(
            'layouts'  => self::layouts(),
            'layout'   => self::current_layout(),
            'pro'      => ffw_is_pro_activated(),
            'preview'  => FFW_App_Preview::base_url(),
            'faqCount' => (int) wp_count_posts( 'ffw' )->publish,
            'settings' => admin_url( 'edit.php?post_type=ffw&page=woocommerce-faq#style' ),
            'manager'  => admin_url( 'edit.php?post_type=ffw&page=ffw-faqs' ),
        );
    }

    /* ------------------------------------------------------------------
     * REST.
     * ------------------------------------------------------------------ */

    /**
     * Search FAQs, products or FAQ Groups by title, or load them by ID.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function lookup( $request ) {
        $types = array(
            'faq'     => 'ffw',
            'product' => 'product',
            'group'   => 'ffw_group',
        );
        $type  = $request->get_param( 'type' );
        $type  = isset( $types[ $type ] ) ? $types[ $type ] : 'ffw';

        if ( ! post_type_exists( $type ) ) {
            return rest_ensure_response( array( 'items' => array() ) );
        }

        $args = array(
            'post_type'      => $type,
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            'no_found_rows'  => true,
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        $ids = array_filter( array_map( 'absint', explode( ',', (string) $request->get_param( 'ids' ) ) ) );
        if ( $ids ) {
            $args['post__in']       = $ids;
            $args['orderby']        = 'post__in';
            $args['posts_per_page'] = count( $ids );
        } elseif ( '' !== (string) $request->get_param( 'search' ) ) {
            $args['s'] = sanitize_text_field( (string) $request->get_param( 'search' ) );
        }

        $items = array();
        foreach ( get_posts( $args ) as $post ) {
            $items[] = array(
                'id'    => (int) $post->ID,
                'title' => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
            );
        }

        return rest_ensure_response( array( 'items' => $items ) );
    }

    /**
     * Save the site-wide layout, like the previous Templates screen.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function save_layout( $request ) {
        $layout = (int) $request->get_param( 'layout' );
        $valid  = null;
        foreach ( self::layouts() as $item ) {
            if ( $item['id'] === $layout ) {
                $valid = $item;
            }
        }
        if ( ! $valid ) {
            return new WP_Error( 'ffw_layout', __( 'That layout does not exist.', 'faq-for-woocommerce' ), array( 'status' => 400 ) );
        }
        if ( $valid['locked'] ) {
            return new WP_Error( 'ffw_layout_pro', __( 'This layout needs Happy FAQs Pro.', 'faq-for-woocommerce' ), array( 'status' => 403 ) );
        }

        $options = get_option( 'ffw_general_settings' );
        $options = is_array( $options ) ? $options : array();
        $options['ffw_layout'] = (string) $layout;
        update_option( 'ffw_general_settings', $options );

        return rest_ensure_response( array( 'layout' => $layout ) );
    }
}

new FFW_App_Shortcodes();
