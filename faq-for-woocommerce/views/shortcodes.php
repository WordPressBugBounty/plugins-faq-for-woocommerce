<div class="ffw-admin-wrapper ffw-shortcodes-wrapper">
    <div class="ffw-contianer">
        <div class="ffw-shortcode-tab-wrapper">
            <h3><?php esc_html_e("FAQs Shortcodes", "faq-for-woocommerce"); ?></h3>
            <p><?php esc_html_e("With shortcodes, Show FAQs on product pages, category pages, or any custom page to enhance customer experience.", "faq-for-woocommerce"); ?></p>
            
            <div class="ffw-shortcode-items">
                <?php 
                $shortcodes = [
                    [
                        'code' => '[ffw_template dynamic_post=true]',
                        'title' => esc_html__('Display FAQs in Product Page', 'faq-for-woocommerce'),
                        'description' => esc_html__('You can display all FAQs in product page with the default template.', 'faq-for-woocommerce'),
                    ],
                    [
                        'code' => '[ffw_template id=20]',
                        'title' => esc_html__('Display FAQs in Others Page.', 'faq-for-woocommerce'),
                        'description' => esc_html__('Display FAQs anywhere on your site. Simply use the shortcode and assign a product ID to show FAQs for that product. Works with any page or template. Example, here `id=20` is product id.', 'faq-for-woocommerce'),
                    ],
                    [
                        'code' => '[ffw_template cat_ids="32, 33"]',
                        'title' => esc_html__('Display FAQs by FAQs Categories', 'faq-for-woocommerce'),
                        'description' => esc_html__('You can display FAQs for specific FAQ categories. If the cat_ids exist, then the product id will be ignored. Here 32 & 33 are FAQ categories id. Please use the comma separator while inputting the data.', 'faq-for-woocommerce'),
                    ],
                ];
                
                foreach ( $shortcodes as $shortcode ) : ?>
                    <div class="ffw-shortcode-box">
                        <div class="ffw-shortcode-header-and-shortcode-box">
                            <div class="ffw-shortcode-info">
                                <h4><?php echo esc_html( $shortcode['title'] ); ?></h4>
                                <p><?php echo esc_html( $shortcode['description'] ); ?></p>
                            </div>
        
                            <div class="ffw-shortcode-header-box">
                                <p class="ffw-shortcode"><?php echo esc_html( $shortcode['code'] ); ?></p>
        
                                <div class="ffw-shortcode-icons-box">
        
                                    <a class="ffw-shortcode-copy-icon-box">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                            viewBox="0 0 408 480" class="ffw-shortcode-copy-icon">
                                            <path fill="#31373D"
                                                d="M299 5v43H43v299H0V48q0-18 12.5-30.5T43 5h256zm64 86q17 0 29.5 12.5T405 133v299q0 18-12.5 30.5T363 475H128q-18 0-30.5-12.5T85 432V133q0-17 12.5-29.5T128 91h235zm0 341V133H128v299h235z"/>
                                        </svg>
                                        <span class="ffw-tooltip"><?php echo esc_html__('Click To Copy', 'materials-for-woocommerce')?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>