<!-- header-section-start-->
<header class="ffw-dashboard-header-wrapper">
    <div class="ffw-contianer">
        <div class="ffw-header-cover">
            <div class="ffw-header-background-images">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/icon300.png'); ?>" alt="">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/icon100.png'); ?>" alt="">
                <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/icon200.png'); ?>" alt="">
            </div>
            <div class="ffw-header-title">
                <div class="ffw-header-logo">
                    <img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/Logo.png'); ?> " alt="">
                    <div class="ffw-header-logo-wrapper">
                        <?php
                            $menu_title = ffw_get_settings_page_menu_title();
                            $plugin_version = apply_filters('ffw_show_settings_page_plugin_version', esc_html(FFW_VERSION));
                            echo sprintf('<h2 class="ffw-logo-text">%s<span class="ffw-version">V: %s</span></h2>', esc_html($menu_title), esc_html($plugin_version) );
                            echo wp_kses_post( '<p>Easily create product FAQs to increase your sell</p>' );
                        ?>
                    </div>   
                </div>
                
                <ul class="ffw-header-menus">
                    <li><a class="ffw-header-menu-items" href="<?php echo esc_url(FFW_DOC_URL); ?>"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/file.png'); ?>" alt=""><?php esc_html_e("Docs", "faq-for-woocommerce"); ?></a></li>  
                    <li><a class="ffw-header-menu-items" href="<?php echo esc_url(FFW_VIDEOS_URL); ?>"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/play.png'); ?>" alt=""><?php esc_html_e("Videos", "faq-for-woocommerce"); ?></a></li>  
                    <li><a class="ffw-header-menu-items" href="<?php echo esc_url(FFW_SUPPORT_URL); ?>"><img src="<?php echo esc_url(FFW_PLUGIN_URL . '/assets/admin/images/headset.png'); ?>" alt=""><?php esc_html_e("Support", "faq-for-woocommerce"); ?></a></li>
                    <li>
                        <?php
                            echo sprintf( 
                                '<a href="%2$s" class="ffw-header-menu-items ffw-primary-btn"><img src="%3$s" alt=""> %1$s</a>',
                                esc_html__('Upgrade to Pro', 'faq-for-woocommerce'),
                                esc_url(FFW_PRO_URL), 
                                esc_url(FFW_PLUGIN_URL . '/assets/admin/images/go-pro.png') 
                            );
                        ?>
                    </li>
                </ul>
                
            </div>
        </div>
        
    </div>
    
    <!-- ffw-main-menu-wrapper-start -->
    <div class="ffw-main-menu-wrapper">
        <ul class="ffw-main-menu">
            <?php
                $is_dashboard_page = false;
                $is_category_page = false;
                $is_faq_list_page = false;
                $is_setting_page = false;
                $is_template_page = false;
                $is_ai_page = false;

                $screen = get_current_screen();

                if( isset($_GET['page']) && $_GET['page'] === "ffw-dashboard" ) {
                    $is_dashboard_page = true;
                }

                if( isset($_GET['post_type']) && $_GET['post_type'] === "ffw" ) {

                    if( $screen->base == "edit" ) {
                        $is_faq_list_page = true;
                    }

                    if( isset($_GET['taxonomy']) && $_GET['taxonomy'] === "ffw-category" ) {
                        $is_category_page = true;
                    }
                    
                    if( isset($_GET['page']) && $_GET['page'] === "woocommerce-faq" ) {
                        $is_setting_page = true;
                    }
                    
                    if( isset($_GET['page']) && $_GET['page'] === "ai-faqs" ) {
                        $is_ai_page = true;
                    }

                    if( isset($_GET['page']) && $_GET['page'] === "ffw-templates" ) {
                        $is_template_page = true;
                    }
                }
            ?>

            <li class="ffw-menu <?php echo $is_dashboard_page ? esc_attr('active') : ''; ?>"><a href="<?php echo esc_url(admin_url("edit.php?post_type=ffw&page=ffw-dashboard")); ?>"><?php esc_html_e("Dashboard", "faq-for-woocommerce"); ?></a></li>
            <li class="ffw-menu <?php echo $is_faq_list_page ? esc_attr('active') : ''; ?>"><a href="<?php echo esc_url(admin_url("edit.php?post_type=ffw")); ?>"><?php esc_html_e("FAQS", "faq-for-woocommerce"); ?></a></li>
            <li class="ffw-menu <?php echo $is_ai_page ? esc_attr('active') : ''; ?>"><a href="<?php echo esc_url(admin_url("edit.php?post_type=ffw&page=ai-faqs")); ?>"><span><?php esc_html_e("AI FAQs", "faq-for-woocommerce"); ?></span></a></li>
            <li class="ffw-menu <?php echo $is_template_page ? esc_attr('active') : ''; ?>"><a href="<?php echo esc_url(admin_url("edit.php?post_type=ffw&page=ffw-templates")); ?>"><span><?php esc_html_e("Templates", "faq-for-woocommerce"); ?></span></a></li>
            <li class="ffw-menu <?php echo $is_setting_page ? esc_attr('active') : ''; ?>"><a href="<?php echo esc_url(admin_url("edit.php?post_type=ffw&page=woocommerce-faq")); ?>"><?php esc_html_e("Settings", "faq-for-woocommerce"); ?></a></li>
        </ul>
    </div>
    <!-- ffw-main-menu-wrapper-end -->
</header>

<?php if(isset($_GET['page']) && $_GET['page'] === 'woocommerce-faq'): ?>
            <hr class="wp-header-end">
    <?php endif; ?>
<!-- header-section-end-->