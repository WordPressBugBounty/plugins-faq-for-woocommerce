<?php
/**
 * Live previews of [ffw_template] lists for the admin pages.
 *
 * A front-end URL that renders one [ffw_template] shortcode in the theme,
 * for editors only. Used by the Shortcodes and Templates pages.
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

    /**
     * Hooks.
     */
    public function __construct() {
        add_action( 'template_redirect', array( $this, 'maybe_render' ) );
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
