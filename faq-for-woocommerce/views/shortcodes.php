<div class="ffw-admin-wrapper ffw-shortcodes-wrapper">
    <div class="ffw-contianer">
        <div class="ffw-shortcode-tab-wrapper">
            <h3><?php esc_html_e("FAQs Shortcodes", "faq-for-woocommerce"); ?></h3>
            <p><?php esc_html_e("Place FAQs on any page, or on WooCommerce products, with the same shortcode library. Insert Happy Prebuilt FAQs for library FAQs, or Happy Custom FAQs (Gutenberg, Elementor, or Divi) to write questions on the page.", "faq-for-woocommerce"); ?></p>
            
            <div class="ffw-shortcode-items">
                <?php 
                $shortcodes = [
                    [
                        'code' => '[ffw_template]',
                        'title' => esc_html__('Display all FAQs', 'faq-for-woocommerce'),
                        'description' => esc_html__('Shows every published FAQ using the layout from Settings. Paste this on any page, or select an FAQs page in Settings to add it automatically.', 'faq-for-woocommerce'),
                    ],
                    [
                        'code' => '[ffw_template faq_cat="12"]',
                        'title' => esc_html__('Display FAQs by FAQ category', 'faq-for-woocommerce'),
                        'description' => esc_html__('Show FAQs from one or more FAQ categories. Copy the category shortcode from the FAQ Categories screen. Example: 12 is a FAQ category ID.', 'faq-for-woocommerce'),
                    ],
                    [
                        'code' => '[ffw_template filter="true"]',
                        'title' => esc_html__('Display FAQs with a category filter', 'faq-for-woocommerce'),
                        'description' => esc_html__('Adds buttons above the FAQs so visitors can show one FAQ category at a time. Works with any other attribute, for example faq_ids or template. Needs FAQs from at least two categories.', 'faq-for-woocommerce'),
                    ],
                    [
                        'code' => '[ffw_template per_page="5"]',
                        'title' => esc_html__('Show 5 FAQs, then a "Show more" button', 'faq-for-woocommerce'),
                        'description' => esc_html__('Keeps long lists short. Search, the category filter and FAQ schema still include every FAQ. Use limit="5" instead to show only 5 FAQs in total.', 'faq-for-woocommerce'),
                    ],
                    [
                        'code' => '[ffw_template faq_ids="12,15"]',
                        'title' => esc_html__('Display selected FAQs', 'faq-for-woocommerce'),
                        'description' => esc_html__('Show specific FAQs by ID. Use a comma-separated list. Example: 12 and 15 are FAQ post IDs.', 'faq-for-woocommerce'),
                    ],
                    [
                        'code' => '[ffw_template dynamic_post=true]',
                        'title' => esc_html__('Display FAQs on the current product', 'faq-for-woocommerce'),
                        'description' => esc_html__('Shows FAQs assigned to the product being viewed. Use this on a product template. WooCommerce must be active.', 'faq-for-woocommerce'),
                    ],
                    [
                        'code' => '[ffw_template id=20]',
                        'title' => esc_html__('Display FAQs for a product ID', 'faq-for-woocommerce'),
                        'description' => esc_html__('Show FAQs assigned to a specific product anywhere on the site. Example: id=20 is the product ID.', 'faq-for-woocommerce'),
                    ],
                    [
                        'code' => '[ffw_template cat_ids="32,33"]',
                        'title' => esc_html__('Display FAQs by FAQ categories (legacy)', 'faq-for-woocommerce'),
                        'description' => esc_html__('Same as faq_cat. 32 and 33 are FAQ category IDs. Prefer faq_cat for new pages.', 'faq-for-woocommerce'),
                    ],
                ];
                $shortcodes = apply_filters( 'ffw_admin_shortcodes_list', $shortcodes );
                
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
        
                                    <a class="ffw-shortcode-copy-icon-box" data-ffw-shortcode="<?php echo esc_attr( $shortcode['code'] ); ?>">
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