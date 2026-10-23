<?php
/**
 * Elementor Happy Custom FAQs widget.
 *
 * @package FAQ_Woocommerce
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_Custom_Faqs_Elementor_Widget class.
 */
class FFW_Custom_Faqs_Elementor_Widget extends \Elementor\Widget_Base {

    /**
     * Widget slug.
     *
     * @return string
     */
    public function get_name() {
        return 'ffw-custom-faqs';
    }

    /**
     * Widget title.
     *
     * @return string
     */
    public function get_title() {
        return esc_html__( 'Happy Custom FAQs', 'faq-for-woocommerce' );
    }

    /**
     * Widget icon.
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-accordion';
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
        return array( 'faq', 'accordion', 'questions', 'help', 'happy', 'custom' );
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
        $this->register_items_controls();
        $this->register_display_controls();
        $this->register_search_controls();
        $this->register_style_box_controls();
        $this->register_style_item_controls();
        $this->register_style_question_controls();
        $this->register_style_icon_controls();
        $this->register_style_answer_controls();
        $this->register_style_search_box_controls();
        $this->register_style_pop_controls();

        ffw_elementor_register_toggle_icon_controls( $this, false );
    }

    /**
     * FAQ repeater.
     *
     * @return void
     */
    protected function register_items_controls() {
        $this->start_controls_section(
            'section_items',
            array(
                'label' => esc_html__( 'FAQs', 'faq-for-woocommerce' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'items_note',
            array(
                'type'            => \Elementor\Controls_Manager::RAW_HTML,
                'raw'             => '<p>' . esc_html__( 'These FAQs stay on this page. They are not added to the FAQ library.', 'faq-for-woocommerce' ) . '</p>',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            )
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'question',
            array(
                'label'       => esc_html__( 'Question', 'faq-for-woocommerce' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'default'     => '',
                'placeholder' => esc_html__( 'Enter a question', 'faq-for-woocommerce' ),
            )
        );

        $repeater->add_control(
            'answer',
            array(
                'label'       => esc_html__( 'Answer', 'faq-for-woocommerce' ),
                'type'        => \Elementor\Controls_Manager::WYSIWYG,
                'default'     => '',
                'placeholder' => esc_html__( 'Write the answer…', 'faq-for-woocommerce' ),
            )
        );

        $this->add_control(
            'faq_items',
            array(
                'label'       => esc_html__( 'Items', 'faq-for-woocommerce' ),
                'type'        => \Elementor\Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => array(
                    array(
                        'question' => '',
                        'answer'   => '',
                    ),
                ),
                'title_field' => '{{{ question }}}',
            )
        );

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
                'description'  => esc_html__( 'Filters this widget as visitors type.', 'faq-for-woocommerce' ),
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
     * Outer accordion box.
     *
     * @return void
     */
    protected function register_style_box_controls() {
        $box = '{{WRAPPER}} #ffw-main-wrapper';

        $this->start_controls_section(
            'section_style_box',
            array(
                'label' => esc_html__( 'Box', 'faq-for-woocommerce' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_responsive_control(
            'box_width',
            array(
                'label'      => esc_html__( 'Width', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( '%', 'px' ),
                'range'      => array(
                    '%'  => array(
                        'min' => 20,
                        'max' => 100,
                    ),
                    'px' => array(
                        'min' => 200,
                        'max' => 1400,
                    ),
                ),
                'selectors'  => array(
                    $box => 'width: {{SIZE}}{{UNIT}} !important; max-width: 100%;',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            array(
                'name'     => 'box_background',
                'selector' => $box,
            )
        );

        $this->add_responsive_control(
            'box_padding',
            array(
                'label'      => esc_html__( 'Padding', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    $box => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name'           => 'box_border',
                'selector'       => $box,
                'fields_options' => $this->important_border_fields(),
            )
        );

        $this->add_responsive_control(
            'box_radius',
            array(
                'label'      => esc_html__( 'Border radius', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    $box => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'box_shadow',
                'selector' => $box,
            )
        );

        $this->end_controls_section();
    }

    /**
     * FAQ item / row.
     *
     * @return void
     */
    protected function register_style_item_controls() {
        $items = $this->sel_items();

        $this->start_controls_section(
            'section_style_item',
            array(
                'label' => esc_html__( 'FAQ item', 'faq-for-woocommerce' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'item_bg_color',
            array(
                'label'     => esc_html__( 'Background', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    $items => 'background-color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'item_open_bg_color',
            array(
                'label'     => esc_html__( 'Open background', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper details[open]' => 'background-color: {{VALUE}} !important;',
                ),
                'condition' => array(
                    'display_mode' => 'accordion',
                ),
            )
        );

        $this->add_responsive_control(
            'item_spacing',
            array(
                'label'      => esc_html__( 'Space between items', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 64,
                    ),
                ),
                'selectors'  => array(
                    $items => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
                ),
            )
        );

        $this->add_responsive_control(
            'item_padding',
            array(
                'label'      => esc_html__( 'Padding', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    $items => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name'           => 'item_border',
                'selector'       => $items,
                'fields_options' => $this->important_border_fields(),
            )
        );

        $this->add_responsive_control(
            'item_radius',
            array(
                'label'      => esc_html__( 'Border radius', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    $items => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important; overflow: hidden;',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'item_shadow',
                'selector' => $items,
            )
        );

        $this->end_controls_section();
    }

    /**
     * Question / header — all layouts.
     *
     * @return void
     */
    protected function register_style_question_controls() {
        $text   = $this->sel_question_text();
        $header = $this->sel_question_header();

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
                'name'           => 'question_typography',
                'selector'       => $text,
                'fields_options' => $this->important_typography_fields(),
            )
        );

        $this->add_responsive_control(
            'question_align',
            array(
                'label'     => esc_html__( 'Alignment', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::CHOOSE,
                'options'   => array(
                    'left'   => array(
                        'title' => esc_html__( 'Left', 'faq-for-woocommerce' ),
                        'icon'  => 'eicon-text-align-left',
                    ),
                    'center' => array(
                        'title' => esc_html__( 'Center', 'faq-for-woocommerce' ),
                        'icon'  => 'eicon-text-align-center',
                    ),
                    'right'  => array(
                        'title' => esc_html__( 'Right', 'faq-for-woocommerce' ),
                        'icon'  => 'eicon-text-align-right',
                    ),
                ),
                'selectors' => array(
                    $header => 'text-align: {{VALUE}} !important;',
                    $text   => 'text-align: {{VALUE}} !important;',
                ),
            )
        );

        $this->start_controls_tabs( 'question_style_tabs' );

        $this->start_controls_tab(
            'question_style_normal',
            array(
                'label' => esc_html__( 'Normal', 'faq-for-woocommerce' ),
            )
        );

        $this->add_control(
            'question_color',
            array(
                'label'     => esc_html__( 'Color', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    $text => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'question_bg_color',
            array(
                'label'     => esc_html__( 'Background', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    $header => 'background-color: {{VALUE}} !important; background: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'question_style_hover',
            array(
                'label' => esc_html__( 'Hover', 'faq-for-woocommerce' ),
            )
        );

        $this->add_control(
            'question_hover_color',
            array(
                'label'     => esc_html__( 'Color', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    $this->sel_question_hover_text() => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'question_hover_bg_color',
            array(
                'label'     => esc_html__( 'Background', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    $this->sel_question_hover_header() => 'background-color: {{VALUE}} !important; background: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'question_style_active',
            array(
                'label' => esc_html__( 'Open', 'faq-for-woocommerce' ),
            )
        );

        $this->add_control(
            'question_active_color',
            array(
                'label'     => esc_html__( 'Color', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    $this->sel_question_active_text() => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'question_active_bg_color',
            array(
                'label'     => esc_html__( 'Background', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    $this->sel_question_active_header() => 'background-color: {{VALUE}} !important; background: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'question_padding',
            array(
                'label'      => esc_html__( 'Padding', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'separator'  => 'before',
                'selectors'  => array(
                    $header => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name'           => 'question_border',
                'selector'       => $header,
                'fields_options' => $this->important_border_fields(),
            )
        );

        $this->add_responsive_control(
            'question_radius',
            array(
                'label'      => esc_html__( 'Border radius', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    $header => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Accordion icons across Classic, Whitish, Trip, Pop, Basic, and Pro.
     *
     * @return void
     */
    protected function register_style_icon_controls() {
        $this->start_controls_section(
            'section_style_icon',
            array(
                'label'     => esc_html__( 'Icon', 'faq-for-woocommerce' ),
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'display_mode' => 'accordion',
                ),
            )
        );

        $this->add_control(
            'icon_color',
            array(
                'label'     => esc_html__( 'Color', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-classic-icon' => 'color: {{VALUE}} !important; border-color: {{VALUE}} !important;',
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-classic-icon:before, {{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-classic-icon:after' => 'background: {{VALUE}} !important;',
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-rotate-icon, {{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .dashicons' => 'color: {{VALUE}} !important;',
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .control-icon' => 'fill: {{VALUE}} !important; color: {{VALUE}} !important;',
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-collapse span.ffw-button:before' => 'border-top-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .ffw-toggle-icon' => '--ffw-icon-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'icon_active_color',
            array(
                'label'     => esc_html__( 'Open color', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-button.ffw-active .ffw-classic-icon' => 'color: {{VALUE}} !important; border-color: {{VALUE}} !important;',
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-button.ffw-active .ffw-classic-icon:before, {{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-button.ffw-active .ffw-classic-icon:after' => 'background: {{VALUE}} !important;',
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper details[open] .control-icon' => 'fill: {{VALUE}} !important;',
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-collapse span.ffw-button.ffw-active:before' => 'border-top-color: {{VALUE}} !important;',
                    '{{WRAPPER}} :is(.ffw-button.ffw-active, .ffw-accordion-active, details[open] > summary, [aria-expanded="true"]) .ffw-toggle-icon' => '--ffw-icon-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'icon_size',
            array(
                'label'      => esc_html__( 'Size', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array(
                    'px' => array(
                        'min' => 8,
                        'max' => 48,
                    ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-classic-icon' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-rotate-icon, {{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .dashicons' => 'font-size: {{SIZE}}{{UNIT}} !important; width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .control-icon' => 'width: {{SIZE}}{{UNIT}} !important; height: {{SIZE}}{{UNIT}} !important;',
                    '{{WRAPPER}} .ffw-toggle-icon' => '--ffw-icon-size: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Answer body — all layouts.
     *
     * @return void
     */
    protected function register_style_answer_controls() {
        $answer = $this->sel_answer();

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
                'name'           => 'answer_typography',
                'selector'       => $answer,
                'fields_options' => $this->important_typography_fields(),
            )
        );

        $this->add_control(
            'answer_color',
            array(
                'label'     => esc_html__( 'Color', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    $answer => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'answer_bg_color',
            array(
                'label'     => esc_html__( 'Background', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    $answer => 'background-color: {{VALUE}} !important; background: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_responsive_control(
            'answer_padding',
            array(
                'label'      => esc_html__( 'Padding', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    $answer => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name'           => 'answer_border',
                'selector'       => $answer,
                'fields_options' => $this->important_border_fields(),
            )
        );

        $this->add_responsive_control(
            'answer_radius',
            array(
                'label'      => esc_html__( 'Border radius', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    $answer => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Inline search field.
     *
     * @return void
     */
    protected function register_style_search_box_controls() {
        $input = '{{WRAPPER}} #ffw-main-wrapper .ffw-inline-search';
        $wrap  = '{{WRAPPER}} #ffw-main-wrapper .ffw-inline-search-wrap';

        $this->start_controls_section(
            'section_style_search',
            array(
                'label'     => esc_html__( 'Search', 'faq-for-woocommerce' ),
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_search' => 'yes',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'search_typography',
                'selector' => $input,
            )
        );

        $this->add_control(
            'search_color',
            array(
                'label'     => esc_html__( 'Text color', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    $input => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'search_placeholder_color',
            array(
                'label'     => esc_html__( 'Placeholder color', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    $input . '::placeholder' => 'color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'search_bg_color',
            array(
                'label'     => esc_html__( 'Background', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    $input => 'background-color: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_responsive_control(
            'search_width',
            array(
                'label'      => esc_html__( 'Width', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( '%', 'px' ),
                'range'      => array(
                    '%'  => array(
                        'min' => 20,
                        'max' => 100,
                    ),
                    'px' => array(
                        'min' => 120,
                        'max' => 800,
                    ),
                ),
                'selectors'  => array(
                    $input => 'max-width: {{SIZE}}{{UNIT}} !important; width: 100%;',
                ),
            )
        );

        $this->add_responsive_control(
            'search_padding',
            array(
                'label'      => esc_html__( 'Padding', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em' ),
                'selectors'  => array(
                    $input => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ),
            )
        );

        $this->add_responsive_control(
            'search_spacing',
            array(
                'label'      => esc_html__( 'Bottom spacing', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 48,
                    ),
                ),
                'selectors'  => array(
                    $wrap => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name'     => 'search_border',
                'selector' => $input,
            )
        );

        $this->add_responsive_control(
            'search_radius',
            array(
                'label'      => esc_html__( 'Border radius', 'faq-for-woocommerce' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    $input => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Pop layout alternating question bars.
     *
     * @return void
     */
    protected function register_style_pop_controls() {
        $this->start_controls_section(
            'section_style_pop',
            array(
                'label'     => esc_html__( 'Pop rows', 'faq-for-woocommerce' ),
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'display_mode' => 'accordion',
                    'layout'       => '4',
                ),
            )
        );

        $this->add_control(
            'pop_odd_bg',
            array(
                'label'     => esc_html__( 'Odd row background', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-collapse:nth-child(odd) > span.ffw-button' => 'background: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'pop_even_bg',
            array(
                'label'     => esc_html__( 'Even row background', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-collapse:nth-child(even) > span.ffw-button' => 'background: {{VALUE}} !important;',
                ),
            )
        );

        $this->add_control(
            'pop_active_bg',
            array(
                'label'     => esc_html__( 'Open row background', 'faq-for-woocommerce' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper .ffw-collapse span.ffw-button.ffw-active' => 'background: {{VALUE}} !important;',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * FAQ item selectors for every layout.
     *
     * @return string
     */
    protected function sel_items() {
        $root = '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper';

        return implode(
            ', ',
            array(
                $root . ' .ffw-accordion-item',
                $root . ' .ffw-faq-list-item',
                $root . ' .ffw-collapse',
                $root . ' details',
                $root . ' .ffw-accordion-border > .ffw-accordion',
                $root . ' .ffw-accordion-outline-border > .ffw-accordion',
            )
        );
    }

    /**
     * Clickable question header (background / padding / border).
     *
     * @return string
     */
    protected function sel_question_header() {
        $root = '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper';

        return implode(
            ', ',
            array(
                $root . ' .ffw-accordion-item > .ffw-button',
                $root . ' .ffw-collapse > span.ffw-button',
                $root . ' .ffw-accordion-heading',
                $root . ' details summary',
                $root . ' .ffw-faq-list-question',
            )
        );
    }

    /**
     * Question text nodes.
     *
     * @return string
     */
    protected function sel_question_text() {
        $root = '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper';

        return implode(
            ', ',
            array(
                $root . ' .ffw-button',
                $root . ' .ffw-question',
                $root . ' .ffw-faq-list-question',
                $root . ' .ffw-accordion-tittle',
                $root . ' .ffw-accordion-heading',
                $root . ' .ffw-accordion-heading span',
                $root . ' details summary',
                $root . ' .ffw-collapse > span',
            )
        );
    }

    /**
     * Hover question text.
     *
     * @return string
     */
    protected function sel_question_hover_text() {
        $root = '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper';

        return implode(
            ', ',
            array(
                $root . ' .ffw-button:hover',
                $root . ' .ffw-button:hover .ffw-question',
                $root . ' .ffw-button:hover span',
                $root . ' .ffw-accordion-heading:hover',
                $root . ' .ffw-accordion-heading:hover span',
                $root . ' details summary:hover',
                $root . ' .ffw-collapse > span:hover',
            )
        );
    }

    /**
     * Hover question header.
     *
     * @return string
     */
    protected function sel_question_hover_header() {
        $root = '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper';

        return implode(
            ', ',
            array(
                $root . ' .ffw-accordion-item > .ffw-button:hover',
                $root . ' .ffw-collapse > span.ffw-button:hover',
                $root . ' .ffw-accordion-heading:hover',
                $root . ' details summary:hover',
            )
        );
    }

    /**
     * Open / active question text.
     *
     * @return string
     */
    protected function sel_question_active_text() {
        $root = '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper';

        return implode(
            ', ',
            array(
                $root . ' .ffw-button.ffw-active',
                $root . ' .ffw-button.ffw-active .ffw-question',
                $root . ' .ffw-button.ffw-active span',
                $root . ' details[open] summary',
                $root . ' .ffw-collapse > span.ffw-active',
            )
        );
    }

    /**
     * Open / active question header.
     *
     * @return string
     */
    protected function sel_question_active_header() {
        $root = '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper';

        return implode(
            ', ',
            array(
                $root . ' .ffw-accordion-item > .ffw-button.ffw-active',
                $root . ' .ffw-collapse > span.ffw-button.ffw-active',
                $root . ' details[open] summary',
            )
        );
    }

    /**
     * Answer selectors for every layout.
     *
     * @return string
     */
    protected function sel_answer() {
        $root = '{{WRAPPER}} #ffw-main-wrapper #ffw-wrapper';

        return implode(
            ', ',
            array(
                $root . ' .ffw-classic-answer',
                $root . ' .ffw-content',
                $root . ' .ffw-faq-list-answer',
                $root . ' .ffw-trip-answer',
                $root . ' .ffw-basic-answer',
                $root . ' .ffw-accordion-body-content',
                $root . ' .ffw-classic-answer p',
                $root . ' .ffw-faq-list-answer p',
                $root . ' .ffw-trip-answer p',
                $root . ' .ffw-accordion-body-content p',
            )
        );
    }

    /**
     * Force typography to beat layout !important font rules.
     *
     * @return array
     */
    protected function important_typography_fields() {
        return array(
            'font_family'     => array(
                'selectors' => array(
                    '{{SELECTOR}}' => 'font-family: "{{VALUE}}" !important;',
                ),
            ),
            'font_size'       => array(
                'selectors' => array(
                    '{{SELECTOR}}' => 'font-size: {{SIZE}}{{UNIT}} !important;',
                ),
            ),
            'font_weight'     => array(
                'selectors' => array(
                    '{{SELECTOR}}' => 'font-weight: {{VALUE}} !important;',
                ),
            ),
            'text_transform'  => array(
                'selectors' => array(
                    '{{SELECTOR}}' => 'text-transform: {{VALUE}} !important;',
                ),
            ),
            'font_style'      => array(
                'selectors' => array(
                    '{{SELECTOR}}' => 'font-style: {{VALUE}} !important;',
                ),
            ),
            'text_decoration' => array(
                'selectors' => array(
                    '{{SELECTOR}}' => 'text-decoration: {{VALUE}} !important;',
                ),
            ),
            'line_height'     => array(
                'selectors' => array(
                    '{{SELECTOR}}' => 'line-height: {{SIZE}}{{UNIT}} !important;',
                ),
            ),
            'letter_spacing'  => array(
                'selectors' => array(
                    '{{SELECTOR}}' => 'letter-spacing: {{SIZE}}{{UNIT}} !important;',
                ),
            ),
        );
    }

    /**
     * Force border controls to beat layout borders.
     *
     * @return array
     */
    protected function important_border_fields() {
        return array(
            'border' => array(
                'selectors' => array(
                    '{{SELECTOR}}' => 'border-style: {{VALUE}} !important;',
                ),
            ),
            'width'  => array(
                'selectors' => array(
                    '{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ),
            ),
            'color'  => array(
                'selectors' => array(
                    '{{SELECTOR}}' => 'border-color: {{VALUE}} !important;',
                ),
            ),
        );
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
        echo ffw_render_custom_faqs(
            array(
                'items'         => isset( $settings['faq_items'] ) ? $settings['faq_items'] : array(),
                'layout'        => $layout,
                'display_mode'  => isset( $settings['display_mode'] ) ? $settings['display_mode'] : 'accordion',
                'show_search'   => isset( $settings['show_search'] ) ? $settings['show_search'] : 'yes',
                'enable_schema' => isset( $settings['enable_schema'] ) ? $settings['enable_schema'] : 'yes',
                'expand_first'  => isset( $settings['expand_first'] ) ? $settings['expand_first'] : '',
                'icon_set'      => isset( $settings['toggle_icon_set'] ) ? $settings['toggle_icon_set'] : '',
                'icon_position' => isset( $settings['toggle_icon_position'] ) ? $settings['toggle_icon_position'] : '',
            )
        );
    }
}
