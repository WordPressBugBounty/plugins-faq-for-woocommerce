<?php
/**
 * Get product faqs list by product id.
 *
 * @param int $product_id product id.
 *
 * @return array
 * @since  1.1.3
 *
 */
if( ! function_exists( 'ffw_get_product_faq_ids' ) ) {
    function ffw_get_product_faq_ids( $product_id ) {
        if ( isset($product_id) && ! empty($product_id) ) {

            // option values.
            $options    = get_option( 'ffw_general_settings' );
            
            $faq_post_ids = [];
            $faq_cat_post_ids = [];
            $faq_tag_post_ids = [];

            // get categories.
            $cat_terms = get_the_terms( $product_id, 'product_cat' );
    
            // fetch category faqs ids.
            if(!empty($cat_terms)) {
                $cat_faq_post_ids_data = [];
                foreach ( $cat_terms as $term ) {
                    $cat_id = $term->term_id;
                    $cat_faq_post_ids = get_term_meta($cat_id, 'ffw_cat_faq_post_ids', true);

                    if(!empty($cat_faq_post_ids)) {
                        $cat_faq_post_ids_data = array_merge($cat_faq_post_ids_data, $cat_faq_post_ids);
                    }
                }

                $cat_faq_post_ids_data = array_unique($cat_faq_post_ids_data);

                if(!empty($cat_faq_post_ids_data)) {
                    $faq_cat_post_ids = apply_filters('ffw_filter_faq_post_ids_by_cat', $cat_faq_post_ids_data);
                }
            }
            
            // get tags.
            $tag_terms = get_the_terms( $product_id, 'product_tag' );
    
            // fetch tag faqs ids.
            if(!empty($tag_terms)) {
                $tag_faq_post_ids_data = [];
                foreach ( $tag_terms as $term ) {
                    $tag_id = $term->term_id;
                    $tag_faq_post_ids = get_term_meta($tag_id, 'ffw_tag_faq_post_ids', true);

                    if(!empty($tag_faq_post_ids)) {
                        $tag_faq_post_ids_data = array_merge($tag_faq_post_ids_data, $tag_faq_post_ids);
                    }
                }

                $tag_faq_post_ids_data = array_unique($tag_faq_post_ids_data);

                if(!empty($tag_faq_post_ids_data)) {
                    $faq_tag_post_ids = apply_filters('ffw_filter_faq_post_ids_by_tag', $tag_faq_post_ids_data);
                }
            }

            // fetch product faqs ids When no product category is assigned.
            // get product faq ids.
            $product_faq_post_ids = get_post_meta($product_id, 'ffw_product_faq_post_ids', true);
            $faq_post_ids = apply_filters('ffw_filter_faq_post_ids_by_product', $product_faq_post_ids);

            if(empty($faq_post_ids)) {
                $faq_post_ids = [];
            }

            $faq_ids = array_merge($faq_cat_post_ids, $faq_post_ids);
            $faq_ids = array_merge($faq_tag_post_ids, $faq_ids);

            // merge global faqs.
            $faq_global_post_ids = apply_filters("ffw_filter_global_faq_post_ids", $faq_post_ids);
            
            // Get global faqs appearance value.
            $ffw_global_faqs_appearance = isset( $options['ffw_global_faqs_appearance'] ) ? $options['ffw_global_faqs_appearance'] : "1";

            if( $ffw_global_faqs_appearance !== "1" ) {
                $faq_ids = array_merge($faq_ids, $faq_global_post_ids);
            }else {
                $faq_ids = array_merge($faq_global_post_ids, $faq_ids);
            }

            if(!empty($faq_ids)) {
                $faq_ids = array_unique($faq_ids);
            }

            $faq_list = ffw_get_faq_list_by_faq_ids($faq_ids);

            return $faq_list;
        }
    }
}

/**
 * Get FAQs list by faq ids.
 * 
 * @return array|void
 */
if( ! function_exists( 'ffw_get_faq_list_by_faq_ids' ) ) {
    function ffw_get_faq_list_by_faq_ids($faq_ids) {

        if(empty($faq_ids)) {
            return;
        }

        $faq_lists = [];

        if( isset($faq_ids) && is_array($faq_ids) ) {
            $new_faq = [];
            foreach ( $faq_ids as $post_id ) {

                // skip trash or draft FAQ posts
                $post_status = get_post_status($post_id);
                if( "publish" !== $post_status ) {
                    continue;
                }

                // make faq data
                $new_faq['id']       = $post_id;
                $new_faq['question'] = get_the_title($post_id);
                $new_faq['answer']   = get_the_content(null, false, $post_id);
                array_push($faq_lists, $new_faq);
            }
        }

        $faq_lists = ! empty($faq_lists) && is_array($faq_lists) ? $faq_lists : [];

        return apply_filters('ffw_get_product_faqs_data', $faq_lists);
    }
}

/**
 * Get product faqs list by product category ids.
 *
 * @param array $cat_ids product category ids.
 *
 * @return array
 * @since  1.3.23
 *
 */
if( ! function_exists( 'ffw_get_product_faqs_by_cat_ids_in_shortcode' ) ) {
    function ffw_get_product_faqs_by_cat_ids_in_shortcode( $args ) {
        if ( isset($args['cat_ids']) && ! empty($args['cat_ids']) ) {
            $cat_ids = explode(',', $args['cat_ids']);

            $faq_post_ids = get_posts(
                array(
                    'posts_per_page' => -1,
                    'post_type' => 'ffw',
                    'fields' => 'ids',
                    'order_by' => $args['order_by'],
                    'order'    => $args['order'],
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'ffw-category',
                            'field' => 'term_id',
                            'terms' => $cat_ids,
                        )
                    )
                )
            );

            $faq_lists = [];

            if( isset($faq_post_ids) && is_array($faq_post_ids) ) {
                $new_faq = [];
                foreach ( $faq_post_ids as $post_id ) {
                    $new_faq['id']       = $post_id;
                    $new_faq['question'] = get_the_title($post_id);
                    $new_faq['answer']   = get_the_content(null, false, $post_id);
                    array_push($faq_lists, $new_faq);
                }
            }

            $faq_lists = ! empty($faq_lists) && is_array($faq_lists) ? $faq_lists : [];

            return apply_filters('ffw_get_product_faq_ids', $faq_lists);
        }
    }
}

/**
 * Get faq body by product id.
 *
 * @param int $post_id product id.
 * @since  1.3.0
 */
if( ! function_exists('ffw_get_option_panel_body') ) {
    function ffw_get_option_panel_body($post_id) {
        $product_faqs = ffw_get_product_faq_ids($post_id);
        ?>
        <div class="ffw-sortable ffw-sortable-options-body ffw-metaboxes-wrapper" id="ffw-sortable">
            <?php
            $counter = 1;
            if ( $product_faqs ) {
                foreach ( $product_faqs as $faq_item ) {
                    ?>
                    <div class="ffw-metabox-item ffw-metabox closed" id="<?php echo esc_attr($faq_item['id']); ?>" data-id="<?php echo esc_attr(absint( $faq_item['id'] )) + 1; ?>">
                        <h3>
                            <a href="#" class="ffw-single-delete-btn ffw-meta-btn" rel="<?php echo esc_attr(absint( $faq_item['id'] )); ?>">Remove</a>
                            <span class="ffw-sort-icon"></span>
                            <?php
                            echo sprintf('<strong>%s</strong>', esc_html__($faq_item['question'], 'faq-for-woocommerce') );
                            ?>
                        </h3>
                        <div class="ffw-metabox-content" style="display: none;">
                            <div class="data">
                                <?php
                                echo sprintf('<div>%s</div>', esc_html__($faq_item['answer'], 'faq-for-woocommerce') );
                                ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    $counter++;
                }
            }
            ?>
        </div>
        <?php
    }
}

/**
 * Shortcode to show template.
 *
 * @param string $atts mixed.
 *
 * @return void
 * @since  1.1.3
 *
 */
if ( ! function_exists('ffw_show_template_shortcode') ) {
	function ffw_show_template_shortcode( $atts ) {
        $options = get_option( 'ffw_general_settings' );
        $product_id = ffw_get_random_product_id_has_faq();

        if(is_product()) {
            global $product;
            $product_id = $product->get_id();
        }

		$atts = shortcode_atts( array(
			'template' => 1,
			'dynamic_post' => false,
			'cat_ids' => array(),
            'order_by' => 'id',
            'order' => 'ASC',
			'id' => $product_id,
		), $atts, 'ffw_template' );

		$template = (int) $atts['template'];

	    if( isset($atts['dynamic_post']) && true == $atts['dynamic_post'] ) {
	        if( isset($options["ffw_hide_dynamic_shortcode_preview"]) && 2 == $options["ffw_hide_dynamic_shortcode_preview"] ) {
	            return false;
            }
        }else {

            if( isset($options["ffw_hide_general_shortcode_preview"]) && 2 == $options["ffw_hide_general_shortcode_preview"] ) {
                return false;
            }

            $product_id = (int) $atts['id'];
        }

	    if( isset($atts['cat_ids']) && !empty($atts['cat_ids']) ) {
	        $cat_ids = $atts['cat_ids'];
        }else {
	        $cat_ids = false;
        }

        $arguments = [];
        $arguments['cat_ids']   = $cat_ids;
        $arguments['order_by']  = $atts['order_by'];
        $arguments['order']     = $atts['order'];
        $arguments['template']  = $atts['template'];

		return ffw_get_template($product_id, $arguments, true);
	}
}
add_shortcode('ffw_template', 'ffw_show_template_shortcode');

/**
 * Get template to show in front.
 *
 * @param int $id product or archive id.
 *
 * @return void
 * @since  1.1.3
 *
 */
if ( ! function_exists('ffw_get_template') ) {
	function ffw_get_template( $id, $args, $is_shortcode = false ) {
		$content = '';
        $archive_type  = '';
        

        //get layout.
        $options = get_option( 'ffw_general_settings' );
        $layout = isset( $options['ffw_layout'] ) ? (int) $options['ffw_layout'] : 1;

        // get layout from shortcode.
        if( $is_shortcode ) {
            if( ( isset($args['template']) && ! empty($args['template']) ) ) {
                $layout = (int) $args['template'];
            }
        }

        $shortcode_wrap_class = $is_shortcode ? ['ffw-main-wrapper-shortcode'] : [];
        if( ( isset($args['cat_ids']) && ! empty($args['cat_ids']) ) ) {
            $faqs = ffw_get_product_faqs_by_cat_ids_in_shortcode($args);
            $display_schema_type = 'shortcode';
        } else {

            $faqs = ffw_get_product_faq_ids($id);
            $display_schema_type = 'product_page';

            if( ffw_is_pro_activated() ) {
                if( is_product_category() || is_product_tag() ) {
                    $archive_type  = is_product_tag() ? 'product_tag' : 'product_category';

                    $faqs = ffw_get_archive_faqs($id, $archive_type);
                }
            }
        }

        // filtered faqs.
        $faqs = apply_filters('ffw_filter_shortcode_faqs', $faqs, $args);

        $args = [];
        $args['id'] = $id;
        $args['layout'] = $layout;
        $args['shortcode_wrap_class'] = $shortcode_wrap_class;
        $args['display_schema_type'] = $display_schema_type;

        if ($is_shortcode) {
            return ffw_get_template_by_faqs($faqs, $args);
        } else {
            echo ffw_get_template_by_faqs($faqs, $args);
        }
	}
}


if ( ! function_exists('ffw_get_layout') ) {
    /**
     * @param $faqs array faqs list
     * @param $layout int template number
     * @param $id int product id
     * @return string
     *
     * @since  1.4.0
     */
    function ffw_get_layout($faqs, $layout, $id ) {
		$content = '';

        $display_schema_type = 'product_page';

        // Get registered option
        $options    = get_option( 'ffw_general_settings' );
        $width      = (isset($options['ffw_width']) && !empty($options['ffw_width'])) ? $options['ffw_width'] : '100';
        $ffw_display_all_answers = isset( $options['ffw_display_all_faq_answers'] ) ? $options['ffw_display_all_faq_answers'] : "2";

        //init layout name
        $layout_name = '';

        // enqueue template CSS.
        if ( 1 === $layout ) {
            $layout_name = "ffw-classic-layout";
            wp_enqueue_style( 'ffw_classic_styles' );
        }elseif ( 2 === $layout ) {
            $layout_name = "ffw-whitish-layout";
            wp_enqueue_style( 'ffw_whitish_styles' );
        }elseif ( 3 === $layout ) {
            $layout_name = "ffw-trip-layout";
            wp_enqueue_style( 'ffw_trip_styles' );
        }elseif ( 4 === $layout ) {
            $layout_name = "ffw-pop-layout";
            wp_enqueue_style( 'ffw_pop_styles' );
        }elseif ( 5 === $layout ) {
            $layout_name = "ffw-basic-layout";
            wp_enqueue_style( 'ffw_basic_styles' );
        }

        do_action('ffw_enqueue_templates_styles', $layout);

        // general styles
        wp_enqueue_style( 'ffw_public_styles' );

        $ffw_wrapper_classes = ['ffw-wrapper', $layout_name];

        if( 3 === $layout ) {
            $ffw_wrapper_classes = array_merge($ffw_wrapper_classes, ['ffw-trip-wrapper']);
        }elseif ( 4 === $layout ) {
            $ffw_wrapper_classes = array_merge($ffw_wrapper_classes, ['ffw-pop-wrapper']);
        }

        $ffw_wrapper_classes = apply_filters("ffw_filter_layout_wrapper_classes", $ffw_wrapper_classes, $layout);
        $ffw_wrapper_classes = implode(' ', $ffw_wrapper_classes);

		ob_start();

        //get faq templates
        if ( 1 === $layout || 2 === $layout ) {
            include FFW_FILE_DIR . '/views/ffw-classic-template.php';
        } elseif ( 3 === $layout ) {
            include FFW_FILE_DIR . '/views/ffw-trip-template.php';
        } elseif ( 4 === $layout ) {
            include FFW_FILE_DIR . '/views/ffw-pop-template.php';
        } elseif ( 5 === $layout ) {
			include FFW_FILE_DIR . '/views/ffw-basic-template.php';
		}

        do_action('ffw_includes_templates_file', $layout, $faqs, $id);

		$content .= ob_get_clean();

		return $content;
	}
}

if( ! function_exists('ffw_get_template_by_faqs') ) {
    function ffw_get_template_by_faqs($faqs, $args = []) {
        //skip, for empty faqs.
        if(empty($faqs)) {
            return;
        }

        $content = '';
        $archive_type  = apply_filters('ffw_archive_type', '');

        $id = isset($args['id']) ? $args['id'] : '';
        $layout = isset($args['layout']) ? $args['layout'] : '';
        $shortcode_wrap_class = isset($args['shortcode_wrap_class']) ? $args['shortcode_wrap_class'] : [];
        $display_schema_type = isset($args['display_schema_type']) ? $args['display_schema_type'] : 'product_page';

        if( empty($layout) ) {
            //get layout.
            $options = FAQ_Woocommerce_Public::instance()->options;
            $layout = isset( $options['ffw_layout'] ) ? (int) $options['ffw_layout'] : 1;
        }

        $wrapper_classes = apply_filters('ffw_filter_template_wrapper_classes', ['ffw-main-wrapper'], $layout, $id);
        $wrapper_classes = array_merge($wrapper_classes, $shortcode_wrap_class);
        $wrapper_classes = implode(' ', $wrapper_classes);

        //faq schema.
        new FAQ_Woocommerce_Schema($faqs, $display_schema_type);

        // Get registered option
        $options    = FAQ_Woocommerce_Public::instance()->options;
        $width      = (isset($options['ffw_width']) && !empty($options['ffw_width'])) ? $options['ffw_width'] : '100';

        //init layout name
        $layout_name = '';

        // enqueue template CSS.
        if ( 1 === $layout ) {
            $layout_name = "ffw-classic-layout";
            wp_enqueue_style( 'ffw_classic_styles' );
        }elseif ( 2 === $layout ) {
            $layout_name = "ffw-whitish-layout";
            wp_enqueue_style( 'ffw_whitish_styles' );
        }elseif ( 3 === $layout ) {
            $layout_name = "ffw-trip-layout";
            wp_enqueue_style( 'ffw_trip_styles' );
        }elseif ( 4 === $layout ) {
            $layout_name = "ffw-pop-layout";
            wp_enqueue_style( 'ffw_pop_styles' );
        }elseif ( 5 === $layout ) {
            $layout_name = "ffw-basic-layout";
            wp_enqueue_style( 'ffw_basic_styles' );
        }

        do_action('ffw_enqueue_templates_styles', $layout);

        // general styles
        wp_enqueue_style( 'ffw_public_styles' );

        ob_start();

        $content .= '<div style="width: '.$width.'%;max-width: 100%;" class="'. esc_attr($wrapper_classes) .'" id="ffw-main-wrapper" data-item_id="'. esc_attr($id) .'" data-layout="'. esc_attr($layout) .'" data-archive_type="'. esc_attr($archive_type) .'" >';
        $content .= '<input type="hidden" id="ffw-hidden-faqs" value="' . base64_encode(wp_json_encode($faqs)) . '" />';

        do_action('ffw_faq_header');
        do_action('ffw_before_faq_start');
        do_action('ffw_expand_collapse_all');

        $ffw_wrapper_classes = ['ffw-wrapper', $layout_name];

        if( 3 === $layout ) {
            $ffw_wrapper_classes = array_merge($ffw_wrapper_classes, ['ffw-trip-wrapper']);
        }elseif ( 4 === $layout ) {
            $ffw_wrapper_classes = array_merge($ffw_wrapper_classes, ['ffw-pop-wrapper']);
        }

        $ffw_wrapper_classes = apply_filters("ffw_filter_layout_wrapper_classes", $ffw_wrapper_classes, $layout);
        $ffw_wrapper_classes = implode(' ', $ffw_wrapper_classes);

        //get faq templates
        if ( 1 === $layout || 2 === $layout ) {
            include FFW_FILE_DIR . '/views/ffw-classic-template.php';
        } elseif ( 3 === $layout ) {
            include FFW_FILE_DIR . '/views/ffw-trip-template.php';
        } elseif ( 4 === $layout ) {
            include FFW_FILE_DIR . '/views/ffw-pop-template.php';
        } elseif ( 5 === $layout ) {
            include FFW_FILE_DIR . '/views/ffw-basic-template.php';
        }

        do_action('ffw_includes_templates_file', $layout, $faqs, $id);

        echo '<br>';

        do_action('ffw_after_faq_end');

        $content .= ob_get_clean();

        return $content . '</div>';
    }
}

/**
 * FAQ Header
 *
 * @return void
 */
function ffw_faq_header() {
    ?>
    <div class="ffw-faq-header">
        <?php
        do_action('ffw_search_input');

        if( ffw_is_pro_activated() ) {
            echo do_shortcode('[ffw_customer_question_form]');
        }
        ?>
    </div>
    <?php
}
add_action('ffw_faq_header', 'ffw_faq_header');

/**
 * Get random product id which has faq list.
 *
 * @return int
 * @since  1.1.3
 */
if ( ! function_exists('ffw_get_random_product_id_has_faq') ) {
	function ffw_get_random_product_id_has_faq() {
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => 1,
			'fields' => 'ids',
			'meta_query' => array(
				array(
					'key' => 'ffw_product_faq_post_ids',
					'value' => '',
					'compare' => '!=',
				),
			),
			'orderby' => 'rand',
			'order' => 'ASC',
		);

		$product_ids = get_posts($args);

		return reset($product_ids);
	}
}

/**
 * Array to string separator
 *
 * @param string $separator - the separator to separate array items
 * @param array $array - array to slice and make string with separator
 *
 * @return string|boolean
 */
function ffw_array_separator( string $separator, array $array ) {

    return is_array( $array ) ? implode( $separator, $array ) : false;

}

/**
 * Generate ffw posts from older and newer datas
 *
 * @since 1.3.0
 * @author WPFeel
 */
add_action('init', 'ffw_generate_post');
function ffw_generate_post() {
    $generate   = get_option('ffw_post_generation');

    if( "complete" !== $generate ) {
        $posts  = ffw_get_faqs_product_list(true);

        if($posts) {
            foreach($posts as $product_id) {
                $faq_list = get_post_meta($product_id, 'ffw_faqs_list');
                $faq_list = reset($faq_list);
                $new_faq_post = [];
                $faq_ids_list = [];
                foreach($faq_list as $faq) {
                    $new_faq_post['post_title']     = $faq['question'];
                    $new_faq_post['post_content']   = $faq['answer'];
                    $new_faq_post['post_type']      = 'ffw';
                    $new_faq_post['post_status']    = 'publish';
                    $inserted_post_id = wp_insert_post($new_faq_post);
                    array_push($faq_ids_list, $inserted_post_id);
                }

                //insert faq post ids to it's own product
                update_post_meta($product_id, 'ffw_product_faq_post_ids', $faq_ids_list);

            }
        }
    }

    update_option("ffw_post_generation", "complete");
}

/**
 * Get faqs list
 *
 * @since 1.3.0
 */
function ffw_get_faqs_product_list($get_only_ids = false) {
    $posts = [];
    $fields = $get_only_ids ? 'ids' : 'all';

    $args = array(
        'post_type' => 'product',
        'fields'    => $fields,
        'meta_query' => array(
            array(
                'key' => 'ffw_faqs_list',
                'value' => '',
                'compare' => '!=',
            )
        )
    );

    $posts = get_posts($args);

    return apply_filters('ffw_get_faqs_product_list', $posts);
}

/**
 * Get faqs post list
 *
 * @since 1.3.0
 */
function ffw_get_faqs_post_list($get_only_ids = false) {
    $posts = [];
    $fields = $get_only_ids ? 'ids' : 'all';

    $args = array(
        'post_type' => 'ffw',
        'fields'    => $fields,
        'posts_per_page' => -1,
    );

    $posts = get_posts($args);

    return apply_filters('ffw_get_faqs_post_list', $posts);
}

/**
 * Get specific product faqs
 *
 * @since 1.3.1
 */
if( ! function_exists('ffw_get_faqs_number_for_product') ) {
    function ffw_get_faqs_number_for_product($product_id) {
        if( empty($product_id) ) return 0;

        $faq_post_ids = get_post_meta($product_id, 'ffw_product_faq_post_ids', true);
        if( !empty($faq_post_ids) && count($faq_post_ids) > 0 ) {
            $count = count($faq_post_ids);
        }else {
            $count = 0;
        }

        return apply_filters('ffw_get_faqs_number_for_product', $count);
    }
}

/**
 * Add the custom faq count column to the product post type
 *
 * @since 1.3.1
 */
if( ! function_exists('ffw_set_custom_faq_count_column') ) {
    add_filter( 'manage_product_posts_columns', 'ffw_set_custom_faq_count_column' );
    function ffw_set_custom_faq_count_column($columns) {
        $columns['faq_count'] = esc_html__( 'FAQs', 'faq-for-woocommerce' );

        return $columns;
    }
}

/**
 * Add the faq count data to the custom column for the product post type
 *
 * @since 1.3.1
 */
if( ! function_exists('ffw_custom_count_column_for_product') ) {
    add_action( 'manage_product_posts_custom_column' , 'ffw_custom_count_column_for_product', 10, 2 );
    function ffw_custom_count_column_for_product( $column, $post_id ) {
        switch ( $column ) {
            case 'faq_count' :
                $terms =  (string) ffw_get_faqs_number_for_product($post_id);
                if ( is_string( $terms ) )
                    esc_html_e( $terms, 'faq-for-woocommerce' );
                else
                    esc_html_e( '0', 'faq-for-woocommerce' );
                break;

        }
    }
}

/**
 * Checks if the specified comment is written by the author of the post commented on.
 *
 * @param object $comment Comment data.
 * @return bool
 */
function ffw_is_comment_by_post_author( $comment = null ) {

    if ( is_object( $comment ) && $comment->user_id > 0 ) {

        $user = get_userdata( $comment->user_id );
        $post = get_post( $comment->comment_post_ID );

        if ( ! empty( $user ) && ! empty( $post ) ) {

            return $comment->user_id === $post->post_author;

        }
    }
    return false;

}

/**
 * Get comment reply link.
 *
 * @param object $comment Comment data.
 * @return bool
 */
function ffw_reply_comment_link( $comment = null ) {
    $options = get_option( 'ffw_general_settings' );
    $reply_button_text = ( isset( $options['ffw_comments_reply_button_text'] ) && !empty($options['ffw_comments_reply_button_text']) ) ? $options['ffw_comments_reply_button_text'] : esc_html__("Reply", "faq-for-woocommerce");
    if ( is_object( $comment ) && $comment->user_id > 0 ) {

        $user = get_userdata( $comment->user_id );
        $post = get_post( $comment->comment_post_ID );

        if ( ! empty( $user ) && ! empty( $post ) ) {
            ?>
            <span class="ffw-comment-reply-button" data-ffw-comment-post-id="<?php echo esc_attr($comment->comment_post_ID); ?>" data-ffw-reply-comment-id="<?php echo esc_attr($comment->comment_ID); ?>"><?php echo esc_html($reply_button_text); ?></span>
            <?php

        }
    }
    return false;

}

/**
 * Register FFW post type called "ffw".
 *
 * @since 1.3.0
 * @author Nazrul Islam Nayan
 */
if( ! function_exists('ffw_post_init') ) {
    function ffw_post_init() {

        // get options
        $options = get_option( 'ffw_general_settings' );
		$options = ! empty( $options ) ? $options : [];

        //get the saved editor value `1` for gutenberg, `2` for classics
		$ffw_editor = isset( $options['ffw_editor'] ) ? $options['ffw_editor'] : "1";


        $labels = array(
            'name'                  => _x( 'Happy FAQs', 'FAQ', 'faq-for-woocommerce' ),
            'singular_name'         => _x( 'FAQ', 'FAQ', 'faq-for-woocommerce' ),
            'menu_name'             => _x( 'Happy FAQs', 'Happy FAQs', 'faq-for-woocommerce' ),
            'name_admin_bar'        => _x( 'Happy FAQs', 'Happy FAQs', 'faq-for-woocommerce' ),
            'add_new'               => esc_html__( 'Add New FAQ', 'faq-for-woocommerce' ),
            'add_new_item'          => esc_html__( 'Add New FAQ', 'faq-for-woocommerce' ),
            'new_item'              => esc_html__( 'New FAQ', 'faq-for-woocommerce' ),
            'edit_item'             => esc_html__( 'Edit FAQ', 'faq-for-woocommerce' ),
            'view_item'             => esc_html__( 'View FAQ', 'faq-for-woocommerce' ),
            'all_items'             => esc_html__( 'All FAQS', 'faq-for-woocommerce' ),
            'search_items'          => esc_html__( 'Search FAQS', 'faq-for-woocommerce' ),
            'parent_item_colon'     => esc_html__( 'Parent FAQS:', 'faq-for-woocommerce' ),
            'not_found'             => esc_html__( 'No faqs found.', 'faq-for-woocommerce' ),
            'not_found_in_trash'    => esc_html__( 'No faqs found in Trash.', 'faq-for-woocommerce' ),
            'featured_image'        => _x( 'FAQ Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'faq-for-woocommerce' ),
            'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'faq-for-woocommerce' ),
            'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'faq-for-woocommerce' ),
            'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'faq-for-woocommerce' ),
            'archives'              => _x( 'FAQ archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'faq-for-woocommerce' ),
            'insert_into_item'      => _x( 'Insert into ffw', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'faq-for-woocommerce' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this faq', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'faq-for-woocommerce' ),
            'filter_items_list'     => _x( 'Filter faqs list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'faq-for-woocommerce' ),
            'items_list_navigation' => _x( 'FAQS list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'faq-for-woocommerce' ),
            'items_list'            => _x( 'FAQS list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'faq-for-woocommerce' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'ffw' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'show_in_rest'       => $ffw_editor == 1 ? true : false,
            'supports'           => array( 'title', 'editor', 'comments' ),
            'taxonomies'         => array( ),
            'menu_icon'          => 'dashicons-editor-help'
        );

        register_post_type( 'ffw', $args );


        // Define the faq for woocommerce category taxonomy
        $args = array(
            'labels' => array(
                'name' 				=> esc_html__( 'FAQ Categories',			'faq-for-woocommerce' ),
                'singular_name' 	=> esc_html__( 'FAQ Category',				'faq-for-woocommerce' ),
                'search_items' 		=> esc_html__( 'Search FAQ Categories', 	'faq-for-woocommerce' ),
                'all_items' 		=> esc_html__( 'All FAQ Categories', 		'faq-for-woocommerce' ),
                'parent_item' 		=> esc_html__( 'Parent FAQ Category', 		'faq-for-woocommerce' ),
                'parent_item_colon' => esc_html__( 'Parent FAQ Category:', 		'faq-for-woocommerce' ),
                'edit_item' 		=> esc_html__( 'Edit FAQ Category', 		'faq-for-woocommerce' ),
                'update_item' 		=> esc_html__( 'Update FAQ Category', 		'faq-for-woocommerce' ),
                'add_new_item' 		=> esc_html__( 'Add New Category', 		'faq-for-woocommerce' ),
                'new_item_name' 	=> esc_html__( 'New FAQ Category Name', 	'faq-for-woocommerce' ),
                'menu_name' 		=> esc_html__( 'Categories', 			'faq-for-woocommerce' ),
            ),
            'public' 		=> true,
            'query_var'		=> true,
            'hierarchical' 	=> true,
            'show_in_rest' 	=> true,
        );

        $args = apply_filters( 'ffw_category_taxonomy_args', $args );

        register_taxonomy( 'ffw-category', 'ffw', $args );
    }
    add_action( 'init', 'ffw_post_init' );
}

/**
 * Register Customer Question Post Type.
 *
 * @return void
 */
function ffw_register_customer_question_post_type() {
    $labels = array(
        'name'                  => _x( 'Customer Questions', 'FAQ', 'faq-for-woocommerce' ),
        'singular_name'         => _x( 'Customer Questions', 'FAQ', 'faq-for-woocommerce' ),
        'menu_name'             => _x( 'Customer Questions', 'Happy FAQs', 'faq-for-woocommerce' ),
        'name_admin_bar'        => _x( 'Customer Questions', 'Happy FAQs', 'faq-for-woocommerce' ),
        'edit_item'             => esc_html__( 'Edit Question', 'faq-for-woocommerce' ),
        'view_item'             => esc_html__( 'View Question', 'faq-for-woocommerce' ),
        'all_items'             => esc_html__( 'All Questions', 'faq-for-woocommerce' ),
        'search_items'          => esc_html__( 'Search Questions', 'faq-for-woocommerce' ),
        'parent_item_colon'     => esc_html__( 'Parent Questions:', 'faq-for-woocommerce' ),
        'not_found'             => esc_html__( 'No questions found.', 'faq-for-woocommerce' ),
        'not_found_in_trash'    => esc_html__( 'No questions found in Trash.', 'faq-for-woocommerce' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'ffw_customer_qna' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'show_in_rest'       => false,
        'supports'           => array( 'title' ),
        'taxonomies'         => array( ),
        'menu_icon'          => 'dashicons-editor-help'
    );

    register_post_type( 'ffw_customer_qna', $args );
}
add_action('init', 'ffw_register_customer_question_post_type');

/**
 * Filter Customer Question Title as 'Questions'.
 *
 * @return void
 */
add_filter('manage_edit-ffw_customer_qna_columns', 'ffw_filter_customer_question_list_title');
function ffw_filter_customer_question_list_title($columns) {
    if (isset($columns['title'])) {
        $columns['title'] = __('Questions', 'faq-for-woocommerce');
    }

    if (isset($columns['date'])) {
        $columns['date'] = __('Submitted On', 'faq-for-woocommerce');
    }

    return $columns;
}

/**
 * Add column to customer questions list
 *
 * @param array $columns The current columns.
 * @return array Modified columns.
 */
function ffw_add_column_to_customer_questions_list($columns) {
    $columns['customer_name'] = esc_html__('Name', 'faq-for-woocommerce');
    $columns['customer_email'] = esc_html__('Email', 'faq-for-woocommerce');
    $columns['location'] = esc_html__('Location', 'faq-for-woocommerce');
    $columns['action'] = esc_html__('Action', 'faq-for-woocommerce');
    return $columns;
}
add_filter('manage_ffw_customer_qna_posts_columns', 'ffw_add_column_to_customer_questions_list');

/**
 * Manage columns to customer questions list
 *
 * @param string $column The name of the column.
 * @param int $post_id The post ID.
 */
function ffw_manage_column_to_customer_questions_list($column, $post_id) {
    if ($column === 'customer_name') {
        $meta_value = get_post_meta($post_id, 'ffw_customer_name', true);
        echo $meta_value ? esc_html($meta_value) : __('Empty', 'faq-for-woocommerce');
    }

    if ($column === 'customer_email') {
        $meta_value = get_post_meta($post_id, 'ffw_customer_email', true);
        echo $meta_value ? esc_html($meta_value) : __('Empty', 'faq-for-woocommerce');
    }

    if ($column === 'location') {
        $meta_value = get_post_meta($post_id, 'ffw_customer_question_location', true);
        echo $meta_value ? esc_html($meta_value) : __('Empty', 'faq-for-woocommerce');
    }
    
    if ($column === 'action') {
        do_action('ffw_customer_question_list_actions', $post_id);
    }
}
add_action('manage_ffw_customer_qna_posts_custom_column', 'ffw_manage_column_to_customer_questions_list', 10, 2);

/**
 * Rearrange columns to customer questions list
 *
 * @param array $columns Existing columns.
 * 
 * @return array
 */
function ffw_rearrange_columns_to_customer_questions_list($columns) {
    $columns = array(
        'cb'             => $columns['cb'],
        'title'          => $columns['title'],
        'customer_name'  => __('Customer Name', 'faq-for-woocommerce'),
        'customer_email' => __('Customer Email', 'faq-for-woocommerce'),
        'location'       => __('Location', 'faq-for-woocommerce'),
        'date'           => $columns['date'],
        'action'         => __('Action', 'faq-for-woocommerce'),
    );

    return $columns;
}
add_filter('manage_ffw_customer_qna_posts_columns', 'ffw_rearrange_columns_to_customer_questions_list', 10);

/**
 * Hide Customer Question Page Title Action.
 *
 * @return void
 */
function ffw_hide_add_new_button($hook) {
    $post_type = 'ffw_customer_qna';

    if ($hook === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === $post_type) {
        echo '<style>
            .page-title-action { 
                display: none !important; 
            }
        </style>';
    }
}
add_action('admin_enqueue_scripts', 'ffw_hide_add_new_button');

/**
 * Remove Edit and Quick Edit actions from Customer Question List.
 *
 * @param array $actions The row actions for the post.
 * @param WP_Post $post The post object.
 * @return array Modified row actions.
 */
function remove_edit_quickedit_from_custom_post_type($actions, $post) {
    if ($post->post_type === 'ffw_customer_qna') {
        unset($actions['edit']);
        unset($actions['inline hide-if-no-js']);
    }

    return $actions;
}
add_filter('post_row_actions', 'remove_edit_quickedit_from_custom_post_type', 10, 2);


/**
 * Add or Remove Customer Question Menus.
 *
 * @return void
 */
function ffw_manage_customer_question_submenu() {
    $parent_post_type = 'ffw';
    $customer_question_post_type = 'ffw_customer_qna';

    remove_menu_page("edit.php?post_type={$customer_question_post_type}");
    remove_submenu_page("edit.php?post_type={$customer_question_post_type}", 'post-new.php?post_type=' . $customer_question_post_type);

    add_submenu_page(
        "edit.php?post_type={$parent_post_type}",
        __('Customer Questions'),
        __('Customer Questions'),
        'manage_options',
        "edit.php?post_type={$customer_question_post_type}"
    );
}
add_action('admin_menu', 'ffw_manage_customer_question_submenu');

/**
 * Filter title placeholder for "ffw" post type.
 *
 * @since 1.6.3
 * @author Nazrul Islam Nayan
 */
add_filter( 'enter_title_here', 'ffw_change_title_text' );
function ffw_change_title_text( $title ){
    $screen = get_current_screen();

    if  ( 'ffw' == $screen->post_type ) {
        $title = esc_html__('Enter Question', 'faq-for-woocommerce');
    }

    return $title;
}


/**
 * Show FAQ answer content by faq id
 */
if( ! function_exists('ffw_show_content') ) {
    function ffw_show_content($faq_id, $id) {
        global $post;
        $post = get_post($faq_id);
        setup_postdata($post);

        $content = get_the_content($post);
        $content = apply_filters("ffw_apply_dynamic_product_attribute", $content, $id, $faq_id);
        $content = apply_filters( 'the_content', $content );
        $content = str_replace( ']]>', ']]&gt;', $content );

        echo wp_kses_post($content);

        wp_reset_postdata();
    }
}

function ffw_comments($post_id, $faq) {

    $options        = get_option( 'ffw_general_settings' );
    $ffw_comment_on = (isset($options['ffw_comment_on']) && !empty($options['ffw_comment_on'])) ? $options['ffw_comment_on'] : 1;

    if( isset($ffw_comment_on) && 1 === (int) $ffw_comment_on ) {
        return true;
    }

    ?>
    <div class="ffw-comment-wrapper">
        <div class="ffw-comment-header">
            <?php
            $ffw_comments_section_title = ( isset( $options['ffw_comments_section_title'] ) && !empty($options['ffw_comments_section_title']) ) ? $options['ffw_comments_section_title'] : esc_html__("Comments", "faq-for-woocommerce");
            echo sprintf("<h3 class='ffw-comment-heading'>%s</h3>", esc_html($ffw_comments_section_title));
            ?>
        </div>
        <div class="ffw-comment-box">
            <input type="hidden" class="ffw_product_id_for_comment" value="<?php echo esc_html($post_id); ?>">
            <?php do_action('ffw_comments_template', $post_id, $faq); ?>
            <?php do_action('ffw_comments_form', $post_id, $faq); ?>
        </div>
    </div>
    <?php
}

add_action('ffw_comments_template', 'ffw_comments_template', 10, 2);
function ffw_comments_template($post_id, $faq) {
    $ffw_comments = new FFW_Comments($post_id, $faq['id']);
    $ffw_comments->comments_template();
}

add_action('ffw_comments_form', 'ffw_comments_form', 10, 2);
function ffw_comments_form($post_id, $faq) {
    $commenter  = wp_get_current_commenter();
    $req        = get_option( 'require_name_email' );
    $html_req   = ( $req ? " required='required'" : '' );
    $options    = get_option( 'ffw_general_settings' );
    $form_title = ( isset( $options['ffw_comments_form_title'] ) && !empty($options['ffw_comments_form_title']) ) ? $options['ffw_comments_form_title'] : esc_html__("Comment Box", "faq-for-woocommerce");
    $submit_text = ( isset( $options['ffw_comments_submit_button_text'] ) && !empty($options['ffw_comments_submit_button_text']) ) ? $options['ffw_comments_submit_button_text'] : esc_html__("Comment", "faq-for-woocommerce");

    $fields = array(
        'author' => sprintf(
            '<p class="ffw-comment-form-author">%s</p>',
            sprintf(
                '<input id="author" name="author" placeholder="%s" type="text" value="%s" size="30" maxlength="245"%s />',
                esc_html__( 'Type your name (required)' ),
                esc_attr( $commenter['comment_author'] ),
                $html_req
            )
        ),
        'email'  => sprintf(
            '<p class="ffw-comment-form-email">%s</p>',
            sprintf(
                '<input id="email" name="email" placeholder="%s" %s value="%s" size="30" maxlength="100" aria-describedby="email-notes"%s />',
                esc_html__( 'Type your email (required)' ),
                ( 'type="email"' ),
                esc_attr( $commenter['comment_author_email'] ),
                $html_req
            )
        ),
        'url'    => sprintf(
            '<p class="ffw-comment-form-url">%s</p>',
            sprintf(
                '<input id="url" name="url" placeholder="%s" %s value="%s" size="30" maxlength="200" />',
                esc_html__( 'Type your website (required)' ),
                ( 'type="url"' ),
                esc_attr( $commenter['comment_author_url'] )
            )
        ),
    );

    $comments_args = array(
        'fields' => $fields,
        // Change the title of send button
        'label_submit' => $submit_text,
        // Change the title of the reply section
        'title_reply' => $form_title,
        // Remove "Text or HTML to be displayed after the set of comment fields".
        'comment_notes_after' => '',
        'class_container' => 'ffw-comment-respond',
        // Redefine your own textarea (the comment body).
        'comment_field' => '<input type="hidden" name="ffw_product_id_for_comment" value="' . $post_id . '"><p class="ffw-comment-form-comment"><label for="comment">' . _x( 'Comment', 'faq-for-woocommerce' ) . '</label><br /><textarea id="comment" name="comment" aria-required="true"></textarea></p>',
    );

    //comment form
    comment_form($comments_args, $faq['id']);
}

add_filter('comment_post_redirect', 'ffw_redirect_on_comment_submit', 99, 2);
function ffw_redirect_on_comment_submit($location, $comment) {
    if( isset($comment->comment_ID) && isset($_POST[ 'ffw_product_id_for_comment' ]) ) {
        $comment_id = $comment->comment_ID;
        $product_id = (int) $_POST[ 'ffw_product_id_for_comment' ];

        //merge comment meta and add post ids for current faq comment id
        $post_ids_for_faq_comment = get_comment_meta($comment_id, 'ffw_post_ids_for_comment');
        if( empty($post_ids_for_faq_comment) && !is_array($post_ids_for_faq_comment) ) {
            $post_ids_for_faq_comment = array();
        }
        array_push($post_ids_for_faq_comment, $product_id);
        $post_ids_for_faq_comment = array_unique($post_ids_for_faq_comment);
        $product_ids = reset($post_ids_for_faq_comment);
        update_comment_meta( $comment_id, 'ffw_post_ids_for_comment', $product_ids );

        //current page permalink
        $location = get_permalink($product_id);
    }

    return $location;
}

if( ! function_exists( 'ffw_strip_all_tags' ) ) {

    /*
     * Extends wp_strip_all_tags to fix WP_Error object passing issue
     *
     * @param string | WP_Error $string
     *
     * @return string
     * @since 1.3.20
     * */
    function ffw_strip_all_tags( $string ) {

        if( $string instanceof WP_Error ){
            return '';
        }

        return wp_strip_all_tags( $string );

    }

}

/**
 * Disable search engines indexing faq pages.
 *
 * @since 1.3.30
 * @param array $robots Associative array of robots directives.
 * @return array Filtered robots directives.
 */
function ffw_page_indexing( $robots ) {
    if ( is_singular( 'ffw' ) ) {
        // Get registered option
        $options = get_option( 'ffw_general_settings' );
        $ffw_post_index = isset( $options['ffw_post_index'] ) ? $options['ffw_post_index'] : "1";

        //index or noindex according to settings
        if( "1" === $ffw_post_index ) {
            $robots['noindex']  = true;
            $robots['nofollow'] = true;
        }else {
            $robots['index']    = true;
            $robots['noindex']  = false;
            $robots['nofollow'] = false;
        }
    }

    return $robots;
}
add_filter( 'wp_robots', 'ffw_page_indexing' );

/**
 * Settings page menu title
 *
 * @since 1.4.1
 */
function ffw_get_settings_page_menu_title() {
    return apply_filters('ffw_filter_settings_page_menu_title', esc_html__('Happy FAQs', 'faq-for-woocommerce'));
}

/**
 * Check if Premium version is activated
 *
 * @since 1.4.1
 */
function ffw_is_premium_active() {
    return is_plugin_active('faq-for-woocommerce-premium/faq-for-woocommerce-premium.php');
}

/**
 * Admin styles and scripts
 *
 * @since 1.4.2
 */
add_action("admin_head", "ffw_free_admin_head");
function ffw_free_admin_head() {
    ?>
    <style>
        #faq_count {
            width: 40px;
        }
    </style>
    <script>
        (function($) {
            // In your Javascript (external .js resource or <script> tag)
            $(document).ready(function() {
                // change settings switch values
                $('.ffw-switch-wrapper .ffw-switch input[type="checkbox"]').on('change', function(e) {
                    let checked = $(this).is(':checked');
                    if( checked ) {
                        $(this).val('on');
                    }else {
                        $(this).val('no');
                    }
                });
            });
        })(jQuery)

    </script>
    <?php
}

/**
 * Log function to view any data in wp-content/debug.log
 * uses: log_it($variable);
 */
if (! function_exists('ffw_log_it') ) {
    function ffw_log_it( $message )
    {
        if (WP_DEBUG === true ) {
            if (is_array($message) || is_object($message) ) {
                error_log("\r\n" . print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }
}

/**
 * Is pro activated
 */
function ffw_is_pro_activated() {
    $activated = false;

    if ( is_plugin_active('faq-for-woocommerce-premium/faq-for-woocommerce-premium.php') ) {
        $activated = true;
    }

    return $activated;
}

/**
 * Insert FAQs by product.
 * 
 * @param int $faq_id FAQ post id.
 * @param int $product_id product id.
 * 
 * @since 1.6.5
 */
function ffw_insert_faqs_by_product($faq_id, $product_id) {

    if(empty($product_id) || empty($faq_id)) {
        return;
    }

    // get product faqs
    $faq_ids = get_post_meta($product_id, 'ffw_product_faq_post_ids', true);

    // when no faqs is set, put empty array
    if( empty($faq_ids) ) {
        $faq_ids = [];
    }

    //push the faq id
    array_push($faq_ids, $faq_id);

    //remove duplicate faq id
    $faq_ids = array_unique($faq_ids);

    // Update the meta field.
    update_post_meta( $product_id, 'ffw_product_faq_post_ids', $faq_ids );
}

/**
 * Get available user roles
 * 
 * @return array
 */
function ffw_get_available_user_roles() {
    if (!function_exists('get_editable_roles')) {
        require_once ABSPATH .'wp-admin/includes/user.php';
    }

    $roles = get_editable_roles();
    $roles = array_keys($roles);

    return apply_filters('ffw_filter_available_user_roles', $roles);
}

/**
 * Get template full path.
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 *
 * @return void
 */
function ffw_include_template( $template_name, $args = array(), $template_path = '' ) {

	if(empty($template_path)) {
		$template_path = untrailingslashit(plugin_dir_path(FFW_FILE)) . '/templates/';
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$template_name = apply_filters( 'ffw_include_template', $template_name, $args, $template_path );

	if ( ! file_exists( $template_path . $template_name ) ) {
		/* translators: %s template */
		wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'faq-for-woocommerce' ), '<code>' . $template_name . '</code>' ), '1.0.0' );
		return;
	}

	extract($args);

	do_action( 'ffw_before_template_part', $template_name, $template_path, $args );

	include $template_path . $template_name;

	do_action( 'ffw_after_template_part', $template_name, $template_path, $args );
}

// Add custom columns to 'ffw' post type.
add_filter('manage_ffw_posts_columns', 'ffw_filter_list_columns');
function ffw_filter_list_columns($columns) {
    $new_columns = [];
    foreach ($columns as $key => $title) {
        if ($key === 'comments') {
            $new_columns['products'] = __('Products', 'faq-for-woocommerce');
            $new_columns['product_categories'] = __('Product Categories', 'faq-for-woocommerce');
            $new_columns['product_tags'] = __('Product Tags', 'faq-for-woocommerce');
            $new_columns['display_pages'] = __('Display Pages', 'faq-for-woocommerce');
            $new_columns['faqs_type'] = __('FAQs Type', 'faq-for-woocommerce');
        }
        $new_columns[$key] = $title;
    }
    return $new_columns;
}

// Populate custom columns.
add_action('manage_ffw_posts_custom_column', 'ffw_display_list_column_data', 10, 2);
function ffw_display_list_column_data($column, $post_id) {
    switch ($column) {
        case 'products':
            // get product ids with faqs.
            $args = array(
                'post_type'  => array('product', 'product_variation'),
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'ffw_product_faq_post_ids',
                        'value' => serialize(strval($post_id)),
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => 'ffw_product_faq_post_ids',
                        'value'   => serialize(intval($post_id)),
                        'compare' => 'LIKE'
                    ),
                ),
                'fields' => 'ids',
                'posts_per_page' => -1,
            );
            $product_ids = get_posts( $args );

            echo !empty($product_ids) ? implode(', ', $product_ids) : '-';
            break;

        case 'product_categories':
            $category_ids = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'meta_query' => [
                    [
                        'key' => 'ffw_cat_faq_post_ids',
                        'value' => $post_id,
                        'compare' => 'LIKE'
                    ]
                ],
                'fields' => 'ids'
            ]);
            if (!empty($category_ids)) {
                $categories = array_map(function ($cat_id) {
                    return get_term($cat_id)->name;
                }, $category_ids);
                echo implode(', ', $categories);
            } else {
                echo '-';
            }
            break;

        case 'product_tags':
            $tag_ids = get_terms([
                'taxonomy' => 'product_tag',
                'hide_empty' => false,
                'meta_query' => [
                    [
                        'key' => 'ffw_tag_faq_post_ids',
                        'value' => $post_id,
                        'compare' => 'LIKE'
                    ]
                ],
                'fields' => 'ids'
            ]);
            if (!empty($tag_ids)) {
                $tags = array_map(function ($tag_id) {
                    return get_term($tag_id)->name;
                }, $tag_ids);
                echo implode(', ', $tags);
            } else {
                echo '-';
            }
            break;

        case 'display_pages':
            $display_pages = [];
            $page_types = [
                'product_and_archive_page' => __('Product & Archive', 'faq-for-woocommerce'), 
                'shop_page' => __('Shop', 'faq-for-woocommerce'), 
                'cart_page' => __('Cart', 'faq-for-woocommerce'), 
                'checkout_page' => __('Checkout', 'faq-for-woocommerce')
            ];

            foreach ($page_types as $page_type => $page_name) {
                $pages_faq_ids = get_option("ffw_{$page_type}_faqs");
                if (is_array($pages_faq_ids) && in_array($post_id, $pages_faq_ids)) {
                    $display_pages[] = $page_name;
                }
            }

            echo $display_pages ? implode(', ', $display_pages) : '-';
            break;

        case 'faqs_type':
            $is_global = get_post_meta($post_id, 'ffw_is_global_faq', true);
            $classname = $is_global ? 'faqs_type-global' : 'faqs_type-general';
            $text = $is_global ? esc_html__('Global', 'faq-for-woocommerce') : esc_html__('General', 'faq-for-woocommerce');
            echo '<span class="'. esc_attr($classname) .'">'. esc_html($text) .'</span>';
            break;
    }
}

add_filter( 'pll_get_post_types', 'ffw_polylang_support', 10, 2 );
function ffw_polylang_support($post_types, $is_translatable) {
    if ( $is_translatable ) {
        $post_types['ffw'] = 'ffw';
        $post_types['ffw_customer_qna'] = 'ffw_customer_qna';
    }

    return $post_types;
}

