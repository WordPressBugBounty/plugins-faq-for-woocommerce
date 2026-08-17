<?php
/**
 * Gutenberg blocks.
 *
 * @package FAQ_Woocommerce
 * @since   1.8.18
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FAQ_Woocommerce_Blocks class.
 */
class FAQ_Woocommerce_Blocks {

    /**
     * Constructor.
     */
    public function __construct() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        add_filter( 'block_categories_all', array( $this, 'register_category' ), 10, 2 );
        add_filter( 'block_categories', array( $this, 'register_category' ), 10, 2 );
        add_action( 'init', array( $this, 'register_blocks' ), 20 );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
    }

    /**
     * Happy FAQs block category.
     *
     * @param array $categories Categories.
     * @return array
     */
    public function register_category( $categories ) {
        foreach ( $categories as $category ) {
            if ( isset( $category['slug'] ) && 'happy-faq' === $category['slug'] ) {
                return $categories;
            }
        }

        array_unshift(
            $categories,
            array(
                'slug'  => 'happy-faq',
                'title' => esc_html__( 'Happy FAQs', 'faq-for-woocommerce' ),
                'icon'  => 'editor-help',
            )
        );

        return $categories;
    }

    /**
     * Register the Display FAQs block.
     *
     * @return void
     */
    public function register_blocks() {
        wp_register_script(
            'ffw-display-faqs-editor',
            FFW_PLUGIN_URL . '/includes/blocks/display-faqs/index.js',
            array(
                'wp-blocks',
                'wp-element',
                'wp-i18n',
                'wp-components',
                'wp-block-editor',
                'wp-server-side-render',
            ),
            FFW_VERSION,
            true
        );

        wp_register_style(
            'ffw-display-faqs-style',
            FFW_PLUGIN_URL . '/includes/blocks/display-faqs/style.css',
            array(),
            FFW_VERSION
        );

        wp_localize_script( 'ffw-display-faqs-editor', 'ffwBlockEditor', $this->get_editor_data() );

        register_block_type(
            'happy-faq/display-faqs',
            array(
                'api_version'     => 2,
                'editor_script'   => 'ffw-display-faqs-editor',
                'style'           => 'ffw-display-faqs-style',
                'render_callback' => array( $this, 'render_display_faqs' ),
                'attributes'      => $this->get_block_attributes(),
            )
        );

        wp_register_script(
            'ffw-custom-faqs-editor',
            FFW_PLUGIN_URL . '/includes/blocks/custom-faqs/index.js',
            array(
                'wp-blocks',
                'wp-element',
                'wp-i18n',
                'wp-components',
                'wp-block-editor',
            ),
            FFW_VERSION,
            true
        );

        wp_register_style(
            'ffw-custom-faqs-editor',
            FFW_PLUGIN_URL . '/includes/blocks/custom-faqs/editor.css',
            array(),
            FFW_VERSION
        );

        wp_localize_script(
            'ffw-custom-faqs-editor',
            'ffwCustomFaqsEditor',
            array(
                'layouts' => ffw_get_builder_layout_options( 'list' ),
            )
        );

        register_block_type(
            'happy-faq/custom-faqs',
            array(
                'api_version'     => 2,
                'editor_script'   => 'ffw-custom-faqs-editor',
                'editor_style'    => 'ffw-custom-faqs-editor',
                'style'           => 'ffw-display-faqs-style',
                'render_callback' => array( $this, 'render_custom_faqs' ),
                'attributes'      => $this->get_custom_faqs_attributes(),
            )
        );
    }

    /**
     * Block attributes.
     *
     * @return array
     */
    protected function get_block_attributes() {
        return array(
            'source'       => array(
                'type'    => 'string',
                'default' => 'all',
            ),
            'faqCat'       => array(
                'type'    => 'string',
                'default' => '',
            ),
            'faqIds'       => array(
                'type'    => 'string',
                'default' => '',
            ),
            'productId'    => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'layout'       => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'displayMode'  => array(
                'type'    => 'string',
                'default' => 'accordion',
            ),
            'limit'        => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'orderBy'      => array(
                'type'    => 'string',
                'default' => 'id',
            ),
            'order'        => array(
                'type'    => 'string',
                'default' => 'ASC',
            ),
            'showSearch'   => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'enableSchema' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'expandFirst'  => array(
                'type'    => 'boolean',
                'default' => false,
            ),
            'groupId'      => array(
                'type'    => 'number',
                'default' => 0,
            ),
        );
    }

    /**
     * Happy Custom FAQs block attributes.
     *
     * @return array
     */
    protected function get_custom_faqs_attributes() {
        return array(
            'items'        => array(
                'type'    => 'array',
                'default' => array(
                    array(
                        'id'       => 'starter-1',
                        'question' => '',
                        'answer'   => '',
                    ),
                ),
                'items'   => array(
                    'type' => 'object',
                ),
            ),
            'layout'       => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'displayMode'  => array(
                'type'    => 'string',
                'default' => 'accordion',
            ),
            'showSearch'   => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'enableSchema' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'expandFirst'         => array(
                'type'    => 'boolean',
                'default' => false,
            ),
            'questionColor'       => array(
                'type'    => 'string',
                'default' => '',
            ),
            'questionBgColor'     => array(
                'type'    => 'string',
                'default' => '',
            ),
            'questionHoverColor'  => array(
                'type'    => 'string',
                'default' => '',
            ),
            'questionFontSize'    => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'answerColor'         => array(
                'type'    => 'string',
                'default' => '',
            ),
            'answerBgColor'       => array(
                'type'    => 'string',
                'default' => '',
            ),
            'answerFontSize'      => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'borderColor'         => array(
                'type'    => 'string',
                'default' => '',
            ),
            'borderRadius'        => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'itemSpacing'         => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'customCss'           => array(
                'type'    => 'string',
                'default' => '',
            ),
        );
    }

    /**
     * Data for the block inspector.
     *
     * @return array
     */
    protected function get_editor_data() {
        return apply_filters(
            'ffw_block_editor_data',
            array(
                'wooActive'  => ffw_is_woocommerce_active(),
                'layouts'    => ffw_get_builder_layout_options( 'list' ),
                'categories' => ffw_get_builder_faq_category_options( 'list' ),
                'sources'    => ffw_get_builder_source_options( 'list' ),
                'groups'     => ffw_get_builder_group_options( 'list' ),
            )
        );
    }

    /**
     * Server-side render.
     *
     * @param array $attributes Block attributes.
     * @return string
     */
    public function render_display_faqs( $attributes ) {
        return ffw_render_builder_display_faqs( $attributes );
    }

    /**
     * Server-side render for Happy Custom FAQs.
     *
     * @param array $attributes Block attributes.
     * @return string
     */
    public function render_custom_faqs( $attributes ) {
        return ffw_render_custom_faqs( $attributes );
    }

    /**
     * Layout CSS/JS so the editor preview matches the front.
     *
     * @return void
     */
    public function enqueue_editor_assets() {
        ffw_enqueue_display_faq_assets( 0 );
        wp_enqueue_style( 'ffw_classic_styles' );
        wp_enqueue_style( 'ffw_whitish_styles' );
        wp_enqueue_style( 'ffw_trip_styles' );
        wp_enqueue_style( 'ffw_pop_styles' );
        wp_enqueue_style( 'ffw_basic_styles' );
    }
}

new FAQ_Woocommerce_Blocks();
