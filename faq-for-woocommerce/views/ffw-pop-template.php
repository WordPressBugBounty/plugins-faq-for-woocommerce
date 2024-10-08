<div class="ffw-layout-container">
    <div class="<?php echo esc_attr($ffw_wrapper_classes); ?>" id="ffw-wrapper">
        <div class="ffw-pop-container">
            <?php
            if ( is_array($faqs) && ! empty($faqs) ) {
                $counter = 0;

                foreach ( $faqs as $faq ) {
                    do_action('ffw_before_display_faq_item', array('faqs' => $faqs, 'counter' => $counter));
                    ?>
                    <div class="ffw-collapse ffw-accordion-item">
                        <?php echo sprintf('<span class="ffw-button">%s</span>', esc_html__($faq['question'], 'faq-for-woocommerce') ); ?>
                        <div class="ffw-content ffw-classic-answer" <?php echo isset($ffw_display_all_answers) && '1' === $ffw_display_all_answers ? 'style="display:block"' : '';  ?>>
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
