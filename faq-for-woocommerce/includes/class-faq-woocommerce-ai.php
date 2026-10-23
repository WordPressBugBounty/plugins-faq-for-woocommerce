<?php
/**
 * Happy FAQs AI engine.
 *
 * Uses the WordPress AI Client (WordPress 7.0+, providers connected under
 * Settings → Connectors) when it can, so no plugin-specific key is needed.
 * Falls back to an OpenAI API key saved in Happy FAQs → Settings → AI FAQs.
 *
 * @package FAQ_Woocommerce
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * FAQ_Woocommerce_AI class.
 */
class FAQ_Woocommerce_AI {

    const OPENAI_ENDPOINT = 'https://api.openai.com/v1/chat/completions';
    const DEFAULT_MODEL   = 'gpt-4o-mini';
    const MAX_FAQS        = 10;
    const SOURCE_LENGTH   = 8000;

    /**
     * Happy FAQs settings.
     *
     * @return array
     */
    protected static function options() {
        $options = get_option( 'ffw_general_settings', array() );

        return is_array( $options ) ? $options : array();
    }

    /**
     * Whether AI features are switched on in Settings.
     *
     * @return bool
     */
    public static function is_enabled() {
        $options = self::options();

        return ! ( isset( $options['ffw_disable_ai_faqs'] ) && '2' === (string) $options['ffw_disable_ai_faqs'] );
    }

    /**
     * Provider preference: auto, wordpress or openai.
     *
     * @return string
     */
    public static function preference() {
        $options = self::options();
        $pref    = isset( $options['ffw_ai_provider'] ) ? (string) $options['ffw_ai_provider'] : 'auto';

        return in_array( $pref, array( 'auto', 'wordpress', 'openai' ), true ) ? $pref : 'auto';
    }

    /**
     * Whether the WordPress AI Client can generate text right now.
     *
     * @return bool
     */
    public static function wordpress_ai_available() {
        if ( ! function_exists( 'wp_ai_client_prompt' ) || ( function_exists( 'wp_supports_ai' ) && ! wp_supports_ai() ) ) {
            return false;
        }

        try {
            return true === wp_ai_client_prompt( 'Happy FAQs availability check' )->is_supported_for_text_generation();
        } catch ( \Throwable $e ) {
            return false;
        }
    }

    /**
     * Whether WordPress has the AI Client at all (WordPress 7.0+).
     *
     * @return bool
     */
    public static function wordpress_ai_exists() {
        return function_exists( 'wp_ai_client_prompt' );
    }

    /**
     * Saved OpenAI key.
     *
     * @return string
     */
    protected static function openai_key() {
        $options = self::options();

        return isset( $options['ffw_ai_faqs_api_key'] ) ? trim( (string) $options['ffw_ai_faqs_api_key'] ) : '';
    }

    /**
     * Engine that will run: wordpress, openai or none.
     *
     * @return string
     */
    public static function engine() {
        $pref = self::preference();

        if ( 'openai' !== $pref && self::wordpress_ai_available() ) {
            return 'wordpress';
        }

        if ( 'wordpress' !== $pref && '' !== self::openai_key() ) {
            return 'openai';
        }

        return 'none';
    }

    /**
     * Human-readable status for the admin screens.
     *
     * @return array{engine:string,label:string,help:string}
     */
    public static function status() {
        $engine = self::engine();

        if ( 'wordpress' === $engine ) {
            return array(
                'engine' => $engine,
                'label'  => __( 'Using WordPress AI', 'faq-for-woocommerce' ),
                'help'   => __( 'The AI provider connected in Settings → Connectors. No Happy FAQs key needed.', 'faq-for-woocommerce' ),
            );
        }

        if ( 'openai' === $engine ) {
            return array(
                'engine' => $engine,
                'label'  => __( 'Using your OpenAI key', 'faq-for-woocommerce' ),
                'help'   => __( 'Set in Happy FAQs → Settings → AI FAQs.', 'faq-for-woocommerce' ),
            );
        }

        return array(
            'engine' => $engine,
            'label'  => __( 'AI is not set up yet', 'faq-for-woocommerce' ),
            'help'   => self::wordpress_ai_exists()
                ? __( 'Connect an AI provider in Settings → Connectors, or add an OpenAI API key in Happy FAQs → Settings → AI FAQs.', 'faq-for-woocommerce' )
                : __( 'Add an OpenAI API key in Happy FAQs → Settings → AI FAQs. On WordPress 7.0 or later you can connect a provider in Settings → Connectors instead.', 'faq-for-woocommerce' ),
        );
    }

    /**
     * Run a prompt and return the decoded JSON object.
     *
     * @param string $system System instruction.
     * @param string $prompt User prompt.
     * @param array  $schema JSON schema of the expected object.
     * @return array|WP_Error
     */
    public static function generate_json( $system, $prompt, $schema ) {
        /**
         * Short-circuit AI generation (for tests or custom providers).
         *
         * @param array|WP_Error|null $result Return non-null to skip the AI call.
         * @param string              $system System instruction.
         * @param string              $prompt Prompt.
         * @param array               $schema Expected JSON schema.
         */
        $pre = apply_filters( 'ffw_ai_pre_generate', null, $system, $prompt, $schema );
        if ( null !== $pre ) {
            return $pre;
        }

        if ( ! self::is_enabled() ) {
            return new WP_Error( 'ffw_ai_disabled', __( 'AI is turned off in Happy FAQs → Settings.', 'faq-for-woocommerce' ) );
        }

        $engine = self::engine();

        if ( 'wordpress' === $engine ) {
            $text = self::generate_with_wordpress( $system, $prompt, $schema );
        } elseif ( 'openai' === $engine ) {
            $text = self::generate_with_openai( $system, $prompt );
        } else {
            $status = self::status();
            return new WP_Error( 'ffw_ai_not_configured', $status['label'] . '. ' . $status['help'] );
        }

        if ( is_wp_error( $text ) ) {
            return $text;
        }

        $data = self::decode_json( (string) $text );
        if ( null === $data ) {
            return new WP_Error( 'ffw_ai_bad_response', __( 'The AI answer could not be read. Please try again.', 'faq-for-woocommerce' ) );
        }

        return $data;
    }

    /**
     * Decode JSON, tolerating a ```json fence around it.
     *
     * @param string $text Raw text.
     * @return array|null
     */
    protected static function decode_json( $text ) {
        $text = trim( $text );
        $text = preg_replace( '/^```(?:json)?\s*|\s*```$/i', '', $text );
        $data = json_decode( (string) $text, true );

        if ( ! is_array( $data ) && preg_match( '/\{.*\}/s', (string) $text, $match ) ) {
            $data = json_decode( $match[0], true );
        }

        return is_array( $data ) ? $data : null;
    }

    /**
     * WordPress AI Client.
     *
     * @param string $system System instruction.
     * @param string $prompt Prompt.
     * @param array  $schema JSON schema.
     * @return string|WP_Error
     */
    protected static function generate_with_wordpress( $system, $prompt, $schema ) {
        try {
            $result = wp_ai_client_prompt( $prompt )
                ->using_system_instruction( $system )
                ->using_temperature( 0.4 )
                ->using_max_tokens( self::max_tokens() )
                ->as_json_response( $schema )
                ->generate_text();
        } catch ( \Throwable $e ) {
            return new WP_Error( 'ffw_ai_wordpress', $e->getMessage() );
        }

        return is_wp_error( $result ) ? $result : (string) $result;
    }

    /**
     * Max tokens from Settings (default 2000).
     *
     * @return int
     */
    protected static function max_tokens() {
        $options = self::options();
        $tokens  = isset( $options['ffw_ai_faqs_max_token'] ) ? absint( $options['ffw_ai_faqs_max_token'] ) : 0;

        return $tokens >= 200 ? min( 16000, $tokens ) : 2000;
    }

    /**
     * OpenAI chat completions with the saved key.
     *
     * @param string $system System instruction.
     * @param string $prompt Prompt.
     * @return string|WP_Error
     */
    protected static function generate_with_openai( $system, $prompt ) {
        $options = self::options();
        $model   = isset( $options['ffw_ai_faqs_model'] ) && '' !== $options['ffw_ai_faqs_model'] ? (string) $options['ffw_ai_faqs_model'] : self::DEFAULT_MODEL;

        $response = wp_remote_post(
            self::OPENAI_ENDPOINT,
            array(
                'timeout' => 60,
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . self::openai_key(),
                ),
                'body'    => wp_json_encode(
                    array(
                        'model'           => $model,
                        'temperature'     => 0.4,
                        'max_tokens'      => self::max_tokens(),
                        'response_format' => array( 'type' => 'json_object' ),
                        'messages'        => array(
                            array(
                                'role'    => 'system',
                                'content' => $system,
                            ),
                            array(
                                'role'    => 'user',
                                'content' => $prompt,
                            ),
                        ),
                    )
                ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $data = json_decode( (string) wp_remote_retrieve_body( $response ), true );

        if ( isset( $data['error']['message'] ) ) {
            return new WP_Error( 'ffw_ai_openai', (string) $data['error']['message'] );
        }

        if ( ! isset( $data['choices'][0]['message']['content'] ) ) {
            return new WP_Error( 'ffw_ai_openai', __( 'OpenAI returned an empty answer.', 'faq-for-woocommerce' ) );
        }

        return (string) $data['choices'][0]['message']['content'];
    }

    /* ------------------------------------------------------------------
     * FAQ tasks.
     * ------------------------------------------------------------------ */

    /**
     * Source text for a product, page or post.
     *
     * @param int $post_id Post ID.
     * @return array{title:string,type:string,text:string}|WP_Error
     */
    public static function source_for_post( $post_id ) {
        $post = get_post( absint( $post_id ) );
        if ( ! $post || ! in_array( $post->post_status, array( 'publish', 'draft', 'pending', 'private', 'future' ), true ) ) {
            return new WP_Error( 'ffw_ai_source', __( 'That page or product was not found.', 'faq-for-woocommerce' ) );
        }

        $lines = array();
        $type  = get_post_type_object( $post->post_type );

        if ( 'product' === $post->post_type && function_exists( 'wc_get_product' ) ) {
            $product = wc_get_product( $post->ID );
            if ( $product ) {
                $lines[] = 'Product: ' . $product->get_name();
                if ( '' !== $product->get_price() ) {
                    $lines[] = 'Price: ' . wp_strip_all_tags( html_entity_decode( wc_price( $product->get_price() ), ENT_QUOTES ) )
                        . ( $product->is_on_sale() && '' !== $product->get_regular_price() ? ' (regular ' . wp_strip_all_tags( html_entity_decode( wc_price( $product->get_regular_price() ), ENT_QUOTES ) ) . ')' : '' );
                }
                if ( $product->get_sku() ) {
                    $lines[] = 'SKU: ' . $product->get_sku();
                }
                $lines[] = 'Stock: ' . ( $product->is_in_stock() ? 'in stock' : 'out of stock' );
                if ( $product->get_weight() ) {
                    $lines[] = 'Weight: ' . $product->get_weight() . ' ' . get_option( 'woocommerce_weight_unit' );
                }
                if ( $product->has_dimensions() && function_exists( 'wc_format_dimensions' ) ) {
                    $lines[] = 'Dimensions: ' . wp_strip_all_tags( html_entity_decode( wc_format_dimensions( $product->get_dimensions( false ) ), ENT_QUOTES ) );
                }
                foreach ( $product->get_attributes() as $attribute ) {
                    if ( is_object( $attribute ) && method_exists( $attribute, 'get_name' ) ) {
                        $values  = $attribute->is_taxonomy() ? wc_get_product_terms( $post->ID, $attribute->get_name(), array( 'fields' => 'names' ) ) : $attribute->get_options();
                        $lines[] = wc_attribute_label( $attribute->get_name() ) . ': ' . implode( ', ', array_map( 'strval', (array) $values ) );
                    }
                }
                $cats = wp_get_post_terms( $post->ID, 'product_cat', array( 'fields' => 'names' ) );
                if ( $cats && ! is_wp_error( $cats ) ) {
                    $lines[] = 'Categories: ' . implode( ', ', $cats );
                }
                $lines[] = 'Short description: ' . self::plain( $product->get_short_description() );
                $lines[] = 'Description: ' . self::plain( $product->get_description() );
            }
        } else {
            $lines[] = 'Title: ' . get_the_title( $post );
            if ( has_excerpt( $post ) ) {
                $lines[] = 'Summary: ' . self::plain( $post->post_excerpt );
            }
            $lines[] = 'Content: ' . self::plain( self::content_without_faqs( $post->post_content ) );
        }

        $text = implode( "\n", array_filter( $lines ) );
        if ( function_exists( 'mb_substr' ) ) {
            $text = mb_substr( $text, 0, self::SOURCE_LENGTH, 'UTF-8' );
        } else {
            $text = substr( $text, 0, self::SOURCE_LENGTH );
        }

        return array(
            'title' => get_the_title( $post ),
            'type'  => $type ? $type->labels->singular_name : $post->post_type,
            'text'  => $text,
        );
    }

    /**
     * Post content without existing FAQ blocks and shortcodes.
     *
     * @param string $content Raw content.
     * @return string
     */
    protected static function content_without_faqs( $content ) {
        $content = preg_replace( '/<!--\s*wp:happy-faq\/[\s\S]*?(\/-->|<!--\s*\/wp:happy-faq\/[^>]*-->)/', '', (string) $content );

        return strip_shortcodes( (string) $content );
    }

    /**
     * HTML to plain text.
     *
     * @param string $html HTML.
     * @return string
     */
    protected static function plain( $html ) {
        $text = wp_strip_all_tags( html_entity_decode( (string) $html, ENT_QUOTES, 'UTF-8' ) );

        return trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
    }

    /**
     * Questions already in the library for a product or post, to avoid repeats.
     *
     * @param int $post_id Product or post ID (0 for none).
     * @return string[]
     */
    public static function existing_questions( $post_id ) {
        $post_id = absint( $post_id );
        $ids     = array();

        if ( $post_id && 'product' === get_post_type( $post_id ) ) {
            // Everything the product shows: its own, category, tag and global FAQs.
            $list = function_exists( 'ffw_get_product_faq_ids' ) ? ffw_get_product_faq_ids( $post_id ) : array();
            $ids  = $list ? wp_list_pluck( (array) $list, 'id' ) : (array) get_post_meta( $post_id, 'ffw_product_faq_post_ids', true );
        } elseif ( $post_id ) {
            $content = (string) get_post_field( 'post_content', $post_id );
            if ( preg_match_all( '/"faqIds"\s*:\s*"([0-9, ]+)"/', $content, $matches ) ) {
                foreach ( $matches[1] as $list ) {
                    $ids = array_merge( $ids, array_map( 'absint', explode( ',', $list ) ) );
                }
            }
        }

        $questions = array();
        foreach ( array_filter( array_map( 'absint', $ids ) ) as $id ) {
            $title = get_the_title( $id );
            if ( '' !== $title ) {
                $questions[] = $title;
            }
        }

        return array_slice( $questions, 0, 40 );
    }

    /**
     * Generate FAQs.
     *
     * @param array $args {
     *     @type string $source_text What the FAQs are about (product data, page text or a topic).
     *     @type string $source_type product, page or topic.
     *     @type int    $count       How many (1–10).
     *     @type string $language    Language name.
     *     @type string $focus       Optional keywords to focus on.
     *     @type array  $avoid       Questions that already exist.
     * }
     * @return array|WP_Error List of array( question, answer ).
     */
    public static function generate_faqs( $args ) {
        $args = wp_parse_args(
            $args,
            array(
                'source_text' => '',
                'source_type' => 'page',
                'count'       => 6,
                'language'    => 'English',
                'focus'       => '',
                'avoid'       => array(),
            )
        );

        $count = max( 1, min( (int) apply_filters( 'ffw_ai_max_faqs', self::MAX_FAQS ), absint( $args['count'] ) ) );

        $system = 'You write FAQ sections for websites and online stores. '
            . 'Write questions the way customers ask them and answer in 1 to 3 short, friendly sentences. '
            . 'Use only facts from the source. If the source does not cover something a customer would ask, '
            . 'answer helpfully without inventing details, for example by pointing them to contact the store. '
            . 'Plain text only: no markdown, no numbering. Reply with JSON only: {"faqs":[{"question":"...","answer":"..."}]}.';

        $prompt  = 'topic' === $args['source_type']
            ? "Write {$count} FAQs about this topic for a website's visitors:\n" . $args['source_text']
            : "Write {$count} FAQs for this {$args['source_type']}. Source:\n" . $args['source_text'];
        $prompt .= "\n\nLanguage: {$args['language']}.";
        if ( '' !== trim( (string) $args['focus'] ) ) {
            $prompt .= "\nFocus on: " . trim( (string) $args['focus'] ) . '.';
        }
        if ( ! empty( $args['avoid'] ) ) {
            $prompt .= "\nThese questions already exist, do not repeat them:\n- " . implode( "\n- ", array_map( 'strval', (array) $args['avoid'] ) );
        }

        $prompt = (string) apply_filters( 'ffw_ai_faq_prompt', $prompt, $args );

        $schema = array(
            'type'                 => 'object',
            'properties'           => array(
                'faqs' => array(
                    'type'  => 'array',
                    'items' => array(
                        'type'                 => 'object',
                        'properties'           => array(
                            'question' => array( 'type' => 'string' ),
                            'answer'   => array( 'type' => 'string' ),
                        ),
                        'required'             => array( 'question', 'answer' ),
                        'additionalProperties' => false,
                    ),
                ),
            ),
            'required'             => array( 'faqs' ),
            'additionalProperties' => false,
        );

        $data = self::generate_json( $system, $prompt, $schema );
        if ( is_wp_error( $data ) ) {
            return $data;
        }

        $faqs = array();
        foreach ( isset( $data['faqs'] ) && is_array( $data['faqs'] ) ? $data['faqs'] : array() as $faq ) {
            $question = isset( $faq['question'] ) ? sanitize_text_field( (string) $faq['question'] ) : '';
            $answer   = isset( $faq['answer'] ) ? sanitize_textarea_field( (string) $faq['answer'] ) : '';
            if ( '' !== $question && '' !== $answer ) {
                $faqs[] = array(
                    'question' => $question,
                    'answer'   => $answer,
                );
            }
        }

        if ( ! $faqs ) {
            return new WP_Error( 'ffw_ai_empty', __( 'The AI did not return any FAQs. Try again or add more detail.', 'faq-for-woocommerce' ) );
        }

        return array_slice( $faqs, 0, $count );
    }

    /**
     * Ways to rewrite an answer.
     *
     * @return array mode => array( label, instruction ).
     */
    public static function improve_modes() {
        return array(
            'improve'      => array( __( 'Improve', 'faq-for-woocommerce' ), 'Rewrite it to be clearer and easier to read. Fix grammar and spelling.' ),
            'shorter'      => array( __( 'Shorter', 'faq-for-woocommerce' ), 'Make it shorter and to the point, keeping every important fact.' ),
            'longer'       => array( __( 'More detail', 'faq-for-woocommerce' ), 'Make it more helpful with a little more explanation, without inventing facts.' ),
            'friendly'     => array( __( 'Friendlier', 'faq-for-woocommerce' ), 'Make the tone warmer and friendlier.' ),
            'professional' => array( __( 'More professional', 'faq-for-woocommerce' ), 'Make the tone more professional and confident.' ),
        );
    }

    /**
     * Rewrite an answer.
     *
     * @param string $question Question.
     * @param string $answer   Current answer (HTML allowed).
     * @param string $mode     A key of improve_modes().
     * @return string|WP_Error New answer as simple HTML.
     */
    public static function improve_answer( $question, $answer, $mode = 'improve' ) {
        $modes = self::improve_modes();
        $mode  = isset( $modes[ $mode ] ) ? $mode : 'improve';

        if ( '' === trim( wp_strip_all_tags( (string) $answer ) ) ) {
            return new WP_Error( 'ffw_ai_empty_answer', __( 'Write an answer first, then improve it.', 'faq-for-woocommerce' ) );
        }

        $system = 'You edit answers in a website FAQ. ' . $modes[ $mode ][1]
            . ' Keep the same language and meaning, and never add facts that are not in the answer. '
            . 'Return simple HTML using only <p>, <ul>, <ol>, <li>, <strong>, <em> and <a> (keep existing links). '
            . 'Reply with JSON only: {"answer":"..."}.';

        $prompt = 'Question: ' . wp_strip_all_tags( (string) $question ) . "\n\nAnswer:\n" . wp_kses_post( (string) $answer );

        $data = self::generate_json(
            $system,
            $prompt,
            array(
                'type'                 => 'object',
                'properties'           => array( 'answer' => array( 'type' => 'string' ) ),
                'required'             => array( 'answer' ),
                'additionalProperties' => false,
            )
        );

        if ( is_wp_error( $data ) ) {
            return $data;
        }

        $new = isset( $data['answer'] ) ? trim( (string) $data['answer'] ) : '';
        if ( '' === $new ) {
            return new WP_Error( 'ffw_ai_empty', __( 'The AI did not return an answer. Please try again.', 'faq-for-woocommerce' ) );
        }

        // Plain text from some models: wrap paragraphs.
        if ( false === strpos( $new, '<' ) ) {
            $new = wpautop( esc_html( $new ) );
        }

        return wp_kses(
            $new,
            array(
                'p'      => array(),
                'ul'     => array(),
                'ol'     => array(),
                'li'     => array(),
                'strong' => array(),
                'em'     => array(),
                'br'     => array(),
                'a'      => array(
                    'href'   => true,
                    'target' => true,
                    'rel'    => true,
                ),
            )
        );
    }

    /**
     * Language name for a locale code, for prompts.
     *
     * @param string $locale Locale, e.g. de_DE. Empty for the site language.
     * @return string
     */
    public static function language_name( $locale = '' ) {
        $locale = '' !== (string) $locale ? (string) $locale : get_locale();
        if ( 'en_US' === $locale || '' === $locale ) {
            return 'English';
        }

        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        $translations = wp_get_available_translations();

        return isset( $translations[ $locale ]['english_name'] ) ? $translations[ $locale ]['english_name'] : $locale;
    }
}
