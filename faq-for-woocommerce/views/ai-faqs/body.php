<section class="ffw-dashboard-body-section-wrapper ffw-ai-wrapper">
    <?php if(isset($_GET['page']) && $_GET['page'] === 'ai-faqs'): ?>
            <hr class="wp-header-end">
    <?php endif; ?>
    
    <div class="ffw-contianer">
        <div class="ffw-ai-content ffw-left-side">
            <div class="ffw-ai-form-content" >
                <h3><?php esc_html_e('AI FAQs Generator', 'faq-for-woocommerce'); ?></h3>
                <p><?php esc_html_e('Create product faqs with AI and import them.', 'faq-for-woocommerce'); ?></p>
                <form class="ffw-ai-faqs-form" method="post">
                    <div class="ffw-form-group">
                        <label for="ffw_ai_select_products"><?php esc_html_e('Select Products *', 'faq-for-woocommerce'); ?></label>
                        <select
                            name="ffw_ai_select_products"
                            class="wc-product-search"
                            id="ffw_ai_select_products"
                            data-allow_clear="true"
                            required
                            data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'faq-for-woocommerce' ); ?>"
                            data-action="woocommerce_json_search_products_and_variations"
                        >
                        </select>
                    </div>
                    
                    <div class="ffw-form-group">
                        <label for="ffw_ai_keywords"><?php esc_html_e('Keywords *', 'faq-for-woocommerce'); ?></label>
                        <input type="text" name="ffw_ai_keywords" id="ffw_ai_keywords" required placeholder="shipping, delivery, payment, info">
                    </div>
                    
                    <div class="ffw-form-group">
                        <label for="ffw_ai_language"><?php esc_html_e('Language', 'faq-for-woocommerce'); ?></label>
                        <?php
                            $languages    = get_available_languages();
                            if ( ! is_multisite() && defined( 'WPLANG' ) && '' !== WPLANG && 'en_US' !== WPLANG && ! in_array( WPLANG, $languages, true ) ) {
                                $languages[] = WPLANG;
                            }

                            $locale = get_locale();
                            if ( ! in_array( $locale, $languages, true ) ) {
                                $locale = '';
                            }
                
                            wp_dropdown_languages(
                                array(
                                    'name'                        => 'ffw_ai_language',
                                    'id'                          => 'ffw_ai_language',
                                    'selected'                    => $locale,
                                    'languages'                   => $languages,
                                )
                            );
                        ?>
                    </div>

                    <button type="submit" name="ffw_ai_submit">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="24" height="24" viewBox="0 0 24 24">
                            <path d="M18.5,4.8l-.9-2.1c-.4-1-1.9-1-2.3,0l-.9,2.1c-.1.3-.4.5-.7.7l-2.1.9c-1,.4-1,1.9,0,2.3l2.1.9c.3.1.5.4.7.7l.9,2.1c.4,1,1.9,1,2.3,0l.9-2.1c.1-.3.4-.5.7-.7l2.1-.9c1-.4,1-1.9,0-2.3l-2.1-.9c-.3-.1-.5-.4-.7-.7ZM6.5,9.9l.6,1.4c0,.2.2.4.4.4l1.4.6c.7.3.7,1.3,0,1.6l-1.4.6c-.2,0-.4.2-.4.4l-.6,1.4c-.3.7-1.3.7-1.6,0l-.6-1.4c0-.2-.2-.4-.4-.4l-1.4-.6c-.7-.3-.7-1.3,0-1.6l1.4-.6c.2,0,.4-.2.4-.4l.6-1.4c.3-.7,1.3-.7,1.6,0ZM14,16.2l.5,1.2c0,.2.2.3.4.4l1.2.5c.1,0,.2.1.3.3,0,.1.1.2.1.4s0,.3-.1.4c0,.1-.2.2-.3.3l-1.2.5c-.2,0-.3.2-.4.4l-.5,1.2c0,.1-.1.2-.3.3-.1,0-.2.1-.4.1s-.3,0-.4-.1c-.1,0-.2-.2-.3-.3l-.5-1.2c0-.2-.2-.3-.4-.4l-1.2-.5c-.1,0-.2-.1-.3-.3,0-.1-.1-.2-.1-.4s0-.3.1-.4c0-.1.2-.2.3-.3l1.2-.5c.2,0,.3-.2.4-.4l.5-1.2c0-.1.1-.2.3-.3.1,0,.2-.1.4-.1s.3,0,.4.1c.1,0,.2.2.3.3Z"/>
                        </svg>
                        <span><?php esc_html_e('Generate FAQs', 'faq-for-woocommerce'); ?></span>
                    </button>
                </form>
            </div>

            <div class="ffw-ai-result-content">
                <div class="ffw-ai-result-content-header">
                    <a href="javascript:void()" class="ffw-ai-back-btn"><span class="dashicons dashicons-arrow-left-alt2"></span><span>Back</span></a>
                    <h3><?php esc_html_e('10 results showing...', 'faq-for-woocommerce'); ?></h3>
                </div>

                <div class="ffw-ai-result-action">
                    <label>
                        <input type="checkbox" class="ffw-ai-result-select-all-btn"/>
                        <span><?php esc_html_e('Select All', 'faq-for-woocommerce'); ?></span>
                    </label>

                    <span class="ffw-ai-selected-count"><?php esc_html_e('No Item selected', 'faq-for-woocommerce'); ?></span>
                </div>

                <form class="ffw-ai-result-form" method="post">
                    <div class="ffw-ai-result-fields"></div>

                    <div class="ffw-ai-result-form-footer">
                        <button type="submit" name="ffw_ai_insert">
                            <span class="dashicons dashicons-arrow-down-alt"></span>
                            <span><?php esc_html_e('Insert FAQs', 'faq-for-woocommerce'); ?></span>
                        </button>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=ffw&page=ai-faqs')); ?>" class="ffw-ai-result-cancel"><?php esc_html_e('Cancel', 'faq-for-woocommerce'); ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>