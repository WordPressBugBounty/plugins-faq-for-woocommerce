<?php
if ( ! function_exists( 'ffw_check_woocommerce' ) ) {
	function ffw_check_woocommerce() {
		return class_exists( 'WooCommerce', false );
	}
}

if ( ! function_exists( 'ffw_is_WC_supported' ) ) {
	function ffw_is_WC_supported() {
		// Ensure WC is loaded before checking version
		return ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, FFW_MIN_WC_VERSION, '>=' ) );
	}
}

/*
 * Tab
 */
add_filter('woocommerce_product_data_tabs', 'ffw_product_settings_tabs' );
function ffw_product_settings_tabs( $tabs ) {
	$tabs['faq_for_woocommerce'] = array(
		'label'    => 'FAQs',
		'target'   => 'ffw_product_data',
		'priority' => 100,
	);
	
	return $tabs;
}


/*
 * Tab content
 */
add_action( 'woocommerce_product_data_panels', 'ffw_product_panels' );
function ffw_product_panels() {
    ?>
    <div id="ffw_product_data" class="panel ffw_options_panel woocommerce_options_panel hidden">
        <?php
        //faqs list
        if ( isset($_GET['post']) && ! empty($_GET['post']) ) {
            $post_id = sanitize_text_field( wp_unslash($_GET['post']) );
            $faq_post_ids = get_post_meta($post_id, 'ffw_product_faq_post_ids', true);
            $faq_post_ids = !empty($faq_post_ids) ? $faq_post_ids : [];
            $product_faqs_data = wp_json_encode($faq_post_ids);
            $faq_posts = ffw_get_faqs_post_list();
            ?>
                <?php include FFW_FILE_DIR . '/views/faq-woocommerce-modal-form.php'; ?>
                <div class="ffw-product-loader">
                    <div class="ffw-product-loader-overlay">
                        <span class="spinner is-active"></span>
                    </div>
                </div>
                <div class="ffw-product-form-header" id="ffw-product-form-header">
                    <?php do_action( 'before_faq_woocommerce_product_options' ); ?>
                    <div class="ffw-sortable-options-wrapper">
                        <div class="ffw-product-form-heading">
                            <?php echo sprintf('<h3 class="ffw-option-header-title">%s</h3>', esc_html__('FAQ List', 'faq-for-woocommerce')); ?>
                            <?php 
                            echo sprintf(
                                '<p>%s <span class="ffw-note">%s</span></p>', 
                                esc_html__('Manage current product FAQs here.', 'faq-for-woocommerce'), 
                                esc_html__('These controls will work if only products are assigned to FAQs instead of product categories.', 'faq-for-woocommerce')
                            ); 
                            ?>
                        </div>
                        <div class="ffw-sortable-options-header">
                            <select class="ffw_search" id="ffw_search">
                                <option value=""><?php esc_html_e('Select a FAQ', 'faq-for-woocommerce'); ?></option>
                                <?php
                                if( $faq_posts ) {
                                    foreach($faq_posts as $post) {
                                        echo sprintf('<option value="%s">%s</option>', esc_html($post->ID), esc_html($post->post_title));
                                    }
                                }
                                ?>

                            </select>
                            <div class="ffw-option-buttons">
                                <?php echo sprintf('<button class="ffw-add-new ffw-options-header-btn">%s</button>', esc_html__('Quick Add', 'faq-for-woocommerce')); ?>
                                <?php echo sprintf('<button class="ffw-delete-all ffw-options-header-btn" id="ffw-delete-all-faq">%s</button>', esc_html__('Delete All', 'faq-for-woocommerce')); ?>
                            </div>
                            <input type="hidden" id="ffw_products" value='<?php echo esc_html($product_faqs_data); ?>'>
                            <input type="hidden" id="ffw_product_page_id" value="<?php echo isset($_GET['post']) ? esc_html($_GET['post']) : ''; ?>">
                        </div>
                        <div class="ffw-body">
                            <?php
                            ffw_get_option_panel_body($_GET['post']);
                            ?>
                        </div>
                    </div>

                    <?php do_action( 'after_faq_woocommerce_product_options' ); ?>
                </div>
            <?php
        }else {
            echo sprintf('<div class="ffw-product-publish-msg">%s</div>', esc_html__("Please publish the product first to insert the faqs", "faq-for-woocommerce"));
        }
        ?>
    </div>
    <?php
}

if ( ! function_exists( 'ffw_insert_new_faq' ) ) {
	add_action( 'wp_ajax_ffw_insert_new_faq', 'ffw_insert_new_faq' );
	/**
	 * Insert FAQ
     *
     * @since 1.0.0
	 */
	function ffw_insert_new_faq() {
		//check_ajax_referer( 'ffw_admin' );

        // check and validate nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'ffw_admin' ) ) {
            wp_send_json_error( esc_html__( 'Invalid nonce', 'faq-for-woocommerce' ) );
        }

        //check user access or not.
        if(!ffw_is_user_capable()) {
            wp_send_json_error(esc_html__('Current user is not capable to perform it.', 'faq-for-woocommerce'));
            wp_die();
        }

		if ( ! isset( $_REQUEST['faq_data'] ) ) {
			wp_send_json_error( esc_html__( 'Invalid Request.', 'faq-for-woocommerce' ) );
			wp_die();
		}

		if ( ! isset( $_REQUEST['faq_data']['product_id'] ) ) {
			wp_send_json_error( esc_html__( 'Not in Edit page.', 'faq-for-woocommerce' ) );
			wp_die();
        }

		if( ! isset( $_REQUEST['faq_data']['question'] ) ) {
            wp_send_json_error( esc_html__( 'No Question added.', 'faq-for-woocommerce' ) );
            wp_die();
        }

        if( ! isset( $_REQUEST['faq_data']['answer'] ) ) {
            wp_send_json_error( esc_html__( 'No Answer added.', 'faq-for-woocommerce' ) );
            wp_die();
        }

		$product_id = (int) sanitize_text_field( wp_unslash($_REQUEST['faq_data']['product_id'] ));

		$new_faq_post                   = [];
        $new_faq_post['post_title']     = $_REQUEST['faq_data']['question'];
        $new_faq_post['post_content']   = $_REQUEST['faq_data']['answer'];
        $new_faq_post['post_type']      = 'ffw';
        $new_faq_post['post_status']    = 'publish';
        $inserted_post_id = wp_insert_post($new_faq_post);
        if( isset($_POST['faq_data']['faq_list']) ) {
            $faq_lists = $_POST['faq_data']['faq_list'];
        }else {
            $faq_lists = [];
        }

        array_push($faq_lists,  (string) $inserted_post_id);

		//update faq list
		$updated = update_post_meta( $product_id, 'ffw_product_faq_post_ids', $faq_lists );

		if ( $updated ) {
		    $msg = esc_html__('Update Successful!', 'faq-for-woocommerce');
        }else {
			$msg = esc_html__('Something Wrong!', 'faq-for-woocommerce');
        }

		ob_start();

		ffw_get_option_panel_body($product_id);

        $faq_body = ob_get_clean();

		if ( $updated ) {
            wp_send_json_success(
                [
                    'faq'           => $faq_body,
                    'new_post_id'   => $inserted_post_id,
                    'faq_data'      => $faq_lists,
                    'message'       => $msg,
                    'success'       => false,
                    'product_id'    => $product_id,
                ], 200
            );
            wp_die();
        } else {
            wp_send_json_error(
                [
                    'message' => esc_html__( 'No faq added.', 'faq-for-woocommerce' ),
                    'success' => false,
                ]
            );
            wp_die();
		}
	}
}

if ( ! function_exists( 'ffw_hide_discount_notice' ) ) {
	add_action( 'wp_ajax_ffw_hide_discount_notice', 'ffw_hide_discount_notice' );
	/**
	 * Hide Discounted Notice
     *
     * @since 1.4.1
	 */
	function ffw_hide_discount_notice() {
		//check_ajax_referer( 'ffw_admin' );

        // check and validate nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'ffw_admin' ) ) {
            wp_send_json_error( esc_html__( 'Invalid nonce', 'faq-for-woocommerce' ) );
        }

        //check user access or not.
        if(!ffw_is_user_capable()) {
            wp_send_json_error(esc_html__('Current user is not capable to perform it.', 'faq-for-woocommerce'));
            wp_die();
        }

        $updated  = update_option('ffw_dismiss_discounted_notice', 'yes');

		if ( $updated ) {
            wp_send_json_success(
                [
                    'message'  => esc_html__( 'Dismissal discounted notice.', 'faq-for-woocommerce' ),
                ], 200
            );
            wp_die();
        } else {
            wp_send_json_error(
                [
                    'message' => esc_html__( 'Could not able to dismiss discounted notice.', 'faq-for-woocommerce' ),
                    'success' => false,
                ]
            );
            wp_die();
		}
	}
}

if ( ! function_exists( 'ffw_delete_all_faqs' ) ) {
	add_action( 'wp_ajax_ffw_delete_all_faqs', 'ffw_delete_all_faqs' );
	/**
	 * Delete All FAQ
     *
     * @since 1.0.0
	 */
	function ffw_delete_all_faqs() {
		//check_ajax_referer( 'ffw_admin' );

        // check and validate nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'ffw_admin' ) ) {
            wp_send_json_error( esc_html__( 'Invalid nonce', 'faq-for-woocommerce' ) );
        }

        //check user access or not.
        if(!ffw_is_user_capable()) {
            wp_send_json_error(esc_html__('Current user is not capable to perform it.', 'faq-for-woocommerce'));
            wp_die();
        }

		if ( ! isset( $_REQUEST['faq_delete_data'] ) ) {
			wp_send_json_error( esc_html__( 'Invalid Request.', 'faq-for-woocommerce' ) );
			wp_die();
		}

		if ( ! isset( $_REQUEST['faq_delete_data']['product_id'] ) ) {
			wp_send_json_error( esc_html__( 'Not in Edit page.', 'faq-for-woocommerce' ) );
			wp_die();
        }

		$product_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['faq_delete_data']['product_id'] ) );

		//deleted all faq list
		$deleted = delete_post_meta( $product_id, 'ffw_product_faq_post_ids' );

		if ( $deleted ) {
		    $msg = esc_html__('Deleted All FAQ Successfull!', 'faq-for-woocommerce');
        }else {
			$msg = esc_html__('Something Wrong!', 'faq-for-woocommerce');
        }


		if ( $deleted ) {
            wp_send_json_success(
                [
                    'message' => $msg,
                    'success' => true,
                ], 200
            );
            wp_die();

        } else {
            wp_send_json_error(
                [
                    'message' => esc_html__( 'No faq deleted.', 'faq-for-woocommerce' ),
                    'success' => false,
                ]
            );
            wp_die();
		}
	}
}

if ( ! function_exists( 'ffw_delete_single_faq' ) ) {
	add_action( 'wp_ajax_ffw_delete_single_faq', 'ffw_delete_single_faq' );
	/**
	 * Delete Single FAQ
	 *
	 * @since 1.0.0
	 */
	function ffw_delete_single_faq() {
		//check_ajax_referer( 'ffw_admin' );

        // check and validate nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'ffw_admin' ) ) {
            wp_send_json_error( esc_html__( 'Invalid nonce', 'faq-for-woocommerce' ) );
        }

        //check user access or not.
        if(!ffw_is_user_capable()) {
            wp_send_json_error(esc_html__('Current user is not capable to perform it.', 'faq-for-woocommerce'));
            wp_die();
        }

		if ( ! isset( $_REQUEST['faq_updated_data'] ) ) {
			wp_send_json_error( esc_html__( 'Invalid Request.', 'faq-for-woocommerce' ) );
			wp_die();
		}

		if ( ! isset( $_REQUEST['faq_updated_data']['product_id'] ) ) {
			wp_send_json_error( esc_html__( 'Not in Edit page.', 'faq-for-woocommerce' ) );
			wp_die();
		}

		if ( ! isset( $_REQUEST['faq_updated_data']['updated_faq_list'] ) ) {
		    $update_faq_list = [];
		}else {
            $update_faq_list = $_REQUEST['faq_updated_data']['updated_faq_list'];
        }

		//remove empty field value from the faq list
        $update_faq_list = array_filter($update_faq_list, 'strlen');

		$product_id = (int) sanitize_text_field( wp_unslash($_REQUEST['faq_updated_data']['product_id'] ) );

		//delete current faq and update faq list
		$updated = update_post_meta( $product_id, 'ffw_product_faq_post_ids', $update_faq_list );

		if ( $updated ) {
			$msg = esc_html__('Delete Successfull!', 'faq-for-woocommerce');
			wp_send_json_success(
				[
                    'update_faq_list' => $update_faq_list,
					'message' => $msg,
					'success' => false,
				], 200
			);
			wp_die();
		}else {
			$msg = esc_html__('Something Wrong!', 'faq-for-woocommerce');
			wp_send_json_error(
				[
					'message' => esc_html__( 'No faq deleted.', 'faq-for-woocommerce' ),
					'success' => false,
				]
			);
			wp_die();
		}
	}
}

if ( ! function_exists( 'ffw_sort_faq_data' ) ) {
    add_action( 'wp_ajax_ffw_sort_faq_data', 'ffw_sort_faq_data' );
    /**
     * Sort All FAQ
     *
     * @since 1.3.0
     */
    function ffw_sort_faq_data() {
        //check_ajax_referer( 'ffw_admin' );

        // check and validate nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'ffw_admin' ) ) {
            wp_send_json_error( esc_html__( 'Invalid nonce', 'faq-for-woocommerce' ) );
        }

        //check user access or not.
        if(!ffw_is_user_capable()) {
            wp_send_json_error(esc_html__('Current user is not capable to perform it.', 'faq-for-woocommerce'));
            wp_die();
        }

        if ( ! isset( $_REQUEST['faq_sorted_data'] ) ) {
            wp_send_json_error( esc_html__( 'Invalid Request.', 'faq-for-woocommerce' ) );
            wp_die();
        }

        if ( ! isset( $_REQUEST['faq_sorted_data']['product_id'] ) ) {
            wp_send_json_error( esc_html__( 'Not in Edit page.', 'faq-for-woocommerce' ) );
            wp_die();
        }

        if ( ! isset( $_REQUEST['faq_sorted_data']['faq_sorted_list'] ) ) {
            $faq_sorted_list = [];
        } else {
            $faq_sorted_list = $_REQUEST['faq_sorted_data']['faq_sorted_list'];
        }

        $product_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['faq_sorted_data']['product_id'] ) );

        //updated faq sorted data
        $updated = update_post_meta( $product_id, 'ffw_product_faq_post_ids', $faq_sorted_list );

        if ( $updated ) {
            $msg = esc_html__('Updated All FAQ Successfully!', 'faq-for-woocommerce');
        }else {
            $msg = esc_html__('Something Wrong!', 'faq-for-woocommerce');
        }


        if ( $updated ) {
            wp_send_json_success(
                [
                    'message' => $msg,
                    'success' => true,
                ], 200
            );
            wp_die();

        } else {
            wp_send_json_error(
                [
                    'message' => esc_html__( 'No faq deleted.', 'faq-for-woocommerce' ),
                    'success' => false,
                ]
            );
            wp_die();
        }
    }
}

if ( ! function_exists( 'ffw_insert_data_from_search' ) ) {
    add_action( 'wp_ajax_ffw_insert_data_from_search', 'ffw_insert_data_from_search' );
    /**
     * Insert search FAQs datas
     *
     * @since 1.3.0
     */
    function ffw_insert_data_from_search() {
        //check_ajax_referer( 'ffw_admin' );

        // check and validate nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'ffw_admin' ) ) {
            wp_send_json_error( esc_html__( 'Invalid nonce', 'faq-for-woocommerce' ) );
        }

        //check user access or not.
        if(!ffw_is_user_capable()) {
            wp_send_json_error(esc_html__('Current user is not capable to perform it.', 'faq-for-woocommerce'));
            wp_die();
        }

        if ( ! isset( $_REQUEST['faq_insert_search_data'] ) ) {
            wp_send_json_error( esc_html__( 'Invalid Request.', 'faq-for-woocommerce' ) );
            wp_die();
        }

        if ( ! isset( $_REQUEST['faq_insert_search_data']['product_id'] ) ) {
            wp_send_json_error( esc_html__( 'No product id.', 'faq-for-woocommerce' ) );
            wp_die();
        }

        $new_item_id = $_REQUEST['faq_insert_search_data']['new_item_id'];

        $product_id = (int) $_REQUEST['faq_insert_search_data']['product_id'];

        $old_faq_data = get_post_meta($product_id, 'ffw_product_faq_post_ids', true);
        $old_faq_data = empty($old_faq_data) ? [] : $old_faq_data;

        if( ! in_array($new_item_id, $old_faq_data) ) {

            if( isset($_REQUEST['faq_insert_search_data']['product_faqs']) ) {
                $data = $_REQUEST['faq_insert_search_data']['product_faqs'];
            }else {
                $data = [];
            }

            array_push($data, $new_item_id);

            $updated = update_post_meta($product_id, 'ffw_product_faq_post_ids', $data);

            ob_start();

            ffw_get_option_panel_body($product_id);

            $faq_body = ob_get_clean();

            if ( $updated ) {
                $msg = esc_html__('FAQ updated Successfully!', 'faq-for-woocommerce');
            }else {
                $msg = esc_html__('Something Wrong!', 'faq-for-woocommerce');
            }


            if ( $updated ) {
                wp_send_json_success(
                    [
                        'faq_body'      => $faq_body,
                        'updated_data'  => $data,
                        'message' => $msg,
                        'success' => true,
                    ], 200
                );
                wp_die();

            } else {
                wp_send_json_error(
                    [
                        'message' => esc_html__( 'No faq datas.', 'faq-for-woocommerce' ),
                        'success' => false,
                    ]
                );
                wp_die();
            }
        }


    }
}


if ( ! function_exists( 'ffw_activate_template' ) ) {
    add_action( 'wp_ajax_ffw_activate_template', 'ffw_activate_template' );
    /**
     * Activate faq template
     *
     * @since 1.4.5
     */
    function ffw_activate_template() {
        //check_ajax_referer( 'ffw_admin' );

        if ( ! isset( $_REQUEST['nonce'] ) ) {
            wp_send_json_error( esc_html__( 'Nonce missing.', 'faq-for-woocommerce' ) );
            wp_die();
        }

        //check user access or not.
        if(!ffw_is_user_capable()) {
            wp_send_json_error(esc_html__('Current user is not capable to perform it.', 'faq-for-woocommerce'));
            wp_die();
        }

        // check and validate nonce.
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'ffw_admin' ) ) {
            wp_send_json_error( esc_html__( 'Invalid nonce', 'faq-for-woocommerce' ) );
        }
        
        if ( ! isset( $_REQUEST['template_id'] ) ) {
            wp_send_json_error( esc_html__( 'Template id missing.', 'faq-for-woocommerce' ) );
            wp_die();
        }

        $template_id = wp_unslash($_REQUEST['template_id']);

        $ffw_options = get_option( 'ffw_general_settings' );

        $ffw_options['ffw_layout'] = $template_id;

        $updated = update_option('ffw_general_settings', $ffw_options);

        if ( $updated ) {
            wp_send_json_success(
                [
                    'message' => esc_html__( 'FAQ option is updated.', 'faq-for-woocommerce' ),
                    'success' => true,
                ], 200
            );
            wp_die();

        } else {
            wp_send_json_error(
                [
                    'message' => esc_html__( 'FAQ option is not updated.', 'faq-for-woocommerce' ),
                    'success' => false,
                ]
            );
            wp_die();
        }


    }
}

/**
 * Get Setting instance free & pro, filter this instance to add pro setting instance
 * 
 * @since 1.4.2
 */
function ffw_get_setting_instance() {
    return apply_filters("ffw_filter_setting_instance", FAQ_Woocommerce_Settings::instance()); 
}

/**
 * Dashboard Header
 */
function ffw_dashboard_header() {
    $content = "";
    
    ob_start();

    include FFW_FILE_DIR . '/views/dashboard/header.php';

	$content .= ob_get_clean();

	echo wp_kses_post($content);
}

/**
 * Display dashboard header
 *
 * @return void
 */
function ffw_display_dashboard_header() {

    $screen = get_current_screen();
	
    if ( empty( $screen->parent_file ) or $screen->parent_file != 'edit.php?post_type=ffw' ) {
        return;
    }

    if( isset($_GET['page']) && ($_GET['page'] === "ffw-dashboard" || $_GET['page'] === "woocommerce-faq") ) {
        return;
    }

    $content = "";
			
    ob_start();

    ?>
    <div class="ffw-admin-wrapper">
        <?php
            include FFW_FILE_DIR . '/views/dashboard/header.php';
        ?>
    </div>
    <?php

    $content .= ob_get_clean();

    echo wp_kses_post($content);
}
add_action( 'admin_notices', 'ffw_display_dashboard_header' );

// Filter Templates Name
add_filter('ffw_filter_template_names', 'ffw_filter_template_names');
function ffw_filter_template_names($templates) {
    $pro_templates = [ 'Glow', 'Smart', 'Wow', 'Zoom'];

    $templates = array_merge($templates, $pro_templates);

    return $templates;
}

/**
 * Check if user capable to access or not.
 *
 * @return boolean
 */
function ffw_is_user_capable() {
    global $current_user;
    $roles = $current_user->roles;

    $settings = get_option( 'ffw_general_settings' );
    $saved_roles = isset($settings['ffw_set_role']) && !empty($settings['ffw_set_role']) ? $settings['ffw_set_role'] : ['administrator'];

    //check if current user role matched to save roles.
    if(array_intersect($roles, $saved_roles)) {
        return true;
    }

    return false;
}

/**
 * Customer list image section.
 *
 * @return boolean
 */
function ffw_add_pro_customer_list_section() {
    $screen = get_current_screen();

    if (ffw_is_pro_activated()) {
        return;
    }

    if ($screen->post_type === 'ffw_customer_qna' && $screen->base === 'edit') {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.wrap').append('<a href="<?php echo esc_url(FFW_PRO_URL); ?>" class="ffw-customer-question-list-image-wrap"></a>');
            });
        </script>
        <style>
            body .wrap {
                position: relative;
            }

            body .wrap > * {
                display: none;
            }

            body .wrap .ffw-customer-question-list-image-wrap {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 820px;
                overflow: hidden;
                z-index: 999;
                display: block;
                background: url(<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/customer-question-list.png'); ?>) no-repeat;
                background-size: contain;
            }
        </style>
        <?php
    }
}
add_action('admin_footer', 'ffw_add_pro_customer_list_section');
