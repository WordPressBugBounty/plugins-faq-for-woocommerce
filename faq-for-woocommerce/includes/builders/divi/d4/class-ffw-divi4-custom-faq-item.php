<?php
/**
 * Divi 4 Happy Custom FAQ Item child module.
 *
 * @package FAQ_Woocommerce
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_Divi4_Custom_Faq_Item class.
 */
class FFW_Divi4_Custom_Faq_Item extends ET_Builder_Module {

    /**
     * Child slug — must match parent `$child_slug` and D5 `d4Shortcode`.
     *
     * @var string
     */
    public $slug = 'ffw_custom_faq_item';

    /**
     * Child module.
     *
     * @var string
     */
    public $type = 'child';

    /**
     * Layers panel title field.
     *
     * @var string
     */
    public $child_title_var = 'question';

    /**
     * Fallback title field.
     *
     * @var string
     */
    public $child_title_fallback_var = 'question';

    /**
     * Partial VB support.
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
        $this->name = esc_html__( 'FAQ Item', 'faq-for-woocommerce' );

        $this->settings_modal_toggles = array(
            'general' => array(
                'toggles' => array(
                    'main_content' => esc_html__( 'FAQ', 'faq-for-woocommerce' ),
                ),
            ),
        );

        $this->advanced_fields = array(
            'fonts'          => false,
            'background'     => false,
            'borders'        => false,
            'box_shadow'     => false,
            'margin_padding' => false,
            'text'           => false,
            'button'         => false,
            'max_width'      => false,
            'filters'        => false,
            'animation'      => false,
        );
    }

    /**
     * Child fields.
     *
     * @return array
     */
    public function get_fields() {
        return array(
            'question' => array(
                'label'           => esc_html__( 'Question', 'faq-for-woocommerce' ),
                'type'            => 'text',
                'option_category' => 'basic_option',
                'description'     => esc_html__( 'This FAQ stays on this page. It is not added to the FAQ library.', 'faq-for-woocommerce' ),
                'toggle_slug'     => 'main_content',
            ),
            'answer'   => array(
                'label'           => esc_html__( 'Answer', 'faq-for-woocommerce' ),
                'type'            => 'tiny_mce',
                'option_category' => 'basic_option',
                'toggle_slug'     => 'main_content',
            ),
        );
    }

    /**
     * Collect the item for the parent. Output is built by the parent.
     *
     * @param array  $attrs       Shortcode attrs.
     * @param string $content     Content.
     * @param string $render_slug Slug.
     * @return string
     */
    public function render( $attrs, $content = null, $render_slug = '' ) {
        $question = isset( $this->props['question'] ) ? $this->props['question'] : '';
        $answer   = isset( $this->props['answer'] ) ? $this->props['answer'] : '';

        if ( '' === $answer && ! empty( $content ) ) {
            $answer = $content;
        }

        ffw_divi_collect_custom_faq_item( $question, $answer );

        return '';
    }
}

new FFW_Divi4_Custom_Faq_Item();
