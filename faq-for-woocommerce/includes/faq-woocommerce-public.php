<?php
add_action('wp_footer', 'ffw_footer_content');
if( ! function_exists( "ffw_footer_content" ) ) {
    function ffw_footer_content() {
        $options = get_option( 'ffw_general_settings' );
        $question_text_color = isset($options['ffw_question_text_color']) && !empty($options['ffw_question_text_color']) ? $options['ffw_question_text_color'] : '';
        $question_bg_color = isset($options['ffw_question_bg_color']) && !empty($options['ffw_question_bg_color']) ? $options['ffw_question_bg_color'] : '';
        $question_bg_secondary_color = isset($options['ffw_question_bg_secondary_color']) && !empty($options['ffw_question_bg_secondary_color']) ? $options['ffw_question_bg_secondary_color'] : '';
        $question_font_size = isset($options['ffw_question_font_size']) && !empty($options['ffw_question_font_size']) ? $options['ffw_question_font_size'] . 'px' : '';
        $question_border_color = isset($options['ffw_question_border_color']) && !empty($options['ffw_question_border_color']) ? $options['ffw_question_border_color'] : '';

        //answer values
        $answer_bg_color = isset($options['ffw_answer_bg_color']) && !empty($options['ffw_answer_bg_color']) ? $options['ffw_answer_bg_color'] : '';
        $answer_text_color = isset($options['ffw_answer_text_color']) && !empty($options['ffw_answer_text_color']) ? $options['ffw_answer_text_color'] : '';
        $answer_border_color = isset($options['ffw_answer_border_color']) && !empty($options['ffw_answer_border_color']) ? $options['ffw_answer_border_color'] : '';

        ?>
        <style>
            #ffw-wrapper .ffw-accordion .ffw-accordion-item .ffw-button,
            #ffw-wrapper.ffw-basic-wrapper .ffw-accordion-item .ffw-button {
                background: <?php echo esc_html($question_bg_color); ?> !important;
                color: <?php echo esc_html($question_text_color); ?> !important;
                font-size: <?php echo esc_html($question_font_size); ?> !important;
            }
            #ffw-wrapper .ffw-accordion .ffw-accordion-item .ffw-button {
                border: 1px solid <?php echo esc_html($question_border_color); ?> !important;
            }
            #ffw-wrapper.ffw-classic-layout .ffw-accordion .ffw-accordion-item .ffw-button {
                border-bottom: none !important;
            }
            #ffw-wrapper.ffw-classic-layout .ffw-accordion .ffw-accordion-item .ffw-button span {
                color: <?php echo esc_html($question_text_color); ?> !important;
            }
            #ffw-wrapper.ffw-classic-layout .ffw-accordion .ffw-accordion-item:last-child .ffw-button,
            #ffw-wrapper.ffw-basic-wrapper .ffw-accordion-item .ffw-accordion-heading {
                border-bottom: 1px solid <?php echo esc_html($question_border_color); ?> !important;
            }
            #ffw-wrapper .ffw-accordion .ffw-accordion-item .ffw-classic-answer {
                background: <?php echo esc_html($answer_bg_color); ?> !important;
                border: 1px solid <?php echo esc_html($answer_border_color); ?> !important;
                color: <?php echo esc_html($answer_text_color); ?> !important;
            }

            #ffw-wrapper.ffw-basic-wrapper .ffw-accordion-item .ffw-basic-answer {
                background: <?php echo esc_html($answer_bg_color); ?> !important;
                color: <?php echo esc_html($answer_text_color); ?> !important;
                border-bottom: 1px solid <?php echo esc_html($answer_border_color); ?> !important;
            }

            /* Glow Template */
            #ffw-wrapper .ffw-accordion-container .ffw-accordion-border .ffw-accordion .ffw-accordion-heading ,
            #ffw-wrraper .ffw-accordion-container .ffw-accordion-outline-border .ffw-accordion .ffw-accordion-heading {
                background: <?php echo esc_html($question_bg_color); ?> !important;
                color: <?php echo esc_html($question_text_color); ?> !important;
                font-size: <?php echo esc_html($question_font_size); ?> !important;
            }
            #ffw-wrapper .ffw-accordion-container .ffw-accordion-border .ffw-accordion .ffw-accordion-heading {
                border: 1px solid <?php echo esc_html($question_border_color); ?> !important;
            }
            #ffw-wrapper .ffw-accordion-container .ffw-accordion-border .ffw-accordion .ffw-accordion-heading  {
                border-bottom: none !important;
            }
            #ffw-wrapper .ffw-accordion-container .ffw-accordion-border .ffw-accordion .ffw-accordion-heading span {
                color: <?php echo esc_html($question_text_color); ?> !important;
            }
            #ffw-wrapper .ffw-accordion-container .ffw-accordion-border .ffw-accordion .ffw-accordion-body-content {
                background: <?php echo esc_html($answer_bg_color); ?> !important;
                border: 1px solid <?php echo esc_html($answer_border_color); ?> !important;
                color: <?php echo esc_html($answer_text_color); ?> !important;
            }

            /*  */
            #ffw-wrapper .ffw-accordion-container .ffw-accordion-outline-border .ffw-accordion .ffw-accordion-heading {
                background: <?php echo esc_html($question_bg_color); ?> !important;
                color: <?php echo esc_html($question_text_color); ?> !important;
                font-size: <?php echo esc_html($question_font_size); ?> !important;
            }


            /* Trip template css */
            #ffw-main-wrapper details {
                background: <?php echo esc_html($question_bg_color); ?> !important;
                color: <?php echo esc_html($question_text_color); ?> !important;
                border: 1px solid <?php echo esc_html($question_border_color); ?> !important;
            }
            #ffw-main-wrapper details summary {
                font-size: <?php echo esc_html($question_font_size); ?> !important;
            }
            #ffw-main-wrapper details .ffw-trip-answer {
                background: <?php echo esc_html($answer_bg_color); ?> !important;
                color: <?php echo esc_html($answer_text_color); ?> !important;
                padding: 10px 0;

                <?php
                    if( ! empty($answer_border_color) ) {
                        echo "border: 1px solid " . esc_html($answer_border_color) . " !important;";
                    }else {
                        echo "border: none;";
                    }
                ?>
            }

            #ffw-main-wrapper details .ffw-trip-answer iframe {
                display: block;
                position: relative;
                z-index: 9999;
            }

            /* Pop template css */
            #ffw-main-wrapper .ffw-collapse:nth-child(even) span{
                background: <?php echo esc_html($question_bg_color); ?> !important;
            }
            #ffw-main-wrapper .ffw-collapse:nth-child(odd) span{
                background: <?php echo esc_html($question_bg_secondary_color); ?> !important;
            }
            #ffw-main-wrapper .ffw-collapse span {
                color: <?php echo esc_html($question_text_color); ?> !important;
            }
            #ffw-main-wrapper .ffw-collapse .ffw-content {
                background: <?php echo esc_html($answer_bg_color); ?> !important;
                color: <?php echo esc_html($answer_text_color); ?> !important;
                border: 1px solid <?php echo esc_html($answer_border_color); ?> !important;
            }

        </style>
        <?php

        //print ffw footer custom css
        if( isset($options['ffw_custom_css']) ) {
            echo "<style>". esc_html($options['ffw_custom_css']) ."</style>";
        }
    }
}

add_filter( 'woocommerce_product_tabs', 'ffw_new_product_tab' );
function ffw_new_product_tab( $tabs ) {
	global $post;
	$id = $post->ID;

    $faqs = ffw_get_product_faqs($id);

    // Get registered option
    $options = get_option( 'ffw_general_settings' );
    $tab_label = isset($options['ffw_tab_label']) && !empty($options['ffw_tab_label']) ? $options['ffw_tab_label'] : esc_html__('FAQs', 'faq-for-woocommerce');

    if( isset($options['ffw_faq_counter_in_front']) && 1 === (int) $options['ffw_faq_counter_in_front'] ) {
        $counter =  (string) ffw_get_faqs_number_for_product($id);
        $tab_label = $tab_label . ' (' . $counter . ')';
    }

    $priority = ( isset( $options['ffw_tab_priority'] ) ) ? $options['ffw_tab_priority'] : '50';

	if ( ! empty($faqs) ) {
        //get display location value
        $options = get_option( 'ffw_general_settings' );
		$options = ! empty( $options ) ? $options : [];
		$ffw_display_location = isset( $options['ffw_display_location'] ) ? $options['ffw_display_location'] : "product_tab";

        if( empty($ffw_display_location) || $ffw_display_location === "product_tab" ) {
            // Adds the new tab
            $tabs['ffw_faqs_tab'] = array(
                'title'    => esc_html__( $tab_label, 'faq-for-woocommerce' ),
                'priority' => apply_filters('ffw_tab_priority', $priority),
                'callback' => 'ffw_display_faqs_in_product_pages',
            );
        }

	}

	return $tabs;

}

/**
 * Display FAQs in Product pages.
 *
 * @return void
 */
function ffw_display_faqs_in_product_pages() {
	global $product;
	$id = $product->get_id();

	$content = ffw_get_template($id, false);

    echo $content;
}

/**
 * Display FAQs in Archive pages.
 *
 * @return void
 */
if(!function_exists('ffw_display_faqs_in_archive_pages')) {
    function ffw_display_faqs_in_archive_pages() {

        $is_archive = false;
        if( is_product_category() ) {
            $is_archive = true;
        }elseif( is_product_tag() ) {
            $is_archive = true;
        }

        //skip, if current page is not product archive page.
        if( ! $is_archive ) {
            return;
        }

        //get option.
        $options = get_option( 'ffw_general_settings' );

        //skip, when archive pages is not enabled.
        if(!isset($options['enable_archive_pages_faqs'])) {
            return;
        }

        $term = get_queried_object();

        //skip, when term not exists.
        if(empty($term) || !property_exists($term, 'term_id')) {
            return;
        }

        $content = ffw_get_template($term->term_id, false);

        echo $content;
    }
}

/**
 * Display FAQs in Shop page.
 *
 * @return void
 */
if(!function_exists('ffw_display_faqs_in_shop_page')) {
    function ffw_display_faqs_in_shop_page() {
        //get option.
        $options = get_option( 'ffw_general_settings' );

        //skip, when shop page is not enabled.
        if(!isset($options['enable_shop_page_faqs'])) {
            return;
        }

        //get shop faqs.
        $faq_ids = get_option( 'ffw_shop_page_faqs' );

        $faqs = ffw_get_faq_list_by_faq_ids($faq_ids);

        echo ffw_get_template_by_faqs($faqs);
    }
}

/**
 * Display FAQs in Cart page.
 *
 * @return void
 */
if(!function_exists('ffw_display_faqs_in_cart_page')) {
    function ffw_display_faqs_in_cart_page() {
        //get option.
        $options = get_option( 'ffw_general_settings' );

        //skip, when cart page is not enabled.
        if(!isset($options['enable_cart_page_faqs'])) {
            return;
        }

        //get cart faqs.
        $faq_ids = get_option( 'ffw_cart_page_faqs' );

        $faqs = ffw_get_faq_list_by_faq_ids($faq_ids);

        echo ffw_get_template_by_faqs($faqs);
    }
}

/**
 * Display FAQs in Checkout page.
 *
 * @return void
 */
if(!function_exists('ffw_display_faqs_in_checkout_page')) {
    function ffw_display_faqs_in_checkout_page() {
    
        //get option.
        $options = get_option( 'ffw_general_settings' );
    
        //skip, when checkout page is not enabled.
        if(!isset($options['enable_checkout_page_faqs'])) {
            return;
        }
    
        //get checkout faqs.
        $faq_ids = get_option( 'ffw_checkout_page_faqs' );
    
        $faqs = ffw_get_faq_list_by_faq_ids($faq_ids);
    
        echo ffw_get_template_by_faqs($faqs);
    }
}

add_action('ffw_before_faq_start', 'ffw_before_faq_start');
function ffw_before_faq_start() {
    $options = get_option( 'ffw_general_settings' );

    if( isset($options['ffw_before_faq']) ) {
        echo esc_html($options['ffw_before_faq']);
    }

}

add_action('ffw_expand_collapse_all', 'ffw_expand_collapse_all_action_cb');
function ffw_expand_collapse_all_action_cb() {
    $options = get_option( 'ffw_general_settings' );
    $options = ! empty( $options ) ? $options : [];
    $ffw_expand_collapse_all = isset( $options['ffw_expand_collapse_all'] ) ? $options['ffw_expand_collapse_all'] : "2";
    $ffw_expand_collapse_label = isset( $options['ffw_expand_collapse_label'] ) ? $options['ffw_expand_collapse_label'] : esc_html__( "Expand/Collapse All", 'faq-for-woocommerce' );


    if( "1" === $ffw_expand_collapse_all && !empty($ffw_expand_collapse_label) ) {
        ?>
        <button class="ffw-btn-expand-collapse-all"><?php echo esc_html($ffw_expand_collapse_label); ?></button>
        <?php
    }

}

add_action('ffw_after_faq_end', 'ffw_after_faq_end');
function ffw_after_faq_end() {
    $options = get_option( 'ffw_general_settings' );

    if( isset($options['ffw_after_faq']) ) {
        esc_html_e( $options['ffw_after_faq'], 'faq-for-woocommerce' );
    }

}

