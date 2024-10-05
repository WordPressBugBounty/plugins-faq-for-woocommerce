<div class="ffw-layout-section-wrapper ffw-template-wrapper">
    <?php
        $layout = 1;
        $options = FAQ_Woocommerce_Settings::instance()->options;
        if( isset($options['ffw_layout']) ) {
            $layout = $options['ffw_layout'];
        }

        $free_templates = ['Classic', 'Whitish', 'Trip', 'Pop', 'Basic'];
        $templates = apply_filters("ffw_filter_template_names", $free_templates);
    ?>
    <div class="ffw-contianer">
        <?php
            if( isset($templates) && !empty($templates) ) {
                $counter = 1;
                foreach($templates as $template_name) {

                    $is_free_template = true;

                    if(! in_array($template_name, $free_templates)){
                        $is_free_template = false;
                    }

                    ?>
                    <div class="ffw-item-wrapper <?php echo $layout == $counter ? 'ffw-template-active' : ''; ?>">
                        <div class="ffw-item-inner">

                            <?php if (! $is_free_template && !ffw_is_pro_activated() ): ?>
                                <div class="ffw-get-pro-badge">
                                    <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/crown.png'); ?>" alt="PRO Badge">
                                    <span><?php esc_html_e('PRO', 'faq-for-woocommerce'); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="ffw-layout-img">
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/template-' . esc_attr($template_name) . '.png'); ?>" alt="<?php echo esc_html($template_name); ?>">
                            </div>
                            <div class="ffw-hover-buttons">
                                <a class="ffw-template-preview-button"><span class="dashicons dashicons-fullscreen-alt"></span><span><?php esc_html_e('Preview', 'faq-for-woocommerce'); ?></span></a>

                                <?php if (! $is_free_template && !ffw_is_pro_activated() ): ?>
                                    <a class="ffw-hover-active-button" href="<?php echo esc_url(FFW_PRO_URL); ?>">
                                        <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/go-pro-white.png'); ?>" alt="<?php echo esc_html($template_name); ?>">
                                        <span><?php esc_html_e('Upgrade', 'faq-for-woocommerce'); ?></span>
                                    </a>
                                <?php endif; ?>

                                <?php if ( $is_free_template || ffw_is_pro_activated() ): ?>
                                    <a class="ffw-hover-active-button ffw-template-active-button" data-templte_id=<?php echo esc_attr($counter); ?>><span class="dashicons dashicons-saved"></span><span><?php esc_html_e('Active', 'faq-for-woocommerce'); ?></span></a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="ffw-layout-img-heading">
                            <?php echo sprintf('<h2>%s Template</h2>', esc_html($template_name)); ?>
                        </div>

                    </div>
                    <?php
                    $counter++;
                }
            }
        ?>

    </div>

    <!-- ffw-template-popup -->
    <div class="ffw-template-popup-wrapper">
        <div class="ffw-template-popup-inner">
            <div class="ffw-layout-name">
                <h2 class="ffw-title"><?php esc_html_e('Template', 'faq-for-woocommerce'); ?></h2>
                <span class="dashicons dashicons-no-alt ffw-template-popup-close-button"></span>
            </div>
            <div class="ffw-temp-img">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/template-Whitish.png'); ?>" alt="">
            </div>
        </div>
    </div>
</div>