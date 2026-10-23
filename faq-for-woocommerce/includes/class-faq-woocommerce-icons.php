<?php
/**
 * Toggle icon sets for FAQ accordions.
 *
 * Every layout shows its own icon unless an icon set is chosen site-wide
 * (Settings → Style) or per block, widget or shortcode. Icons are inline
 * SVG drawn with currentColor and animate between closed and open.
 *
 * @package FAQ_Woocommerce
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FAQ_Woocommerce_Icons class.
 */
class FAQ_Woocommerce_Icons {

    /**
     * Icon settings for the FAQ list being rendered (a stack, lists can nest).
     *
     * @var array[]
     */
    protected static $stack = array();

    /**
     * Icon sets: id => label, motion and SVG body (24×24, currentColor).
     *
     * Motions: rotate-180, rotate-90 and rotate-45 turn the icon; plus folds
     * the vertical bar into a minus; swap shows .ffw-ti-open instead of
     * .ffw-ti-closed; switch slides a knob.
     *
     * @return array
     */
    public static function sets() {
        $plus      = '<path class="ffw-ti-h" d="M6 12h12"/><path class="ffw-ti-v" d="M12 6v12"/>';
        $plus_sm   = '<path class="ffw-ti-h" d="M8 12h8"/><path class="ffw-ti-v" d="M12 8v8"/>';
        $chevron   = '<path d="M6.5 9.5 12 15l5.5-5.5"/>';
        $chev_sm   = '<path d="M8.5 10.5 12 14l3.5-3.5"/>';
        $contrast  = ' style="stroke:var(--ffw-icon-contrast,#fff)"';

        $sets = array(
            'chevron'            => array( __( 'Chevron', 'faq-for-woocommerce' ), 'rotate-180', $chevron ),
            'chevron-bold'       => array( __( 'Chevron bold', 'faq-for-woocommerce' ), 'rotate-180', '<path stroke-width="3" d="M6.5 9.5 12 15l5.5-5.5"/>' ),
            'chevron-circle'     => array( __( 'Chevron in circle', 'faq-for-woocommerce' ), 'rotate-180', '<circle cx="12" cy="12" r="9.25"/>' . $chev_sm ),
            'chevron-solid'      => array( __( 'Chevron in solid circle', 'faq-for-woocommerce' ), 'rotate-180', '<circle cx="12" cy="12" r="10" fill="currentColor" stroke="none"/><path' . $contrast . ' d="M8.5 10.5 12 14l3.5-3.5"/>' ),
            'chevron-square'     => array( __( 'Chevron in square', 'faq-for-woocommerce' ), 'rotate-180', '<rect x="3" y="3" width="18" height="18" rx="5"/>' . $chev_sm ),
            'chevron-right'      => array( __( 'Chevron right', 'faq-for-woocommerce' ), 'rotate-90', '<path d="M9.5 6.5 15 12l-5.5 5.5"/>' ),
            'double-chevron'     => array( __( 'Double chevron', 'faq-for-woocommerce' ), 'rotate-180', '<path d="M7 6.5l5 5 5-5"/><path d="M7 12.5l5 5 5-5"/>' ),
            'arrow'              => array( __( 'Arrow', 'faq-for-woocommerce' ), 'rotate-180', '<path d="M12 5v14"/><path d="M6.5 13.5 12 19l5.5-5.5"/>' ),
            'arrow-circle'       => array( __( 'Arrow in circle', 'faq-for-woocommerce' ), 'rotate-180', '<circle cx="12" cy="12" r="9.25"/><path d="M12 7.5v9"/><path d="M8.5 13 12 16.5 15.5 13"/>' ),
            'arrow-right'        => array( __( 'Arrow right', 'faq-for-woocommerce' ), 'rotate-90', '<path d="M5 12h14"/><path d="M13.5 6.5 19 12l-5.5 5.5"/>' ),
            'caret'              => array( __( 'Caret', 'faq-for-woocommerce' ), 'rotate-180', '<path d="M6 9.5h12L12 16z" fill="currentColor" stroke-width="1.5"/>' ),
            'caret-right'        => array( __( 'Caret right', 'faq-for-woocommerce' ), 'rotate-90', '<path d="M9.5 6v12l6.5-6z" fill="currentColor" stroke-width="1.5"/>' ),
            'caret-circle'       => array( __( 'Caret in circle', 'faq-for-woocommerce' ), 'rotate-180', '<circle cx="12" cy="12" r="9.25"/><path d="M8.5 10.5h7L12 14.5z" fill="currentColor" stroke-width="1.2"/>' ),
            'plus-minus'         => array( __( 'Plus / minus', 'faq-for-woocommerce' ), 'plus', $plus ),
            'plus-minus-light'   => array( __( 'Plus / minus light', 'faq-for-woocommerce' ), 'plus', '<g stroke-width="1.4">' . $plus . '</g>' ),
            'plus-minus-circle'  => array( __( 'Plus / minus in circle', 'faq-for-woocommerce' ), 'plus', '<circle cx="12" cy="12" r="9.25"/>' . $plus_sm ),
            'plus-minus-solid'   => array( __( 'Plus / minus in solid circle', 'faq-for-woocommerce' ), 'plus', '<circle cx="12" cy="12" r="10" fill="currentColor" stroke="none"/><g' . $contrast . '>' . $plus_sm . '</g>' ),
            'plus-minus-square'  => array( __( 'Plus / minus in square', 'faq-for-woocommerce' ), 'plus', '<rect x="3" y="3" width="18" height="18" rx="5"/>' . $plus_sm ),
            'plus-close'         => array( __( 'Plus to close', 'faq-for-woocommerce' ), 'rotate-45', $plus ),
            'plus-close-circle'  => array( __( 'Plus to close in circle', 'faq-for-woocommerce' ), 'rotate-45', '<circle cx="12" cy="12" r="9.25"/>' . $plus_sm ),
            'folder'             => array(
                __( 'Folder', 'faq-for-woocommerce' ),
                'swap',
                '<g class="ffw-ti-closed"><path d="M3.5 7.5a2 2 0 0 1 2-2h4l2 2.5h7a2 2 0 0 1 2 2v7.5a2 2 0 0 1-2 2h-13a2 2 0 0 1-2-2z"/></g>'
                . '<g class="ffw-ti-open"><path d="M3.5 17.5v-10a2 2 0 0 1 2-2h4l2 2.5h5.5a2 2 0 0 1 2 2V11"/><path d="M3.5 17.5 6 11.5a1.5 1.5 0 0 1 1.4-1h12.1a1 1 0 0 1 .9 1.4l-2.2 5.2a1.5 1.5 0 0 1-1.4.9z"/></g>',
            ),
            'switch'             => array(
                __( 'Switch', 'faq-for-woocommerce' ),
                'switch',
                '<rect x="2.5" y="7" width="19" height="10" rx="5"/><circle class="ffw-ti-knob" cx="7.5" cy="12" r="3" fill="currentColor" stroke="none"/>',
            ),
        );

        /**
         * Add or change toggle icon sets.
         *
         * @param array $sets id => array( label, motion, svg body ).
         */
        return (array) apply_filters( 'ffw_toggle_icon_sets', $sets );
    }

    /**
     * Choices for selects: default, none, then every set.
     *
     * @return array id => label
     */
    public static function choices() {
        $choices = array(
            'default' => __( 'Layout default', 'faq-for-woocommerce' ),
            'none'    => __( 'No icon', 'faq-for-woocommerce' ),
        );

        foreach ( self::sets() as $id => $set ) {
            $choices[ $id ] = $set[0];
        }

        return $choices;
    }

    /**
     * Valid icon set id, or "default".
     *
     * @param mixed $set Raw value.
     * @return string
     */
    public static function sanitize_set( $set ) {
        $set = sanitize_key( (string) $set );

        return array_key_exists( $set, self::choices() ) ? $set : 'default';
    }

    /**
     * Normalized icon settings. Empty values fall back to Settings → Style.
     *
     * @param array $args icon_set ("" = site setting, "default" = layout icon), icon_position, icon_color, icon_size.
     * @return array
     */
    public static function resolve( $args = array() ) {
        $args    = is_array( $args ) ? $args : array();
        $options = get_option( 'ffw_general_settings', array() );
        $options = is_array( $options ) ? $options : array();

        // An empty block, widget or shortcode value uses the site setting.
        $pick = static function ( $key, $option ) use ( $args, $options ) {
            if ( isset( $args[ $key ] ) && '' !== (string) $args[ $key ] && '0' !== (string) $args[ $key ] ) {
                return $args[ $key ];
            }
            return isset( $options[ $option ] ) ? $options[ $option ] : '';
        };

        $color = $pick( 'icon_color', 'ffw_icon_color' );
        $color = function_exists( 'ffw_sanitize_css_color' ) ? ffw_sanitize_css_color( $color ) : sanitize_hex_color( (string) $color );

        return array(
            'set'      => self::sanitize_set( $pick( 'icon_set', 'ffw_icon_set' ) ),
            'position' => 'left' === $pick( 'icon_position', 'ffw_icon_position' ) ? 'left' : 'right',
            'color'    => (string) $color,
            'size'     => min( 48, absint( $pick( 'icon_size', 'ffw_icon_size' ) ) ),
        );
    }

    /**
     * Whether resolved settings change the layout's own icon.
     *
     * @param array $icon Resolved settings.
     * @return bool
     */
    public static function is_custom( $icon ) {
        return 'default' !== $icon['set'];
    }

    /**
     * Start rendering a FAQ list with these icon settings.
     *
     * @param array $icon Resolved settings.
     * @return void
     */
    public static function push( $icon ) {
        self::$stack[] = $icon;
    }

    /**
     * Finish rendering a FAQ list.
     *
     * @return void
     */
    public static function pop() {
        array_pop( self::$stack );
    }

    /**
     * Settings for the list being rendered, or the site-wide ones.
     *
     * @return array
     */
    public static function current() {
        return self::$stack ? end( self::$stack ) : self::resolve();
    }

    /**
     * Toggle icon markup for one FAQ.
     *
     * @param string $default_html The layout's own icon (kept when no set is chosen).
     * @return string
     */
    public static function html( $default_html = '' ) {
        $icon = self::current();

        if ( ! self::is_custom( $icon ) ) {
            return (string) $default_html;
        }

        if ( 'none' === $icon['set'] ) {
            return '';
        }

        return self::svg( $icon['set'] );
    }

    /**
     * One icon as inline SVG.
     *
     * @param string $set Set id.
     * @return string
     */
    public static function svg( $set ) {
        $sets = self::sets();
        if ( ! isset( $sets[ $set ] ) ) {
            return '';
        }

        return sprintf(
            '<span class="ffw-toggle-icon ffw-ti-%1$s ffw-ti-m-%2$s" aria-hidden="true"><svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">%3$s</svg></span>',
            esc_attr( $set ),
            esc_attr( $sets[ $set ][1] ),
            $sets[ $set ][2] // Static markup defined above.
        );
    }

    /**
     * Wrapper classes for a list with these settings.
     *
     * @param array $icon Resolved settings.
     * @return string[]
     */
    public static function wrapper_classes( $icon ) {
        if ( ! self::is_custom( $icon ) ) {
            return array();
        }

        $classes = array( 'ffw-has-toggle-icons', 'ffw-icons-' . $icon['set'] );
        if ( 'left' === $icon['position'] ) {
            $classes[] = 'ffw-icon-left';
        }

        return $classes;
    }

    /**
     * Inline CSS variables for color and size.
     *
     * @param array $icon Resolved settings.
     * @return string
     */
    public static function wrapper_style( $icon ) {
        if ( ! self::is_custom( $icon ) ) {
            return '';
        }

        $style = '';
        if ( '' !== $icon['color'] ) {
            $style .= '--ffw-icon-color:' . $icon['color'] . ';';
        }
        if ( $icon['size'] >= 10 ) {
            $style .= '--ffw-icon-size:' . $icon['size'] . 'px;';
        }

        return $style;
    }

    /**
     * Register the icon stylesheet.
     *
     * @return void
     */
    public static function register_style() {
        if ( ! wp_style_is( 'ffw-toggle-icons', 'registered' ) ) {
            wp_register_style( 'ffw-toggle-icons', FFW_PLUGIN_URL . '/assets/public/css/ffw-icons.css', array(), FFW_VERSION );
        }
    }
}

if ( ! function_exists( 'ffw_get_toggle_icon' ) ) {
    /**
     * Toggle icon for a FAQ in a layout template.
     *
     * @since 3.0.0
     * @param string $default_html The layout's own icon.
     * @return string
     */
    function ffw_get_toggle_icon( $default_html = '' ) {
        return FAQ_Woocommerce_Icons::html( $default_html );
    }
}
