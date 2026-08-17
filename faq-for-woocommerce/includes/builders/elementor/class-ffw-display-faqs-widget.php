<?php
/**
 * Elementor Display FAQs widget.
 *
 * @package FAQ_Woocommerce
 * @since   1.8.18
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_Display_Faqs_Elementor_Widget class.
 */
class FFW_Display_Faqs_Elementor_Widget extends \Elementor\Widget_Base {

    /**
     * Widget slug.
     *
     * @return string
     */
    public function get_name() {
        return 'ffw-display-faqs';
    }

    /**
     * Widget title.
     *
     * @return string
     */
    public function get_title() {
        return esc_html__( 'Happy Prebuilt FAQs', 'faq-for-woocommerce' );
    }

    /**
     * Widget icon.
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-toggle';
    }

    /**
     * Widget category.
     *
     * @return array
     */
    public function get_categories() {
        return array( 'happy-faq' );
    }

    /**
     * Search keywords.
     *
     * @return array
     */
    public function get_keywords() {
        return array( 'faq', 'accordion', 'questions', 'help', 'happy', 'prebuilt', 'library' );
    }

    /**
     * Docs link.
     *
     * @return string
     */
    public function get_custom_help_url() {
        return FFW_DOC_URL;
    }

    /**
     * Frontend styles.
     *
     * @return array
     */
    public function get_style_depends() {
        ffw_enqueue_display_faq_assets( 0 );

        return array(
            'ffw_public_styles',
            'ffw_classic_styles',
            'ffw_whitish_styles',
            'ffw_trip_styles',
            'ffw_pop_styles',
            'ffw_basic_styles',
            'ffw-display-faqs-style',
        );
    }

    /**
     * Frontend scripts.
     *
     * @return array
     */
    public function get_script_depends() {
        return array(
            'ffw_public_js',
            'ffw_classic_js',
            'ffw_pop_js',
            'ffw_basic_js',
            'ffw-inline-search',
        );
    }

    /**
     * Widget controls.
     *
     * @return void
     */
    protected function register_controls() {
        $this->register_source_controls();
        $this->register_display_controls();
        $this->register_search_controls();
        $this->register_style_controls();
    }

    /**
     * Source tab — first thing users set.
     *
     * @return void
     */
    protected function register_source_controls() {
        $this->start_controls_section(
            'section_source',
            array(
                'label' => esc_html__( 'FAQ source', 'faq-for-woocommerce' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'source',
            array(
                'label'       => esc_html__( 'Show', 'faq-for-woocommerce' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'default'     => 'all',
                'label_block' => true,
                'options'     => ffw_get_builder_source_options( 'assoc' ),
                    'description' => esc_html__( 'Drop the widget to show all FAQs. Change the source only if you need a category, specific questions, a group, or product FAQs.', 'faq-for-woocommerce' ),
            )
        );

        $category_options = ffw_get_builder_faq_category_options( 'assoc' );
        $this->add_control(
            'faq_cat',
            array(
                'label'       => esc_html__( 'FAQ category', 'faq-for-woocommerce' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'options'     => $category_options,
                'label_block' => true,
                'condition'   => array(
                    'source' => 'faq_cat',
                ),
                'description' => empty( $category_options )
                    ? esc_html__( 'No FAQ categories yet. Create one under Happy FAQs → Categories.', 'faq-for-woocommerce' )
                    : esc_html__( 'Only FAQs in this category are shown.', 'faq-for-woocommerce' ),
            )
        );

        $this->add_control(
            'faq_ids',
            array(
                'label'       => esc_html__( 'FAQs', 'faq-for-woocommerce' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'multiple'    => true,
                'options'     => ffw_get_builder_faq_post_options(),
                'label_block' => true,
                'condition'   => array(
                    'source' => 'faq_ids',
                ),
                'description' => esc_html__( 'Search and pick questions. Leave empty to show nothing until you choose some.', 'faq-for-woocommerce' ),
            )
        );

        $group_options = ffw_get_builder_group_options( 'assoc' );
        $sources       = ffw_get_builder_source_options( 'assoc' );
        if ( isset( $sources['group'] ) ) {
            $this->add_control(
                'group_id',
                array(
                    'label'       => esc_html__( 'FAQ Group', 'faq-for-woocommerce' ),
                    'type'        => \Elementor\Controls_Manager::SELECT2,
                    'options'     => $group_options,
                    'label_block' => true,
                    'condition'   => array(
                        'source' => 'group',
                    ),
                    'description' => empty( $group_options )
                        ? esc_html__( 'No groups yet. Create one under Happy FAQs → FAQ Groups.', 'faq-for-woocommerce' )
                        : esc_html__( 'A named collection of FAQs. Create groups under Happy FAQs → FAQ Groups.', 'faq-for-woocommerce' ),
                )
            );
        }

        if ( ffw_is_woocommerce_active() ) {
            $this->add_control(
                'current_product_note',
                array(
                    'type'            => \Elementor\Controls_Manager::RAW_HTML,
                    'raw'             => '<p>' . esc_html__( 'Uses the product of the current page. Preview in Elementor may be empty unless you are editing a product.', 'faq-for-woocommerce' ) . '</p>',
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                    'condition'       => array(
                        'source' => 'current_product',
                    ),
                )
            );

            $this->add_control(
                'product_id',
                array(
                    'label'       => esc_html__( 'Product ID', 'faq-for-woocommerce' ),
                    'type'        => \Elementor\Controls_Manager::NUMBER,
                    'min'         => 1,
                    'condition'   => array(
                        'source' => 'product',
                    ),
                    'description' => esc_html__( 'WooCommerce product ID whose assigned FAQs should appear.', 'faq-for-woocommerce' ),
                )
            );
        }

        $this->end_controls_section();
    }

    /**
     * Display tab.
     *
     * @return void
     */
    protected function register_display_controls() {
        $this->start_controls_section(
            'section_display',
            array(
                'label' => esc_html__( 'Display', 'faq-for-woocommerce' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'display_mode',
            array(
                'label'   => esc_html__( 'Style', 'faq-for-woocommerce' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'default' => 'accordion',
                'options' => array(
                    'accordion' => array(
                        'title' => esc_html__( 'Accordion', 'faq-for-woocommerce' ),
                        'icon'  => 'eicon-toggle',
                    ),
                    'list'      => array(
                        'title' => esc_html__( 'List', 'faq-for-woocommerce' ),
                        'icon'  => 'eicon-editor-list-ul',
                    ),
                ),
                'toggle'  => false,
            )
        );

        $this->add_control(
            'layout',
            array(
                'label'     => esc_html__( 'Layout', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => '0',
                'options'   => ffw_get_builder_layout_options( 'assoc' ),
                'condition' => array(
                    'display_mode' => 'accordion',
                ),
            )
        );

        $this->add_control(
            'expand_first',
            array(
                'label'        => esc_html__( 'Expand first FAQ', 'faq-for-woocommerce' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'faq-for-woocommerce' ),
                'label_off'    => esc_html__( 'No', 'faq-for-woocommerce' ),
                'return_value' => 'yes',
                'default'      => '',
                'condition'    => array(
                    'display_mode' => 'accordion',
                ),
            )
        );

        $this->add_control(
            'limit',
            array(
                'label'       => esc_html__( 'Limit', 'faq-for-woocommerce' ),
                'type'        => \Elementor\Controls_Manager::NUMBER,
                'min'         => 0,
                'max'         => 50,
                'default'     => 0,
                'description' => esc_html__( '0 shows every matching FAQ.', 'faq-for-woocommerce' ),
            )
        );

        $this->add_control(
            'order_by',
            array(
                'label'   => esc_html__( 'Order by', 'faq-for-woocommerce' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'id',
                'options' => array(
                    'id'    => esc_html__( 'ID', 'faq-for-woocommerce' ),
                    'title' => esc_html__( 'Question', 'faq-for-woocommerce' ),
                    'date'  => esc_html__( 'Date', 'faq-for-woocommerce' ),
                ),
            )
        );

        $this->add_control(
            'order',
            array(
                'label'   => esc_html__( 'Order', 'faq-for-woocommerce' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'ASC',
                'options' => array(
                    'ASC'  => esc_html__( 'Ascending', 'faq-for-woocommerce' ),
                    'DESC' => esc_html__( 'Descending', 'faq-for-woocommerce' ),
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Search and schema.
     *
     * @return void
     */
    protected function register_search_controls() {
        $this->start_controls_section(
            'section_search',
            array(
                'label' => esc_html__( 'Search & schema', 'faq-for-woocommerce' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_search',
            array(
                'label'        => esc_html__( 'Show search box', 'faq-for-woocommerce' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'faq-for-woocommerce' ),
                'label_off'    => esc_html__( 'No', 'faq-for-woocommerce' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'Filters this widget as visitors type. No extra shortcode needed.', 'faq-for-woocommerce' ),
            )
        );

        $this->add_control(
            'enable_schema',
            array(
                'label'        => esc_html__( 'Output FAQ schema', 'faq-for-woocommerce' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'faq-for-woocommerce' ),
                'label_off'    => esc_html__( 'No', 'faq-for-woocommerce' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Light style controls that wrap existing markup.
     *
     * @return void
     */
    protected function register_style_controls() {
        $this->start_controls_section(
            'section_style_question',
            array(
                'label' => esc_html__( 'Question', 'faq-for-woocommerce' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'question_typography',
                'selector' => '{{WRAPPER}} .ffw-question, {{WRAPPER}} .ffw-button, {{WRAPPER}} .ffw-faq-list-question, {{WRAPPER}} .ffw-accordion-tittle',
            )
        );

        $this->add_control(
            'question_color',
            array(
                'label'     => esc_html__( 'Color', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .ffw-question, {{WRAPPER}} .ffw-button, {{WRAPPER}} .ffw-faq-list-question, {{WRAPPER}} .ffw-accordion-tittle' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_answer',
            array(
                'label' => esc_html__( 'Answer', 'faq-for-woocommerce' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'answer_typography',
                'selector' => '{{WRAPPER}} .ffw-classic-answer, {{WRAPPER}} .ffw-content, {{WRAPPER}} .ffw-faq-list-answer, {{WRAPPER}} .ffw-trip-answer',
            )
        );

        $this->add_control(
            'answer_color',
            array(
                'label'     => esc_html__( 'Color', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .ffw-classic-answer, {{WRAPPER}} .ffw-content, {{WRAPPER}} .ffw-faq-list-answer, {{WRAPPER}} .ffw-trip-answer' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'item_spacing',
            array(
                'label'      => esc_html__( 'Item spacing', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 48,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .ffw-accordion-item, {{WRAPPER}} .ffw-faq-list-item, {{WRAPPER}} .ffw-collapse' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Frontend and editor render.
     *
     * @return void
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $layout   = isset( $settings['layout'] ) ? absint( $settings['layout'] ) : 0;

        ffw_enqueue_display_faq_assets( $layout );

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo ffw_render_builder_display_faqs( $settings );
    }
}
