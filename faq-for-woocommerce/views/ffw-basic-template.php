<div class="ffw-layout-container">
    <div class="ffw-wrapper ffw-basic-wrapper " id="ffw-wrapper">
        <div class="ffw-accordion-container" >
            <?php
                if ( is_array($faqs) && ! empty($faqs) ) {
                    $counter = 0;
                    foreach ( $faqs as $faq ) {
                        do_action('ffw_before_display_faq_item', array('faqs' => $faqs, 'counter' => $counter));
                        $ffw_open_item = ( isset($ffw_display_all_answers) && '1' === $ffw_display_all_answers )
                            || ( ! empty( $ffw_expand_first ) && 0 === $counter );
                        $ffw_a11y_id = ffw_get_faq_a11y_id( $faq['id'] );
            ?>
            <div class="ffw-accordion-item ffw-accordion" <?php echo ffw_get_faq_item_html_attrs( (int) $faq['id'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <div class="ffw-accordion-heading" <?php echo ffw_get_faq_toggle_attrs( $ffw_a11y_id, $ffw_open_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                    <?php echo sprintf('<span class="ffw-button ffw-accordion-tittle">%s</span>', esc_html( $faq['question'] ) ); ?>
                    <?php echo ffw_get_toggle_icon( '<i class="fa-solid fa-angle-down ffw-rotate-icon" aria-hidden="true"><span class="dashicons dashicons-arrow-down-alt2"></span></i>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static icon markup. ?>
                </div>
                <div class="ffw-content ffw-basic-answer" <?php echo ffw_get_faq_panel_attrs( $ffw_a11y_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo $ffw_open_item ? 'style="display:block"' : 'style="display:none"';  ?>>
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