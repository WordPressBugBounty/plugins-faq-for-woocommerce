<!-- footer-section-start -->
<footer class="ffw-dashboard-footer-wrapper">
    <div class="ffw-contianer">
        <div class="ffw-footer-inner">
        <div class="ffw-dashboard-logo-area">
            <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/Logo.png'); ?> " alt="">
            <?php
                echo sprintf('<h2 class="ffw-logo-text">%s</h2>', esc_html(ffw_get_settings_page_menu_title()) );
            ?>   
        </div>
            <p class="ffw-footer-contents"><?php esc_html_e("Elevate user experience with Happy WooCommerce FAQs: WordPress Product FAQ plugin. Build brand, enrich sells and make your customer happy.", "faq-for-woocommerce"); ?></p>
            <ul class="ffw-footer-menus">
                <li><a class="ffw-footer-menu" href="<?php echo esc_url(FFW_DOC_URL); ?>"><?php esc_html_e("Docs", "faq-for-woocommerce"); ?></a></li>
                <li><a class="ffw-footer-menu" href="<?php echo esc_url(FFW_SUPPORT_URL); ?>"><?php esc_html_e("Support", "faq-for-woocommerce"); ?></a></li>
                <li><a class="ffw-footer-menu" href="<?php echo esc_url(FFW_SITE_URL); ?>"><?php esc_html_e("Website", "faq-for-woocommerce"); ?></a></li>
            </ul>
            <div class="ffw-footer-icons">
                <ul class="ffw-Social-media-icons">
                    <li><a href="<?php echo esc_url(FFW_FACEBOOK_URL); ?>"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/Facebook.png'); ?>" alt=""></a></li>
                    <li><a href="<?php echo esc_url(FFW_VIDEOS_URL); ?>"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/YouTube.png'); ?>" alt=""></a></li>
                    <li><a href="<?php echo esc_url(FFW_LINKEDIN_URL); ?>"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/linkedin.png'); ?>" alt=""></a></li>
                </ul>
            </div>
        </div>
    </div>
    
</footer>