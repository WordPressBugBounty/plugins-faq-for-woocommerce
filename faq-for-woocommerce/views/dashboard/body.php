<!-- xplainer-body-section-start -->
<section class="ffw-dashboard-body-section-wrapper">
    <?php if(isset($_GET['page']) && $_GET['page'] === 'ffw-dashboard'): ?>
            <hr class="wp-header-end">
    <?php endif; ?>
    
    <div class="ffw-contianer">
        <div class="ffw-left-side">
            <div class="ffw-testimonial">
                <div class="ffw-tesimonial-heading">
                    <h3 class="ffw-tesimonial-heading-title"><?php esc_html_e("Happy customers", "faq-for-woocommerce"); ?></h3>
                    <p class="ffw-tesimonial-sub-heading"><?php esc_html_e("Let’s have a look what people are saying about us", "faq-for-woocommerce"); ?></p>
                </div>
                <div class="ffw-review-img">
                    <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/rating-point.png'); ?>" alt="">
                </div>
                <div class="ffw-tesimonial-contents">
                    <div class="ffw-star-imegas">
                        <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/rating.png'); ?>" alt="">
                    </div>
                    <div class="ffw-testimonial-text-wrapper">
                        <p class="ffw-testimonial-text"><span>XPlainer</span> transformed our customer support game. With their FAQ plugin, our clients can find answers to their questions instantly. Our workload reduced, and the analytics helped us improve the user experience. <span class="ffw-text-bolds">A real game-changer!</span></p>
                    </div>
                    <div class="ffw-profile-photo">
                        <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/reviewer-avatar.png'); ?>" alt="">
                    </div>
                </div>

            </div>
            <div class="ffw-iframe-section-wrapper">
                <iframe  src="https://www.youtube.com/embed/oGLDl0GKh-k?si=FcK_dvEseUat8vd2" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
            <div class="ffw-money-back-section-wrapper">
                <div class="money-back-section">
                    <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/money-back-guarentee.png'); ?>" alt="">
                    <div class="money-back-contents">
                        <h3 class="money-back-content-heading">14 days 100% Money-Back Guarantee</h3>
                        <p class="money-back-content-para">Your satisfaction, guaranteed: Try our services risk-free with a generous 14-days, 100% Money-Back Guarantee</p>
                        <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/card.png'); ?>" alt="">
                    </div>
                </div>
            </div>
            <div class="ffw-free-vs-pro-section-wrapper">
                <div class="ffw-free-vs-pro-section">
                    <h3 class="ffw-free-vs-pro-heading"><?php esc_html_e("XPlainer Free vs Pro", "faq-for-woocommerce"); ?></h3>
                    <p class="ffw-free-vs-pro-sub-heading"><?php esc_html_e("Get the most out of XPlainer FAQ by upgrading to Pro and unlocking all of the powerful features.", "faq-for-woocommerce"); ?></p>

                    <table class="ffw-free-vs-pro-table">
                        <tr>
                            <th><?php esc_html_e("Features", "faq-for-woocommerce"); ?></th>
                            <th><?php esc_html_e("Free", "faq-for-woocommerce"); ?></th>
                            <th><?php esc_html_e("Pro", "faq-for-woocommerce"); ?></th>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Unlimited FAQs", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Easily add unlimited product faqs. No limit at all.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("AI FAQs Generator", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Generate FAQs with Open AI support.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Schema Support", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Automatically Google schema will be added to the displayed FAQs page.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Sorting FAQs", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Drag and sort faqs with your desired order.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Shortcode Support", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Almost all types of shortcode support to display faqs anywhere.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Expand/Collapse All", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Enable to expand or collapse all the faqs.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Media/Image Support", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("All types of media can be added inside the answers.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Comment Support", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Get customer private/public comments about the faqs.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Fully Customization", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Let's customize almost everywhere as like you want.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("FAQs by Product Categories", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Assign product categories to FAQs. It will minimize your time to connect FAQs with products.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/cancel.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Variation Product FAQs", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Easily create FAQs for variation products and easily engage with your customers by answering their variation queries about the child products.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/cancel.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("More Beautiful FAQ Templates", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("With more advanced FAQ templates you can serve a high quality view and sell more.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/cancel.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Display Location", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Let's display faqs anywhere in the product page. You may like to show the faqs after `add to cart button` or `social share button`, `top of the page`, `bottom of the page` etc.", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/cancel.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Dynamic Product Attributes", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Add product attribute to the answers and it will show the value. Suppose, adding `{product_price}` it will show $30.00", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/cancel.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Global FAQs", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Create one FAQ as global to show it in all the products", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/cancel.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("RTL Support", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Enable it to display FAQs right to left order, if your language is rtl format", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/cancel.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Search FAQs", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("A search box will appear on the FAQs section to search FAQs easily", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/cancel.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p><?php esc_html_e("Multi Column Support", "faq-for-woocommerce"); ?></p>
                                <p><?php esc_html_e("Enable to show FAQs in multi column", "faq-for-woocommerce"); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/cancel.png'); ?>">
                            </td>
                            <td>
                                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/ok.png'); ?>">
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>

        <div class="ffw-right-side">
            <div class="ffw-rate-us-wrapper">
                <h3 class="ffw-rate-us-heading"><?php esc_html_e('Rate us', 'faq-for-woocommerce'); ?></h3>
                <p class="ffw-rate-us-content"> <?php esc_html_e('Let’s know your thoughts about our products and share your feedback.', 'faq-for-woocommerce'); ?></p>
                <a class="ffw-secondary-btn" href="<?php echo esc_url(FFW_REVIEW_URL); ?>"><?php esc_html_e('Rate us', 'faq-for-woocommerce'); ?></a>
            </div>

            <div class="ffw-why-pro-wrapper">
                <h3 class="ffw-why-pro-haading"><?php esc_html_e('Why Pro?', 'faq-for-woocommerce'); ?></h3>

                <ul class="ffw-why-pro-features">
                    <li class="ffw-why-pro-features-contents"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done.png'); ?>" alt=""><span><?php esc_html_e("FAQs by Product Categories", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-why-pro-features-contents"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done.png'); ?>" alt=""><span><?php esc_html_e("Variation Product FAQs", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-why-pro-features-contents"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done.png'); ?>" alt=""><span><?php esc_html_e("Beautiful FAQs Templates", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-why-pro-features-contents"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done.png'); ?>" alt=""><span><?php esc_html_e("Display Location", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-why-pro-features-contents"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done.png'); ?>" alt=""><span><?php esc_html_e("Dynamic Product Attributes", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-why-pro-features-contents"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done.png'); ?>" alt=""><span><?php esc_html_e("Global FAQs", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-why-pro-features-contents"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done.png'); ?>" alt=""><span><?php esc_html_e("RTL Support", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-why-pro-features-contents"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done.png'); ?>" alt=""><span><?php esc_html_e("Search FAQs", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-why-pro-features-contents"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done.png'); ?>" alt=""><span><?php esc_html_e("Multi Column Support", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-why-pro-features-contents">
                        <a class="ffw-more-anchor" href="<?php echo esc_url(FFW_PRO_URL); ?>">+More</a>
                    </li>
                </ul>
                <a class="ffw-secondary-btn" href="<?php echo esc_url(FFW_PRO_URL); ?>"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/go-pro-white.png'); ?>" alt="">Upgrade to Pro</a>
            </div>

            <div class="ffw-other-plugin-section-wrapper">
                <h3 class="ffw-other-plugin-heading">Other Plugins by Optemiz</h3>
                <div class="ffw-other-plugin-item">
                    <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/patternly.png'); ?>" alt="">
                    <div class="ffw-other-plugin-contents">
                        <h4 class="ffw-other-plugin-content-heading"><a href="https://wordpress.org/plugins/patternly">Patternly</a></h4>
                        <p class="ffw-other-plugin-content-para">Patternly is Gutenberg templates library for quickly creating professional websites</p>
                    </div>
                </div>
                <div class="ffw-other-plugin-item">
                    <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/temptool.png'); ?>" alt="">
                    <div class="ffw-other-plugin-contents">
                        <h4 class="ffw-other-plugin-content-heading"><a href="https://wordpress.org/plugins/current-template-name">TempTool</a></h4>
                        <p class="ffw-other-plugin-content-para">TempTool shows your current template file information</p>
                    </div>
                </div>
            </div>

            <div class="ffw-free-xplainer-wrapper">
                <h3 class="ffw-free-xplainer">Free XPlainer features</h3>
                <ul class="ffw-free-xplainer-features">
                    <li class="ffw-free-xplainer-features-content"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done-purple.png'); ?>" alt=""><span><?php esc_html_e("Unlimited FAQs", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-free-xplainer-features-content"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done-purple.png'); ?>" alt=""><span><?php esc_html_e("AI Generated FAQs", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-free-xplainer-features-content"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done-purple.png'); ?>" alt=""><span><?php esc_html_e("Schema Support", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-free-xplainer-features-content"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done-purple.png'); ?>" alt=""><span><?php esc_html_e("Shortcode Support", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-free-xplainer-features-content"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done-purple.png'); ?>" alt=""><span><?php esc_html_e("Comment Support", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-free-xplainer-features-content"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done-purple.png'); ?>" alt=""><span><?php esc_html_e("Expand/Collapse All", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-free-xplainer-features-content"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done-purple.png'); ?>" alt=""><span><?php esc_html_e("Media/Image Support", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-free-xplainer-features-content"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done-purple.png'); ?>" alt=""><span><?php esc_html_e("Sorting FAQs", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-free-xplainer-features-content"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/done-purple.png'); ?>" alt=""><span><?php esc_html_e("Fully Customizable", "faq-for-woocommerce"); ?></span></li>
                    <li class="ffw-free-xplainer-features-content"><a class="ffw-get-started-anchor" href="<?php echo esc_url(FFW_FREE_URL); ?>">+More</a></li>
                </ul>

            </div>
        </div>
    </div>
</section>
<!-- xplainer-body-section-end -->