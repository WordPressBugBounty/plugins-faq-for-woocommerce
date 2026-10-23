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
        add_action( 'enqueue_block_assets', array( $this, 'enqueue_editor_assets' ) );
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
        // Toggle icon and style panels shared by both blocks.
        wp_register_script(
            'ffw-block-controls',
            FFW_PLUGIN_URL . '/includes/blocks/shared/controls.js',
            array( 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor' ),
            FFW_VERSION,
            true
        );
        wp_localize_script( 'ffw-block-controls', 'ffwBlockIcons', $this->get_icon_editor_data() );

        wp_register_script(
            'ffw-display-faqs-editor',
            FFW_PLUGIN_URL . '/includes/blocks/display-faqs/index.js',
            array(
                'ffw-block-controls',
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

        $this->register_block_from_metadata(
            FFW_FILE_DIR . '/includes/blocks/display-faqs',
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
                'ffw-block-controls',
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

        $this->register_block_from_metadata(
            FFW_FILE_DIR . '/includes/blocks/custom-faqs',
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

        $this->register_block_styles();
    }

    /**
     * Cards, Minimal and Soft styles for both FAQ blocks.
     *
     * @since 3.0.0
     * @return void
     */
    protected function register_block_styles() {
        if ( ! function_exists( 'register_block_style' ) ) {
            return;
        }

        $styles = array(
            'ffw-cards'   => __( 'Cards', 'faq-for-woocommerce' ),
            'ffw-minimal' => __( 'Minimal', 'faq-for-woocommerce' ),
            'ffw-soft'    => __( 'Soft', 'faq-for-woocommerce' ),
        );

        foreach ( array( 'happy-faq/display-faqs', 'happy-faq/custom-faqs' ) as $block ) {
            foreach ( $styles as $name => $label ) {
                register_block_style(
                    $block,
                    array(
                        'name'  => $name,
                        'label' => $label,
                    )
                );
            }
        }
    }

    /**
     * Register a block from its block.json directory.
     *
     * Falls back to the block name string on WordPress versions that cannot
     * read block metadata from a folder path.
     *
     * @param string $block_dir  Absolute path to the block directory.
     * @param string $block_name Block name (namespace/slug).
     * @param array  $args       Block type arguments.
     * @return void
     */
    protected function register_block_from_metadata( $block_dir, $block_name, $args ) {
        if ( function_exists( 'register_block_type_from_metadata' ) ) {
            register_block_type_from_metadata( $block_dir, $args );
            return;
        }

        register_block_type( $block_name, $args );
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
            'perPage'      => array(
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
            'showFilter'   => array(
                'type'    => 'boolean',
                'default' => false,
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
            'questionColor' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'questionBgColor' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'questionHoverColor' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'questionFontSize' => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'answerColor' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'answerBgColor' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'answerFontSize' => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'borderColor' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'borderRadius' => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'itemSpacing' => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'iconSet' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'iconPosition' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'iconColor' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'iconSize' => array(
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
            'iconSet' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'iconPosition' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'iconColor' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'iconSize' => array(
                'type'    => 'number',
                'default' => 0,
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
     * Hooked to enqueue_block_assets (not enqueue_block_editor_assets) so the
     * styles are copied into the iframed block canvas. Frontend rendering still
     * enqueues its own assets from the render callback.
     *
     * @return void
     */
    public function enqueue_editor_assets() {
        if ( ! is_admin() ) {
            return;
        }

        ffw_enqueue_display_faq_assets( 0 );
        wp_enqueue_style( 'ffw_classic_styles' );
        wp_enqueue_style( 'ffw_whitish_styles' );
        wp_enqueue_style( 'ffw_trip_styles' );
        wp_enqueue_style( 'ffw_pop_styles' );
        wp_enqueue_style( 'ffw_basic_styles' );
        wp_enqueue_style( 'ffw-block-styles' );

        // Icon previews in the block panel and in the rendered preview.
        FAQ_Woocommerce_Icons::register_style();
        wp_enqueue_style( 'ffw-toggle-icons' );
        wp_add_inline_style(
            'ffw-toggle-icons',
            '.ffw-block-icon-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px}'
            . '.ffw-block-icon-choice{display:flex;flex-direction:column;align-items:center;gap:6px;min-height:68px;padding:8px 4px;border:1px solid #e0e0e0;border-radius:8px;background:#fff;color:#1e1e1e;cursor:pointer;font-size:11px;line-height:1.25;text-align:center}'
            . '.ffw-block-icon-choice:hover{border-color:#949494}'
            . '.ffw-block-icon-choice.is-selected{border-color:var(--wp-admin-theme-color,#3858e9);box-shadow:0 0 0 1px var(--wp-admin-theme-color,#3858e9)}'
            . '.ffw-block-icon-choice:focus-visible{outline:2px solid var(--wp-admin-theme-color,#3858e9);outline-offset:1px}'
            . '.ffw-block-icon-preview{display:flex;gap:6px;height:22px;align-items:center}.ffw-block-icon-preview .ffw-toggle-icon{--ffw-icon-size:18px}'
            . '.ffw-block-icon-text{height:22px;line-height:22px;font-size:14px;font-weight:600;color:#757575}'
            . '.ffw-block-help{margin:0 0 12px;color:#757575;font-size:12px}'
        );
    }

    /**
     * Icon sets for the block editor.
     *
     * @since 3.0.0
     * @return array
     */
    protected function get_icon_editor_data() {
        $sets = array();
        foreach ( FAQ_Woocommerce_Icons::sets() as $id => $set ) {
            $sets[] = array(
                'value' => $id,
                'label' => $set[0],
                'svg'   => FAQ_Woocommerce_Icons::svg( $id ),
            );
        }

        $choices = FAQ_Woocommerce_Icons::choices();
        $site    = FAQ_Woocommerce_Icons::resolve();

        return array(
            'sets'      => $sets,
            'siteLabel' => isset( $choices[ $site['set'] ] ) ? $choices[ $site['set'] ] : '',
        );
    }
}

new FAQ_Woocommerce_Blocks();
