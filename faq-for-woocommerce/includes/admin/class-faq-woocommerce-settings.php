<?php
/**
 * FAQ Woocommerce Admin Settings
 *
 * @class    FAQ_Woocommerce_Settings
 * @package  FAQ_Woocommerce\Admin\Setting
 * @version  1.0.0
 *
 * @link    https://wpfeel.net/
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * FAQ_Woocommerce_Settings class.
 */
class FAQ_Woocommerce_Settings {
    /* Saved options */
    public $options;


    /**
     * The single instance of the class.
     *
     * @var FAQ_Woocommerce_Settings
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main FAQ_Woocommerce_Settings Instance.
     *
     * Ensures only one instance of FAQ_Woocommerce_Settings is loaded or can be loaded.
     *
     * @since 1.4.2
     */
    public static function instance() {
        if ( is_null(self::$_instance) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'ffw_settings_init' ) );

        // Get registered option
        $this->options = get_option( 'ffw_general_settings' );

    }

    /**
     * Get Setting Page Options
     *
     * @return mixed|null
     */
    function ffw_get_setting_options() {
        $option_arr = [
            'ffw_settings' => [
                'ffw_general_setting' => [
                    'label' => '',
                    'callback' => '',
                    'fields' => [
                        [
                            'id' => 'ffw_tab_label',
                            'label' => esc_html__( 'Tab Label', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_tab_label',
                        ],
                        [
                            'id' => 'ffw_display_location',
                            'label' => esc_html__( 'Display Location (Single Product Page)', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_display_location',
                        ],
                        [
                            'id' => 'ffw_enable_archive_pages_faqs',
                            'label' => esc_html__( 'Enable FAQs in Archive', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_enable_archive_pages_faqs',
                        ],
                        [
                            'id' => 'ffw_display_location_archive',
                            'label' => esc_html__( 'Display Location (Archive Page)', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_display_location_archive',
                        ],
                        [
                            'id' => 'ffw_enable_shop_page_faqs',
                            'label' => esc_html__( 'Enable FAQs in Shop', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_enable_shop_page_faqs',
                        ],
                        [
                            'id' => 'ffw_display_location_shop',
                            'label' => esc_html__( 'Display Location (Shop Page)', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_display_location_shop',
                        ],
                        [
                            'id' => 'ffw_enable_cart_page_faqs',
                            'label' => esc_html__( 'Enable FAQs in Cart', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_enable_cart_page_faqs',
                        ],
                        [
                            'id' => 'ffw_display_location_cart',
                            'label' => esc_html__( 'Display Location (Cart Page)', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_display_location_cart',
                        ],
                        [
                            'id' => 'ffw_enable_checkout_page_faqs',
                            'label' => esc_html__( 'Enable FAQs in Checkout', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_enable_checkout_page_faqs',
                        ],
                        [
                            'id' => 'ffw_display_location_checkout',
                            'label' => esc_html__( 'Display Location (Checkout Page)', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_display_location_checkout',
                        ],
                        // [
                        //     'id' => 'ffw_display_condition',
                        //     'label' => esc_html__( 'Display Condition', 'faq-for-woocommerce' ),
                        //     'region' => 'free',
                        //     'callback' => 'ffw_display_condition',
                        // ],
                        [
                            'id' => 'ffw_tab_priority',
                            'label' => esc_html__( 'Tab Priority', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_tab_priority',
                        ],
                        [
                            'id' => 'ffw_editor',
                            'label' => esc_html__( 'Editor', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_editor',
                        ],
                        [
                            'id' => 'ffw_enable_global_faqs',
                            'label' => esc_html__( 'Enable Global FAQs', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_enable_global_faqs',
                        ],
                        [
                            'id' => 'ffw_global_faqs_appearance',
                            'label' => esc_html__( 'Global FAQs Appearance', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_global_faqs_appearance',
                        ],
                        [
                            'id' => 'ffw_post_index',
                            'label' => esc_html__( 'FAQ Post Behaviour', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_post_index',
                        ],
                        [
                            'id' => 'ffw_enable_dynamic_attributes',
                            'label' => esc_html__( 'Enable Dynamic Attributes', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_enable_dynamic_attributes',
                        ],
                        [
                            'id' => 'ffw_display_all_faq_answers',
                            'label' => esc_html__( 'Display All Answers', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_display_all_faq_answers',
                        ],
                        [
                            'id' => 'ffw_expand_collapse_all',
                            'label' => esc_html__( 'Expand/Collapse All', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_expand_collapse_all',
                        ],
                        [
                            'id' => 'ffw_enable_rtl',
                            'label' => esc_html__( 'Enable RTL', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_enable_rtl',
                        ],
                        [
                            'id' => 'ffw_expand_collapse_label',
                            'label' => esc_html__( 'Expand/Collapse All Label', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_expand_collapse_label',
                        ],
                        [
                            'id' => 'ffw_set_role',
                            'label' => esc_html__( 'Set Access Role', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_set_role',
                        ],
                        [
                            'id' => 'ffw_enable_search_box',
                            'label' => esc_html__( 'Enable Search Box', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_enable_search_box',
                        ],
                        [
                            'id' => 'ffw_enable_multi_column_support',
                            'label' => esc_html__( 'Enable Multi Column', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_enable_multi_column_support',
                        ],
                        [
                            'id' => 'ffw_search_box_position',
                            'label' => esc_html__( 'Search Box Position', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_search_box_position',
                        ],
                        [
                            'id' => 'ffw_faq_counter_in_front',
                            'label' => esc_html__( 'FAQ Counter', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_faq_counter_in_front',
                        ],
                        [
                            'id' => 'ffw_hide_faq_number_for_product',
                            'label' => esc_html__( 'FAQ Counter in Product List', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_hide_faq_number_for_product',
                        ],
                        [
                            'id' => 'ffw_hide_general_shortcode_preview',
                            'label' => esc_html__( 'Hide General Shortcode Preview', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_hide_general_shortcode_preview',
                        ],
                        [
                            'id' => 'ffw_hide_dynamic_shortcode_preview',
                            'label' => esc_html__( 'Hide Dynamic Shortcode Preview in Product Page', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_hide_dynamic_shortcode_preview',
                        ],
                        [
                            'id' => 'ffw_before_faq',
                            'label' => esc_html__( 'Before FAQ', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_before_faq_render',
                        ],
                        [
                            'id' => 'ffw_after_faq',
                            'label' => esc_html__( 'After FAQ', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_after_faq_render',
                        ],
                    ]
                ]
            ],
            'ffw_style_settings' => [
                'ffw_general_setting' => [
                    'label' => '',
                    'callback' => '',
                    'fields' => [
                        [
                            'id' => 'ffw_width',
                            'label' => esc_html__( 'Width (%)', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_width_field',
                        ],
                        [
                            'id' => 'ffw_question_text_color',
                            'label' => esc_html__( 'Question Text Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_question_text_color_field',
                        ],
                        [
                            'id' => 'ffw_question_bg_color',
                            'label' => esc_html__( 'Question Background Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_question_bg_color_field',
                        ],
                        [
                            'id' => 'ffw_question_bg_secondary_color',
                            'label' => esc_html__( 'Question Background Secondary Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_question_bg_secondary_color_field',
                        ],
                        [
                            'id' => 'ffw_question_border_color',
                            'label' => esc_html__( 'Question Border Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_question_border_color_field',
                        ],
                        [
                            'id' => 'ffw_question_font_size',
                            'label' => esc_html__( 'Question Font Size', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_question_font_size_field',
                        ],
                        [
                            'id' => 'ffw_answer_bg_color',
                            'label' => esc_html__( 'Answer Background Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_answer_bg_color_field',
                        ],
                        [
                            'id' => 'ffw_answer_text_color',
                            'label' => esc_html__( 'Answer Text Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_answer_text_color_field',
                        ],
                        [
                            'id' => 'ffw_answer_border_color',
                            'label' => esc_html__( 'Answer Border Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_answer_border_color_field',
                        ],
                        [
                            'id' => 'ffw_custom_css',
                            'label' => esc_html__( 'Custom CSS', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_custom_css_field',
                        ]
                    ]
                ]
            ],
            'ffw_schema_settings' => [
                'ffw_general_setting' => [
                    'label' => '',
                    'callback' => '',
                    'fields' => [
                        [
                            'id' => 'ffw_disable_schema',
                            'label' => esc_html__( 'Enable/Disable Schema', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_disable_schema'
                        ],
                        [
                            'id' => 'ffw_schema_description_type',
                            'label' => esc_html__( 'Schema Description Type', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_schema_description_type'
                        ]
                    ]
                ]
            ],
            'ffw_comment_settings' => [
                'ffw_comment_general_setting' => [
                    'label' => '',
                    'callback' => 'ffw_comment_general_setting_cb',
                    'fields' => [
                        [
                            'id' => 'ffw_comments_on',
                            'label' => esc_html__( 'Enable Comment', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_on_cb'
                        ],
                        [
                            'id' => 'ffw_comments_ordering',
                            'label' => esc_html__( 'Sort Comment Ordering', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_ordering_cb'
                        ],
                        [
                            'id' => 'ffw_comments_avatar',
                            'label' => esc_html__( 'Comment Avatar', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_avatar_cb'
                        ],
                        [
                            'id' => 'ffw_comments_section_title',
                            'label' => esc_html__( 'Comment Section Title', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_section_title_cb'
                        ],
                        [
                            'id' => 'ffw_comments_form_title',
                            'label' => esc_html__( 'Comment Form Title', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_form_title_cb'
                        ],
                        [
                            'id' => 'ffw_comments_reply_form_title',
                            'label' => esc_html__( 'Comment Reply Form Title', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_reply_form_title_cb'
                        ],
                        [
                            'id' => 'ffw_comments_reply_button_text',
                            'label' => esc_html__( 'Comment Reply Button Text', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_reply_button_text_cb'
                        ],
                        [
                            'id' => 'ffw_comments_submit_button_text',
                            'label' => esc_html__( 'Comment Submit Button Text', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_submit_button_text_cb'
                        ],
                    ]
                ],
                'ffw_comment_style_setting' => [
                    'label' => '',
                    'callback' => 'ffw_comment_style_setting_cb',
                    'fields' => [
                        [
                            'id' => 'ffw_comments_section_title_color',
                            'label' => esc_html__( 'Form Section Title Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_section_title_color_cb'
                        ],
                        [
                            'id' => 'ffw_comments_form_section_title_font_size',
                            'label' => esc_html__( 'Form Section Title Font Size', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_form_section_title_font_size_cb'
                        ],
                        [
                            'id' => 'ffw_comments_avatar_style',
                            'label' => esc_html__( 'Avatar Style', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_avatar_style_cb'
                        ],
                        [
                            'id' => 'ffw_comments_date_time_font_size',
                            'label' => esc_html__( 'Comment Date Font Size', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_date_time_font_size_cb'
                        ],
                        [
                            'id' => 'ffw_comments_date_time_color',
                            'label' => esc_html__( 'Comment Date Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_date_time_color_cb'
                        ],
                        [
                            'id' => 'ffw_comments_content_font_size',
                            'label' => esc_html__( 'Comment Content Font Size', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_content_font_size_cb'
                        ],
                        [
                            'id' => 'ffw_comments_content_color',
                            'label' => esc_html__( 'Comment Content Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_content_color_cb'
                        ],
                        [
                            'id' => 'ffw_comments_author_name_font_size',
                            'label' => esc_html__( 'Author Name Font Size', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_author_name_font_size_cb'
                        ],
                        [
                            'id' => 'ffw_comments_author_name_font_size',
                            'label' => esc_html__( 'Author Name Font Size', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_author_name_font_size_cb'
                        ],
                        [
                            'id' => 'ffw_comments_author_name_color',
                            'label' => esc_html__( 'Author Name Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_author_name_color_cb'
                        ],
                        [
                            'id' => 'ffw_comments_reply_button_font_size',
                            'label' => esc_html__( 'Reply Button Font Size', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_reply_button_font_size_cb'
                        ],
                        [
                            'id' => 'ffw_comments_reply_button_text_color',
                            'label' => esc_html__( 'Reply Button Text Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_reply_button_text_color_cb'
                        ],
                        [
                            'id' => 'ffw_comments_submit_button_font_size',
                            'label' => esc_html__( 'Submit Button Size', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_submit_button_font_size_cb'
                        ],
                        [
                            'id' => 'ffw_comments_submit_button_text_color',
                            'label' => esc_html__( 'Submit Button Text Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_submit_button_text_color_cb'
                        ],
                        [
                            'id' => 'ffw_comments_submit_button_bg_color',
                            'label' => esc_html__( 'Submit Button Background', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_submit_button_bg_color_cb'
                        ],
                        [
                            'id' => 'ffw_comments_form_border_color',
                            'label' => esc_html__( 'Form Border Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_form_border_color_cb'
                        ],
                        [
                            'id' => 'ffw_comments_form_title_font_size',
                            'label' => esc_html__( 'Form Title Font Size', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_form_title_font_size_cb'
                        ],
                        [
                            'id' => 'ffw_comments_form_title_color',
                            'label' => esc_html__( 'Form Title Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_form_title_color_cb'
                        ],
                        [
                            'id' => 'ffw_comments_reply_form_title_font_size',
                            'label' => esc_html__( 'Reply Form Title Font Size', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_reply_form_title_font_size_cb'
                        ],
                        [
                            'id' => 'ffw_comments_reply_form_title_color',
                            'label' => esc_html__( 'Reply Form Title Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_reply_form_title_color_cb'
                        ],
                        [
                            'id' => 'ffw_comments_reply_form_submit_button_text',
                            'label' => esc_html__( 'Reply Form Submit Button Text', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_reply_form_submit_button_text_cb'
                        ],
                        [
                            'id' => 'ffw_comments_reply_form_submit_button_font_size',
                            'label' => esc_html__( 'Reply Form Submit Button Font Size', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_reply_form_submit_button_font_size_cb'
                        ],
                        [
                            'id' => 'ffw_comments_reply_form_submit_button_text_color',
                            'label' => esc_html__( 'Reply Form Submit Button Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_reply_form_submit_button_text_color_cb'
                        ],
                        [
                            'id' => 'ffw_comments_reply_form_submit_button_bg_color',
                            'label' => esc_html__( 'Reply Form Submit Button Background', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_reply_form_submit_button_bg_color_cb'
                        ],
                        [
                            'id' => 'ffw_comments_reply_form_border_color',
                            'label' => esc_html__( 'Reply Form Border Color', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_comments_reply_form_border_color_cb'
                        ],
                    ]
                ],
            ],
            'ffw_ai_faqs_settings' => [
                'ffw_general_setting' => [
                    'label' => '',
                    'callback' => '',
                    'fields' => [
                        [
                            'id' => 'ffw_ai_faqs_models',
                            'label' => esc_html__( 'AI Model', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_ai_faqs_models'
                        ],
                        [
                            'id' => 'ffw_ai_faqs_api_key',
                            'label' => esc_html__( 'API Key', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_ai_faqs_api_key'
                        ],
                        [
                            'id' => 'ffw_ai_faqs_max_token',
                            'label' => esc_html__( 'Set Max Tokens', 'faq-for-woocommerce' ),
                            'region' => 'free',
                            'callback' => 'ffw_ai_faqs_max_token'
                        ]
                    ]
                ]
            ],
        ];

        return apply_filters("ffw_filter_setting_options", $option_arr, $this);
    }

	/**
	 * Settings Init.
	 */
    public function ffw_settings_init() {

        $option_arr = $this->ffw_get_setting_options();

        if( isset($option_arr) && is_array($option_arr) ) {
            foreach( $option_arr as $opt_group => $sections ) {

                // register option settings
                register_setting( $opt_group, 'ffw_general_settings' );

                foreach( $sections as $sec_key => $section ) {

                    $section_callback   = isset($section['callback']) && ! empty($section['callback']) ? array($this, $section['callback']) : '';

                    // add settings section
                    add_settings_section(
                        $sec_key,
                        esc_html__( '', 'faq-for-woocommerce' ),
                        $section_callback,
                        $opt_group
                    );

                    if( isset($section['fields']) && is_array($section['fields']) ) {
                        foreach($section['fields'] as $field) {

                            $object  = isset($field['region']) && ! empty($field['region']) && "free" == $field['region'] ? $this : ffw_get_setting_instance();

                            add_settings_field(
                                $field['id'],
                                $field['label'],
                                array( $object, $field['callback'] ),
                                $opt_group,
                                $sec_key
                            );
                        }
                    }

                }

            }
        }
    }

    /**
     * Enable/Disable FAQ Schema.
     *
     * @since 1.2.2
     * @return
     */
    function ffw_disable_schema( ) {
        $options        = $this->options;
        $options        = ! empty( $options ) ? $options : [];
        $disable_schema = array_key_exists( 'ffw_disable_schema', $options ) && ! empty($options['ffw_disable_schema']) ? $options['ffw_disable_schema'] : '';
        ?>
        <select class="ffw-schema-enable-disable" name='ffw_general_settings[ffw_disable_schema]'>
            <option value="1" <?php selected( $disable_schema, 1 ); ?>>Enable</option>
            <option value="2" <?php selected( $disable_schema, 2 ); ?>>Disable</option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Schema can be disabled from the product page by selecting disable.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Schema Description Type.
     *
     * @since 1.3.18
     * @return void
     */
    function ffw_schema_description_type( ) {
        $options            = $this->options;
        $options            = ! empty( $options ) ? $options : [];
        $schema_desc_type   = array_key_exists( 'ffw_schema_description_type', $options ) && ! empty($options['ffw_schema_description_type']) ? $options['ffw_schema_description_type'] : 1;
        ?>
        <select class="ffw-schema-description-type" name='ffw_general_settings[ffw_schema_description_type]'>
            <option value="1" <?php selected( $schema_desc_type, 1 ); ?>><?php echo esc_html__("Description (with HTML)", "faq-for-woocommerce"); ?></option>
            <option value="2" <?php selected( $schema_desc_type, 2 ); ?>><?php echo esc_html__("Description (without HTML)", "faq-for-woocommerce"); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Select schema description type.', 'faq-for-woocommerce'));
    }

    /**
     * Display Condition.
     */
    function ffw_display_condition() {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];
		$ffw_display_condition = isset( $options['ffw_display_condition'] ) ? $options['ffw_display_condition'] : "by_product";

        if (!ffw_is_pro_activated()): ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <select class="ffw-display-condition" name='ffw_general_settings[ffw_display_condition]'>
            <option value="by_product" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_condition, "by_product" ); ?>><?php esc_html_e('By Product', 'faq-for-woocommerce'); ?></option>
            <option value="by_product_cat" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_condition, "by_product_cat" ); ?>><?php esc_html_e('By Product Categories', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Choose the display condition for FAQs in product page, How FAQs will be fetched.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Width Callback.
     */
    function ffw_tab_label() {
        $val = ( isset( $this->options['ffw_tab_label'] ) ) ? $this->options['ffw_tab_label'] : '';
        echo '<input type="text" placeholder="FAQs" class="ffw-tab-label" name="ffw_general_settings[ffw_tab_label]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq tab label, it will affect in the product page description faq tab.', 'faq-for-woocommerce'));

        $this->ffw_layout();
    }

    /**
     * FAQ Layout.
     */
    function ffw_layout() {
        $val = ( isset( $this->options['ffw_layout'] ) ) ? $this->options['ffw_layout'] : 1;
        echo '<input type="hidden" class="ffw-layout" name="ffw_general_settings[ffw_layout]" value="'. esc_html($val) .'" />';
    }

    /**
     * FAQ Editor Callback.
     */
    function ffw_editor() {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];
		$ffw_editor = isset( $options['ffw_editor'] ) ? $options['ffw_editor'] : "1";
        ?>
        <select class="ffw-display-all-answers" name='ffw_general_settings[ffw_editor]'>
            <option value="1" <?php selected( $ffw_editor, "1" ); ?>><?php esc_html_e('Gutenberg', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_editor, "2" ); ?>><?php esc_html_e('Classic', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Choose the answer editor from gutenberg or classic editor.', 'faq-for-woocommerce'));
    }

    /**
     * Display Location for Product Pages.
     */
    function ffw_display_location() {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];
		$ffw_display_location = isset( $options['ffw_display_location'] ) ? $options['ffw_display_location'] : "product_tab";

        if (!ffw_is_pro_activated()): ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <select class="ffw-display-all-answers" name='ffw_general_settings[ffw_display_location]'>
            <option value="product_tab" <?php selected( $ffw_display_location, "product_tab" ); ?>><?php esc_html_e('Product Tab', 'faq-for-woocommerce'); ?></option>
            <option value="top_of_the_product_page" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location, "top_of_the_product_page" ); ?>><?php esc_html_e('Top of Product page [Pro]', 'faq-for-woocommerce'); ?></option>
            <option value="before_product_summary" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location, "before_product_summary" ); ?>><?php esc_html_e('Before Product Summary [Pro]', 'faq-for-woocommerce'); ?></option>
            <option value="after_add_to_cart_button" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location, "after_add_to_cart_button" ); ?>><?php esc_html_e('After `Add To Cart` Button [Pro]', 'faq-for-woocommerce'); ?></option>
            <option value="after_product_meta" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location, "after_product_meta" ); ?>><?php esc_html_e('After Product Meta [Pro]', 'faq-for-woocommerce'); ?></option>
            <option value="after_social_share_buttons" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location, "after_social_share_buttons" ); ?>><?php esc_html_e('After Social Share Buttons [Pro]', 'faq-for-woocommerce'); ?></option>
            <option value="bottom_of_the_product_page" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location, "bottom_of_the_product_page" ); ?>><?php esc_html_e('Bottom of Product page [Pro]', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Choose the display location of FAQs in product page, Where it should be displayed.', 'faq-for-woocommerce'));
    }

    /**
     * Enable FAQs in Archive Page
     *
     * @since 1.7.6
     */
    function ffw_enable_archive_pages_faqs() {
        ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>

            <div class="ffw-switch">
                <input type="checkbox" class="ffw-free-setting-switcher ffw-enable-archive-pages-faqs" checked="checked">
                <span class="ffw-switch-slider ffw-switch-round"></span>
            </div>
        </div>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable to display FAQs in Archive Pages (product categories, tags).', 'faq-for-woocommerce'));
    }
    
    /**
     * Display Location for Archieve Pages.
     * 
     * @return void
     */
    function ffw_display_location_archive() {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];
		$ffw_display_location_archive = isset( $options['ffw_display_location_archive'] ) ? $options['ffw_display_location_archive'] : "before_main_content";

        if (!ffw_is_pro_activated()): ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <select class="ffw-display-all-answers" name='ffw_general_settings[ffw_display_location_archive]'>
            <option value="before_main_content" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_archive, "before_main_content" ); ?>><?php esc_html_e('Before Main Content', 'faq-for-woocommerce'); ?></option>
            <option value="archive_description" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_archive, "archive_description" ); ?>><?php esc_html_e('Before Archive Description', 'faq-for-woocommerce'); ?></option>
            <option value="before_shop_loop" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_archive, "before_shop_loop" ); ?>><?php esc_html_e('Before Shop Loop', 'faq-for-woocommerce'); ?></option>
            <option value="after_shop_loop" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_archive, "after_shop_loop" ); ?>><?php esc_html_e('After Shop Loop', 'faq-for-woocommerce'); ?></option>
            <option value="after_main_content" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_archive, "after_main_content" ); ?>><?php esc_html_e('After Main Content', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Choose the display location of FAQs in archive pages (product category & tag pages). Assign product categories or tags to the FAQs.', 'faq-for-woocommerce'));
    }
    
    /**
     * Enable FAQs in Shop Page
     *
     * @since 1.7.7
     */
    function ffw_enable_shop_page_faqs() {
        ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>

            <div class="ffw-switch">
                <input type="checkbox" class="ffw-free-setting-switcher ffw-enable-shop-page-faqs" checked="checked">
                <span class="ffw-switch-slider ffw-switch-round"></span>
            </div>
        </div>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable to display FAQs in Shop Page.', 'faq-for-woocommerce'));
    }
    
    /**
     * Display Location for Shop Page.
     * 
     * @since 1.7.7
     * @return void
     */
    function ffw_display_location_shop() {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];
		$ffw_display_location_shop = isset( $options['ffw_display_location_shop'] ) ? $options['ffw_display_location_shop'] : "before_main_content";

        if (!ffw_is_pro_activated()): ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <select class="ffw-display-all-answers" name='ffw_general_settings[ffw_display_location_shop]'>
            <option value="before_main_content" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_shop, "before_main_content" ); ?>><?php esc_html_e('Before Main Content', 'faq-for-woocommerce'); ?></option>
            <option value="archive_description" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_shop, "archive_description" ); ?>><?php esc_html_e('Before Archive Description', 'faq-for-woocommerce'); ?></option>
            <option value="before_shop_loop" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_shop, "before_shop_loop" ); ?>><?php esc_html_e('Before Shop Loop', 'faq-for-woocommerce'); ?></option>
            <option value="after_shop_loop" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_shop, "after_shop_loop" ); ?>><?php esc_html_e('After Shop Loop', 'faq-for-woocommerce'); ?></option>
            <option value="after_main_content" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_shop, "after_main_content" ); ?>><?php esc_html_e('After Main Content', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Choose the display location of FAQs in shop page. Assign faq location as `Shop Page` to the FAQs.', 'faq-for-woocommerce'));
    }
    
    /**
     * Enable FAQs in Cart Page
     *
     * @since 1.7.7
     */
    function ffw_enable_cart_page_faqs() {
        ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>

            <div class="ffw-switch">
                <input type="checkbox" class="ffw-free-setting-switcher ffw-enable-cart-page-faqs" checked="checked">
                <span class="ffw-switch-slider ffw-switch-round"></span>
            </div>
        </div>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable to display FAQs in Cart Page.', 'faq-for-woocommerce'));
    }
    
    /**
     * Display Location for Cart Page.
     * 
     * @since 1.7.7
     * @return void
     */
    function ffw_display_location_cart() {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];
		$ffw_display_location_cart = isset( $options['ffw_display_location_cart'] ) ? $options['ffw_display_location_cart'] : "before_cart";

        if (!ffw_is_pro_activated()): ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <select class="ffw-display-all-answers" name='ffw_general_settings[ffw_display_location_cart]'>
            <option value="before_cart" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_cart, "before_cart" ); ?>><?php esc_html_e('Before Cart', 'faq-for-woocommerce'); ?></option>
            <option value="after_cart" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_cart, "after_cart" ); ?>><?php esc_html_e('After Cart', 'faq-for-woocommerce'); ?></option>
            <option value="before_cart_table" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_cart, "before_cart_table" ); ?>><?php esc_html_e('Before Cart Table', 'faq-for-woocommerce'); ?></option>
            <option value="after_cart_table" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_cart, "after_cart_table" ); ?>><?php esc_html_e('After Cart Table', 'faq-for-woocommerce'); ?></option>
            <option value="before_cart_contents" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_cart, "before_cart_contents" ); ?>><?php esc_html_e('Before Cart Content', 'faq-for-woocommerce'); ?></option>
            <option value="after_cart_contents" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_cart, "after_cart_contents" ); ?>><?php esc_html_e('After Cart Content', 'faq-for-woocommerce'); ?></option>
            <option value="before_cart_totals" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_cart, "before_cart_totals" ); ?>><?php esc_html_e('Before Cart Total', 'faq-for-woocommerce'); ?></option>
            <option value="after_cart_totals" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_cart, "after_cart_totals" ); ?>><?php esc_html_e('After Cart Total', 'faq-for-woocommerce'); ?></option>
            <option value="cart_totals_before_shipping" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_cart, "cart_totals_before_shipping" ); ?>><?php esc_html_e('Before Shopping', 'faq-for-woocommerce'); ?></option>
            <option value="cart_totals_after_shipping" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_cart, "cart_totals_after_shipping" ); ?>><?php esc_html_e('After Shopping', 'faq-for-woocommerce'); ?></option>
            <option value="cart_totals_before_order_total" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_cart, "cart_totals_before_order_total" ); ?>><?php esc_html_e('Before Order Total', 'faq-for-woocommerce'); ?></option>
            <option value="cart_totals_after_order_total" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_cart, "cart_totals_after_order_total" ); ?>><?php esc_html_e('After Order Total', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Choose the display location of FAQs in cart page. Assign faq location as `Cart Page` to the FAQs.', 'faq-for-woocommerce'));
    }
    
    /**
     * Enable FAQs in Checkout Page
     *
     * @since 1.7.7
     */
    function ffw_enable_checkout_page_faqs() {
        ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>

            <div class="ffw-switch">
                <input type="checkbox" class="ffw-free-setting-switcher ffw-enable-checkout-page-faqs" checked="checked">
                <span class="ffw-switch-slider ffw-switch-round"></span>
            </div>
        </div>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable to display FAQs in Checkout Page.', 'faq-for-woocommerce'));
    }
    
    /**
     * Display Location for Checkout Page.
     * 
     * @since 1.7.7
     * @return void
     */
    function ffw_display_location_checkout() {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];
		$ffw_display_location_checkout = isset( $options['ffw_display_location_checkout'] ) ? $options['ffw_display_location_checkout'] : "before_checkout_form";

        if (!ffw_is_pro_activated()): ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <select class="ffw-display-all-answers" name='ffw_general_settings[ffw_display_location_checkout]'>
            <option value="before_checkout_form" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "before_checkout_form" ); ?>><?php esc_html_e('Before Checkout Form', 'faq-for-woocommerce'); ?></option>
            <option value="after_checkout_form" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "after_checkout_form" ); ?>><?php esc_html_e('After Checkout Form', 'faq-for-woocommerce'); ?></option>
            <option value="checkout_before_customer_details" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "checkout_before_customer_details" ); ?>><?php esc_html_e('Before Customer Details', 'faq-for-woocommerce'); ?></option>
            <option value="checkout_after_customer_details" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "checkout_after_customer_details" ); ?>><?php esc_html_e('After Customer Details', 'faq-for-woocommerce'); ?></option>
            <option value="before_checkout_billing_form" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "before_checkout_billing_form" ); ?>><?php esc_html_e('Before Customer Billing', 'faq-for-woocommerce'); ?></option>
            <option value="after_checkout_billing_form" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "after_checkout_billing_form" ); ?>><?php esc_html_e('After Customer Billing', 'faq-for-woocommerce'); ?></option>
            <option value="before_checkout_shipping_form" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "before_checkout_shipping_form" ); ?>><?php esc_html_e('Before Customer Shipping', 'faq-for-woocommerce'); ?></option>
            <option value="after_checkout_shipping_form" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "after_checkout_shipping_form" ); ?>><?php esc_html_e('After Customer Shipping', 'faq-for-woocommerce'); ?></option>
            <option value="before_order_notes" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "before_order_notes" ); ?>><?php esc_html_e('Before Order Notes', 'faq-for-woocommerce'); ?></option>
            <option value="after_order_notes" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "after_order_notes" ); ?>><?php esc_html_e('After Order Notes', 'faq-for-woocommerce'); ?></option>
            <option value="checkout_before_order_review" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "checkout_before_order_review" ); ?>><?php esc_html_e('Before Order Review', 'faq-for-woocommerce'); ?></option>
            <option value="checkout_after_order_review" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "checkout_before_order_review" ); ?>><?php esc_html_e('After Order Review', 'faq-for-woocommerce'); ?></option>
            <option value="review_order_before_payment" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "review_order_before_payment" ); ?>><?php esc_html_e('Before Order Payment', 'faq-for-woocommerce'); ?></option>
            <option value="review_order_after_payment" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "review_order_after_payment" ); ?>><?php esc_html_e('After Order Payment', 'faq-for-woocommerce'); ?></option>
            <option value="review_order_before_submit" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "review_order_before_submit" ); ?>><?php esc_html_e('Before Submit Button', 'faq-for-woocommerce'); ?></option>
            <option value="review_order_after_submit" <?php echo !ffw_is_pro_activated() ? esc_attr('disabled') : ''; ?> <?php selected( $ffw_display_location_checkout, "review_order_after_submit" ); ?>><?php esc_html_e('After Submit Button', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Choose the display location of FAQs in cart page. Assign faq location as `Checkout Page` to the FAQs.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Tab Priority Callback.
     */
    function ffw_tab_priority() {
        $val = ( isset( $this->options['ffw_tab_priority'] ) ) ? $this->options['ffw_tab_priority'] : '50';
        echo '<input type="number" placeholder="100" class="ffw-tab-priority" name="ffw_general_settings[ffw_tab_priority]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Reorder faq tab according to its priority. Increase or decrease with 10 volume.', 'faq-for-woocommerce'));
    }

	/**
	 * Before FAQ Callback.
	 */
    function ffw_before_faq_render( ) {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];
		$before_faq = array_key_exists( 'ffw_before_faq', $options ) && ! empty($options['ffw_before_faq']) ? $options['ffw_before_faq'] : '';

		$settings  = array(
			'media_buttons' => true,
			'textarea_name' => 'ffw_general_settings[ffw_before_faq]',
		);
        wp_editor( $before_faq, 'ffw_before_faq_html', $settings );
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Before faq content will appear just before first faq accordion start.', 'faq-for-woocommerce'));
    }

    /**
     * Enable RTL support
     *
     * @since 1.4.1
     */
    function ffw_enable_rtl() {
        ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>
            <div class="ffw-switch">
                <input type="checkbox" class="ffw-free-setting-switcher ffw-enable-rtl" checked="checked">
                <span class="ffw-switch-slider ffw-switch-round"></span>
            </div>
        </div>

        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable RTL mode, FAQs are shown in `right to left` view.', 'faq-for-woocommerce'));
    }

    /**
     * Enable Dynamic Attributes
     *
     * @since 1.4.1
     */
    function ffw_enable_dynamic_attributes() {
        ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>

            <div class="ffw-switch">
                <input type="checkbox" class="ffw-free-setting-switcher ffw-enable-dynamic-attributes" checked="checked">
                <span class="ffw-switch-slider ffw-switch-round"></span>
            </div>
        </div>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable to show any product attributes value inside answers dynamically.', 'faq-for-woocommerce'));
    }

    /**
     * Enable Multi Column Support
     *
     * @since 1.4.1
     */
    function ffw_enable_multi_column_support() {
        ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>

            <div class="ffw-switch">
                <input type="checkbox" class="ffw-free-setting-switcher ffw-enable-multi-column-support" checked="checked">
                <span class="ffw-switch-slider ffw-switch-round"></span>
            </div>
        </div>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable to show FAQs in multiple column mode.', 'faq-for-woocommerce'));
    }

    /**
     * Enable Search Box
     *
     * @since 1.4.1
     */
    function ffw_enable_search_box() {
        ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>

            <div class="ffw-switch">
                <input type="checkbox" class="ffw-free-setting-switcher ffw-enable-search-box" checked="checked">
                <span class="ffw-switch-slider ffw-switch-round"></span>
            </div>
        </div>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable search box, customer can easily search FAQs as they want.', 'faq-for-woocommerce'));
    }

    /**
     * Enable Search Box Position
     *
     * @since 1.4.1
     */
    function ffw_search_box_position() {
        ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>

            <div class="ffw-switch">
                <input type="checkbox" class="ffw-free-setting-switcher ffw-enable-search-box" checked="checked">
                <span class="ffw-switch-slider ffw-switch-round"></span>
            </div>
        </div>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Select the search box position.', 'faq-for-woocommerce'));
    }

    /**
     * Enable Global FAQs
     *
     * @since 1.4.1
     */
    function ffw_enable_global_faqs() {
        ?>
        <div class="ffw-get-pro-wrapper">
            <div class="ffw-get-pro-badge">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
            </div>

            <div class="ffw-switch">
                <input type="checkbox" class="ffw-free-setting-switcher ffw-enable-global-faqs" checked="checked">
                <span class="ffw-switch-slider ffw-switch-round"></span>
            </div>
        </div>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable Global FAQs to set specific FAQ for `All the products`.', 'faq-for-woocommerce'));
    }

	/**
	 * Role options.
	 */
    function ffw_set_role() {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];
		$role = isset( $options['ffw_set_role'] ) ? $options['ffw_set_role'] : ["administrator"];

        $available_roles = ffw_get_available_user_roles();
        ?>
        <select class="widefat ffw-role-select" name='ffw_general_settings[ffw_set_role][]' multiple>
            <?php
                if(!empty($available_roles)) {
                    foreach($available_roles as $available_role) {
                    ?>
                    <option value="<?php echo esc_html($available_role); ?>" <?php selected( true, in_array($available_role, $role) ); ?>><?php echo esc_html($available_role); ?></option>
                    <?php
                    }
                }
            ?>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Which role of user should have access to FAQ posts?', 'faq-for-woocommerce'));
    }

	/**
	 * Show/Hide FAQs counter in front.
	 */
    function ffw_faq_counter_in_front() {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];
		$ffw_counter = isset( $options['ffw_faq_counter_in_front'] ) ? $options['ffw_faq_counter_in_front'] : "2";
        ?>
        <select class="ffw-counter-in-front" name='ffw_general_settings[ffw_faq_counter_in_front]'>
            <option value="1" <?php selected( $ffw_counter, "1" ); ?>><?php esc_html_e('Show', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_counter, "2" ); ?>><?php esc_html_e('Hide', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Show/Hide FAQs counter in front faqs tab.', 'faq-for-woocommerce'));
    }

	/**
	 * Show/Hide FAQs Counter in Product List.
	 */
    function ffw_hide_faq_number_for_product() {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];
		$ffw_counter = isset( $options['ffw_hide_faq_number_for_product'] ) ? $options['ffw_hide_faq_number_for_product'] : "1";
        ?>
        <select class="ffw-counter-in-product-list" name='ffw_general_settings[ffw_hide_faq_number_for_product]'>
            <option value="1" <?php selected( $ffw_counter, "1" ); ?>><?php esc_html_e('Show', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_counter, "2" ); ?>><?php esc_html_e('Hide', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Hide FAQs counter column from product list table.', 'faq-for-woocommerce'));
    }

    /**
     * Hide shortcode preview for general uses.
     */
    function ffw_hide_general_shortcode_preview() {
        $options = $this->options;
        $options = ! empty( $options ) ? $options : [];
        $ffw_general_shortcode_preview = isset( $options['ffw_hide_general_shortcode_preview'] ) ? $options['ffw_hide_general_shortcode_preview'] : "1";
        ?>
        <select class="ffw-hide-general-shortcode-preview" name='ffw_general_settings[ffw_hide_general_shortcode_preview]'>
            <option value="1" <?php selected( $ffw_general_shortcode_preview, "1" ); ?>><?php esc_html_e('Show', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_general_shortcode_preview, "2" ); ?>><?php esc_html_e('Hide', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Hide FAQs general shortcode like [ffw_template]', 'faq-for-woocommerce'));
    }


    /**
     * Hide dynamic shortcode preview for product/single page.
     */
    function ffw_hide_dynamic_shortcode_preview() {
        $options = $this->options;
        $options = ! empty( $options ) ? $options : [];
        $ffw_dynamic_shortcode_preview = isset( $options['ffw_hide_dynamic_shortcode_preview'] ) ? $options['ffw_hide_dynamic_shortcode_preview'] : "1";
        ?>
        <select class="ffw-hide-general-shortcode-preview" name='ffw_general_settings[ffw_hide_dynamic_shortcode_preview]'>
            <option value="1" <?php selected( $ffw_dynamic_shortcode_preview, "1" ); ?>><?php esc_html_e('Show', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_dynamic_shortcode_preview, "2" ); ?>><?php esc_html_e('Hide', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Hide dynamic shortcode preview in single product page.', 'faq-for-woocommerce'));
    }

	/**
	 * Display all faq answers.
	 */
    function ffw_display_all_faq_answers() {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];
		$ffw_display_all_answers = isset( $options['ffw_display_all_faq_answers'] ) ? $options['ffw_display_all_faq_answers'] : "2";
        ?>
        <select class="ffw-display-all-answers" name='ffw_general_settings[ffw_display_all_faq_answers]'>
            <option value="1" <?php selected( $ffw_display_all_answers, "1" ); ?>><?php esc_html_e('Show', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_display_all_answers, "2" ); ?>><?php esc_html_e('Hide', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Display all the answers when the page loads.', 'faq-for-woocommerce'));
    }

    /**
     * Expand/Collapse All faqs.
     *
     * @since 1.3.31
     */
    function ffw_expand_collapse_all() {
        $options = $this->options;
        $options = ! empty( $options ) ? $options : [];
        $ffw_expand_collapse_all = isset( $options['ffw_expand_collapse_all'] ) ? $options['ffw_expand_collapse_all'] : "2";
        ?>
        <select class="ffw-expend-collapse-label-all" name='ffw_general_settings[ffw_expand_collapse_all]'>
            <option value="1" <?php selected( $ffw_expand_collapse_all, "1" ); ?>><?php esc_html_e('Enable', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_expand_collapse_all, "2" ); ?>><?php esc_html_e('Disable', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable to open and close all FAQs simultaneously.', 'faq-for-woocommerce'));
    }

    /**
     * Expand/Collapse Label.
     *
     * @since 1.3.31
     */
    function ffw_expand_collapse_label() {
        $options = $this->options;
        $options = ! empty( $options ) ? $options : [];
        $ffw_expand_collapse_label = isset( $options['ffw_expand_collapse_label'] ) ? $options['ffw_expand_collapse_label'] : esc_html__( "Expand/Collapse All", 'faq-for-woocommerce' );

        echo sprintf('<input type="text" name="ffw_general_settings[ffw_expand_collapse_label]" class="ffw-expand-collapse-label" placeholder="%s" value="%s"/>', esc_html__( "Expand/Collapse All", 'faq-for-woocommerce' ), esc_html($ffw_expand_collapse_label) );

        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Set a label for expend/collapse all button.', 'faq-for-woocommerce'));
    }


    /**
     * Global faqs appearance.
     *
     * @since 1.6.5
     */
    function ffw_global_faqs_appearance() {
        $options = $this->options;
        $options = ! empty( $options ) ? $options : [];
        $ffw_global_faqs_appearance = isset( $options['ffw_global_faqs_appearance'] ) ? $options['ffw_global_faqs_appearance'] : "1";
        ?>
        <select class="ffw-disable-noindex" name='ffw_general_settings[ffw_global_faqs_appearance]'>
            <option value="1" <?php selected( $ffw_global_faqs_appearance, "1" ); ?>><?php esc_html_e('Display First', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_global_faqs_appearance, "2" ); ?>><?php esc_html_e('Display Last', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Select whether Global FAQs will display first or last of other FAQs. Make sure Global FAQs setting is enabled.', 'faq-for-woocommerce'));
    }
    
    /**
     * Index/Noindexing for faq post type
     *
     * @since 1.3.30
     */
    function ffw_post_index() {
        $options = $this->options;
        $options = ! empty( $options ) ? $options : [];
        $ffw_post_index = isset( $options['ffw_post_index'] ) ? $options['ffw_post_index'] : "1";
        ?>
        <select class="ffw-disable-noindex" name='ffw_general_settings[ffw_post_index]'>
            <option value="1" <?php selected( $ffw_post_index, "1" ); ?>><?php esc_html_e('Noindex', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $ffw_post_index, "2" ); ?>><?php esc_html_e('Index', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Index/Noindex FAQ posts.', 'faq-for-woocommerce'));
    }

	/**
	 * After FAQ Callback.
	 */
    function ffw_after_faq_render() {
        $options = $this->options;
		$options = ! empty( $options ) ? $options : [];

		$after_faq = array_key_exists( 'ffw_after_faq', $options ) && ! empty($options['ffw_after_faq']) ? $options['ffw_after_faq'] : '';
		$settings  = array(
			'media_buttons' => true,
			'textarea_name' => 'ffw_general_settings[ffw_after_faq]',
		);
		wp_editor( $after_faq, 'ffw_after_faq_html', $settings );
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Before faq content will appear just after last faq accordion end.', 'faq-for-woocommerce'));
    }

	/**
	 * Setting Description Callback.
	 */
    function ffw_settings_section_callback() {
        echo sprintf('<p>%s</p>', esc_html__('This is where you can set faq options.The following options affect the faqs are displayed on the frontend.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Width Callback.
     */
    function ffw_width_field() {
        $val = ( isset( $this->options['ffw_width'] ) ) ? $this->options['ffw_width'] : '';
        echo '<input type="number" max="100" placeholder="100" class="ffw-width" name="ffw_general_settings[ffw_width]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Control faq wrapper width by percentage.', 'faq-for-woocommerce'));
    }

    /**
     * Custom CSS Callback.
     */
    function ffw_custom_css_field() {
        $val = ( isset( $this->options['ffw_custom_css'] ) ) ? $this->options['ffw_custom_css'] : '';
        echo '<textarea class="ffw-custom-css" name="ffw_general_settings[ffw_custom_css]" >'. esc_html($val) .'</textarea>';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Custom css is the most priority css, you can customize anything.', 'faq-for-woocommerce'));
    }

    /**
     * Question Text Color Callback.
     */
    function ffw_question_text_color_field() {
        $val = ( isset( $this->options['ffw_question_text_color'] ) ) ? $this->options['ffw_question_text_color'] : '';
        echo '<input type="text" class="ffw_question_text_color" name="ffw_general_settings[ffw_question_text_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq question text color.', 'faq-for-woocommerce'));
    }

    /**
     * Question Background Color Callback.
     */
    function ffw_question_bg_color_field() {
        $val = ( isset( $this->options['ffw_question_bg_color'] ) ) ? $this->options['ffw_question_bg_color'] : '';
        echo '<input type="text" class="ffw_question_bg_color" name="ffw_general_settings[ffw_question_bg_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq question background color.', 'faq-for-woocommerce'));
    }

    /**
     * Question Background Secondary Color Callback.
     */
    function ffw_question_bg_secondary_color_field() {
        $val = ( isset( $this->options['ffw_question_bg_secondary_color'] ) ) ? $this->options['ffw_question_bg_secondary_color'] : '';
        echo '<input type="text" class="ffw_question_bg_secondary_color" name="ffw_general_settings[ffw_question_bg_secondary_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq question background color, this setting is for only pop layout.', 'faq-for-woocommerce'));
    }

    /**
     * Question Border Color Callback.
     */
    function ffw_question_border_color_field() {
        $val = ( isset( $this->options['ffw_question_border_color'] ) ) ? $this->options['ffw_question_border_color'] : '';
        echo '<input type="text" class="ffw_question_border_color" name="ffw_general_settings[ffw_question_border_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq question border color.', 'faq-for-woocommerce'));
    }

    /**
     * Question Font Size Callback.
     */
    function ffw_question_font_size_field() {
        $val = ( isset( $this->options['ffw_question_font_size'] ) ) ? $this->options['ffw_question_font_size'] : '';
        echo '<input type="range" class="ffw_question_font_size" name="ffw_general_settings[ffw_question_font_size]" min="0" max="50" step="1" value="' . esc_html($val) . '" />';
        echo sprintf('<span class="ffw_question_font_size_label">%s</span>', esc_html__($val . 'px', 'faq-for-woocommerce') );
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq question font size, move the range control for increase/decrease font size.', 'faq-for-woocommerce'));
    }

    /**
     * Answer Text Color Callback.
     */
    function ffw_answer_text_color_field() {
        $val = ( isset( $this->options['ffw_answer_text_color'] ) ) ? $this->options['ffw_answer_text_color'] : '';
        echo '<input type="text" class="ffw_answer_text_color" name="ffw_general_settings[ffw_answer_text_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq answer text color.', 'faq-for-woocommerce'));
    }

    /**
     * Answer Background Color Callback.
     */
    function ffw_answer_bg_color_field() {
        $val = ( isset( $this->options['ffw_answer_bg_color'] ) ) ? $this->options['ffw_answer_bg_color'] : '';
        echo '<input type="text" class="ffw_answer_bg_color" name="ffw_general_settings[ffw_answer_bg_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq answer background color.', 'faq-for-woocommerce'));
    }

    /**
     * Answer Border Color Callback.
     */
    function ffw_answer_border_color_field() {
        $val = ( isset( $this->options['ffw_answer_border_color'] ) ) ? $this->options['ffw_answer_border_color'] : '';
        echo '<input type="text" class="ffw_answer_border_color" name="ffw_general_settings[ffw_answer_border_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq answer border color.', 'faq-for-woocommerce'));
    }

    /**
     * Answer Font Size Callback.
     */
    function ffw_answer_font_size_field() {
        $val = ( isset( $this->options['ffw_answer_font_size'] ) ) ? $this->options['ffw_answer_font_size'] : '';
        echo '<input type="range" class="ffw_answer_font_size" name="ffw_general_settings[ffw_answer_font_size]" min="0" max="50" step="1" value="' . esc_html($val) . '" />';
        echo sprintf('<span class="ffw_answer_font_size_label">%s</span>', esc_html__($val . 'px', 'faq-for-woocommerce') );
    }

    /**
     * Comment General Section.
     *
     * @param $args array comment general section arguments
     */
    function ffw_comment_general_setting_cb($args) {
        echo sprintf("<h6>%s</h6>", esc_html__("Comment General Settings", "faq-for-woocommerce"));
    }

    /**
     * Comment options.
     */
    function ffw_comments_on_cb() {
        $options = ! empty( $this->options ) ? $this->options : [];
        $faq_comment_enable = isset( $options['ffw_comment_on'] ) && !empty($options['ffw_comment_on']) ? $options['ffw_comment_on'] : 1;

        ?>
        <select class="ffw-comment-on-select" name='ffw_general_settings[ffw_comment_on]'>
            <option value="1" <?php selected( $faq_comment_enable, 1 ); ?>>Disable</option>
            <option value="2" <?php selected( $faq_comment_enable, 2 ); ?>>Enable</option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable/Disable FAQs comment feature.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Sort Ordering Callback.
     */
    function ffw_comments_ordering_cb() {
        $options = ! empty( $this->options ) ? $this->options : [];
        $faq_comment_ordering = isset( $options['ffw_comments_ordering'] ) && !empty($options['ffw_comments_ordering']) ? $options['ffw_comments_ordering'] : 2;

        ?>
        <select class="ffw-comment-on-select" name='ffw_general_settings[ffw_comments_ordering]'>
            <option value="1" <?php selected( $faq_comment_ordering, 1 ); ?>><?php esc_html_e("Ascending", "faq-for-woocommerce"); ?></option>
            <option value="2" <?php selected( $faq_comment_ordering, 2 ); ?>><?php esc_html_e("Descending", "faq-for-woocommerce"); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change to sort the faq comments ordeirng.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Avatar Callback.
     */
    function ffw_comments_avatar_cb() {
        $options = ! empty( $this->options ) ? $this->options : [];
        $faq_comment_avatar = isset( $options['ffw_comments_avatar'] ) && !empty($options['ffw_comments_avatar']) ? $options['ffw_comments_avatar'] : 1;

        ?>
        <select class="ffw-comments-avatar" name='ffw_general_settings[ffw_comments_avatar]'>
            <option value="1" <?php selected( $faq_comment_avatar, 1 ); ?>><?php esc_html_e("Show", "faq-for-woocommerce"); ?></option>
            <option value="2" <?php selected( $faq_comment_avatar, 2 ); ?>><?php esc_html_e("Hide", "faq-for-woocommerce"); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change to show/hide the faq comments avatar.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Section Title.
     */
    function ffw_comments_section_title_cb() {
        $val = ( isset( $this->options['ffw_comments_section_title'] ) && !empty($this->options['ffw_comments_section_title']) ) ? $this->options['ffw_comments_section_title'] : esc_html__("Comments", "faq-for-woocommerce");
        echo '<input type="text" placeholder="Comment section title here" class="ffw-ffw-comment-section-title" name="ffw_general_settings[ffw_comments_section_title]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments section title.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Form Title.
     */
    function ffw_comments_form_title_cb() {
        $val = ( isset( $this->options['ffw_comments_form_title'] ) && !empty($this->options['ffw_comments_form_title']) ) ? $this->options['ffw_comments_form_title'] : esc_html__("Comment Box", "faq-for-woocommerce");
        echo '<input type="text" placeholder="Comment form title here" class="ffw-ffw-comment-form-title" name="ffw_general_settings[ffw_comments_form_title]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments form title.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Title.
     */
    function ffw_comments_reply_form_title_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_title'] ) && !empty($this->options['ffw_comments_reply_form_title']) ) ? $this->options['ffw_comments_reply_form_title'] : esc_html__("Write a Reply", "faq-for-woocommerce");
        echo '<input type="text" placeholder="Comment reply form title here" class="ffw-ffw-comment-form-title" name="ffw_general_settings[ffw_comments_reply_form_title]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments reply form title.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Button Title.
     */
    function ffw_comments_reply_button_text_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_button_text'] ) && !empty($this->options['ffw_comments_reply_button_text']) ) ? $this->options['ffw_comments_reply_button_text'] : esc_html__("Reply", "faq-for-woocommerce");
        echo '<input type="text" placeholder="Comment reply button text here" class="ffw-ffw-comment-form-title" name="ffw_general_settings[ffw_comments_reply_button_text]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments reply button text.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Submit Button Text.
     */
    function ffw_comments_submit_button_text_cb() {
        $val = ( isset( $this->options['ffw_comments_submit_button_text'] ) && !empty($this->options['ffw_comments_submit_button_text']) ) ? $this->options['ffw_comments_submit_button_text'] : esc_html__("Comment", "faq-for-woocommerce");
        echo '<input type="text" placeholder="Comment submit button text here" class="ffw-ffw-comment-submit-button-text" name="ffw_general_settings[ffw_comments_submit_button_text]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments submit button text.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Submit Button Text.
     */
    function ffw_comments_reply_form_submit_button_text_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_submit_button_text'] ) && !empty($this->options['ffw_comments_reply_form_submit_button_text']) ) ? $this->options['ffw_comments_reply_form_submit_button_text'] : esc_html__("Reply Comment", "faq-for-woocommerce");
        echo '<input type="text" placeholder="Comment reply form submit button text here" class="ffw-ffw-comment-form-title" name="ffw_general_settings[ffw_comments_reply_form_submit_button_text]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments reply form submit button text.', 'faq-for-woocommerce'));
    }


    /**
     * FAQ Avatar Style.
     */
    function ffw_comments_avatar_style_cb() {
        $options = ! empty( $this->options ) ? $this->options : [];
        $faq_comment_avatar_style = isset( $options['ffw_comments_avatar_style'] ) && !empty($options['ffw_comments_avatar_style']) ? $options['ffw_comments_avatar_style'] : 1;

        ?>
        <select class="ffw-comment-avatar-style" name='ffw_general_settings[ffw_comments_avatar_style]'>
            <option value="1" <?php selected( $faq_comment_avatar_style, 1 ); ?>><?php esc_html_e("Circle", "faq-for-woocommerce"); ?></option>
            <option value="2" <?php selected( $faq_comment_avatar_style, 2 ); ?>><?php esc_html_e("Rectangle", "faq-for-woocommerce"); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change comments avatar style.', 'faq-for-woocommerce'));
    }


    /**
     * FAQ Comment Date Font Size.
     */
    function ffw_comments_date_time_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_date_time_font_size'] ) && !empty($this->options['ffw_comments_date_time_font_size']) ) ? $this->options['ffw_comments_date_time_font_size'] : '14px';
        echo '<input type="text" placeholder="Comment date font size here" class="ffw-ffw-comment-form-title" name="ffw_general_settings[ffw_comments_date_time_font_size]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments date-time font size.', 'faq-for-woocommerce'));
    }


    /**
     * FAQ Comment Date Time Color.
     */
    function ffw_comments_date_time_color_cb() {
        $val = ( isset( $this->options['ffw_comments_date_time_color'] ) && !empty($this->options['ffw_comments_date_time_color']) ) ? $this->options['ffw_comments_date_time_color'] : "#28303d";
        echo '<input type="text" class="ffw-comments-date-time-color" name="ffw_general_settings[ffw_comments_date_time_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments date color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Content Color.
     */
    function ffw_comments_content_color_cb() {
        $val = ( isset( $this->options['ffw_comments_content_color'] ) && !empty($this->options['ffw_comments_content_color']) ) ? $this->options['ffw_comments_content_color'] : "#28303d";
        echo '<input type="text" class="ffw-comments-content-color" name="ffw_general_settings[ffw_comments_content_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments content color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Section Title Color.
     */
    function ffw_comments_section_title_color_cb() {
        $val = ( isset( $this->options['ffw_comments_section_title_color'] ) && !empty($this->options['ffw_comments_section_title_color']) ) ? $this->options['ffw_comments_section_title_color'] : "#28303d";
        echo '<input type="text" class="ffw-comments-content-color" name="ffw_general_settings[ffw_comments_section_title_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments section title color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Author Name Color.
     */
    function ffw_comments_author_name_color_cb() {
        $val = ( isset( $this->options['ffw_comments_author_name_color'] ) && !empty($this->options['ffw_comments_author_name_color']) ) ? $this->options['ffw_comments_author_name_color'] : "#28303d";
        echo '<input type="text" class="ffw-comments-author-name-color" name="ffw_general_settings[ffw_comments_author_name_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments author name color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Button Text Color.
     */
    function ffw_comments_reply_button_text_color_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_button_text_color'] ) && !empty($this->options['ffw_comments_reply_button_text_color']) ) ? $this->options['ffw_comments_reply_button_text_color'] : "#0b0beb";
        echo '<input type="text" class="ffw-comments-reply-button-text-color" name="ffw_general_settings[ffw_comments_reply_button_text_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments reply button text color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Submit Button Background Color.
     */
    function ffw_comments_submit_button_bg_color_cb() {
        $val = ( isset( $this->options['ffw_comments_submit_button_bg_color'] ) && !empty($this->options['ffw_comments_submit_button_bg_color']) ) ? $this->options['ffw_comments_submit_button_bg_color'] : "#008000";
        echo '<input type="text" class="ffw-comments-submit-button-bg-color" name="ffw_general_settings[ffw_comments_submit_button_bg_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments submit button background color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Form Border Color.
     */
    function ffw_comments_form_border_color_cb() {
        $val = ( isset( $this->options['ffw_comments_form_border_color'] ) && !empty($this->options['ffw_comments_form_border_color']) ) ? $this->options['ffw_comments_form_border_color'] : "#008000";
        echo '<input type="text" class="ffw-comments-form-border-color" name="ffw_general_settings[ffw_comments_form_border_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments form border color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Form Title Color.
     */
    function ffw_comments_form_title_color_cb() {
        $val = ( isset( $this->options['ffw_comments_form_title_color'] ) && !empty($this->options['ffw_comments_form_title_color']) ) ? $this->options['ffw_comments_form_title_color'] : "#28303d";
        echo '<input type="text" class="ffw-comments-form-title-color" name="ffw_general_settings[ffw_comments_form_title_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments form title color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Title Color.
     */
    function ffw_comments_reply_form_title_color_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_title_color'] ) && !empty($this->options['ffw_comments_reply_form_title_color']) ) ? $this->options['ffw_comments_reply_form_title_color'] : "#28303d";
        echo '<input type="text" class="ffw-comments-reply-form-title-color" name="ffw_general_settings[ffw_comments_reply_form_title_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments reply form title color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Form Submit Button Color.
     */
    function ffw_comments_submit_button_text_color_cb() {
        $val = ( isset( $this->options['ffw_comments_submit_button_text_color'] ) && !empty($this->options['ffw_comments_submit_button_text_color']) ) ? $this->options['ffw_comments_submit_button_text_color'] : "#ffffff";
        echo '<input type="text" class="ffw-comments-submit-button-text-color" name="ffw_general_settings[ffw_comments_submit_button_text_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments form submit button color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Submit Button Color.
     */
    function ffw_comments_reply_form_submit_button_text_color_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_submit_button_text_color'] ) && !empty($this->options['ffw_comments_reply_form_submit_button_text_color']) ) ? $this->options['ffw_comments_reply_form_submit_button_text_color'] : "#ffffff";
        echo '<input type="text" class="ffw-comments-reply-form-submit-button-text-color" name="ffw_general_settings[ffw_comments_reply_form_submit_button_text_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments reply form submit button text color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Submit Button Background Color.
     */
    function ffw_comments_reply_form_submit_button_bg_color_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_submit_button_bg_color'] ) && !empty($this->options['ffw_comments_reply_form_submit_button_bg_color']) ) ? $this->options['ffw_comments_reply_form_submit_button_bg_color'] : "#008000";
        echo '<input type="text" class="ffw-comments-reply-form-submit-button-bg-color" name="ffw_general_settings[ffw_comments_reply_form_submit_button_bg_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments reply form submit button background color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Border Color Color.
     */
    function ffw_comments_reply_form_border_color_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_border_color'] ) && !empty($this->options['ffw_comments_reply_form_border_color']) ) ? $this->options['ffw_comments_reply_form_border_color'] : "#008000";
        echo '<input type="text" class="ffw-comments-reply-form-border-color" name="ffw_general_settings[ffw_comments_reply_form_border_color]" value="' . esc_html($val) . '" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Change the faq comments reply form border color.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Content Font Size.
     */
    function ffw_comments_content_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_content_font_size'] ) && !empty($this->options['ffw_comments_content_font_size']) ) ? $this->options['ffw_comments_content_font_size'] : "18px";
        echo '<input type="text" placeholder="Comment content font size here" class="ffw-ffw-comment-content-font-size" name="ffw_general_settings[ffw_comments_content_font_size]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments content font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Author Name Font Size.
     */
    function ffw_comments_author_name_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_author_name_font_size'] ) && !empty($this->options['ffw_comments_author_name_font_size']) ) ? $this->options['ffw_comments_author_name_font_size'] : "16px";
        echo '<input type="text" placeholder="Comment author name font size here" class="ffw-ffw-comment-author-name-font-size" name="ffw_general_settings[ffw_comments_author_name_font_size]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments author name font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Button Font Size.
     */
    function ffw_comments_reply_button_font_size_cb() {
        $val = ( isset($this->options['ffw_comments_reply_button_font_size']) && !empty($this->options['ffw_comments_reply_button_font_size']) ) ? $this->options['ffw_comments_reply_button_font_size'] : "16px";
        echo '<input type="text" placeholder="Comment reply button size here" class="ffw-ffw-comment-reply-button-font-size" name="ffw_general_settings[ffw_comments_reply_button_font_size]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments reply button font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Submit Button Font Size.
     */
    function ffw_comments_submit_button_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_submit_button_font_size'] ) && !empty($this->options['ffw_comments_submit_button_font_size']) ) ? $this->options['ffw_comments_submit_button_font_size'] : "16px";
        echo '<input type="text" placeholder="Comment submit button font size here" class="ffw-ffw-comment-submit-button-font-size" name="ffw_general_settings[ffw_comments_submit_button_font_size]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments submit button font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Section Title Font Size.
     */
    function ffw_comments_form_section_title_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_form_section_title_font_size'] ) && !empty($this->options['ffw_comments_form_section_title_font_size']) ) ? $this->options['ffw_comments_form_section_title_font_size'] : "20px";
        echo '<input type="text" placeholder="Comment section header title font size here" class="ffw-ffw-comment-form-title-font-size" name="ffw_general_settings[ffw_comments_form_section_title_font_size]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments Section title font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Form Title Font Size.
     */
    function ffw_comments_form_title_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_form_title_font_size'] ) && !empty($this->options['ffw_comments_form_title_font_size']) ) ? $this->options['ffw_comments_form_title_font_size'] : "16px";
        echo '<input type="text" placeholder="Comment form title font size here" class="ffw-ffw-comment-form-title-font-size" name="ffw_general_settings[ffw_comments_form_title_font_size]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments form title font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Title Font Size.
     */
    function ffw_comments_reply_form_title_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_title_font_size'] ) && !empty($this->options['ffw_comments_reply_form_title_font_size']) ) ? $this->options['ffw_comments_reply_form_title_font_size'] : "16px";
        echo '<input type="text" placeholder="Comment reply form title font size here" class="ffw-ffw-comment-reply-form-title-font-size" name="ffw_general_settings[ffw_comments_reply_form_title_font_size]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments reply form title font size.', 'faq-for-woocommerce'));
    }

    /**
     * FAQ Comment Reply Form Submit Button Font Size.
     */
    function ffw_comments_reply_form_submit_button_font_size_cb() {
        $val = ( isset( $this->options['ffw_comments_reply_form_submit_button_font_size'] ) && !empty($this->options['ffw_comments_reply_form_submit_button_font_size']) ) ? $this->options['ffw_comments_reply_form_submit_button_font_size'] : "16px";
        echo '<input type="text" placeholder="Comment reply form submit button font size here" class="ffw-ffw-comment-reply-form-submit-button-font-size" name="ffw_general_settings[ffw_comments_reply_form_submit_button_font_size]" value="'. esc_html($val) .'" />';
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Put comments reply form submit button font size.', 'faq-for-woocommerce'));
    }

    /**
     * Comment Styles Section.
     *
     * @param $args array comment style section arguments
     */
    function ffw_comment_style_setting_cb($args) {
        echo sprintf("<h6>%s</h6>", esc_html__("Comment Style Settings", "faq-for-woocommerce"));
    }

    /**
     * Enable/Disable AI FAQ.
     *
     * @since 1.6.5
     * @return
     */
    function ffw_disable_ai_faqs( ) {
        $options        = $this->options;
        $options        = ! empty( $options ) ? $options : [];
        $disable_ai_faqs = array_key_exists( 'ffw_disable_ai_faqs', $options ) && ! empty($options['ffw_disable_ai_faqs']) ? $options['ffw_disable_ai_faqs'] : '';
        ?>
        <select class="ffw-ai_faqs-enable-disable" name='ffw_general_settings[ffw_disable_ai_faqs]'>
            <option value="1" <?php selected( $disable_ai_faqs, 1 ); ?>><?php esc_html_e('Enable', 'faq-for-woocommerce'); ?></option>
            <option value="2" <?php selected( $disable_ai_faqs, 2 ); ?>><?php esc_html_e('Disable', 'faq-for-woocommerce'); ?></option>
        </select>
        <?php
        echo sprintf('<p class="ffw-setting-description"><span>&#9432;</span>%s</p>', esc_html__('Enable to generate AI based product FAQs.', 'faq-for-woocommerce'));
    }

    /**
     * AI FAQ moedels.
     *
     * @since 1.6.5
     * @return
     */
    function ffw_ai_faqs_models( ) {
        $options        = $this->options;
        $options        = ! empty( $options ) ? $options : [];
        $ai_faqs_models = array_key_exists( 'ffw_ai_faqs_models', $options ) && ! empty($options['ffw_ai_faqs_model']) ? $options['ffw_ai_faqs_model'] : '';
        
        $ai_models = [
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4' => 'GPT-4',
        ];

        if(!empty($ai_models)):
        ?>
        <select class="ffw-ai_faqs-enable-disable" name='ffw_general_settings[ffw_ai_faqs_model]'>
            <?php foreach($ai_models as $model_key => $model_name): ?>
            <option value="<?php echo esc_attr($model_key); ?>" <?php selected( $ai_faqs_models, $model_key ); ?>><?php echo esc_html($model_name); ?></option>
            <?php endforeach; ?>
        </select>
        <?php
        endif;
        echo sprintf(wp_kses_post('<p class="ffw-setting-description"><span>&#9432;</span>Check out available <a href="%s" target="_blank">Open AI Models</a></p>'), esc_url('https://platform.openai.com/docs/models'));
    }

    /**
     * API key for AI FAQs.
     */
    function ffw_ai_faqs_api_key() {
        $val = ( isset( $this->options['ffw_ai_faqs_api_key'] ) ) ? $this->options['ffw_ai_faqs_api_key'] : '';
        echo '<input type="text" placeholder="'. esc_attr('API Secret Key', 'faq-for-woocommerce') .'" class="ffw-api-key" name="ffw_general_settings[ffw_ai_faqs_api_key]" value="'. esc_html($val) .'" />';
        echo sprintf(wp_kses_post('<p class="ffw-setting-description"><span>&#9432;</span>Get <a href="%s" target="_blank">API Key</a> to generate product faqs with Open AI.</p>'), esc_url('https://platform.openai.com/api-keys'));
    }
    
    /**
     * Max token for AI FAQs.
     */
    function ffw_ai_faqs_max_token() {
        $val = ( isset( $this->options['ffw_ai_faqs_max_token'] ) ) ? $this->options['ffw_ai_faqs_max_token'] : 2000;
        echo '<input type="number" placeholder="Set Max Token" class="ffw-max-token" name="ffw_general_settings[ffw_ai_faqs_max_token]" value="'. esc_html($val) .'" />';
        echo sprintf(wp_kses_post('<p class="ffw-setting-description"><span>&#9432;</span>FAQs will be generated based on the Token limit you have set. For more information, check out this <a href="%s">link</a>.</p>'), esc_url('https://happydevs.net'));
    }

    /**
	 * Options Page HTML.
	 */
    function ffw_options_page() {

        // redirect to pro page
        if( isset($_GET['page']) && "ffw-go-pro" === $_GET['page'] ) {
            wp_redirect( FFW_PRO_URL );
            exit;
        }

        ?>
        <div class="ffw-setting-wrap ffw-admin-wrapper">
            <div class="ffw-go-pro-modal ffw-hide">
                <div class="ffw-go-pro-modal-card">
                    <div class="ffw-go-pro-modal-card-inner">
                        <a href="#" id="ffw-popup-close" class="ffw-modal-close">
                            <span class="dashicons dashicons-no-alt"></span>
                        </a>
                        <div class="ffw-go-pro-modal-content">
                            <div class="ffw-go-pro-icon ffw-go-pro-close-btn">
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/gift-box.svg'); ?>" alt="">
                            </div>
                            <?php
                                echo sprintf('<h3>%s</h3>', esc_html__("Be A PRO", "faq-for-woocommerce"));
                                echo sprintf('<p>%s</p>', esc_html__("Grab the best features of Happy WooCommerce FAQs to increase your sell.", "faq-for-woocommerce"));
                                echo sprintf('<a class="ffw-go-pro-modal-link ffw-primary-btn"" target="__blank" href="%2$s">%1$s</a>', esc_html__("Upgrade Now", "faq-for-woocommerce"), esc_url(FFW_PRO_URL));
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <form action='options.php' method='post'>
                <?php

                echo ffw_dashboard_header();

                ?>
                <div class="ffw-dashboard-body-section-wrapper">
                    <div class="ffw-contianer">
                        <div class="ffw-settings-inner">
                            <?php $this->ffw_nav_items(); ?>
                            
                            <div class="tab-content">
    
                                <div class="tab-pane active" id="ffw-general">
                                    <div class="ffw-setting-wrapper">
                                        <div class="ffw-setting-form ffw-left-side">
                                            <?php
                                                settings_fields( 'ffw_settings' );
                                                do_settings_sections( 'ffw_settings' );
                                            ?>
                                        </div>
    
                                        <?php $this->ffw_settings_sidebar(); ?>
                                    </div>
                                </div>
    
                                <div class="tab-pane" id="ffw-style">
                                    <div class="ffw-setting-wrapper">
                                        <div class="ffw-setting-form ffw-left-side">
                                            <?php
                                                settings_fields( 'ffw_style_settings' );
                                                do_settings_sections( 'ffw_style_settings' );
                                            ?>
                                        </div>
    
                                        <?php $this->ffw_settings_sidebar(); ?>
                                    </div>
                                </div>
    
                                <div class="tab-pane" id="ffw-schema">
                                    <div class="ffw-setting-wrapper">
                                        <div class="ffw-setting-form ffw-left-side">
                                            <?php
                                                settings_fields( 'ffw_schema_settings' );
                                                do_settings_sections( 'ffw_schema_settings' );
                                            ?>
                                        </div>
    
                                        <?php $this->ffw_settings_sidebar(); ?>
                                    </div>
                                </div>
    
                                <div class="tab-pane" id="ffw-comment">
                                    <div class="ffw-setting-wrapper">
                                        <div class="ffw-setting-form ffw-left-side">
                                            <?php
                                            settings_fields( 'ffw_comment_settings' );
                                            do_settings_sections( 'ffw_comment_settings' );
                                            ?>
                                        </div>
    
                                        <?php $this->ffw_settings_sidebar(); ?>
                                    </div>
                                </div>
                                
                                <div class="tab-pane" id="ffw-ai-faqs">
                                    <div class="ffw-setting-wrapper">
                                        <div class="ffw-setting-form ffw-left-side">
                                            <?php
                                            settings_fields( 'ffw_ai_faqs_settings' );
                                            do_settings_sections( 'ffw_ai_faqs_settings' );
                                            ?>
                                        </div>
    
                                        <?php $this->ffw_settings_sidebar(); ?>
                                    </div>
                                </div>
                            </div>
    
                            <?php submit_button(); ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Setting sidebar.
     *
     * @since 1.4.5
     */
    function ffw_settings_sidebar() {
        ob_start();

        include FFW_FILE_DIR . '/includes/template-parts/sidebar.php';

        return ob_get_contents();
    }

    /**
     * Setting Page Nav Items.
     */
    function ffw_nav_items() {

        //setting options class initialize
        $tabs = $this->ffw_get_options_tabs();
        ?>
        
        <nav>
            <div class="nav nav-tabs nav-tab-wrapper wflg-nav-tab-wrapper" id="nav-tab" role="tablist">
                <?php
                foreach ($tabs as $tab) {
                    ?>
                    <a href="#<?php echo esc_attr($tab['slug']); ?>" 
                        class="<?php echo esc_attr(ffw_array_separator(' ', $tab['class'])); ?>"
                        data-toggle="tab" 
                        role="tab" 
                        aria-selected="true"><?php echo esc_html($tab['title']); ?></a>
                    <?php
                }
                ?>
            </div>

            <div class="ffw-nav-submit">
                <?php submit_button(); ?>
            </div>
        </nav>
        <?php
    }

    /**
     * Get setting tab items.
     *
     * @since  1.1.8
     * @return array
     */
    function ffw_get_options_tabs() {
        $settings = array(
            array(
                'title' => esc_html__( 'General', 'faq-for-woocommerce' ),
                'slug' => 'ffw-general',
                'class' => array('nav-item','nav-link', 'active')
            ),
            array(
                'title' => esc_html__( 'Style', 'faq-for-woocommerce' ),
                'slug' => 'ffw-style',
                'class' => array('nav-item', 'nav-link')
            ),
            array(
                'title' => esc_html__( 'Schema', 'faq-for-woocommerce' ),
                'slug' => 'ffw-schema',
                'class' => array('nav-item', 'nav-link')
            ),
            array(
                'title' => esc_html__( 'Comment', 'faq-for-woocommerce' ),
                'slug' => 'ffw-comment',
                'class' => array('nav-item', 'nav-link')
            ),
            array(
                'title' => esc_html__( 'AI FAQs', 'faq-for-woocommerce' ),
                'slug' => 'ffw-ai-faqs',
                'class' => array('nav-item', 'nav-link')
            ),
        );

        return apply_filters('ffw_options_tabs', $settings);
    }

    /**
	 * Get product categories
	 *
	 * @return array
	 */
	public static function get_product_categories() {

		$args = array(
			'taxonomy'     => 'product_cat',
			'orderby'      => 'name',
			'show_count'   => 0,
			'pad_counts'   => 0,
			'hierarchical' => 1,
			'title_li'     => '',
			'hide_empty'   => 0,
		);

		$all_categories = get_categories( $args );

		$ids   = wp_list_pluck( $all_categories, 'term_id' );
		$names = wp_list_pluck( $all_categories, 'name' );

		$categories = array_combine( $ids, $names );
		$categories = ! empty( $categories ) ? $categories : array();

		return apply_filters( 'ffw_product_categories', $categories );
	}

    /**
	 * Get product tags
	 *
	 * @return array
	 */
	public static function get_product_tags() {

		$args = array(
			'taxonomy'     => 'product_tag',
			'orderby'      => 'name',
			'show_count'   => 0,
			'pad_counts'   => 0,
			'hierarchical' => 1,
			'title_li'     => '',
			'hide_empty'   => 0,
		);

		$all_tags = get_tags( $args );

		$ids   = wp_list_pluck( $all_tags, 'term_id' );
		$names = wp_list_pluck( $all_tags, 'name' );

		$tags = array_combine( $ids, $names );
		$tags = ! empty( $tags ) ? $tags : array();

		return apply_filters( 'ffw_product_tags', $tags );
	}
}

new FAQ_Woocommerce_Settings();