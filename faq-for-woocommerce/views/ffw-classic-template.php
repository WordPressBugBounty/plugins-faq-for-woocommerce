<div class="ffw-layout-container">
    <div class="<?php echo esc_attr($ffw_wrapper_classes); ?>" id="ffw-wrapper">
        <div class="ffw-accordion">
            <?php
            if ( is_array($faqs) && ! empty($faqs) ) {
                $counter = 0;

                foreach ( $faqs as $faq ) {
                    do_action('ffw_before_display_faq_item', array('faqs' => $faqs, 'counter' => $counter));
                    $ffw_open_item = ( isset($ffw_display_all_answers) && '1' === $ffw_display_all_answers )
                        || ( ! empty( $ffw_expand_first ) && 0 === $counter );
			    ?>
                <div class="ffw-accordion-item" <?php echo ffw_get_faq_item_html_attrs( (int) $faq['id'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                    <button class="ffw-button <?php echo $ffw_open_item ? 'ffw-active' : '';  ?>">
                        <?php echo sprintf('<span class="ffw-question">%s</span>', esc_html__($faq['question'], 'faq-for-woocommerce') ); ?>
                        <span class="ffw-classic-icon" aria-hidden="true"></span>
                    </button>
                    <div class="ffw-classic-answer" <?php echo $ffw_open_item ? 'style="display:block"' : 'style="display:none"';  ?>>
                        <?php
                        $post_id = get_the_ID();
                        $faq_id = (int) $faq['id'];
                        ffw_show_content($faq_id, $id);

                        //faq comment
                        ffw_comments($post_id, $faq);
                        ?>
                    </div>
                </div>
                <?php
                do_action('ffw_after_display_faq_item', array('faqs' => $faqs, 'counter' => $counter));
                $counter++;
                }
            }
            ?>
        </div>
    </div>
</div>