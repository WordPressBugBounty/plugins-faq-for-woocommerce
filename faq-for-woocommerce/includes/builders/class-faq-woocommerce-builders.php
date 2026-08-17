<?php
/**
 * Page builder integrations.
 *
 * @package FAQ_Woocommerce
 * @since   1.8.18
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FAQ_Woocommerce_Builders class.
 */
class FAQ_Woocommerce_Builders {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

        add_action( 'elementor/init', array( $this, 'init_elementor' ) );
        add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widgets' ) );
        add_action( 'elementor/elements/categories_registered', array( $this, 'register_elementor_category' ) );
        add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue_builder_assets' ) );
        add_action( 'elementor/preview/enqueue_scripts', array( $this, 'enqueue_builder_assets' ) );
        add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_builder_assets' ) );

        add_action( 'et_builder_ready', array( $this, 'register_divi4_module' ) );
        add_action( 'divi_module_library_modules_dependency_tree', array( $this, 'register_divi5_module' ) );
        add_action( 'divi_visual_builder_assets_before_enqueue_scripts', array( $this, 'enqueue_divi5_visual_builder' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_divi_preview_assets' ), 20 );
    }

    /**
     * REST preview used by the Divi 5 Visual Builder.
     *
     * @return void
     */
    public function register_rest_routes() {
        register_rest_route(
            'happy-faq/v1',
            '/render',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'rest_render' ),
                'permission_callback' => function () {
                    return current_user_can( 'edit_posts' );
                },
            )
        );

        register_rest_route(
            'happy-faq/v1',
            '/render-custom',
            array(
                'methods'             => array( 'GET', 'POST' ),
                'callback'            => array( $this, 'rest_render_custom' ),
                'permission_callback' => function () {
                    return current_user_can( 'edit_posts' );
                },
            )
        );
    }

    /**
     * Render FAQs for builder preview.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function rest_render( $request ) {
        $html = ffw_render_builder_display_faqs( $request->get_params() );

        return rest_ensure_response(
            array(
                'html' => $html,
            )
        );
    }

    /**
     * Render Custom FAQs for builder preview.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function rest_render_custom( $request ) {
        $items_raw = $request->get_param( 'items' );
        $items     = array();

        if ( is_string( $items_raw ) ) {
            $decoded = json_decode( wp_unslash( $items_raw ), true );
            if ( is_array( $decoded ) ) {
                $items = $decoded;
            }
        } elseif ( is_array( $items_raw ) ) {
            $items = $items_raw;
        }

        $html = ffw_render_custom_faqs(
            array(
                'items'         => $items,
                'layout'        => $request->get_param( 'layout' ),
                'display_mode'  => $request->get_param( 'display_mode' ),
                'show_search'   => $request->get_param( 'show_search' ),
                'enable_schema' => $request->get_param( 'enable_schema' ),
                'expand_first'  => $request->get_param( 'expand_first' ),
            )
        );

        return rest_ensure_response(
            array(
                'html' => $html,
            )
        );
    }

    /**
     * Load Elementor widget class.
     *
     * @return void
     */
    public function init_elementor() {
        require_once __DIR__ . '/elementor/class-ffw-display-faqs-widget.php';
        require_once __DIR__ . '/elementor/class-ffw-custom-faqs-widget.php';
    }

    /**
     * Happy FAQs Elementor category.
     *
     * @param \Elementor\Elements_Manager $elements_manager Manager.
     * @return void
     */
    public function register_elementor_category( $elements_manager ) {
        $elements_manager->add_category(
            'happy-faq',
            array(
                'title' => esc_html__( 'Happy FAQs', 'faq-for-woocommerce' ),
                'icon'  => 'eicon-toggle',
            )
        );
    }

    /**
     * Register the Elementor widget.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager Manager.
     * @return void
     */
    public function register_elementor_widgets( $widgets_manager ) {
        if ( ! class_exists( 'FFW_Display_Faqs_Elementor_Widget' ) ) {
            require_once __DIR__ . '/elementor/class-ffw-display-faqs-widget.php';
        }

        if ( ! class_exists( 'FFW_Custom_Faqs_Elementor_Widget' ) ) {
            require_once __DIR__ . '/elementor/class-ffw-custom-faqs-widget.php';
        }

        $widgets_manager->register( new FFW_Display_Faqs_Elementor_Widget() );
        $widgets_manager->register( new FFW_Custom_Faqs_Elementor_Widget() );
    }

    /**
     * Divi 4 module. Also registers on Divi 5 so D4 shortcodes still render
     * until the migrator converts them.
     *
     * @return void
     */
    public function register_divi4_module() {
        if ( ! class_exists( 'ET_Builder_Module' ) ) {
            return;
        }

        require_once __DIR__ . '/divi/d4/class-ffw-divi4-display-faqs.php';
        require_once __DIR__ . '/divi/d4/class-ffw-divi4-custom-faq-item.php';
        require_once __DIR__ . '/divi/d4/class-ffw-divi4-custom-faqs.php';
    }

    /**
     * Divi 5 native module.
     *
     * @param object $dependency_tree Divi 5 dependency tree.
     * @return void
     */
    public function register_divi5_module( $dependency_tree ) {
        if ( ! class_exists( '\ET\Builder\Packages\ModuleLibrary\ModuleRegistration' ) ) {
            return;
        }

        require_once __DIR__ . '/divi/d5/server/class-ffw-divi5-display-faqs.php';
        require_once __DIR__ . '/divi/d5/server/class-ffw-divi5-custom-faq-item.php';
        require_once __DIR__ . '/divi/d5/server/class-ffw-divi5-custom-faqs.php';
        $dependency_tree->add_dependency( new FFW_Divi5_Display_Faqs() );
        $dependency_tree->add_dependency( new FFW_Divi5_Custom_Faq_Item() );
        $dependency_tree->add_dependency( new FFW_Divi5_Custom_Faqs() );
    }

    /**
     * Enqueue the Divi 5 Visual Builder bundle.
     *
     * @return void
     */
    public function enqueue_divi5_visual_builder() {
        if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
            return;
        }

        if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
            return;
        }

        $this->enqueue_builder_assets();

        $script_src = FFW_PLUGIN_URL . '/includes/builders/divi/d5/visual-builder/build/ffw-divi5-display-faqs.js';

        if ( class_exists( '\ET\Builder\VisualBuilder\Assets\PackageBuildManager' ) ) {
            \ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
                array(
                    'name'    => 'ffw-divi5-display-faqs',
                    'version' => FFW_VERSION,
                    'script'  => array(
                        'src'                => $script_src,
                        'deps'               => array(
                            'react',
                            'jquery',
                            'divi-module-library',
                            'wp-hooks',
                            'wp-i18n',
                            'wp-api-fetch',
                            'divi-rest',
                        ),
                        'enqueue_top_window' => false,
                        'enqueue_app_window' => true,
                    ),
                )
            );
            return;
        }

        wp_enqueue_script(
            'ffw-divi5-display-faqs',
            $script_src,
            array( 'react', 'jquery', 'wp-hooks', 'wp-i18n', 'wp-api-fetch' ),
            FFW_VERSION,
            true
        );
    }

    /**
     * FAQ CSS/JS inside Divi Visual Builder.
     *
     * @return void
     */
    public function maybe_enqueue_divi_preview_assets() {
        if ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
            $this->enqueue_builder_assets();
        }
    }

    /**
     * Shared frontend assets for builder canvases.
     *
     * @return void
     */
    public function enqueue_builder_assets() {
        ffw_enqueue_display_faq_assets( 0 );
        wp_enqueue_style( 'ffw_classic_styles' );
        wp_enqueue_style( 'ffw_whitish_styles' );
        wp_enqueue_style( 'ffw_trip_styles' );
        wp_enqueue_style( 'ffw_pop_styles' );
        wp_enqueue_style( 'ffw_basic_styles' );
    }
}

new FAQ_Woocommerce_Builders();
