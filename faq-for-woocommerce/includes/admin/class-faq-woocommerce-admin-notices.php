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
        add_action( 'admin_notices', [$this, 'coupon_notice'] );
        //add_action( 'admin_notices', [$this, 'discount_banner_notice'] );
        add_action( 'wp_ajax_ffw_save_review_notice', [ $this, 'ffw_save_review_notice' ] );
        add_action( 'wp_ajax_ffw_hide_coupon_notice', [ $this, 'ffw_hide_coupon_notice' ] );

        add_action( 'admin_print_footer_scripts', [ $this, 'ffw_admin_footer_scripts' ] );
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
     * Coupon notice.
     * 
     * @return void
     */
    public function coupon_notice() {
        
        $is_coupon_notice_hide = get_option('ffw_coupon_notice_hide', false);

        if( $is_coupon_notice_hide ) {
            return;
        }
        ?>
        <div class="ffw-coupon-notice notice notice-success is-dismissible" style="background: #0A0A0A;border-left-color: #fff;padding: 10px 50px;">
            <p style="color: #fff;font-size: 18px;margin:0;"><?php _e('<strong style="color: #fff;text-decoration: line-through;">XPlainer Product FAQs</strong> is rebranded to <strong style="color: #fdfc1e;">`Happy WooCommerce FAQs`</strong>. <a href="https://happydevs.net/happy-woocommerce-faqs-pro" style="color: #bbf7d0;">Get <strong>20% discount</strong></a> on rebranding, Coupon: <strong style="color: #fdfc1e;">"IMHAPPY"</strong>.', 'faq-for-woocommerce'); ?></p>
        </div>
        <?php
    }

    /**
     * Display review notice
     * @global string $plugin_page
     * @return void
     */
    public function review_notice() {
        global $plugin_page;
        $nonce         = wp_create_nonce( 'ffw_admin' );
        $pluginName    = sprintf( '<b>%s</b>', esc_html__( 'Happy WooCommerce FAQs', 'faq-for-woocommerce' ) );
        $has_notice    = false;
        $user_id       = get_current_user_id();

        $next_timestamp             = get_option('ffw_review_notice_next_show_time');
        $review_notice_dismissed    = get_user_meta( $user_id, 'ffw_review_notice_dismissed', true );

        $count_faqs = ffw_get_faqs_post_list(true);
        if( count($count_faqs) > 5 ) {
            if ( ! empty($next_timestamp) ) {
                if ( ( time() > $next_timestamp ) ) {
                    $show_notice = true;
                }else {
                    $show_notice = false;
                }
            } else {
                if ( isset($review_notice_dismissed) && ! empty($review_notice_dismissed) ) {
                    $show_notice = false;
                }else {
                    $show_notice = true;
                }
            }
        } else {
            $show_notice = false;
        }

        // Review Notice.
        if ( $show_notice ) {
            $has_notice = true;
            ?>
            <div class="ffw-notice notice notice-info is-dismissible" style="line-height:1.5;font-size:14px;" data-which="rating" data-nonce="<?php echo esc_attr( $nonce ); ?>">
                <div class="ffw-notice-content">
                    <div class="ffw-review-notice-logo"></div>
                    <div class="ffw-review-notice-right">
                        <p>
                            <?php
                            $faq_posts_count = count($count_faqs) - 1;
                            printf(
                            /* translators: 1: plugin name,2: Slightly Smiling Face (Emoji), 3: line break 'br' tag */
                                esc_html__( 'Cool! This is great that you have made %3$s+ FAQs with %1$s. Can you please take 60 seconds to leave your feedback. This encourages us to continue providing the best features for you. %2$s', 'faq-for-woocommerce' ),
                                $pluginName, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                '<span style="font-size: 14px;">🙂</span>',
                                $faq_posts_count
                            );
                            ?>
                        </p>
                        <p>
                            <a class="button button-secondary" data-response="later" href="#"><?php esc_html_e( 'Remind me later', 'faq-for-woocommerce' ); ?></a>
                            <a class="button button-secondary" data-response="done" href="#" target="_blank"><?php esc_html_e( 'I already did!', 'faq-for-woocommerce' ); ?></a>
                            <a class="button button-primary" data-response="given" href="#" target="_blank"><?php esc_html_e( 'Review Here', 'faq-for-woocommerce' ); ?></a>
                        </p>
                    </div>
                </div>
            </div>
            <?php
        }

        if ( true === $has_notice ) {
            add_action( 'admin_print_footer_scripts', function() use ( $nonce ) {
                ?>
                <script>
                    (function($){
                        "use strict";
                        $(document)
                            .on('click', '.ffw-notice a.button', function (e) {
                                e.preventDefault();
                                // noinspection ES6ConvertVarToLetConst
                                var self = $(this), notice = self.attr('data-response');
                                if ( 'given' === notice ) {
                                    window.open('https://wordpress.org/support/plugin/faq-for-woocommerce/reviews/?rate=5#new-post', '_blank');
                                }
                                self.closest(".ffw-notice").slideUp( 200, 'linear' );
                                wp.ajax.post( 'ffw_save_review_notice', { nonce: '<?php echo esc_attr( $nonce ); ?>', notice: notice } );
                            })
                            .on('click', '.ffw-notice .notice-dismiss', function (e) {
                                e.preventDefault();
                                // noinspection ES6ConvertVarToLetConst
                                var self = $(this), ffw_notice = self.closest('.ffw-notice'), which = ffw_notice.attr('data-which');
                                wp.ajax.post( 'ffw_save_review_notice', { nonce: '<?php echo esc_attr( $nonce ); ?>', notice: 'later' } );
                            });

                    })(jQuery)
                </script>
                <?php
            }, 99 );
        }
    }

    /**
     * Show Review request admin notice
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
        if ( isset( $_POST['notice'] ) && in_array( $_POST['notice'], $review_actions ) ) {
            $value  = [
                'review_notice' => sanitize_text_field( $_POST['notice'] ), //phpcs:ignore
                'updated_at'    => time(),
            ];

            if ( 'done' === $_POST['notice'] ) {
                $user_id = get_current_user_id();
                add_user_meta( $user_id, 'ffw_review_notice_dismissed', true, true );
            }else {
                update_option( 'ffw_review_notice_next_show_time', time() + ( DAY_IN_SECONDS * 30 ) );
            }

            update_option( 'ffw_review_notice', $value );
            wp_send_json_success( $value );
            wp_die();
        }

        wp_send_json_error( esc_html__( 'Invalid Request.', 'faq-for-woocommerce' ) );
        wp_die();
    }
    
    /**
     * Hide coupon notice.
     * 
     * @return void
     */
    public function ffw_hide_coupon_notice() {
        // check and validate nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'ffw_admin' ) ) {
            wp_send_json_error( esc_html__( 'Invalid nonce', 'faq-for-woocommerce' ) );
        }

        update_option('ffw_coupon_notice_hide', true);

        wp_send_json_success( esc_html__( 'Coupon notice hide successfull.', 'faq-for-woocommerce' ) );
        wp_die();
    }

    /**
     * Admin footer scripts
     * 
     * @return void
     */
    public function ffw_admin_footer_scripts() {
        $nonce  = wp_create_nonce( 'ffw_admin' );
        ?>
        <script>
            (function($){
                "use strict";
                $(document).on('click', '.ffw-coupon-notice .notice-dismiss', function (e) {
                    e.preventDefault();

                    let nonce = '<?php echo esc_html($nonce); ?>';

                    wp.ajax.post( 'ffw_hide_coupon_notice', {
                        nonce: '<?php echo esc_html($nonce); ?>'
                    });
                })
            })(jQuery)
        </script>
        <?php
    }
}

return new FFW_Admin_Notices();
