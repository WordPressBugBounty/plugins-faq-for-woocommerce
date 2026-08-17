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
                        <p class="ffw-testimonial-text"><span>Happy FAQs</span> transformed our customer support game. With their FAQ plugin, our clients can find answers to their questions instantly. Our workload reduced, and the analytics helped us improve the user experience. <span class="ffw-text-bolds">A real game-changer!</span></p>
                    </div>
                    <div class="ffw-profile-photo">
                        <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/reviewer-avatar.png'); ?>" alt="">
                    </div>
                </div>

            </div>
            <div class="ffw-iframe-section-wrapper">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/CnsPBdgIbaU?si=09LXrbnolHNOqMHm" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
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
                    <h3 class="ffw-free-vs-pro-heading"><?php esc_html_e( 'Happy FAQs Free vs Pro', 'faq-for-woocommerce' ); ?></h3>
                    <p class="ffw-free-vs-pro-sub-heading"><?php esc_html_e( 'One FAQ library for your site and your store. Free covers help pages, builders, and product FAQs. Pro unlocks Groups, AJAX search, extra layouts, and store extras.', 'faq-for-woocommerce' ); ?></p>

                    <?php
                    $ffw_vs_ok = FFW_PLUGIN_URL . '/assets/admin/images/ok.png';
                    $ffw_vs_no = FFW_PLUGIN_URL . '/assets/admin/images/cancel.png';

                    $ffw_vs_group = static function ( $title, $desc, $emoji ) {
                        ?>
                        <tr class="ffw-free-vs-pro-group">
                            <td colspan="3">
                                <p><?php echo esc_html( $title ); ?> <span class="ffw-free-vs-pro-group-emoji" aria-hidden="true"><?php echo esc_html( $emoji ); ?></span></p>
                                <p><?php echo esc_html( $desc ); ?></p>
                            </td>
                        </tr>
                        <?php
                    };

                    $ffw_vs_row = static function ( $title, $desc, $in_free, $ok, $no ) {
                        ?>
                        <tr class="ffw-free-vs-pro-sub">
                            <td>
                                <p><?php echo esc_html( $title ); ?></p>
                                <p><?php echo esc_html( $desc ); ?></p>
                            </td>
                            <td>
                                <img src="<?php echo esc_url( $in_free ? $ok : $no ); ?>" alt="">
                            </td>
                            <td>
                                <img src="<?php echo esc_url( $ok ); ?>" alt="">
                            </td>
                        </tr>
                        <?php
                    };
                    ?>

                    <table class="ffw-free-vs-pro-table">
                        <tr>
                            <th><?php esc_html_e( 'Features', 'faq-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Free', 'faq-for-woocommerce' ); ?></th>
                            <th><?php esc_html_e( 'Pro', 'faq-for-woocommerce' ); ?></th>
                        </tr>
                        <?php
                        $ffw_vs_group(
                            __( 'General FAQs', 'faq-for-woocommerce' ),
                            __( 'Create answers once and place them on help pages, posts, and anywhere else on the site.', 'faq-for-woocommerce' ),
                            '💬'
                        );
                        $ffw_vs_row(
                            __( 'Unlimited FAQs', 'faq-for-woocommerce' ),
                            __( 'One reusable FAQ library. No cap on how many questions you can publish.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Place FAQs on any page', 'faq-for-woocommerce' ),
                            __( 'Show all FAQs, a FAQ category, or selected FAQs on any page or post.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'FAQ categories', 'faq-for-woocommerce' ),
                            __( 'Group questions into categories like Shipping or Returns, then embed that category.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'FAQ page setting', 'faq-for-woocommerce' ),
                            __( 'Pick a page in Settings and Happy FAQs can inject the FAQ list for you.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Shortcodes', 'faq-for-woocommerce' ),
                            __( '[ffw_template] for all, category, selected IDs, and current product. Pro adds group_id.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );

                        $ffw_vs_group(
                            __( 'Additional features', 'faq-for-woocommerce' ),
                            __( 'Builders, search, schema, and the extras that make the library easier to use.', 'faq-for-woocommerce' ),
                            '✨'
                        );
                        $ffw_vs_row(
                            __( 'Happy Prebuilt FAQs', 'faq-for-woocommerce' ),
                            __( 'Gutenberg block, Elementor widget, and Divi 4/5 module. Shows FAQs from your library. Same markup as the shortcode.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Happy Custom FAQs', 'faq-for-woocommerce' ),
                            __( 'Write questions and answers in Gutenberg, Elementor, or Divi 4/5. They stay on that page.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'FAQPage schema', 'faq-for-woocommerce' ),
                            __( 'Google FAQ schema on help pages and product pages, with duplicate questions removed.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Inline search', 'faq-for-woocommerce' ),
                            __( 'A search box that filters the FAQs already on the page as you type.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'AI FAQ generator', 'faq-for-woocommerce' ),
                            __( 'Generate questions and answers with OpenAI from the AI FAQs screen.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Media in answers', 'faq-for-woocommerce' ),
                            __( 'Add images, video, and other media inside FAQ answers.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Expand / collapse all', 'faq-for-woocommerce' ),
                            __( 'One control to open or close every FAQ in the list.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Comments', 'faq-for-woocommerce' ),
                            __( 'Let visitors comment on FAQs when comments are enabled.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Customization', 'faq-for-woocommerce' ),
                            __( 'Layouts, colors, width, and display options in Settings.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'FAQ Groups', 'faq-for-woocommerce' ),
                            __( 'Named collections of existing FAQs, with drag-and-drop order and a copyable shortcode.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'AJAX library search', 'faq-for-woocommerce' ),
                            __( 'Autocomplete across all published FAQs. Place it with [ffw_search], a block, or an Elementor widget.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Hash deep-links', 'faq-for-woocommerce' ),
                            __( 'Open a specific FAQ with a link like #ffw-faq-123.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Extra layouts', 'faq-for-woocommerce' ),
                            __( 'Glow, Smart, Wow, and Zoom layouts in addition to the five free templates.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Import / export', 'faq-for-woocommerce' ),
                            __( 'Download and restore FAQs, categories, groups, and product assignments as JSON.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'RTL support', 'faq-for-woocommerce' ),
                            __( 'Display FAQs right-to-left when your language needs it.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Multi-column layout', 'faq-for-woocommerce' ),
                            __( 'Show FAQs in more than one column.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );

                        $ffw_vs_group(
                            __( 'Product FAQs', 'faq-for-woocommerce' ),
                            __( 'The same FAQ posts can also be assigned to WooCommerce products, categories, and tags.', 'faq-for-woocommerce' ),
                            '🛒'
                        );
                        $ffw_vs_row(
                            __( 'Assign FAQs to products', 'faq-for-woocommerce' ),
                            __( 'Attach FAQs to a product from the product screen or the FAQ screen.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'FAQs by product categories', 'faq-for-woocommerce' ),
                            __( 'Assign a product category to FAQs so every product in that category inherits them.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'FAQs by product tags', 'faq-for-woocommerce' ),
                            __( 'Assign a product tag to FAQs to connect them to matching products.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Sort FAQs on a product', 'faq-for-woocommerce' ),
                            __( 'Drag FAQs into the order you want on each product.', 'faq-for-woocommerce' ),
                            true,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Customer asking questions', 'faq-for-woocommerce' ),
                            __( 'Let shoppers submit a question from the product page, with email notifications.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Variation product FAQs', 'faq-for-woocommerce' ),
                            __( 'Show different FAQs for variable product variations.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Extra product page locations', 'faq-for-woocommerce' ),
                            __( 'Place FAQs after Add to cart, at the top or bottom of the product page, and similar spots.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Product archive FAQs', 'faq-for-woocommerce' ),
                            __( 'Show FAQs on WooCommerce category and tag archives.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Shop page FAQs', 'faq-for-woocommerce' ),
                            __( 'Show order, payment, or shipping FAQs on the shop page.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Cart page FAQs', 'faq-for-woocommerce' ),
                            __( 'Show FAQs on the cart page.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Checkout page FAQs', 'faq-for-woocommerce' ),
                            __( 'Show FAQs on the checkout page.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Dynamic product attributes', 'faq-for-woocommerce' ),
                            __( 'Insert placeholders like {product_price} in an answer and show the live product value.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'Global product FAQs', 'faq-for-woocommerce' ),
                            __( 'Mark a FAQ as global so it appears on every product.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        $ffw_vs_row(
                            __( 'AJAX search on product FAQs', 'faq-for-woocommerce' ),
                            __( 'A search box on the product FAQ list that filters those FAQs over AJAX.', 'faq-for-woocommerce' ),
                            false,
                            $ffw_vs_ok,
                            $ffw_vs_no
                        );
                        ?>
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
                    <?php
                    $ffw_why_pro_done = FFW_PLUGIN_URL . '/assets/admin/images/done.png';
                    $ffw_why_pro = array(
                        __( 'FAQ Groups', 'faq-for-woocommerce' ),
                        __( 'Product FAQs', 'faq-for-woocommerce' ),
                        __( 'AJAX library search', 'faq-for-woocommerce' ),
                        __( 'Extra layouts', 'faq-for-woocommerce' ),
                        __( 'Import / export', 'faq-for-woocommerce' ),
                        __( 'Cart, Checkout and Shop FAQs', 'faq-for-woocommerce' ),
                        __( 'Customer asking questions', 'faq-for-woocommerce' ),
                        __( 'Variation product FAQs', 'faq-for-woocommerce' ),
                        __( 'Dynamic product attributes', 'faq-for-woocommerce' ),
                        __( 'Global product FAQs', 'faq-for-woocommerce' ),
                    );

                    foreach ( array_slice( $ffw_why_pro, 0, 20 ) as $ffw_why_pro_feature ) {
                        ?>
                        <li class="ffw-why-pro-features-contents">
                            <img src="<?php echo esc_url( $ffw_why_pro_done ); ?>" alt="">
                            <span><?php echo esc_html( $ffw_why_pro_feature ); ?></span>
                        </li>
                        <?php
                    }
                    ?>
                    <li class="ffw-why-pro-features-contents">
                        <a class="ffw-more-anchor" href="<?php echo esc_url(FFW_PRO_URL); ?>">+More</a>
                    </li>
                </ul>
                <a class="ffw-secondary-btn" href="<?php echo esc_url(FFW_PRO_URL); ?>"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/go-pro-white.png'); ?>" alt="">Upgrade to Pro</a>
            </div>

            <div class="ffw-other-plugin-section-wrapper">
                <h3 class="ffw-other-plugin-heading"><?php esc_html_e('Essential Plugins', 'faq-for-woocommerce'); ?></h3>
                <div class="ffw-other-plugin-item">
                    <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/patternly.png'); ?>" alt="">
                    <div class="ffw-other-plugin-contents">
                        <h4 class="ffw-other-plugin-content-heading"><a href="https://wordpress.org/plugins/patternly"><?php esc_html_e('Patternly', 'faq-for-woocommerce'); ?></a></h4>
                        <p class="ffw-other-plugin-content-para"><?php esc_html_e('Patternly is Gutenberg templates library for quickly creating professional websites', 'faq-for-woocommerce'); ?></p>
                    </div>
                </div>
                <div class="ffw-other-plugin-item">
                    <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/pagely.png'); ?>" alt="">
                    <div class="ffw-other-plugin-contents">
                        <h4 class="ffw-other-plugin-content-heading"><a href="https://wordpress.org/plugins/current-template-name"><?php esc_html_e('Pagely', 'faq-for-woocommerce'); ?></a></h4>
                        <p class="ffw-other-plugin-content-para"><?php esc_html_e('Pagely shows your current template file information', 'faq-for-woocommerce'); ?></p>
                    </div>
                </div>
                <div class="ffw-other-plugin-item">
                    <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/autocomplete-orders.png'); ?>" alt="">
                    <div class="ffw-other-plugin-contents">
                        <h4 class="ffw-other-plugin-content-heading"><a href="https://wordpress.org/plugins/advanced-autocomplete-orders-for-woocommerce/"><?php esc_html_e('Advanced Autocomplete Order for WooCommerce', 'faq-for-woocommerce'); ?></a></h4>
                        <p class="ffw-other-plugin-content-para"><?php esc_html_e('Automatically update orders to ‘Completed’ right after purchase', 'faq-for-woocommerce'); ?></p>
                    </div>
                </div>
            </div>

            <div class="ffw-free-xplainer-wrapper">
                <h3 class="ffw-free-xplainer"><?php esc_html_e( 'Free features', 'faq-for-woocommerce' ); ?></h3>
                <ul class="ffw-free-xplainer-features">
                    <?php
                    $ffw_free_done = FFW_PLUGIN_URL . '/assets/admin/images/done-purple.png';
                    $ffw_free_features = array(
                        __( 'Unlimited FAQs', 'faq-for-woocommerce' ),
                        __( 'Product FAQs', 'faq-for-woocommerce' ),
                        __( 'FAQPage schema', 'faq-for-woocommerce' ),
                        __( 'FAQs on any page', 'faq-for-woocommerce' ),
                        __( 'FAQ categories', 'faq-for-woocommerce' ),
                        __( 'Shortcodes', 'faq-for-woocommerce' ),
                        __( 'Happy Prebuilt FAQs', 'faq-for-woocommerce' ),
                        __( 'Happy Custom FAQs', 'faq-for-woocommerce' ),
                        __( 'Inline search', 'faq-for-woocommerce' ),
                        __( 'AI FAQ generator', 'faq-for-woocommerce' ),
                        __( 'FAQs by product categories and tags', 'faq-for-woocommerce' ),
                        __( 'Comments and media', 'faq-for-woocommerce' ),
                    );

                    foreach ( array_slice( $ffw_free_features, 0, 20 ) as $ffw_free_feature ) {
                        ?>
                        <li class="ffw-free-xplainer-features-content">
                            <img src="<?php echo esc_url( $ffw_free_done ); ?>" alt="">
                            <span><?php echo esc_html( $ffw_free_feature ); ?></span>
                        </li>
                        <?php
                    }
                    ?>
                    <li class="ffw-free-xplainer-features-content"><a class="ffw-get-started-anchor" href="<?php echo esc_url(FFW_FREE_URL); ?>">+More</a></li>
                </ul>

            </div>
        </div>
    </div>
</section>
<!-- xplainer-body-section-end -->