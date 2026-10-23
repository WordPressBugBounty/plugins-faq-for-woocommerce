<?php
/**
 * Happy FAQs admin app: the slim brand header on every Happy FAQs screen,
 * and React pages for the plugin's admin screens.
 *
 * Each React page replaces a menu page's output only. The page slug, its
 * capability, its options and its form handlers stay the same, and the
 * previous screen is one click away with &ffw_classic=1.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FAQ_Woocommerce_Admin_App class.
 */
class FAQ_Woocommerce_Admin_App {

    /**
     * Parent menu of every Happy FAQs screen.
     */
    const PARENT = 'edit.php?post_type=ffw';

    /**
     * REST namespace for the admin pages.
     */
    const NS = 'ffw/v1/admin';

    /**
     * Single instance.
     *
     * @var FAQ_Woocommerce_Admin_App|null
     */
    protected static $instance = null;

    /**
     * Original page callbacks, by page slug.
     *
     * @var array
     */
    protected $classic = array();

    /**
     * Instance.
     *
     * @return FAQ_Woocommerce_Admin_App
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Hooks.
     */
    protected function __construct() {
        if ( ! is_admin() ) {
            return;
        }

        add_action( 'in_admin_header', array( $this, 'render_header' ), 50 );

        add_action( 'admin_menu', array( $this, 'take_over_pages' ), 1000 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 5 );
        add_action( 'admin_enqueue_scripts', array( $this, 'dequeue_classic_assets' ), 1000 );
        add_filter( 'admin_body_class', array( $this, 'body_class' ) );
    }

    /* ------------------------------------------------------------------
     * Pages.
     * ------------------------------------------------------------------ */

    /**
     * React pages, by menu slug.
     *
     * Each entry: title, script (URL), deps, data (callable returning the
     * page's boot data), and optionally style (URL).
     *
     * @return array
     */
    public static function pages() {
        /**
         * Filters the React admin pages.
         *
         * @since 3.0.0
         * @param array $pages Pages by menu slug.
         */
        return (array) apply_filters( 'ffw_admin_app_pages', array() );
    }

    /**
     * Current page slug.
     *
     * @return string
     */
    public static function current_page() {
        return isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Whether the previous screen was asked for.
     *
     * @return bool
     */
    public static function is_classic() {
        return ! empty( $_GET['ffw_classic'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Swap each React page's output, keeping the original callback for
     * &ffw_classic=1.
     *
     * @return void
     */
    public function take_over_pages() {
        global $wp_filter;

        foreach ( self::pages() as $slug => $page ) {
            $hook = get_plugin_page_hookname( $slug, isset( $page['parent'] ) ? $page['parent'] : self::PARENT );
            if ( ! $hook || empty( $wp_filter[ $hook ] ) ) {
                continue;
            }

            $this->classic[ $slug ] = $wp_filter[ $hook ];
            unset( $wp_filter[ $hook ] );

            add_action( $hook, array( $this, 'render_page' ) );
        }
    }

    /**
     * Page output: the React mount, or the previous screen.
     *
     * @return void
     */
    public function render_page() {
        $slug  = self::current_page();
        $pages = self::pages();

        if ( self::is_classic() || empty( $pages[ $slug ] ) ) {
            if ( isset( $this->classic[ $slug ] ) ) {
                $this->classic[ $slug ]->do_action( array( '' ) );
            }
            return;
        }

        $title = isset( $pages[ $slug ]['title'] ) ? $pages[ $slug ]['title'] : get_admin_page_title();
        ?>
        <div class="wrap ffw-app-wrap">
            <h1 class="screen-reader-text"><?php echo esc_html( $title ); ?></h1>
            <hr class="wp-header-end">
            <div id="ffw-app" class="ffw-app" data-page="<?php echo esc_attr( $slug ); ?>">
                <div class="ffw-app-boot" role="status">
                    <span class="ffw-ui-spin is-large" aria-hidden="true"></span>
                    <span><?php esc_html_e( 'Loading…', 'faq-for-woocommerce' ); ?></span>
                </div>
            </div>
            <noscript>
                <p>
                    <a href="<?php echo esc_url( add_query_arg( 'ffw_classic', '1' ) ); ?>"><?php esc_html_e( 'This screen needs JavaScript. Open the previous screen.', 'faq-for-woocommerce' ); ?></a>
                </p>
            </noscript>
        </div>
        <?php
    }

    /**
     * Shared UI kit and the current page's script.
     *
     * @param string $hook_suffix Admin page hook.
     * @return void
     */
    public function enqueue( $hook_suffix ) {
        $is_app = ! self::is_classic() && isset( self::pages()[ self::current_page() ] );
        if ( ! self::is_plugin_screen() && ! $is_app ) {
            return;
        }

        $version = defined( 'FFW_VERSION' ) ? FFW_VERSION : '3.0.0';
        $base    = FFW_PLUGIN_URL . '/assets/admin/';

        wp_enqueue_style( 'ffw-admin-header', $base . 'css/ffw-admin-header.css', array(), $version );

        wp_register_style( 'ffw-ui', $base . 'css/ffw-ui.css', array(), $version );
        wp_register_script( 'ffw-ui', $base . 'js/ffw-ui.js', array( 'wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-url' ), $version, true );
        wp_add_inline_script(
            'ffw-ui',
            'window.ffwUIConfig = ' . wp_json_encode(
                array(
                'root'       => esc_url_raw( rest_url( self::NS ) ),
                'adminUrl'   => esc_url_raw( admin_url() ),
                'pluginUrl'  => esc_url_raw( FFW_PLUGIN_URL ),
                'proUrl'     => esc_url_raw( FFW_PRO_URL ),
                'docsUrl'    => esc_url_raw( FFW_DOC_URL ),
                'supportUrl' => esc_url_raw( FFW_SUPPORT_URL ),
                'pro'        => ffw_is_pro_activated(),
                'version'    => $version,
                'classicUrl' => esc_url_raw( add_query_arg( 'ffw_classic', '1' ) ),
                )
            ) . ';',
            'before'
        );
        if ( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations( 'ffw-ui', 'faq-for-woocommerce' );
        }

        $slug  = self::current_page();
        $pages = self::pages();
        if ( self::is_classic() || empty( $pages[ $slug ] ) ) {
            return;
        }

        $page = $pages[ $slug ];
        wp_enqueue_style( 'ffw-ui' );
        if ( ! empty( $page['style'] ) ) {
            wp_enqueue_style( 'ffw-page-' . $slug, $page['style'], array( 'ffw-ui' ), $version );
        }

        $deps = array_merge( array( 'ffw-ui' ), isset( $page['deps'] ) ? (array) $page['deps'] : array() );
        wp_enqueue_script( 'ffw-page-' . $slug, $page['script'], $deps, $version, true );

        $data = array();
        if ( ! empty( $page['data'] ) && is_callable( $page['data'] ) ) {
            $data = call_user_func( $page['data'] );
        }
        // JSON keeps numbers and booleans (wp_localize_script would turn them into strings).
        wp_add_inline_script( 'ffw-page-' . $slug, 'window.ffwPage = ' . wp_json_encode( (array) $data ) . ';', 'before' );
        if ( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations( 'ffw-page-' . $slug, isset( $page['domain'] ) ? $page['domain'] : 'faq-for-woocommerce' );
        }
    }

    /**
     * The previous screens' styles reset every element on the page (and
     * Bootstrap restyles WordPress), so React pages leave them out.
     *
     * @return void
     */
    public function dequeue_classic_assets() {
        if ( self::is_classic() || ! isset( self::pages()[ self::current_page() ] ) ) {
            return;
        }
        foreach ( array( 'ffw_bootstrap', 'ffw_admin_styles', 'ffw-ai', 'ffw-pro-ai-gaps', 'ffw-setup-wizard', 'ffw-patterns', 'ffw-help-center-admin', 'ffw-analytics-teaser' ) as $handle ) {
            wp_dequeue_style( $handle );
        }
        foreach ( array( 'ffw_bootstrap_js', 'ffw_admin', 'ffw-ai', 'ffw-pro-ai-gaps', 'ffw-setup-wizard', 'ffw-patterns', 'ffw-help-center' ) as $handle ) {
            wp_dequeue_script( $handle );
        }
    }

    /**
     * Body classes for plugin screens.
     *
     * @param string $classes Classes.
     * @return string
     */
    public function body_class( $classes ) {
        if ( self::is_plugin_screen() ) {
            $classes .= ' ffw-screen';
        }
        if ( ! self::is_classic() && isset( self::pages()[ self::current_page() ] ) ) {
            $classes .= ' ffw-app-screen ffw-app-' . sanitize_html_class( self::current_page() );
        }
        return $classes;
    }

    /* ------------------------------------------------------------------
     * Header.
     * ------------------------------------------------------------------ */

    /**
     * Whether this is a Happy FAQs admin screen.
     *
     * @return bool
     */
    public static function is_plugin_screen() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen ) {
            return false;
        }

        if ( in_array( $screen->post_type, array( 'ffw', 'ffw_customer_qna', 'ffw_group' ), true ) ) {
            return true;
        }

        return ! empty( $screen->parent_file ) && self::PARENT === $screen->parent_file;
    }

    /**
     * Navigation items.
     *
     * @return array Each: slug, label, url, group ('main' or 'more'), match (callable or array), pro (bool).
     */
    public static function nav_items() {
        $page = 'edit.php?post_type=ffw&page=';
        $pro  = ffw_is_pro_activated();

        $items = array(
            array(
                'slug'  => 'dashboard',
                'label' => __( 'Dashboard', 'faq-for-woocommerce' ),
                'url'   => admin_url( $page . 'ffw-dashboard' ),
                'pages' => array( 'ffw-dashboard' ),
            ),
            array(
                'slug'       => 'faqs',
                'label'      => __( 'FAQs', 'faq-for-woocommerce' ),
                'url'        => admin_url( $page . 'ffw-faqs' ),
                'pages'      => array( 'ffw-faqs' ),
                'post_types' => array( 'ffw' ),
            ),
            array(
                'slug'       => 'questions',
                'label'      => __( 'Customer Questions', 'faq-for-woocommerce' ),
                'url'        => admin_url( 'edit.php?post_type=ffw_customer_qna' ),
                'post_types' => array( 'ffw_customer_qna' ),
            ),
            array(
                'slug'  => 'analytics',
                'label' => __( 'Analytics', 'faq-for-woocommerce' ),
                'url'   => admin_url( $page . 'ffw-analytics' ),
                'pages' => array( 'ffw-analytics' ),
            ),
            array(
                'slug'  => 'help-center',
                'label' => __( 'Help Center', 'faq-for-woocommerce' ),
                'url'   => admin_url( $page . 'ffw-help-center' ),
                'pages' => array( 'ffw-help-center' ),
            ),
            array(
                'slug'  => 'patterns',
                'label' => __( 'Patterns', 'faq-for-woocommerce' ),
                'url'   => admin_url( $page . 'ffw-patterns' ),
                'pages' => array( 'ffw-patterns' ),
            ),
            array(
                'slug'  => 'shortcodes',
                'label' => __( 'Shortcodes', 'faq-for-woocommerce' ),
                'url'   => admin_url( $page . 'ffw-shortcodes' ),
                'pages' => array( 'ffw-shortcodes' ),
                'group' => 'more',
            ),
            array(
                'slug'  => 'templates',
                'label' => __( 'Templates', 'faq-for-woocommerce' ),
                'url'   => admin_url( $page . 'ffw-templates' ),
                'pages' => array( 'ffw-templates' ),
                'group' => 'more',
            ),
            array(
                'slug'  => 'ai',
                'label' => __( 'AI FAQs', 'faq-for-woocommerce' ),
                'url'   => admin_url( $page . 'ai-faqs' ),
                'pages' => array( 'ai-faqs' ),
                'group' => 'more',
            ),
            array(
                'slug'  => 'settings',
                'label' => __( 'Settings', 'faq-for-woocommerce' ),
                'url'   => admin_url( $page . 'woocommerce-faq' ),
                'pages' => array( 'woocommerce-faq' ),
            ),
        );

        if ( ! $pro ) {
            // Pro screens, shown as locked so the upgrade path is clear.
            array_splice(
                $items,
                9,
                0,
                array(
                    array(
                        'slug'   => 'groups',
                        'label'  => __( 'FAQ Groups', 'faq-for-woocommerce' ),
                        'url'    => FFW_PRO_URL,
                        'group'  => 'more',
                        'locked' => true,
                    ),
                    array(
                        'slug'   => 'import-export',
                        'label'  => __( 'Import / Export', 'faq-for-woocommerce' ),
                        'url'    => FFW_PRO_URL,
                        'group'  => 'more',
                        'locked' => true,
                    ),
                )
            );
        }

        /**
         * Filters the Happy FAQs header navigation.
         *
         * @since 3.0.0
         * @param array $items Navigation items.
         */
        return (array) apply_filters( 'ffw_admin_nav_items', $items );
    }

    /**
     * Whether a nav item is the current screen.
     *
     * @param array $item Nav item.
     * @return bool
     */
    protected static function is_active( $item ) {
        $page   = self::current_page();
        $screen = get_current_screen();

        if ( $page ) {
            return ! empty( $item['pages'] ) && in_array( $page, $item['pages'], true );
        }

        if ( $screen && ! empty( $item['post_types'] ) && in_array( $screen->post_type, $item['post_types'], true ) ) {
            return true;
        }

        return false;
    }

    /**
     * Slim brand header, above the notices on every Happy FAQs screen.
     *
     * @return void
     */
    public function render_header() {
        // This header replaces the one printed on admin_notices.
        remove_action( 'admin_notices', 'ffw_display_dashboard_header' );

        if ( ! self::is_plugin_screen() || ! current_user_can( 'edit_posts' ) ) {
            return;
        }

        // Full-screen and embedded screens draw their own frame.
        if ( 'ffw-setup' === self::current_page() || ! empty( $_GET['ffw_embed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $screen = get_current_screen();
        if ( $screen && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
            return;
        }

        $pro     = ffw_is_pro_activated();
        $version = apply_filters( 'ffw_show_settings_page_plugin_version', defined( 'FFW_PRO_VERSION' ) && $pro ? FFW_PRO_VERSION : FFW_VERSION );
        $items   = self::nav_items();
        $main    = array();
        $more    = array();
        foreach ( $items as $item ) {
            if ( isset( $item['group'] ) && 'more' === $item['group'] ) {
                $more[] = $item;
            } else {
                $main[] = $item;
            }
        }

        // Older Pro versions add their links with this action.
        ob_start();
        do_action( 'ffw_admin_header_menu_items' );
        $legacy = trim( (string) ob_get_clean() );

        $more_active = false;
        foreach ( $more as $item ) {
            if ( self::is_active( $item ) ) {
                $more_active = true;
            }
        }
        if ( $legacy && false !== strpos( $legacy, 'active' ) ) {
            $more_active = true;
        }
        ?>
        <header class="ffw-hdr" role="banner">
            <div class="ffw-hdr-inner">
                <a class="ffw-hdr-brand" href="<?php echo esc_url( admin_url( 'edit.php?post_type=ffw&page=ffw-dashboard' ) ); ?>">
                    <span class="ffw-hdr-mark" aria-hidden="true">
                        <svg viewBox="0 0 32 32" width="28" height="28"><rect width="32" height="32" rx="9" fill="#FFC72C"/><path d="M12.2 12.4a3.9 3.9 0 1 1 5.6 3.5c-1.2.6-1.8 1.3-1.8 2.6v.6" fill="none" stroke="#111" stroke-width="2.8" stroke-linecap="round"/><circle cx="16" cy="23.1" r="1.75" fill="#111"/></svg>
                    </span>
                    <span class="ffw-hdr-name"><?php echo esc_html( ffw_get_settings_page_menu_title() ); ?></span>
                    <?php if ( $pro && false === stripos( ffw_get_settings_page_menu_title(), 'pro' ) ) : ?>
                        <span class="ffw-hdr-pro"><?php esc_html_e( 'Pro', 'faq-for-woocommerce' ); ?></span>
                    <?php endif; ?>
                    <span class="ffw-hdr-version"><?php echo esc_html( 'v' . $version ); ?></span>
                </a>

                <nav class="ffw-hdr-nav" aria-label="<?php esc_attr_e( 'Happy FAQs', 'faq-for-woocommerce' ); ?>">
                    <ul class="ffw-hdr-menu">
                        <?php foreach ( $main as $item ) : ?>
                            <?php $active = self::is_active( $item ); ?>
                            <li class="ffw-hdr-item<?php echo $active ? ' is-active' : ''; ?>">
                                <a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $item['label'] ); ?></a>
                            </li>
                        <?php endforeach; ?>
                        <?php if ( $more || $legacy ) : ?>
                            <li class="ffw-hdr-item ffw-hdr-more<?php echo $more_active ? ' is-active' : ''; ?>">
                                <details>
                                    <summary><?php esc_html_e( 'Tools', 'faq-for-woocommerce' ); ?><svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></summary>
                                    <ul class="ffw-hdr-dropdown">
                                        <?php foreach ( $more as $item ) : ?>
                                            <?php $active = self::is_active( $item ); ?>
                                            <li class="<?php echo $active ? 'is-active' : ''; ?>">
                                                <a href="<?php echo esc_url( $item['url'] ); ?>"<?php echo ! empty( $item['locked'] ) ? ' target="_blank" rel="noopener"' : ''; ?><?php echo $active ? ' aria-current="page"' : ''; ?>>
                                                    <?php echo esc_html( $item['label'] ); ?>
                                                    <?php if ( ! empty( $item['locked'] ) ) : ?>
                                                        <span class="ffw-hdr-lock"><?php esc_html_e( 'Pro', 'faq-for-woocommerce' ); ?></span>
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php echo wp_kses_post( $legacy ); ?>
                                    </ul>
                                </details>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <div class="ffw-hdr-actions">
                    <a class="ffw-hdr-link" href="<?php echo esc_url( FFW_DOC_URL ); ?>" target="_blank" rel="noopener" title="<?php esc_attr_e( 'Docs', 'faq-for-woocommerce' ); ?>">
                        <svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true"><path d="M6 3h9l4 4v14H6zM14 3v5h5M9 13h7M9 17h7" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span><?php esc_html_e( 'Docs', 'faq-for-woocommerce' ); ?></span>
                    </a>
                    <a class="ffw-hdr-link" href="<?php echo esc_url( FFW_SUPPORT_URL ); ?>" target="_blank" rel="noopener" title="<?php esc_attr_e( 'Support', 'faq-for-woocommerce' ); ?>">
                        <svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true"><path d="M4 14v-2a8 8 0 0 1 16 0v2M4 14a2 2 0 0 1 2-2h1v6H6a2 2 0 0 1-2-2zm16 0a2 2 0 0 0-2-2h-1v6h1a2 2 0 0 0 2-2zM17 18c0 2-2 3-5 3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span><?php esc_html_e( 'Support', 'faq-for-woocommerce' ); ?></span>
                    </a>
                    <?php if ( ! $pro ) : ?>
                        <a class="ffw-hdr-upgrade" href="<?php echo esc_url( FFW_PRO_URL ); ?>" target="_blank" rel="noopener">
                            <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true"><path d="M3 8l4.5 4L12 5l4.5 7L21 8l-2 11H5z" fill="currentColor"/></svg>
                            <?php esc_html_e( 'Upgrade to Pro', 'faq-for-woocommerce' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        <?php
        do_action( 'ffw_after_dashboard_header' );
    }
}

FAQ_Woocommerce_Admin_App::instance();
