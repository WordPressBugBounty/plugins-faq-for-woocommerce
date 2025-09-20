<?php
/**
 * FAQ Woocommerce Admin Settings
 *
 * @class    FFW_Admin_Menu
 * @package  FAQ_Woocommerce\Admin\Menu
 * @version  1.0.0
 *
 * @link    https://happydevs.net/
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * FFW_Admin_Menu class
 */
class FFW_Admin_Menu {

    /**
     * The single instance of the class.
     *
     * @var FFW_Admin_Menu
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main FFW_Admin_Menu Instance.
     *
     * Ensures only one instance of FFW_Admin_Menu is loaded or can be loaded.
     *
     * @since 1.4.1
     */
    public static function instance() {
        if ( is_null(self::$_instance) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
    }

    /**
	 * Admin Menus.
	 */
    public function admin_menu() {

        $this->ffw_add_settings_menu();

        add_submenu_page( 'edit.php?post_type=ffw', esc_html__('AI FAQs', 'faq-for-woocommerce'), esc_html__('AI FAQs', 'faq-for-woocommerce'), 'manage_options', 'ai-faqs', array( $this, 'ffw_ai_page' ), 9999 );
        add_submenu_page( 'edit.php?post_type=ffw', esc_html__('Templates', 'faq-for-woocommerce'), esc_html__('Templates', 'faq-for-woocommerce'), 'manage_options', 'ffw-templates', array( $this, 'ffw_templates_page' ), 9999 );

        if( ! ffw_is_premium_active() ) {
            add_submenu_page( 'edit.php?post_type=ffw', esc_html__('Upgrade', 'faq-for-woocommerce'), esc_html__('Upgrade', 'faq-for-woocommerce'), 'manage_options', 'ffw-upgrade-to-pro', array( $this, 'submenu_callback' ), 9999 );
        }

        
    }

    /**
	 * Menu Page.
	 */
    public function ffw_add_settings_menu() {

        // Get registered option
        $options = get_option( 'ffw_general_settings' );

        if( isset($options['ffw_set_role']) ) {
            $ffw_roles = isset( $options['ffw_set_role'] ) ? $options['ffw_set_role'] : ["administrator"];

            //get current user
            $user = wp_get_current_user();
            $roles = $user->roles;

            

            //when user role is not set to ffw_set_role, hide the ffw post type
            // if( ! empty($roles) && is_array($roles) ) {
            //     foreach($roles as $role) {

            //         if( ! in_array( $role, (array) $ffw_roles )  ) {
            //             remove_menu_page( 'edit.php?post_type=ffw' );
            //         }
            //     }
            // }
        }


        $settings_page_menu_title = ffw_get_settings_page_menu_title();
        $setting_instance = FAQ_Woocommerce_Settings::instance();

		add_submenu_page( 'edit.php?post_type=ffw', $settings_page_menu_title, esc_html__('Settings', 'faq-for-woocommerce'), 'manage_options', 'woocommerce-faq', array( $setting_instance, 'ffw_options_page' ), 9999 );
    }

    /**
     * AI page content
     * 
     * @since 1.7.0
     * @return void
     */
    public function ffw_ai_page() {
        $content = "";
			
        ob_start();
        ?>
        <div class="ffw-admin-wrapper">
            <?php
                include FFW_FILE_DIR . '/views/ai-faqs/body.php';
            ?>
        </div>
        <?php

        $content .= ob_get_clean();

        echo $content;
    }

    /**
     * Templates Page content
     * 
     * @since 2.0.0
     * 
     * @return void
     */
    public function ffw_templates_page() {
        $content = "";
			
        ob_start();
        ?>
        <div class="ffw-admin-wrapper">
            <?php
                include FFW_FILE_DIR . '/views/templates.php';
            ?>
        </div>
        <?php

        $content .= ob_get_clean();

        echo $content;
    }

    /**
	 * Submenu callback.
	 */
    public function submenu_callback() {

        // redirect to pro page
        if( isset($_GET['page']) && 'ffw-upgrade-to-pro' == $_GET['page'] ) {
            wp_redirect( FFW_PRO_URL );
            die;
        }
        
    }


}

new FFW_Admin_Menu();