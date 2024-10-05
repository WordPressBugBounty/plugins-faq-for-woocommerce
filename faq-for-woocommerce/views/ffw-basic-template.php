<div class="ffw-layout-container">
    <div class="ffw-wrapper ffw-basic-wrapper " id="ffw-wrapper">
        <div class="ffw-accordion-container" >
            <?php
                if ( is_array($faqs) && ! empty($faqs) ) {
                    $counter = 0;
                    foreach ( $faqs as $faq ) {
                        do_action('ffw_before_display_faq_item', array('faqs' => $faqs, 'counter' => $counter));
            ?>
            <div class="ffw-accordion-item ffw-accordion">
                <div class="ffw-accordion-heading">
                    <?php echo sprintf('<span class="ffw-button ffw-accordion-tittle">%s</span>', esc_html__($faq['question'], 'faq-for-woocommerce') ); ?>
                    <i class="fa-solid fa-angle-down ffw-rotate-icon"><span class="dashicons dashicons-arrow-down-alt2"></span></i>
                </div>
                <div class="ffw-content ffw-basic-answer" <?php echo isset($ffw_display_all_answers) && '1' === $ffw_display_all_answers ? 'style="display:block"' : 'style="display:none"';  ?>>
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