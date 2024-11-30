<?php
/**
 * Load assets
 *
 * @package FAQ_Woocommerce\Admin
 * @version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'FAQ_Woocommerce_Admin_Assets', false ) ) :

    /**
     * FAQ_Woocommerce_Admin_Assets Class.
     */
    class FAQ_Woocommerce_Admin_Assets {

        /**
         * Hook in tabs.
         */
        public function __construct() {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ), 999 );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 999 );
        }

        /**
         * Enqueue styles.
         */
        public function admin_styles($needle) {

            // Register admin styles.
            wp_register_style( 'ffw_bootstrap', FFW_PLUGIN_URL . '/assets/admin/css/bootstrap.min.css', array(), time() );
            wp_register_style( 'ffw_admin_styles', FFW_PLUGIN_URL . '/assets/admin/css/faq-woocommerce-admin.min.css', array(), time() );
            wp_register_style( 'ffw_admin_popup_styles', FFW_PLUGIN_URL . '/assets/admin/css/faq-woocommerce-popup.min.css', array(), time() );

            wp_register_style( 'select2css', '//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', false, '1.0', 'all' );
            wp_register_script( 'select2', '//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '1.0', true );

            // Add RTL support for admin styles.
            //wp_style_add_data( 'ffw_admin_styles', 'rtl', 'replace' );

            // enqueue CSS.
            if( "ffw_page_woocommerce-faq" === $needle ) {
                wp_enqueue_style( 'ffw_bootstrap' );
            }

            
            global $post;

            $post_type = '';
            if(isset($post->post_type)) {
                $post_type = $post->post_type;
            } else {
                if(isset($_GET['post_type'])) {
                    $post_type = wp_unslash($_GET['post_type']);
                }
            }

            if( isset($post_type) ) {

                if(empty($post_type)) {
                    if(isset($_GET['page']) && $_GET['page']=== 'ffw-dashboard') {
                        wp_enqueue_style( 'select2css' );
                        wp_enqueue_script( 'select2' );
                        wp_enqueue_style( 'ffw_admin_styles' );
                    }
                }

                if('ffw' == $post_type || 'product' == $post_type ) {
                    wp_enqueue_style( 'select2css' );
                    wp_enqueue_script( 'select2' );
                }

                if('ffw' == $post_type || 'ffw_customer_qna' == $post_type) {
                    wp_enqueue_style( 'ffw_admin_styles' );

                    ?>
                        <style>
                            .notice {
                                display: none !important;
                            }
                        </style>
                    <?php
                }elseif('product' == $post_type) {

                    if(isset($_GET['post']) && isset($_GET['action']) && 'edit' === $_GET['action']) {
                        wp_enqueue_style( 'ffw_admin_styles' );
                        wp_enqueue_style( 'woocommerce_admin_styles' );
                        wp_enqueue_style( 'ffw_admin_popup_styles' );
                    }

                }
            }
        }


        /**
         * Enqueue scripts.
         */
        public function admin_scripts($needle) {
            global $wp_query, $post;

            // Add the color picker css file
            wp_enqueue_style( 'wp-color-picker' );


            $post_type = '';
            $page_action = '';
            if( isset($_GET['post']) && isset($_GET['action']) ) {
                $post_type = get_post_type($_GET['post']);
                $page_action = wp_unslash($_GET['action']);
            }

            // dependency
            $dep = array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip', 'wp-color-picker');

            if ( class_exists( 'WooCommerce' ) ) {
                $dep[] = 'wc-enhanced-select';
            }

            // Register scripts.
            wp_register_script( 'ffw_bootstrap_js', FFW_PLUGIN_URL . '/assets/admin/js/bootstrap.min.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), time(), true );
            wp_register_script( 'ffw_sweetalert', FFW_PLUGIN_URL . '/assets/admin/js/ffw-sweetalert.all.min.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), time(), true );
            wp_register_script( 'ffw_admin', FFW_PLUGIN_URL . '/assets/admin/js/faq-woocommerce-admin.min.js', $dep, time(), true );
			wp_localize_script( 'ffw_admin', 'ffw_admin', [
				'ajaxurl'               => admin_url( 'admin-ajax.php' ),
				'nonce'                 => wp_create_nonce( 'ffw_admin' ),
                'current_post_type'     => $post_type,
                'current_page_action'   => $page_action,
                'faw_list_page_url'     => admin_url('edit.php?post_type=ffw'),
                'strings'               => [
                    'ai_item_selected'  => esc_html__('No Item selected', 'faq-for-woocommerce')
                ]
			] );

            //Enqueue scripts
            if( "ffw_page_woocommerce-faq" === $needle ) {
                wp_enqueue_script( 'ffw_bootstrap_js' );
            }

            if ( isset($_GET['post']) && isset($_GET['action']) && 'edit' === $_GET['action'] ) {
                wp_enqueue_script( 'ffw_sweetalert' );
                wp_enqueue_script( 'ffw_admin' );
            } elseif( isset($_GET['post_type']) && 'ffw' === $_GET['post_type'] ) {
                wp_enqueue_script( 'ffw_admin' );
            } elseif( isset($_GET['page']) && 'woocommerce-faq' === $_GET['page'] ) {
                wp_enqueue_script( 'ffw_admin' );
            } elseif( isset($_GET['post_type']) && 'ffw_customer_qna' === $_GET['post_type'] ) {
                wp_enqueue_script( 'ffw_admin' );
            }


        }

    }

endif;

return new FAQ_Woocommerce_Admin_Assets();
