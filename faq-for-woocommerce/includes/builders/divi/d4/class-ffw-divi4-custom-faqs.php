<?php
/**
 * Divi 4 Happy Custom FAQs parent module.
 *
 * Slug `ffw_custom_faqs` must stay stable — Divi 5 conversion-outline.json
 * maps it via module.json `d4Shortcode`.
 *
 * @package FAQ_Woocommerce
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_Divi4_Custom_Faqs class.
 */
class FFW_Divi4_Custom_Faqs extends ET_Builder_Module {

    /**
     * Module slug — must match Divi 5 `d4Shortcode`.
     *
     * @var string
     */
    public $slug = 'ffw_custom_faqs';

    /**
     * FAQ Item child slug.
     *
     * @var string
     */
    public $child_slug = 'ffw_custom_faq_item';

    /**
     * Partial VB support: PHP render via AJAX.
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
        $this->name            = esc_html__( 'Happy Custom FAQs', 'faq-for-woocommerce' );
        $this->plural          = esc_html__( 'Happy Custom FAQs', 'faq-for-woocommerce' );
        $this->child_item_text = esc_html__( 'FAQ Item', 'faq-for-woocommerce' );
        $this->icon_path       = plugin_dir_path( __FILE__ ) . 'icon.svg';
        $this->main_css_element = '%%order_class%%';

        $root = '%%order_class%% #ffw-main-wrapper #ffw-wrapper';

        $this->settings_modal_toggles = array(
            'general'  => array(
                'toggles' => array(
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
                        'main'      => $root . ' .ffw-button, ' . $root . ' .ffw-question, ' . $root . ' .ffw-faq-list-question, ' . $root . ' .ffw-accordion-tittle, ' . $root . ' .ffw-accordion-heading, ' . $root . ' details summary',
                        'important' => 'all',
                    ),
                ),
                'body'   => array(
                    'label' => esc_html__( 'Answer', 'faq-for-woocommerce' ),
                    'css'   => array(
                        'main'      => $root . ' .ffw-classic-answer, ' . $root . ' .ffw-content, ' . $root . ' .ffw-faq-list-answer, ' . $root . ' .ffw-trip-answer, ' . $root . ' .ffw-basic-answer, ' . $root . ' .ffw-accordion-body-content',
                        'important' => 'all',
                    ),
                ),
            ),
            'background'     => array(
                'use_background_color' => true,
                'css'                  => array(
                    'main' => '%%order_class%% #ffw-main-wrapper',
                ),
            ),
            'margin_padding' => array(
                'css' => array(
                    'important' => 'all',
                    'main'      => '%%order_class%% #ffw-main-wrapper',
                ),
            ),
            'borders'        => array(
                'default' => array(
                    'css' => array(
                        'main' => array(
                            'border_radii'  => '%%order_class%% #ffw-main-wrapper',
                            'border_styles' => '%%order_class%% #ffw-main-wrapper',
                        ),
                    ),
                ),
                'item'    => array(
                    'label_prefix' => esc_html__( 'Item', 'faq-for-woocommerce' ),
                    'css'          => array(
                        'main'      => array(
                            'border_radii'  => $root . ' .ffw-accordion-item, ' . $root . ' .ffw-faq-list-item, ' . $root . ' .ffw-collapse, ' . $root . ' details',
                            'border_styles' => $root . ' .ffw-accordion-item, ' . $root . ' .ffw-faq-list-item, ' . $root . ' .ffw-collapse, ' . $root . ' details',
                        ),
                        'important' => 'all',
                    ),
                ),
            ),
            'box_shadow'     => array(
                'default' => array(
                    'css' => array(
                        'main' => '%%order_class%% #ffw-main-wrapper',
                    ),
                ),
                'item'    => array(
                    'label' => esc_html__( 'Item box shadow', 'faq-for-woocommerce' ),
                    'css'   => array(
                        'main'      => $root . ' .ffw-accordion-item, ' . $root . ' .ffw-collapse, ' . $root . ' details',
                        'important' => true,
                    ),
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
            'faq_item'     => array(
                'label'    => esc_html__( 'FAQ item', 'faq-for-woocommerce' ),
                'selector' => $root . ' .ffw-accordion-item, ' . $root . ' .ffw-faq-list-item, ' . $root . ' .ffw-collapse, ' . $root . ' details',
            ),
            'faq_question' => array(
                'label'    => esc_html__( 'Question', 'faq-for-woocommerce' ),
                'selector' => $root . ' .ffw-button, ' . $root . ' .ffw-question, ' . $root . ' .ffw-accordion-heading, ' . $root . ' details summary',
            ),
            'faq_answer'   => array(
                'label'    => esc_html__( 'Answer', 'faq-for-woocommerce' ),
                'selector' => $root . ' .ffw-classic-answer, ' . $root . ' .ffw-content, ' . $root . ' .ffw-trip-answer, ' . $root . ' .ffw-basic-answer, ' . $root . ' .ffw-accordion-body-content',
            ),
            'faq_icon'     => array(
                'label'    => esc_html__( 'Icon', 'faq-for-woocommerce' ),
                'selector' => $root . ' .ffw-classic-icon, ' . $root . ' .ffw-rotate-icon, ' . $root . ' .control-icon, ' . $root . ' .dashicons',
            ),
            'faq_search'   => array(
                'label'    => esc_html__( 'Search', 'faq-for-woocommerce' ),
                'selector' => '%%order_class%% #ffw-main-wrapper .ffw-inline-search',
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
     * Module fields.
     *
     * @return array
     */
    public function get_fields() {
        return array(
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
        );
    }

    /**
     * Frontend render. Children run first and collect items.
     *
     * @param array  $attrs       Shortcode attrs.
     * @param string $content     Child output.
     * @param string $render_slug Slug.
     * @return string
     */
    public function render( $attrs, $content = null, $render_slug = '' ) {
        $layout = isset( $this->props['layout'] ) ? absint( $this->props['layout'] ) : 0;
        ffw_enqueue_display_faq_assets( $layout );

        $items = ffw_divi_take_custom_faq_items();

        return ffw_render_custom_faqs(
            array(
                'items'         => $items,
                'layout'        => $layout,
                'display_mode'  => isset( $this->props['display_mode'] ) ? $this->props['display_mode'] : 'accordion',
                'show_search'   => isset( $this->props['show_search'] ) ? $this->props['show_search'] : 'on',
                'enable_schema' => isset( $this->props['enable_schema'] ) ? $this->props['enable_schema'] : 'on',
                'expand_first'  => isset( $this->props['expand_first'] ) ? $this->props['expand_first'] : 'off',
            )
        );
    }
}

new FFW_Divi4_Custom_Faqs();
