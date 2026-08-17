<?php
/**
 * Whether WooCommerce is active.
 *
 * @since 1.8.18
 * @return bool
 */
if ( ! function_exists( 'ffw_is_woocommerce_active' ) ) {
    function ffw_is_woocommerce_active() {
        return class_exists( 'WooCommerce', false );
    }
}

/**
 * Alias used across admin and frontend.
 *
 * @return bool
 */
if ( ! function_exists( 'ffw_check_woocommerce' ) ) {
    function ffw_check_woocommerce() {
        return ffw_is_woocommerce_active();
    }
}

/**
 * Parse a comma/space separated list of IDs.
 *
 * @param mixed $value Raw IDs.
 * @since 1.8.18
 * @return int[]
 */
if ( ! function_exists( 'ffw_parse_id_list' ) ) {
    function ffw_parse_id_list( $value ) {
        if ( is_array( $value ) ) {
            return array_values( array_filter( array_map( 'intval', $value ) ) );
        }

        if ( '' === $value || null === $value || false === $value ) {
            return array();
        }

        $parts = preg_split( '/[\s,]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY );

        return array_values( array_filter( array_map( 'intval', $parts ) ) );
    }
}

/**
 * Treat shortcode-style flags as boolean.
 *
 * @param mixed $value Raw value.
 * @since 1.8.18
 * @return bool
 */
if ( ! function_exists( 'ffw_is_truthy' ) ) {
    function ffw_is_truthy( $value ) {
        return true === $value || 1 === $value || '1' === $value || 'true' === $value;
    }
}

/**
 * Map FAQ IDs to the current WPML language.
 *
 * @param array $faq_ids FAQ post IDs.
 * @since 1.8.18
 * @return array
 */
if ( ! function_exists( 'ffw_map_faq_ids_to_current_language' ) ) {
    function ffw_map_faq_ids_to_current_language( $faq_ids ) {
        if ( empty( $faq_ids ) || ! is_array( $faq_ids ) ) {
            return array();
        }

        $faq_ids = array_values( array_unique( $faq_ids ) );

        if ( ! defined( 'ICL_SITEPRESS_VERSION' ) || ! function_exists( 'apply_filters' ) ) {
            return $faq_ids;
        }

        global $sitepress;
        if ( ! $sitepress ) {
            return $faq_ids;
        }

        $default_lang = $sitepress->get_default_language();
        $current_lang = $sitepress->get_current_language();

        if ( $current_lang === $default_lang ) {
            return $faq_ids;
        }

        $translated = array();
        foreach ( $faq_ids as $faq_id ) {
            $mapped = apply_filters( 'wpml_object_id', $faq_id, 'ffw', false, $current_lang );
            if ( $mapped ) {
                $translated[] = $mapped;
            }
        }

        return array_values( array_unique( $translated ) );
    }
}

/**
 * Current product ID when viewing a WooCommerce product.
 *
 * @since 1.8.18
 * @return int
 */
if ( ! function_exists( 'ffw_get_current_product_id' ) ) {
    function ffw_get_current_product_id() {
        if ( ! ffw_is_woocommerce_active() ) {
            return 0;
        }

        if ( function_exists( 'is_product' ) && is_product() ) {
            global $product;
            if ( is_object( $product ) && method_exists( $product, 'get_id' ) ) {
                return (int) $product->get_id();
            }

            return (int) get_the_ID();
        }

        return 0;
    }
}

/**
 * Selected FAQs page ID from settings.
 *
 * @since 1.8.18
 * @return int
 */
if ( ! function_exists( 'ffw_get_faq_page_id' ) ) {
    function ffw_get_faq_page_id() {
        $options = get_option( 'ffw_general_settings', array() );
        $options = is_array( $options ) ? $options : array();

        return isset( $options['ffw_faq_page'] ) ? absint( $options['ffw_faq_page'] ) : 0;
    }
}

/**
 * HTML attributes for a displayed FAQ item (hash deep-link target).
 *
 * @param int $faq_id FAQ post ID.
 * @since 1.8.18
 * @return string
 */
if ( ! function_exists( 'ffw_get_faq_item_html_attrs' ) ) {
    function ffw_get_faq_item_html_attrs( $faq_id ) {
        $faq_id = (int) $faq_id;
        if ( 0 === $faq_id ) {
            return '';
        }

        if ( $faq_id < 0 ) {
            return sprintf(
                'id="%s" data-ffw-custom-faq-id="%d"',
                esc_attr( 'ffw-custom-faq-' . abs( $faq_id ) ),
                $faq_id
            );
        }

        return sprintf(
            'id="%s" data-ffw-faq-id="%d"',
            esc_attr( 'ffw-faq-' . $faq_id ),
            $faq_id
        );
    }
}

/**
 * Permalink + hash for one FAQ. Uses the FAQ page when one is set.
 *
 * @param int $faq_id FAQ post ID.
 * @since 1.8.18
 * @return string
 */
if ( ! function_exists( 'ffw_get_faq_deep_link' ) ) {
    function ffw_get_faq_deep_link( $faq_id ) {
        $hash    = '#ffw-faq-' . absint( $faq_id );
        $page_id = ffw_get_faq_page_id();

        if ( $page_id ) {
            return get_permalink( $page_id ) . $hash;
        }

        return $hash;
    }
}

/**
 * All published FAQs.
 *
 * @param array $args Query args.
 * @since 1.8.18
 * @return array
 */
if ( ! function_exists( 'ffw_get_all_faqs_for_display' ) ) {
    function ffw_get_all_faqs_for_display( $args = array() ) {
        $ids = get_posts(
            array(
                'post_type'      => 'ffw',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => ! empty( $args['order_by'] ) ? $args['order_by'] : 'id',
                'order'          => ! empty( $args['order'] ) ? $args['order'] : 'ASC',
                'fields'         => 'ids',
            )
        );

        return ffw_get_faq_list_by_faq_ids( ffw_map_faq_ids_to_current_language( $ids ) );
    }
}

/**
 * FAQs in one or more ffw-category terms.
 *
 * @param mixed $cats Term IDs or slugs.
 * @param array $args Query args.
 * @since 1.8.18
 * @return array
 */
if ( ! function_exists( 'ffw_get_faqs_by_faq_category' ) ) {
    function ffw_get_faqs_by_faq_category( $cats, $args = array() ) {
        if ( empty( $cats ) ) {
            return array();
        }

        if ( is_string( $cats ) ) {
            $cats = preg_split( '/[\s,]+/', $cats, -1, PREG_SPLIT_NO_EMPTY );
        }

        $term_ids = array();
        $slugs    = array();

        foreach ( (array) $cats as $cat ) {
            if ( is_numeric( $cat ) ) {
                $term_ids[] = (int) $cat;
            } else {
                $slugs[] = sanitize_title( $cat );
            }
        }

        $tax_query = array( 'relation' => 'OR' );

        if ( ! empty( $term_ids ) ) {
            $tax_query[] = array(
                'taxonomy' => 'ffw-category',
                'field'    => 'term_id',
                'terms'    => $term_ids,
            );
        }

        if ( ! empty( $slugs ) ) {
            $tax_query[] = array(
                'taxonomy' => 'ffw-category',
                'field'    => 'slug',
                'terms'    => $slugs,
            );
        }

        if ( count( $tax_query ) < 2 ) {
            return array();
        }

        $ids = get_posts(
            array(
                'posts_per_page' => -1,
                'post_type'      => 'ffw',
                'post_status'    => 'publish',
                'fields'         => 'ids',
                'orderby'        => ! empty( $args['order_by'] ) ? $args['order_by'] : 'id',
                'order'          => ! empty( $args['order'] ) ? $args['order'] : 'ASC',
                'tax_query'      => $tax_query,
            )
        );

        return ffw_get_faq_list_by_faq_ids( ffw_map_faq_ids_to_current_language( $ids ) );
    }
}

/**
 * FAQs assigned to WooCommerce product categories via term meta.
 *
 * @param mixed $cat_ids Product category term IDs.
 * @since 1.8.18
 * @return array
 */
if ( ! function_exists( 'ffw_get_faqs_by_product_category_ids' ) ) {
    function ffw_get_faqs_by_product_category_ids( $cat_ids ) {
        $ids = ffw_parse_id_list( $cat_ids );

        if ( empty( $ids ) || ! taxonomy_exists( 'product_cat' ) ) {
            return array();
        }

        $faq_ids = array();
        foreach ( $ids as $term_id ) {
            $term_faqs = get_term_meta( $term_id, 'ffw_cat_faq_post_ids', true );
            if ( ! empty( $term_faqs ) && is_array( $term_faqs ) ) {
                $faq_ids = array_merge( $faq_ids, $term_faqs );
            }
        }

        $faq_ids = apply_filters( 'ffw_filter_faq_post_ids_by_cat', array_unique( $faq_ids ) );

        return ffw_get_faq_list_by_faq_ids( ffw_map_faq_ids_to_current_language( $faq_ids ) );
    }
}

/**
 * Resolve FAQs for shortcode, product tab, and builders.
 *
 * Sources: all, faq_cat, faq_ids, product, product_cats, current_product.
 *
 * @param array $args Display args.
 * @since 1.8.18
 * @return array
 */
if ( ! function_exists( 'ffw_get_faqs_for_display' ) ) {
    function ffw_get_faqs_for_display( $args = array() ) {
        $defaults = array(
            'source'          => '',
            'product_id'      => 0,
            'faq_cat'         => '',
            'faq_ids'         => '',
            'cat_ids'         => '',
            'product_cat_ids' => '',
            'dynamic_post'    => false,
            'order_by'        => 'id',
            'order'           => 'ASC',
            'group_id'        => 0,
        );
        $args   = wp_parse_args( $args, $defaults );
        $source = sanitize_key( (string) $args['source'] );
        $faqs   = array();

        if ( '' === $source ) {
            if ( ! empty( $args['faq_ids'] ) ) {
                $source = 'faq_ids';
            } elseif ( ! empty( $args['faq_cat'] ) ) {
                $source = 'faq_cat';
            } elseif ( ! empty( $args['cat_ids'] ) ) {
                $source = 'faq_cat';
            } elseif ( ! empty( $args['product_cat_ids'] ) ) {
                $source = 'product_cats';
            } elseif ( ffw_is_truthy( $args['dynamic_post'] ) ) {
                $source = 'current_product';
            } elseif ( ! empty( $args['group_id'] ) ) {
                $source = 'group';
            } elseif ( ! empty( $args['product_id'] ) ) {
                $source = 'product';
            }
        }

        switch ( $source ) {
            case 'all':
                $faqs = ffw_get_all_faqs_for_display( $args );
                break;

            case 'faq_cat':
                $cats = ! empty( $args['faq_cat'] ) ? $args['faq_cat'] : $args['cat_ids'];
                $faqs = ffw_get_faqs_by_faq_category( $cats, $args );
                break;

            case 'faq_ids':
                $faqs = ffw_get_faq_list_by_faq_ids(
                    ffw_map_faq_ids_to_current_language( ffw_parse_id_list( $args['faq_ids'] ) )
                );
                break;

            case 'group':
                $group_ids = apply_filters( 'ffw_get_group_faq_ids', array(), absint( $args['group_id'] ) );
                $faqs      = ffw_get_faq_list_by_faq_ids(
                    ffw_map_faq_ids_to_current_language( ffw_parse_id_list( $group_ids ) )
                );
                break;

            case 'product_cats':
                $faqs = ffw_get_faqs_by_product_category_ids( $args['product_cat_ids'] );
                break;

            case 'current_product':
                $product_id = ! empty( $args['product_id'] ) ? (int) $args['product_id'] : ffw_get_current_product_id();
                if ( $product_id ) {
                    $faqs = ffw_get_product_faq_ids( $product_id );
                }
                break;

            case 'product':
            default:
                if ( ! empty( $args['product_id'] ) ) {
                    $faqs = ffw_get_product_faq_ids( (int) $args['product_id'] );
                }
                break;
        }

        if ( ! is_array( $faqs ) ) {
            $faqs = array();
        }

        return apply_filters( 'ffw_faqs_for_display', $faqs, $args, $source );
    }
}

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
			
			// WPML Support -> Map current product ID to default language product ID.
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && function_exists( 'apply_filters' ) ) {
				global $sitepress;
				$default_lang = $sitepress->get_default_language();
				$current_lang = $sitepress->get_current_language();

				// Only translate if viewing a non-default language.
				if ( $current_lang !== $default_lang ) {
					$product_id = apply_filters( 'wpml_object_id', $product_id, 'product', false, $default_lang );
				}
			}

            // option values.
            $options    = get_option( 'ffw_general_settings' );
            
            $faq_post_ids = [];
            $faq_cat_post_ids = [];
            $faq_tag_post_ids = [];

            // get categories.
            $cat_terms = taxonomy_exists( 'product_cat' ) ? get_the_terms( $product_id, 'product_cat' ) : array();
    
            // fetch category faqs ids.
            if(!empty($cat_terms) && ! is_wp_error( $cat_terms ) ) {
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
            $tag_terms = taxonomy_exists( 'product_tag' ) ? get_the_terms( $product_id, 'product_tag' ) : array();
    
            // fetch tag faqs ids.
            if(!empty($tag_terms) && ! is_wp_error( $tag_terms ) ) {
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
			
			// WPML Support -> Map FAQ IDs to current language.
			if ( defined( 'ICL_SITEPRESS_VERSION' ) && function_exists( 'apply_filters' ) ) {
				global $sitepress;
				$default_lang = $sitepress->get_default_language();
				$current_lang = $sitepress->get_current_language();

				// Only translate if viewing a non-default language
				if ( $current_lang !== $default_lang ) {
					foreach ( $faq_ids as $faq_id ) {
						$translated_id = apply_filters( 'wpml_object_id', $faq_id, 'ffw', false, $current_lang );
						if ( $translated_id ) {
							$translated_faq_ids[] = $translated_id;
						}
					}
					
					$faq_ids = $translated_faq_ids;
				}
			}

            $faq_list = ffw_get_faq_list_by_faq_ids($faq_ids);

            return $faq_list;
        }

        return array();
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
            return array();
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
            return ffw_get_faqs_by_faq_category( $args['cat_ids'], $args );
        }

        return array();
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
        $atts    = is_array( $atts ) ? $atts : array();

        $explicit_template = array_key_exists( 'template', $atts ) && '' !== $atts['template']
            ? (int) $atts['template']
            : null;

		$atts = shortcode_atts( array(
			'template'     => '',
			'dynamic_post' => false,
			'cat_ids'      => '',
			'faq_cat'      => '',
			'faq_ids'      => '',
			'source'       => '',
            'group_id'     => '',
            'order_by'     => 'id',
            'order'        => 'ASC',
			'id'           => '',
		), $atts, 'ffw_template' );

	    if( ffw_is_truthy( $atts['dynamic_post'] ) ) {
	        if( isset($options["ffw_hide_dynamic_shortcode_preview"]) && 2 == $options["ffw_hide_dynamic_shortcode_preview"] ) {
	            return false;
            }
        }else {

            if( isset($options["ffw_hide_general_shortcode_preview"]) && 2 == $options["ffw_hide_general_shortcode_preview"] ) {
                return false;
            }
        }

        $has_specific_source = ! empty( $atts['source'] )
            || ! empty( $atts['faq_cat'] )
            || ! empty( $atts['faq_ids'] )
            || ! empty( $atts['group_id'] )
            || ! empty( $atts['cat_ids'] )
            || ffw_is_truthy( $atts['dynamic_post'] )
            || ( '' !== $atts['id'] && null !== $atts['id'] );

        $arguments = array(
            'cat_ids'      => $atts['cat_ids'],
            'faq_cat'      => $atts['faq_cat'],
            'faq_ids'      => $atts['faq_ids'],
            'source'       => $atts['source'],
            'group_id'     => $atts['group_id'],
            'order_by'     => $atts['order_by'],
            'order'        => $atts['order'],
            'dynamic_post' => $atts['dynamic_post'],
            'product_id'   => (int) $atts['id'],
        );

        if ( ! $has_specific_source ) {
            $arguments['source']     = 'all';
            $arguments['product_id'] = 0;
        }

        if ( null !== $explicit_template ) {
            $arguments['template'] = $explicit_template;
        }

		return ffw_get_template( (int) $atts['id'], $arguments, true );
	}
}
add_shortcode('ffw_template', 'ffw_show_template_shortcode');

/**
 * Get template to show in front.
 *
 * @param int   $id            product or archive id.
 * @param mixed $args          Display args or legacy false.
 * @param bool  $is_shortcode  Whether rendering from shortcode.
 *
 * @return string|void
 * @since  1.1.3
 *
 */
if ( ! function_exists('ffw_get_template') ) {
	function ffw_get_template( $id, $args = array(), $is_shortcode = false ) {
        if ( ! is_array( $args ) ) {
            $args = array();
        }

        //get layout.
        $options = get_option( 'ffw_general_settings' );
        $layout = isset( $options['ffw_layout'] ) ? (int) $options['ffw_layout'] : 1;

        if ( isset( $args['template'] ) && '' !== $args['template'] && false !== $args['template'] ) {
            $layout = (int) $args['template'];
        }

        $shortcode_wrap_class = $is_shortcode ? ['ffw-main-wrapper-shortcode'] : [];

        $display_args = array(
            'source'       => isset( $args['source'] ) ? $args['source'] : '',
            'product_id'   => ! empty( $args['product_id'] ) ? (int) $args['product_id'] : (int) $id,
            'faq_cat'      => isset( $args['faq_cat'] ) ? $args['faq_cat'] : '',
            'faq_ids'      => isset( $args['faq_ids'] ) ? $args['faq_ids'] : '',
            'group_id'     => isset( $args['group_id'] ) ? absint( $args['group_id'] ) : 0,
            'cat_ids'      => isset( $args['cat_ids'] ) ? $args['cat_ids'] : '',
            'dynamic_post' => isset( $args['dynamic_post'] ) ? $args['dynamic_post'] : false,
            'order_by'     => isset( $args['order_by'] ) ? $args['order_by'] : 'id',
            'order'        => isset( $args['order'] ) ? $args['order'] : 'ASC',
        );

        $faqs = ffw_get_faqs_for_display( $display_args );

        $display_schema_type = 'product_page';
        if ( $is_shortcode
            || in_array( $display_args['source'], array( 'all', 'faq_cat', 'faq_ids', 'group' ), true )
            || ! empty( $display_args['faq_cat'] )
            || ! empty( $display_args['faq_ids'] )
            || ! empty( $display_args['group_id'] )
            || ! empty( $display_args['cat_ids'] ) ) {
            $display_schema_type = 'shortcode';
        }

        if ( empty( $display_args['cat_ids'] ) && empty( $display_args['faq_cat'] ) && empty( $display_args['faq_ids'] ) && empty( $display_args['group_id'] )
            && ! in_array( $display_args['source'], array( 'all', 'faq_cat', 'faq_ids', 'group' ), true )
            && ffw_is_pro_activated() && ffw_is_woocommerce_active()
            && function_exists( 'ffw_get_archive_faqs' ) ) {
            if ( ( function_exists( 'is_product_category' ) && is_product_category() )
                || ( function_exists( 'is_product_tag' ) && is_product_tag() ) ) {
                $archive_type = is_product_tag() ? 'product_tag' : 'product_category';
                $faqs         = ffw_get_archive_faqs( $id, $archive_type );
            }
        }

        // filtered faqs.
        $faqs = apply_filters('ffw_filter_shortcode_faqs', $faqs, $args);

        $render_args = array(
            'id'                   => $id,
            'layout'               => $layout,
            'shortcode_wrap_class' => $shortcode_wrap_class,
            'display_schema_type'  => $display_schema_type,
        );

        foreach ( array( 'limit', 'show_search', 'enable_schema', 'display_mode', 'expand_first' ) as $extra_key ) {
            if ( array_key_exists( $extra_key, $args ) ) {
                $render_args[ $extra_key ] = $args[ $extra_key ];
            }
        }

        if ($is_shortcode) {
            return ffw_get_template_by_faqs($faqs, $render_args);
        }

        echo ffw_get_template_by_faqs($faqs, $render_args);
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
        $ffw_expand_first = false;

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
        $limit = isset( $args['limit'] ) ? absint( $args['limit'] ) : 0;
        if ( $limit > 0 && is_array( $faqs ) && count( $faqs ) > $limit ) {
            $faqs = array_slice( $faqs, 0, $limit );
        }

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
        $display_mode = isset( $args['display_mode'] ) ? sanitize_key( $args['display_mode'] ) : 'accordion';
        $ffw_expand_first = ! empty( $args['expand_first'] );
        $enable_schema = ! array_key_exists( 'enable_schema', $args ) || ! empty( $args['enable_schema'] );
        $show_inline_search = ! empty( $args['show_search'] );

        if( empty($layout) ) {
            //get layout.
            $options = FAQ_Woocommerce_Public::instance()->options;
            $layout = isset( $options['ffw_layout'] ) ? (int) $options['ffw_layout'] : 1;
        }

        $wrapper_classes = apply_filters('ffw_filter_template_wrapper_classes', ['ffw-main-wrapper'], $layout, $id);
        $wrapper_classes = array_merge($wrapper_classes, $shortcode_wrap_class);
        if ( 'list' === $display_mode ) {
            $wrapper_classes[] = 'ffw-display-mode-list';
        }
        if ( $show_inline_search ) {
            $wrapper_classes[] = 'ffw-has-inline-search';
        }
        $wrapper_classes = implode(' ', $wrapper_classes);

        //faq schema.
        if ( $enable_schema ) {
            new FAQ_Woocommerce_Schema($faqs, $display_schema_type);
        }

        ffw_prime_inline_faq_answers( $faqs );

        // Get registered option
        $options    = FAQ_Woocommerce_Public::instance()->options;
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
        wp_enqueue_style( 'ffw-display-faqs-style' );

        ob_start();

        $expand_attr = $ffw_expand_first ? ' data-ffw-expand="first"' : '';
        $content .= '<div style="width: '.$width.'%;max-width: 100%;" class="'. esc_attr($wrapper_classes) .'" id="ffw-main-wrapper" data-item_id="'. esc_attr($id) .'" data-layout="'. esc_attr($layout) .'" data-archive_type="'. esc_attr($archive_type) .'"' . $expand_attr . ' >';
        $content .= '<input type="hidden" id="ffw-hidden-faqs" value="' . base64_encode(wp_json_encode($faqs)) . '" />';

        if ( array_key_exists( 'show_search', $args ) ) {
            add_filter( 'ffw_enable_search_input', '__return_false' );
        }

        do_action('ffw_faq_header');
        do_action('ffw_before_faq_start');
        do_action('ffw_expand_collapse_all');

        if ( $show_inline_search ) {
            echo ffw_get_inline_search_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }

        if ( array_key_exists( 'show_search', $args ) ) {
            remove_filter( 'ffw_enable_search_input', '__return_false' );
        }

        $ffw_wrapper_classes = ['ffw-wrapper', $layout_name];

        if( 3 === $layout ) {
            $ffw_wrapper_classes = array_merge($ffw_wrapper_classes, ['ffw-trip-wrapper']);
        }elseif ( 4 === $layout ) {
            $ffw_wrapper_classes = array_merge($ffw_wrapper_classes, ['ffw-pop-wrapper']);
        }

        $ffw_wrapper_classes = apply_filters("ffw_filter_layout_wrapper_classes", $ffw_wrapper_classes, $layout);
        $ffw_wrapper_classes = implode(' ', $ffw_wrapper_classes);

        //get faq templates
        if ( 'list' === $display_mode ) {
            include FFW_FILE_DIR . '/views/ffw-list-template.php';
        } elseif ( 1 === $layout || 2 === $layout ) {
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
 * Render FAQs for the Gutenberg block and other builders.
 *
 * @since 1.8.18
 *
 * @param array $args Display args.
 * @return string
 */
if ( ! function_exists( 'ffw_render_display_faqs' ) ) {
    function ffw_render_display_faqs( $args = array() ) {
        $defaults = array(
            'source'         => 'all',
            'faq_cat'        => '',
            'faq_ids'        => '',
            'product_id'     => 0,
            'group_id'       => 0,
            'layout'         => 0,
            'limit'          => 0,
            'order_by'       => 'id',
            'order'          => 'ASC',
            'show_search'    => false,
            'enable_schema'  => true,
            'display_mode'   => 'accordion',
            'expand_first'   => false,
        );
        $args = wp_parse_args( $args, $defaults );

        $layout = absint( $args['layout'] );
        if ( $layout < 1 ) {
            $options = get_option( 'ffw_general_settings', array() );
            $layout  = isset( $options['ffw_layout'] ) ? (int) $options['ffw_layout'] : 1;
        }

        $template_args = array(
            'source'         => sanitize_key( $args['source'] ),
            'faq_cat'        => $args['faq_cat'],
            'faq_ids'        => $args['faq_ids'],
            'product_id'     => absint( $args['product_id'] ),
            'group_id'       => absint( $args['group_id'] ),
            'template'       => $layout,
            'order_by'       => sanitize_key( $args['order_by'] ),
            'order'          => 'DESC' === strtoupper( $args['order'] ) ? 'DESC' : 'ASC',
            'limit'          => absint( $args['limit'] ),
            'show_search'    => ! empty( $args['show_search'] ),
            'enable_schema'  => ! empty( $args['enable_schema'] ),
            'display_mode'   => 'list' === $args['display_mode'] ? 'list' : 'accordion',
            'expand_first'   => ! empty( $args['expand_first'] ),
        );

        $html = ffw_get_template( (int) $template_args['product_id'], $template_args, true );

        return is_string( $html ) ? $html : '';
    }
}

/**
 * Whether a builder control value is on.
 *
 * Accepts Gutenberg booleans, Elementor yes/empty, and Divi on/off.
 *
 * @since 1.8.18
 * @param mixed $value Control value.
 * @return bool
 */
function ffw_builder_is_on( $value ) {
    if ( is_bool( $value ) ) {
        return $value;
    }

    if ( is_numeric( $value ) ) {
        return 1 === (int) $value;
    }

    return in_array( strtolower( trim( (string) $value ) ), array( 'on', 'yes', 'true', '1' ), true );
}

/**
 * Whether the current request is a builder editor/preview.
 *
 * @since 1.8.18
 * @return bool
 */
function ffw_builder_is_editor_context() {
    if ( defined( 'REST_REQUEST' ) && REST_REQUEST && isset( $_REQUEST['context'] ) && 'edit' === $_REQUEST['context'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return true;
    }

    if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
        return true;
    }

    if ( function_exists( 'et_core_is_fb_enabled' ) && et_core_is_fb_enabled() ) {
        return true;
    }

    if ( defined( 'ET_FB_PROCESSING' ) && ET_FB_PROCESSING ) {
        return true;
    }

    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        $route = isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? (string) $GLOBALS['wp']->query_vars['rest_route'] : '';
        if ( false !== strpos( $route, '/happy-faq/' ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Layout choices shared by Gutenberg, Elementor, and Divi.
 *
 * @since 1.8.18
 * @param string $format list|assoc
 * @return array
 */
function ffw_get_builder_layout_options( $format = 'list' ) {
    $items = array(
        '0' => esc_html__( 'Default (Settings)', 'faq-for-woocommerce' ),
        '1' => esc_html__( 'Classic', 'faq-for-woocommerce' ),
        '2' => esc_html__( 'Whitish', 'faq-for-woocommerce' ),
        '3' => esc_html__( 'Trip', 'faq-for-woocommerce' ),
        '4' => esc_html__( 'Pop', 'faq-for-woocommerce' ),
        '5' => esc_html__( 'Basic', 'faq-for-woocommerce' ),
    );

    $items = apply_filters( 'ffw_builder_layout_options', $items );

    if ( 'assoc' === $format ) {
        return $items;
    }

    $list = array();
    foreach ( $items as $value => $label ) {
        $list[] = array(
            'value' => (string) $value,
            'label' => $label,
        );
    }

    return apply_filters( 'ffw_block_layout_options', $list );
}

/**
 * Source choices shared by builders.
 *
 * @since 1.8.18
 * @param string $format list|assoc
 * @return array
 */
function ffw_get_builder_source_options( $format = 'list' ) {
    $items = array(
        'all'     => esc_html__( 'All FAQs', 'faq-for-woocommerce' ),
        'faq_cat' => esc_html__( 'FAQ category', 'faq-for-woocommerce' ),
        'faq_ids' => esc_html__( 'Selected FAQs', 'faq-for-woocommerce' ),
    );

    if ( ffw_is_woocommerce_active() ) {
        $items['current_product'] = esc_html__( 'Current product', 'faq-for-woocommerce' );
        $items['product']         = esc_html__( 'Product ID', 'faq-for-woocommerce' );
    }

    $items = apply_filters( 'ffw_builder_source_options', $items );

    if ( 'assoc' === $format ) {
        return $items;
    }

    $list = array();
    foreach ( $items as $value => $label ) {
        $list[] = array(
            'value' => $value,
            'label' => $label,
        );
    }

    return $list;
}

/**
 * FAQ category choices.
 *
 * @since 1.8.18
 * @param string $format list|assoc
 * @return array
 */
function ffw_get_builder_faq_category_options( $format = 'list' ) {
    $items = array();
    $terms = get_terms(
        array(
            'taxonomy'   => 'ffw-category',
            'hide_empty' => false,
        )
    );

    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            $items[ (string) $term->term_id ] = $term->name;
        }
    }

    if ( 'assoc' === $format ) {
        return $items;
    }

    $list = array();
    foreach ( $items as $value => $label ) {
        $list[] = array(
            'value' => (string) $value,
            'label' => $label,
        );
    }

    return $list;
}

/**
 * FAQ Group choices for builders. Empty until Pro fills the filter.
 *
 * @since 1.8.18
 * @param string $format list|assoc
 * @return array
 */
function ffw_get_builder_group_options( $format = 'list' ) {
    $items = apply_filters( 'ffw_builder_group_options', array() );
    if ( ! is_array( $items ) ) {
        $items = array();
    }

    if ( 'assoc' === $format ) {
        return $items;
    }

    $list = array();
    foreach ( $items as $value => $label ) {
        $list[] = array(
            'value' => (string) $value,
            'label' => $label,
        );
    }

    return $list;
}

/**
 * Published FAQ posts for Elementor SELECT2.
 *
 * @since 1.8.18
 * @return array
 */
function ffw_get_builder_faq_post_options() {
    $posts = get_posts(
        array(
            'post_type'      => 'ffw',
            'post_status'    => 'publish',
            'posts_per_page' => 300,
            'orderby'        => 'title',
            'order'          => 'ASC',
        )
    );

    $items = array();
    foreach ( $posts as $post ) {
        $items[ (string) $post->ID ] = $post->post_title;
    }

    return $items;
}

/**
 * Map builder field names to ffw_render_display_faqs() args.
 *
 * @since 1.8.18
 * @param array $raw Gutenberg, Elementor, or Divi settings.
 * @return array
 */
function ffw_normalize_builder_display_args( $raw = array() ) {
    $raw = is_array( $raw ) ? $raw : array();

    $aliases = array(
        'faqCat'       => 'faq_cat',
        'faqIds'       => 'faq_ids',
        'productId'    => 'product_id',
        'displayMode'  => 'display_mode',
        'orderBy'      => 'order_by',
        'showSearch'   => 'show_search',
        'enableSchema' => 'enable_schema',
        'expandFirst'  => 'expand_first',
        'faq_source'   => 'source',
        'faq_limit'    => 'limit',
        'faq_order'    => 'order',
        'faqSource'    => 'source',
        'faqLimit'     => 'limit',
        'faqOrder'     => 'order',
        'groupId'      => 'group_id',
        'faqGroup'     => 'group_id',
    );

    foreach ( $aliases as $from => $to ) {
        if ( isset( $raw[ $from ] ) && ! isset( $raw[ $to ] ) ) {
            $raw[ $to ] = $raw[ $from ];
        }
    }

    $faq_ids = isset( $raw['faq_ids'] ) ? $raw['faq_ids'] : '';
    if ( is_array( $faq_ids ) ) {
        $faq_ids = implode( ',', array_map( 'absint', $faq_ids ) );
    }

    $show_search = array_key_exists( 'show_search', $raw )
        ? ffw_builder_is_on( $raw['show_search'] )
        : true;
    $enable_schema = array_key_exists( 'enable_schema', $raw )
        ? ffw_builder_is_on( $raw['enable_schema'] )
        : true;

    $order = isset( $raw['order'] ) ? strtoupper( (string) $raw['order'] ) : 'ASC';

    return array(
        'source'        => isset( $raw['source'] ) ? sanitize_key( $raw['source'] ) : 'all',
        'faq_cat'       => isset( $raw['faq_cat'] ) ? $raw['faq_cat'] : '',
        'faq_ids'       => $faq_ids,
        'product_id'    => isset( $raw['product_id'] ) ? absint( $raw['product_id'] ) : 0,
        'group_id'      => isset( $raw['group_id'] ) ? absint( $raw['group_id'] ) : 0,
        'layout'        => isset( $raw['layout'] ) ? absint( $raw['layout'] ) : 0,
        'limit'         => isset( $raw['limit'] ) ? absint( $raw['limit'] ) : 0,
        'order_by'      => isset( $raw['order_by'] ) ? sanitize_key( $raw['order_by'] ) : 'id',
        'order'         => 'DESC' === $order ? 'DESC' : 'ASC',
        'show_search'   => $show_search,
        'enable_schema' => $enable_schema,
        'display_mode'  => isset( $raw['display_mode'] ) && 'list' === $raw['display_mode'] ? 'list' : 'accordion',
        'expand_first'  => ! empty( $raw['expand_first'] ) && ffw_builder_is_on( $raw['expand_first'] ),
    );
}

/**
 * Render FAQs for a builder, with editor placeholders.
 *
 * @since 1.8.18
 * @param array $raw Builder settings.
 * @return string
 */
function ffw_render_builder_display_faqs( $raw = array() ) {
    $args = ffw_normalize_builder_display_args( $raw );

    if ( in_array( $args['source'], array( 'product', 'current_product' ), true ) && ! ffw_is_woocommerce_active() ) {
        $args['source'] = 'all';
    }

    if ( 'group' === $args['source'] && empty( $args['group_id'] ) && ffw_builder_is_editor_context() ) {
        return '<p class="ffw-builder-placeholder">' . esc_html__( 'Select an FAQ Group. Create groups under Happy FAQs → FAQ Groups.', 'faq-for-woocommerce' ) . '</p>';
    }

    if ( 'current_product' === $args['source'] && ! ffw_get_current_product_id() && ffw_builder_is_editor_context() ) {
        return '<p class="ffw-builder-placeholder">' . esc_html__( 'FAQs for the current product will show on product pages.', 'faq-for-woocommerce' ) . '</p>';
    }

    $html = ffw_render_display_faqs( $args );

    if ( '' === $html && ffw_builder_is_editor_context() ) {
        return '<p class="ffw-builder-placeholder">' . esc_html__( 'No FAQs found for this source. Create FAQs or choose another source.', 'faq-for-woocommerce' ) . '</p>';
    }

    return $html;
}

/**
 * Store inline FAQ answers so templates can render items that are not CPT posts.
 *
 * Custom FAQ builders use negative integer IDs. Do not pass those through absint().
 *
 * @since 2.0.0
 * @param array $faqs FAQ rows.
 * @return void
 */
function ffw_prime_inline_faq_answers( $faqs ) {
    if ( ! is_array( $faqs ) ) {
        return;
    }

    $map = isset( $GLOBALS['ffw_inline_faq_answers'] ) && is_array( $GLOBALS['ffw_inline_faq_answers'] )
        ? $GLOBALS['ffw_inline_faq_answers']
        : array();

    foreach ( $faqs as $faq ) {
        if ( ! is_array( $faq ) || empty( $faq['inline'] ) ) {
            continue;
        }

        $faq_id = isset( $faq['id'] ) ? (int) $faq['id'] : 0;
        if ( 0 === $faq_id ) {
            continue;
        }

        $map[ $faq_id ] = isset( $faq['answer'] ) ? $faq['answer'] : '';
    }

    $GLOBALS['ffw_inline_faq_answers'] = $map;
}

/**
 * Get an inline FAQ answer by mapped id.
 *
 * @since 2.0.0
 * @param int $faq_id FAQ id, including negative custom ids.
 * @return string|null
 */
function ffw_get_inline_faq_answer( $faq_id ) {
    $faq_id = (int) $faq_id;
    if ( empty( $GLOBALS['ffw_inline_faq_answers'] ) || ! is_array( $GLOBALS['ffw_inline_faq_answers'] ) ) {
        return null;
    }

    if ( ! array_key_exists( $faq_id, $GLOBALS['ffw_inline_faq_answers'] ) ) {
        return null;
    }

    return $GLOBALS['ffw_inline_faq_answers'][ $faq_id ];
}

/**
 * Normalize Custom FAQ repeater items for the shared templates.
 *
 * @since 2.0.0
 * @param array $items Builder items.
 * @return array
 */
function ffw_normalize_custom_faq_items( $items ) {
    if ( ! is_array( $items ) ) {
        return array();
    }

    $faqs  = array();

    foreach ( $items as $item ) {
        if ( ! is_array( $item ) ) {
            continue;
        }

        $question = isset( $item['question'] ) ? sanitize_text_field( wp_unslash( $item['question'] ) ) : '';
        $answer   = isset( $item['answer'] ) ? wp_kses_post( wp_unslash( $item['answer'] ) ) : '';

        if ( '' === $question && '' === wp_strip_all_tags( $answer ) ) {
            continue;
        }

        $faqs[] = array(
            'id'       => ffw_next_inline_faq_id(),
            'question' => $question,
            'answer'   => $answer,
            'inline'   => true,
        );
    }

    return $faqs;
}

/**
 * Next negative id for an inline Custom FAQ item on this request.
 *
 * Keeps HTML ids unique when more than one Custom FAQs block is on the page.
 *
 * @since 2.0.0
 * @return int
 */
function ffw_next_inline_faq_id() {
    if ( ! isset( $GLOBALS['ffw_inline_faq_seq'] ) ) {
        $GLOBALS['ffw_inline_faq_seq'] = 0;
    }

    ++$GLOBALS['ffw_inline_faq_seq'];

    return -(int) $GLOBALS['ffw_inline_faq_seq'];
}

/**
 * Map Custom FAQ builder fields to render args.
 *
 * @since 2.0.0
 * @param array $raw Gutenberg, Elementor, or Divi settings.
 * @return array
 */
function ffw_normalize_custom_faq_args( $raw = array() ) {
    $raw = is_array( $raw ) ? $raw : array();

    $aliases = array(
        'displayMode'        => 'display_mode',
        'showSearch'         => 'show_search',
        'enableSchema'       => 'enable_schema',
        'expandFirst'        => 'expand_first',
        'faq_items'          => 'items',
        'faqItems'           => 'items',
        'questionColor'      => 'question_color',
        'questionBgColor'    => 'question_bg_color',
        'questionHoverColor' => 'question_hover_color',
        'questionFontSize'   => 'question_font_size',
        'answerColor'        => 'answer_color',
        'answerBgColor'      => 'answer_bg_color',
        'answerFontSize'     => 'answer_font_size',
        'borderColor'        => 'border_color',
        'borderRadius'       => 'border_radius',
        'itemSpacing'        => 'item_spacing',
        'customCss'          => 'custom_css',
    );

    foreach ( $aliases as $from => $to ) {
        if ( isset( $raw[ $from ] ) && ! isset( $raw[ $to ] ) ) {
            $raw[ $to ] = $raw[ $from ];
        }
    }

    $show_search = array_key_exists( 'show_search', $raw )
        ? ffw_builder_is_on( $raw['show_search'] )
        : true;
    $enable_schema = array_key_exists( 'enable_schema', $raw )
        ? ffw_builder_is_on( $raw['enable_schema'] )
        : true;

    return array(
        'items'                => isset( $raw['items'] ) && is_array( $raw['items'] ) ? $raw['items'] : array(),
        'layout'               => isset( $raw['layout'] ) ? absint( $raw['layout'] ) : 0,
        'display_mode'         => isset( $raw['display_mode'] ) && 'list' === $raw['display_mode'] ? 'list' : 'accordion',
        'show_search'          => $show_search,
        'enable_schema'        => $enable_schema,
        'expand_first'         => ! empty( $raw['expand_first'] ) && ffw_builder_is_on( $raw['expand_first'] ),
        'question_color'       => isset( $raw['question_color'] ) ? ffw_sanitize_css_color( $raw['question_color'] ) : '',
        'question_bg_color'    => isset( $raw['question_bg_color'] ) ? ffw_sanitize_css_color( $raw['question_bg_color'] ) : '',
        'question_hover_color' => isset( $raw['question_hover_color'] ) ? ffw_sanitize_css_color( $raw['question_hover_color'] ) : '',
        'question_font_size'   => isset( $raw['question_font_size'] ) ? min( 72, absint( $raw['question_font_size'] ) ) : 0,
        'answer_color'         => isset( $raw['answer_color'] ) ? ffw_sanitize_css_color( $raw['answer_color'] ) : '',
        'answer_bg_color'      => isset( $raw['answer_bg_color'] ) ? ffw_sanitize_css_color( $raw['answer_bg_color'] ) : '',
        'answer_font_size'     => isset( $raw['answer_font_size'] ) ? min( 72, absint( $raw['answer_font_size'] ) ) : 0,
        'border_color'         => isset( $raw['border_color'] ) ? ffw_sanitize_css_color( $raw['border_color'] ) : '',
        'border_radius'        => isset( $raw['border_radius'] ) ? min( 48, absint( $raw['border_radius'] ) ) : 0,
        'item_spacing'         => isset( $raw['item_spacing'] ) ? min( 64, absint( $raw['item_spacing'] ) ) : 0,
        'custom_css'           => isset( $raw['custom_css'] ) ? ffw_sanitize_custom_block_css( $raw['custom_css'] ) : '',
    );
}

/**
 * Sanitize a CSS color from a builder control.
 *
 * @since 2.0.0
 * @param mixed $color Color value.
 * @return string
 */
function ffw_sanitize_css_color( $color ) {
    $color = trim( (string) $color );
    if ( '' === $color ) {
        return '';
    }

    $hex = sanitize_hex_color( $color );
    if ( $hex ) {
        return $hex;
    }

    if ( preg_match( '/^var\(\s*--[a-zA-Z0-9\-_]+\s*(?:,[^)]+)?\)$/', $color ) ) {
        return $color;
    }

    if ( preg_match( '/^(?:rgba?|hsla?)\(\s*[\d.]+%?(?:\s*,\s*[\d.]+%?){2}(?:\s*,\s*[\d.]+)?\s*\)$/i', $color ) ) {
        return $color;
    }

    return '';
}

/**
 * Strip dangerous tokens from per-block custom CSS.
 *
 * @since 2.0.0
 * @param mixed $css CSS string.
 * @return string
 */
function ffw_sanitize_custom_block_css( $css ) {
    $css = wp_unslash( (string) $css );
    $css = wp_strip_all_tags( $css );
    $css = str_ireplace( array( '</style', '<script', 'expression(', 'javascript:', '@import' ), '', $css );

    return trim( $css );
}

/**
 * Whether Custom FAQs style overrides are set.
 *
 * @since 2.0.0
 * @param array $args Normalized args.
 * @return bool
 */
function ffw_custom_faq_has_style_overrides( $args ) {
    $keys = array(
        'question_color',
        'question_bg_color',
        'question_hover_color',
        'answer_color',
        'answer_bg_color',
        'border_color',
        'custom_css',
    );

    foreach ( $keys as $key ) {
        if ( ! empty( $args[ $key ] ) ) {
            return true;
        }
    }

    return ! empty( $args['question_font_size'] )
        || ! empty( $args['answer_font_size'] )
        || ! empty( $args['border_radius'] )
        || ! empty( $args['item_spacing'] );
}

/**
 * CSS for one Custom FAQs instance. Empty values keep the layout defaults.
 *
 * Layout styles use #ffw-main-wrapper with !important, so this CSS is
 * instance-scoped and at least as specific.
 *
 * @since 2.0.0
 * @param array  $args     Normalized args.
 * @param string $instance Unique class, e.g. ffw-custom-style-1.
 * @return string
 */
function ffw_get_custom_faq_override_css( $args, $instance ) {
    $instance = preg_replace( '/[^a-zA-Z0-9\-_]/', '', (string) $instance );
    if ( '' === $instance || ! ffw_custom_faq_has_style_overrides( $args ) ) {
        return '';
    }

    $root = '#ffw-main-wrapper.' . $instance . ' #ffw-wrapper';
    $q    = implode(
        ',',
        array(
            $root . ' .ffw-button',
            $root . ' .ffw-question',
            $root . ' .ffw-faq-list-question',
            $root . ' .ffw-accordion-tittle',
            $root . ' .ffw-accordion-heading',
            $root . ' .ffw-accordion-heading span',
            $root . ' .ffw-button span',
            $root . ' details',
            $root . ' details summary',
            $root . ' .ffw-collapse > span',
        )
    );
    $q_hover = implode(
        ',',
        array(
            $root . ' .ffw-button:hover',
            $root . ' .ffw-button:focus',
            $root . ' .ffw-button.ffw-active',
            $root . ' .ffw-button:hover span',
            $root . ' .ffw-button.ffw-active span',
            $root . ' details[open] summary',
            $root . ' .ffw-collapse > span.ffw-active',
        )
    );
    $a = implode(
        ',',
        array(
            $root . ' .ffw-classic-answer',
            $root . ' .ffw-content',
            $root . ' .ffw-faq-list-answer',
            $root . ' .ffw-trip-answer',
            $root . ' .ffw-basic-answer',
            $root . ' .ffw-accordion-body-content',
            $root . ' .ffw-classic-answer p',
            $root . ' .ffw-faq-list-answer p',
        )
    );
    $items = implode(
        ',',
        array(
            $root . ' .ffw-accordion-item',
            $root . ' .ffw-faq-list-item',
            $root . ' .ffw-collapse',
            $root . ' details',
        )
    );

    $css = '';

    if ( ! empty( $args['question_color'] ) ) {
        $css .= $q . '{color:' . $args['question_color'] . ' !important;}';
    }
    if ( ! empty( $args['question_bg_color'] ) ) {
        $css .= $q . '{background:' . $args['question_bg_color'] . ' !important;}';
    }
    if ( ! empty( $args['question_hover_color'] ) ) {
        $css .= $q_hover . '{color:' . $args['question_hover_color'] . ' !important;}';
    }
    if ( ! empty( $args['question_font_size'] ) ) {
        $css .= $q . '{font-size:' . absint( $args['question_font_size'] ) . 'px !important;}';
    }
    if ( ! empty( $args['answer_color'] ) ) {
        $css .= $a . '{color:' . $args['answer_color'] . ' !important;}';
    }
    if ( ! empty( $args['answer_bg_color'] ) ) {
        $css .= $a . '{background:' . $args['answer_bg_color'] . ' !important;}';
    }
    if ( ! empty( $args['answer_font_size'] ) ) {
        $css .= $a . '{font-size:' . absint( $args['answer_font_size'] ) . 'px !important;}';
    }
    if ( ! empty( $args['border_color'] ) ) {
        $css .= $items . '{border-color:' . $args['border_color'] . ' !important;border-style:solid;border-width:1px;}';
        $css .= $q . '{border-color:' . $args['border_color'] . ' !important;}';
        $css .= $a . '{border-color:' . $args['border_color'] . ' !important;}';
    }
    if ( ! empty( $args['border_radius'] ) ) {
        $css .= $items . '{border-radius:' . absint( $args['border_radius'] ) . 'px !important;overflow:hidden;}';
    }
    if ( ! empty( $args['item_spacing'] ) ) {
        $css .= $items . '{margin-bottom:' . absint( $args['item_spacing'] ) . 'px !important;}';
    }
    if ( ! empty( $args['custom_css'] ) ) {
        $css .= '#ffw-main-wrapper.' . $instance . ' #ffw-wrapper{' . $args['custom_css'] . '}';
    }

    return $css;
}

/**
 * Render Happy Custom FAQs written in a builder, not stored as CPT posts.
 *
 * @since 2.0.0
 * @param array $raw Builder settings.
 * @return string
 */
function ffw_render_custom_faqs( $raw = array() ) {
    $args = ffw_normalize_custom_faq_args( $raw );
    $faqs = ffw_normalize_custom_faq_items( $args['items'] );

    if ( empty( $faqs ) ) {
        if ( ffw_builder_is_editor_context() ) {
            return '<p class="ffw-builder-placeholder">' . esc_html__( 'Add questions and answers here. They stay on this page and are not saved to the FAQ library.', 'faq-for-woocommerce' ) . '</p>';
        }

        return '';
    }

    $layout = absint( $args['layout'] );
    if ( $layout < 1 ) {
        $options = get_option( 'ffw_general_settings', array() );
        $layout  = isset( $options['ffw_layout'] ) ? (int) $options['ffw_layout'] : 1;
    }

    ffw_enqueue_display_faq_assets( $layout );

    $wrap_class = array( 'ffw-custom-faqs' );
    $style_html = '';

    if ( ffw_custom_faq_has_style_overrides( $args ) ) {
        $instance     = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'ffw-custom-style-' ) : uniqid( 'ffw-custom-style-' );
        $wrap_class[] = $instance;
        $css          = ffw_get_custom_faq_override_css( $args, $instance );
        if ( '' !== $css ) {
            $style_html = '<style id="' . esc_attr( $instance ) . '-css">' . $css . '</style>';
        }
    }

    $html = ffw_get_template_by_faqs(
        $faqs,
        array(
            'layout'               => $layout,
            'display_mode'         => $args['display_mode'],
            'show_search'          => $args['show_search'],
            'enable_schema'        => $args['enable_schema'],
            'expand_first'         => $args['expand_first'],
            'shortcode_wrap_class' => $wrap_class,
            'display_schema_type'  => 'custom_page',
        )
    );

    if ( ! is_string( $html ) ) {
        return '';
    }

    return $style_html . $html;
}

/**
 * Collect a Divi 4 Custom FAQ child item for the parent render.
 *
 * @since 2.0.0
 * @param string $question Question.
 * @param string $answer   Answer HTML.
 * @return void
 */
function ffw_divi_collect_custom_faq_item( $question, $answer ) {
    if ( ! isset( $GLOBALS['ffw_divi_custom_faq_items'] ) || ! is_array( $GLOBALS['ffw_divi_custom_faq_items'] ) ) {
        $GLOBALS['ffw_divi_custom_faq_items'] = array();
    }

    $GLOBALS['ffw_divi_custom_faq_items'][] = array(
        'question' => $question,
        'answer'   => $answer,
    );
}

/**
 * Take and reset collected Divi 4 Custom FAQ items.
 *
 * @since 2.0.0
 * @return array
 */
function ffw_divi_take_custom_faq_items() {
    $items = isset( $GLOBALS['ffw_divi_custom_faq_items'] ) && is_array( $GLOBALS['ffw_divi_custom_faq_items'] )
        ? $GLOBALS['ffw_divi_custom_faq_items']
        : array();

    $GLOBALS['ffw_divi_custom_faq_items'] = array();

    return $items;
}

/**
 * Map Divi 5 innerBlocks to Custom FAQ items.
 *
 * @since 2.0.0
 * @param object|array $block Divi 5 block.
 * @return array
 */
function ffw_divi5_inner_blocks_to_custom_faq_items( $block ) {
    $inners = array();

    if ( is_object( $block ) && isset( $block->parsed_block['innerBlocks'] ) && is_array( $block->parsed_block['innerBlocks'] ) ) {
        $inners = $block->parsed_block['innerBlocks'];
    } elseif ( is_array( $block ) && isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
        $inners = $block['innerBlocks'];
    }

    $items = array();

    foreach ( $inners as $inner ) {
        $name = '';
        if ( isset( $inner['blockName'] ) ) {
            $name = $inner['blockName'];
        } elseif ( isset( $inner['name'] ) ) {
            $name = $inner['name'];
        }

        if ( $name && ! in_array( $name, array( 'happy-faq/divi-custom-faq-item', 'ffw_custom_faq_item' ), true ) ) {
            continue;
        }

        $attrs   = isset( $inner['attrs'] ) && is_array( $inner['attrs'] ) ? $inner['attrs'] : array();
        $items[] = array(
            'question' => ffw_divi5_attr_inner_value( $attrs, array( 'question', 'title' ), '' ),
            'answer'   => ffw_divi5_attr_inner_value( $attrs, array( 'answer', 'content' ), '' ),
        );
    }

    return $items;
}

/**
 * Read a Divi 5 innerContent value from attrs.
 *
 * @since 2.0.0
 * @param array $attrs Module attrs.
 * @param array $names Attribute names to try.
 * @param mixed $default Default.
 * @return mixed
 */
function ffw_divi5_attr_inner_value( $attrs, $names, $default = '' ) {
    foreach ( (array) $names as $name ) {
        if ( ! isset( $attrs[ $name ]['innerContent']['desktop']['value'] ) ) {
            continue;
        }

        $value = $attrs[ $name ]['innerContent']['desktop']['value'];
        if ( is_array( $value ) ) {
            if ( isset( $value['html'] ) ) {
                return $value['html'];
            }
            if ( isset( $value['text'] ) ) {
                return $value['text'];
            }
            if ( isset( $value['raw'] ) ) {
                return $value['raw'];
            }
        }

        return $value;
    }

    return $default;
}

/**
 * Enqueue layout CSS/JS so builder previews match the front.
 *
 * @since 1.8.18
 * @param int $layout Layout id.
 * @return void
 */
function ffw_enqueue_display_faq_assets( $layout = 0 ) {
    if ( class_exists( 'FAQ_Woocommerce_Public' ) ) {
        FAQ_Woocommerce_Public::instance()->ffw_public_styles();
        FAQ_Woocommerce_Public::instance()->ffw_public_scripts();
    }

    $layout = absint( $layout );
    if ( $layout < 1 ) {
        $options = get_option( 'ffw_general_settings', array() );
        $layout  = isset( $options['ffw_layout'] ) ? (int) $options['ffw_layout'] : 1;
    }

    wp_enqueue_style( 'ffw_public_styles' );
    wp_enqueue_style( 'ffw-display-faqs-style' );
    wp_enqueue_script( 'ffw_public_js' );
    wp_enqueue_script( 'ffw-inline-search' );

    if ( 4 === $layout ) {
        wp_enqueue_style( 'ffw_pop_styles' );
        wp_enqueue_script( 'ffw_pop_js' );
    } elseif ( 5 === $layout ) {
        wp_enqueue_style( 'ffw_basic_styles' );
        wp_enqueue_script( 'ffw_basic_js' );
    } elseif ( 3 === $layout ) {
        wp_enqueue_style( 'ffw_trip_styles' );
        wp_enqueue_script( 'ffw_classic_js' );
    } elseif ( 2 === $layout ) {
        wp_enqueue_style( 'ffw_whitish_styles' );
        wp_enqueue_script( 'ffw_classic_js' );
    } else {
        wp_enqueue_style( 'ffw_classic_styles' );
        wp_enqueue_script( 'ffw_classic_js' );
    }

    do_action( 'ffw_enqueue_templates_styles', $layout );
    do_action( 'ffw_enqueue_template_js', $layout );
}

/**
 * Inline search field HTML.
 *
 * @since 1.8.18
 * @return string
 */
if ( ! function_exists( 'ffw_get_inline_search_html' ) ) {
    function ffw_get_inline_search_html() {
        ob_start();
        $search_id = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'ffw-inline-search-' ) : uniqid( 'ffw-inline-search-' );
        ?>
        <div class="ffw-inline-search-wrap">
            <label class="screen-reader-text" for="<?php echo esc_attr( $search_id ); ?>"><?php esc_html_e( 'Search FAQs', 'faq-for-woocommerce' ); ?></label>
            <input type="search" id="<?php echo esc_attr( $search_id ); ?>" class="ffw-inline-search" placeholder="<?php esc_attr_e( 'Search FAQs…', 'faq-for-woocommerce' ); ?>" autocomplete="off" />
        </div>
        <?php
        return ob_get_clean();
    }
}

/**
 * Output inline search on shortcode/product FAQs when enabled.
 *
 * @since 1.8.18
 * @return void
 */
function ffw_display_inline_search_input() {
    if ( ffw_is_pro_activated() ) {
        $options = get_option( 'ffw_general_settings', array() );
        if ( ! empty( $options['enable_search_box'] ) ) {
            return;
        }
    }

    $options = get_option( 'ffw_general_settings', array() );
    if ( empty( $options['ffw_enable_inline_search'] ) || '1' !== (string) $options['ffw_enable_inline_search'] ) {
        return;
    }

    echo ffw_get_inline_search_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'ffw_search_input', 'ffw_display_inline_search_input', 5 );

/**
 * FAQ Header
 *
 * @return void
 */
function ffw_faq_header() {
    ?>
    <div class="ffw-faq-header">
        <?php
        if ( apply_filters( 'ffw_enable_search_input', true ) ) {
            do_action('ffw_search_input');
        }

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
        if ( ! post_type_exists( 'product' ) ) {
            return 0;
        }

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

		if ( empty( $product_ids ) ) {
            return 0;
        }

		return (int) reset($product_ids);
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
            'menu_position'      => 10,
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
        $faq_id = (int) $faq_id;
        $inline = ffw_get_inline_faq_answer( $faq_id );
        if ( null !== $inline ) {
            $content = apply_filters( 'the_content', $inline );
            echo wp_kses_post( $content );
            return;
        }

        if ( $faq_id <= 0 ) {
            return;
        }

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

    if ( is_array( $faq ) && ! empty( $faq['inline'] ) ) {
        return;
    }

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
    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

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

    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

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
		_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'faq-for-woocommerce' ), '<code>' . $template_name . '</code>' ), '1.0.0' );
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

            if (!empty($product_ids)) {
                $links = array();
                foreach ($product_ids as $pid) {
                    $title = get_the_title( $pid );
                    if ( function_exists( 'wc_get_product' ) ) {
                        $product = wc_get_product($pid);
                        if ($product) {
                            $title = $product->get_name();
                        }
                    }
                    $url = get_permalink($pid);
                    $links[] = "<a href='" . esc_url($url) . "'>" . esc_html( $title ) . " (#$pid)</a>";
                }
                echo implode(', ', $links);
            } else {
                echo '-';
            }
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

