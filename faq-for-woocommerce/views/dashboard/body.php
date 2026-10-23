<?php require_once FFW_ABSPATH . '/includes/admin/dashboard-features.php'; ?>
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
                        foreach ( ffw_dashboard_feature_table() as $ffw_vs_section ) {
                            $ffw_vs_group( $ffw_vs_section['title'], $ffw_vs_section['text'], $ffw_vs_section['emoji'] );
                            foreach ( $ffw_vs_section['rows'] as $ffw_vs_item ) {
                                $ffw_vs_row( $ffw_vs_item[0], $ffw_vs_item[1], $ffw_vs_item[2], $ffw_vs_ok, $ffw_vs_no );
                            }
                        }
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
                    $ffw_why_pro = ffw_dashboard_why_pro();

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

            <div class="ffw-free-xplainer-wrapper">
                <h3 class="ffw-free-xplainer"><?php esc_html_e( 'Free features', 'faq-for-woocommerce' ); ?></h3>
                <ul class="ffw-free-xplainer-features">
                    <?php
                    $ffw_free_done = FFW_PLUGIN_URL . '/assets/admin/images/done-purple.png';
                    $ffw_free_features = ffw_dashboard_free_features();

                    foreach ( array_slice( $ffw_free_features, 0, 24 ) as $ffw_free_feature ) {
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