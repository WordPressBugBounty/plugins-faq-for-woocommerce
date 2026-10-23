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

            wp_register_style( 'select2css', FFW_PLUGIN_URL . '/assets/admin/css/ffw-select2.min.css', array(), '4.1.0-rc.0', 'all' );
            wp_register_script( 'select2', FFW_PLUGIN_URL . '/assets/admin/js/ffw-select2.min.js', array( 'jquery' ), '4.1.0-rc.0', true );

            // Add RTL support for admin styles.
            //wp_style_add_data( 'ffw_admin_styles', 'rtl', 'replace' );

            // enqueue CSS.
            if( "ffw_page_woocommerce-faq" === $needle ) {
                wp_enqueue_style( 'ffw_bootstrap' );

                // Toggle icon picker in Settings → Style.
                FAQ_Woocommerce_Icons::register_style();
                wp_enqueue_style( 'ffw-toggle-icons' );
                wp_add_inline_style( 'ffw-toggle-icons', '
                    .form-table tr.ffw-row-full{flex:0 0 100% !important;width:100% !important;max-width:100% !important}
                    .form-table tr.ffw-row-full td{width:auto;max-width:100%}
                    .ffw-icon-picker{display:grid;grid-template-columns:repeat(auto-fill,minmax(104px,1fr));gap:8px;max-width:100%}
                    .ffw-icon-choice{position:relative;display:flex;flex-direction:column;align-items:center;gap:8px;padding:12px 8px 10px;border:1px solid #e2e4e7;border-radius:12px;background:#fff;cursor:pointer;text-align:center;transition:border-color .15s,box-shadow .15s}
                    .ffw-icon-choice:hover{border-color:#a7aaad}
                    .ffw-icon-choice.is-selected{border-color:#2271b1;box-shadow:0 0 0 1px #2271b1}
                    .ffw-icon-choice input{position:absolute;opacity:0;pointer-events:none}
                    .ffw-icon-choice input:focus-visible + .ffw-icon-choice-preview{outline:2px solid #2271b1;outline-offset:3px;border-radius:6px}
                    .ffw-icon-choice-preview{display:flex;align-items:center;justify-content:center;gap:10px;height:28px;color:#1d2327}
                    .ffw-icon-choice-preview .ffw-toggle-icon{--ffw-icon-size:22px}
                    .ffw-icon-choice-text{font-size:15px;font-weight:600;color:#50575e}
                    .ffw-icon-choice-label{font-size:12px;line-height:1.3;color:#3c434a}
                    .ffw-icon-style-fields{display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px}
                    .ffw-icon-style-fields label{display:flex;flex-direction:column;gap:4px}
                    .ffw-icon-style-fields input[type=number]{width:90px}
                ' );
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

                if('ffw' == $post_type || 'ffw_group' == $post_type || 'product' == $post_type ) {
                    wp_enqueue_style( 'select2css' );
                    wp_enqueue_script( 'select2' );
                }

                if('ffw' == $post_type || 'ffw_group' == $post_type || 'ffw_customer_qna' == $post_type) {
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
                        if ( ffw_is_woocommerce_active() ) {
                            wp_enqueue_style( 'woocommerce_admin_styles' );
                        }
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
            $dep = array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'wp-color-picker');
            $woo_script_deps = array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core' );

            if ( ffw_is_woocommerce_active() ) {
                $dep[] = 'jquery-blockui';
                $dep[] = 'jquery-tiptip';
                $dep[] = 'wc-enhanced-select';
                $woo_script_deps[] = 'jquery-blockui';
                $woo_script_deps[] = 'jquery-tiptip';
            }

            // Register scripts.
            wp_register_script( 'ffw_bootstrap_js', FFW_PLUGIN_URL . '/assets/admin/js/bootstrap.min.js', $woo_script_deps, time(), true );
            wp_register_script( 'ffw_sweetalert', FFW_PLUGIN_URL . '/assets/admin/js/ffw-sweetalert.all.min.js', $woo_script_deps, time(), true );
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
            } elseif( isset($_GET['post_type']) && in_array( $_GET['post_type'], array( 'ffw', 'ffw_group' ), true ) ) {
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
