<?php
/**
 * Happy FAQs pattern library.
 *
 * Ready-made FAQ sections, bundled with the plugin (no remote requests),
 * registered as block patterns and shown in a gallery under
 * Happy FAQs → Patterns with live previews and one-click pages.
 *
 * @package FAQ_Woocommerce
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FAQ_Woocommerce_Patterns class.
 */
class FAQ_Woocommerce_Patterns {

    const CATEGORY     = 'happy-faqs';
    const PAGE         = 'ffw-patterns';
    const PREVIEW_VAR  = 'ffw-pattern-preview';
    const CREATE_ACTION = 'ffw_create_pattern_page';

    /**
     * Pattern definitions, keyed by slug.
     *
     * @var array|null
     */
    protected static $patterns = null;

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register' ), 20 );
        add_action( 'admin_menu', array( $this, 'register_page' ), 25 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_page_assets' ) );
        add_action( 'template_redirect', array( $this, 'maybe_render_preview' ) );
        add_action( 'admin_post_' . self::CREATE_ACTION, array( $this, 'create_page' ) );
    }

    /* ------------------------------------------------------------------
     * Markup helpers. Output matches what the block editor saves, so
     * inserted patterns never show "unexpected content".
     * ------------------------------------------------------------------ */

    /**
     * Full-width (alignfull) section with a centered content column.
     *
     * @param string $inner      Inner blocks.
     * @param string $background Background color.
     * @param string $width      Content width.
     * @return string
     */
    public static function section( $inner, $background = '#ffffff', $width = '760px' ) {
        $attrs = array(
            'align'  => 'full',
            'style'  => array(
                'spacing' => array(
                    'padding' => array(
                        'top'    => 'clamp(56px, 8vw, 96px)',
                        'bottom' => 'clamp(56px, 8vw, 96px)',
                        'left'   => '24px',
                        'right'  => '24px',
                    ),
                ),
                'color'   => array( 'background' => $background ),
            ),
            'layout' => array(
                'type'        => 'constrained',
                'contentSize' => $width,
            ),
        );

        return '<!-- wp:group ' . serialize_block_attributes( $attrs ) . " -->\n"
            . '<div class="wp-block-group alignfull has-background" style="background-color:' . esc_attr( $background ) . ';padding-top:clamp(56px, 8vw, 96px);padding-right:24px;padding-bottom:clamp(56px, 8vw, 96px);padding-left:24px">'
            . $inner
            . "</div>\n<!-- /wp:group -->";
    }

    /**
     * Rounded inner panel.
     *
     * @param string $inner      Inner blocks.
     * @param string $background Panel color.
     * @return string
     */
    public static function panel( $inner, $background = '#f6f6f9' ) {
        $attrs = array(
            'style'  => array(
                'border'  => array( 'radius' => '20px' ),
                'spacing' => array(
                    'padding' => array(
                        'top'    => '36px',
                        'bottom' => '36px',
                        'left'   => '36px',
                        'right'  => '36px',
                    ),
                ),
                'color'   => array( 'background' => $background ),
            ),
            'layout' => array( 'type' => 'constrained' ),
        );

        return '<!-- wp:group ' . serialize_block_attributes( $attrs ) . " -->\n"
            . '<div class="wp-block-group has-background" style="border-radius:20px;background-color:' . esc_attr( $background ) . ';padding-top:36px;padding-right:36px;padding-bottom:36px;padding-left:36px">'
            . $inner
            . "</div>\n<!-- /wp:group -->";
    }

    /**
     * Small uppercase label above a heading.
     *
     * @param string $text   Label (already escaped).
     * @param string $color  Accent color.
     * @param string $align  left or center.
     * @return string
     */
    public static function eyebrow( $text, $color = '#4f46e5', $align = 'center' ) {
        $attrs = array();
        if ( 'center' === $align ) {
            $attrs['align'] = 'center';
        }
        $attrs['style'] = array(
            'color'      => array( 'text' => $color ),
            'typography' => array(
                'fontSize'      => '13px',
                'fontWeight'    => '600',
                'textTransform' => 'uppercase',
                'letterSpacing' => '0.14em',
            ),
            'spacing'    => array( 'margin' => array( 'bottom' => '12px' ) ),
        );

        $class = ( 'center' === $align ? 'has-text-align-center ' : '' ) . 'has-text-color';

        return '<!-- wp:paragraph ' . serialize_block_attributes( $attrs ) . " -->\n"
            . '<p class="' . $class . '" style="color:' . esc_attr( $color ) . ';margin-bottom:12px;font-size:13px;font-weight:600;letter-spacing:0.14em;text-transform:uppercase">' . $text . "</p>\n<!-- /wp:paragraph -->";
    }

    /**
     * Section heading.
     *
     * @param string $text  Heading (already escaped).
     * @param string $align left or center.
     * @param string $size  Font size.
     * @return string
     */
    public static function heading( $text, $align = 'center', $size = 'clamp(30px, 4vw, 42px)' ) {
        $attrs = array();
        if ( 'center' === $align ) {
            $attrs['textAlign'] = 'center';
        }
        $attrs['style'] = array(
            'typography' => array(
                'fontSize'      => $size,
                'fontWeight'    => '600',
                'letterSpacing' => '-0.02em',
                'lineHeight'    => '1.15',
            ),
            'color'      => array( 'text' => '#18181b' ),
            'spacing'    => array(
                'margin' => array(
                    'top'    => '0',
                    'bottom' => '14px',
                ),
            ),
        );

        $class = 'wp-block-heading ' . ( 'center' === $align ? 'has-text-align-center ' : '' ) . 'has-text-color';

        return '<!-- wp:heading ' . serialize_block_attributes( $attrs ) . " -->\n"
            . '<h2 class="' . $class . '" style="color:#18181b;margin-top:0;margin-bottom:14px;font-size:' . esc_attr( $size ) . ';font-weight:600;letter-spacing:-0.02em;line-height:1.15">' . $text . "</h2>\n<!-- /wp:heading -->";
    }

    /**
     * Intro paragraph.
     *
     * @param string $text   Text (already escaped).
     * @param string $align  left or center.
     * @param string $bottom Space below.
     * @return string
     */
    public static function lead( $text, $align = 'center', $bottom = '40px' ) {
        $attrs = array();
        if ( 'center' === $align ) {
            $attrs['align'] = 'center';
        }
        $attrs['style'] = array(
            'color'      => array( 'text' => '#52525b' ),
            'typography' => array(
                'fontSize'   => '18px',
                'lineHeight' => '1.6',
            ),
            'spacing'    => array( 'margin' => array( 'bottom' => $bottom ) ),
        );

        $class = ( 'center' === $align ? 'has-text-align-center ' : '' ) . 'has-text-color';

        return '<!-- wp:paragraph ' . serialize_block_attributes( $attrs ) . " -->\n"
            . '<p class="' . $class . '" style="color:#52525b;margin-bottom:' . esc_attr( $bottom ) . ';font-size:18px;line-height:1.6">' . $text . "</p>\n<!-- /wp:paragraph -->";
    }

    /**
     * Pill button.
     *
     * @param string $text    Label (already escaped).
     * @param string $justify left or center.
     * @param string $href    Link.
     * @return string
     */
    public static function button( $text, $justify = 'left', $href = '#contact' ) {
        $wrap = array( 'layout' => array( 'type' => 'flex', 'justifyContent' => $justify ) );
        $btn  = array(
            'style' => array(
                'color'   => array(
                    'background' => '#18181b',
                    'text'       => '#ffffff',
                ),
                'border'  => array( 'radius' => '999px' ),
                'spacing' => array(
                    'padding' => array(
                        'top'    => '14px',
                        'bottom' => '14px',
                        'left'   => '26px',
                        'right'  => '26px',
                    ),
                ),
            ),
        );

        return '<!-- wp:buttons ' . serialize_block_attributes( $wrap ) . " -->\n"
            . '<div class="wp-block-buttons"><!-- wp:button ' . serialize_block_attributes( $btn ) . " -->\n"
            . '<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="' . esc_url( $href ) . '" style="border-radius:999px;color:#ffffff;background-color:#18181b;padding-top:14px;padding-right:26px;padding-bottom:14px;padding-left:26px">' . $text . "</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons -->";
    }

    /**
     * Two columns.
     *
     * @param string $left       Left inner blocks.
     * @param string $right      Right inner blocks.
     * @param string $left_width Left column width.
     * @return string
     */
    public static function columns( $left, $right, $left_width = '38%' ) {
        $right_width = ( 100 - (int) $left_width ) . '%';

        return "<!-- wp:columns -->\n<div class=\"wp-block-columns\">"
            . '<!-- wp:column ' . serialize_block_attributes( array( 'width' => $left_width ) ) . " -->\n"
            . '<div class="wp-block-column" style="flex-basis:' . esc_attr( $left_width ) . '">' . $left . "</div>\n<!-- /wp:column -->\n\n"
            . '<!-- wp:column ' . serialize_block_attributes( array( 'width' => $right_width ) ) . " -->\n"
            . '<div class="wp-block-column" style="flex-basis:' . esc_attr( $right_width ) . '">' . $right . "</div>\n<!-- /wp:column --></div>\n<!-- /wp:columns -->";
    }

    /**
     * Happy Custom FAQs block with written questions.
     *
     * @param array  $items Pairs of [question, answer] (plain text, not escaped).
     * @param string $style cards, minimal or soft.
     * @param array  $extra Extra block attributes.
     * @return string
     */
    public static function custom_faqs( $items, $style = 'cards', $extra = array() ) {
        $faqs = array();
        foreach ( $items as $i => $item ) {
            $faqs[] = array(
                'id'       => 'p' . ( $i + 1 ),
                'question' => $item[0],
                'answer'   => $item[1],
            );
        }

        $attrs = array_merge(
            array(
                'items'      => $faqs,
                'layout'     => 5,
                'showSearch' => false,
                'className'  => 'is-style-ffw-' . $style,
            ),
            $extra
        );

        return '<!-- wp:happy-faq/custom-faqs ' . serialize_block_attributes( $attrs ) . ' /-->';
    }

    /**
     * Happy Prebuilt FAQs block (FAQs from the library).
     *
     * @param array  $attrs Block attributes.
     * @param string $style cards, minimal or soft.
     * @return string
     */
    public static function prebuilt( $attrs, $style = 'cards' ) {
        $attrs = array_merge(
            array(
                'layout'     => 5,
                'showSearch' => false,
                'className'  => 'is-style-ffw-' . $style,
            ),
            $attrs
        );

        return '<!-- wp:happy-faq/display-faqs ' . serialize_block_attributes( $attrs ) . ' /-->';
    }

    /* ------------------------------------------------------------------
     * Registry.
     * ------------------------------------------------------------------ */

    /**
     * All patterns: slug => title, description, group, keywords, content.
     *
     * @return array
     */
    public static function get_patterns() {
        if ( null === self::$patterns ) {
            $patterns = include __DIR__ . '/library.php';
            self::$patterns = (array) apply_filters( 'ffw_patterns', is_array( $patterns ) ? $patterns : array() );
        }

        return self::$patterns;
    }

    /**
     * Gallery groups.
     *
     * @return array
     */
    public static function get_groups() {
        return array(
            'general'  => __( 'General', 'faq-for-woocommerce' ),
            'dynamic'  => __( 'Live lists', 'faq-for-woocommerce' ),
            'store'    => __( 'Store', 'faq-for-woocommerce' ),
            'industry' => __( 'Industries', 'faq-for-woocommerce' ),
        );
    }

    /**
     * Register the inserter category and every pattern.
     *
     * @return void
     */
    public function register() {
        if ( ! function_exists( 'register_block_pattern' ) ) {
            return;
        }

        register_block_pattern_category(
            self::CATEGORY,
            array( 'label' => __( 'Happy FAQs', 'faq-for-woocommerce' ) )
        );

        foreach ( self::get_patterns() as $slug => $pattern ) {
            register_block_pattern(
                'happy-faq/' . $slug,
                array(
                    'title'         => $pattern['title'],
                    'description'   => $pattern['description'],
                    'content'       => $pattern['content'],
                    'categories'    => array( self::CATEGORY ),
                    'keywords'      => isset( $pattern['keywords'] ) ? $pattern['keywords'] : array(),
                    'viewportWidth' => 1200,
                )
            );
        }
    }

    /* ------------------------------------------------------------------
     * Gallery screen.
     * ------------------------------------------------------------------ */

    /**
     * Happy FAQs → Patterns.
     *
     * @return void
     */
    public function register_page() {
        add_submenu_page(
            'edit.php?post_type=ffw',
            __( 'FAQ Patterns', 'faq-for-woocommerce' ),
            __( 'Patterns', 'faq-for-woocommerce' ),
            'edit_pages',
            self::PAGE,
            array( $this, 'render_page' ),
            3
        );
    }

    /**
     * Gallery URL.
     *
     * @return string
     */
    public static function get_page_url() {
        return admin_url( 'edit.php?post_type=ffw&page=' . self::PAGE );
    }

    /**
     * Front-end preview URL for one pattern.
     *
     * @param string $slug Pattern slug.
     * @return string
     */
    public static function get_preview_url( $slug ) {
        return add_query_arg(
            array(
                self::PREVIEW_VAR => $slug,
                '_wpnonce'        => wp_create_nonce( 'ffw_pattern_preview' ),
            ),
            home_url( '/' )
        );
    }

    /**
     * Gallery assets.
     *
     * @param string $hook Admin page hook.
     * @return void
     */
    public function enqueue_page_assets( $hook ) {
        if ( ! isset( $_GET['page'] ) || self::PAGE !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $css = '/assets/admin/css/ffw-patterns.css';
        $js  = '/assets/admin/js/ffw-patterns.js';

        wp_enqueue_style( 'ffw-patterns', FFW_PLUGIN_URL . $css, array(), FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $css ) );
        wp_enqueue_script( 'ffw-patterns', FFW_PLUGIN_URL . $js, array(), FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $js ), true );

        $content = array();
        foreach ( self::get_patterns() as $slug => $pattern ) {
            $content[ $slug ] = $pattern['content'];
        }

        wp_localize_script(
            'ffw-patterns',
            'ffwPatterns',
            array(
                'content' => $content,
                'strings' => array(
                    'copied'     => __( 'Copied. Paste it into the block editor.', 'faq-for-woocommerce' ),
                    'copyFailed' => __( 'Copy is blocked in this browser. Use "Create page" instead.', 'faq-for-woocommerce' ),
                    /* translators: %d: number of patterns shown */
                    'count'      => __( '%d patterns', 'faq-for-woocommerce' ),
                ),
            )
        );
    }

    /**
     * Gallery markup.
     *
     * @return void
     */
    public function render_page() {
        if ( ! current_user_can( 'edit_pages' ) ) {
            return;
        }

        $patterns = self::get_patterns();
        $groups   = self::get_groups();

        include FFW_FILE_DIR . '/views/patterns/gallery.php';
    }

    /**
     * Live preview: the pattern rendered with the site's theme styles.
     *
     * @return void
     */
    public function maybe_render_preview() {
        if ( ! isset( $_GET[ self::PREVIEW_VAR ] ) ) {
            return;
        }

        $slug     = sanitize_key( wp_unslash( $_GET[ self::PREVIEW_VAR ] ) );
        $nonce    = isset( $_GET['_wpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        $patterns = self::get_patterns();

        if ( ! current_user_can( 'edit_pages' ) || ! wp_verify_nonce( $nonce, 'ffw_pattern_preview' ) || ! isset( $patterns[ $slug ] ) ) {
            wp_die( esc_html__( 'This preview is not available.', 'faq-for-woocommerce' ), '', array( 'response' => 403 ) );
        }

        self::output_preview( isset( $patterns[ $slug ]['preview'] ) ? $patterns[ $slug ]['preview'] : $patterns[ $slug ]['content'] );
    }

    /**
     * Print block content as a bare page with the theme's styles, then exit.
     *
     * @since 3.0.0
     * @param string $content Block content.
     * @return void
     */
    public static function output_preview( $content ) {
        show_admin_bar( false );
        nocache_headers();
        header( 'X-Robots-Tag: noindex' );

        // Render before wp_head() so block and layout styles are enqueued in time.
        $html = do_blocks( $content );
        ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <?php wp_head(); ?>
    <style>
        html, body { margin: 0 !important; padding: 0 !important; background: #ffffff; }
        .ffw-pattern-preview { margin: 0; }
        .ffw-pattern-preview > * { margin-top: 0; margin-bottom: 0; }
    </style>
</head>
<body <?php body_class( 'ffw-pattern-preview-body' ); ?>>
    <main class="ffw-pattern-preview wp-site-blocks is-layout-constrained">
        <?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered blocks from bundled patterns. ?>
    </main>
    <?php wp_footer(); ?>
</body>
</html>
        <?php
        exit;
    }

    /**
     * Create a draft page from a pattern and open it in the editor.
     *
     * @return void
     */
    public function create_page() {
        $slug = isset( $_GET['pattern'] ) ? sanitize_key( wp_unslash( $_GET['pattern'] ) ) : '';

        check_admin_referer( self::CREATE_ACTION . '_' . $slug );

        if ( ! current_user_can( 'edit_pages' ) ) {
            wp_die( esc_html__( 'You are not allowed to create pages.', 'faq-for-woocommerce' ), '', array( 'response' => 403 ) );
        }

        $patterns = self::get_patterns();
        if ( ! isset( $patterns[ $slug ] ) ) {
            wp_die( esc_html__( 'Pattern not found.', 'faq-for-woocommerce' ), '', array( 'response' => 404 ) );
        }

        $page_id = wp_insert_post(
            array(
                'post_type'    => 'page',
                'post_status'  => 'draft',
                'post_title'   => wp_slash( $patterns[ $slug ]['title'] ),
                // wp_insert_post() unslashes; keep the \u escapes in block attributes.
                'post_content' => wp_slash( $patterns[ $slug ]['content'] ),
            ),
            true
        );

        if ( is_wp_error( $page_id ) ) {
            wp_die( esc_html( $page_id->get_error_message() ) );
        }

        wp_safe_redirect( admin_url( 'post.php?post=' . (int) $page_id . '&action=edit' ) );
        exit;
    }

    /**
     * "Create page" link for a pattern.
     *
     * @param string $slug Pattern slug.
     * @return string
     */
    public static function get_create_url( $slug ) {
        return wp_nonce_url(
            add_query_arg(
                array(
                    'action'  => self::CREATE_ACTION,
                    'pattern' => $slug,
                ),
                admin_url( 'admin-post.php' )
            ),
            self::CREATE_ACTION . '_' . $slug
        );
    }
}

return new FAQ_Woocommerce_Patterns();
