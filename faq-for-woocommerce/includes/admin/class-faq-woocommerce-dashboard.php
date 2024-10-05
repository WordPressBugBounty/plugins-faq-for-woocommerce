<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'FFW_Dashboard' ) ) {
	/**
	 * FFW_Dashboard class
	 *
	 * @since 1.4.9
	 */
	class FFW_Dashboard {

		public $message;
		public $status = true;

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_dashboard_menu' ), 99 );
		}

		/**
		 * Add dashboard menu
		 *
		 * @return void
		 */
		public function add_dashboard_menu() {
			global $submenu;

			add_submenu_page(
				'edit.php?post_type=ffw', 
				esc_html__('Dashboard', 'faq-for-woocommerce'),
				esc_html__('Dashboard', 'faq-for-woocommerce'),
				'manage_options', 
				'ffw-dashboard', 
				array($this, 'render_dashboard_content') 
			);

			// Create a new sub-menu in the order that we want
			$new_submenu = array();
			$menu_item_count = 3;

			if ( ! isset( $submenu['edit.php?post_type=ffw'] ) || ! is_array($submenu['edit.php?post_type=ffw']) ) { 
				return; 
			}
			
			foreach ( $submenu['edit.php?post_type=ffw'] as $key => $sub_item ) {
				
				if ( $sub_item[0] == 'Dashboard' ) {
					$new_submenu[0] = $sub_item; 
				} elseif ( $sub_item[0] == 'Settings' ) {
					$new_submenu[ sizeof($submenu) ] = $sub_item;
				} else {
					$new_submenu[$menu_item_count] = $sub_item;
					$menu_item_count++;
				}
			}

			ksort($new_submenu);
			
			$submenu['edit.php?post_type=ffw'] = $new_submenu;
		}

		/**
		 * Render dashboard page content
		 *
		 * @return void
		 */
		public function render_dashboard_content() {
			$content = "";
			
			ob_start();

			?>
			<div class="ffw-admin-wrapper">
				<?php
					include FFW_FILE_DIR . '/views/dashboard/header.php';
					include FFW_FILE_DIR . '/views/dashboard/body.php';
					include FFW_FILE_DIR . '/views/dashboard/footer.php';
				?>
			</div>
			<?php

			$content .= ob_get_clean();

			echo wp_kses_post($content);
		}

	}

	new FFW_Dashboard();
}
