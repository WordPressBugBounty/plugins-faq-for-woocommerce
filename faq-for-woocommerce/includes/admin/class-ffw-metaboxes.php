<?php
/**
 * Add Metaboxes
 *
 * @package FAQ_Woocommerce\Admin
 * @version 1.4.0
 */
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('FFW_Metaboxes', false)) :

    /**
     * FFW_Metaboxes Class.
     */
    class FFW_Metaboxes
    {

        /**
         * Hook in tabs.
         */
        public function __construct()
        {
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
            add_action( 'save_post', array( $this, 'save' ) );
            add_action( 'ffw_metabox_content_item', array( $this, 'global_faqs_box' ), 20 );
            add_action( 'ffw_metabox_content_item', array( $this, 'product_attribute_box' ), 20 );
            add_action( 'ffw_metabox_content_item', array( $this, 'product_search_box' ) );
        }

        /**
         * Get product support, Specific/Global product support.
         *
         */
        public function get_product_support() {
            return apply_filters('ffw_is_global_product_support_enable', false);
        }

        /**
         * Add Metaboxes.
         *
         * @param mixed $post_type post type
         */
        public function add_meta_boxes($post_type) {
            // metabox for `ffw` post type.
            $post_types = array( 'ffw' );

            if ( in_array( $post_type, $post_types ) ) {
                add_meta_box(
                    'ffw_faq_meta_settings',
                    esc_html__( 'Happy FAQs Panel', 'faq-for-woocommerce' ),
                    array( $this, 'meta_box_content' ),
                    $post_type,
                    'normal',
                    'high'
                );
            }
        }

        /**
         * Meta Box content.
         *
         * @param WP_Post $post The post object.
         */
        public function meta_box_content( $post ) {

            do_action('ffw_metabox_content_before');

                ?>
                <div class="ffw-metabox-wrapper woocommerce">
                    <table class="form-table ffw-admin-form-table">
                        <?php
                            do_action('ffw_metabox_content_item', $post);
                        ?>
                    </table>
                </div>
                <?php

            do_action('ffw_metabox_content_after');


        }

        /**
         * Product search box should appear here, search and select products.
         *
         * @param mixed $post post object
         */
        public function product_search_box($post) {

            //faq post ID
            $faq_id = $post->ID;

            $product_type = array('product');
            $exclude_type = '';
            if( ffw_is_pro_activated() ) {
                array_push($product_type, 'product_variation');
                $exclude_type = 'variation';
            }

            // add nonce field
            wp_nonce_field( 'ffw_faq_product_settings', 'ffw_faq_product_settings_nonce' );

            $is_global_product_support_enable = $this->get_product_support();

            if( ! $is_global_product_support_enable ) {
                // get product ids with faqs
                $args = array(
                    'post_type'  => $product_type,
                    'meta_query' => array(
                        'relation' => 'OR',
                        array(
                            'key'     => 'ffw_product_faq_post_ids',
                            'value' => serialize(strval($faq_id)),
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => 'ffw_product_faq_post_ids',
                            'value'   => serialize(intval($faq_id)),
                            'compare' => 'LIKE'
                        ),
                    ),
                    'fields' => 'ids',
                    'posts_per_page' => -1,
                );
                $product_ids = get_posts( $args );

                // get global faq value
                $is_global_faq = get_post_meta($faq_id, 'ffw_is_global_faq', true);

                $classlist = array('ffw-product-search-row');

                $options = get_option( 'ffw_general_settings' );
                $options = ! empty( $options ) ? $options : [];

                if( isset($options['enable_global_faqs']) ) {
                    if( $is_global_faq ) {
                        array_push($classlist, 'ffw-hide');
                    }
                }

                $classlist = implode(' ', $classlist);
            ?>

                <!-- FAQ Product Option -->
                <tr class="<?php echo esc_attr($classlist); ?>">
                    <th scope="row" class="titledesc">
                        <label for="ffw_faq_products">
                            <?php esc_html_e( 'Products', 'faq-for-woocommerce' ); ?>
                        </label>
                    </th>
                    <td class="forminp forminp-multi-select-search">
                        <select
                                name="ffw_faq_products[]"
                                class="wc-product-search"
                                multiple="multiple"
                                id="ffw_faq_products"
                                data-allow_clear="true"
                                data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'faq-for-woocommerce' ); ?>"
                                data-exclude_type="<?php echo esc_attr($exclude_type); ?>"
                                data-action="woocommerce_json_search_products_and_variations"
                        >
                            <?php
                            if( isset($product_ids) && ! empty($product_ids) ) {
                                foreach ( $product_ids as $product_id ) {
                                    $product = wc_get_product( $product_id );
                                    $faq_ids = get_post_meta($product_id, 'ffw_product_faq_post_ids', true);

                                    if ( $product ) {
                                        if( isset($faq_ids) && ! empty($faq_ids) && is_array($faq_ids) && in_array($faq_id, $faq_ids) ) {
                                            echo '<option value="' . esc_attr( $product_id ) . '" selected="selected">' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
                                        }
                                    }
                                }
                            }
                            ?>
                        </select>
                        <input type="hidden" name="ffw_faq_save_products" value="<?php echo esc_html(implode(',', $product_ids)); ?>">
                        <p class="description">
                            <?php esc_html_e('Search and Select products for the FAQ.', 'faq-for-woocommerce'); ?>
                            <span class="ffw-note"><?php esc_html_e('If product categories are assigned, FAQs will be fetched from the product categories first.', 'faq-for-woocommerce'); ?></span>
                        </p>
                    </td>
                </tr>

                <!-- FAQ Categories Option -->
                <tr class="<?php echo esc_attr($classlist); ?>">
                    <th scope="row" class="titledesc">
                        <label for="ffw_faq_categories">
                            <span><?php esc_html_e( 'Product Categories', 'faq-for-woocommerce' ); ?></span>
                        </label>
                    </th>
                    <td class="forminp forminp-multi-select-search">
                        <select
                            name="ffw_faq_categories[]"
                            multiple="multiple"
                            id="ffw_faq_categories"
                            class="ffw-select2 ffw-category-select2"
                            placeholder="<?php esc_html_e('search categories', 'faq-for-woocommerce'); ?>"
                        >
                        <?php
                        $settings = FAQ_Woocommerce_Settings::instance();
                        $categories =  $settings->get_product_categories();

                        $cat_ids = [];
                        if( isset($categories) && ! empty($categories) ) {
                            $disabled = !ffw_is_pro_activated() ? esc_attr('disabled') : '';
                            foreach ( $categories as $cat_id => $cat_name ) {
                                $faq_ids = get_term_meta($cat_id, 'ffw_cat_faq_post_ids', true);

                                if ( !empty($cat_name) ) {
                                    $selected = '';
                                    if( isset($faq_ids) && ! empty($faq_ids) && is_array($faq_ids) ) {
                                        $selected = selected(in_array($faq_id, $faq_ids), true, false);
                                    }
                                    
                                    echo '<option value="' . esc_attr( $cat_id ) . '" ' . esc_attr($selected) . '>' . wp_kses_post( $cat_name ) . '</option>';
                                
                                    if($selected) {
                                        array_push($cat_ids, $cat_id);
                                    }
                                }
                            }

                            $cat_ids = array_unique($cat_ids);
                        }
                        ?>
                        </select>
                        <input type="hidden" name="ffw_faq_save_categories" value="<?php echo esc_html(implode(',', $cat_ids)); ?>">
                        <p class="description"><?php esc_html_e('Select product categories for the FAQ.', 'faq-for-woocommerce'); ?></p>
                    </td>
                </tr>
                
                <!-- FAQ Tags Option -->
                <tr class="<?php echo esc_attr($classlist); ?>">
                    <th scope="row" class="titledesc">
                        <label for="ffw_faq_tags">
                            <span><?php esc_html_e( 'Product Tags', 'faq-for-woocommerce' ); ?></span>
                        </label>
                    </th>
                    <td class="forminp forminp-multi-select-search">
                        <select
                            name="ffw_faq_tags[]"
                            multiple="multiple"
                            id="ffw_faq_tags"
                            class="ffw-select2 ffw-tag-select2"
                            placeholder="<?php esc_html_e('search tags', 'faq-for-woocommerce'); ?>"
                        >
                        <?php
                        $settings   = FAQ_Woocommerce_Settings::instance();
                        $tags       =  $settings->get_product_tags();

                        $tag_ids = [];
                        if( isset($tags) && ! empty($tags) ) {
                            $disabled = !ffw_is_pro_activated() ? esc_attr('disabled') : '';

                            foreach ( $tags as $tag_id => $tag_name ) {
                                $faq_ids = get_term_meta($tag_id, 'ffw_tag_faq_post_ids', true);

                                if ( !empty($tag_name) ) {
                                    $selected = '';
                                    if( isset($faq_ids) && ! empty($faq_ids) && is_array($faq_ids) ) {
                                        $selected = selected(in_array($faq_id, $faq_ids), true, false);
                                    }
                                    
                                    echo '<option value="' . esc_attr( $tag_id ) . '" ' . esc_attr($selected) . '>' . wp_kses_post( $tag_name ) . '</option>';
                                
                                    if($selected) {
                                        array_push($tag_ids, $tag_id);
                                    }
                                }
                            }

                            $tag_ids = array_unique($tag_ids);
                        }
                        ?>
                        </select>
                        <input type="hidden" name="ffw_faq_save_tags" value="<?php echo esc_html(implode(',', $tag_ids)); ?>">
                        <p class="description"><?php esc_html_e('Select product tags for the FAQ.', 'faq-for-woocommerce'); ?></p>
                    </td>
                </tr>

                <!-- Display in Pages Option -->
                <tr>
                    <th scope="row" class="titledesc">
                        <label for="ffw_faq_type">
                            <span><?php esc_html_e( 'Display in Pages', 'faq-for-woocommerce' ); ?></span>
                        </label>
                    </th>
                    <td class="">
                        <select
                            name="ffw_display_in_pages[]"
                            multiple="multiple"
                            id="ffw_display_in_pages"
                            class="ffw-select2 ffw-display_in_pages-select2"
                            placeholder="<?php esc_html_e('Select Woo Pages', 'faq-for-woocommerce'); ?>"
                        >
                        <?php
                        $page_types =  [
                            'product_and_archive_page' => esc_html__('Product & Archive Page', 'faq-for-woocommerce'),
                            'shop_page' => esc_html__('Shop Page', 'faq-for-woocommerce'),
                            'cart_page' => esc_html__('Cart Page', 'faq-for-woocommerce'),
                            'checkout_page' => esc_html__('Checkout Page', 'faq-for-woocommerce'),
                            // 'account_page' => esc_html__('Account Page', 'faq-for-woocommerce'),
                        ];

                        $page_type_ids = [];
                        if( isset($page_types) && ! empty($page_types) ) {
                            $disabled = !ffw_is_pro_activated() ? esc_attr('disabled') : '';
                            foreach ( $page_types as $page_type_id => $page_type ) {
                                $page_faq_saved_ids = get_option("ffw_{$page_type_id}_faqs");

                                $selected = '';
                                $disabled = '';
                                $pro_label = '';
                                if( !ffw_is_pro_activated() && $page_type_id !== 'product_and_archive_page' ) {
                                    $disabled = 'disabled';
                                    $pro_label = ' [PRO]';
                                }

                                if( isset($page_faq_saved_ids) && ! empty($page_faq_saved_ids) && is_array($page_faq_saved_ids) ) {
                                    $selected = selected(in_array($faq_id, $page_faq_saved_ids), true, false);
                                }

                                ?>
                                <option value="<?php echo esc_attr($page_type_id); ?>" <?php echo esc_attr($selected); ?> <?php echo esc_attr($disabled); ?> ><?php echo wp_kses_post( $page_type ); ?><?php echo wp_kses_post( $pro_label ); ?></option>
                                <?php
                                if($selected) {
                                    array_push($page_type_ids, $page_type_id);
                                }
                            }

                            $page_type_ids = array_unique($page_type_ids);
                        }
                        ?>
                        </select>
                        <input type="hidden" name="ffw_page_save_types" value="<?php echo esc_html(implode(',', $page_type_ids)); ?>">
                        <p class="description"><?php esc_html_e('Select pages to display FAQs.', 'faq-for-woocommerce'); ?></p>
                    </td>
                </tr>
            <?php
            }
        }

        public function global_faqs_box() {
            if(!ffw_is_pro_activated()) :
            ?>
            <tr class="ffw-global-faqs-row">
                <th scope="row" class="titledesc">
                    <label for="ffw_global_faqs">
                        <span><?php esc_html_e( 'Global FAQs', 'faq-for-woocommerce' ); ?></span>
                        <div class="ffw-get-pro-wrapper">
                            <div class="ffw-get-pro-badge">
                                <span><?php esc_html_e('pro', 'faq-for-woocommerce'); ?></span>
                            </div>
                        </div>
                    </label>
                </th>
                <td class="">
                    <div class="ffw-switch">
                        <input 
                        type="checkbox" 
                        class="ffw-switch-global-faq-checkbox" 
                        name="ffw_global_faq_checkbox" 
                        style="border: none;"
                        disabled>
                        <span class="ffw-switch-slider ffw-switch-round"></span>
                    </div>
                    <p class="description"><?php esc_html_e('Enable to make this faq as Global FAQ, the faq will be displayed for all the products if enabled.', 'faq-for-woocommerce'); ?></p>
                </td>
            </tr>
            <?php
            endif;
        }

        public function product_attribute_box() {
            if(!ffw_is_pro_activated()) :
                ?>
                <tr class="ffw-attribute-dropdown-area">
                    <th scope="row" class="titledesc">
                        <label for="ffw_dynamic_attribute_label">
                            <span><?php esc_html_e( 'Product Attributes', 'faq-for-woocommerce' ); ?></span>
                            <div class="ffw-get-pro-wrapper">
                                <div class="ffw-get-pro-badge">
                                    <span><?php esc_html_e('pro', 'faq-for-woocommerce'); ?></span>
                                </div>
                            </div>
                        </label>
                    </th>
                    <td class="">
                        <select class="ffw-product-attributes-select" disabled>
                            <option>Select Attributes</option>
                        </select>
                        <p class="description"><?php esc_html_e('Select any product attribute and Paste the copied attribute to the product content.', 'faq-for-woocommerce'); ?></p>
                    </td>
                </tr>
                <?php
            endif;
        }

        /**
         * Save the meta when the post is saved.
         *
         * @param int $post_id The ID of the post being saved.
         */
        public function save( $post_id ) {

            // when nonce is not set, do nothing
            if ( ! isset( $_POST['ffw_faq_product_settings_nonce'] ) ) {
                return $post_id;
            }

            $nonce = wp_unslash($_POST['ffw_faq_product_settings_nonce']);

            // Verify that the nonce is valid.
            if ( ! wp_verify_nonce( $nonce, 'ffw_faq_product_settings' ) ) {
                return $post_id;
            }

            /*
             * If this is an autosave, our form has not been submitted,
             * so we don't want to do anything.
             */
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return $post_id;
            }

            // Check the user's permissions.
            if ( 'ffw' == $_POST['post_type'] ) {
                //check user access or not.
                if(!ffw_is_user_capable()) {
                    return $post_id;
                }
            }

            do_action('ffw_save_faq_meta', $post_id);

            $is_global_product_support_enable = $this->get_product_support();

            if( ! $is_global_product_support_enable ) {

                $product_ids = [];
                $cat_ids = [];
                $removed_product_ids = [];
                $removed_cat_ids = [];
                $removed_tag_ids = [];

                // save product ids.
                if( isset($_POST['ffw_faq_products']) && !empty($_POST['ffw_faq_products']) ) {
                    // Sanitize the faqs products field value.
                    $product_ids = $_POST['ffw_faq_products'];
                }

                // Reference for saved previous products ids.
                // It'll help to check if new selected product ids are removed or not.
                if(isset($_POST['ffw_faq_save_products']) && !empty($_POST['ffw_faq_save_products'])) {
                    $saved_product_ids = sanitize_text_field($_POST['ffw_faq_save_products']);

                    if(!empty($saved_product_ids)) {
                        $saved_product_ids      = explode(',', $saved_product_ids);
                        $removed_product_ids    = array_diff($saved_product_ids, $product_ids);
                    }
                }

                /**
                 * Let's remove faq for the removed product ids.
                 * 
                 * @since 1.6.0
                 */
                if( isset($removed_product_ids) && is_array($removed_product_ids) && !empty($removed_product_ids) ) {
                    foreach($removed_product_ids as $removed_product_id) {

                        // get product faqs
                        $faq_ids = get_post_meta($removed_product_id, 'ffw_product_faq_post_ids', true);

                        // search curret faq id to saved ids and remove if found.
                        if( !empty($faq_ids) ) {
                            $index = array_search($post_id, $faq_ids);

                            if(isset($faq_ids[$index])) {
                                unset($faq_ids[$index]);

                                // Update the meta field.
                                update_post_meta( $removed_product_id, 'ffw_product_faq_post_ids', $faq_ids );
                            }
                        }
                        
                    }
                }

                /**
                 * Add faq post id to the product.
                 * 
                 * @since 1.6.0
                 */
                if( isset($product_ids) && is_array($product_ids) && !empty($product_ids) ) {
                    foreach($product_ids as $product_id) {
                        $post_id    = (int) $post_id;
                        $product_id = (int) $product_id;

                        //insert faqs.
                        ffw_insert_faqs_by_product($post_id, $product_id);
                    }
                }

                /**
                 * Categories Conditions and Savings.
                 * 
                 * @since 1.6.0
                 */

                // save categories ids.
                if( isset($_POST['ffw_faq_categories']) && !empty($_POST['ffw_faq_categories']) ) {
                    // sanitize the faqs categories field value.
                    $cat_ids = $_POST['ffw_faq_categories'];
                }

                // Reference for saved previous category ids.
                // It'll help to check if new selected category ids are removed or not.
                if(isset($_POST['ffw_faq_save_categories']) && !empty($_POST['ffw_faq_save_categories'])) {
                    $saved_cat_ids = sanitize_text_field($_POST['ffw_faq_save_categories']);

                    if(!empty($saved_cat_ids)) {
                        $saved_cat_ids = explode(',', $saved_cat_ids);
                        $removed_cat_ids = array_diff($saved_cat_ids, $cat_ids);
                    }
                }

                /**
                 * Let's remove faq for the removed category ids.
                 * 
                 * @since 1.6.0
                 */
                if( isset($removed_cat_ids) && is_array($removed_cat_ids) && !empty($removed_cat_ids) ) {
                    foreach($removed_cat_ids as $removed_cat_id) {

                        // get category faqs
                        $faq_ids = get_term_meta($removed_cat_id, 'ffw_cat_faq_post_ids', true);

                        // search curret faq id to saved ids and remove if found.
                        if( !empty($faq_ids) ) {
                            $index = array_search($post_id, $faq_ids);

                            if(isset($faq_ids[$index])) {
                                unset($faq_ids[$index]);

                                // Update the meta field.
                                update_term_meta( $removed_cat_id, 'ffw_cat_faq_post_ids', $faq_ids );
                            }
                        }
                        
                    }
                }

                /**
                 * Add faq post id to the categories.
                 * 
                 * @since 1.6.0
                 */
                if( isset($cat_ids) && is_array($cat_ids) && !empty($cat_ids) ) {
                    foreach($cat_ids as $cat_id) {

                        // get categories faqs.
                        $faq_ids = get_term_meta($cat_id, 'ffw_cat_faq_post_ids', true);

                        // when no faqs is set, put empty array.
                        if( empty($faq_ids) ) {
                            $faq_ids = [];
                        }

                        //push the faq id.
                        array_push($faq_ids, $post_id);

                        //remove duplicate faq id.
                        $faq_ids = array_unique($faq_ids);

                        // Update the meta field.
                        update_term_meta( $cat_id, 'ffw_cat_faq_post_ids', $faq_ids );
                    }
                }

                /**
                 * Tag Conditions and Savings.
                 * 
                 * @since 1.7.5
                 */

                // save tags ids.
                if( isset($_POST['ffw_faq_tags']) && !empty($_POST['ffw_faq_tags']) ) {
                    // sanitize the faqs tags field value.
                    $tag_ids = $_POST['ffw_faq_tags'];
                }

                // Reference for saved previous tag ids.
                // It'll help to check if new selected tag ids are removed or not.
                if(isset($_POST['ffw_faq_save_tags']) && !empty($_POST['ffw_faq_save_tags'])) {
                    $saved_tag_ids = sanitize_text_field($_POST['ffw_faq_save_tags']);

                    if(!empty($saved_tag_ids)) {
                        $saved_tag_ids = explode(',', $saved_tag_ids);
                        $removed_tag_ids = array_diff($saved_tag_ids, $tag_ids);
                    }
                }

                /**
                 * Let's remove faq for the removed tag ids.
                 * 
                 * @since 1.6.0
                 */
                if( isset($removed_tag_ids) && is_array($removed_tag_ids) && !empty($removed_tag_ids) ) {
                    foreach($removed_tag_ids as $removed_tag_id) {

                        // get tag faqs.
                        $faq_ids = get_term_meta($removed_tag_id, 'ffw_tag_faq_post_ids', true);

                        // search curret faq id to saved ids and remove if found.
                        if( !empty($faq_ids) ) {
                            $index = array_search($post_id, $faq_ids);

                            if(isset($faq_ids[$index])) {
                                unset($faq_ids[$index]);

                                // Update the meta field.
                                update_term_meta( $removed_tag_id, 'ffw_tag_faq_post_ids', $faq_ids );
                            }
                        }
                        
                    }
                }

                /**
                 * Add faq post id to the tags.
                 * 
                 * @since 1.7.5
                 */
                if( isset($tag_ids) && is_array($tag_ids) && !empty($tag_ids) ) {
                    foreach($tag_ids as $tag_id) {

                        // get tags faqs.
                        $faq_ids = get_term_meta($tag_id, 'ffw_tag_faq_post_ids', true);

                        // when no faqs is set, put empty array.
                        if( empty($faq_ids) ) {
                            $faq_ids = [];
                        }

                        //push the faq id.
                        array_push($faq_ids, $post_id);

                        //remove duplicate faq id.
                        $faq_ids = array_unique($faq_ids);

                        // Update the meta field.
                        update_term_meta( $tag_id, 'ffw_tag_faq_post_ids', $faq_ids );
                    }
                }
            }

            /**
             * Display Pages Conditions and Savings.
             * 
             * @since 1.7.7
             */
            $page_types = [];
            $removed_page_types = [];

            // save pages types.
            if( isset($_POST['ffw_display_in_pages']) && !empty($_POST['ffw_display_in_pages']) ) {
                // sanitize the pages field value.
                $page_types = $_POST['ffw_display_in_pages'];
            }

            // Reference for saved previous tag ids.
            // It'll help to check if new selected tag ids are removed or not.
            if(isset($_POST['ffw_page_save_types']) && !empty($_POST['ffw_page_save_types'])) {
                $saved_page_types = sanitize_text_field($_POST['ffw_page_save_types']);

                if(!empty($saved_page_types)) {
                    $saved_page_types = explode(',', $saved_page_types);
                    $removed_page_types = array_diff($saved_page_types, $page_types);
                }
            }

            /**
             * Let's remove faq for the removed page types.
             * 
             * @since 1.6.0
             */
            if( isset($removed_page_types) && is_array($removed_page_types) && !empty($removed_page_types) ) {
                foreach($removed_page_types as $removed_page_type) {

                    // get page's faqs.
                    $faq_ids = get_option("ffw_{$removed_page_type}_faqs");

                    // search curret faq id to saved ids and remove if found.
                    if( !empty($faq_ids) ) {
                        $index = array_search($post_id, $faq_ids);

                        if(isset($faq_ids[$index])) {
                            unset($faq_ids[$index]);

                            // Update pages faqs.
                            update_option( "ffw_{$removed_page_type}_faqs", $faq_ids );
                        }
                    }
                    
                }
            }

            /**
             * Add faq post id to the page type.
             * 
             * @since 1.7.7
             */
            if( isset($page_types) && is_array($page_types) && !empty($page_types) ) {
                foreach($page_types as $page_type) {

                    // get page's faqs.
                    $faq_ids = get_option("ffw_{$page_type}_faqs");

                    // when no faqs is set, put empty array.
                    if( empty($faq_ids) ) {
                        $faq_ids = [];
                    }

                    //push the faq id.
                    array_push($faq_ids, $post_id);

                    //remove duplicate faq id.
                    $faq_ids = array_unique($faq_ids);

                    // Update pages faqs.
                    update_option( "ffw_{$page_type}_faqs", $faq_ids );
                }
            }

        }

    }

endif;

return new FFW_Metaboxes();