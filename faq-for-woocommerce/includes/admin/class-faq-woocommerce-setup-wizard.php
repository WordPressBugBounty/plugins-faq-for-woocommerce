<?php
/**
 * First-run setup wizard.
 *
 * Five skippable steps: goal, first FAQs, display, recommended tools, welcome.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   2.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FAQ_Woocommerce_Setup_Wizard class.
 */
class FAQ_Woocommerce_Setup_Wizard {

    /**
     * Wizard page slug.
     */
    const PAGE = 'ffw-setup';

    /**
     * Option holding wizard state.
     */
    const OPTION = 'ffw_setup_wizard';

    /**
     * Transient that triggers the post-activation redirect.
     */
    const REDIRECT_TRANSIENT = 'ffw_setup_wizard_redirect';

    /**
     * Nonce action.
     */
    const NONCE = 'ffw_setup_wizard';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_page' ) );
        add_action( 'admin_init', array( $this, 'maybe_redirect' ), 1 );
        add_action( 'current_screen', array( $this, 'prepare_screen' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 20 );
        add_filter( 'admin_body_class', array( $this, 'body_class' ) );
        add_filter( 'plugin_action_links_' . FFW_BASENAME, array( $this, 'plugin_action_link' ), 20 );

        add_action( 'wp_ajax_ffw_setup_create_faqs', array( $this, 'ajax_create_faqs' ) );
        add_action( 'wp_ajax_ffw_setup_install_extras', array( $this, 'ajax_install_extras' ) );
        add_action( 'wp_ajax_ffw_setup_finish', array( $this, 'ajax_finish' ) );
    }

    /**
     * Wizard state.
     *
     * @return array
     */
    public static function get_state() {
        $state = get_option( self::OPTION, array() );

        return wp_parse_args(
            is_array( $state ) ? $state : array(),
            array(
                'status'  => '',
                'goal'    => '',
                'faq_ids' => array(),
            )
        );
    }

    /**
     * Save part of the wizard state.
     *
     * @param array $data Values to merge.
     * @return void
     */
    public static function update_state( $data ) {
        update_option( self::OPTION, array_merge( self::get_state(), $data ), false );
    }

    /**
     * Wizard URL.
     *
     * @return string
     */
    public static function get_url() {
        return admin_url( 'admin.php?page=' . self::PAGE );
    }

    /**
     * Whether the current request is the wizard page.
     *
     * @return bool
     */
    protected function is_wizard_page() {
        return isset( $_GET['page'] ) && self::PAGE === sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Register the hidden wizard page.
     *
     * @return void
     */
    public function register_page() {
        // Parent "options.php" keeps the page out of the admin menu.
        add_submenu_page(
            'options.php',
            esc_html__( 'Happy FAQs Setup', 'faq-for-woocommerce' ),
            esc_html__( 'Happy FAQs Setup', 'faq-for-woocommerce' ),
            'manage_options',
            self::PAGE,
            array( $this, 'render_page' )
        );
    }

    /**
     * Redirect to the wizard once, right after first activation.
     *
     * @return void
     */
    public function maybe_redirect() {
        if ( ! get_transient( self::REDIRECT_TRANSIENT ) ) {
            return;
        }

        if ( wp_doing_ajax() || wp_doing_cron() || is_network_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            return;
        }

        delete_transient( self::REDIRECT_TRANSIENT );

        // Bulk activation or a user who cannot finish the wizard: stay put.
        if ( isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_options' ) || $this->is_wizard_page() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        if ( 'pending' !== self::get_state()['status'] ) {
            return;
        }

        wp_safe_redirect( self::get_url() );
        exit;
    }

    /**
     * Strip admin notices from the wizard screen.
     *
     * @param WP_Screen $screen Current screen.
     * @return void
     */
    public function prepare_screen( $screen ) {
        if ( ! $this->is_wizard_page() ) {
            return;
        }

        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );
        remove_all_actions( 'network_admin_notices' );
        remove_all_actions( 'user_admin_notices' );
    }

    /**
     * Body class used to hide the admin chrome.
     *
     * @param string $classes Classes.
     * @return string
     */
    public function body_class( $classes ) {
        if ( $this->is_wizard_page() ) {
            $classes .= ' ffw-setup-wizard-body';
        }

        return $classes;
    }

    /**
     * "Setup Wizard" link on the Plugins screen until the wizard is done.
     *
     * @param array $links Links.
     * @return array
     */
    public function plugin_action_link( $links ) {
        if ( 'completed' === self::get_state()['status'] || ! current_user_can( 'manage_options' ) ) {
            return $links;
        }

        array_unshift(
            $links,
            sprintf( '<a href="%s">%s</a>', esc_url( self::get_url() ), esc_html__( 'Setup Wizard', 'faq-for-woocommerce' ) )
        );

        return $links;
    }

    /**
     * Recommended tools offered on step 4. Skips anything already active.
     *
     * @return array
     */
    public static function get_extras() {
        $extras = array(
            'autocomplete_orders' => array(
                'slug'        => 'advanced-autocomplete-orders-for-woocommerce',
                'title'       => __( 'Autocomplete WooCommerce orders', 'faq-for-woocommerce' ),
                'description' => __( 'Mark paid orders as completed automatically, so customers get their emails and downloads without you lifting a finger.', 'faq-for-woocommerce' ),
                'icon'        => 'dashicons-yes-alt',
                'requires_wc' => true,
            ),
            'social_feeds'        => array(
                'slug'        => 'feedliyo-social-product-feed-lite',
                'title'       => __( 'Social feeds and product feeds', 'faq-for-woocommerce' ),
                'description' => __( 'Show your social media posts on your site and share your products to shopping and social channels.', 'faq-for-woocommerce' ),
                'icon'        => 'dashicons-share',
                'requires_wc' => false,
            ),
        );

        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        foreach ( $extras as $key => $extra ) {
            if ( $extra['requires_wc'] && ! ffw_is_woocommerce_active() ) {
                unset( $extras[ $key ] );
                continue;
            }

            $file = self::find_plugin_file( $extra['slug'] );
            if ( $file && is_plugin_active( $file ) ) {
                unset( $extras[ $key ] );
            }
        }

        return $extras;
    }

    /**
     * Installed plugin file for a WordPress.org slug.
     *
     * @param string $slug Plugin slug (folder name).
     * @return string Empty when not installed.
     */
    protected static function find_plugin_file( $slug ) {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins( '/' . $slug );
        if ( empty( $plugins ) ) {
            return '';
        }

        $files = array_keys( $plugins );
        $main  = $slug . '.php';

        return $slug . '/' . ( in_array( $main, $files, true ) ? $main : $files[0] );
    }

    /**
     * Whether the current user may install and activate plugins here.
     *
     * @return bool
     */
    protected function can_install_plugins() {
        return current_user_can( 'install_plugins' )
            && current_user_can( 'activate_plugins' )
            && ( ! function_exists( 'wp_is_file_mod_allowed' ) || wp_is_file_mod_allowed( 'ffw_setup_wizard' ) );
    }

    /**
     * Enqueue wizard assets.
     *
     * @return void
     */
    public function enqueue_assets() {
        if ( ! $this->is_wizard_page() ) {
            return;
        }

        $deps = array( 'jquery' );
        if ( ffw_is_woocommerce_active() && wp_script_is( 'wc-enhanced-select', 'registered' ) ) {
            $deps[] = 'wc-enhanced-select';
            wp_enqueue_style( 'woocommerce_admin_styles' );
        }

        wp_enqueue_style( 'dashicons' );
        $css = '/assets/admin/css/ffw-setup-wizard.css';
        $js  = '/assets/admin/js/ffw-setup-wizard.min.js';

        wp_enqueue_style( 'ffw-setup-wizard', FFW_PLUGIN_URL . $css, array(), FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $css ) );
        wp_enqueue_script( 'ffw-setup-wizard', FFW_PLUGIN_URL . $js, $deps, FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $js ), true );

        wp_localize_script(
            'ffw-setup-wizard',
            'ffwSetupWizard',
            array(
                'ajaxurl'   => admin_url( 'admin-ajax.php' ),
                'nonce'     => wp_create_nonce( self::NONCE ),
                'hasWoo'    => ffw_is_woocommerce_active(),
                'dashboard' => admin_url( 'admin.php?page=ffw-dashboard' ),
                'strings'   => array(
                    'copied'        => __( 'Copied!', 'faq-for-woocommerce' ),
                    'copy'          => __( 'Copy', 'faq-for-woocommerce' ),
                    'copyManual'    => __( 'Press Ctrl+C', 'faq-for-woocommerce' ),
                    'copyManualMac' => __( 'Press ⌘C', 'faq-for-woocommerce' ),
                    'saving'        => __( 'Saving…', 'faq-for-woocommerce' ),
                    'needQuestion'  => __( 'Add at least one question, or skip this step.', 'faq-for-woocommerce' ),
                    'saveFailed'    => __( 'Could not save your FAQs. Please try again.', 'faq-for-woocommerce' ),
                    'toolsWorking'  => __( 'Setting up your selected tools…', 'faq-for-woocommerce' ),
                    'toolsReady'    => __( 'Your selected tools are installed and active.', 'faq-for-woocommerce' ),
                    'toolsPartial'  => __( 'Some tools could not be set up automatically. You can add them later from Plugins.', 'faq-for-woocommerce' ),
                    /* translators: %d: number of FAQs */
                    'faqsCreated'   => __( '%d FAQs created', 'faq-for-woocommerce' ),
                    'oneFaqCreated' => __( '1 FAQ created', 'faq-for-woocommerce' ),
                ),
            )
        );
    }

    /**
     * Render the wizard.
     *
     * @return void
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $has_woo      = ffw_is_woocommerce_active();
        $has_elementor = did_action( 'elementor/loaded' ) > 0;
        $extras       = self::get_extras();
        $state        = self::get_state();

        include FFW_FILE_DIR . '/views/setup-wizard/wizard.php';
    }

    /**
     * Stop an AJAX request with a bad nonce or without the capability.
     *
     * Each handler calls check_ajax_referer() itself and passes the result here.
     *
     * @param int|false $nonce_valid Result of check_ajax_referer().
     * @return void
     */
    protected function verify_request( $nonce_valid ) {
        if ( ! $nonce_valid ) {
            wp_send_json_error( array( 'message' => __( 'Your session expired. Please reload the page.', 'faq-for-woocommerce' ) ), 403 );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'You are not allowed to run the setup wizard.', 'faq-for-woocommerce' ) ), 403 );
        }
    }

    /**
     * Step 2: create the first FAQs.
     *
     * @return void
     */
    public function ajax_create_faqs() {
        $this->verify_request( check_ajax_referer( self::NONCE, 'nonce', false ) );

        $goal = isset( $_POST['goal'] ) && 'product' === sanitize_key( wp_unslash( $_POST['goal'] ) ) && ffw_is_woocommerce_active() ? 'product' : 'general';

        $rows = isset( $_POST['faqs'] ) && is_array( $_POST['faqs'] ) ? wp_unslash( $_POST['faqs'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $faqs = array();

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $question = isset( $row['question'] ) ? sanitize_text_field( $row['question'] ) : '';
            $answer   = isset( $row['answer'] ) ? wp_kses_post( $row['answer'] ) : '';

            if ( '' === $question ) {
                continue;
            }

            $faqs[] = array(
                'question' => $question,
                'answer'   => $answer,
            );
        }

        if ( empty( $faqs ) ) {
            wp_send_json_error( array( 'message' => __( 'Add at least one question, or skip this step.', 'faq-for-woocommerce' ) ) );
        }

        // Optional FAQ category for general FAQs.
        $term_id = 0;
        if ( 'general' === $goal && ! empty( $_POST['category'] ) ) {
            $category = sanitize_text_field( wp_unslash( $_POST['category'] ) );
            $existing = term_exists( $category, 'ffw-category' );

            if ( $existing && ! is_wp_error( $existing ) ) {
                $term_id = (int) $existing['term_id'];
            } else {
                $term = wp_insert_term( $category, 'ffw-category' );
                if ( ! is_wp_error( $term ) ) {
                    $term_id = (int) $term['term_id'];
                }
            }
        }

        // Products to attach for product FAQs.
        $product_ids = array();
        if ( 'product' === $goal && ! empty( $_POST['products'] ) && is_array( $_POST['products'] ) ) {
            foreach ( array_map( 'absint', wp_unslash( $_POST['products'] ) ) as $product_id ) {
                if ( $product_id && in_array( get_post_type( $product_id ), array( 'product', 'product_variation' ), true ) ) {
                    $product_ids[] = $product_id;
                }
            }
            $product_ids = array_values( array_unique( $product_ids ) );
        }

        $faq_ids = array();
        foreach ( $faqs as $faq ) {
            $faq_id = wp_insert_post(
                array(
                    'post_type'    => 'ffw',
                    'post_status'  => 'publish',
                    'post_title'   => $faq['question'],
                    'post_content' => $faq['answer'],
                ),
                true
            );

            if ( is_wp_error( $faq_id ) || ! $faq_id ) {
                continue;
            }

            if ( $term_id ) {
                wp_set_object_terms( $faq_id, array( $term_id ), 'ffw-category' );
            }

            foreach ( $product_ids as $product_id ) {
                ffw_insert_faqs_by_product( (int) $faq_id, $product_id );
            }

            $faq_ids[] = (int) $faq_id;
        }

        if ( empty( $faq_ids ) ) {
            wp_send_json_error( array( 'message' => __( 'Could not save your FAQs. Please try again.', 'faq-for-woocommerce' ) ) );
        }

        if ( $term_id ) {
            $shortcode = sprintf( '[ffw_template faq_cat="%d"]', $term_id );
        } elseif ( ! empty( $product_ids ) ) {
            $shortcode = sprintf( '[ffw_template id="%d"]', $product_ids[0] );
        } else {
            $shortcode = sprintf( '[ffw_template faq_ids="%s"]', implode( ',', $faq_ids ) );
        }

        self::update_state(
            array(
                'goal'        => $goal,
                'faq_ids'     => array_values( array_unique( array_merge( self::get_state()['faq_ids'], $faq_ids ) ) ),
                'product_ids' => $product_ids,
            )
        );

        // The sample-FAQ banner is no longer needed once real FAQs exist.
        if ( 'pending' === get_option( 'ffw_onboarding_status' ) ) {
            update_option( 'ffw_onboarding_status', 'complete', false );
        }

        wp_send_json_success(
            array(
                'faq_ids'     => $faq_ids,
                'shortcode'   => $shortcode,
                'product_url' => ! empty( $product_ids ) ? get_permalink( $product_ids[0] ) : '',
            )
        );
    }

    /**
     * Step 4: install and activate the selected tools.
     *
     * @return void
     */
    public function ajax_install_extras() {
        $this->verify_request( check_ajax_referer( self::NONCE, 'nonce', false ) );

        // Finish even if the visitor leaves the page.
        ignore_user_abort( true );

        if ( ! $this->can_install_plugins() ) {
            wp_send_json_error( array( 'message' => __( 'You are not allowed to install plugins on this site.', 'faq-for-woocommerce' ) ) );
        }

        $requested = isset( $_POST['extras'] ) && is_array( $_POST['extras'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['extras'] ) ) : array();
        $extras    = array_intersect_key( self::get_extras(), array_flip( $requested ) );
        $results   = array();

        foreach ( $extras as $key => $extra ) {
            $results[ $key ] = $this->install_and_activate( $extra['slug'] );
        }

        self::update_state( array( 'extras' => $results ) );

        wp_send_json_success( array( 'results' => $results ) );
    }

    /**
     * Install (when missing) and activate a WordPress.org plugin.
     *
     * @param string $slug Plugin slug.
     * @return array { status: active|failed, message }
     */
    protected function install_and_activate( $slug ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $file = self::find_plugin_file( $slug );

        if ( ! $file ) {
            $api = plugins_api(
                'plugin_information',
                array(
                    'slug'   => $slug,
                    'fields' => array( 'sections' => false ),
                )
            );

            if ( is_wp_error( $api ) || empty( $api->download_link ) ) {
                return array( 'status' => 'failed', 'message' => is_wp_error( $api ) ? $api->get_error_message() : 'no_download_link' );
            }

            $skin      = new WP_Ajax_Upgrader_Skin();
            $upgrader  = new Plugin_Upgrader( $skin );
            $installed = $upgrader->install( $api->download_link );

            if ( is_wp_error( $installed ) || ! $installed ) {
                $error = is_wp_error( $installed ) ? $installed : $skin->get_errors();
                return array( 'status' => 'failed', 'message' => is_wp_error( $error ) && $error->has_errors() ? $error->get_error_message() : 'install_failed' );
            }

            wp_clean_plugins_cache();
            $file = self::find_plugin_file( $slug );

            if ( ! $file ) {
                return array( 'status' => 'failed', 'message' => 'plugin_file_not_found' );
            }
        }

        if ( ! is_plugin_active( $file ) ) {
            $activated = activate_plugin( $file );
            if ( is_wp_error( $activated ) ) {
                return array( 'status' => 'failed', 'message' => $activated->get_error_message() );
            }
        }

        return array( 'status' => 'active', 'message' => '' );
    }

    /**
     * Mark the wizard as completed or skipped.
     *
     * @return void
     */
    public function ajax_finish() {
        $this->verify_request( check_ajax_referer( self::NONCE, 'nonce', false ) );

        $status = isset( $_POST['status'] ) && 'completed' === sanitize_key( wp_unslash( $_POST['status'] ) ) ? 'completed' : 'skipped';
        $goal   = isset( $_POST['goal'] ) && 'product' === sanitize_key( wp_unslash( $_POST['goal'] ) ) ? 'product' : 'general';

        self::update_state(
            array(
                'status' => $status,
                'goal'   => $goal,
            )
        );

        wp_send_json_success();
    }
}

return new FAQ_Woocommerce_Setup_Wizard();
