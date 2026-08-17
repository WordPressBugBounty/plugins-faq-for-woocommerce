<?php
/**
 * Divi 5 Happy Custom FAQ Item child module.
 *
 * @package FAQ_Woocommerce
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * FFW_Divi5_Custom_Faq_Item class.
 */
class FFW_Divi5_Custom_Faq_Item implements DependencyInterface {

    /**
     * Load hook.
     *
     * @return void
     */
    public function load() {
        add_action( 'init', array( self::class, 'register_module' ) );
    }

    /**
     * Register from module.json.
     *
     * @return void
     */
    public static function register_module() {
        ModuleRegistration::register_module(
            dirname( __DIR__ ) . '/visual-builder/src/custom-faq-item',
            array(
                'render_callback' => array( self::class, 'render_callback' ),
            )
        );
    }

    /**
     * Child HTML is unused. The parent rebuilds the accordion from innerBlocks.
     *
     * @param array  $attrs    Attrs.
     * @param string $content  Content.
     * @param object $block    Block.
     * @param object $elements Elements.
     * @return string
     */
    public static function render_callback( $attrs, $content, $block, $elements ) {
        return '';
    }
}
