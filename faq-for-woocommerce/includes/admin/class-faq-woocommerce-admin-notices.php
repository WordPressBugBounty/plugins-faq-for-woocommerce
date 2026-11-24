<?php
/**
 * Display notices in admin
 *
 * @class   FAQ_Woocommerce_Admin
 * @package FAQ_Woocommerce\Admin
 * @version 1.1.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * FFW_Admin_Notices Class.
 */
class FFW_Admin_Notices {

    /**
     * Stores notices.
     *
     * @var array
     */
    private static $notices = array();

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_notices', [$this, 'review_notice'] );
        //add_action( 'admin_notices', [$this, 'discount_banner_notice'] );
        add_action( 'wp_ajax_ffw_save_review_notice', [ $this, 'ffw_save_review_notice' ] );
    }

    /**
     * Display discount banner notice
     * 
     * @return void
     */
    public function discount_banner_notice() {

        if( ffw_is_premium_active() ) {
            return;
        }

        if( 'yes' == get_option('ffw_dismiss_discounted_notice') ) {
            return;
        }

        ?>
        <div class="ffw-notice ffw-discount-notice notice notice-info is-dismissible" style="line-height:1.5;font-size:14px;" data-which="discount">
            <a class="ffw-get-discount" href="<?php echo esc_url(FFW_PRO_URL); ?>">
                <span class="ffw-get-discount-button"><?php esc_html_e('Buy Now', 'faq-for-woocommerce'); ?></span>
            </a>
        </div>
        <?php
    }
    
    /**
     * Days before the review request can show again after "Maybe later".
     */
    const REVIEW_SNOOZE_DAYS = 60;

    /**
     * Cached milestone result.
     */
    const REVIEW_MILESTONE_TRANSIENT = 'ffw_review_milestone';

    /**
     * Review request: one notice on Happy FAQs screens, only after the plugin
     * has helped visitors (answers opened, helpful votes, or FAQs published).
     *
     * @since 3.0.1
     * @return void
     */
    public function review_notice() {
        if ( ! $this->should_show_review_notice() ) {
            return;
        }

        $milestone = $this->get_review_milestone();
        if ( empty( $milestone ) ) {
            return;
        }

        $nonce      = wp_create_nonce( 'ffw_admin' );
        $review_url = 'https://wordpress.org/support/plugin/faq-for-woocommerce/reviews/#new-post';
        $count      = number_format_i18n( $milestone['count'] );

        if ( 'views' === $milestone['type'] ) {
            /* translators: %s: number of FAQ answers visitors opened */
            $message = sprintf( _n( 'Happy FAQs has answered %s question for your visitors.', 'Happy FAQs has answered %s questions for your visitors.', $milestone['count'], 'faq-for-woocommerce' ), $count );
        } elseif ( 'helpful' === $milestone['type'] ) {
            /* translators: %s: number of "helpful" votes on one FAQ */
            $message = sprintf( _n( 'Visitors marked one of your FAQs helpful %s time.', 'Visitors marked one of your FAQs helpful %s times.', $milestone['count'], 'faq-for-woocommerce' ), $count );
        } else {
            /* translators: %s: number of published FAQs */
            $message = sprintf( _n( 'You have published %s FAQ with Happy FAQs.', 'You have published %s FAQs with Happy FAQs.', $milestone['count'], 'faq-for-woocommerce' ), $count );
        }
        ?>
        <div class="ffw-notice ffw-review-notice notice notice-info is-dismissible" style="line-height:1.5;font-size:14px;" data-which="rating">
            <div class="ffw-notice-content">
                <div class="ffw-review-notice-logo"></div>
                <div class="ffw-review-notice-right">
                    <p>
                        <strong><?php echo esc_html( $message ); ?></strong>
                        <?php esc_html_e( 'Would you share a quick review on WordPress.org? It helps a small team a lot.', 'faq-for-woocommerce' ); ?>
                    </p>
                    <p>
                        <a class="button button-primary" data-response="given" href="<?php echo esc_url( $review_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Leave a review', 'faq-for-woocommerce' ); ?></a>
                        <a class="button button-secondary" data-response="later" href="#"><?php esc_html_e( 'Maybe later', 'faq-for-woocommerce' ); ?></a>
                        <a class="button button-secondary" data-response="done" href="#"><?php esc_html_e( 'I already did', 'faq-for-woocommerce' ); ?></a>
                    </p>
                </div>
            </div>
        </div>
        <?php
        add_action( 'admin_print_footer_scripts', function() use ( $nonce ) {
            ?>
            <script>
                (function($){
                    "use strict";
                    $(document)
                        .on('click', '.ffw-review-notice a.button', function (e) {
                            // noinspection ES6ConvertVarToLetConst
                            var self = $(this), notice = self.attr('data-response');
                            if ( 'given' !== notice ) {
                                e.preventDefault();
                            }
                            self.closest('.ffw-review-notice').slideUp( 200, 'linear' );
                            wp.ajax.post( 'ffw_save_review_notice', { nonce: '<?php echo esc_attr( $nonce ); ?>', notice: notice } );
                        })
                        .on('click', '.ffw-review-notice .notice-dismiss', function () {
                            wp.ajax.post( 'ffw_save_review_notice', { nonce: '<?php echo esc_attr( $nonce ); ?>', notice: 'later' } );
                        });
                })(jQuery)
            </script>
            <?php
        }, 99 );
    }

    /**
     * Whether the review request may show for this user on this screen.
     *
     * @since 3.0.1
     * @return bool
     */
    protected function should_show_review_notice() {
        if ( ! current_user_can( 'edit_posts' ) || ! ffw_is_user_capable() ) {
            return false;
        }

        if ( ! $this->is_review_screen() ) {
            return false;
        }

        // The setup walkthrough banner comes first; never stack both.
        if ( 'pending' === get_option( 'ffw_onboarding_status' ) ) {
            return false;
        }

        if ( get_user_meta( get_current_user_id(), 'ffw_review_notice_dismissed', true ) ) {
            return false;
        }

        $next_timestamp = (int) get_option( 'ffw_review_notice_next_show_time' );
        if ( $next_timestamp && time() < $next_timestamp ) {
            return false;
        }

        return true;
    }

    /**
     * Happy FAQs admin screens, without the setup wizard, the block editor
     * and the embedded editor.
     *
     * @since 3.0.1
     * @return bool
     */
    protected function is_review_screen() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen ) {
            return false;
        }

        if ( class_exists( 'FAQ_Woocommerce_Admin_App' ) ) {
            $is_plugin_screen = FAQ_Woocommerce_Admin_App::is_plugin_screen();
        } else {
            $is_plugin_screen = in_array( $screen->post_type, array( 'ffw', 'ffw_customer_qna', 'ffw_group' ), true )
                || ( ! empty( $screen->parent_file ) && 'edit.php?post_type=ffw' === $screen->parent_file );
        }

        if ( ! $is_plugin_screen ) {
            return false;
        }

        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( 'ffw-setup' === $page || ! empty( $_GET['ffw_embed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return false;
        }

        if ( method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
            return false;
        }

        return true;
    }

    /**
     * First milestone reached, checked in this order: answers opened,
     * helpful votes on one FAQ, published FAQs. Cached for 12 hours.
     *
     * @since 3.0.1
     * @return array|null Array with 'type' and 'count', or null.
     */
    protected function get_review_milestone() {
        $cached = get_transient( self::REVIEW_MILESTONE_TRANSIENT );
        if ( false !== $cached ) {
            return is_array( $cached ) ? $cached : null;
        }

        global $wpdb;

        /**
         * Filters the milestones that trigger the review request.
         *
         * @since 3.0.1
         * @param array $thresholds 'views' (answers opened, all FAQs), 'helpful' (votes on one FAQ), 'faqs' (published FAQs).
         */
        $thresholds = wp_parse_args(
            apply_filters( 'ffw_review_notice_thresholds', array() ),
            array(
                'views'   => 100,
                'helpful' => 5,
                'faqs'    => 10,
            )
        );

        // phpcs:disable WordPress.DB.DirectDatabaseQuery
        $views = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM( CAST( pm.meta_value AS UNSIGNED ) ) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'",
                '_ffw_views',
                'ffw'
            )
        );

        $helpful = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT MAX( CAST( pm.meta_value AS UNSIGNED ) ) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'",
                '_ffw_helpful_yes',
                'ffw'
            )
        );
        // phpcs:enable

        $counts = wp_count_posts( 'ffw' );
        $faqs   = isset( $counts->publish ) ? (int) $counts->publish : 0;

        $milestone = null;
        if ( $views >= (int) $thresholds['views'] ) {
            $milestone = array( 'type' => 'views', 'count' => $views );
        } elseif ( $helpful >= (int) $thresholds['helpful'] ) {
            $milestone = array( 'type' => 'helpful', 'count' => $helpful );
        } elseif ( $faqs >= (int) $thresholds['faqs'] ) {
            $milestone = array( 'type' => 'faqs', 'count' => $faqs );
        }

        set_transient( self::REVIEW_MILESTONE_TRANSIENT, $milestone ? $milestone : 'none', 12 * HOUR_IN_SECONDS );

        return $milestone;
    }

    /**
     * Save the answer to the review request.
     *
     * "Leave a review" and "I already did" hide it for this user for good;
     * "Maybe later" and the close button snooze it for everyone.
     */
    public function ffw_save_review_notice() {

        // check and validate nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'ffw_admin' ) ) {
            wp_send_json_error( esc_html__( 'Invalid nonce', 'faq-for-woocommerce' ) );
        }

        //check user access or not.
        if(!ffw_is_user_capable()) {
            wp_send_json_error(esc_html__('Current user is not capable to perform it.', 'faq-for-woocommerce'));
            wp_die();
        }

        $review_actions = [ 'later', 'done', 'given' ];
        $notice         = isset( $_POST['notice'] ) ? sanitize_key( wp_unslash( $_POST['notice'] ) ) : '';
        if ( in_array( $notice, $review_actions, true ) ) {
            $value  = [
                'review_notice' => $notice,
                'updated_at'    => time(),
            ];

            if ( 'later' === $notice ) {
                update_option( 'ffw_review_notice_next_show_time', time() + ( DAY_IN_SECONDS * self::REVIEW_SNOOZE_DAYS ) );
            } else {
                update_user_meta( get_current_user_id(), 'ffw_review_notice_dismissed', true );
            }

            update_option( 'ffw_review_notice', $value );
            wp_send_json_success( $value );
            wp_die();
        }

        wp_send_json_error( esc_html__( 'Invalid Request.', 'faq-for-woocommerce' ) );
        wp_die();
    }
}

return new FFW_Admin_Notices();
