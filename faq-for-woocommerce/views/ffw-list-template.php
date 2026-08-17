<div class="ffw-layout-container">
    <div class="<?php echo esc_attr( $ffw_wrapper_classes ); ?> ffw-list-wrapper" id="ffw-wrapper">
        <div class="ffw-faq-list">
            <?php
            if ( is_array( $faqs ) && ! empty( $faqs ) ) {
                $counter = 0;
                foreach ( $faqs as $faq ) {
                    do_action( 'ffw_before_display_faq_item', array( 'faqs' => $faqs, 'counter' => $counter ) );
                    ?>
                    <div class="ffw-faq-list-item ffw-accordion-item" <?php echo ffw_get_faq_item_html_attrs( (int) $faq['id'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                        <h3 class="ffw-faq-list-question ffw-question"><?php echo esc_html( $faq['question'] ); ?></h3>
                        <div class="ffw-faq-list-answer">
                            <?php
                            $faq_id = (int) $faq['id'];
                            ffw_show_content( $faq_id, $id );
                            ?>
                        </div>
                    </div>
                    <?php
                    do_action( 'ffw_after_display_faq_item', array( 'faqs' => $faqs, 'counter' => $counter ) );
                    $counter++;
                }
            }
            ?>
        </div>
    </div>
</div>
