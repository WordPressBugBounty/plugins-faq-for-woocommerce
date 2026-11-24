<?php
/**
 * FAQ Woocommerce Schema
 *
 * @class    FAQ_Woocommerce_Schema
 * @package  FAQ_Woocommerce\Schema
 * @version  1.2.2
 *
 * @link    https://wpfeel.net/
 * @since   1.2.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * FAQ_Woocommerce_Schema class.
 */
class FAQ_Woocommerce_Schema {
    /**
     * Faq list.
     *
     * @var array
     */
    protected $faqs = array();

    /**
     * Display Type.
     *
     * @var array
     */
    protected $disply_type = "";

    /**
     * Schema Enable/Disable.
     *
     * @var mixed
     */
    protected $schema_enable = true;

    /**
     * FFW Settings Options
     *
     * @var mixed
     */
    public $options;

    /**
     * Collected FAQ entries keyed by FAQ ID (or content hash).
     *
     * @var array
     */
    protected static $collected_faqs = array();

    /**
     * Whether the footer output hook is registered.
     *
     * @var bool
     */
    protected static $footer_hooked = false;

    /**
     * Constructor.
     */
    public function __construct($faqs, $type) {
        $this->faqs = !empty($faqs) ? $faqs : $this->faqs;
        $this->disply_type = $type;
        $this->schema_enable = $this->get_schema_setting();
        $this->options  = get_option( 'ffw_general_settings' );
        $this->collect_faqs();
    }

    /**
     * Get schema setting.
     *
     * @return boolean
     */
    public function get_schema_setting() {
        $options        = get_option( 'ffw_general_settings' );
        $options        = ! empty( $options ) ? $options : [];
        $enable_schema  = array_key_exists( 'ffw_disable_schema', $options ) && ! empty($options['ffw_disable_schema']) ? (int) $options['ffw_disable_schema'] : 1;

        return isset($enable_schema) && 1 === $enable_schema ? true : false;
    }

    /**
     * Collect FAQs and print a single FAQPage JSON-LD in the footer.
     *
     * @since 1.8.18
     * @return void
     */
    protected function collect_faqs() {
        if ( ! $this->schema_enable || empty( $this->faqs ) || ! is_array( $this->faqs ) ) {
            return;
        }

        foreach ( $this->faqs as $faq ) {
            if ( ! is_array( $faq ) ) {
                continue;
            }

            $faq_id = isset( $faq['id'] ) ? (int) $faq['id'] : 0;
            $key    = $faq_id > 0
                ? (string) $faq_id
                : md5( ( isset( $faq['question'] ) ? $faq['question'] : '' ) . ( isset( $faq['answer'] ) ? $faq['answer'] : '' ) );

            if ( isset( self::$collected_faqs[ $key ] ) ) {
                continue;
            }

            self::$collected_faqs[ $key ] = $faq;
        }

        if ( ! self::$footer_hooked ) {
            self::$footer_hooked = true;
            add_action( 'wp_footer', array( __CLASS__, 'output_schema' ), 20 );
        }
    }

    /**
     * Whether a FAQ is already in this page's FAQPage schema.
     *
     * Lets add-ons add related questions (for example nested FAQs) only
     * where their parent is in the schema.
     *
     * @since 2.1.0
     * @param int $faq_id FAQ post ID.
     * @return bool
     */
    public static function is_collected( $faq_id ) {
        return isset( self::$collected_faqs[ (string) (int) $faq_id ] );
    }

    /**
     * Output one FAQPage JSON-LD block for all collected FAQs.
     *
     * @since 1.8.18
     * @return void
     */
    public static function output_schema() {
        if ( empty( self::$collected_faqs ) ) {
            return;
        }

        $seo_plugin = self::seo_faq_block_on_page();
        if ( $seo_plugin ) {
            printf( "\n<!-- Happy FAQs: FAQ schema skipped, the %s FAQ block on this page already adds it -->\n", esc_html( $seo_plugin ) );
            return;
        }

        $schema = new self( array(), 'footer' );
        $schema->schema_enable = true;
        $schema->options       = get_option( 'ffw_general_settings' );
        $schema->process_faqs_schema( array_values( self::$collected_faqs ), 'footer' );
    }

    /**
     * Name of the SEO plugin whose FAQ block is on this page, if any.
     *
     * Yoast SEO and Rank Math add FAQPage schema for their own FAQ blocks.
     * A second FAQPage block on the same page is invalid for Google, so
     * Happy FAQs leaves the schema to them on those pages.
     *
     * @since 3.0.1
     * @return string Plugin name, or an empty string.
     */
    public static function seo_faq_block_on_page() {
        $plugin = '';

        if ( is_singular() && function_exists( 'has_block' ) ) {
            $post_id = get_queried_object_id();

            if ( defined( 'WPSEO_VERSION' ) && has_block( 'yoast/faq-block', $post_id ) ) {
                $plugin = 'Yoast SEO';
            } elseif ( class_exists( 'RankMath' ) && has_block( 'rank-math/faq-block', $post_id ) ) {
                $plugin = 'Rank Math';
            }
        }

        /**
         * Filters whether another plugin's FAQ block already adds FAQPage schema to this page.
         *
         * Return a plugin name to skip Happy FAQs schema, or an empty string to keep it.
         *
         * @since 3.0.1
         * @param string $plugin Detected plugin name, or an empty string.
         */
        return (string) apply_filters( 'ffw_seo_faq_block_on_page', $plugin );
    }

    /**
     * Display FAQ
     *
     * @deprecated 1.8.18 Schema is collected and printed in wp_footer.
     */
    public function display_schema() {
        $this->collect_faqs();
    }

    /**
     * Process FAQs Schema to display
     *
     * @since 1.3.29
     */
    public function process_faqs_schema($faqs, $type) {
        $options            = ! empty( $this->options ) ? $this->options : [];
        $schema_desc_type   = array_key_exists( 'ffw_schema_description_type', $options ) && ! empty($options['ffw_schema_description_type']) ? $options['ffw_schema_description_type'] : 1;

        $faq_lists = [];
        $single_schema_data = [];
        if( sizeof($faqs) > 0 ) {
            foreach ($faqs as $key => $faq_list) {
                $single_schema_data = [];

                if ( is_array( $faq_list ) && isset( $faq_list['question'] ) ) {
                    $question = $faq_list['question'];
                    $answer   = isset( $faq_list['answer'] ) ? $faq_list['answer'] : '';

                    if ( 2 === (int) $schema_desc_type ) {
                        $question = ffw_strip_all_tags( wp_specialchars_decode( $question ) );
                        $answer   = ffw_strip_all_tags( wp_specialchars_decode( $answer ) );
                    }

                    $single_schema_data['@type'] = 'Question';
                    $single_schema_data['name']  = $question;
                    $single_schema_data['acceptedAnswer']['@type'] = 'Answer';
                    $single_schema_data['acceptedAnswer']['text']  = $answer;
                    array_push($faq_lists, $single_schema_data);
                    continue;
                }

                foreach( $faq_list as $faq_key => $faq_value ) {

                    //strip tags and spacial characters
                    if( 2 === (int) $schema_desc_type ) {
                        $faq_value = ffw_strip_all_tags(wp_specialchars_decode($faq_value));
                    }

                    $single_schema_data['@type'] = 'Question';
                    if( 'question' === $faq_key ) {
                        $single_schema_data['name'] = $faq_value;
                    }
                    $single_schema_data['acceptedAnswer']['@type'] = 'Answer';
                    if( 'answer' === $faq_key ) {
                        $single_schema_data['acceptedAnswer']['text'] = $faq_value;
                    }
                }
                array_push($faq_lists, $single_schema_data);
            }
        }

        if( !empty($faq_lists) ) {
            ?>
            <!-- FAQ schema by Happy FAQs -->
            <script type="application/ld+json">
                <?php
                $markup["@context"]     = "https://schema.org";
                $markup["@type"]        = "FAQPage";
                $markup["mainEntity"]   = $faq_lists;
                echo wp_json_encode($markup, JSON_UNESCAPED_SLASHES);
                ?>
                </script>
            <?php
        }
    }

}
