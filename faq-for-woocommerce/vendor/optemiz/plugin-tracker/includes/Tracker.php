<?php
/**
 * Tracker setup
 *
 * @package Tracker
 * @since   1.0.0
 */
namespace Optemiz\PluginTracker;

defined( 'ABSPATH' ) || exit;

/**
 * Main Tracker Class.
 *
 * @class Tracker
 */
if ( ! class_exists( 'Tracker', false ) ) :
    /**
     * Main Tracker Class.
     *
     * @class Tracker
     */
    class Tracker {

        public $slug;

        public $api_url;

        public $plugin_base_path;

        public $insights;

        function __construct() {
        }

        function execute() {
            if ( function_exists( 'wc_get_logger' ) ) {
                wc_get_logger()->info(
                    'Inside Tracker->execute()',
                    array(
                        'source'        => $this->slug,
                    )
                );
            }

            add_action( 'upgrader_process_complete', array($this, 'plugin_updated'), 10, 2 );

            add_action($this->slug . '_tracker_optin', array($this, 'tracker_optin'));
            add_action($this->slug . '_uninstall_reason_submitted', array($this, 'uninstall_reason_submitted'));
        }

        function tracker_optin($data) {
            $new_data = [];
            $new_data['plugin_name'] = $this->slug;

            if ( function_exists( 'wc_get_logger' ) ) {
                wc_get_logger()->info(
                    'Inside Tracker->tracker_optin()',
                    array(
                        'source'      => $this->slug,
                    )
                );
            }

            if(!empty($data) && is_array($data)) {
                $new_data['user_nicename'] = $data['first_name'] . ' ' . $data['last_name'];

                unset($data['first_name']);
                unset($data['last_name']);

                $formated_keys = ['tracking_skipped', 'ip_address', 'hash', 'server', 'wp', 'users', 'active_plugins', 'inactive_plugins'];
                foreach($data as $key => $value) {
                    $new_key = $key;

                    if($key === 'admin_email') {
                        $new_key = 'user_email';
                    }elseif($key === 'site') {
                        $new_key = 'site_name';
                    }elseif($key === 'url') {
                        $new_key = 'site_url';
                    }elseif($key === 'project_version') {
                        $new_key = 'plugin_version';
                    }

                    if(in_array($key, $formated_keys)) {
                        $new_data['info'][$new_key] = $value;
                    }else {
                        $new_data[$new_key] = $value;
                    }

                }
            }

            $new_data['is_multi_site'] = is_multisite();
            $new_data['status'] = 'activated';
            $new_data['last_updated_date'] = time();

            //generate token.
            $token_data['site_url']     = $data['url'];
            $token_data['plugin_name']  = $this->slug;
            $response = $this->send_request($token_data, $this->api_url . '/wp-json/optemiz/v1/email_tracker/generate_token', true);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();

                if ( function_exists( 'wc_get_logger' ) ) {
                    wc_get_logger()->info(
                        'Inside Tracker->tracker_optin() is_wp_error($response)',
                        array(
                            'source'      => $this->slug,
                            'error_message' => $error_message,
                        )
                    );
                }
            } else {
                // Get the body of the response
                $response_body  = wp_remote_retrieve_body($response);
                $data           = json_decode($response_body, true);

                $token_option_name = $this->get_token_option_name();
                $token_exist = get_option($token_option_name);

                if ( function_exists( 'wc_get_logger' ) ) {
                    wc_get_logger()->info(
                        'Inside Tracker->tracker_optin() else of is_wp_error($response)',
                        array(
                            'source'      => $this->slug,
                            'token_option_name' => $token_option_name,
                            'token_exist' => $token_exist,
                            'response_body' => $data,
                        )
                    );
                }

                if(false === $token_exist && isset($data['token'])) {
                    update_option($token_option_name, $data['token']);

                    $new_data['token'] = $data['token'];

                    if ( function_exists( 'wc_get_logger' ) ) {
                        wc_get_logger()->info(
                            'Inside Tracker->tracker_optin() false === $token_exist && isset($data[token])',
                            array(
                                'source'      => $this->slug,
                                'new_data' => $new_data,
                            )
                        );
                    }

                    $this->send_request($new_data, $this->api_url . '/wp-json/optemiz/v1/email_tracker/optin');
                }
            }
        }
        
        function uninstall_reason_submitted($data) {
            global $wpdb;

            if(!isset($data['url'])) {
                return;
            }
            
            if(!isset($data['admin_email'])) {
                return;
            }

            if ( function_exists( 'wc_get_logger' ) ) {
                wc_get_logger()->info(
                    'Inside Tracker->uninstall_reason_submitted()',
                    array(
                        'source'        => $this->slug,
                        'data'        => $data,
                    )
                );
            }

            $new_data['user_email']     = $data['admin_email'];
            $new_data['site_url']       = $data['url'];
            $new_data['reason_id']      = $data['reason_id'];
            $new_data['reason_info']    = $data['reason_info'];
            $new_data['status']         = 'deactivated';

            $this->send_request($new_data, $this->api_url . '/wp-json/optemiz/v1/email_tracker/deactivate');
        }

        public function send_request( $params, $url, $blocking = false ) {
            $params = json_encode($params);
	
			$headers = [
				'user-agent'      => 'OPT_Email_Tracker/' . md5( esc_url( home_url() ) ) . ';',
				'Accept'          => 'application/json',
				'Content-Type'    => 'application/json',
			];
	
			$response = wp_remote_post(
				$url,
				[
					'method'      => 'POST',
					'timeout'     => 30,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => $blocking,
					'headers'     => $headers,
					'body'        => $params,
					'cookies'     => [],
				]
			);

            if ( function_exists( 'wc_get_logger' ) ) {
                wc_get_logger()->info(
                    'Inside Tracker->send_request()',
                    array(
                        'source'      => $this->slug,
                        'response' => $response,
                    )
                );
            }
	
			return $response;
		}

        /**
         * Track after plugin is updated;
         * 
         * @param $upgrader_object Array
         * @param $options Array
         */
        function plugin_updated( $upgrader_object, $options ) {

            if ( function_exists( 'wc_get_logger' ) ) {
                wc_get_logger()->info(
                    'Inside Tracker->plugin_updated()',
                    array(
                        'source'        => $this->slug,
                    )
                );
            }
            
            // when plugin is updated.
            if( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) ) {
                foreach( $options['plugins'] as $plugin ) {
                    
                    if( $plugin == $this->plugin_base_path ) {
                        $tracking_data = $this->insights->get_tracking_data();

                        //check whether trakcing allowed or not.
                        $allow_tracking = get_option("{$this->slug}_allow_tracking");

                        if ( function_exists( 'wc_get_logger' ) ) {
                            wc_get_logger()->info(
                                'Inside Tracker->plugin_updated() -> $plugin == $this->plugin_base_path',
                                array(
                                    'source'        => $this->slug,
                                    'plugin base path' => $this->plugin_base_path,
                                    'tracking_data' => $tracking_data,
                                    'allow_tracking' => $allow_tracking,
                                )
                            );
                        }

                        if("yes" === $allow_tracking) {

                            $token_option_name  = $this->get_token_option_name();
                            $already_tracked    = get_option($token_option_name);

                            if ( function_exists( 'wc_get_logger' ) ) {
                                wc_get_logger()->info(
                                    'Inside Tracker->plugin_updated() -> "yes" === $allow_tracking',
                                    array(
                                        'source'        => $this->slug,
                                        'token_option_name' => $token_option_name,
                                        'already_tracked' => $already_tracked,
                                    )
                                );
                            }

                            //when no token exists, send data.
                            if (false === $already_tracked) {

                                if ( function_exists( 'wc_get_logger' ) ) {
                                    wc_get_logger()->info(
                                        'Inside Tracker->plugin_updated() -> false === $already_tracked',
                                        array(
                                            'source'        => $this->slug
                                        )
                                    );
                                }

                                $this->tracker_optin($tracking_data);
                            }else {
                                // $tracking_data['site_url']          = $tracking_data['url'];
                                // $tracking_data['user_email']        = $tracking_data['admin_email'];
                                // $tracking_data['plugin_version']    = $tracking_data['project_version'];

                                // unset($tracking_data['url']);
                                // unset($tracking_data['admin_email']);
                                // unset($tracking_data['project_version']);

                                // $this->send_request($tracking_data, $this->api_url . '/wp-json/optemiz/v1/email_tracker/updated');
                            }
                        }
                    }
                }
            }
        }

        /**
         * Get token option name.
         */
        function get_token_option_name() {
            $site_url 		= home_url();
            $plugin_name 	= $this->slug;

            $site_url 		= base64_encode($site_url);
            $plugin_name 	= base64_encode($plugin_name);

            $token_option_name = "opt_tracked_{$site_url}_{$plugin_name}_token";

            return apply_filters("opt_filter_token_option_name", $token_option_name);
        }

    }

endif;