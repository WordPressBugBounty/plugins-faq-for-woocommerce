<?php
add_action('wp_footer', 'ffw_footer_content');
if( ! function_exists( "ffw_footer_content" ) ) {
    function ffw_footer_content() {
        // Only pages that rendered FAQs need these styles.
        if ( ! wp_style_is( 'ffw_public_styles', 'enqueued' ) && ! wp_style_is( 'ffw_public_styles', 'done' ) ) {
            return;
        }

        $options = FAQ_Woocommerce_Public::instance()->options;
        $options = is_array( $options ) ? $options : array();

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- values are sanitized in ffw_get_style_settings_css().
        echo '<style id="ffw-style-settings">' . ffw_get_style_settings_css( $options ) . '</style>';

        // Custom CSS comes last. Tags are stripped, not escaped: ">" and quotes are valid CSS.
        $custom_css = isset( $options['ffw_custom_css'] ) ? (string) $options['ffw_custom_css'] : '';
        echo '<style id="ffw-custom-css">' . ffw_prepare_custom_css( $custom_css ) . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}

add_filter( 'woocommerce_product_tabs', 'ffw_new_product_tab' );
function ffw_new_product_tab( $tabs ) {
    if ( ! ffw_is_woocommerce_active() ) {
        return $tabs;
    }
    //get display location value
    $options = FAQ_Woocommerce_Public::instance()->options;
    $ffw_display_location = isset( $options['ffw_display_location'] ) ? $options['ffw_display_location'] : "product_tab";
    $tab_label = isset($options['ffw_tab_label']) && !empty($options['ffw_tab_label']) ? $options['ffw_tab_label'] : esc_html__('FAQs', 'faq-for-woocommerce');

    //skip if product page is disabled.
    // if( isset($options['enable_product_pages_faqs']) && $options['enable_product_pages_faqs'] !== 'on' ) {
    //     return $tabs;
    // }

	global $post;
	$id = $post->ID;

    $faqs = ffw_get_product_faq_ids($id);

    if( isset($options['ffw_faq_counter_in_front']) && 1 === (int) $options['ffw_faq_counter_in_front'] ) {
        $counter =  (string) ffw_get_faqs_number_for_product($id);
        $tab_label = $tab_label . ' (' . $counter . ')';
    }

    $priority = ( isset( $options['ffw_tab_priority'] ) ) ? $options['ffw_tab_priority'] : '50';

	if ( ! empty($faqs) ) {
        if( empty($ffw_display_location) || $ffw_display_location === "product_tab" ) {
            // Adds the new tab
            $tabs['ffw_faqs_tab'] = array(
                'title'    => esc_html( $tab_label ),
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
    if ( ! ffw_is_woocommerce_active() ) {
        return;
    }

	global $product;
    $id = ( is_object( $product ) && method_exists( $product, 'get_id' ) ) ? $product->get_id() : get_the_ID();

	ffw_get_template( $id, array(
        'source'     => 'product',
        'product_id' => $id,
    ) );
}

/**
 * Display FAQs in Archive pages.
 *
 * @return void
 */
if(!function_exists('ffw_display_faqs_in_archive_pages')) {
    function ffw_display_faqs_in_archive_pages() {

        $is_archive = false;
        if( function_exists( 'is_product_category' ) && is_product_category() ) {
            $is_archive = true;
        }elseif( function_exists( 'is_product_tag' ) && is_product_tag() ) {
            $is_archive = true;
        }

        //skip, if current page is not product archive page.
        if( ! $is_archive ) {
            return;
        }

        if ( ! ffw_are_archive_faqs_enabled() ) {
            return;
        }

        $term = get_queried_object();

        //skip, when term not exists.
        if(empty($term) || !property_exists($term, 'term_id')) {
            return;
        }

        ffw_get_template(
            $term->term_id,
            array(
                'source' => 'archive',
            )
        );
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
        $options = FAQ_Woocommerce_Public::instance()->options;

        //skip, when shop page is not enabled.
        if(!isset($options['enable_shop_page_faqs'])) {
            return;
        }

        //get shop faqs.
        $faq_ids = get_option( 'ffw_shop_page_faqs' );

        $faqs = ffw_get_faq_list_by_faq_ids($faq_ids);

        echo ffw_get_template_by_faqs($faqs); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- templates escape their own output.
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
        $options = FAQ_Woocommerce_Public::instance()->options;

        //skip, when cart page is not enabled.
        if(!isset($options['enable_cart_page_faqs'])) {
            return;
        }

        //get cart faqs.
        $faq_ids = get_option( 'ffw_cart_page_faqs' );

        $faqs = ffw_get_faq_list_by_faq_ids($faq_ids);

        echo ffw_get_template_by_faqs($faqs); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- templates escape their own output.
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
        $options = FAQ_Woocommerce_Public::instance()->options;
    
        //skip, when checkout page is not enabled.
        if(!isset($options['enable_checkout_page_faqs'])) {
            return;
        }
    
        //get checkout faqs.
        $faq_ids = get_option( 'ffw_checkout_page_faqs' );
    
        $faqs = ffw_get_faq_list_by_faq_ids($faq_ids);
    
        echo ffw_get_template_by_faqs($faqs); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- templates escape their own output.
    }
}

add_action('ffw_before_faq_start', 'ffw_before_faq_start');
function ffw_before_faq_start() {
    $options = FAQ_Woocommerce_Public::instance()->options;

    if( isset($options['ffw_before_faq']) && '' !== trim( (string) $options['ffw_before_faq'] ) ) {
        // Written in the rich text editor: allow post HTML.
        echo '<div class="ffw-before-faq">' . wp_kses_post( wpautop( (string) $options['ffw_before_faq'] ) ) . '</div>';
    }

}

add_action('ffw_expand_collapse_all', 'ffw_expand_collapse_all_action_cb');
function ffw_expand_collapse_all_action_cb() {
    $options = FAQ_Woocommerce_Public::instance()->options;
    $options = ! empty( $options ) ? $options : [];
    $ffw_expand_collapse_all = isset( $options['ffw_expand_collapse_all'] ) ? $options['ffw_expand_collapse_all'] : "2";
    $ffw_expand_collapse_label = isset( $options['ffw_expand_collapse_label'] ) ? $options['ffw_expand_collapse_label'] : esc_html__( "Expand/Collapse All", 'faq-for-woocommerce' );


    if( "1" === $ffw_expand_collapse_all && !empty($ffw_expand_collapse_label) ) {
        ?>
        <button type="button" class="ffw-btn-expand-collapse-all" aria-expanded="<?php echo isset( $options['ffw_display_all_faq_answers'] ) && '1' === $options['ffw_display_all_faq_answers'] ? 'true' : 'false'; ?>"><?php echo esc_html($ffw_expand_collapse_label); ?></button>
        <?php
    }

}

add_action('ffw_after_faq_end', 'ffw_after_faq_end');
function ffw_after_faq_end() {
    $options = FAQ_Woocommerce_Public::instance()->options;

    if( isset($options['ffw_after_faq']) && '' !== trim( (string) $options['ffw_after_faq'] ) ) {
        echo '<div class="ffw-after-faq">' . wp_kses_post( wpautop( (string) $options['ffw_after_faq'] ) ) . '</div>';
    }

}

