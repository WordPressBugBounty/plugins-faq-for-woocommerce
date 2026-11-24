<?php
/**
 * Live previews of FAQ lists for the admin pages.
 *
 * Front-end URLs that render in the theme, for logged-in editors only:
 * one [ffw_template] shortcode (Shortcodes and Templates pages), or sample
 * questions with the Style settings being edited (Settings → Style).
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_App_Preview class.
 */
class FFW_App_Preview {

    const VAR    = 'ffw-shortcode-preview';
    const ACTION = 'ffw_shortcode_preview';

    const STYLE_VAR    = 'ffw-style-preview';
    const STYLE_ACTION = 'ffw_style_preview';

    /**
     * Hooks.
     */
    public function __construct() {
        add_action( 'template_redirect', array( $this, 'maybe_render' ) );
        add_action( 'template_redirect', array( $this, 'maybe_render_style' ) );
        add_action( 'rest_api_init', array( $this, 'routes' ) );
    }

    /**
     * Settings → Style: keys the preview can change.
     *
     * @since 3.0.1
     * @return string[]
     */
    public static function style_keys() {
        return array(
            'ffw_width',
            'ffw_icon_set',
            'ffw_icon_position',
            'ffw_icon_size',
            'ffw_icon_color',
            'ffw_question_text_color',
            'ffw_question_bg_color',
            'ffw_question_bg_secondary_color',
            'ffw_question_border_color',
            'ffw_question_font_size',
            'ffw_answer_text_color',
            'ffw_answer_bg_color',
            'ffw_answer_border_color',
            'ffw_custom_css',
        );
    }

    /**
     * Data for the settings page script.
     *
     * @since 3.0.1
     * @return array
     */
    public static function style_data() {
        return array(
            'url'  => add_query_arg(
                array(
                    self::STYLE_VAR => '1',
                    '_wpnonce'      => wp_create_nonce( self::STYLE_ACTION ),
                ),
                home_url( '/' )
            ),
            'keys' => self::style_keys(),
        );
    }

    /**
     * REST route: keep the values being edited and return their CSS.
     *
     * @since 3.0.1
     * @return void
     */
    public function routes() {
        register_rest_route(
            FAQ_Woocommerce_Admin_App::NS,
            '/settings/style-preview',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'style_draft' ),
                'permission_callback' => static function () {
                    return current_user_can( 'manage_options' );
                },
                'args'                => array(
                    'values' => array(
                        'type'     => 'object',
                        'required' => true,
                    ),
                ),
            )
        );
    }

    /**
     * Store the edited style values for this user's preview. Nothing is saved
     * to the settings.
     *
     * @since 3.0.1
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function style_draft( $request ) {
        $values = self::clean_style( (array) $request->get_param( 'values' ) );
        set_transient( self::draft_key(), $values, HOUR_IN_SECONDS );

        $width = isset( $values['ffw_width'] ) ? absint( $values['ffw_width'] ) : 0;
        $size  = isset( $values['ffw_icon_size'] ) ? absint( $values['ffw_icon_size'] ) : 0;

        return rest_ensure_response(
            array(
                'width'  => ( $width > 0 ? min( 100, $width ) : 100 ) . '%',
                'color'  => ffw_sanitize_css_color( isset( $values['ffw_icon_color'] ) ? $values['ffw_icon_color'] : '' ),
                // Smaller sizes use the default, as on the site.
                'size'   => $size >= 10 ? min( 48, $size ) . 'px' : '',
                'css'    => ffw_get_style_settings_css( $values ),
                'custom' => ffw_prepare_custom_css( isset( $values['ffw_custom_css'] ) ? $values['ffw_custom_css'] : '' ),
            )
        );
    }

    /**
     * Only style keys, as text.
     *
     * @param array $values Raw values.
     * @return array
     */
    private static function clean_style( $values ) {
        $clean = array();
        foreach ( self::style_keys() as $key ) {
            if ( ! isset( $values[ $key ] ) || ! is_scalar( $values[ $key ] ) ) {
                continue;
            }
            $value         = (string) $values[ $key ];
            $clean[ $key ] = 'ffw_custom_css' === $key ? wp_strip_all_tags( $value ) : sanitize_text_field( $value );
        }
        return $clean;
    }

    /**
     * Transient name for the current user.
     *
     * @return string
     */
    private static function draft_key() {
        return 'ffw_style_preview_' . get_current_user_id();
    }

    /**
     * Render sample questions with the site's layout, theme and Style settings.
     *
     * @since 3.0.1
     * @return void
     */
    public function maybe_render_style() {
        if ( ! isset( $_GET[ self::STYLE_VAR ] ) ) {
            return;
        }

        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $nonce, self::STYLE_ACTION ) ) {
            wp_die( esc_html__( 'This preview is not available.', 'faq-for-woocommerce' ), '', array( 'response' => 403 ) );
        }

        // Unsaved values, for this request only.
        $draft = isset( $_GET['draft'] ) ? get_transient( self::draft_key() ) : false;
        if ( is_array( $draft ) ) {
            $options = get_option( 'ffw_general_settings' );
            $options = is_array( $options ) ? $options : array();
            $options = array_merge( array_diff_key( $options, array_flip( self::style_keys() ) ), $draft );
            add_filter(
                'option_ffw_general_settings',
                static function () use ( $options ) {
                    return $options;
                }
            );
            FAQ_Woocommerce_Public::instance()->options = $options;
        }

        show_admin_bar( false );
        nocache_headers();
        header( 'X-Robots-Tag: noindex' );

        // Any icon set can be chosen while the preview is open.
        FAQ_Woocommerce_Icons::register_style();
        wp_enqueue_style( 'ffw-toggle-icons' );

        // Render before wp_head() so the layout's styles are enqueued in time.
        $html = ffw_render_custom_faqs(
            array(
                'items'         => array(
                    array(
                        'question' => __( 'How long does shipping take?', 'faq-for-woocommerce' ),
                        'answer'   => '<p>' . __( 'Orders ship within 1–2 business days. Delivery takes 3–5 days in most areas.', 'faq-for-woocommerce' ) . '</p>',
                    ),
                    array(
                        'question' => __( 'Can I return an item?', 'faq-for-woocommerce' ),
                        'answer'   => '<p>' . __( 'Yes. Send it back within 30 days for a full refund.', 'faq-for-woocommerce' ) . '</p>',
                    ),
                    array(
                        'question' => __( 'Do you ship internationally?', 'faq-for-woocommerce' ),
                        'answer'   => '<p>' . __( 'Yes, to most countries. Shipping costs are shown at checkout.', 'faq-for-woocommerce' ) . '</p>',
                    ),
                ),
                'expand_first'  => true,
                'enable_schema' => false,
                'show_search'   => false,
            )
        );
        $origin = wp_parse_url( admin_url() );
        $origin = $origin['scheme'] . '://' . $origin['host'] . ( isset( $origin['port'] ) ? ':' . $origin['port'] : '' );
        ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <?php wp_head(); ?>
    <style>
        html, body { margin: 0 !important; padding: 0 !important; min-height: 0 !important; overflow-x: hidden; }
        body::before, body::after { content: none !important; }
        .ffw-style-preview { box-sizing: border-box; padding: 16px; }
        .ffw-style-preview > .ffw-main-wrapper > br:last-child { display: none; }
    </style>
</head>
<body <?php body_class( 'ffw-style-preview-body' ); ?>>
    <div class="ffw-style-preview">
        <?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- templates escape their own output. ?>
    </div>
    <?php wp_footer(); ?>
    <script>
    ( function () {
        var origin = <?php echo wp_json_encode( $origin ); ?>;
        var box = document.querySelector( '.ffw-style-preview' );
        function size() {
            window.parent.postMessage( { ffwStylePreview: 'size', height: Math.ceil( box.getBoundingClientRect().height ) }, origin );
        }
        function css( id, text ) {
            var tag = document.getElementById( id );
            if ( tag ) {
                tag.textContent = text;
            }
        }
        window.addEventListener( 'message', function ( event ) {
            var message = event.data;
            if ( event.source !== window.parent || event.origin !== origin || ! message || ! message.ffwStylePreview ) {
                return;
            }
            if ( 'reload' === message.ffwStylePreview ) {
                // Not a new entry in the browser history.
                var url = new URL( window.location.href );
                url.searchParams.set( 'draft', '1' );
                url.searchParams.set( 't', String( Date.now() ) );
                window.location.replace( url.href );
                return;
            }
            css( 'ffw-style-settings', message.css || '' );
            css( 'ffw-custom-css', message.custom || '' );
            var list = document.getElementById( 'ffw-main-wrapper' );
            if ( list ) {
                list.style.width = message.width || '100%';
                list.style.setProperty( '--ffw-icon-color', message.color || '' );
                list.style.setProperty( '--ffw-icon-size', message.size || '' );
            }
            size();
        } );
        if ( window.ResizeObserver ) {
            new ResizeObserver( size ).observe( box );
        }
        window.addEventListener( 'load', size );
        size();
    } )();
    </script>
</body>
</html>
        <?php
        exit;
    }

    /**
     * Base preview URL (append &code=...).
     *
     * @return string
     */
    public static function base_url() {
        return add_query_arg(
            array(
                self::VAR  => '1',
                '_wpnonce' => wp_create_nonce( self::ACTION ),
            ),
            home_url( '/' )
        );
    }

    /**
     * Only [ffw_template ...] shortcodes, one at a time.
     *
     * @param string $code Raw shortcode.
     * @return string Empty when not allowed.
     */
    public static function clean_code( $code ) {
        $code = trim( (string) $code );
        if ( ! preg_match( '/^\[ffw_template(\s[^\[\]]*)?\]$/', $code ) ) {
            return '';
        }
        return $code;
    }

    /**
     * Render the preview.
     *
     * @return void
     */
    public function maybe_render() {
        if ( ! isset( $_GET[ self::VAR ] ) ) {
            return;
        }

        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        if ( ! current_user_can( 'edit_posts' ) || ! wp_verify_nonce( $nonce, self::ACTION ) ) {
            wp_die( esc_html__( 'This preview is not available.', 'faq-for-woocommerce' ), '', array( 'response' => 403 ) );
        }

        $code = isset( $_GET['code'] ) ? self::clean_code( wp_unslash( $_GET['code'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- only [ffw_template] passes clean_code().
        if ( '' === $code ) {
            wp_die( esc_html__( 'Nothing to preview.', 'faq-for-woocommerce' ), '', array( 'response' => 400 ) );
        }

        // Render first so the list's styles and scripts are enqueued for wp_head().
        $html = do_shortcode( $code );
        if ( '' === trim( wp_strip_all_tags( (string) $html ) ) ) {
            $html = '<p style="padding:24px;color:#646970;font:14px/1.5 -apple-system,BlinkMacSystemFont,sans-serif;text-align:center">' . esc_html__( 'No FAQs match these options yet.', 'faq-for-woocommerce' ) . '</p>';
        }

        $content = '<div style="max-width:880px;margin:0 auto;padding:28px 20px">' . $html . '</div>';

        if ( class_exists( 'FAQ_Woocommerce_Patterns' ) && method_exists( 'FAQ_Woocommerce_Patterns', 'output_preview' ) ) {
            FAQ_Woocommerce_Patterns::output_preview( $content );
        }

        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output.
        exit;
    }
}

new FFW_App_Preview();
