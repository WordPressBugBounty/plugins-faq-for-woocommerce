<?php
/**
 * FAQ insights: answer views and "Was this helpful?" votes.
 *
 * Totals are stored as post meta on each FAQ. No IP addresses or user data
 * are stored; repeat votes are blocked with a short-lived salted hash.
 *
 * @package FAQ_Woocommerce
 * @since   2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FAQ_Woocommerce_Insights class.
 */
class FAQ_Woocommerce_Insights {

    const META_VIEWS = '_ffw_views';
    const META_YES   = '_ffw_helpful_yes';
    const META_NO    = '_ffw_helpful_no';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'wp_ajax_ffw_faq_event', array( $this, 'ajax_event' ) );
        add_action( 'wp_ajax_nopriv_ffw_faq_event', array( $this, 'ajax_event' ) );
        add_action( 'ffw_after_faq_answer', array( $this, 'render_votes' ) );

        add_filter( 'manage_ffw_posts_columns', array( $this, 'columns' ), 20 );
        add_action( 'manage_ffw_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
        add_filter( 'manage_edit-ffw_sortable_columns', array( $this, 'sortable_columns' ) );
        add_action( 'pre_get_posts', array( $this, 'sort_by_column' ) );
    }

    /**
     * Whether "Was this helpful?" votes are shown on the site.
     *
     * @return bool
     */
    public static function votes_enabled() {
        $options = get_option( 'ffw_general_settings', array() );
        $enabled = is_array( $options ) && isset( $options['ffw_enable_helpful_votes'] ) && '1' === (string) $options['ffw_enable_helpful_votes'];

        return (bool) apply_filters( 'ffw_show_helpful_votes', $enabled );
    }

    /**
     * Counts for one FAQ.
     *
     * @param int $faq_id FAQ post ID.
     * @return array { views, yes, no }
     */
    public static function get_counts( $faq_id ) {
        return array(
            'views' => (int) get_post_meta( $faq_id, self::META_VIEWS, true ),
            'yes'   => (int) get_post_meta( $faq_id, self::META_YES, true ),
            'no'    => (int) get_post_meta( $faq_id, self::META_NO, true ),
        );
    }

    /**
     * "Was this helpful?" buttons under a library FAQ answer.
     *
     * @param int $faq_id FAQ post ID.
     * @return void
     */
    public function render_votes( $faq_id ) {
        $faq_id = (int) $faq_id;
        if ( $faq_id <= 0 || ! self::votes_enabled() ) {
            return;
        }
        ?>
        <div class="ffw-helpful" data-ffw-helpful="<?php echo esc_attr( (string) $faq_id ); ?>">
            <span class="ffw-helpful-label"><?php esc_html_e( 'Was this helpful?', 'faq-for-woocommerce' ); ?></span>
            <button type="button" class="ffw-helpful-btn" data-ffw-vote="yes"><?php esc_html_e( 'Yes', 'faq-for-woocommerce' ); ?></button>
            <button type="button" class="ffw-helpful-btn" data-ffw-vote="no"><?php esc_html_e( 'No', 'faq-for-woocommerce' ); ?></button>
            <span class="ffw-helpful-status" role="status" aria-live="polite"></span>
        </div>
        <?php
    }

    /**
     * Record a view or a vote. Public, no nonce, so cached pages keep working.
     *
     * @return void
     */
    public function ajax_event() {
        $faq_id = isset( $_POST['faq_id'] ) ? absint( wp_unslash( $_POST['faq_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $event  = isset( $_POST['event'] ) ? sanitize_key( wp_unslash( $_POST['event'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $object = isset( $_POST['object_id'] ) ? absint( wp_unslash( $_POST['object_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

        $meta_keys = array(
            'view' => self::META_VIEWS,
            'yes'  => self::META_YES,
            'no'   => self::META_NO,
        );

        if ( ! $faq_id || ! isset( $meta_keys[ $event ] ) || 'ffw' !== get_post_type( $faq_id ) || 'publish' !== get_post_status( $faq_id ) ) {
            wp_send_json_error( array( 'message' => 'invalid' ), 400 );
        }

        if ( 'view' !== $event && ! self::votes_enabled() ) {
            wp_send_json_error( array( 'message' => 'disabled' ), 403 );
        }

        // One view per hour and one vote per day for each visitor and FAQ.
        $is_vote = 'view' !== $event;
        $window  = $is_vote ? DAY_IN_SECONDS : HOUR_IN_SECONDS;
        $guard   = 'ffw_evt_' . substr( wp_hash( self::client_fingerprint() . '|' . $faq_id . '|' . ( $is_vote ? 'vote' : 'view' ) ), 0, 32 );

        if ( get_transient( $guard ) ) {
            wp_send_json_success( array( 'counted' => false ) );
        }

        set_transient( $guard, 1, $window );
        self::increment( $faq_id, $meta_keys[ $event ] );

        /**
         * A view or vote was counted.
         *
         * @param int    $faq_id FAQ post ID.
         * @param string $event  view, yes or no.
         * @param int    $object Page or product it happened on (0 if unknown, not validated).
         */
        do_action( 'ffw_faq_event_recorded', $faq_id, $event, $object );

        wp_send_json_success( array( 'counted' => true ) );
    }

    /**
     * Visitor fingerprint for duplicate checks only. It is hashed with the
     * site salt before use and never stored as-is.
     *
     * @return string
     */
    protected static function client_fingerprint() {
        $ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
        $agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

        return $ip . '|' . $agent;
    }

    /**
     * Add 1 to a counter without losing concurrent updates.
     *
     * @param int    $faq_id   FAQ post ID.
     * @param string $meta_key Meta key.
     * @return void
     */
    protected static function increment( $faq_id, $meta_key ) {
        global $wpdb;

        if ( ! add_post_meta( $faq_id, $meta_key, 1, true ) ) {
            $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $wpdb->prepare(
                    "UPDATE {$wpdb->postmeta} SET meta_value = CAST(meta_value AS UNSIGNED) + 1 WHERE post_id = %d AND meta_key = %s",
                    $faq_id,
                    $meta_key
                )
            );
            wp_cache_delete( $faq_id, 'post_meta' );
        }
    }

    /**
     * Views and Helpful columns on All FAQs.
     *
     * @param array $columns Columns.
     * @return array
     */
    public function columns( $columns ) {
        $new = array();
        foreach ( $columns as $key => $label ) {
            if ( 'date' === $key ) {
                $new['ffw_views']   = __( 'Views', 'faq-for-woocommerce' );
                $new['ffw_helpful'] = __( 'Helpful', 'faq-for-woocommerce' );
            }
            $new[ $key ] = $label;
        }

        if ( ! isset( $new['ffw_views'] ) ) {
            $new['ffw_views']   = __( 'Views', 'faq-for-woocommerce' );
            $new['ffw_helpful'] = __( 'Helpful', 'faq-for-woocommerce' );
        }

        return $new;
    }

    /**
     * Column values.
     *
     * @param string $column  Column key.
     * @param int    $post_id FAQ post ID.
     * @return void
     */
    public function column_content( $column, $post_id ) {
        if ( 'ffw_views' !== $column && 'ffw_helpful' !== $column ) {
            return;
        }

        $counts = self::get_counts( $post_id );

        if ( 'ffw_views' === $column ) {
            echo esc_html( number_format_i18n( $counts['views'] ) );
            return;
        }

        $total = $counts['yes'] + $counts['no'];
        if ( ! $total ) {
            echo '<span aria-hidden="true">&mdash;</span><span class="screen-reader-text">' . esc_html__( 'No votes yet', 'faq-for-woocommerce' ) . '</span>';
            return;
        }

        printf(
            '<strong>%1$s%%</strong> <span class="ffw-helpful-votes">%2$s</span>',
            esc_html( number_format_i18n( round( $counts['yes'] / $total * 100 ) ) ),
            esc_html(
                sprintf(
                    /* translators: 1: yes votes, 2: no votes */
                    __( '%1$s yes · %2$s no', 'faq-for-woocommerce' ),
                    number_format_i18n( $counts['yes'] ),
                    number_format_i18n( $counts['no'] )
                )
            )
        );
    }

    /**
     * Make Views and Helpful sortable.
     *
     * @param array $columns Sortable columns.
     * @return array
     */
    public function sortable_columns( $columns ) {
        $columns['ffw_views']   = 'ffw_views';
        $columns['ffw_helpful'] = 'ffw_helpful';

        return $columns;
    }

    /**
     * Order the FAQ list by views or yes votes.
     *
     * A LEFT JOIN keeps FAQs that have no counter yet, sorted as 0.
     *
     * @param WP_Query $query Query.
     * @return void
     */
    public function sort_by_column( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() || 'ffw' !== $query->get( 'post_type' ) ) {
            return;
        }

        $map     = array(
            'ffw_views'   => self::META_VIEWS,
            'ffw_helpful' => self::META_YES,
        );
        $orderby = $query->get( 'orderby' );

        if ( ! is_string( $orderby ) || ! isset( $map[ $orderby ] ) ) {
            return;
        }

        $meta_key = $map[ $orderby ];
        $order    = 'ASC' === strtoupper( (string) $query->get( 'order' ) ) ? 'ASC' : 'DESC';

        add_filter(
            'posts_clauses',
            function ( $clauses, $current ) use ( $query, $meta_key, $order ) {
                global $wpdb;

                if ( $current !== $query ) {
                    return $clauses;
                }

                $clauses['join']   .= $wpdb->prepare( " LEFT JOIN {$wpdb->postmeta} AS ffw_count ON ( ffw_count.post_id = {$wpdb->posts}.ID AND ffw_count.meta_key = %s )", $meta_key );
                $clauses['orderby'] = "COALESCE( CAST( ffw_count.meta_value AS UNSIGNED ), 0 ) {$order}, {$wpdb->posts}.post_date DESC";

                return $clauses;
            },
            10,
            2
        );
    }
}

return new FAQ_Woocommerce_Insights();
