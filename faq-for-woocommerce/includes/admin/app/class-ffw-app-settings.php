<?php
/**
 * Happy FAQs → Settings, as a React page.
 *
 * Every field keeps its key and its stored values in ffw_general_settings:
 * selects store the same "1"/"2" or keyword values, switches store "on" and
 * are removed when off, exactly like the previous form. A save merges into
 * the saved option, so keys this page does not show are kept.
 *
 * Fields added by other plugins with ffw_filter_setting_options and not
 * described here are rendered by their own callback and saved like a form.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_App_Settings class.
 */
class FFW_App_Settings {

    const SLUG   = 'woocommerce-faq';
    const OPTION = 'ffw_general_settings';

    /**
     * Hooks.
     */
    public function __construct() {
        add_filter( 'ffw_admin_app_pages', array( $this, 'register' ) );
        add_action( 'rest_api_init', array( $this, 'routes' ) );
    }

    /**
     * Register the page.
     *
     * @param array $pages Pages.
     * @return array
     */
    public function register( $pages ) {
        $pages[ self::SLUG ] = array(
            'title'  => __( 'Settings', 'faq-for-woocommerce' ),
            'script' => FFW_PLUGIN_URL . '/assets/admin/js/pages/settings.js',
            'style'  => FFW_PLUGIN_URL . '/assets/admin/css/pages/settings.css',
            'data'   => array( $this, 'data' ),
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
            '/settings',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'save' ),
                'permission_callback' => static function () {
                    return current_user_can( 'manage_options' );
                },
                'args'                => array(
                    'values' => array( 'type' => 'object' ),
                    'legacy' => array( 'type' => 'object' ),
                ),
            )
        );
    }

    /* ------------------------------------------------------------------
     * Schema.
     * ------------------------------------------------------------------ */

    /**
     * Enable/Disable style select options.
     *
     * @return array
     */
    protected static function on_off( $on = '1', $off = '2' ) {
        return array( 'on' => $on, 'off' => $off );
    }

    /**
     * Our description of each known field, by field id.
     *
     * @return array
     */
    public static function described_fields() {
        $pro = ffw_is_pro_activated();

        $locations = array(
            'product'  => array(
                'product_tab'                 => __( 'Product tab', 'faq-for-woocommerce' ),
                'top_of_the_product_page'     => __( 'Top of the product page', 'faq-for-woocommerce' ),
                'before_product_summary'      => __( 'Before the product summary', 'faq-for-woocommerce' ),
                'after_add_to_cart_button'    => __( 'After the "Add to cart" button', 'faq-for-woocommerce' ),
                'after_product_meta'          => __( 'After the product meta', 'faq-for-woocommerce' ),
                'after_social_share_buttons'  => __( 'After the social share buttons', 'faq-for-woocommerce' ),
                'bottom_of_the_product_page'  => __( 'Bottom of the product page', 'faq-for-woocommerce' ),
            ),
            'archive'  => array(
                'before_main_content' => __( 'Before the main content', 'faq-for-woocommerce' ),
                'archive_description' => __( 'Before the archive description', 'faq-for-woocommerce' ),
                'before_shop_loop'    => __( 'Before the product grid', 'faq-for-woocommerce' ),
                'after_shop_loop'     => __( 'After the product grid', 'faq-for-woocommerce' ),
                'after_main_content'  => __( 'After the main content', 'faq-for-woocommerce' ),
            ),
            'cart'     => array(
                'before_cart'                    => __( 'Before the cart', 'faq-for-woocommerce' ),
                'after_cart'                     => __( 'After the cart', 'faq-for-woocommerce' ),
                'before_cart_table'              => __( 'Before the cart table', 'faq-for-woocommerce' ),
                'after_cart_table'               => __( 'After the cart table', 'faq-for-woocommerce' ),
                'before_cart_contents'           => __( 'Before the cart contents', 'faq-for-woocommerce' ),
                'after_cart_contents'            => __( 'After the cart contents', 'faq-for-woocommerce' ),
                'before_cart_totals'             => __( 'Before the cart totals', 'faq-for-woocommerce' ),
                'after_cart_totals'              => __( 'After the cart totals', 'faq-for-woocommerce' ),
                'cart_totals_before_shipping'    => __( 'Before shipping', 'faq-for-woocommerce' ),
                'cart_totals_after_shipping'     => __( 'After shipping', 'faq-for-woocommerce' ),
                'cart_totals_before_order_total' => __( 'Before the order total', 'faq-for-woocommerce' ),
                'cart_totals_after_order_total'  => __( 'After the order total', 'faq-for-woocommerce' ),
            ),
            'checkout' => array(
                'before_checkout_form'             => __( 'Before the checkout form', 'faq-for-woocommerce' ),
                'after_checkout_form'              => __( 'After the checkout form', 'faq-for-woocommerce' ),
                'checkout_before_customer_details' => __( 'Before customer details', 'faq-for-woocommerce' ),
                'checkout_after_customer_details'  => __( 'After customer details', 'faq-for-woocommerce' ),
                'before_checkout_billing_form'     => __( 'Before the billing form', 'faq-for-woocommerce' ),
                'after_checkout_billing_form'      => __( 'After the billing form', 'faq-for-woocommerce' ),
                'before_checkout_shipping_form'    => __( 'Before the shipping form', 'faq-for-woocommerce' ),
                'after_checkout_shipping_form'     => __( 'After the shipping form', 'faq-for-woocommerce' ),
                'before_order_notes'               => __( 'Before order notes', 'faq-for-woocommerce' ),
                'after_order_notes'                => __( 'After order notes', 'faq-for-woocommerce' ),
                'checkout_before_order_review'     => __( 'Before the order review', 'faq-for-woocommerce' ),
                'checkout_after_order_review'      => __( 'After the order review', 'faq-for-woocommerce' ),
                'review_order_before_payment'      => __( 'Before payment', 'faq-for-woocommerce' ),
                'review_order_after_payment'       => __( 'After payment', 'faq-for-woocommerce' ),
                'review_order_before_submit'       => __( 'Before the order button', 'faq-for-woocommerce' ),
                'review_order_after_submit'        => __( 'After the order button', 'faq-for-woocommerce' ),
            ),
        );

        $opts = static function ( $list, $locked_except = null ) use ( $pro ) {
            $out = array();
            foreach ( $list as $value => $label ) {
                $out[] = array(
                    'value'  => $value,
                    'label'  => $label,
                    'locked' => ! $pro && null !== $locked_except && $value !== $locked_except,
                );
            }
            return $out;
        };

        $show_hide = array(
            array( 'value' => '1', 'label' => __( 'Show', 'faq-for-woocommerce' ) ),
            array( 'value' => '2', 'label' => __( 'Hide', 'faq-for-woocommerce' ) ),
        );

        $fields = array(
            // General.
            'ffw_faq_page'                       => array(
                'type'    => 'page',
                'key'     => 'ffw_faq_page',
                'default' => '0',
                'label'   => __( 'FAQ page', 'faq-for-woocommerce' ),
                'help'    => __( 'Every FAQ is added to this page automatically, with your template. You can also place [ffw_template] on any page.', 'faq-for-woocommerce' ),
            ),
            'ffw_enable_inline_search'           => array(
                'type'    => 'toggle',
                'key'     => 'ffw_enable_inline_search',
                'default' => '2',
                'map'     => self::on_off(),
                'label'   => __( 'Search box above FAQs', 'faq-for-woocommerce' ),
                'help'    => __( 'Filters the FAQs as visitors type. A block can turn it on or off for itself.', 'faq-for-woocommerce' ),
            ),
            'ffw_enable_helpful_votes'           => array(
                'type'    => 'toggle',
                'key'     => 'ffw_enable_helpful_votes',
                'default' => '2',
                'map'     => self::on_off(),
                'label'   => __( '"Was this helpful?" votes', 'faq-for-woocommerce' ),
                'help'    => __( 'Shows Yes / No under each answer. Results appear in the Helpful column of All FAQs. Views are counted either way.', 'faq-for-woocommerce' ),
            ),
            'ffw_display_all_faq_answers'        => array(
                'type'    => 'toggle',
                'key'     => 'ffw_display_all_faq_answers',
                'default' => '2',
                'map'     => self::on_off(),
                'label'   => __( 'Open every answer on load', 'faq-for-woocommerce' ),
                'help'    => __( 'Shows all answers when the page loads, instead of only the questions.', 'faq-for-woocommerce' ),
            ),
            'ffw_expand_collapse_all'            => array(
                'type'    => 'toggle',
                'key'     => 'ffw_expand_collapse_all',
                'default' => '2',
                'map'     => self::on_off(),
                'label'   => __( '"Expand / collapse all" button', 'faq-for-woocommerce' ),
                'help'    => __( 'Opens or closes every FAQ in a list at once.', 'faq-for-woocommerce' ),
            ),
            'ffw_expand_collapse_label'          => array(
                'type'        => 'text',
                'key'         => 'ffw_expand_collapse_label',
                'default'     => __( 'Expand/Collapse All', 'faq-for-woocommerce' ),
                'placeholder' => __( 'Expand/Collapse All', 'faq-for-woocommerce' ),
                'label'       => __( 'Button label', 'faq-for-woocommerce' ),
                'help'        => __( 'Text on the expand / collapse all button.', 'faq-for-woocommerce' ),
                'dimUnless'   => array( 'ffw_expand_collapse_all', '1' ),
            ),
            'ffw_enable_rtl'                     => array(
                'type'    => 'select',
                'key'     => 'ffw_rtl_mode',
                'default' => 'auto',
                'options' => array(
                    array( 'value' => 'auto', 'label' => __( 'Automatic (follow the site language)', 'faq-for-woocommerce' ) ),
                    array( 'value' => 'always', 'label' => __( 'Always right to left', 'faq-for-woocommerce' ) ),
                    array( 'value' => 'never', 'label' => __( 'Never', 'faq-for-woocommerce' ) ),
                ),
                'label'   => __( 'Right to left (RTL)', 'faq-for-woocommerce' ),
                'help'    => __( 'For Arabic, Hebrew, Persian and other right-to-left languages.', 'faq-for-woocommerce' ),
            ),
            'ffw_before_faq'                     => array(
                'type'  => 'richtext',
                'key'   => 'ffw_before_faq',
                'label' => __( 'Before the FAQs', 'faq-for-woocommerce' ),
                'help'  => __( 'Shown just above the first FAQ of a list.', 'faq-for-woocommerce' ),
            ),
            'ffw_after_faq'                      => array(
                'type'  => 'richtext',
                'key'   => 'ffw_after_faq',
                'label' => __( 'After the FAQs', 'faq-for-woocommerce' ),
                'help'  => __( 'Shown just below the last FAQ of a list.', 'faq-for-woocommerce' ),
            ),
            'ffw_editor'                         => array(
                'type'    => 'segment',
                'key'     => 'ffw_editor',
                'default' => '1',
                'options' => array(
                    array( 'value' => '1', 'label' => __( 'Block editor', 'faq-for-woocommerce' ) ),
                    array( 'value' => '2', 'label' => __( 'Classic editor', 'faq-for-woocommerce' ) ),
                ),
                'label'   => __( 'Answer editor', 'faq-for-woocommerce' ),
                'help'    => __( 'The editor used to write FAQ answers.', 'faq-for-woocommerce' ),
            ),
            'ffw_set_role'                       => array(
                'type'    => 'roles',
                'key'     => 'ffw_set_role',
                'default' => array( 'administrator' ),
                'label'   => __( 'Who can manage FAQs', 'faq-for-woocommerce' ),
                'help'    => __( 'User roles that can open the FAQ screens.', 'faq-for-woocommerce' ),
            ),
            'ffw_hide_general_shortcode_preview' => array(
                'type'    => 'toggle',
                'key'     => 'ffw_hide_general_shortcode_preview',
                'default' => '1',
                'map'     => self::on_off(),
                'label'   => __( 'Show [ffw_template] lists', 'faq-for-woocommerce' ),
                'help'    => __( 'Turn off to hide every FAQ list placed with the [ffw_template] shortcode, for example while you rework them.', 'faq-for-woocommerce' ),
            ),

            // WooCommerce.
            'ffw_tab_label'                      => array(
                'type'        => 'text',
                'key'         => 'ffw_tab_label',
                'default'     => '',
                'placeholder' => __( 'FAQs', 'faq-for-woocommerce' ),
                'label'       => __( 'Tab title', 'faq-for-woocommerce' ),
                'help'        => __( 'Title of the FAQ tab on product pages.', 'faq-for-woocommerce' ),
            ),
            'ffw_display_location'               => array(
                'type'    => 'select',
                'key'     => 'ffw_display_location',
                'default' => 'product_tab',
                'options' => $opts( $locations['product'], 'product_tab' ),
                'label'   => __( 'Where FAQs show', 'faq-for-woocommerce' ),
                'help'    => __( 'Place of the FAQs on product pages.', 'faq-for-woocommerce' ),
            ),
            'ffw_tab_priority'                   => array(
                'type'        => 'number',
                'key'         => 'ffw_tab_priority',
                'default'     => '50',
                'placeholder' => '50',
                'step'        => 1,
                'label'       => __( 'Tab order', 'faq-for-woocommerce' ),
                'help'        => __( 'Lower numbers move the FAQ tab left. WooCommerce uses 10 (Description), 20 (Additional information) and 30 (Reviews).', 'faq-for-woocommerce' ),
            ),
            'ffw_faq_counter_in_front'           => array(
                'type'    => 'toggle',
                'key'     => 'ffw_faq_counter_in_front',
                'default' => '2',
                'map'     => self::on_off(),
                'label'   => __( 'FAQ count in the tab title', 'faq-for-woocommerce' ),
                'help'    => __( 'For example "FAQs (4)".', 'faq-for-woocommerce' ),
            ),
            'ffw_hide_faq_number_for_product'    => array(
                'type'    => 'toggle',
                'key'     => 'ffw_hide_faq_number_for_product',
                'default' => '1',
                'map'     => self::on_off(),
                'label'   => __( 'FAQs column in the product list', 'faq-for-woocommerce' ),
                'help'    => __( 'Shows how many FAQs each product has, in Products → All Products.', 'faq-for-woocommerce' ),
            ),
            'ffw_hide_dynamic_shortcode_preview' => array(
                'type'    => 'toggle',
                'key'     => 'ffw_hide_dynamic_shortcode_preview',
                'default' => '1',
                'map'     => self::on_off(),
                'label'   => __( 'Show product shortcode lists', 'faq-for-woocommerce' ),
                'help'    => __( 'Turn off to hide FAQ lists placed with [ffw_template dynamic_post=true] on product templates.', 'faq-for-woocommerce' ),
            ),
            'ffw_enable_archive_pages_faqs'      => array(
                'type'  => 'switch',
                'key'   => 'enable_archive_pages_faqs',
                'pro'   => true,
                'label' => __( 'FAQs on category and tag pages', 'faq-for-woocommerce' ),
                'help'  => __( 'Shows the FAQs assigned to a product category or tag on its page.', 'faq-for-woocommerce' ),
            ),
            'ffw_display_location_archive'       => array(
                'type'      => 'select',
                'key'       => 'ffw_display_location_archive',
                'default'   => 'before_main_content',
                'pro'       => true,
                'options'   => $opts( $locations['archive'] ),
                'label'     => __( 'Position', 'faq-for-woocommerce' ),
                'dimUnless' => array( 'enable_archive_pages_faqs', 'on' ),
            ),
            'ffw_enable_shop_page_faqs'          => array(
                'type'  => 'switch',
                'key'   => 'enable_shop_page_faqs',
                'pro'   => true,
                'label' => __( 'FAQs on the shop page', 'faq-for-woocommerce' ),
                'help'  => __( 'Shows FAQs set to "Shop page" in their Happy FAQs Panel.', 'faq-for-woocommerce' ),
            ),
            'ffw_display_location_shop'          => array(
                'type'      => 'select',
                'key'       => 'ffw_display_location_shop',
                'default'   => 'before_main_content',
                'pro'       => true,
                'options'   => $opts( $locations['archive'] ),
                'label'     => __( 'Position', 'faq-for-woocommerce' ),
                'dimUnless' => array( 'enable_shop_page_faqs', 'on' ),
            ),
            'ffw_enable_cart_page_faqs'          => array(
                'type'  => 'switch',
                'key'   => 'enable_cart_page_faqs',
                'pro'   => true,
                'label' => __( 'FAQs on the cart page', 'faq-for-woocommerce' ),
                'help'  => __( 'Shows FAQs set to "Cart page" in their Happy FAQs Panel.', 'faq-for-woocommerce' ),
            ),
            'ffw_display_location_cart'          => array(
                'type'      => 'select',
                'key'       => 'ffw_display_location_cart',
                'default'   => 'before_cart',
                'pro'       => true,
                'options'   => $opts( $locations['cart'] ),
                'label'     => __( 'Position', 'faq-for-woocommerce' ),
                'dimUnless' => array( 'enable_cart_page_faqs', 'on' ),
            ),
            'ffw_enable_checkout_page_faqs'      => array(
                'type'  => 'switch',
                'key'   => 'enable_checkout_page_faqs',
                'pro'   => true,
                'label' => __( 'FAQs on the checkout page', 'faq-for-woocommerce' ),
                'help'  => __( 'Shows FAQs set to "Checkout page" in their Happy FAQs Panel.', 'faq-for-woocommerce' ),
            ),
            'ffw_display_location_checkout'      => array(
                'type'      => 'select',
                'key'       => 'ffw_display_location_checkout',
                'default'   => 'before_checkout_form',
                'pro'       => true,
                'options'   => $opts( $locations['checkout'] ),
                'label'     => __( 'Position', 'faq-for-woocommerce' ),
                'dimUnless' => array( 'enable_checkout_page_faqs', 'on' ),
            ),
            'ffw_enable_global_faqs'             => array(
                'type'  => 'switch',
                'key'   => 'enable_global_faqs',
                'pro'   => true,
                'label' => __( 'Global FAQs', 'faq-for-woocommerce' ),
                'help'  => __( 'FAQs marked "Global" in their Happy FAQs Panel show on every product.', 'faq-for-woocommerce' ),
            ),
            'ffw_global_faqs_appearance'         => array(
                'type'      => 'segment',
                'key'       => 'ffw_global_faqs_appearance',
                'default'   => '1',
                'options'   => array(
                    array( 'value' => '1', 'label' => __( 'Before product FAQs', 'faq-for-woocommerce' ) ),
                    array( 'value' => '2', 'label' => __( 'After product FAQs', 'faq-for-woocommerce' ) ),
                ),
                'label'     => __( 'Global FAQs order', 'faq-for-woocommerce' ),
                'help'      => __( 'Where global FAQs go among a product\'s own FAQs.', 'faq-for-woocommerce' ),
                'dimUnless' => $pro ? array( 'enable_global_faqs', 'on' ) : null,
            ),
            'ffw_enable_dynamic_attributes'      => array(
                'type'  => 'switch',
                'key'   => 'enable_dynamic_attributes',
                'pro'   => true,
                'label' => __( 'Product attributes in answers', 'faq-for-woocommerce' ),
                'help'  => __( 'Answers can show a product attribute, such as its size or material. Set them in the Happy FAQs Panel.', 'faq-for-woocommerce' ),
            ),
            'ffw_enable_search_box'              => array(
                'type'  => 'switch',
                'key'   => 'enable_search_box',
                'pro'   => true,
                'label' => __( 'Search box in the product FAQ tab', 'faq-for-woocommerce' ),
                'help'  => __( 'Customers can search a product\'s FAQs.', 'faq-for-woocommerce' ),
            ),
            'ffw_search_box_position'            => array(
                'type'      => 'segment',
                'key'       => 'search_box_position',
                'array'     => true,
                'default'   => 'left',
                'pro'       => true,
                'options'   => array(
                    array( 'value' => 'left', 'label' => __( 'Left', 'faq-for-woocommerce' ) ),
                    array( 'value' => 'center', 'label' => __( 'Center', 'faq-for-woocommerce' ) ),
                    array( 'value' => 'right', 'label' => __( 'Right', 'faq-for-woocommerce' ) ),
                ),
                'label'     => __( 'Search box position', 'faq-for-woocommerce' ),
                'dimUnless' => array( 'enable_search_box', 'on' ),
            ),
            'ffw_enable_multi_column_support'    => array(
                'type'  => 'switch',
                'key'   => 'enable_multi_column_support',
                'pro'   => true,
                'label' => __( 'Two-column FAQs on products', 'faq-for-woocommerce' ),
                'help'  => __( 'Splits long product FAQ lists into two columns.', 'faq-for-woocommerce' ),
            ),

            // Questions and forms.
            'ffw_enable_customer_questions'      => array(
                'type'  => 'switch',
                'key'   => 'enable_customer_questions',
                'pro'   => true,
                'label' => __( '"Ask a question" form', 'faq-for-woocommerce' ),
                'help'  => __( 'Visitors can ask a question. You answer it in Customer Questions and can publish it as a FAQ.', 'faq-for-woocommerce' ),
            ),
            'ffw_display_customer_questions'     => array(
                'type'  => 'switch',
                'key'   => 'display_customer_questions',
                'pro'   => true,
                'label' => __( 'Show answered questions', 'faq-for-woocommerce' ),
                'help'  => __( 'Answered questions show on the product or page where they were asked. Pending questions never show.', 'faq-for-woocommerce' ),
            ),
            'ffw_contact_form_suggestions'       => array(
                'type'  => 'switch',
                'key'   => 'contact_form_suggestions',
                'pro'   => true,
                'label' => __( 'FAQ suggestions in contact forms', 'faq-for-woocommerce' ),
                'help'  => __( 'While visitors type in WPForms, Contact Form 7 or Fluent Forms, matching FAQs show under the message. Tickets avoided are counted in Analytics.', 'faq-for-woocommerce' ),
                'info'  => self::contact_forms_found(),
            ),
            'ffw_contact_form_suggestions_heading' => array(
                'type'        => 'text',
                'key'         => 'contact_form_suggestions_heading',
                'default'     => '',
                'pro'         => true,
                'placeholder' => __( 'These answers might help', 'faq-for-woocommerce' ),
                'label'       => __( 'Suggestions heading', 'faq-for-woocommerce' ),
                'help'        => __( 'Shown above the suggested FAQs.', 'faq-for-woocommerce' ),
                'dimUnless'   => array( 'contact_form_suggestions', 'on' ),
            ),
            'ffw_lms_faqs_display'               => array(
                'type'    => 'select',
                'key'     => 'lms_faqs_display',
                'default' => 'auto',
                'pro'     => true,
                'options' => array(
                    array( 'value' => 'auto', 'label' => __( 'After the course or lesson content', 'faq-for-woocommerce' ) ),
                    array( 'value' => 'shortcode', 'label' => __( 'Only where [ffw_lms_faqs] is placed', 'faq-for-woocommerce' ) ),
                ),
                'label'   => __( 'Course and lesson FAQs', 'faq-for-woocommerce' ),
                'help'    => __( 'For Tutor LMS, LearnDash and LearnPress.', 'faq-for-woocommerce' ),
            ),

            // SEO.
            'ffw_disable_schema'                 => array(
                'type'    => 'toggle',
                'key'     => 'ffw_disable_schema',
                'default' => '1',
                'map'     => self::on_off(),
                'label'   => __( 'FAQ schema (FAQPage)', 'faq-for-woocommerce' ),
                'help'    => __( 'Adds Google FAQ structured data to pages and products that show FAQs. Duplicate questions are left out.', 'faq-for-woocommerce' ),
            ),
            'ffw_schema_description_type'        => array(
                'type'      => 'segment',
                'key'       => 'ffw_schema_description_type',
                'default'   => '1',
                'options'   => array(
                    array( 'value' => '1', 'label' => __( 'Keep HTML', 'faq-for-woocommerce' ) ),
                    array( 'value' => '2', 'label' => __( 'Plain text', 'faq-for-woocommerce' ) ),
                ),
                'label'     => __( 'Answers in the schema', 'faq-for-woocommerce' ),
                'help'      => __( 'Google allows basic HTML such as links and lists in answers.', 'faq-for-woocommerce' ),
                'dimUnless' => array( 'ffw_disable_schema', '1' ),
            ),
            'ffw_post_index'                     => array(
                'type'    => 'segment',
                'key'     => 'ffw_post_index',
                'default' => '1',
                'options' => array(
                    array( 'value' => '1', 'label' => __( 'Noindex', 'faq-for-woocommerce' ) ),
                    array( 'value' => '2', 'label' => __( 'Index', 'faq-for-woocommerce' ) ),
                ),
                'label'   => __( 'Single FAQ pages in search engines', 'faq-for-woocommerce' ),
                'help'    => __( 'Each FAQ also has its own page. Noindex keeps those pages out of search results, so your FAQ pages rank instead.', 'faq-for-woocommerce' ),
            ),

            // Style.
            'ffw_width'                          => array(
                'type'        => 'number',
                'key'         => 'ffw_width',
                'default'     => '',
                'placeholder' => '100',
                'min'         => 1,
                'max'         => 100,
                'suffix'      => '%',
                'label'       => __( 'Width', 'faq-for-woocommerce' ),
                'help'        => __( 'Width of FAQ lists, as a percentage of their container.', 'faq-for-woocommerce' ),
            ),
            'ffw_icon_set'                       => array(
                'type'    => 'icons',
                'key'     => 'ffw_icon_set',
                'default' => 'default',
                'label'   => __( 'Toggle icon', 'faq-for-woocommerce' ),
                'help'    => __( 'The open and close icon. "Layout default" keeps your layout\'s icon. Blocks and widgets can choose their own.', 'faq-for-woocommerce' ),
                'stacked' => true,
            ),
            'ffw_icon_style'                     => array(
                'type'  => 'icon_style',
                'keys'  => array( 'ffw_icon_position', 'ffw_icon_size', 'ffw_icon_color' ),
                'label' => __( 'Icon position, size and color', 'faq-for-woocommerce' ),
                'help'  => __( 'Leave the color empty to match the question text.', 'faq-for-woocommerce' ),
            ),
            'ffw_question_text_color'            => array(
                'type'  => 'color',
                'key'   => 'ffw_question_text_color',
                'label' => __( 'Question text', 'faq-for-woocommerce' ),
            ),
            'ffw_question_bg_color'              => array(
                'type'  => 'color',
                'key'   => 'ffw_question_bg_color',
                'label' => __( 'Question background', 'faq-for-woocommerce' ),
            ),
            'ffw_question_bg_secondary_color'    => array(
                'type'  => 'color',
                'key'   => 'ffw_question_bg_secondary_color',
                'label' => __( 'Question background, second color', 'faq-for-woocommerce' ),
                'help'  => __( 'Used by the Pop layout only.', 'faq-for-woocommerce' ),
            ),
            'ffw_question_border_color'          => array(
                'type'  => 'color',
                'key'   => 'ffw_question_border_color',
                'label' => __( 'Question border', 'faq-for-woocommerce' ),
            ),
            'ffw_question_font_size'             => array(
                'type'  => 'range',
                'key'   => 'ffw_question_font_size',
                'min'   => 0,
                'max'   => 50,
                'label' => __( 'Question font size', 'faq-for-woocommerce' ),
            ),
            'ffw_answer_text_color'              => array(
                'type'  => 'color',
                'key'   => 'ffw_answer_text_color',
                'label' => __( 'Answer text', 'faq-for-woocommerce' ),
            ),
            'ffw_answer_bg_color'                => array(
                'type'  => 'color',
                'key'   => 'ffw_answer_bg_color',
                'label' => __( 'Answer background', 'faq-for-woocommerce' ),
            ),
            'ffw_answer_border_color'            => array(
                'type'  => 'color',
                'key'   => 'ffw_answer_border_color',
                'label' => __( 'Answer border', 'faq-for-woocommerce' ),
            ),
            'ffw_custom_css'                     => array(
                'type'    => 'code',
                'key'     => 'ffw_custom_css',
                'label'   => __( 'Custom CSS', 'faq-for-woocommerce' ),
                'help'    => __( 'Loaded after the plugin styles, so it wins.', 'faq-for-woocommerce' ),
                'stacked' => true,
            ),

            // AI.
            'ffw_ai_provider'                    => array(
                'type'    => 'select',
                'key'     => 'ffw_ai_provider',
                'default' => class_exists( 'FAQ_Woocommerce_AI' ) ? FAQ_Woocommerce_AI::preference() : 'auto',
                'options' => array(
                    array( 'value' => 'auto', 'label' => __( 'Automatic (recommended)', 'faq-for-woocommerce' ) ),
                    array( 'value' => 'wordpress', 'label' => __( 'WordPress AI only', 'faq-for-woocommerce' ) ),
                    array( 'value' => 'openai', 'label' => __( 'My OpenAI key only', 'faq-for-woocommerce' ) ),
                ),
                'label'   => __( 'AI provider', 'faq-for-woocommerce' ),
                'help'    => __( 'Automatic uses the AI connected in WordPress (Settings → Connectors) when there is one, otherwise your OpenAI key.', 'faq-for-woocommerce' ),
                'status'  => self::ai_status(),
            ),
            'ffw_ai_faqs_models'                 => array(
                'type'    => 'select',
                'key'     => 'ffw_ai_faqs_model',
                'default' => class_exists( 'FAQ_Woocommerce_AI' ) ? FAQ_Woocommerce_AI::DEFAULT_MODEL : 'gpt-4o-mini',
                'options' => self::ai_models(),
                'label'   => __( 'OpenAI model', 'faq-for-woocommerce' ),
                'help'    => __( 'Used with your OpenAI key.', 'faq-for-woocommerce' ),
            ),
            'ffw_ai_faqs_api_key'                => array(
                'type'        => 'secret',
                'key'         => 'ffw_ai_faqs_api_key',
                'default'     => '',
                'placeholder' => 'sk-…',
                'label'       => __( 'OpenAI API key', 'faq-for-woocommerce' ),
                'help'        => __( 'Only needed when WordPress AI is not connected.', 'faq-for-woocommerce' ),
                'link'        => array( 'url' => 'https://platform.openai.com/api-keys', 'label' => __( 'Get an OpenAI key', 'faq-for-woocommerce' ) ),
            ),
            'ffw_ai_faqs_max_token'              => array(
                'type'        => 'number',
                'key'         => 'ffw_ai_faqs_max_token',
                'default'     => '2000',
                'placeholder' => '2000',
                'min'         => 100,
                'step'        => 100,
                'label'       => __( 'Max tokens', 'faq-for-woocommerce' ),
                'help'        => __( 'Upper limit for one generation. Higher allows longer answers.', 'faq-for-woocommerce' ),
            ),
        );

        // Comments: selects.
        $fields['ffw_comments_on'] = array(
            'type'    => 'toggle',
            'key'     => 'ffw_comment_on',
            'default' => '1',
            'map'     => self::on_off( '2', '1' ),
            'label'   => __( 'Comments on FAQs', 'faq-for-woocommerce' ),
            'help'    => __( 'Visitors can comment under each answer.', 'faq-for-woocommerce' ),
        );
        $fields['ffw_comments_ordering'] = array(
            'type'      => 'segment',
            'key'       => 'ffw_comments_ordering',
            'default'   => '2',
            'options'   => array(
                array( 'value' => '2', 'label' => __( 'Newest first', 'faq-for-woocommerce' ) ),
                array( 'value' => '1', 'label' => __( 'Oldest first', 'faq-for-woocommerce' ) ),
            ),
            'label'     => __( 'Order', 'faq-for-woocommerce' ),
            'dimUnless' => array( 'ffw_comment_on', '2' ),
        );
        $fields['ffw_comments_avatar'] = array(
            'type'      => 'toggle',
            'key'       => 'ffw_comments_avatar',
            'default'   => '1',
            'map'       => self::on_off(),
            'label'     => __( 'Avatars', 'faq-for-woocommerce' ),
            'dimUnless' => array( 'ffw_comment_on', '2' ),
        );
        $fields['ffw_comments_avatar_style'] = array(
            'type'      => 'segment',
            'key'       => 'ffw_comments_avatar_style',
            'default'   => '1',
            'options'   => array(
                array( 'value' => '1', 'label' => __( 'Circle', 'faq-for-woocommerce' ) ),
                array( 'value' => '2', 'label' => __( 'Square', 'faq-for-woocommerce' ) ),
            ),
            'label'     => __( 'Avatar shape', 'faq-for-woocommerce' ),
            'dimUnless' => array( 'ffw_comment_on', '2' ),
        );

        // Comments: labels, colors and sizes. Empty values fall back to these defaults on the site.
        $texts = array(
            'ffw_comments_section_title'                 => array( __( 'Section title', 'faq-for-woocommerce' ), __( 'Comments', 'faq-for-woocommerce' ) ),
            'ffw_comments_form_title'                    => array( __( 'Form title', 'faq-for-woocommerce' ), __( 'Comment Box', 'faq-for-woocommerce' ) ),
            'ffw_comments_reply_form_title'              => array( __( 'Reply form title', 'faq-for-woocommerce' ), __( 'Write a Reply', 'faq-for-woocommerce' ) ),
            'ffw_comments_reply_button_text'             => array( __( 'Reply button', 'faq-for-woocommerce' ), __( 'Reply', 'faq-for-woocommerce' ) ),
            'ffw_comments_submit_button_text'            => array( __( 'Submit button', 'faq-for-woocommerce' ), __( 'Comment', 'faq-for-woocommerce' ) ),
            'ffw_comments_reply_form_submit_button_text' => array( __( 'Reply submit button', 'faq-for-woocommerce' ), __( 'Reply Comment', 'faq-for-woocommerce' ) ),
        );
        foreach ( $texts as $id => $text ) {
            $fields[ $id ] = array(
                'type'        => 'text',
                'key'         => $id,
                'default'     => $text[1],
                'placeholder' => $text[1],
                'label'       => $text[0],
                'size'        => 'medium',
            );
        }

        $colors = array(
            'ffw_comments_section_title_color'                 => array( __( 'Section title', 'faq-for-woocommerce' ), '#28303d' ),
            'ffw_comments_form_title_color'                    => array( __( 'Form title', 'faq-for-woocommerce' ), '#28303d' ),
            'ffw_comments_reply_form_title_color'              => array( __( 'Reply form title', 'faq-for-woocommerce' ), '#28303d' ),
            'ffw_comments_author_name_color'                   => array( __( 'Author name', 'faq-for-woocommerce' ), '#28303d' ),
            'ffw_comments_date_time_color'                     => array( __( 'Date', 'faq-for-woocommerce' ), '#28303d' ),
            'ffw_comments_content_color'                       => array( __( 'Comment text', 'faq-for-woocommerce' ), '#28303d' ),
            'ffw_comments_reply_button_text_color'             => array( __( 'Reply button text', 'faq-for-woocommerce' ), '#0b0beb' ),
            'ffw_comments_submit_button_text_color'            => array( __( 'Submit button text', 'faq-for-woocommerce' ), '#ffffff' ),
            'ffw_comments_submit_button_bg_color'              => array( __( 'Submit button background', 'faq-for-woocommerce' ), '#008000' ),
            'ffw_comments_form_border_color'                   => array( __( 'Form border', 'faq-for-woocommerce' ), '#008000' ),
            'ffw_comments_reply_form_submit_button_text_color' => array( __( 'Reply submit button text', 'faq-for-woocommerce' ), '#ffffff' ),
            'ffw_comments_reply_form_submit_button_bg_color'   => array( __( 'Reply submit button background', 'faq-for-woocommerce' ), '#008000' ),
            'ffw_comments_reply_form_border_color'             => array( __( 'Reply form border', 'faq-for-woocommerce' ), '#008000' ),
        );
        foreach ( $colors as $id => $color ) {
            $fields[ $id ] = array(
                'type'    => 'color',
                'key'     => $id,
                'default' => $color[1],
                'label'   => $color[0],
            );
        }

        $sizes = array(
            'ffw_comments_form_section_title_font_size'       => array( __( 'Section title', 'faq-for-woocommerce' ), '20px' ),
            'ffw_comments_form_title_font_size'               => array( __( 'Form title', 'faq-for-woocommerce' ), '16px' ),
            'ffw_comments_reply_form_title_font_size'         => array( __( 'Reply form title', 'faq-for-woocommerce' ), '16px' ),
            'ffw_comments_author_name_font_size'              => array( __( 'Author name', 'faq-for-woocommerce' ), '16px' ),
            'ffw_comments_date_time_font_size'                => array( __( 'Date', 'faq-for-woocommerce' ), '14px' ),
            'ffw_comments_content_font_size'                  => array( __( 'Comment text', 'faq-for-woocommerce' ), '18px' ),
            'ffw_comments_reply_button_font_size'             => array( __( 'Reply button', 'faq-for-woocommerce' ), '16px' ),
            'ffw_comments_submit_button_font_size'            => array( __( 'Submit button', 'faq-for-woocommerce' ), '16px' ),
            'ffw_comments_reply_form_submit_button_font_size' => array( __( 'Reply submit button', 'faq-for-woocommerce' ), '16px' ),
        );
        foreach ( $sizes as $id => $size ) {
            $fields[ $id ] = array(
                'type'        => 'text',
                'key'         => $id,
                'default'     => $size[1],
                'placeholder' => $size[1],
                'label'       => $size[0],
                'size'        => 'short',
            );
        }

        /**
         * Filters how the React Settings page shows known fields, by field id.
         *
         * @since 3.0.0
         * @param array $fields Field descriptions.
         */
        return (array) apply_filters( 'ffw_settings_fields', $fields );
    }

    /**
     * Sections, groups and the field ids in each, in order.
     *
     * @return array
     */
    public static function layout() {
        $layout = array(
            array(
                'id'     => 'general',
                'label'  => __( 'General', 'faq-for-woocommerce' ),
                'icon'   => 'settings',
                'source' => 'ffw_settings',
                'groups' => array(
                    array(
                        'title'  => __( 'FAQ page', 'faq-for-woocommerce' ),
                        'text'   => __( 'One page with all your FAQs.', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_faq_page' ),
                    ),
                    array(
                        'title'  => __( 'On the site', 'faq-for-woocommerce' ),
                        'text'   => __( 'How every FAQ list behaves for visitors.', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_enable_inline_search', 'ffw_enable_helpful_votes', 'ffw_display_all_faq_answers', 'ffw_expand_collapse_all', 'ffw_expand_collapse_label', 'ffw_enable_rtl', 'ffw_hide_general_shortcode_preview' ),
                    ),
                    array(
                        'title'  => __( 'Text around the FAQs', 'faq-for-woocommerce' ),
                        'text'   => __( 'Optional content shown with every FAQ list.', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_before_faq', 'ffw_after_faq' ),
                    ),
                    array(
                        'title'  => __( 'Editing and access', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_editor', 'ffw_set_role' ),
                    ),
                ),
            ),
            array(
                'id'     => 'store',
                'label'  => __( 'WooCommerce', 'faq-for-woocommerce' ),
                'icon'   => 'store',
                'source' => 'ffw_settings',
                'groups' => array(
                    array(
                        'title'  => __( 'Product pages', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_display_location', 'ffw_tab_label', 'ffw_tab_priority', 'ffw_faq_counter_in_front', 'ffw_enable_search_box', 'ffw_search_box_position', 'ffw_enable_multi_column_support', 'ffw_enable_dynamic_attributes', 'ffw_hide_dynamic_shortcode_preview' ),
                    ),
                    array(
                        'title'  => __( 'Global FAQs', 'faq-for-woocommerce' ),
                        'text'   => __( 'Answers that apply to every product, such as shipping and returns.', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_enable_global_faqs', 'ffw_global_faqs_appearance' ),
                    ),
                    array(
                        'title'  => __( 'Store pages', 'faq-for-woocommerce' ),
                        'text'   => __( 'FAQs on category, tag, shop, cart and checkout pages.', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_enable_archive_pages_faqs', 'ffw_display_location_archive', 'ffw_enable_shop_page_faqs', 'ffw_display_location_shop', 'ffw_enable_cart_page_faqs', 'ffw_display_location_cart', 'ffw_enable_checkout_page_faqs', 'ffw_display_location_checkout' ),
                    ),
                    array(
                        'title'  => __( 'Admin', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_hide_faq_number_for_product' ),
                    ),
                ),
            ),
            array(
                'id'     => 'engage',
                'label'  => __( 'Questions & forms', 'faq-for-woocommerce' ),
                'icon'   => 'chat',
                'source' => 'ffw_settings',
                'groups' => array(
                    array(
                        'title'  => __( 'Customer questions', 'faq-for-woocommerce' ),
                        'text'   => __( 'Let visitors ask what your FAQs do not answer yet.', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_enable_customer_questions', 'ffw_display_customer_questions' ),
                    ),
                    array(
                        'title'  => __( 'Contact forms', 'faq-for-woocommerce' ),
                        'text'   => __( 'Answer questions before they become tickets.', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_contact_form_suggestions', 'ffw_contact_form_suggestions_heading' ),
                    ),
                    array(
                        'title'  => __( 'Courses', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_lms_faqs_display' ),
                    ),
                ),
            ),
            array(
                'id'      => 'style',
                'label'   => __( 'Style', 'faq-for-woocommerce' ),
                'icon'    => 'palette',
                'source'  => 'ffw_style_settings',
                'preview' => true,
                'groups'  => array(
                    array(
                        'title'  => __( 'Layout and icon', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_width', 'ffw_icon_set', 'ffw_icon_style' ),
                    ),
                    array(
                        'title'  => __( 'Questions', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_question_text_color', 'ffw_question_bg_color', 'ffw_question_bg_secondary_color', 'ffw_question_border_color', 'ffw_question_font_size' ),
                    ),
                    array(
                        'title'  => __( 'Answers', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_answer_text_color', 'ffw_answer_bg_color', 'ffw_answer_border_color' ),
                    ),
                    array(
                        'title'  => __( 'Custom CSS', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_custom_css' ),
                    ),
                ),
            ),
            array(
                'id'     => 'seo',
                'label'  => __( 'SEO & schema', 'faq-for-woocommerce' ),
                'icon'   => 'globe',
                'source' => 'ffw_schema_settings',
                'groups' => array(
                    array(
                        'title'  => __( 'Structured data', 'faq-for-woocommerce' ),
                        'text'   => __( 'Helps Google understand your FAQs.', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_disable_schema', 'ffw_schema_description_type' ),
                    ),
                    array(
                        'title'  => __( 'Indexing', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_post_index' ),
                    ),
                ),
            ),
            array(
                'id'     => 'comments',
                'label'  => __( 'Comments', 'faq-for-woocommerce' ),
                'icon'   => 'chat',
                'source' => 'ffw_comment_settings',
                'groups' => array(
                    array(
                        'title'  => __( 'Comments', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_comments_on', 'ffw_comments_ordering', 'ffw_comments_avatar', 'ffw_comments_avatar_style' ),
                    ),
                    array(
                        'title'     => __( 'Labels', 'faq-for-woocommerce' ),
                        'collapsed' => true,
                        'fields'    => array( 'ffw_comments_section_title', 'ffw_comments_form_title', 'ffw_comments_reply_form_title', 'ffw_comments_reply_button_text', 'ffw_comments_submit_button_text', 'ffw_comments_reply_form_submit_button_text' ),
                    ),
                    array(
                        'title'     => __( 'Colors', 'faq-for-woocommerce' ),
                        'collapsed' => true,
                        'fields'    => array( 'ffw_comments_section_title_color', 'ffw_comments_form_title_color', 'ffw_comments_reply_form_title_color', 'ffw_comments_author_name_color', 'ffw_comments_date_time_color', 'ffw_comments_content_color', 'ffw_comments_reply_button_text_color', 'ffw_comments_submit_button_text_color', 'ffw_comments_submit_button_bg_color', 'ffw_comments_form_border_color', 'ffw_comments_reply_form_submit_button_text_color', 'ffw_comments_reply_form_submit_button_bg_color', 'ffw_comments_reply_form_border_color' ),
                    ),
                    array(
                        'title'     => __( 'Font sizes', 'faq-for-woocommerce' ),
                        'collapsed' => true,
                        'fields'    => array( 'ffw_comments_form_section_title_font_size', 'ffw_comments_form_title_font_size', 'ffw_comments_reply_form_title_font_size', 'ffw_comments_author_name_font_size', 'ffw_comments_date_time_font_size', 'ffw_comments_content_font_size', 'ffw_comments_reply_button_font_size', 'ffw_comments_submit_button_font_size', 'ffw_comments_reply_form_submit_button_font_size' ),
                    ),
                ),
            ),
            array(
                'id'     => 'ai',
                'label'  => __( 'AI', 'faq-for-woocommerce' ),
                'icon'   => 'sparkle',
                'source' => 'ffw_ai_faqs_settings',
                'groups' => array(
                    array(
                        'title'  => __( 'AI FAQs', 'faq-for-woocommerce' ),
                        'text'   => __( 'Generate FAQs from your products and pages, and improve answers.', 'faq-for-woocommerce' ),
                        'fields' => array( 'ffw_ai_provider', 'ffw_ai_faqs_models', 'ffw_ai_faqs_api_key', 'ffw_ai_faqs_max_token' ),
                    ),
                ),
            ),
        );

        /**
         * Filters the sections and groups of the React Settings page.
         *
         * @since 3.0.0
         * @param array $layout Sections.
         */
        return (array) apply_filters( 'ffw_settings_layout', $layout );
    }

    /* ------------------------------------------------------------------
     * Page data.
     * ------------------------------------------------------------------ */

    /**
     * The settings object, with the Settings class loaded.
     *
     * @return FAQ_Woocommerce_Settings|null
     */
    protected static function settings_object() {
        if ( ! class_exists( 'FAQ_Woocommerce_Settings' ) ) {
            $file = FFW_ABSPATH . '/includes/admin/class-faq-woocommerce-settings.php';
            if ( file_exists( $file ) ) {
                include_once $file;
            }
        }
        return class_exists( 'FAQ_Woocommerce_Settings' ) ? FAQ_Woocommerce_Settings::instance() : null;
    }

    /**
     * Boot data for the page.
     *
     * @return array
     */
    public function data() {
        $settings = self::settings_object();
        $options  = get_option( self::OPTION );
        $options  = is_array( $options ) ? $options : array();
        $pro      = ffw_is_pro_activated();

        // The rich text fields use the WordPress editor.
        wp_enqueue_editor();
        wp_enqueue_media();
        FAQ_Woocommerce_Icons::register_style();
        wp_enqueue_style( 'ffw-toggle-icons' );

        $described = self::described_fields();
        $registered = $settings ? $settings->ffw_get_setting_options() : array();

        // Every registered field id, with the option group it came from.
        $field_group = array();
        $field_meta  = array();
        foreach ( $registered as $group => $sections ) {
            foreach ( (array) $sections as $section ) {
                if ( empty( $section['fields'] ) || ! is_array( $section['fields'] ) ) {
                    continue;
                }
                foreach ( $section['fields'] as $field ) {
                    if ( empty( $field['id'] ) || isset( $field_group[ $field['id'] ] ) ) {
                        continue;
                    }
                    $field_group[ $field['id'] ] = $group;
                    $field_meta[ $field['id'] ]  = $field;
                }
            }
        }

        $fields = array();
        foreach ( $field_meta as $id => $field ) {
            $is_pro_region = isset( $field['region'] ) && 'free' !== $field['region'];

            if ( isset( $described[ $id ] ) ) {
                $entry = $described[ $id ];
                // A Pro field in the free plugin is shown locked and never saved.
                $entry['locked'] = ! empty( $entry['pro'] ) && ! $pro;
                $entry['id']     = $id;
                $fields[ $id ]   = $entry;
                continue;
            }

            $object   = $is_pro_region ? ffw_get_setting_instance() : $settings;
            $callback = isset( $field['callback'] ) ? $field['callback'] : '';
            $html     = '';
            if ( $object && $callback && is_callable( array( $object, $callback ) ) ) {
                ob_start();
                call_user_func( array( $object, $callback ) );
                $html = (string) ob_get_clean();
            }

            $fields[ $id ] = array(
                'id'     => $id,
                'type'   => 'legacy',
                'label'  => wp_strip_all_tags( isset( $field['label'] ) ? $field['label'] : $id ),
                'html'   => $html,
                'source' => $field_group[ $id ],
            );
        }

        // Sections: our groups, then anything else that was registered.
        $layout = self::layout();
        $placed = array();
        foreach ( $layout as $s => $section ) {
            foreach ( $section['groups'] as $g => $group ) {
                $ids = array();
                foreach ( $group['fields'] as $id ) {
                    if ( isset( $fields[ $id ] ) && ! isset( $placed[ $id ] ) ) {
                        $ids[]         = $id;
                        $placed[ $id ] = true;
                    }
                }
                $layout[ $s ]['groups'][ $g ]['fields'] = $ids;
            }
        }

        $extra = array();
        foreach ( $fields as $id => $field ) {
            if ( isset( $placed[ $id ] ) ) {
                continue;
            }
            $source = isset( $field_group[ $id ] ) ? $field_group[ $id ] : 'ffw_settings';
            $extra[ $source ][] = $id;
        }

        foreach ( $extra as $source => $ids ) {
            $target = null;
            foreach ( $layout as $s => $section ) {
                if ( isset( $section['source'] ) && $section['source'] === $source ) {
                    $target = $s;
                    break;
                }
            }
            $group = array(
                'title'  => __( 'More settings', 'faq-for-woocommerce' ),
                'fields' => $ids,
            );
            if ( null === $target ) {
                $layout[] = array(
                    'id'     => sanitize_key( $source ),
                    'label'  => __( 'More', 'faq-for-woocommerce' ),
                    'icon'   => 'grid',
                    'source' => $source,
                    'groups' => array( $group ),
                );
            } else {
                $layout[ $target ]['groups'][] = $group;
            }
        }

        // Drop empty groups and sections.
        foreach ( $layout as $s => $section ) {
            $layout[ $s ]['groups'] = array_values(
                array_filter(
                    $section['groups'],
                    static function ( $group ) {
                        return ! empty( $group['fields'] );
                    }
                )
            );
        }
        $layout = array_values(
            array_filter(
                $layout,
                static function ( $section ) {
                    return ! empty( $section['groups'] );
                }
            )
        );

        $pages = array();
        foreach ( get_pages( array( 'post_status' => 'publish,private,draft' ) ) as $page ) {
            $pages[] = array(
                'id'    => (int) $page->ID,
                'title' => $page->post_title ? $page->post_title : __( '(no title)', 'faq-for-woocommerce' ),
                'state' => 'publish' === $page->post_status ? '' : $page->post_status,
            );
        }

        $roles = array();
        if ( ! function_exists( 'get_editable_roles' ) ) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }
        $names = wp_roles()->get_names();
        foreach ( ffw_get_available_user_roles() as $role ) {
            $roles[] = array(
                'value' => $role,
                'label' => isset( $names[ $role ] ) ? translate_user_role( $names[ $role ] ) : $role,
            );
        }

        $icons = array();
        foreach ( FAQ_Woocommerce_Icons::choices() as $id => $label ) {
            $icons[] = array(
                'value' => $id,
                'label' => $label,
                'svg'   => in_array( $id, array( 'default', 'none' ), true ) ? '' : FAQ_Woocommerce_Icons::svg( $id ),
            );
        }

        return array(
            'values'   => self::values( $options ),
            'fields'   => $fields,
            'layout'   => $layout,
            'pages'    => $pages,
            'roles'    => $roles,
            'icons'    => $icons,
            'pro'      => $pro,
            'woo'      => class_exists( 'WooCommerce' ),
            'faqCount' => (int) wp_count_posts( 'ffw' )->publish,
            'pageUrl'  => admin_url( 'post.php?action=edit&post=' ),
            'viewUrl'  => home_url( '/?page_id=' ),
        );
    }

    /**
     * Saved values, with the same fallbacks the previous screen used.
     *
     * @param array $options Saved option.
     * @return array
     */
    protected static function values( $options ) {
        // RTL: older sites stored a Pro switch instead of a mode.
        if ( empty( $options['ffw_rtl_mode'] ) ) {
            $options['ffw_rtl_mode'] = function_exists( 'ffw_is_setting_enabled' ) && ffw_is_setting_enabled( $options, 'enable_rtl' ) ? 'always' : 'auto';
        }

        // Empty comment fields show their defaults, like before.
        foreach ( self::described_fields() as $field ) {
            if ( isset( $field['key'], $field['default'] ) && 0 === strpos( $field['key'], 'ffw_comment' ) && isset( $options[ $field['key'] ] ) && '' === $options[ $field['key'] ] ) {
                unset( $options[ $field['key'] ] );
            }
        }

        return (object) $options;
    }

    /* ------------------------------------------------------------------
     * Save.
     * ------------------------------------------------------------------ */

    /**
     * Save: merge into the saved option, then sanitize like the form.
     *
     * values: { key: value } for the fields this page draws; null removes
     * the key (a switch turned off). legacy: { names: [], pairs: [[name, value]] }
     * from fields drawn by other plugins.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function save( $request ) {
        $values = $request->get_param( 'values' );
        $legacy = $request->get_param( 'legacy' );
        $values = is_array( $values ) ? $values : array();
        $legacy = is_array( $legacy ) ? $legacy : array();

        $options = get_option( self::OPTION );
        $options = is_array( $options ) ? $options : array();
        $allowed = self::writable_keys();

        foreach ( $values as $key => $value ) {
            $key = (string) $key;
            if ( ! isset( $allowed[ $key ] ) ) {
                continue;
            }
            if ( null === $value ) {
                unset( $options[ $key ] );
                continue;
            }
            if ( is_array( $value ) ) {
                $options[ $key ] = array_values( array_map( 'strval', $value ) );
            } elseif ( is_bool( $value ) ) {
                $options[ $key ] = $value ? 'on' : '';
            } else {
                $options[ $key ] = (string) $value;
            }
        }

        // Fields drawn by other plugins: like a form post of their inputs.
        if ( ! empty( $legacy['names'] ) && is_array( $legacy['names'] ) ) {
            $posted = array();
            foreach ( (array) ( isset( $legacy['pairs'] ) ? $legacy['pairs'] : array() ) as $pair ) {
                if ( ! is_array( $pair ) || 2 !== count( $pair ) ) {
                    continue;
                }
                $name = self::option_key( $pair[0] );
                if ( ! $name ) {
                    continue;
                }
                if ( $name['multiple'] ) {
                    $posted[ $name['key'] ][] = (string) $pair[1];
                } else {
                    $posted[ $name['key'] ] = (string) $pair[1];
                }
            }
            foreach ( $legacy['names'] as $raw ) {
                $name = self::option_key( $raw );
                if ( ! $name ) {
                    continue;
                }
                if ( array_key_exists( $name['key'], $posted ) ) {
                    $options[ $name['key'] ] = $posted[ $name['key'] ];
                } else {
                    unset( $options[ $name['key'] ] );
                }
            }
        }

        $settings = self::settings_object();
        if ( $settings && method_exists( $settings, 'sanitize_general_settings' ) ) {
            $options = $settings->sanitize_general_settings( $options );
        }

        update_option( self::OPTION, $options );

        // Keep the instance in step for anything that reads it in this request.
        if ( $settings ) {
            $settings->options = $options;
        }

        /**
         * Fires after the React Settings page saves.
         *
         * @since 3.0.0
         * @param array $options Saved settings.
         */
        do_action( 'ffw_settings_saved', $options );

        return rest_ensure_response(
            array(
                'values' => self::values( get_option( self::OPTION, array() ) ),
            )
        );
    }

    /**
     * Keys this page may write: the described fields' keys.
     *
     * @return array key => true
     */
    protected static function writable_keys() {
        $keys = array();
        foreach ( self::described_fields() as $field ) {
            if ( ! empty( $field['key'] ) ) {
                $keys[ $field['key'] ] = true;
            }
            if ( ! empty( $field['keys'] ) ) {
                foreach ( $field['keys'] as $key ) {
                    $keys[ $key ] = true;
                }
            }
        }
        return $keys;
    }

    /**
     * Option key from an input name like ffw_general_settings[key] or [key][].
     *
     * @param string $name Input name.
     * @return array|null key, multiple
     */
    protected static function option_key( $name ) {
        if ( ! is_string( $name ) || ! preg_match( '/^' . self::OPTION . '\[([A-Za-z0-9_\-]+)\](\[\])?$/', $name, $match ) ) {
            return null;
        }
        return array(
            'key'      => $match[1],
            'multiple' => ! empty( $match[2] ),
        );
    }

    /* ------------------------------------------------------------------
     * Helpers.
     * ------------------------------------------------------------------ */

    /**
     * Contact form plugins found (Pro).
     *
     * @return string
     */
    protected static function contact_forms_found() {
        if ( ! class_exists( '\HappyDevs\FFW_PRO\Contact_Suggestions' ) || ! method_exists( '\HappyDevs\FFW_PRO\Contact_Suggestions', 'detected_plugins' ) ) {
            return '';
        }
        $found = \HappyDevs\FFW_PRO\Contact_Suggestions::detected_plugins();
        return $found
            /* translators: %s: form plugin names */
            ? sprintf( __( 'Found: %s.', 'faq-for-woocommerce' ), implode( ', ', $found ) )
            : __( 'No supported form plugin is active yet.', 'faq-for-woocommerce' );
    }

    /**
     * AI connection status.
     *
     * @return array|null
     */
    protected static function ai_status() {
        if ( ! class_exists( 'FAQ_Woocommerce_AI' ) ) {
            return null;
        }
        $status = FAQ_Woocommerce_AI::status();
        return array(
            'label'      => isset( $status['label'] ) ? $status['label'] : '',
            'help'       => isset( $status['help'] ) ? $status['help'] : '',
            'ok'         => isset( $status['engine'] ) && in_array( $status['engine'], array( 'wordpress', 'openai' ), true ),
            'connectors' => FAQ_Woocommerce_AI::wordpress_ai_exists() ? admin_url( 'options-connectors.php' ) : '',
        );
    }

    /**
     * OpenAI models, keeping a saved model that is not in the list.
     *
     * @return array
     */
    protected static function ai_models() {
        $models = array(
            'gpt-4o-mini'  => 'GPT-4o mini (fast, low cost)',
            'gpt-4.1-mini' => 'GPT-4.1 mini',
            'gpt-4o'       => 'GPT-4o',
            'gpt-4.1'      => 'GPT-4.1',
        );
        $options = get_option( self::OPTION );
        if ( is_array( $options ) && ! empty( $options['ffw_ai_faqs_model'] ) && ! isset( $models[ $options['ffw_ai_faqs_model'] ] ) ) {
            $models[ $options['ffw_ai_faqs_model'] ] = $options['ffw_ai_faqs_model'];
        }
        $out = array();
        foreach ( $models as $value => $label ) {
            $out[] = array( 'value' => $value, 'label' => $label );
        }
        return $out;
    }
}

new FFW_App_Settings();
