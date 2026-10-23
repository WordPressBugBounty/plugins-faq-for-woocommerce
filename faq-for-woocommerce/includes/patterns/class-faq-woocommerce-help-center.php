<?php
/**
 * One-click Help Center page.
 *
 * Builds a page from pattern blocks: a hero, popular questions, all FAQs as
 * filter tabs or a category sidebar, and a "Still need help?" section. With
 * Happy FAQs Pro the hero gets the library search and the contact section can
 * use the "Ask a question" form.
 *
 * @package FAQ_Woocommerce
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class FAQ_Woocommerce_Help_Center.
 */
class FAQ_Woocommerce_Help_Center {

    const PAGE          = 'ffw-help-center';
    const CREATE_ACTION = 'ffw_create_help_center';
    const PREVIEW_VAR   = 'ffw-help-center-preview';
    const CLASS_PREFIX  = 'ffw-hc-';

    /**
     * Constructor.
     */
    public function __construct() {
        // After Patterns (25), so the same position puts Help Center just above it.
        add_action( 'admin_menu', array( $this, 'register_page' ), 26 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_page_assets' ) );
        add_action( 'admin_post_' . self::CREATE_ACTION, array( $this, 'create_page' ) );
        add_action( 'template_redirect', array( $this, 'maybe_render_preview' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'enqueue_block_assets', array( $this, 'enqueue_editor_styles' ) );
    }

    /* ------------------------------------------------------------------
     * Options.
     * ------------------------------------------------------------------ */

    /**
     * Happy FAQs Pro library search block is available.
     *
     * @return bool
     */
    public static function has_pro_search() {
        return WP_Block_Type_Registry::get_instance()->is_registered( 'happy-faq/search-faqs' );
    }

    /**
     * Happy FAQs Pro "Ask a question" block is available.
     *
     * @return bool
     */
    public static function has_pro_form() {
        return WP_Block_Type_Registry::get_instance()->is_registered( 'happy-faq/ask-question' );
    }

    /**
     * A contact page if the site has one, otherwise the admin email.
     *
     * @return string
     */
    public static function default_contact_url() {
        foreach ( array( 'contact', 'contact-us', 'support' ) as $slug ) {
            $page = get_page_by_path( $slug );
            if ( $page && 'publish' === $page->post_status ) {
                return (string) get_permalink( $page );
            }
        }

        return 'mailto:' . sanitize_email( (string) get_option( 'admin_email' ) );
    }

    /**
     * Defaults for the builder.
     *
     * @return array
     */
    public static function defaults() {
        return array(
            'title'       => __( 'Help Center', 'faq-for-woocommerce' ),
            'layout'      => 'tabs',
            'style'       => 'cards',
            'popular'     => true,
            'search'      => self::has_pro_search() ? 'library' : 'inline',
            'contact'     => self::has_pro_form() ? 'form' : 'link',
            'contact_url' => self::default_contact_url(),
        );
    }

    /**
     * Clean builder options from a request.
     *
     * @param array $raw Raw values (unslashed).
     * @return array
     */
    public static function parse( $raw ) {
        $raw      = is_array( $raw ) ? $raw : array();
        $defaults = self::defaults();

        $options = array(
            'title'       => isset( $raw['title'] ) && '' !== trim( (string) $raw['title'] ) ? sanitize_text_field( (string) $raw['title'] ) : $defaults['title'],
            'layout'      => isset( $raw['layout'] ) && 'sidebar' === $raw['layout'] ? 'sidebar' : 'tabs',
            'style'       => isset( $raw['style'] ) && in_array( $raw['style'], array( 'cards', 'minimal', 'soft' ), true ) ? $raw['style'] : 'cards',
            'popular'     => ! empty( $raw['popular'] ),
            'search'      => isset( $raw['search'] ) && in_array( $raw['search'], array( 'library', 'inline', 'none' ), true ) ? $raw['search'] : $defaults['search'],
            'contact'     => isset( $raw['contact'] ) && in_array( $raw['contact'], array( 'form', 'link', 'none' ), true ) ? $raw['contact'] : $defaults['contact'],
            'contact_url' => isset( $raw['contact_url'] ) ? esc_url_raw( trim( (string) $raw['contact_url'] ), array( 'http', 'https', 'mailto' ) ) : $defaults['contact_url'],
        );

        // Pro choices fall back when Happy FAQs Pro is not active.
        if ( 'library' === $options['search'] && ! self::has_pro_search() ) {
            $options['search'] = 'inline';
        }
        if ( 'form' === $options['contact'] && ! self::has_pro_form() ) {
            $options['contact'] = 'link';
        }
        if ( 'link' === $options['contact'] && '' === $options['contact_url'] ) {
            $options['contact'] = 'none';
        }

        return $options;
    }

    /**
     * FAQ categories that have published FAQs.
     *
     * @return WP_Term[]
     */
    public static function categories() {
        if ( ! taxonomy_exists( 'ffw-category' ) ) {
            return array();
        }

        $terms = get_terms(
            array(
                'taxonomy'   => 'ffw-category',
                'hide_empty' => true,
                'orderby'    => 'name',
            )
        );

        return is_array( $terms ) ? $terms : array();
    }

    /* ------------------------------------------------------------------
     * Page content.
     * ------------------------------------------------------------------ */

    /**
     * Block content for a Help Center page.
     *
     * @param array $options Options from parse().
     * @return string
     */
    public static function build( $options ) {
        $p          = 'FAQ_Woocommerce_Patterns';
        $style      = $options['style'];
        $categories = self::categories();
        $layout     = 'sidebar' === $options['layout'] && $categories ? 'sidebar' : 'tabs';
        $blocks     = array();

        // Hero.
        $hero = $p::eyebrow( esc_html__( 'Help center', 'faq-for-woocommerce' ), '#4f46e5' )
            . $p::heading( esc_html__( 'How can we help?', 'faq-for-woocommerce' ), 'center', 'clamp(32px, 4.6vw, 48px)' )
            . $p::lead(
                'library' === $options['search']
                    ? esc_html__( 'Search all our answers, or browse by topic below.', 'faq-for-woocommerce' )
                    : esc_html__( 'Find answers to common questions, or browse by topic below.', 'faq-for-woocommerce' ),
                'center',
                'library' === $options['search'] ? '28px' : '0'
            );

        if ( 'library' === $options['search'] ) {
            $hero .= '<!-- wp:happy-faq/search-faqs ' . serialize_block_attributes(
                array(
                    'placeholder' => __( 'Search for answers…', 'faq-for-woocommerce' ),
                    'limit'       => 8,
                )
            ) . ' /-->';
        }

        $blocks[] = $p::section( $hero, '#f7f7fb', '720px' );

        // Popular questions.
        if ( $options['popular'] ) {
            $blocks[] = $p::section(
                $p::eyebrow( esc_html__( 'Popular', 'faq-for-woocommerce' ), '#b45309' )
                . $p::heading( esc_html__( 'Most asked questions', 'faq-for-woocommerce' ), 'center', 'clamp(26px, 3.4vw, 34px)' )
                . $p::lead( esc_html__( 'The answers people look for most.', 'faq-for-woocommerce' ), 'center', '32px' )
                . $p::prebuilt(
                    array(
                        'source' => 'popular',
                        'limit'  => 5,
                    ),
                    $style
                ),
                '#ffffff'
            );
        }

        // All questions.
        $inline = 'inline' === $options['search'];
        $browse = $p::heading( esc_html__( 'Browse by topic', 'faq-for-woocommerce' ), 'tabs' === $layout ? 'center' : 'left', 'clamp(26px, 3.4vw, 34px)' );

        if ( 'sidebar' === $layout ) {
            $blocks[] = $p::section( $browse . self::sidebar( $categories, $style, $inline ), '#ffffff', '1080px' );
        } else {
            $blocks[] = $p::section(
                $browse
                . $p::lead( esc_html__( 'Pick a topic to narrow the list.', 'faq-for-woocommerce' ), 'center', '32px' )
                . $p::prebuilt(
                    array(
                        'source'     => 'all',
                        'showSearch' => $inline,
                        'showFilter' => true,
                        'perPage'    => 10,
                    ),
                    $style
                ),
                $options['popular'] ? '#f7f7fb' : '#ffffff',
                '820px'
            );
        }

        // Still need help?
        if ( 'none' !== $options['contact'] ) {
            $inner = $p::heading( esc_html__( 'Still need help?', 'faq-for-woocommerce' ), 'center', 'clamp(24px, 3vw, 30px)' );

            if ( 'form' === $options['contact'] ) {
                $inner .= $p::lead( esc_html__( 'Send us your question and we’ll reply by email.', 'faq-for-woocommerce' ), 'center', '24px' )
                    . '<!-- wp:happy-faq/ask-question ' . serialize_block_attributes(
                        array(
                            'mode'  => 'inline',
                            'title' => __( 'Ask a question', 'faq-for-woocommerce' ),
                        )
                    ) . ' /-->';
            } else {
                $inner .= $p::lead( esc_html__( 'Can’t find what you’re looking for? Our team is happy to help.', 'faq-for-woocommerce' ), 'center', '24px' )
                    . $p::button( esc_html__( 'Contact us', 'faq-for-woocommerce' ), 'center', $options['contact_url'] );
            }

            $blocks[] = $p::section( $p::panel( $inner, '#ffffff' ), 'tabs' === $layout && $options['popular'] ? '#ffffff' : '#f7f7fb', '720px' );
        }

        return implode( "\n\n", $blocks );
    }

    /**
     * Sticky topic list on the left, one FAQ list per category on the right.
     *
     * @param WP_Term[] $categories FAQ categories.
     * @param string    $style      Block style.
     * @param bool      $inline     Add an inline search to each list.
     * @return string
     */
    protected static function sidebar( $categories, $style, $inline ) {
        $links    = '';
        $sections = '';

        foreach ( $categories as $term ) {
            $anchor = self::CLASS_PREFIX . sanitize_html_class( $term->slug );

            $links .= "<!-- wp:list-item -->\n<li><a href=\"#" . esc_attr( $anchor ) . '">' . esc_html( $term->name ) . "</a></li>\n<!-- /wp:list-item -->";

            $sections .= "\n\n" . '<!-- wp:heading ' . serialize_block_attributes(
                array(
                    'level'     => 3,
                    'className' => self::CLASS_PREFIX . 'topic-title',
                )
            ) . " -->\n"
                . '<h3 class="wp-block-heading ' . esc_attr( self::CLASS_PREFIX . 'topic-title' ) . '" id="' . esc_attr( $anchor ) . '">' . esc_html( $term->name ) . "</h3>\n<!-- /wp:heading -->\n\n"
                . FAQ_Woocommerce_Patterns::prebuilt(
                    array(
                        'source' => 'faq_cat',
                        'faqCat' => $term->slug,
                    ),
                    $style
                );
        }

        // One search box for every topic; Happy FAQs' inline search filters
        // all lists inside the parent column and hides empty topics.
        if ( $inline ) {
            $sections = "<!-- wp:html -->\n" . '<div class="ffw-inline-search-wrap ' . esc_attr( self::CLASS_PREFIX . 'search' ) . '">'
                . '<label class="screen-reader-text" for="ffw-hc-search-input">' . esc_html__( 'Search FAQs', 'faq-for-woocommerce' ) . '</label>'
                . '<input type="search" id="ffw-hc-search-input" class="ffw-inline-search" placeholder="' . esc_attr__( 'Search all topics…', 'faq-for-woocommerce' ) . '" autocomplete="off" />'
                . "</div>\n<!-- /wp:html -->" . $sections;
        }

        $nav = '<!-- wp:group ' . serialize_block_attributes(
            array(
                'className' => self::CLASS_PREFIX . 'sidebar',
                'layout'    => array( 'type' => 'constrained' ),
            )
        ) . " -->\n"
            . '<div class="wp-block-group ' . esc_attr( self::CLASS_PREFIX . 'sidebar' ) . '">'
            . '<!-- wp:paragraph ' . serialize_block_attributes( array( 'className' => self::CLASS_PREFIX . 'sidebar-label' ) ) . " -->\n"
            . '<p class="' . esc_attr( self::CLASS_PREFIX . 'sidebar-label' ) . '">' . esc_html__( 'Topics', 'faq-for-woocommerce' ) . "</p>\n<!-- /wp:paragraph -->\n\n"
            . '<!-- wp:list ' . serialize_block_attributes( array( 'className' => self::CLASS_PREFIX . 'topics' ) ) . " -->\n"
            . '<ul class="wp-block-list ' . esc_attr( self::CLASS_PREFIX . 'topics' ) . '">' . $links . "</ul>\n<!-- /wp:list --></div>\n<!-- /wp:group -->";

        return FAQ_Woocommerce_Patterns::columns( $nav, $sections, '28%' );
    }

    /* ------------------------------------------------------------------
     * Admin.
     * ------------------------------------------------------------------ */

    /**
     * Happy FAQs → Help Center Page, right above Patterns.
     *
     * @return void
     */
    public function register_page() {
        add_submenu_page(
            'edit.php?post_type=ffw',
            __( 'Help Center Page', 'faq-for-woocommerce' ),
            __( 'Help Center Page', 'faq-for-woocommerce' ),
            'edit_pages',
            self::PAGE,
            array( $this, 'render_page' ),
            3
        );
    }

    /**
     * Builder page.
     *
     * @return void
     */
    public function render_page() {
        if ( ! current_user_can( 'edit_pages' ) ) {
            return;
        }

        include FFW_FILE_DIR . '/views/help-center/page.php';
    }

    /**
     * Builder URL.
     *
     * @return string
     */
    public static function get_page_url() {
        return admin_url( 'edit.php?post_type=ffw&page=' . self::PAGE );
    }

    /**
     * Builder assets: Patterns page styles plus the builder's own.
     *
     * @return void
     */
    public function enqueue_page_assets() {
        if ( ! isset( $_GET['page'] ) || self::PAGE !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $base = '/assets/admin/css/ffw-patterns.css';
        $css  = '/assets/admin/css/ffw-help-center-admin.css';
        $js   = '/assets/admin/js/ffw-help-center.js';

        wp_enqueue_style( 'ffw-patterns', FFW_PLUGIN_URL . $base, array(), FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $base ) );
        wp_enqueue_style( 'ffw-help-center-admin', FFW_PLUGIN_URL . $css, array( 'ffw-patterns' ), FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $css ) );
        wp_enqueue_script( 'ffw-help-center', FFW_PLUGIN_URL . $js, array(), FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $js ), true );
    }

    /**
     * Create the page and open it.
     *
     * @return void
     */
    public function create_page() {
        check_admin_referer( self::CREATE_ACTION );

        if ( ! current_user_can( 'edit_pages' ) ) {
            wp_die( esc_html__( 'You are not allowed to create pages.', 'faq-for-woocommerce' ), '', array( 'response' => 403 ) );
        }

        $raw     = isset( $_POST['ffw_hc'] ) ? wp_unslash( (array) $_POST['ffw_hc'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- cleaned in parse().
        $options = self::parse( $raw );
        $publish = ! empty( $raw['publish'] ) && current_user_can( 'publish_pages' );

        $page_id = wp_insert_post(
            array(
                'post_type'    => 'page',
                'post_status'  => $publish ? 'publish' : 'draft',
                'post_title'   => wp_slash( $options['title'] ),
                // wp_insert_post() unslashes; keep the \u escapes in block attributes.
                'post_content' => wp_slash( self::build( $options ) ),
            ),
            true
        );

        if ( is_wp_error( $page_id ) ) {
            wp_die( esc_html( $page_id->get_error_message() ) );
        }

        update_post_meta( (int) $page_id, '_ffw_help_center', 1 );

        wp_safe_redirect( $publish ? (string) get_permalink( (int) $page_id ) : admin_url( 'post.php?post=' . (int) $page_id . '&action=edit' ) );
        exit;
    }

    /**
     * Preview URL for the builder.
     *
     * @return string
     */
    public static function preview_url() {
        return add_query_arg(
            array(
                self::PREVIEW_VAR => 1,
                '_wpnonce'        => wp_create_nonce( 'ffw_help_center_preview' ),
            ),
            home_url( '/' )
        );
    }

    /**
     * Live preview of the page with the current builder options.
     *
     * @return void
     */
    public function maybe_render_preview() {
        if ( ! isset( $_GET[ self::PREVIEW_VAR ] ) ) {
            return;
        }

        $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        if ( ! current_user_can( 'edit_pages' ) || ! wp_verify_nonce( $nonce, 'ffw_help_center_preview' ) ) {
            wp_die( esc_html__( 'This preview is not available.', 'faq-for-woocommerce' ), '', array( 'response' => 403 ) );
        }

        $raw = isset( $_GET['ffw_hc'] ) ? wp_unslash( (array) $_GET['ffw_hc'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- cleaned in parse().

        $this->register_style();
        wp_enqueue_style( 'ffw-help-center' );

        FAQ_Woocommerce_Patterns::output_preview( self::build( self::parse( $raw ) ) );
    }

    /* ------------------------------------------------------------------
     * Styles.
     * ------------------------------------------------------------------ */

    /**
     * Register the sidebar styles.
     *
     * @return void
     */
    protected function register_style() {
        $css = '/assets/public/css/ffw-help-center.css';
        wp_register_style( 'ffw-help-center', FFW_PLUGIN_URL . $css, array(), FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $css ) );
    }

    /**
     * Only on pages that use the Help Center sidebar.
     *
     * @return void
     */
    public function enqueue_styles() {
        $post = get_queried_object();
        if ( ! is_singular() || ! $post instanceof WP_Post || false === strpos( $post->post_content, self::CLASS_PREFIX . 'sidebar' ) ) {
            return;
        }

        $this->register_style();
        wp_enqueue_style( 'ffw-help-center' );
    }

    /**
     * Same styles in the block editor.
     *
     * @return void
     */
    public function enqueue_editor_styles() {
        if ( ! is_admin() ) {
            return;
        }

        $this->register_style();
        wp_enqueue_style( 'ffw-help-center' );
    }
}

return new FAQ_Woocommerce_Help_Center();
