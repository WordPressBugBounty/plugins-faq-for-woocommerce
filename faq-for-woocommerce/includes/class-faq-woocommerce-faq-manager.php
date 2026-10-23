<?php
/**
 * Happy FAQs → FAQs: one screen for All FAQs, Add New and Categories.
 *
 * A React app (wp.element) with three tabs. The list and categories talk to
 * the ffw/v1/manager REST routes below; Add New and Edit load WordPress's own
 * FAQ editor in "embed" mode, so the Happy FAQs Panel, Pro fields, AI tools
 * and every saved setting keep working exactly as before.
 *
 * The classic screens stay available with &ffw_classic=1, and links to them
 * (All FAQs, Add New, Categories) open the matching tab.
 *
 * @package FAQ_Woocommerce
 * @since   3.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * FAQ_Woocommerce_FAQ_Manager class.
 */
class FAQ_Woocommerce_FAQ_Manager {

    const PAGE      = 'ffw-faqs';
    const NS        = 'ffw/v1';
    const PER_PAGE  = 20;
    const STATUSES  = array( 'publish', 'draft', 'pending', 'private', 'future' );

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );

        if ( ! is_admin() ) {
            return;
        }

        add_action( 'admin_menu', array( $this, 'register_page' ), 25 );
        add_action( 'admin_menu', array( $this, 'tidy_menu' ), 98 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        // Old screens open the matching tab.
        add_action( 'load-edit.php', array( $this, 'redirect_list' ) );
        add_action( 'load-post-new.php', array( $this, 'redirect_new' ) );
        add_action( 'load-edit-tags.php', array( $this, 'redirect_categories' ) );

        // Editor inside the FAQs screen.
        add_filter( 'admin_body_class', array( $this, 'embed_body_class' ) );
        add_action( 'admin_head', array( $this, 'embed_styles' ) );
        add_action( 'admin_footer', array( $this, 'embed_script' ) );
        add_action( 'edit_form_top', array( $this, 'embed_field' ) );
        add_filter( 'redirect_post_location', array( $this, 'embed_redirect' ) );

        // WordPress 7.1 isolates the block editor (Document-Isolation-Policy) for
        // in-browser image processing. Isolated, it can't run inside the FAQs
        // screen, so the embedded editor processes images on the server instead.
        add_filter( 'wp_client_side_media_processing_enabled', array( $this, 'embed_media_processing' ) );
    }

    /**
     * No client-side media processing (and so no document isolation) in embed mode.
     *
     * @param bool $enabled Enabled.
     * @return bool
     */
    public function embed_media_processing( $enabled ) {
        return self::is_embed() ? false : $enabled;
    }

    /* ------------------------------------------------------------------
     * Screen.
     * ------------------------------------------------------------------ */

    /**
     * URL of the FAQs screen, optionally at a tab route such as "/new".
     *
     * @param string $route Hash route.
     * @return string
     */
    public static function url( $route = '' ) {
        $url = admin_url( 'edit.php?post_type=ffw&page=' . self::PAGE );

        return '' === $route ? $url : $url . '#' . $route;
    }

    /**
     * Whether this request is the editor inside the FAQs screen.
     *
     * @return bool
     */
    public static function is_embed() {
        // phpcs:ignore WordPress.Security.NonceVerification
        return ! empty( $_GET['ffw_embed'] ) || ! empty( $_POST['ffw_embed'] );
    }

    /**
     * Happy FAQs → FAQs.
     *
     * @return void
     */
    public function register_page() {
        add_submenu_page(
            'edit.php?post_type=ffw',
            __( 'FAQs', 'faq-for-woocommerce' ),
            __( 'FAQs', 'faq-for-woocommerce' ),
            'edit_posts',
            self::PAGE,
            array( $this, 'render_page' ),
            1
        );
    }

    /**
     * Drop All FAQs, Add New and Categories from the menu: the FAQs screen has them as tabs.
     *
     * @return void
     */
    public function tidy_menu() {
        if ( ! apply_filters( 'ffw_manager_replace_menu', true ) ) {
            return;
        }

        remove_submenu_page( 'edit.php?post_type=ffw', 'edit.php?post_type=ffw' );
        remove_submenu_page( 'edit.php?post_type=ffw', 'post-new.php?post_type=ffw' );
        remove_submenu_page( 'edit.php?post_type=ffw', 'edit-tags.php?taxonomy=ffw-category&amp;post_type=ffw' );
        remove_submenu_page( 'edit.php?post_type=ffw', 'edit-tags.php?taxonomy=ffw-category&post_type=ffw' );
    }

    /**
     * Only these query args are ours to translate; anything else keeps the classic screen.
     *
     * @param string[] $allowed Allowed keys.
     * @return bool
     */
    protected function only_args( array $allowed ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        foreach ( array_keys( $_GET ) as $key ) {
            if ( ! in_array( $key, $allowed, true ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * edit.php?post_type=ffw → All FAQs tab, keeping status, search and category.
     *
     * @return void
     */
    public function redirect_list() {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        if ( ! isset( $_GET['post_type'] ) || 'ffw' !== $_GET['post_type'] || ! empty( $_GET['ffw_classic'] ) ) {
            return;
        }
        if ( ! $this->only_args( array( 'post_type', 'post_status', 's', 'ffw-category', 'paged', 'trashed', 'untrashed', 'deleted', 'ids', 'all_posts' ) ) ) {
            return;
        }

        $query = array();
        if ( ! empty( $_GET['post_status'] ) ) {
            $query['status'] = sanitize_key( wp_unslash( $_GET['post_status'] ) );
        }
        if ( ! empty( $_GET['s'] ) ) {
            $query['search'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );
        }
        if ( ! empty( $_GET['ffw-category'] ) ) {
            $term = get_term_by( 'slug', sanitize_title( wp_unslash( $_GET['ffw-category'] ) ), 'ffw-category' );
            if ( $term ) {
                $query['category'] = (int) $term->term_id;
            }
        }
        if ( ! empty( $_GET['paged'] ) ) {
            $query['page'] = absint( $_GET['paged'] );
        }
        // Lets the FAQs screen say "moved to Trash" when the editor sends it here.
        if ( ! empty( $_GET['trashed'] ) ) {
            $query['trashed'] = 1;
        }
        // phpcs:enable

        wp_safe_redirect( self::url( '/list' . ( $query ? '?' . http_build_query( $query ) : '' ) ) );
        exit;
    }

    /**
     * post-new.php?post_type=ffw → Add New tab.
     *
     * @return void
     */
    public function redirect_new() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! isset( $_GET['post_type'] ) || 'ffw' !== $_GET['post_type'] || ! $this->only_args( array( 'post_type' ) ) ) {
            return;
        }

        wp_safe_redirect( self::url( '/new' ) );
        exit;
    }

    /**
     * edit-tags.php?taxonomy=ffw-category → Categories tab.
     *
     * @return void
     */
    public function redirect_categories() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! isset( $_GET['taxonomy'] ) || 'ffw-category' !== $_GET['taxonomy'] || ! $this->only_args( array( 'taxonomy', 'post_type' ) ) ) {
            return;
        }

        wp_safe_redirect( self::url( '/categories' ) );
        exit;
    }

    /**
     * App root.
     *
     * @return void
     */
    public function render_page() {
        ?>
        <div class="wrap ffw-mgr-wrap">
            <h1 class="screen-reader-text"><?php esc_html_e( 'FAQs', 'faq-for-woocommerce' ); ?></h1>
            <div id="ffw-faq-manager" class="ffw-mgr">
                <p class="ffw-mgr-loading"><span class="spinner is-active"></span> <?php esc_html_e( 'Loading FAQs…', 'faq-for-woocommerce' ); ?></p>
            </div>
            <noscript><p><?php esc_html_e( 'This screen needs JavaScript.', 'faq-for-woocommerce' ); ?> <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ffw&ffw_classic=1' ) ); ?>"><?php esc_html_e( 'Open the classic FAQ list', 'faq-for-woocommerce' ); ?></a></p></noscript>
        </div>
        <?php
    }

    /**
     * App assets on the FAQs screen.
     *
     * @param string $hook Screen hook.
     * @return void
     */
    public function enqueue_assets( $hook ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! isset( $_GET['page'] ) || self::PAGE !== $_GET['page'] ) {
            return;
        }

        $css = '/assets/admin/css/ffw-faq-manager.css';
        $js  = '/assets/admin/js/ffw-faq-manager.js';

        wp_enqueue_style( 'ffw-faq-manager', FFW_PLUGIN_URL . $css, array(), FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $css ) );
        wp_enqueue_script( 'ffw-faq-manager', FFW_PLUGIN_URL . $js, array( 'wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-url' ), FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $js ), true );
        wp_set_script_translations( 'ffw-faq-manager', 'faq-for-woocommerce' );

        $woo = post_type_exists( 'product' );
        wp_localize_script(
            'ffw-faq-manager',
            'ffwManager',
            array(
                'root'        => esc_url_raw( rest_url( self::NS . '/manager' ) ),
                'adminUrl'    => admin_url(),
                'newUrl'      => add_query_arg( array( 'post_type' => 'ffw', 'ffw_embed' => 1 ), admin_url( 'post-new.php' ) ),
                'editUrl'     => add_query_arg( array( 'action' => 'edit', 'ffw_embed' => 1 ), admin_url( 'post.php' ) ),
                'fullEditUrl' => add_query_arg( array( 'action' => 'edit' ), admin_url( 'post.php' ) ),
                'classicList' => admin_url( 'edit.php?post_type=ffw&ffw_classic=1' ),
                'classicCats' => admin_url( 'edit-tags.php?taxonomy=ffw-category&post_type=ffw&ffw_classic=1' ),
                'termUrl'     => admin_url( 'term.php?taxonomy=ffw-category&post_type=ffw&tag_ID=' ),
                'perPage'     => self::PER_PAGE,
                'woo'         => $woo,
                'votes'       => class_exists( 'FAQ_Woocommerce_Insights' ) && FAQ_Woocommerce_Insights::votes_enabled(),
                'canCats'     => current_user_can( get_taxonomy( 'ffw-category' )->cap->manage_terms ),
                'canPublish'  => current_user_can( 'publish_posts' ),
                'blockEditor' => use_block_editor_for_post_type( 'ffw' ),
                'pro'         => function_exists( 'ffw_is_pro_activated' ) && ffw_is_pro_activated(),
            )
        );
    }

    /* ------------------------------------------------------------------
     * Editor in embed mode.
     * ------------------------------------------------------------------ */

    /**
     * Whether the current admin screen is the FAQ editor.
     *
     * @return bool
     */
    protected function is_faq_editor() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

        return $screen && 'post' === $screen->base && 'ffw' === $screen->post_type;
    }

    /**
     * Body class for the editor inside the FAQs screen.
     *
     * @param string $classes Classes.
     * @return string
     */
    public function embed_body_class( $classes ) {
        if ( self::is_embed() && $this->is_faq_editor() ) {
            $classes .= ' ffw-embed';
        }

        return $classes;
    }

    /**
     * Hide the admin menu, toolbar, notices and footer around the editor.
     *
     * @return void
     */
    public function embed_styles() {
        if ( ! self::is_embed() || ! $this->is_faq_editor() ) {
            return;
        }
        ?>
        <style id="ffw-embed-css">
            html.wp-toolbar { padding-top: 0 !important; }
            .ffw-embed #wpadminbar, .ffw-embed #adminmenumain, .ffw-embed #adminmenuback, .ffw-embed #adminmenuwrap,
            .ffw-embed #wpfooter, .ffw-embed .ffw-admin-wrapper, .ffw-embed #screen-meta-links,
            .ffw-embed #wpbody-content > .notice, .ffw-embed #wpbody-content > .update-nag, .ffw-embed #wpbody-content > .updated,
            .ffw-embed #wpbody-content > .error, .ffw-embed .ffw-onboarding-banner, .ffw-embed .page-title-action,
            body.ffw-embed div.fs-notice, .ffw-embed .wrap > .notice:not(#message), .ffw-embed .wrap > .update-nag { display: none !important; }
            .ffw-embed #wpcontent, .ffw-embed #wpfooter { margin-left: 0 !important; padding-left: 0 !important; }
            .ffw-embed #wpbody-content { padding-bottom: 20px; }
            .ffw-embed .wrap { margin: 16px 20px 0; }
            .ffw-embed .interface-interface-skeleton { top: 0 !important; left: 0 !important; }
            .ffw-embed.is-fullscreen-mode .interface-interface-skeleton { top: 0 !important; }
            .ffw-embed .edit-post-fullscreen-mode-close, .ffw-embed .editor-fullscreen-mode-close { display: none !important; }
        </style>
        <?php
    }

    /**
     * Keep embed mode across a classic editor save.
     *
     * @return void
     */
    public function embed_field() {
        if ( self::is_embed() ) {
            echo '<input type="hidden" name="ffw_embed" value="1">';
        }
    }

    /**
     * Classic editor: stay in embed mode after saving.
     *
     * @param string $location Redirect.
     * @return string
     */
    public function embed_redirect( $location ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( ! empty( $_POST['ffw_embed'] ) && false !== strpos( $location, 'post.php' ) ) {
            $location = add_query_arg( 'ffw_embed', 1, $location );
        }

        return $location;
    }

    /**
     * Tell the FAQs screen when the FAQ was saved.
     *
     * @return void
     */
    public function embed_script() {
        if ( ! self::is_embed() || ! $this->is_faq_editor() ) {
            return;
        }
        ?>
        <script id="ffw-embed-js">
        ( function () {
            // The block editor may run isolated (Document-Isolation-Policy), so
            // the FAQs screen only hears from it through messages.
            if ( window.parent === window ) {
                return;
            }
            function send( data ) {
                data.source = 'ffw-faq-editor';
                window.parent.postMessage( data, window.location.origin );
            }
            var params = new URLSearchParams( window.location.search );
            var postId = parseInt( params.get( 'post' ) || ( document.getElementById( 'post_ID' ) || {} ).value || 0, 10 );
            var blockEditor = function () {
                return window.wp && wp.data && wp.data.select && wp.data.select( 'core/editor' );
            };

            // Classic editor: a save reloads the page with ?message=, which
            // WordPress strips from the address bar early on.
            if ( <?php echo wp_json_encode( ! empty( $_GET['message'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?> && ! blockEditor() ) {
                send( { type: 'saved', id: postId } );
            }

            function title() {
                var editor = blockEditor();
                if ( editor && editor.getEditedPostAttribute ) {
                    return editor.getEditedPostAttribute( 'title' ) || '';
                }
                var field = document.getElementById( 'title' );
                return field ? field.value : '';
            }

            function dirty() {
                var editor = blockEditor();
                if ( editor && editor.isEditedPostDirty ) {
                    return editor.isEditedPostDirty();
                }
                return !! ( window.wp && wp.autosave && wp.autosave.server && wp.autosave.server.postChanged() );
            }

            // Unsaved changes and title, whenever they change.
            var last = '';
            window.setInterval( function () {
                var state = JSON.stringify( [ dirty(), title() ] );
                if ( state !== last ) {
                    last = state;
                    send( { type: 'state', id: postId, dirty: dirty(), title: title() } );
                }
            }, 700 );

            send( { type: 'ready', id: postId, title: title() } );

            // Block editor: report each finished save. Its scripts can load
            // after this one, so wait for them.
            function watchSaves() {
                if ( ! ( window.wp && wp.data && wp.data.subscribe ) ) {
                    return false;
                }
                var saving = false;
                var left = false;
                wp.data.subscribe( function () {
                    var editor = blockEditor();
                    if ( ! editor || ! editor.getCurrentPostId ) {
                        return;
                    }
                    // Newer editors stay put after Move to Trash.
                    if ( ! left && 'trash' === editor.getCurrentPostAttribute( 'status' ) ) {
                        left = true;
                        send( { type: 'left', id: postId, trashed: true } );
                        return;
                    }
                    var now = editor.isSavingPost() && ! editor.isAutosavingPost();
                    if ( saving && ! now && editor.didPostSaveRequestSucceed() ) {
                        postId = editor.getCurrentPostId();
                        send( { type: 'saved', id: postId, status: editor.getEditedPostAttribute( 'status' ), title: title() } );
                    }
                    saving = now;
                } );
                return true;
            }
            if ( ! watchSaves() ) {
                window.addEventListener( 'DOMContentLoaded', watchSaves );
            }
        } )();
        </script>
        <?php
    }

    /* ------------------------------------------------------------------
     * REST.
     * ------------------------------------------------------------------ */

    /**
     * Routes under ffw/v1/manager.
     *
     * @return void
     */
    public function register_routes() {
        $can_edit = static function () {
            return current_user_can( 'edit_posts' );
        };
        $can_cats = static function () {
            $taxonomy = get_taxonomy( 'ffw-category' );
            return $taxonomy && current_user_can( $taxonomy->cap->manage_terms );
        };

        register_rest_route(
            self::NS,
            '/manager/faqs',
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array( $this, 'rest_list' ),
                    'permission_callback' => $can_edit,
                ),
                array(
                    'methods'             => 'POST',
                    'callback'            => array( $this, 'rest_create' ),
                    'permission_callback' => $can_edit,
                ),
            )
        );
        register_rest_route(
            self::NS,
            '/manager/faqs/(?P<id>\d+)',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'rest_update' ),
                'permission_callback' => $can_edit,
            )
        );
        register_rest_route(
            self::NS,
            '/manager/faqs/(?P<id>\d+)/duplicate',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'rest_duplicate' ),
                'permission_callback' => $can_edit,
            )
        );
        register_rest_route(
            self::NS,
            '/manager/faqs/bulk',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'rest_bulk' ),
                'permission_callback' => $can_edit,
            )
        );
        register_rest_route(
            self::NS,
            '/manager/categories',
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array( $this, 'rest_categories' ),
                    'permission_callback' => $can_edit,
                ),
                array(
                    'methods'             => 'POST',
                    'callback'            => array( $this, 'rest_save_category' ),
                    'permission_callback' => $can_cats,
                ),
            )
        );
        register_rest_route(
            self::NS,
            '/manager/categories/(?P<id>\d+)',
            array(
                array(
                    'methods'             => 'POST',
                    'callback'            => array( $this, 'rest_save_category' ),
                    'permission_callback' => $can_cats,
                ),
                array(
                    'methods'             => 'DELETE',
                    'callback'            => array( $this, 'rest_delete_category' ),
                    'permission_callback' => $can_cats,
                ),
            )
        );
    }

    /**
     * Error response.
     *
     * @param string $message Message.
     * @param int    $status  HTTP status.
     * @return WP_Error
     */
    protected function error( $message, $status = 400 ) {
        return new WP_Error( 'ffw_manager', $message, array( 'status' => $status ) );
    }

    /**
     * FAQ status counts.
     *
     * @return array<string, int>
     */
    protected function counts() {
        $counts = wp_count_posts( 'ffw', 'readable' );
        $out    = array( 'all' => 0 );
        foreach ( array_merge( self::STATUSES, array( 'trash' ) ) as $status ) {
            $out[ $status ] = isset( $counts->$status ) ? (int) $counts->$status : 0;
            if ( 'trash' !== $status ) {
                $out['all'] += $out[ $status ];
            }
        }

        return $out;
    }

    /**
     * FAQ ID => products, product categories, product tags and store pages it shows on.
     *
     * Read once per request from the lists the Happy FAQs Panel saves.
     *
     * @param int[] $faq_ids FAQs on this page of the list.
     * @return array
     */
    protected function placements( array $faq_ids ) {
        global $wpdb;

        $map = array_fill_keys( $faq_ids, array( 'products' => array(), 'productCats' => array(), 'productTags' => array(), 'pages' => array() ) );
        if ( ! $faq_ids ) {
            return $map;
        }

        $wanted = array_flip( $faq_ids );

        if ( post_type_exists( 'product' ) ) {
            $rows = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'ffw_product_faq_post_ids' AND meta_value <> ''" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $products = array();
            foreach ( (array) $rows as $row ) {
                foreach ( (array) maybe_unserialize( $row->meta_value ) as $faq_id ) {
                    if ( isset( $wanted[ (int) $faq_id ] ) ) {
                        $products[ (int) $row->post_id ][] = (int) $faq_id;
                    }
                }
            }
            foreach ( $products as $product_id => $ids ) {
                $post = get_post( $product_id );
                if ( ! $post || 'trash' === $post->post_status ) {
                    continue;
                }
                foreach ( array_unique( $ids ) as $faq_id ) {
                    $map[ $faq_id ]['products'][] = array(
                        'id'   => $product_id,
                        'name' => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
                        'url'  => (string) get_edit_post_link( $product_id, 'raw' ),
                    );
                }
            }

            foreach ( array( 'ffw_cat_faq_post_ids' => 'productCats', 'ffw_tag_faq_post_ids' => 'productTags' ) as $key => $field ) {
                $rows = $wpdb->get_results( $wpdb->prepare( "SELECT term_id, meta_value FROM {$wpdb->termmeta} WHERE meta_key = %s AND meta_value <> ''", $key ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
                foreach ( (array) $rows as $row ) {
                    $term = null;
                    foreach ( array_unique( array_map( 'intval', (array) maybe_unserialize( $row->meta_value ) ) ) as $faq_id ) {
                        if ( ! isset( $wanted[ $faq_id ] ) ) {
                            continue;
                        }
                        $term = $term ? $term : get_term( (int) $row->term_id );
                        if ( $term && ! is_wp_error( $term ) ) {
                            $map[ $faq_id ][ $field ][] = html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' );
                        }
                    }
                }
            }
        }

        $pages = array(
            'product_and_archive_page' => __( 'Product & Archive', 'faq-for-woocommerce' ),
            'shop_page'                => __( 'Shop', 'faq-for-woocommerce' ),
            'cart_page'                => __( 'Cart', 'faq-for-woocommerce' ),
            'checkout_page'            => __( 'Checkout', 'faq-for-woocommerce' ),
        );
        foreach ( $pages as $type => $label ) {
            foreach ( (array) get_option( "ffw_{$type}_faqs", array() ) as $faq_id ) {
                if ( isset( $wanted[ (int) $faq_id ] ) && ! in_array( $label, $map[ (int) $faq_id ]['pages'], true ) ) {
                    $map[ (int) $faq_id ]['pages'][] = $label;
                }
            }
        }

        return $map;
    }

    /**
     * One FAQ for the list.
     *
     * @param WP_Post $post      FAQ.
     * @param array   $placement From placements().
     * @return array
     */
    protected function item( WP_Post $post, array $placement ) {
        $categories = array();
        foreach ( (array) get_the_terms( $post, 'ffw-category' ) as $term ) {
            if ( $term && ! is_wp_error( $term ) ) {
                $categories[] = array(
                    'id'   => (int) $term->term_id,
                    'name' => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
                );
            }
        }

        $excerpt = wp_strip_all_tags( strip_shortcodes( excerpt_remove_blocks( $post->post_content ) ) );
        $excerpt = html_entity_decode( wp_trim_words( $excerpt, 22, '…' ), ENT_QUOTES, 'UTF-8' );
        $status  = get_post_status_object( $post->post_status );

        $item = array(
            'id'          => (int) $post->ID,
            'title'       => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
            'excerpt'     => $excerpt,
            'status'      => $post->post_status,
            'statusLabel' => $status ? $status->label : $post->post_status,
            'date'        => get_the_date( '', $post ),
            'modified'    => human_time_diff( (int) get_post_modified_time( 'U', true, $post ), time() ),
            'order'       => (int) $post->menu_order,
            'categories'  => $categories,
            'global'      => (bool) get_post_meta( $post->ID, 'ffw_is_global_faq', true ),
            'views'       => (int) get_post_meta( $post->ID, '_ffw_views', true ),
            'yes'         => (int) get_post_meta( $post->ID, '_ffw_helpful_yes', true ),
            'no'          => (int) get_post_meta( $post->ID, '_ffw_helpful_no', true ),
            'comments'    => (int) $post->comment_count,
            'canEdit'     => current_user_can( 'edit_post', $post->ID ),
            'canDelete'   => current_user_can( 'delete_post', $post->ID ),
            'badges'      => array(),
        ) + $placement;

        /**
         * FAQ data for the FAQs screen. Add "badges" (short labels) or extra fields.
         *
         * @since 3.0.0
         * @param array   $item FAQ data.
         * @param WP_Post $post FAQ.
         */
        return (array) apply_filters( 'ffw_manager_faq_data', $item, $post );
    }

    /**
     * GET /manager/faqs.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function rest_list( $request ) {
        $status   = sanitize_key( (string) $request->get_param( 'status' ) );
        $search   = trim( sanitize_text_field( (string) $request->get_param( 'search' ) ) );
        $category = (int) $request->get_param( 'category' );
        $orderby  = sanitize_key( (string) $request->get_param( 'orderby' ) );
        $order    = 'asc' === strtolower( (string) $request->get_param( 'order' ) ) ? 'ASC' : 'DESC';
        $page     = max( 1, (int) $request->get_param( 'page' ) );
        $per_page = min( 100, max( 5, (int) ( $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : self::PER_PAGE ) ) );

        $args = array(
            'post_type'        => 'ffw',
            'post_status'      => in_array( $status, array_merge( self::STATUSES, array( 'trash' ) ), true ) ? $status : self::STATUSES,
            'posts_per_page'   => $per_page,
            'paged'            => $page,
            'suppress_filters' => false,
        );
        if ( '' !== $search ) {
            $args['s'] = $search;
        }

        if ( $category > 0 ) {
            $args['tax_query'] = array( array( 'taxonomy' => 'ffw-category', 'terms' => $category ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
        } elseif ( -1 === $category ) {
            $args['tax_query'] = array( array( 'taxonomy' => 'ffw-category', 'operator' => 'NOT EXISTS' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
        }

        $meta_orders = array(
            'views'   => '_ffw_views',
            'helpful' => '_ffw_helpful_yes',
        );
        if ( isset( $meta_orders[ $orderby ] ) ) {
            // Keep FAQs without the counter; they sort as 0.
            $args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                'relation' => 'OR',
                'counted'  => array(
                    'key'  => $meta_orders[ $orderby ],
                    'type' => 'NUMERIC',
                ),
                'none'     => array(
                    'key'     => $meta_orders[ $orderby ],
                    'compare' => 'NOT EXISTS',
                ),
            );
            $args['orderby'] = array(
                'counted' => $order,
                'date'    => 'DESC',
            );
        } elseif ( in_array( $orderby, array( 'title', 'modified', 'menu_order', 'date' ), true ) ) {
            $args['orderby'] = array(
                $orderby => $order,
                'ID'     => $order,
            );
        } elseif ( '' !== $search ) {
            $args['orderby'] = 'relevance';
        } else {
            $args['orderby'] = array(
                'date' => 'DESC',
                'ID'   => 'DESC',
            );
        }

        $query      = new WP_Query( $args );
        $ids        = wp_list_pluck( $query->posts, 'ID' );
        $placements = $this->placements( array_map( 'intval', $ids ) );
        update_object_term_cache( $ids, 'ffw' );
        update_postmeta_cache( $ids );

        $items = array();
        foreach ( $query->posts as $post ) {
            $items[] = $this->item( $post, $placements[ (int) $post->ID ] );
        }

        return rest_ensure_response(
            array(
                'items'  => $items,
                'total'  => (int) $query->found_posts,
                'pages'  => (int) $query->max_num_pages,
                'page'   => $page,
                'counts' => $this->counts(),
            )
        );
    }

    /**
     * Valid FAQ category IDs from a request value.
     *
     * @param mixed $value IDs.
     * @return int[]
     */
    protected function category_ids( $value ) {
        $ids = array();
        foreach ( (array) $value as $id ) {
            $term = get_term( absint( $id ), 'ffw-category' );
            if ( $term && ! is_wp_error( $term ) ) {
                $ids[] = (int) $term->term_id;
            }
        }

        return array_values( array_unique( $ids ) );
    }

    /**
     * Status the current user may set.
     *
     * @param string $status  Requested.
     * @param string $current Fallback.
     * @return string
     */
    protected function allowed_status( $status, $current ) {
        $status = sanitize_key( (string) $status );
        if ( ! in_array( $status, array( 'publish', 'draft', 'pending', 'private' ), true ) ) {
            return $current;
        }
        if ( in_array( $status, array( 'publish', 'private' ), true ) && ! current_user_can( 'publish_posts' ) ) {
            return 'pending';
        }

        return $status;
    }

    /**
     * Answer text from the quick add box as editor content.
     *
     * @param string $text Plain text or HTML.
     * @return string
     */
    protected function answer_content( $text ) {
        $html = wp_kses_post( wpautop( trim( (string) $text ) ) );
        if ( '' === $html || ! use_block_editor_for_post_type( 'ffw' ) || false !== strpos( $html, '<!-- wp:' ) ) {
            return $html;
        }

        // One paragraph block per paragraph, so the block editor opens it as blocks.
        $blocks = array();
        foreach ( preg_split( '#(?<=</p>)\s*#', trim( $html ), -1, PREG_SPLIT_NO_EMPTY ) as $part ) {
            $blocks[] = 0 === strpos( $part, '<p' ) ? "<!-- wp:paragraph -->\n{$part}\n<!-- /wp:paragraph -->" : "<!-- wp:html -->\n{$part}\n<!-- /wp:html -->";
        }

        return implode( "\n\n", $blocks );
    }

    /**
     * POST /manager/faqs: quick add.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function rest_create( $request ) {
        $title = trim( sanitize_text_field( (string) $request->get_param( 'title' ) ) );
        if ( '' === $title ) {
            return $this->error( __( 'Write a question first.', 'faq-for-woocommerce' ) );
        }

        $id = wp_insert_post(
            wp_slash(
                array(
                    'post_type'    => 'ffw',
                    'post_title'   => $title,
                    'post_content' => $this->answer_content( (string) $request->get_param( 'content' ) ),
                    'post_status'  => $this->allowed_status( $request->get_param( 'status' ), 'draft' ),
                )
            ),
            true
        );
        if ( is_wp_error( $id ) ) {
            return $this->error( $id->get_error_message(), 500 );
        }

        $categories = $this->category_ids( $request->get_param( 'categories' ) );
        if ( $categories ) {
            wp_set_object_terms( $id, $categories, 'ffw-category' );
        }

        $post = get_post( $id );

        return rest_ensure_response( array( 'item' => $this->item( $post, $this->placements( array( (int) $id ) )[ (int) $id ] ) ) );
    }

    /**
     * The FAQ for an ID, if the current user may edit it.
     *
     * @param int $id FAQ.
     * @return WP_Post|WP_Error
     */
    protected function faq( $id ) {
        $post = get_post( (int) $id );
        if ( ! $post || 'ffw' !== $post->post_type ) {
            return $this->error( __( 'FAQ not found.', 'faq-for-woocommerce' ), 404 );
        }
        if ( ! current_user_can( 'edit_post', $post->ID ) ) {
            return $this->error( __( 'You are not allowed to edit this FAQ.', 'faq-for-woocommerce' ), 403 );
        }

        return $post;
    }

    /**
     * POST /manager/faqs/{id}: quick edit. Only the sent fields change;
     * product links and store pages are left to the FAQ editor.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function rest_update( $request ) {
        $post = $this->faq( $request['id'] );
        if ( is_wp_error( $post ) ) {
            return $post;
        }

        $update = array( 'ID' => $post->ID );
        if ( null !== $request->get_param( 'title' ) ) {
            $title = trim( sanitize_text_field( (string) $request->get_param( 'title' ) ) );
            if ( '' === $title ) {
                return $this->error( __( 'The question cannot be empty.', 'faq-for-woocommerce' ) );
            }
            $update['post_title'] = $title;
        }
        if ( null !== $request->get_param( 'status' ) ) {
            $update['post_status'] = $this->allowed_status( $request->get_param( 'status' ), $post->post_status );
        }
        if ( null !== $request->get_param( 'order' ) ) {
            $update['menu_order'] = (int) $request->get_param( 'order' );
        }

        if ( count( $update ) > 1 ) {
            $result = wp_update_post( wp_slash( $update ), true );
            if ( is_wp_error( $result ) ) {
                return $this->error( $result->get_error_message(), 500 );
            }
        }

        if ( null !== $request->get_param( 'categories' ) ) {
            wp_set_object_terms( $post->ID, $this->category_ids( $request->get_param( 'categories' ) ), 'ffw-category' );
        }

        $post = get_post( $post->ID );

        return rest_ensure_response( array( 'item' => $this->item( $post, $this->placements( array( (int) $post->ID ) )[ (int) $post->ID ] ) ) );
    }

    /**
     * POST /manager/faqs/{id}/duplicate: a draft copy with the same answer,
     * categories and FAQ settings (not its views, votes or product links).
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function rest_duplicate( $request ) {
        $post = $this->faq( $request['id'] );
        if ( is_wp_error( $post ) ) {
            return $post;
        }

        $id = wp_insert_post(
            wp_slash(
                array(
                    'post_type'    => 'ffw',
                    /* translators: %s: FAQ question */
                    'post_title'   => sprintf( __( '%s (copy)', 'faq-for-woocommerce' ), $post->post_title ),
                    'post_content' => $post->post_content,
                    'post_status'  => 'draft',
                    'menu_order'   => (int) $post->menu_order,
                )
            ),
            true
        );
        if ( is_wp_error( $id ) ) {
            return $this->error( $id->get_error_message(), 500 );
        }

        wp_set_object_terms( $id, wp_get_object_terms( $post->ID, 'ffw-category', array( 'fields' => 'ids' ) ), 'ffw-category' );

        /**
         * FAQ meta copied by Duplicate.
         *
         * @since 3.0.0
         * @param string[] $keys Meta keys.
         */
        foreach ( (array) apply_filters( 'ffw_manager_duplicate_meta', array( 'ffw_is_global_faq', 'ffw_child_faq_ids' ) ) as $key ) {
            $value = get_post_meta( $post->ID, $key, true );
            if ( '' !== $value && null !== $value ) {
                update_post_meta( $id, $key, $value );
            }
        }

        $copy = get_post( $id );

        return rest_ensure_response( array( 'item' => $this->item( $copy, $this->placements( array( (int) $id ) )[ (int) $id ] ) ) );
    }

    /**
     * Remove a FAQ ID from the product, term and store page lists that hold it.
     * Run before a FAQ is deleted for good, so no list keeps a missing FAQ.
     *
     * @param int $faq_id FAQ.
     * @return void
     */
    protected function forget_placements( $faq_id ) {
        global $wpdb;
        $lists = array(
            array( $wpdb->postmeta, 'post_id', 'ffw_product_faq_post_ids', 'post' ),
            array( $wpdb->termmeta, 'term_id', 'ffw_cat_faq_post_ids', 'term' ),
            array( $wpdb->termmeta, 'term_id', 'ffw_tag_faq_post_ids', 'term' ),
        );
        foreach ( $lists as $list ) {
            $rows = $wpdb->get_results( $wpdb->prepare( "SELECT {$list[1]} AS object_id, meta_value FROM {$list[0]} WHERE meta_key = %s AND meta_value <> ''", $list[2] ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            foreach ( (array) $rows as $row ) {
                $ids = array_map( 'intval', (array) maybe_unserialize( $row->meta_value ) );
                if ( ! in_array( $faq_id, $ids, true ) ) {
                    continue;
                }
                $ids = array_values( array_diff( $ids, array( $faq_id ) ) );
                if ( 'post' === $list[3] ) {
                    update_post_meta( (int) $row->object_id, $list[2], $ids );
                } else {
                    update_term_meta( (int) $row->object_id, $list[2], $ids );
                }
            }
        }

        foreach ( array( 'product_and_archive_page', 'shop_page', 'cart_page', 'checkout_page' ) as $type ) {
            $ids = get_option( "ffw_{$type}_faqs" );
            if ( is_array( $ids ) && in_array( $faq_id, array_map( 'intval', $ids ), true ) ) {
                update_option( "ffw_{$type}_faqs", array_values( array_diff( array_map( 'intval', $ids ), array( $faq_id ) ) ) );
            }
        }
    }

    /**
     * POST /manager/faqs/bulk: publish, draft, trash, restore, delete,
     * add_category, remove_category for several FAQs.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function rest_bulk( $request ) {
        $action   = sanitize_key( (string) $request->get_param( 'action' ) );
        $ids      = array_values( array_unique( array_filter( array_map( 'absint', (array) $request->get_param( 'ids' ) ) ) ) );
        $category = $this->category_ids( array( $request->get_param( 'category' ) ) );
        $actions  = array( 'publish', 'draft', 'trash', 'restore', 'delete', 'add_category', 'remove_category' );

        if ( ! in_array( $action, $actions, true ) || ! $ids ) {
            return $this->error( __( 'Choose FAQs and an action.', 'faq-for-woocommerce' ) );
        }
        if ( in_array( $action, array( 'add_category', 'remove_category' ), true ) && ! $category ) {
            return $this->error( __( 'Choose a category.', 'faq-for-woocommerce' ) );
        }

        $done   = 0;
        $failed = 0;
        foreach ( $ids as $id ) {
            $post = get_post( $id );
            if ( ! $post || 'ffw' !== $post->post_type ) {
                ++$failed;
                continue;
            }

            $needs = in_array( $action, array( 'trash', 'restore', 'delete' ), true ) ? 'delete_post' : 'edit_post';
            if ( ! current_user_can( $needs, $id ) ) {
                ++$failed;
                continue;
            }

            switch ( $action ) {
                case 'publish':
                case 'draft':
                    $result = 'trash' === $post->post_status ? false : wp_update_post( array( 'ID' => $id, 'post_status' => $this->allowed_status( $action, $post->post_status ) ) );
                    break;
                case 'trash':
                    $result = wp_trash_post( $id );
                    break;
                case 'restore':
                    $result = wp_untrash_post( $id );
                    break;
                case 'delete':
                    $this->forget_placements( $id );
                    $result = wp_delete_post( $id, true );
                    break;
                case 'add_category':
                    $result = ! is_wp_error( wp_set_object_terms( $id, $category, 'ffw-category', true ) );
                    break;
                default:
                    $result = ! is_wp_error( wp_remove_object_terms( $id, $category, 'ffw-category' ) );
            }

            if ( $result && ! is_wp_error( $result ) ) {
                ++$done;
            } else {
                ++$failed;
            }
        }

        return rest_ensure_response(
            array(
                'done'   => $done,
                'failed' => $failed,
                'counts' => $this->counts(),
            )
        );
    }

    /* ------------------------------------------------------------------
     * Categories.
     * ------------------------------------------------------------------ */

    /**
     * Categories as a tree-ordered list with depth.
     *
     * @return array
     */
    protected function category_list() {
        $terms = get_terms(
            array(
                'taxonomy'   => 'ffw-category',
                'hide_empty' => false,
                'orderby'    => 'name',
            )
        );
        if ( is_wp_error( $terms ) ) {
            return array();
        }

        $children = array();
        foreach ( $terms as $term ) {
            $children[ (int) $term->parent ][] = $term;
        }

        $out  = array();
        $walk = function ( $parent, $depth ) use ( &$walk, &$out, $children ) {
            foreach ( $children[ $parent ] ?? array() as $term ) {
                $out[] = array(
                    'id'          => (int) $term->term_id,
                    'name'        => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
                    'slug'        => urldecode( $term->slug ),
                    'description' => $term->description,
                    'parent'      => (int) $term->parent,
                    'count'       => (int) $term->count,
                    'depth'       => $depth,
                );
                $walk( (int) $term->term_id, $depth + 1 );
            }
        };
        $walk( 0, 0 );

        // Terms whose parent is missing.
        $seen = wp_list_pluck( $out, 'id' );
        foreach ( $terms as $term ) {
            if ( ! in_array( (int) $term->term_id, $seen, true ) ) {
                $out[] = array(
                    'id'          => (int) $term->term_id,
                    'name'        => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
                    'slug'        => urldecode( $term->slug ),
                    'description' => $term->description,
                    'parent'      => (int) $term->parent,
                    'count'       => (int) $term->count,
                    'depth'       => 0,
                );
            }
        }

        return $out;
    }

    /**
     * GET /manager/categories.
     *
     * @return WP_REST_Response
     */
    public function rest_categories() {
        $uncategorized = new WP_Query(
            array(
                'post_type'      => 'ffw',
                'post_status'    => self::STATUSES,
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'tax_query'      => array( array( 'taxonomy' => 'ffw-category', 'operator' => 'NOT EXISTS' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
            )
        );

        return rest_ensure_response(
            array(
                'items'         => $this->category_list(),
                'uncategorized' => (int) $uncategorized->found_posts,
            )
        );
    }

    /**
     * POST /manager/categories(/{id}): add or edit a category.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function rest_save_category( $request ) {
        $id     = (int) $request['id'];
        $name   = trim( sanitize_text_field( (string) $request->get_param( 'name' ) ) );
        $parent = (int) $request->get_param( 'parent' );
        $args   = array(
            'description' => sanitize_textarea_field( (string) $request->get_param( 'description' ) ),
            'parent'      => 0,
        );
        if ( '' !== (string) $request->get_param( 'slug' ) ) {
            $args['slug'] = sanitize_title( (string) $request->get_param( 'slug' ) );
        }

        if ( '' === $name ) {
            return $this->error( __( 'Give the category a name.', 'faq-for-woocommerce' ) );
        }

        if ( $parent ) {
            $parent_term = get_term( $parent, 'ffw-category' );
            if ( ! $parent_term || is_wp_error( $parent_term ) ) {
                return $this->error( __( 'That parent category does not exist.', 'faq-for-woocommerce' ) );
            }
            // A category can't sit inside itself or one of its own sub-categories.
            if ( $id && ( $parent === $id || in_array( $id, get_ancestors( $parent, 'ffw-category', 'taxonomy' ), true ) ) ) {
                return $this->error( __( 'A category cannot be inside itself or one of its sub-categories.', 'faq-for-woocommerce' ) );
            }
            $args['parent'] = $parent;
        }

        if ( $id ) {
            $term = get_term( $id, 'ffw-category' );
            if ( ! $term || is_wp_error( $term ) ) {
                return $this->error( __( 'Category not found.', 'faq-for-woocommerce' ), 404 );
            }
            $args['name'] = $name;
            $result       = wp_update_term( $id, 'ffw-category', $args );
        } else {
            $result = wp_insert_term( $name, 'ffw-category', $args );
        }

        if ( is_wp_error( $result ) ) {
            return $this->error( $result->get_error_message() );
        }

        return rest_ensure_response(
            array(
                'id'    => (int) $result['term_id'],
                'items' => $this->category_list(),
            )
        );
    }

    /**
     * DELETE /manager/categories/{id}. Its FAQs stay; sub-categories move up.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function rest_delete_category( $request ) {
        $id   = (int) $request['id'];
        $term = get_term( $id, 'ffw-category' );
        if ( ! $term || is_wp_error( $term ) ) {
            return $this->error( __( 'Category not found.', 'faq-for-woocommerce' ), 404 );
        }

        $result = wp_delete_term( $id, 'ffw-category' );
        if ( ! $result || is_wp_error( $result ) ) {
            return $this->error( is_wp_error( $result ) ? $result->get_error_message() : __( 'Could not delete that category.', 'faq-for-woocommerce' ), 500 );
        }

        return rest_ensure_response( array( 'items' => $this->category_list() ) );
    }
}

new FAQ_Woocommerce_FAQ_Manager();
