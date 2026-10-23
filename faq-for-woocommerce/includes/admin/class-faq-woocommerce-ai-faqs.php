<?php
/**
 * Happy FAQs → AI FAQs screen and "Improve with AI" in the FAQ editor.
 *
 * Generation runs through FAQ_Woocommerce_AI (WordPress AI Client or an
 * OpenAI key).
 *
 * @class    FAQ_Woocommerce_AI_FAQs
 * @package  FAQ_Woocommerce\Admin
 * @version  3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * FAQ_Woocommerce_AI_FAQs class.
 */
class FAQ_Woocommerce_AI_FAQs {

    const NONCE = 'ffw_ai';

    /**
     * Instance of self
     *
     * @var FAQ_Woocommerce_AI_FAQs
     */
    public static $_instance = null;

    /**
     * Initializes class.
     *
     * @return FAQ_Woocommerce_AI_FAQs
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        if ( ! FAQ_Woocommerce_AI::is_enabled() ) {
            return;
        }

        add_action( 'wp_ajax_ffw_ai_search_sources', array( $this, 'ajax_search_sources' ) );
        add_action( 'wp_ajax_ffw_ai_generate', array( $this, 'ajax_generate' ) );
        add_action( 'wp_ajax_ffw_ai_insert', array( $this, 'ajax_insert' ) );
        add_action( 'wp_ajax_ffw_ai_improve', array( $this, 'ajax_improve' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_screen_assets' ) );
        add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
        add_action( 'add_meta_boxes_ffw', array( $this, 'add_classic_meta_box' ) );

        do_action( 'ffw_ai_faqs_loaded', $this );
    }

    /* ------------------------------------------------------------------
     * Helpers.
     * ------------------------------------------------------------------ */

    /**
     * Stop unless the request has a valid nonce and the user may manage FAQs.
     *
     * @return void
     */
    protected function verify() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), self::NONCE ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Your session expired. Reload the page and try again.', 'faq-for-woocommerce' ) ), 403 );
        }

        $capable = function_exists( 'ffw_is_user_capable' ) ? ffw_is_user_capable() : current_user_can( 'manage_options' );
        if ( ! $capable || ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'You are not allowed to do this.', 'faq-for-woocommerce' ) ), 403 );
        }
    }

    /**
     * Send a WP_Error as JSON.
     *
     * @param WP_Error $error Error.
     * @return void
     */
    protected function fail( $error ) {
        wp_send_json_error( array( 'message' => $error->get_error_message() ), 400 );
    }

    /**
     * Data shared with the AI screen and editor scripts.
     *
     * @return array
     */
    public static function script_data() {
        $categories = array();
        if ( taxonomy_exists( 'product_cat' ) ) {
            $terms = get_terms(
                array(
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => true,
                    'number'     => 200,
                )
            );
            foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
                $categories[] = array(
                    'id'    => (int) $term->term_id,
                    'name'  => html_entity_decode( $term->name, ENT_QUOTES, 'UTF-8' ),
                    'count' => (int) $term->count,
                );
            }
        }

        $modes = array();
        foreach ( FAQ_Woocommerce_AI::improve_modes() as $key => $mode ) {
            $modes[] = array(
                'value' => $key,
                'label' => $mode[0],
            );
        }

        return array(
            'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
            'nonce'      => wp_create_nonce( self::NONCE ),
            'status'     => FAQ_Woocommerce_AI::status(),
            'settingsUrl'   => admin_url( 'edit.php?post_type=ffw&page=woocommerce-faq#ffw-ai-faqs' ),
            'connectorsUrl' => FAQ_Woocommerce_AI::wordpress_ai_exists() ? admin_url( 'options-connectors.php' ) : '',
            'woo'        => function_exists( 'wc_get_product' ),
            'bulk'       => defined( 'FFW_PRO_AI_BULK' ),
            'proUrl'     => defined( 'FFW_PRO_URL' ) ? FFW_PRO_URL : '',
            'categories' => $categories,
            'modes'      => $modes,
            'maxFaqs'    => (int) apply_filters( 'ffw_ai_max_faqs', FAQ_Woocommerce_AI::MAX_FAQS ),
        );
    }

    /* ------------------------------------------------------------------
     * AJAX.
     * ------------------------------------------------------------------ */

    /**
     * Find products, pages or posts by title.
     *
     * @return void
     */
    public function ajax_search_sources() {
        $this->verify();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in verify().
        $term = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
        $kind = isset( $_POST['kind'] ) ? sanitize_key( wp_unslash( $_POST['kind'] ) ) : 'page';
        // phpcs:enable

        $types = 'product' === $kind ? array( 'product' ) : array( 'page', 'post' );
        $types = array_values( array_filter( $types, 'post_type_exists' ) );

        if ( ! $types ) {
            wp_send_json_success( array( 'items' => array() ) );
        }

        $posts = get_posts(
            array(
                'post_type'        => $types,
                'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future' ),
                's'                => $term,
                'posts_per_page'   => 20,
                'orderby'          => '' === $term ? 'modified' : 'relevance',
                'suppress_filters' => false,
            )
        );

        $items = array();
        foreach ( $posts as $post ) {
            $type    = get_post_type_object( $post->post_type );
            $items[] = array(
                'id'     => (int) $post->ID,
                'title'  => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
                'type'   => $type ? $type->labels->singular_name : $post->post_type,
                'status' => 'publish' === $post->post_status ? '' : $post->post_status,
            );
        }

        wp_send_json_success( array( 'items' => $items ) );
    }

    /**
     * Generate FAQs from a product, page, post or topic.
     *
     * @return void
     */
    public function ajax_generate() {
        $this->verify();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in verify().
        $source   = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : 'page';
        $source   = in_array( $source, array( 'product', 'page', 'topic' ), true ) ? $source : 'page';
        $post_id  = isset( $_POST['source_id'] ) ? absint( wp_unslash( $_POST['source_id'] ) ) : 0;
        $topic    = isset( $_POST['topic'] ) ? sanitize_textarea_field( wp_unslash( $_POST['topic'] ) ) : '';
        $count    = isset( $_POST['count'] ) ? absint( wp_unslash( $_POST['count'] ) ) : 6;
        $focus    = isset( $_POST['focus'] ) ? sanitize_text_field( wp_unslash( $_POST['focus'] ) ) : '';
        $language = isset( $_POST['language'] ) ? sanitize_text_field( wp_unslash( $_POST['language'] ) ) : '';
        // phpcs:enable

        $title = '';
        if ( 'topic' === $source ) {
            if ( strlen( $topic ) < 3 ) {
                $this->fail( new WP_Error( 'ffw_ai_topic', __( 'Describe the topic in a few words.', 'faq-for-woocommerce' ) ) );
            }
            $text  = $topic;
            $title = wp_html_excerpt( $topic, 60, '…' );
        } else {
            if ( ! $post_id || ( 'product' === $source && 'product' !== get_post_type( $post_id ) ) ) {
                $this->fail( new WP_Error( 'ffw_ai_source', 'product' === $source ? __( 'Choose a product.', 'faq-for-woocommerce' ) : __( 'Choose a page or post.', 'faq-for-woocommerce' ) ) );
            }
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                $this->fail( new WP_Error( 'ffw_ai_cap', __( 'You are not allowed to use that page.', 'faq-for-woocommerce' ) ) );
            }

            $data = FAQ_Woocommerce_AI::source_for_post( $post_id );
            if ( is_wp_error( $data ) ) {
                $this->fail( $data );
            }
            $text  = $data['text'];
            $title = $data['title'];
        }

        $faqs = FAQ_Woocommerce_AI::generate_faqs(
            array(
                'source_text' => $text,
                'source_type' => 'topic' === $source ? 'topic' : ( 'product' === $source ? 'product' : 'page' ),
                'count'       => $count,
                'language'    => FAQ_Woocommerce_AI::language_name( $language ),
                'focus'       => $focus,
                'avoid'       => 'topic' === $source ? array() : FAQ_Woocommerce_AI::existing_questions( $post_id ),
            )
        );

        if ( is_wp_error( $faqs ) ) {
            $this->fail( $faqs );
        }

        wp_send_json_success(
            array(
                'faqs'  => $faqs,
                'title' => $title,
            )
        );
    }

    /**
     * Save chosen FAQs; attach them to the product, or add them to the page.
     *
     * @return void
     */
    public function ajax_insert() {
        $this->verify();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in verify().
        $items       = isset( $_POST['items'] ) && is_array( $_POST['items'] ) ? wp_unslash( $_POST['items'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field below.
        $source      = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : '';
        $post_id     = isset( $_POST['source_id'] ) ? absint( wp_unslash( $_POST['source_id'] ) ) : 0;
        $status      = isset( $_POST['status'] ) && 'draft' === $_POST['status'] ? 'draft' : 'publish';
        $add_to_page = ! empty( $_POST['add_to_page'] ) && '1' === sanitize_key( wp_unslash( $_POST['add_to_page'] ) );
        $faq_cat     = isset( $_POST['faq_cat'] ) ? absint( wp_unslash( $_POST['faq_cat'] ) ) : 0;
        // phpcs:enable

        if ( 'publish' === $status && ! current_user_can( 'publish_posts' ) ) {
            $status = 'draft';
        }

        $ids = array();
        foreach ( array_slice( $items, 0, 50 ) as $item ) {
            $question = isset( $item['question'] ) ? sanitize_text_field( (string) $item['question'] ) : '';
            $answer   = isset( $item['answer'] ) ? sanitize_textarea_field( (string) $item['answer'] ) : '';
            if ( '' === $question || '' === $answer ) {
                continue;
            }

            $id = wp_insert_post(
                array(
                    'post_type'    => 'ffw',
                    'post_status'  => $status,
                    'post_title'   => wp_slash( $question ),
                    'post_content' => wp_slash( wpautop( esc_html( $answer ) ) ),
                ),
                true
            );

            if ( is_wp_error( $id ) || ! $id ) {
                continue;
            }

            $ids[] = (int) $id;

            if ( $faq_cat && taxonomy_exists( 'ffw-category' ) ) {
                wp_set_object_terms( $id, array( $faq_cat ), 'ffw-category', true );
            }

            if ( 'product' === $source && $post_id && 'product' === get_post_type( $post_id ) && function_exists( 'ffw_insert_faqs_by_product' ) ) {
                ffw_insert_faqs_by_product( (int) $id, $post_id );
            }
        }

        if ( ! $ids ) {
            $this->fail( new WP_Error( 'ffw_ai_insert', __( 'Select at least one FAQ with a question and an answer.', 'faq-for-woocommerce' ) ) );
        }

        // Page or post: add a Happy Prebuilt FAQs block with these FAQs at the end.
        $added = false;
        if ( 'page' === $source && $add_to_page && 'publish' === $status && $post_id && current_user_can( 'edit_post', $post_id ) ) {
            $block   = '<!-- wp:happy-faq/display-faqs ' . wp_json_encode(
                array(
                    'source' => 'faq_ids',
                    'faqIds' => implode( ',', $ids ),
                )
            ) . ' /-->';
            $content = (string) get_post_field( 'post_content', $post_id );
            $result  = wp_update_post(
                array(
                    'ID'           => $post_id,
                    'post_content' => wp_slash( rtrim( $content ) . "\n\n" . $block ),
                ),
                true
            );
            $added   = ! is_wp_error( $result );
        }

        do_action( 'ffw_ai_faqs_inserted', $ids, $source, $post_id );

        wp_send_json_success(
            array(
                'ids'       => $ids,
                'count'     => count( $ids ),
                'status'    => $status,
                'listUrl'   => admin_url( 'edit.php?post_type=ffw' . ( 'draft' === $status ? '&post_status=draft' : '' ) ),
                'targetUrl' => $post_id ? (string) get_permalink( $post_id ) : '',
                'editUrl'   => $post_id ? (string) get_edit_post_link( $post_id, 'raw' ) : '',
                'addedToPage' => $added,
            )
        );
    }

    /**
     * Rewrite a FAQ answer.
     *
     * @return void
     */
    public function ajax_improve() {
        $this->verify();

        // phpcs:disable WordPress.Security.NonceVerification.Missing -- verified in verify().
        $question = isset( $_POST['question'] ) ? sanitize_text_field( wp_unslash( $_POST['question'] ) ) : '';
        $answer   = isset( $_POST['answer'] ) ? wp_kses_post( wp_unslash( $_POST['answer'] ) ) : '';
        $mode     = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'improve';
        // phpcs:enable

        $result = FAQ_Woocommerce_AI::improve_answer( $question, $answer, $mode );
        if ( is_wp_error( $result ) ) {
            $this->fail( $result );
        }

        wp_send_json_success( array( 'answer' => $result ) );
    }

    /* ------------------------------------------------------------------
     * Assets and editor UI.
     * ------------------------------------------------------------------ */

    /**
     * AI FAQs screen assets.
     *
     * @param string $hook Page hook.
     * @return void
     */
    public function enqueue_screen_assets( $hook ) {
        $is_screen = isset( $_GET['page'] ) && 'ai-faqs' === sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $screen    = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        $classic   = $screen && 'ffw' === $screen->post_type && 'post' === $screen->base && ! $screen->is_block_editor();

        if ( ! $is_screen && ! $classic ) {
            return;
        }

        $css = '/assets/admin/css/ffw-ai.css';
        $js  = '/assets/admin/js/ffw-ai.js';
        wp_enqueue_style( 'ffw-ai', FFW_PLUGIN_URL . $css, array(), FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $css ) );
        wp_enqueue_script( 'ffw-ai', FFW_PLUGIN_URL . $js, array( 'jquery' ), FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $js ), true );
        wp_localize_script( 'ffw-ai', 'ffwAI', self::script_data() + $this->strings() );
    }

    /**
     * Block editor: "Improve with AI" panel on FAQs.
     *
     * @return void
     */
    public function enqueue_block_editor_assets() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || 'ffw' !== $screen->post_type ) {
            return;
        }

        $js = '/assets/admin/js/ffw-ai-editor.js';
        wp_enqueue_script(
            'ffw-ai-editor',
            FFW_PLUGIN_URL . $js,
            array( 'wp-plugins', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n', 'wp-blocks', 'wp-editor' ),
            FFW_VERSION . '.' . filemtime( FFW_FILE_DIR . $js ),
            true
        );
        wp_localize_script( 'ffw-ai-editor', 'ffwAI', self::script_data() + $this->strings() );
    }

    /**
     * Classic editor: "Improve with AI" box.
     *
     * @return void
     */
    public function add_classic_meta_box() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( $screen && $screen->is_block_editor() ) {
            return;
        }

        add_meta_box( 'ffw-ai-improve', __( 'Improve with AI', 'faq-for-woocommerce' ), array( $this, 'render_classic_meta_box' ), 'ffw', 'side', 'high' );
    }

    /**
     * Classic editor box markup (ffw-ai.js wires it up).
     *
     * @return void
     */
    public function render_classic_meta_box() {
        $status = FAQ_Woocommerce_AI::status();
        ?>
        <div class="ffw-ai-improve-box" data-ffw-ai-improve>
            <p class="ffw-ai-improve-status is-<?php echo esc_attr( $status['engine'] ); ?>"><?php echo esc_html( $status['label'] ); ?></p>
            <div class="ffw-ai-improve-modes">
                <?php foreach ( FAQ_Woocommerce_AI::improve_modes() as $key => $mode ) : ?>
                    <button type="button" class="button" data-mode="<?php echo esc_attr( $key ); ?>" <?php disabled( 'none', $status['engine'] ); ?>><?php echo esc_html( $mode[0] ); ?></button>
                <?php endforeach; ?>
            </div>
            <div class="ffw-ai-improve-result" hidden>
                <div class="ffw-ai-improve-preview"></div>
                <p>
                    <button type="button" class="button button-primary" data-ffw-ai-apply><?php esc_html_e( 'Replace answer', 'faq-for-woocommerce' ); ?></button>
                    <button type="button" class="button-link" data-ffw-ai-discard><?php esc_html_e( 'Discard', 'faq-for-woocommerce' ); ?></button>
                </p>
            </div>
            <p class="ffw-ai-improve-message" role="status" aria-live="polite"></p>
        </div>
        <?php
    }

    /**
     * Translated UI strings.
     *
     * @return array
     */
    protected function strings() {
        return array(
            'i18n' => array(
                'generating' => __( 'Writing FAQs…', 'faq-for-woocommerce' ),
                'improving'  => __( 'Rewriting…', 'faq-for-woocommerce' ),
                'saving'     => __( 'Saving…', 'faq-for-woocommerce' ),
                'error'      => __( 'Something went wrong. Please try again.', 'faq-for-woocommerce' ),
                'noResults'  => __( 'Nothing found.', 'faq-for-woocommerce' ),
                'chooseSource' => __( 'Search for and choose a product or page first.', 'faq-for-woocommerce' ),
                /* translators: %d: number of FAQs */
                'selected'   => __( '%d selected', 'faq-for-woocommerce' ),
                /* translators: %d: number of FAQs */
                'inserted'   => __( '%d FAQs saved.', 'faq-for-woocommerce' ),
                /* translators: %d: number of FAQs */
                'drafted'    => __( '%d FAQs saved as drafts.', 'faq-for-woocommerce' ),
                'viewFaqs'   => __( 'View FAQs', 'faq-for-woocommerce' ),
                'viewPage'   => __( 'View page', 'faq-for-woocommerce' ),
                'addedToPage' => __( 'A FAQ block with these FAQs was added to the end of the page.', 'faq-for-woocommerce' ),
                'replaced'   => __( 'Answer replaced. Save the FAQ to keep it.', 'faq-for-woocommerce' ),
                'noAnswer'   => __( 'Write an answer first, then improve it.', 'faq-for-woocommerce' ),
                /* translators: 1: done, 2: total */
                'bulkProgress' => __( '%1$d of %2$d products done', 'faq-for-woocommerce' ),
                /* translators: %d: number of draft FAQs */
                'bulkDone'   => __( 'Done. %d draft FAQs are ready to review.', 'faq-for-woocommerce' ),
                'bulkNone'   => __( 'No products match.', 'faq-for-woocommerce' ),
                'stop'       => __( 'Stop', 'faq-for-woocommerce' ),
            ),
        );
    }
}

return FAQ_Woocommerce_AI_FAQs::instance();
