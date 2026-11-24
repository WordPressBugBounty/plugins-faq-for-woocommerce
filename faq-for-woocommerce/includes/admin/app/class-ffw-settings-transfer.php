<?php
/**
 * Happy FAQs → Settings → Import / Export.
 *
 * Moves every saved setting (ffw_general_settings) as a JSON file. An import
 * makes this site's settings match the file: a switch that is off in the file
 * is turned off here, because off switches are not stored. The previous
 * settings are kept once, so an import can be undone.
 *
 * Kept on this site when the file cannot decide them: API keys (left out of
 * the file unless asked for), the FAQ page when no page matches, and choices
 * that need Pro when Pro is not active.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FFW_Settings_Transfer class.
 */
class FFW_Settings_Transfer {

    const BACKUP   = 'ffw_settings_backup';
    const FORMAT   = 1;
    const MAX_SIZE = 1048576;
    const MAX_KEYS = 500;

    /**
     * Hooks.
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'routes' ) );
    }

    /**
     * REST routes.
     *
     * @return void
     */
    public function routes() {
        $can = static function () {
            return current_user_can( 'manage_options' );
        };

        register_rest_route(
            FAQ_Woocommerce_Admin_App::NS,
            '/settings/export',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'export' ),
                'permission_callback' => $can,
                'args'                => array(
                    'secrets' => array(
                        'type'    => 'boolean',
                        'default' => false,
                    ),
                ),
            )
        );

        register_rest_route(
            FAQ_Woocommerce_Admin_App::NS,
            '/settings/import',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'import' ),
                'permission_callback' => $can,
                'args'                => array(
                    'file'    => array(
                        'type'     => 'string',
                        'required' => true,
                    ),
                    'preview' => array(
                        'type'    => 'boolean',
                        'default' => true,
                    ),
                ),
            )
        );

        register_rest_route(
            FAQ_Woocommerce_Admin_App::NS,
            '/settings/restore',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'restore' ),
                'permission_callback' => $can,
            )
        );
    }

    /**
     * Data for the Settings page.
     *
     * @return array
     */
    public static function data() {
        $has_secret = false;
        foreach ( self::options() as $key => $value ) {
            if ( self::is_secret( $key ) && '' !== $value ) {
                $has_secret = true;
            }
        }
        return array(
            'hasSecret' => $has_secret,
            'backup'    => self::backup_info(),
            'maxSize'   => self::MAX_SIZE,
            'faqsUrl'   => ffw_is_pro_activated() ? admin_url( 'edit.php?post_type=ffw&page=ffw-import-export' ) : '',
        );
    }

    /* ------------------------------------------------------------------
     * Export.
     * ------------------------------------------------------------------ */

    /**
     * Export every saved setting.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function export( $request ) {
        $settings = self::options();

        if ( ! $request->get_param( 'secrets' ) ) {
            foreach ( array_keys( $settings ) as $key ) {
                if ( self::is_secret( $key ) ) {
                    unset( $settings[ $key ] );
                }
            }
        }

        // The FAQ page is a post ID, which is another page on another site.
        $page    = array();
        $page_id = isset( $settings['ffw_faq_page'] ) ? (int) $settings['ffw_faq_page'] : 0;
        if ( $page_id && 'page' === get_post_type( $page_id ) ) {
            $page = array(
                'id'    => $page_id,
                'path'  => get_page_uri( $page_id ),
                'title' => get_post_field( 'post_title', $page_id ),
            );
        }

        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        $file = array(
            'plugin'   => 'happy-faqs',
            'type'     => 'settings',
            'format'   => self::FORMAT,
            'version'  => defined( 'FFW_VERSION' ) ? FFW_VERSION : '',
            'pro'      => ffw_is_pro_activated(),
            'site'     => home_url( '/' ),
            'exported' => gmdate( 'c' ),
            'faq_page' => (object) $page,
            'settings' => (object) $settings,
        );

        /**
         * Filters the settings export before it is downloaded.
         *
         * @since 3.0.1
         * @param array $file Export file.
         */
        $file = apply_filters( 'ffw_settings_export', $file );

        return rest_ensure_response(
            array(
                'name'  => sanitize_file_name( 'happy-faqs-settings-' . ( $host ? $host : 'site' ) . '-' . wp_date( 'Y-m-d' ) . '.json' ),
                'count' => count( $settings ),
                'file'  => $file,
            )
        );
    }

    /* ------------------------------------------------------------------
     * Import.
     * ------------------------------------------------------------------ */

    /**
     * Preview an import, or run it.
     *
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response|WP_Error
     */
    public function import( $request ) {
        $file = self::read_file( (string) $request->get_param( 'file' ) );
        if ( is_wp_error( $file ) ) {
            return $file;
        }

        $current = self::options();
        $result  = self::merge( $file, $current );
        $changes = self::changes( $current, $result['settings'] );

        $response = array(
            'site'     => isset( $file['site'] ) && is_string( $file['site'] ) ? esc_url_raw( $file['site'] ) : '',
            'version'  => isset( $file['version'] ) && is_string( $file['version'] ) ? sanitize_text_field( $file['version'] ) : '',
            'exported' => self::file_date( $file ),
            'count'    => count( $file['settings'] ),
            'changes'  => $changes,
            'notes'    => $result['notes'],
        );

        if ( $request->get_param( 'preview' ) ) {
            return rest_ensure_response( $response );
        }

        update_option(
            self::BACKUP,
            array(
                'time'     => time(),
                'settings' => $current,
            ),
            false
        );

        $response += self::store( $result['settings'] );

        /**
         * Fires after settings are imported from a file.
         *
         * @since 3.0.1
         * @param array $settings Settings now saved.
         * @param array $current  Settings before the import.
         */
        do_action( 'ffw_settings_imported', $result['settings'], $current );

        return rest_ensure_response( $response );
    }

    /**
     * Put back the settings saved before the last import.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function restore() {
        $backup = get_option( self::BACKUP );
        if ( ! is_array( $backup ) || ! isset( $backup['settings'] ) || ! is_array( $backup['settings'] ) ) {
            return new WP_Error( 'ffw_no_backup', __( 'There is no import to undo.', 'faq-for-woocommerce' ), array( 'status' => 404 ) );
        }

        delete_option( self::BACKUP );

        return rest_ensure_response( self::store( $backup['settings'] ) );
    }

    /**
     * Save the settings and return what the page needs.
     *
     * @param array $settings Settings.
     * @return array
     */
    protected static function store( $settings ) {
        update_option( FFW_App_Settings::OPTION, $settings );

        $object = FFW_App_Settings::settings_object();
        if ( $object ) {
            $object->options = $settings;
        }

        /** This action is documented in includes/admin/app/class-ffw-app-settings.php */
        do_action( 'ffw_settings_saved', $settings );

        return array(
            'values'   => FFW_App_Settings::values( self::options() ),
            'transfer' => self::data(),
        );
    }

    /**
     * Read and check an uploaded file.
     *
     * @param string $text File contents.
     * @return array|WP_Error
     */
    protected static function read_file( $text ) {
        if ( strlen( $text ) > self::MAX_SIZE ) {
            return self::file_error( __( 'That file is too large to be a Happy FAQs settings file.', 'faq-for-woocommerce' ) );
        }

        $file = json_decode( preg_replace( '/^\xEF\xBB\xBF/', '', $text ), true, 12 );
        if ( ! is_array( $file ) ) {
            return self::file_error( __( 'That file could not be read. Choose the .json file you exported from Happy FAQs → Settings → Import / Export.', 'faq-for-woocommerce' ) );
        }

        if ( ! isset( $file['plugin'], $file['type'] ) || 'happy-faqs' !== $file['plugin'] || 'settings' !== $file['type'] ) {
            if ( isset( $file['faqs'] ) ) {
                return self::file_error( __( 'That file holds FAQs, not settings. Import it on Happy FAQs → Import / Export (Pro).', 'faq-for-woocommerce' ) );
            }
            return self::file_error( __( 'That is not a Happy FAQs settings file.', 'faq-for-woocommerce' ) );
        }

        if ( ! isset( $file['format'] ) || ! is_int( $file['format'] ) || $file['format'] < 1 ) {
            return self::file_error( __( 'That settings file is damaged.', 'faq-for-woocommerce' ) );
        }

        if ( $file['format'] > self::FORMAT ) {
            return self::file_error( __( 'That file was made by a newer version of Happy FAQs. Update the plugin on this site, then import it.', 'faq-for-woocommerce' ) );
        }

        if ( ! isset( $file['settings'] ) || ! is_array( $file['settings'] ) || count( $file['settings'] ) > self::MAX_KEYS ) {
            return self::file_error( __( 'That settings file is damaged.', 'faq-for-woocommerce' ) );
        }

        if ( ! $file['settings'] ) {
            return self::file_error( __( 'That file has no settings in it.', 'faq-for-woocommerce' ) );
        }

        return $file;
    }

    /**
     * A file error.
     *
     * @param string $message Message.
     * @return WP_Error
     */
    protected static function file_error( $message ) {
        return new WP_Error( 'ffw_settings_file', $message, array( 'status' => 400 ) );
    }

    /**
     * The settings this site has after importing a file.
     *
     * @param array $file    Checked file.
     * @param array $current Saved settings.
     * @return array settings, notes
     */
    protected static function merge( $file, $current ) {
        $fields  = self::fields_by_key();
        $pro     = ffw_is_pro_activated();
        $next    = array();
        $notes   = array();
        $skipped = array();
        $no_role = array();

        foreach ( $file['settings'] as $key => $value ) {
            $key = (string) $key;
            if ( ! preg_match( '/^[A-Za-z0-9_\-]+$/', $key ) || null === $value ) {
                continue;
            }

            // Same as what is saved: nothing to clean or convert.
            if ( array_key_exists( $key, $current ) && $current[ $key ] === $value ) {
                $next[ $key ] = $value;
                continue;
            }

            $field = isset( $fields[ $key ] ) ? $fields[ $key ] : array();
            $value = self::clean( $value, $field, array_key_exists( $key, $current ) ? $current[ $key ] : null );

            if ( null === $value ) {
                $skipped[] = self::label( $key, $field );
                $value     = isset( $current[ $key ] ) ? $current[ $key ] : null;
            }

            if ( null !== $value ) {
                $next[ $key ] = $value;
            }
        }

        if ( $skipped ) {
            /* translators: %s: setting names */
            $notes[] = sprintf( __( 'Not valid in the file, so kept as they are: %s.', 'faq-for-woocommerce' ), implode( ', ', $skipped ) );
        }

        // User roles this site does not have.
        if ( isset( $next['ffw_set_role'] ) && is_array( $next['ffw_set_role'] ) ) {
            $roles   = array_map( 'strval', $next['ffw_set_role'] );
            $no_role = array_values( array_diff( $roles, array_keys( wp_roles()->get_names() ) ) );
            if ( $no_role ) {
                $roles = array_values( array_diff( $roles, $no_role ) );
                /* translators: %s: user role names */
                $notes[] = sprintf( __( 'These user roles are not on this site and are left out: %s.', 'faq-for-woocommerce' ), implode( ', ', $no_role ) );
                if ( $roles ) {
                    $next['ffw_set_role'] = $roles;
                } else {
                    self::keep( $next, $current, 'ffw_set_role' );
                }
            }
        }

        // The FAQ page: the same page, or one with the same address.
        $page_id = isset( $next['ffw_faq_page'] ) ? (int) $next['ffw_faq_page'] : 0;
        if ( $page_id ) {
            $found = self::find_page( $file, $page_id );
            if ( $found ) {
                $next['ffw_faq_page'] = (string) $found;
            } else {
                self::keep( $next, $current, 'ffw_faq_page' );
                $title   = isset( $file['faq_page']['title'] ) && is_string( $file['faq_page']['title'] ) ? sanitize_text_field( $file['faq_page']['title'] ) : '';
                $notes[] = $title
                    /* translators: %s: page title */
                    ? sprintf( __( 'This site has no page called "%s", so the FAQ page setting stays as it is. You can pick one in General.', 'faq-for-woocommerce' ), $title )
                    : __( 'The FAQ page in the file is not on this site, so that setting stays as it is. You can pick one in General.', 'faq-for-woocommerce' );
            }
        }

        // API keys left out of the file stay.
        $kept_secret = false;
        foreach ( $current as $key => $value ) {
            if ( self::is_secret( $key ) && ( ! isset( $next[ $key ] ) || '' === $next[ $key ] ) ) {
                $next[ $key ] = $value;
                $kept_secret  = $kept_secret || '' !== $value;
            }
        }
        if ( $kept_secret ) {
            $notes[] = __( 'The file has no API key, so the one saved here stays.', 'faq-for-woocommerce' );
        }

        // A file from a site without Pro says nothing about Pro settings.
        if ( $pro && empty( $file['pro'] ) ) {
            $kept_pro = false;
            foreach ( $current as $key => $value ) {
                $is_pro = isset( $fields[ $key ] ) ? ! empty( $fields[ $key ]['pro'] ) : 'ffw_layout' !== $key;
                if ( $is_pro && ! isset( $next[ $key ] ) ) {
                    $next[ $key ] = $value;
                    $kept_pro     = true;
                }
            }
            if ( $kept_pro ) {
                $notes[] = __( 'The file comes from a site without Happy FAQs Pro, so Pro settings that are not in it stay as they are.', 'faq-for-woocommerce' );
            }
        }

        // Without Pro, a Pro layout or position would leave FAQs with nowhere to show.
        if ( ! $pro ) {
            $locked = array();
            if ( isset( $next['ffw_layout'] ) && (int) $next['ffw_layout'] > 5 ) {
                self::keep( $next, $current, 'ffw_layout' );
                $locked[] = self::label( 'ffw_layout', array() );
            }
            foreach ( $fields as $key => $field ) {
                if ( empty( $next[ $key ] ) || empty( $field['options'] ) || ! empty( $field['pro'] ) ) {
                    continue;
                }
                foreach ( $field['options'] as $option ) {
                    if ( ! empty( $option['locked'] ) && (string) $option['value'] === self::plain( $next[ $key ] ) ) {
                        self::keep( $next, $current, $key );
                        $locked[] = self::label( $key, $field );
                    }
                }
            }
            if ( $locked ) {
                /* translators: %s: setting names */
                $notes[] = sprintf( __( 'These choices need Happy FAQs Pro, so they stay as they are: %s.', 'faq-for-woocommerce' ), implode( ', ', $locked ) );
            }
            foreach ( $next as $key => $value ) {
                if ( ! empty( $fields[ $key ]['pro'] ) && ( ! isset( $current[ $key ] ) || self::plain( $current[ $key ] ) !== self::plain( $value ) ) ) {
                    $notes[] = __( 'Pro settings in the file are saved too. They start working when Happy FAQs Pro is active.', 'faq-for-woocommerce' );
                    break;
                }
            }
        }

        // Clean what came from the file. Values that did not change keep their exact form.
        $object = FFW_App_Settings::settings_object();
        $clean  = $object ? $object->sanitize_general_settings( $next ) : array_map( 'sanitize_text_field', array_filter( $next, 'is_string' ) );
        foreach ( $clean as $key => $value ) {
            if ( array_key_exists( $key, $current ) && $current[ $key ] === $next[ $key ] ) {
                $clean[ $key ] = $current[ $key ];
            }
        }

        /**
         * Filters the settings an import will save.
         *
         * @since 3.0.1
         * @param array $clean   Settings after the import.
         * @param array $file    Import file.
         * @param array $current Saved settings.
         */
        $clean = (array) apply_filters( 'ffw_settings_import', $clean, $file, $current );

        return array(
            'settings' => $clean,
            'notes'    => $notes,
        );
    }

    /**
     * Keep this site's value of a setting.
     *
     * @param array  $next    Settings after the import.
     * @param array  $current Saved settings.
     * @param string $key     Key.
     * @return void
     */
    protected static function keep( &$next, $current, $key ) {
        if ( array_key_exists( $key, $current ) ) {
            $next[ $key ] = $current[ $key ];
        } else {
            unset( $next[ $key ] );
        }
    }

    /**
     * A value in the shape its setting is stored in, or null.
     *
     * @param mixed $value   Value from the file.
     * @param array $field   Field description, if known.
     * @param mixed $current Saved value.
     * @return mixed
     */
    protected static function clean( $value, $field, $current ) {
        if ( is_array( $value ) ) {
            foreach ( $value as $item ) {
                if ( ! is_scalar( $item ) ) {
                    return null;
                }
            }
        }

        if ( $field ) {
            // A choice this version does not have would leave the setting blank.
            $choice  = self::plain( $value );
            $choices = self::choices( $field );
            if ( '' !== $choice && $choices && ! in_array( $choice, $choices, true ) ) {
                return null;
            }

            $list = ! empty( $field['array'] ) || ( isset( $field['type'] ) && 'roles' === $field['type'] );
            if ( $list ) {
                return array_values( array_map( 'strval', is_array( $value ) ? $value : array( $value ) ) );
            }
            if ( is_array( $value ) ) {
                return null;
            }
            return is_bool( $value ) ? ( $value ? 'on' : '' ) : (string) $value;
        }

        if ( null !== $current && is_array( $current ) !== is_array( $value ) ) {
            return null;
        }
        return is_bool( $value ) ? ( $value ? 'on' : '' ) : $value;
    }

    /**
     * Values a field with fixed choices accepts. Empty when any value is fine.
     *
     * @param array $field Field.
     * @return string[]
     */
    protected static function choices( $field ) {
        $type = isset( $field['type'] ) ? $field['type'] : '';
        // AI models come and go, so any name is kept.
        if ( isset( $field['key'] ) && 'ffw_ai_faqs_model' === $field['key'] ) {
            return array();
        }
        if ( 'icons' === $type ) {
            return array_map( 'strval', array_keys( FAQ_Woocommerce_Icons::choices() ) );
        }
        if ( 'toggle' === $type && ! empty( $field['map'] ) ) {
            return array_map( 'strval', array_values( $field['map'] ) );
        }
        if ( ! in_array( $type, array( 'select', 'segment' ), true ) || empty( $field['options'] ) ) {
            return array();
        }
        return array_map( 'strval', wp_list_pluck( $field['options'], 'value' ) );
    }

    /**
     * The page on this site for the file's FAQ page.
     *
     * @param array $file    File.
     * @param int   $page_id Page ID in the file.
     * @return int
     */
    protected static function find_page( $file, $page_id ) {
        $here = isset( $file['site'] ) && is_string( $file['site'] ) && untrailingslashit( $file['site'] ) === untrailingslashit( home_url() );
        if ( $here && 'page' === get_post_type( $page_id ) && 'trash' !== get_post_status( $page_id ) ) {
            return $page_id;
        }

        $path = isset( $file['faq_page']['path'] ) && is_string( $file['faq_page']['path'] ) ? $file['faq_page']['path'] : '';
        $page = '' !== $path ? get_page_by_path( $path ) : null;
        return $page && 'trash' !== $page->post_status ? (int) $page->ID : 0;
    }

    /* ------------------------------------------------------------------
     * Preview.
     * ------------------------------------------------------------------ */

    /**
     * What an import changes, in the words of the Settings page.
     *
     * @param array $before Saved settings.
     * @param array $after  Settings after the import.
     * @return array
     */
    protected static function changes( $before, $after ) {
        $fields   = self::fields_by_key();
        $sections = self::sections_by_key();
        $changes  = array();

        foreach ( array_keys( $before + $after ) as $key ) {
            $field = isset( $fields[ $key ] ) ? $fields[ $key ] : array();
            $old   = self::effective( isset( $before[ $key ] ) ? $before[ $key ] : null, $field );
            $new   = self::effective( isset( $after[ $key ] ) ? $after[ $key ] : null, $field );

            if ( self::plain( $old ) === self::plain( $new ) ) {
                continue;
            }

            $changes[] = array(
                'key'     => (string) $key,
                'label'   => self::label( $key, $field ),
                'section' => isset( $sections[ $key ] ) ? $sections[ $key ] : __( 'Other', 'faq-for-woocommerce' ),
                'from'    => self::show( $key, $old, $field ),
                'to'      => self::show( $key, $new, $field ),
            );
        }

        // In the order of the Settings page.
        $order = array_flip( array_keys( $sections ) );
        usort(
            $changes,
            static function ( $a, $b ) use ( $order ) {
                $x = isset( $order[ $a['key'] ] ) ? $order[ $a['key'] ] : PHP_INT_MAX;
                $y = isset( $order[ $b['key'] ] ) ? $order[ $b['key'] ] : PHP_INT_MAX;
                return $x === $y ? strcmp( $a['key'], $b['key'] ) : ( $x < $y ? -1 : 1 );
            }
        );

        return $changes;
    }

    /**
     * A saved value with the fallback the plugin uses.
     *
     * @param mixed $value Saved value or null.
     * @param array $field Field.
     * @return mixed
     */
    protected static function effective( $value, $field ) {
        $type = isset( $field['type'] ) ? $field['type'] : '';
        if ( 'switch' === $type ) {
            return in_array( strtolower( self::plain( $value ) ), array( 'on', '1', 'yes', 'true' ), true ) ? 'on' : '';
        }
        if ( null === $value || ( '' === $value && isset( $field['key'] ) && 0 === strpos( $field['key'], 'ffw_comment' ) ) ) {
            return isset( $field['default'] ) ? $field['default'] : '';
        }
        return $value;
    }

    /**
     * A value as one string, for comparing.
     *
     * @param mixed $value Value.
     * @return string
     */
    protected static function plain( $value ) {
        return is_array( $value ) ? implode( ',', array_map( 'strval', array_filter( $value, 'is_scalar' ) ) ) : (string) $value;
    }

    /**
     * A value for people.
     *
     * @param string $key   Key.
     * @param mixed  $value Value.
     * @param array  $field Field.
     * @return string
     */
    protected static function show( $key, $value, $field ) {
        $plain = self::plain( $value );
        $type  = isset( $field['type'] ) ? $field['type'] : '';

        if ( self::is_secret( $key ) ) {
            return '' === $plain ? __( 'None', 'faq-for-woocommerce' ) : __( 'A saved key', 'faq-for-woocommerce' );
        }

        if ( 'switch' === $type || 'toggle' === $type ) {
            $on = isset( $field['map']['on'] ) ? (string) $field['map']['on'] : 'on';
            return $plain === $on ? __( 'On', 'faq-for-woocommerce' ) : __( 'Off', 'faq-for-woocommerce' );
        }

        if ( 'page' === $type ) {
            return (int) $plain ? wp_strip_all_tags( get_the_title( (int) $plain ) ) : __( 'No page', 'faq-for-woocommerce' );
        }

        if ( 'roles' === $type ) {
            $names = wp_roles()->get_names();
            $out   = array();
            foreach ( (array) $value as $role ) {
                $out[] = isset( $names[ $role ] ) ? translate_user_role( $names[ $role ] ) : $role;
            }
            return implode( ', ', $out );
        }

        if ( 'icons' === $type ) {
            $icons = FAQ_Woocommerce_Icons::choices();
            return isset( $icons[ $plain ] ) ? $icons[ $plain ] : $plain;
        }

        if ( 'ffw_layout' === $key ) {
            $layouts = FFW_App_Shortcodes::layouts();
            $index   = (int) $plain ? (int) $plain - 1 : 0;
            return isset( $layouts[ $index ] ) ? $layouts[ $index ]['name'] : '#' . $plain;
        }

        if ( ! empty( $field['options'] ) ) {
            foreach ( $field['options'] as $option ) {
                if ( (string) $option['value'] === $plain ) {
                    return $option['label'];
                }
            }
        }

        $text = trim( preg_replace( '/\s+/', ' ', wp_specialchars_decode( wp_strip_all_tags( $plain ), ENT_QUOTES ) ) );
        if ( '' === $text ) {
            return in_array( $type, array( 'color', 'range', 'number' ), true ) ? __( 'Default', 'faq-for-woocommerce' ) : __( 'Empty', 'faq-for-woocommerce' );
        }
        return mb_strlen( $text ) > 70 ? mb_substr( $text, 0, 70 ) . '…' : $text;
    }

    /**
     * Name of a setting.
     *
     * @param string $key   Key.
     * @param array  $field Field.
     * @return string
     */
    protected static function label( $key, $field ) {
        $own = array(
            'ffw_layout'        => __( 'Layout', 'faq-for-woocommerce' ),
            'ffw_icon_position' => __( 'Icon position', 'faq-for-woocommerce' ),
            'ffw_icon_size'     => __( 'Icon size', 'faq-for-woocommerce' ),
            'ffw_icon_color'    => __( 'Icon color', 'faq-for-woocommerce' ),
        );
        if ( isset( $own[ $key ] ) ) {
            return $own[ $key ];
        }
        if ( ! empty( $field['label'] ) ) {
            return $field['label'];
        }

        // Fields registered by other plugins: their id is the key, often with a prefix.
        foreach ( self::registered() as $id => $label ) {
            if ( $id === $key || 'ffw_' . $key === $id ) {
                return $label;
            }
        }
        return ucfirst( trim( str_replace( array( 'ffw_', '_', '-' ), array( '', ' ', ' ' ), $key ) ) );
    }

    /* ------------------------------------------------------------------
     * Helpers.
     * ------------------------------------------------------------------ */

    /**
     * Saved settings.
     *
     * @return array
     */
    protected static function options() {
        $options = get_option( FFW_App_Settings::OPTION );
        return is_array( $options ) ? $options : array();
    }

    /**
     * Described fields by option key.
     *
     * @return array
     */
    protected static function fields_by_key() {
        static $out = null;
        if ( null === $out ) {
            $out = array();
            foreach ( FFW_App_Settings::described_fields() as $field ) {
                if ( ! empty( $field['key'] ) ) {
                    $out[ $field['key'] ] = $field;
                }
                foreach ( empty( $field['keys'] ) ? array() : $field['keys'] as $key ) {
                    $out[ $key ] = array(
                        'key'  => $key,
                        'type' => 'ffw_icon_position' === $key ? 'segment' : ( 'ffw_icon_color' === $key ? 'color' : 'number' ),
                    );
                }
            }
            if ( isset( $out['ffw_icon_position'] ) ) {
                $out['ffw_icon_position'] += array(
                    'default' => 'right',
                    'options' => array(
                        array( 'value' => 'left', 'label' => __( 'Left', 'faq-for-woocommerce' ) ),
                        array( 'value' => 'right', 'label' => __( 'Right', 'faq-for-woocommerce' ) ),
                    ),
                );
            }
        }
        return $out;
    }

    /**
     * Section name of each option key, in the order of the page.
     *
     * @return array
     */
    protected static function sections_by_key() {
        $fields = FFW_App_Settings::described_fields();
        $out    = array();
        foreach ( FFW_App_Settings::layout() as $section ) {
            // Chosen on Templates, not on this page.
            if ( 'style' === $section['id'] ) {
                $out['ffw_layout'] = $section['label'];
            }
            foreach ( $section['groups'] as $group ) {
                foreach ( $group['fields'] as $id ) {
                    $field = isset( $fields[ $id ] ) ? $fields[ $id ] : array( 'key' => preg_replace( '/^ffw_/', '', $id ) );
                    foreach ( empty( $field['keys'] ) ? array( isset( $field['key'] ) ? $field['key'] : $id ) : $field['keys'] as $key ) {
                        $out[ $key ] = $section['label'];
                    }
                }
            }
        }
        return $out;
    }

    /**
     * Labels of every registered field, by field id.
     *
     * @return array
     */
    protected static function registered() {
        static $out = null;
        if ( null === $out ) {
            $out    = array();
            $object = FFW_App_Settings::settings_object();
            foreach ( $object ? (array) $object->ffw_get_setting_options() : array() as $sections ) {
                foreach ( (array) $sections as $section ) {
                    foreach ( ! empty( $section['fields'] ) && is_array( $section['fields'] ) ? $section['fields'] : array() as $field ) {
                        if ( ! empty( $field['id'] ) && ! empty( $field['label'] ) ) {
                            $out[ $field['id'] ] = wp_strip_all_tags( $field['label'] );
                        }
                    }
                }
            }
        }
        return $out;
    }

    /**
     * Is this setting an API key or similar?
     *
     * @param string $key Key.
     * @return bool
     */
    protected static function is_secret( $key ) {
        $fields = self::fields_by_key();
        $secret = isset( $fields[ $key ] )
            ? isset( $fields[ $key ]['type'] ) && 'secret' === $fields[ $key ]['type']
            : (bool) preg_match( '/(api_?key|secret|password|token)/i', (string) $key );

        /**
         * Filters whether a setting is secret: left out of exports unless asked for.
         *
         * @since 3.0.1
         * @param bool   $secret Secret.
         * @param string $key    Setting key.
         */
        return (bool) apply_filters( 'ffw_settings_is_secret', $secret, $key );
    }

    /**
     * When the file was exported, as a local date.
     *
     * @param array $file File.
     * @return string
     */
    protected static function file_date( $file ) {
        $time = isset( $file['exported'] ) && is_string( $file['exported'] ) ? strtotime( $file['exported'] ) : false;
        return $time ? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $time ) : '';
    }

    /**
     * The import that can be undone.
     *
     * @return array|null
     */
    protected static function backup_info() {
        $backup = get_option( self::BACKUP );
        if ( ! is_array( $backup ) || empty( $backup['time'] ) || ! isset( $backup['settings'] ) ) {
            return null;
        }
        return array(
            'date' => wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), (int) $backup['time'] ),
        );
    }
}

new FFW_Settings_Transfer();
