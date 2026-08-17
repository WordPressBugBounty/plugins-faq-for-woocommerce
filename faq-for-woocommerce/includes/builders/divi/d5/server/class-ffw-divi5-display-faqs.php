<?php
/**
 * Divi 5 Display FAQs module.
 *
 * @package FAQ_Woocommerce
 * @since   1.8.18
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Framework\Utility\HTMLUtility;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Element\ElementClassnames;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * FFW_Divi5_Display_Faqs class.
 */
class FFW_Divi5_Display_Faqs implements DependencyInterface {

    /**
     * Load hook.
     *
     * @return void
     */
    public function load() {
        add_action( 'init', array( self::class, 'register_module' ) );
    }

    /**
     * Register the native Divi 5 module from module.json + conversion-outline.json.
     *
     * @return void
     */
    public static function register_module() {
        $module_json_folder_path = dirname( __DIR__ ) . '/visual-builder/src';

        ModuleRegistration::register_module(
            $module_json_folder_path,
            array(
                'render_callback' => array( self::class, 'render_callback' ),
            )
        );
    }

    /**
     * Read a D5 innerContent value.
     *
     * @param array  $attrs   Module attrs.
     * @param string $name    Attribute name.
     * @param mixed  $default Default.
     * @return mixed
     */
    public static function inner_value( $attrs, $name, $default = '' ) {
        if ( isset( $attrs[ $name ]['innerContent']['desktop']['value'] ) ) {
            return $attrs[ $name ]['innerContent']['desktop']['value'];
        }

        return $default;
    }

    /**
     * Map D5 attrs to the shared render helper.
     *
     * @param array $attrs Module attrs.
     * @return array
     */
    public static function attrs_to_args( $attrs ) {
        return array(
            'faq_source'    => self::inner_value( $attrs, 'faqSource', 'all' ),
            'faq_cat'       => self::inner_value( $attrs, 'faqCat', '' ),
            'faq_ids'       => self::inner_value( $attrs, 'faqIds', '' ),
            'group_id'      => self::inner_value( $attrs, 'faqGroup', '' ),
            'product_id'    => self::inner_value( $attrs, 'productId', '' ),
            'layout'        => self::inner_value( $attrs, 'layout', '0' ),
            'display_mode'  => self::inner_value( $attrs, 'displayMode', 'accordion' ),
            'faq_limit'     => self::inner_value( $attrs, 'faqLimit', '0' ),
            'order_by'      => self::inner_value( $attrs, 'orderBy', 'id' ),
            'faq_order'     => self::inner_value( $attrs, 'faqOrder', 'ASC' ),
            'show_search'   => self::inner_value( $attrs, 'showSearch', 'on' ),
            'enable_schema' => self::inner_value( $attrs, 'enableSchema', 'on' ),
            'expand_first'  => self::inner_value( $attrs, 'expandFirst', 'off' ),
        );
    }

    /**
     * Module styles.
     *
     * @param array $args Style args.
     * @return void
     */
    public static function module_styles( $args ) {
        $elements = $args['elements'];

        Style::add(
            array(
                'id'            => $args['id'],
                'name'          => $args['name'],
                'orderIndex'    => $args['orderIndex'],
                'storeInstance' => $args['storeInstance'],
                'styles'        => array(
                    $elements->style(
                        array(
                            'attrName'   => 'module',
                            'styleProps' => array(
                                'disabledOn' => array(
                                    'disabledModuleVisibility' => $args['settings']['disabledModuleVisibility'] ?? null,
                                ),
                            ),
                        )
                    ),
                    $elements->style(
                        array(
                            'attrName' => 'faqQuestion',
                        )
                    ),
                    $elements->style(
                        array(
                            'attrName' => 'faqAnswer',
                        )
                    ),
                ),
            )
        );
    }

    /**
     * Script data.
     *
     * @param array $args Args.
     * @return void
     */
    public static function module_script_data( $args ) {
        $elements = $args['elements'];
        $elements->script_data(
            array(
                'attrName' => 'module',
            )
        );
    }

    /**
     * Classnames.
     *
     * @param array $args Args.
     * @return void
     */
    public static function module_classnames( $args ) {
        $classnames_instance = $args['classnamesInstance'];
        $attrs               = $args['attrs'];

        $classnames_instance->add(
            ElementClassnames::classnames(
                array(
                    'attrs' => $attrs['module']['decoration'] ?? array(),
                )
            )
        );
    }

    /**
     * Frontend HTML.
     *
     * @param array  $attrs    Attrs.
     * @param string $content  Content.
     * @param object $block    Block.
     * @param object $elements Elements.
     * @return string
     */
    public static function render_callback( $attrs, $content, $block, $elements ) {
        $args = self::attrs_to_args( $attrs );
        ffw_enqueue_display_faq_assets( isset( $args['layout'] ) ? absint( $args['layout'] ) : 0 );

        $faq_html = ffw_render_builder_display_faqs( $args );

        $module_inner = HTMLUtility::render(
            array(
                'tag'               => 'div',
                'attributes'        => array(
                    'class' => 'et_pb_module_inner',
                ),
                'childrenSanitizer' => 'et_core_esc_previously',
                'children'          => $faq_html,
            )
        );

        $module_elements = $elements->style_components(
            array(
                'attrName' => 'module',
            )
        );

        $parent = null;
        if ( isset( $block->parsed_block['id'] ) ) {
            $parent = BlockParserStore::get_parent(
                $block->parsed_block['id'],
                $block->parsed_block['storeInstance'] ?? null
            );
        }

        return Module::render(
            array(
                'orderIndex'          => $block->parsed_block['orderIndex'] ?? 0,
                'storeInstance'       => $block->parsed_block['storeInstance'] ?? null,
                'attrs'               => $attrs,
                'elements'            => $elements,
                'id'                  => $block->parsed_block['id'] ?? '',
                'moduleClassName'     => 'ffw_display_faqs',
                'name'                => isset( $block->block_type->name ) ? $block->block_type->name : 'happy-faq/divi-faqs',
                'classnamesFunction'  => array( self::class, 'module_classnames' ),
                'moduleCategory'      => isset( $block->block_type->category ) ? $block->block_type->category : 'module',
                'stylesComponent'     => array( self::class, 'module_styles' ),
                'scriptDataComponent' => array( self::class, 'module_script_data' ),
                'parentAttrs'         => $parent->attrs ?? array(),
                'parentId'            => $parent->id ?? '',
                'parentName'          => $parent->blockName ?? '',
                'children'            => $module_elements . $module_inner,
            )
        );
    }
}
