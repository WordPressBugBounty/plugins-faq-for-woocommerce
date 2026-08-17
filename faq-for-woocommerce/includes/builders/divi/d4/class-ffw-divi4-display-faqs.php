<?php
/**
 * Divi 4 Display FAQs module.
 *
 * Slug `ffw_display_faqs` must stay stable — Divi 5 conversion-outline.json
 * maps it via module.json `d4Shortcode`.
 *
 * @package FAQ_Woocommerce
 * @since   1.8.18
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_Divi4_Display_Faqs class.
 */
class FFW_Divi4_Display_Faqs extends ET_Builder_Module {

    /**
     * Module slug — must match Divi 5 `d4Shortcode`.
     *
     * @var string
     */
    public $slug = 'ffw_display_faqs';

    /**
     * Partial VB support: PHP render via AJAX, no D4 React app required.
     *
     * @var string
     */
    public $vb_support = 'partial';

    /**
     * Setup.
     *
     * @return void
     */
    public function init() {
        $this->name       = esc_html__( 'Happy Prebuilt FAQs', 'faq-for-woocommerce' );
        $this->plural     = esc_html__( 'Happy Prebuilt FAQs', 'faq-for-woocommerce' );
        $this->icon_path  = plugin_dir_path( __FILE__ ) . 'icon.svg';
        $this->main_css_element = '%%order_class%%';

        $this->settings_modal_toggles = array(
            'general'  => array(
                'toggles' => array(
                    'faq_source'  => esc_html__( 'FAQ source', 'faq-for-woocommerce' ),
                    'faq_display' => esc_html__( 'Display', 'faq-for-woocommerce' ),
                    'faq_options' => esc_html__( 'Search & schema', 'faq-for-woocommerce' ),
                ),
            ),
            'advanced' => array(
                'toggles' => array(
                    'faq_text' => esc_html__( 'FAQ text', 'faq-for-woocommerce' ),
                ),
            ),
        );

        $this->advanced_fields = array(
            'fonts'          => array(
                'header' => array(
                    'label' => esc_html__( 'Question', 'faq-for-woocommerce' ),
                    'css'   => array(
                        'main' => '%%order_class%% .ffw-question, %%order_class%% .ffw-button, %%order_class%% .ffw-faq-list-question',
                    ),
                ),
                'body'   => array(
                    'label' => esc_html__( 'Answer', 'faq-for-woocommerce' ),
                    'css'   => array(
                        'main' => '%%order_class%% .ffw-classic-answer, %%order_class%% .ffw-content, %%order_class%% .ffw-faq-list-answer, %%order_class%% .ffw-trip-answer',
                    ),
                ),
            ),
            'background'     => array(
                'use_background_color' => true,
            ),
            'margin_padding' => array(
                'css' => array(
                    'important' => 'all',
                ),
            ),
            'text'           => false,
            'button'         => false,
        );

        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__( 'Main Element', 'faq-for-woocommerce' ),
                'selector' => '%%order_class%%',
            ),
        );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_preview_assets' ) );
        add_action( 'et_fb_enqueue_assets', array( $this, 'enqueue_preview_assets' ) );
    }

    /**
     * FAQ assets in Divi Visual Builder.
     *
     * @return void
     */
    public function enqueue_preview_assets() {
        ffw_enqueue_display_faq_assets( 0 );
    }

    /**
     * Module fields. Names here are the D4 shortcode attributes converted by
     * conversion-outline.json — do not rename without updating that file.
     *
     * @return array
     */
    public function get_fields() {
        $category_options = array( '' => esc_html__( 'Select a category', 'faq-for-woocommerce' ) ) + ffw_get_builder_faq_category_options( 'assoc' );

        $fields = array(
            'faq_source'    => array(
                'label'           => esc_html__( 'Show', 'faq-for-woocommerce' ),
                'type'            => 'select',
                'option_category' => 'basic_option',
                'options'         => ffw_get_builder_source_options( 'assoc' ),
                'default'         => 'all',
                'description'     => esc_html__( 'Drop the module to show all FAQs. Change the source for a category, selected questions, or product FAQs.', 'faq-for-woocommerce' ),
                'toggle_slug'     => 'faq_source',
            ),
            'faq_cat'       => array(
                'label'           => esc_html__( 'FAQ category', 'faq-for-woocommerce' ),
                'type'            => 'select',
                'option_category' => 'basic_option',
                'options'         => $category_options,
                'default'         => '',
                'description'     => esc_html__( 'Only FAQs in this category are shown.', 'faq-for-woocommerce' ),
                'toggle_slug'     => 'faq_source',
                'show_if'         => array(
                    'faq_source' => 'faq_cat',
                ),
            ),
            'faq_ids'       => array(
                'label'           => esc_html__( 'FAQ IDs', 'faq-for-woocommerce' ),
                'type'            => 'text',
                'option_category' => 'basic_option',
                'default'         => '',
                'description'     => esc_html__( 'Comma-separated FAQ post IDs.', 'faq-for-woocommerce' ),
                'toggle_slug'     => 'faq_source',
                'show_if'         => array(
                    'faq_source' => 'faq_ids',
                ),
            ),
            'group_id'      => array(
                'label'           => esc_html__( 'FAQ Group', 'faq-for-woocommerce' ),
                'type'            => 'select',
                'option_category' => 'basic_option',
                'options'         => array( '' => esc_html__( 'Select a group', 'faq-for-woocommerce' ) ) + ffw_get_builder_group_options( 'assoc' ),
                'default'         => '',
                'description'     => esc_html__( 'A named collection of FAQs from Happy FAQs Pro.', 'faq-for-woocommerce' ),
                'toggle_slug'     => 'faq_source',
                'show_if'         => array(
                    'faq_source' => 'group',
                ),
            ),
            'product_id'    => array(
                'label'           => esc_html__( 'Product ID', 'faq-for-woocommerce' ),
                'type'            => 'text',
                'option_category' => 'basic_option',
                'default'         => '',
                'description'     => esc_html__( 'WooCommerce product ID whose assigned FAQs should appear.', 'faq-for-woocommerce' ),
                'toggle_slug'     => 'faq_source',
                'show_if'         => array(
                    'faq_source' => 'product',
                ),
            ),
            'display_mode'  => array(
                'label'           => esc_html__( 'Display mode', 'faq-for-woocommerce' ),
                'type'            => 'select',
                'option_category' => 'layout',
                'options'         => array(
                    'accordion' => esc_html__( 'Accordion', 'faq-for-woocommerce' ),
                    'list'      => esc_html__( 'List', 'faq-for-woocommerce' ),
                ),
                'default'         => 'accordion',
                'toggle_slug'     => 'faq_display',
            ),
            'layout'        => array(
                'label'           => esc_html__( 'Layout', 'faq-for-woocommerce' ),
                'type'            => 'select',
                'option_category' => 'layout',
                'options'         => ffw_get_builder_layout_options( 'assoc' ),
                'default'         => '0',
                'toggle_slug'     => 'faq_display',
                'show_if'         => array(
                    'display_mode' => 'accordion',
                ),
            ),
            'expand_first'  => array(
                'label'           => esc_html__( 'Expand first FAQ', 'faq-for-woocommerce' ),
                'type'            => 'yes_no_button',
                'option_category' => 'configuration',
                'options'         => array(
                    'off' => esc_html__( 'No', 'faq-for-woocommerce' ),
                    'on'  => esc_html__( 'Yes', 'faq-for-woocommerce' ),
                ),
                'default'         => 'off',
                'toggle_slug'     => 'faq_display',
                'show_if'         => array(
                    'display_mode' => 'accordion',
                ),
            ),
            'faq_limit'     => array(
                'label'           => esc_html__( 'Limit', 'faq-for-woocommerce' ),
                'type'            => 'range',
                'option_category' => 'configuration',
                'default'         => '0',
                'range_settings'  => array(
                    'min'  => 0,
                    'max'  => 50,
                    'step' => 1,
                ),
                'description'     => esc_html__( '0 shows every matching FAQ.', 'faq-for-woocommerce' ),
                'toggle_slug'     => 'faq_display',
            ),
            'order_by'      => array(
                'label'           => esc_html__( 'Order by', 'faq-for-woocommerce' ),
                'type'            => 'select',
                'option_category' => 'configuration',
                'options'         => array(
                    'id'    => esc_html__( 'ID', 'faq-for-woocommerce' ),
                    'title' => esc_html__( 'Question', 'faq-for-woocommerce' ),
                    'date'  => esc_html__( 'Date', 'faq-for-woocommerce' ),
                ),
                'default'         => 'id',
                'toggle_slug'     => 'faq_display',
            ),
            'faq_order'     => array(
                'label'           => esc_html__( 'Order', 'faq-for-woocommerce' ),
                'type'            => 'select',
                'option_category' => 'configuration',
                'options'         => array(
                    'ASC'  => esc_html__( 'Ascending', 'faq-for-woocommerce' ),
                    'DESC' => esc_html__( 'Descending', 'faq-for-woocommerce' ),
                ),
                'default'         => 'ASC',
                'toggle_slug'     => 'faq_display',
            ),
            'show_search'   => array(
                'label'           => esc_html__( 'Show search box', 'faq-for-woocommerce' ),
                'type'            => 'yes_no_button',
                'option_category' => 'configuration',
                'options'         => array(
                    'off' => esc_html__( 'No', 'faq-for-woocommerce' ),
                    'on'  => esc_html__( 'Yes', 'faq-for-woocommerce' ),
                ),
                'default'         => 'on',
                'description'     => esc_html__( 'Filters this module as visitors type.', 'faq-for-woocommerce' ),
                'toggle_slug'     => 'faq_options',
            ),
            'enable_schema' => array(
                'label'           => esc_html__( 'Output FAQ schema', 'faq-for-woocommerce' ),
                'type'            => 'yes_no_button',
                'option_category' => 'configuration',
                'options'         => array(
                    'off' => esc_html__( 'No', 'faq-for-woocommerce' ),
                    'on'  => esc_html__( 'Yes', 'faq-for-woocommerce' ),
                ),
                'default'         => 'on',
                'toggle_slug'     => 'faq_options',
            ),
            '__faqs_html'   => array(
                'type'                => 'computed',
                'computed_callback'   => array( 'FFW_Divi4_Display_Faqs', 'get_faqs_html' ),
                'computed_depends_on' => array(
                    'faq_source',
                    'faq_cat',
                    'faq_ids',
                    'group_id',
                    'product_id',
                    'display_mode',
                    'layout',
                    'expand_first',
                    'faq_limit',
                    'order_by',
                    'faq_order',
                    'show_search',
                    'enable_schema',
                ),
            ),
        );

        if ( ! ffw_is_woocommerce_active() ) {
            unset( $fields['product_id'] );
        }

        return $fields;
    }

    /**
     * Server-side HTML for Visual Builder AJAX preview and frontend.
     *
     * @param array $args Module props.
     * @return string
     */
    public static function get_faqs_html( $args = array() ) {
        return ffw_render_builder_display_faqs( $args );
    }

    /**
     * Frontend render.
     *
     * @param array  $attrs        Shortcode attrs.
     * @param string $content      Content.
     * @param string $render_slug  Slug.
     * @return string
     */
    public function render( $attrs, $content = null, $render_slug = '' ) {
        $layout = isset( $this->props['layout'] ) ? absint( $this->props['layout'] ) : 0;
        ffw_enqueue_display_faq_assets( $layout );

        return self::get_faqs_html( $this->props );
    }
}

new FFW_Divi4_Display_Faqs();
