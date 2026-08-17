<?php
/**
 * Sample FAQ walkthrough and copy-shortcode UI.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   1.8.18
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FAQ_Woocommerce_Onboarding class.
 */
class FAQ_Woocommerce_Onboarding {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'maybe_mark_status' ) );
        add_action( 'admin_notices', array( $this, 'render_onboarding_banner' ), 15 );
        add_action( 'ffw_after_dashboard_header', array( $this, 'render_onboarding_banner_after_header' ) );
        add_action( 'admin_notices', array( $this, 'render_shortcode_bar' ), 20 );
        add_filter( 'manage_edit-ffw-category_columns', array( $this, 'category_columns' ) );
        add_filter( 'manage_ffw-category_custom_column', array( $this, 'category_column_content' ), 10, 3 );
        add_filter( 'manage_ffw_posts_columns', array( $this, 'faq_list_columns' ) );
        add_action( 'manage_ffw_posts_custom_column', array( $this, 'faq_list_column_content' ), 10, 2 );
        add_action( 'wp_ajax_ffw_run_onboarding', array( $this, 'ajax_run_onboarding' ) );
        add_action( 'wp_ajax_ffw_dismiss_onboarding', array( $this, 'ajax_dismiss_onboarding' ) );
        add_action( 'admin_print_footer_scripts', array( $this, 'footer_scripts' ) );
    }

    /**
     * Walkthrough banner on Happy FAQs screens that do not use the plugin header.
     *
     * @return void
     */
    public function render_onboarding_banner() {
        if ( $this->has_dashboard_header() ) {
            return;
        }

        $this->output_onboarding_banner();
    }

    /**
     * Walkthrough banner after the Dashboard header.
     *
     * @return void
     */
    public function render_onboarding_banner_after_header() {
        $this->output_onboarding_banner();
    }

    protected $banner_rendered = false;

    /**
     * Print the walkthrough banner markup.
     *
     * @return void
     */
    protected function output_onboarding_banner() {
        if ( $this->banner_rendered ) {
            return;
        }

        if ( 'pending' !== get_option( 'ffw_onboarding_status' ) ) {
            return;
        }

        if ( ! $this->is_faq_admin_screen() || ! current_user_can( 'edit_posts' ) ) {
            return;
        }

        $run_url = wp_nonce_url(
            add_query_arg( 'ffw_run_onboarding', '1' ),
            'ffw_run_onboarding'
        );
        $dismiss_url = wp_nonce_url(
            add_query_arg( 'ffw_dismiss_onboarding', '1' ),
            'ffw_dismiss_onboarding'
        );
        ?>
        <div class="ffw-onboarding-banner" id="ffw-onboarding-banner">
            <div class="ffw-onboarding-banner-inner">
                <div class="ffw-onboarding-banner-copy">
                    <h3><?php esc_html_e( 'Set up your first FAQ page', 'faq-for-woocommerce' ); ?></h3>
                    <p><?php esc_html_e( 'Create a Getting Started category, three sample FAQs, and an FAQs page. You can edit or delete them later. Sites that already have FAQs will not see this.', 'faq-for-woocommerce' ); ?></p>
                </div>
                <div class="ffw-onboarding-banner-actions">
                    <a href="<?php echo esc_url( $run_url ); ?>" class="button button-primary ffw-onboarding-run"><?php esc_html_e( 'Add sample FAQs', 'faq-for-woocommerce' ); ?></a>
                    <a href="<?php echo esc_url( $dismiss_url ); ?>" class="button ffw-onboarding-dismiss"><?php esc_html_e( 'Skip', 'faq-for-woocommerce' ); ?></a>
                </div>
            </div>
        </div>
        <?php
        $this->banner_rendered = true;
    }

    /**
     * Copy-shortcode bar on All FAQs and FAQ category screens.
     *
     * @return void
     */
    public function render_shortcode_bar() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return;
        }

        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen ) {
            return;
        }

        $is_faq_list = ( 'edit' === $screen->base && 'ffw' === $screen->post_type );
        $is_faq_cats = ( isset( $screen->taxonomy ) && 'ffw-category' === $screen->taxonomy && in_array( $screen->base, array( 'edit-tags', 'term' ), true ) );

        if ( ! $is_faq_list && ! $is_faq_cats ) {
            return;
        }

        $shortcode = '[ffw_template]';
        $label     = __( 'All FAQs', 'faq-for-woocommerce' );
        $help      = __( 'Paste this shortcode on any page, or choose an FAQs page in Settings to inject it automatically.', 'faq-for-woocommerce' );

        if ( $is_faq_cats ) {
            $help = __( 'Use the All FAQs shortcode below, or copy a category shortcode from the Shortcode column.', 'faq-for-woocommerce' );
        }
        ?>
        <div class="ffw-admin-shortcode-bar">
            <div class="ffw-admin-shortcode-bar-copy">
                <strong><?php echo esc_html( $label ); ?></strong>
                <span><?php echo esc_html( $help ); ?></span>
                <span><?php esc_html_e( 'Insert Happy Prebuilt FAQs for library FAQs, or Happy Custom FAQs (Gutenberg, Elementor, or Divi) to write questions on the page.', 'faq-for-woocommerce' ); ?></span>
            </div>
            <?php echo ffw_get_copy_shortcode_html( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <?php
    }

    /**
     * Add a Shortcode column to FAQ categories.
     *
     * @param array $columns Columns.
     * @return array
     */
    public function category_columns( $columns ) {
        $new = array();

        foreach ( $columns as $key => $label ) {
            $new[ $key ] = $label;
            if ( 'name' === $key ) {
                $new['ffw_shortcode'] = esc_html__( 'Shortcode', 'faq-for-woocommerce' );
            }
        }

        if ( ! isset( $new['ffw_shortcode'] ) ) {
            $new['ffw_shortcode'] = esc_html__( 'Shortcode', 'faq-for-woocommerce' );
        }

        return $new;
    }

    /**
     * Category shortcode cell.
     *
     * @param string $content Column content.
     * @param string $column  Column name.
     * @param int    $term_id Term ID.
     * @return string
     */
    public function category_column_content( $content, $column, $term_id ) {
        if ( 'ffw_shortcode' !== $column ) {
            return $content;
        }

        $shortcode = sprintf( '[ffw_template faq_cat="%d"]', absint( $term_id ) );

        return ffw_get_copy_shortcode_html( $shortcode );
    }

    /**
     * Add a Shortcode column to All FAQs.
     *
     * @param array $columns Columns.
     * @return array
     */
    public function faq_list_columns( $columns ) {
        $new = array();

        foreach ( $columns as $key => $label ) {
            $new[ $key ] = $label;
            if ( 'title' === $key ) {
                $new['ffw_shortcode'] = esc_html__( 'Shortcode', 'faq-for-woocommerce' );
            }
        }

        if ( ! isset( $new['ffw_shortcode'] ) ) {
            $new['ffw_shortcode'] = esc_html__( 'Shortcode', 'faq-for-woocommerce' );
        }

        return $new;
    }

    /**
     * All FAQs shortcode cell.
     *
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     * @return void
     */
    public function faq_list_column_content( $column, $post_id ) {
        if ( 'ffw_shortcode' !== $column ) {
            return;
        }

        echo ffw_get_copy_shortcode_html( sprintf( '[ffw_template faq_ids="%d"]', absint( $post_id ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Create sample FAQs, category, and FAQ page.
     *
     * @return void
     */
    public function ajax_run_onboarding() {
        check_ajax_referer( 'ffw_admin', 'nonce' );

        if ( ! current_user_can( 'publish_pages' ) || ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to create sample FAQs.', 'faq-for-woocommerce' ) ) );
        }

        $result = $this->create_sample_content();

        wp_send_json_success( $result );
    }

    /**
     * Dismiss the walkthrough.
     *
     * @return void
     */
    public function ajax_dismiss_onboarding() {
        check_ajax_referer( 'ffw_admin', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error();
        }

        update_option( 'ffw_onboarding_status', 'dismissed', false );
        wp_send_json_success();
    }

    /**
     * GET fallbacks when JS is unavailable.
     *
     * @return void
     */
    public function maybe_mark_status() {
        $status = get_option( 'ffw_onboarding_status', false );

        if ( false === $status ) {
            $count = wp_count_posts( 'ffw' );
            $published = isset( $count->publish ) ? (int) $count->publish : 0;
            update_option( 'ffw_onboarding_status', $published > 0 ? 'skipped' : 'pending', false );
        }

        if ( isset( $_GET['ffw_run_onboarding'] ) && current_user_can( 'publish_pages' ) ) {
            check_admin_referer( 'ffw_run_onboarding' );
            $this->create_sample_content();
            wp_safe_redirect( remove_query_arg( array( 'ffw_run_onboarding', '_wpnonce' ) ) );
            exit;
        }

        if ( isset( $_GET['ffw_dismiss_onboarding'] ) && current_user_can( 'edit_posts' ) ) {
            check_admin_referer( 'ffw_dismiss_onboarding' );
            update_option( 'ffw_onboarding_status', 'dismissed', false );
            wp_safe_redirect( remove_query_arg( array( 'ffw_dismiss_onboarding', '_wpnonce' ) ) );
            exit;
        }
    }

    /**
     * Sample category, FAQs, and FAQs page.
     *
     * @return array
     */
    public function create_sample_content() {
        $term_id = $this->ensure_getting_started_category();
        $faq_ids = $this->ensure_sample_faqs( $term_id );
        $page_id = $this->ensure_faq_page();

        $options = get_option( 'ffw_general_settings', array() );
        $options = is_array( $options ) ? $options : array();
        $options['ffw_faq_page'] = $page_id;
        update_option( 'ffw_general_settings', $options );
        update_option( 'ffw_onboarding_status', 'complete', false );

        return array(
            'page_id'  => $page_id,
            'page_url' => get_permalink( $page_id ),
            'edit_url' => get_edit_post_link( $page_id, 'raw' ),
            'faq_ids'  => $faq_ids,
            'term_id'  => $term_id,
            'message'  => sprintf(
                /* translators: %s: FAQs page URL */
                __( 'Sample FAQs are ready. View your FAQs page: %s', 'faq-for-woocommerce' ),
                get_permalink( $page_id )
            ),
        );
    }

    /**
     * Getting Started FAQ category.
     *
     * @return int
     */
    protected function ensure_getting_started_category() {
        $existing = term_exists( 'getting-started', 'ffw-category' );

        if ( $existing && ! is_wp_error( $existing ) ) {
            return (int) $existing['term_id'];
        }

        $term = wp_insert_term(
            __( 'Getting Started', 'faq-for-woocommerce' ),
            'ffw-category',
            array(
                'slug'        => 'getting-started',
                'description' => __( 'Sample FAQs created by Happy FAQs.', 'faq-for-woocommerce' ),
            )
        );

        if ( is_wp_error( $term ) ) {
            return 0;
        }

        return (int) $term['term_id'];
    }

    /**
     * Three sample FAQ posts.
     *
     * @param int $term_id Category term ID.
     * @return array
     */
    protected function ensure_sample_faqs( $term_id ) {
        $existing = get_posts(
            array(
                'post_type'      => 'ffw',
                'post_status'    => 'any',
                'posts_per_page' => 3,
                'fields'         => 'ids',
                'meta_key'       => '_ffw_is_sample',
                'meta_value'     => '1',
            )
        );

        if ( count( $existing ) >= 3 ) {
            if ( $term_id ) {
                foreach ( $existing as $faq_id ) {
                    wp_set_object_terms( (int) $faq_id, array( $term_id ), 'ffw-category', true );
                }
            }

            return array_map( 'absint', $existing );
        }

        $samples = array(
            array(
                'title'   => __( 'How do I add FAQs to a page?', 'faq-for-woocommerce' ),
                'content' => '<p>' . esc_html__( 'Create FAQs under Happy FAQs, then paste the ffw_template shortcode on any page. In Settings you can also choose an FAQs page so FAQs are added automatically.', 'faq-for-woocommerce' ) . '</p>',
            ),
            array(
                'title'   => __( 'Can I show only some FAQs?', 'faq-for-woocommerce' ),
                'content' => '<p>' . esc_html__( 'Yes. Use the faq_cat attribute for a FAQ category, or faq_ids for specific FAQs. Copy those shortcodes from All FAQs and FAQ Categories.', 'faq-for-woocommerce' ) . '</p>',
            ),
            array(
                'title'   => __( 'Can the same FAQ appear on a product?', 'faq-for-woocommerce' ),
                'content' => '<p>' . esc_html__( 'Yes. Assign the FAQ on a product, product category, or product tag. One FAQ can live on your help page and on products when WooCommerce is active.', 'faq-for-woocommerce' ) . '</p>',
            ),
        );

        $ids = array();

        foreach ( $samples as $sample ) {
            $faq_id = wp_insert_post(
                array(
                    'post_title'   => $sample['title'],
                    'post_content' => $sample['content'],
                    'post_status'  => 'publish',
                    'post_type'    => 'ffw',
                )
            );

            if ( is_wp_error( $faq_id ) || ! $faq_id ) {
                continue;
            }

            update_post_meta( $faq_id, '_ffw_is_sample', '1' );

            if ( $term_id ) {
                wp_set_object_terms( $faq_id, array( $term_id ), 'ffw-category' );
            }

            $ids[] = (int) $faq_id;
        }

        return $ids;
    }

    /**
     * Publish or reuse an FAQs page.
     *
     * @return int
     */
    protected function ensure_faq_page() {
        $saved = ffw_get_faq_page_id();
        if ( $saved && 'publish' === get_post_status( $saved ) ) {
            return $saved;
        }

        $page = get_page_by_path( 'faqs' );
        if ( $page instanceof WP_Post ) {
            return (int) $page->ID;
        }

        $page_id = wp_insert_post(
            array(
                'post_title'   => __( 'FAQs', 'faq-for-woocommerce' ),
                'post_name'    => 'faqs',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            )
        );

        return is_wp_error( $page_id ) ? 0 : (int) $page_id;
    }

    /**
     * Whether the Happy FAQs header template is printed on this screen.
     *
     * @return bool
     */
    protected function has_dashboard_header() {
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        if ( in_array( $page, array( 'ffw-dashboard', 'woocommerce-faq' ), true ) ) {
            return true;
        }

        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( $screen && ! empty( $screen->parent_file ) && 'edit.php?post_type=ffw' === $screen->parent_file ) {
            return true;
        }

        return false;
    }

    /**
     * Happy FAQs admin screens.
     *
     * @return bool
     */
    protected function is_faq_admin_screen() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen ) {
            return false;
        }

        if ( isset( $screen->post_type ) && in_array( $screen->post_type, array( 'ffw', 'ffw_customer_qna' ), true ) ) {
            return true;
        }

        if ( isset( $screen->id ) && false !== strpos( (string) $screen->id, 'woocommerce-faq' ) ) {
            return true;
        }

        return false;
    }

    /**
     * AJAX for the walkthrough buttons.
     *
     * @return void
     */
    public function footer_scripts() {
        if ( ! $this->is_faq_admin_screen() ) {
            return;
        }
        ?>
        <script>
        jQuery(function ($) {
            function ffwOnboardingNonce() {
                return (typeof ffw_admin !== 'undefined' && ffw_admin.nonce) ? ffw_admin.nonce : '';
            }

            $(document).on('click', '.ffw-onboarding-run', function (e) {
                e.preventDefault();
                var $btn = $(this);
                $btn.addClass('disabled');
                $.post(ajaxurl, {
                    action: 'ffw_run_onboarding',
                    nonce: ffwOnboardingNonce()
                }).done(function (response) {
                    if (!response || !response.success) {
                        $btn.removeClass('disabled');
                        return;
                    }
                    var pageUrl = response.data && response.data.page_url ? String(response.data.page_url).replace(/"/g, '&quot;') : '';
                    var html = '<div class="ffw-onboarding-banner-inner"><div class="ffw-onboarding-banner-copy">';
                    html += '<h3><?php echo esc_js( __( 'Your FAQ page is ready', 'faq-for-woocommerce' ) ); ?></h3>';
                    html += '<p><?php echo esc_js( __( 'Sample FAQs, a Getting Started category, and an FAQs page were created. Edit them anytime.', 'faq-for-woocommerce' ) ); ?></p>';
                    html += '</div><div class="ffw-onboarding-banner-actions">';
                    if (pageUrl) {
                        html += '<a class="button button-primary" href="' + pageUrl + '"><?php echo esc_js( __( 'View FAQs page', 'faq-for-woocommerce' ) ); ?></a>';
                    }
                    html += '<a href="#" class="button ffw-onboarding-dismiss"><?php echo esc_js( __( 'Dismiss', 'faq-for-woocommerce' ) ); ?></a>';
                    html += '</div></div>';
                    $('#ffw-onboarding-banner').html(html);
                }).fail(function () {
                    $btn.removeClass('disabled');
                });
            });

            $(document).on('click', '.ffw-onboarding-dismiss', function (e) {
                e.preventDefault();
                $.post(ajaxurl, {
                    action: 'ffw_dismiss_onboarding',
                    nonce: ffwOnboardingNonce()
                }).always(function () {
                    $('#ffw-onboarding-banner').slideUp(150);
                });
            });
        });
        </script>
        <?php
    }
}

new FAQ_Woocommerce_Onboarding();
