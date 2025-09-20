<?php
/**
 * FAQ Woocommerce Admin
 *
 * @class    FAQ_Woocommerce_AI_FAQs
 * @package  FAQ_Woocommerce\Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * FAQ_Woocommerce_AI_FAQs class.
 */
class FAQ_Woocommerce_AI_FAQs {

    /**
     * Instance of self
     *
     * @var FAQ_Woocommerce_AI_FAQs
     */
    public static $_instance = null;
    
    /**
     * API key.
     *
     * @var FAQ_Woocommerce_AI_FAQs
     */
    public $api_key = null;
    
    /**
     * API endpoint.
     *
     * @var FAQ_Woocommerce_AI_FAQs
     */
    public $api_endpoint = null;
    
    /**
     * Max token.
     *
     * @var FAQ_Woocommerce_AI_FAQs
     */
    public $max_token = 2000;
    
    /**
     * API model.
     *
     * @var FAQ_Woocommerce_AI_FAQs
     */
    public $model = 'gpt-3.5-turbo';
    
    /**
     * Client.
     *
     * @var FAQ_Woocommerce_AI_FAQs
     */
    public $client = null;

    /**
     * Constructor.
     */
    public function __construct() {

        $settings = FAQ_Woocommerce_Settings::instance();

        if(isset($settings->options['ffw_disable_ai_faqs']) && '2' === $settings->options['ffw_disable_ai_faqs']) {
            return;
        }

        $this->api_endpoint     = 'https://api.openai.com/v1/chat/completions';

        $this->api_key          = isset($settings->options['ffw_ai_faqs_api_key']) ? $settings->options['ffw_ai_faqs_api_key'] : "";
        $this->model            = isset($settings->options['ffw_ai_faqs_model']) ? $settings->options['ffw_ai_faqs_model'] : $this->model;
        $this->max_token        = isset($settings->options['ffw_ai_faqs_max_token']) ? $settings->options['ffw_ai_faqs_max_token'] : $this->max_token;


        $this->hooks();

        do_action( 'ffw_ai_faqs_loaded', $this );
    }

    /**
     * Initializes class
     *
     * Checks for an existing instance
     * and if it doesn't find one, create it.
     *
     * @return object
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Hooks.
     * 
     * @return void
     */
    public function hooks() {
        add_action('wp_ajax_ffw_generate_ai_faqs', array($this, 'generate_ai_faqs'));
        add_action('wp_ajax_ffw_insert_ai_faqs', array($this, 'insert_ai_faqs'));
    }

    /**
     * Generate FAQs.
     *
     * @return mixed
     */
    public function generate_ai_faqs() {

        //check and validate nonce.
        if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_key(wp_unslash($_POST['nonce'])), 'ffw_admin') ) {
            wp_send_json_error(esc_html__('Invalid nonce', 'faq-for-woocommerce'));
        }

        //check user access or not.
        if(!ffw_is_user_capable()) {
            wp_send_json_error(esc_html__('Current user is not capable to perform it.', 'faq-for-woocommerce'));
            wp_die();
        }

        //check keywords.
        if (isset($_POST['ffw_ai_keywords']) || ! empty($_POST['ffw_ai_keywords'])  ) {
            $keywords = sanitize_text_field(wp_unslash($_POST['ffw_ai_keywords']));
        } else {
            $message = esc_html__('Keywords are missing.', 'faq-for-woocommerce');
            wp_send_json_error($message);
        }
        
        //check product.
        if (isset($_POST['ffw_ai_select_products']) || ! empty($_POST['ffw_ai_select_products'])  ) {
            $product = sanitize_text_field(wp_unslash($_POST['ffw_ai_select_products']));
        } else {
            $message = esc_html__('Product is missing.', 'faq-for-woocommerce');
            wp_send_json_error($message);
        }

        //include file to get languages.
        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        $translations = wp_get_available_translations();

        $default_language_code = 'en';
        if(isset($_POST['ffw_ai_language'])) {
            $language_code = sanitize_text_field(wp_unslash($_POST['ffw_ai_language']));
        }

        $language_code = !empty($language_code) ? $language_code : $default_language_code;
        $language = apply_filters('ffw_default_ai_generator_faqs_language', esc_html__('English (United States)', 'faq-for-woocommerce'));

        if(isset($translations[$language_code])) {
            $language = $translations[$language_code]['native_name'];
        }

        $args = [];
        $args['product'] = $product;
        $args['keywords'] = $keywords;
        $args['language'] = $language;

        //get prompt content.
        $prompt = $this->create_prompt($args);

        //generate faqs.
        $response = $this->generate_faqs($prompt);

        $faqs = $response['faqs'];

        ob_start();

        if(!empty($faqs) && is_array($faqs)) {
            foreach($faqs as $index => $faq) {
                ?>
                <div class="ffw-ai-result-item-group">
                    <label for="ffw_ai_result_item_<?php echo esc_attr($index); ?>">
                        <input 
                            type="checkbox" 
                            name="ffw_ai_faq_items_selected[<?php echo esc_attr($index); ?>]" 
                            id="ffw_ai_result_item_<?php echo esc_attr($index); ?>" 
                            value="<?php echo esc_attr($index); ?>"
                        />
                    </label>

                    <div class="ffw_ai_result_item_content">
                        <div class="ffw-ai-result-item-inner-group" >

                            <?php if(isset($faq['question']) && !empty($faq['question'])): ?>
                                <input 
                                    type="text" 
                                    style="width: <?php echo (isset($faq['answer']) && !empty($faq['answer'])) ? esc_attr('calc(100% - 30px)') : esc_attr('100%'); ?>;"
                                    data-item="<?php echo esc_attr($index); ?>" 
                                    name="ffw_ai_faq_items[<?php echo esc_attr($index); ?>][question]"
                                    value="<?php echo esc_html($faq['question']); ?>"
                                />
                            <?php endif; ?>
                            
                            <?php if(isset($faq['answer']) && !empty($faq['answer'])): ?>
                                <span class="ffw-ai-result-icon"><?php esc_html_e('Q', 'faq-for-woocommerce'); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if(isset($faq['answer']) && !empty($faq['answer'])): ?>
                            <div class="ffw-ai-result-item-inner-group">
                                <textarea 
                                data-item="<?php echo esc_attr($index); ?>" 
                                name="ffw_ai_faq_items[<?php echo esc_attr($index); ?>][answer]"
                                cols="30" 
                                rows="2"><?php echo esc_html($faq['answer']); ?></textarea>
                                <span class="ffw-ai-result-icon"><?php esc_html_e('A', 'faq-for-woocommerce'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
        }

        $faq_result_html = ob_get_clean();

        $product_id = $product;
        $product = wc_get_product($product);
        $product_title = $product->get_name();

        if(!empty($response) && is_array($response) && !empty($faq_result_html)) {
            $message = esc_html__('FAQs are generated successfully!', 'faq-for-woocommerce');
            wp_send_json_success([
                'response' => $response,
                'product_id' => $product_id,
                'product_title' => $product_title,
                'html' => $faq_result_html,
                'message' => $message,
            ]);

        }else {
            wp_send_json_error([
                'response' => false,
                'message' => esc_html__('FAQs generation failed!', 'faq-for-woocommerce')
            ]);
        }

    }

    /**
     * Insert FAQs.
     *
     * @return mixed
     */
    public function insert_ai_faqs() {
        //check and validate nonce.
        if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_key(wp_unslash($_POST['nonce'])), 'ffw_admin') ) {
            wp_send_json_error(esc_html__('Invalid nonce', 'faq-for-woocommerce'));
        }

        //check user access or not.
        if(!ffw_is_user_capable()) {
            wp_send_json_error(esc_html__('Current user is not capable to perform it.', 'faq-for-woocommerce'));
            wp_die();
        }

        //check product.
        if (isset($_POST['ffw_insert_ai_product_id']) || ! empty($_POST['ffw_insert_ai_product_id'])  ) {
            $product_id = sanitize_text_field(wp_unslash($_POST['ffw_insert_ai_product_id']));
        } else {
            $message = esc_html__('Product is missing.', 'faq-for-woocommerce');
            wp_send_json_error($message);
        }
        
        //check faqs.
        if (isset($_POST['ffw_ai_faq_items']) || ! empty($_POST['ffw_ai_faq_items'])  ) {
            $faqs = wp_unslash($_POST['ffw_ai_faq_items']);
        } else {
            $message = esc_html__('FAQs are missing.', 'faq-for-woocommerce');
            wp_send_json_error($message);
        }
        
        //check selected faqs.
        if (isset($_POST['ffw_ai_faq_items_selected']) || ! empty($_POST['ffw_ai_faq_items_selected'])  ) {
            $selected_faqs = wp_unslash($_POST['ffw_ai_faq_items_selected']);
        } else {
            $message = esc_html__('No FAQs is selected.', 'faq-for-woocommerce');
            wp_send_json_error($message);
        }

        $faq_id = '';
        if(!empty($faqs) && is_array($faqs)) {
            foreach($faqs as $index => $faq) {

                //skip if current faq is not selected to insert.
                if(!in_array($index, $selected_faqs)) {
                    continue;
                }

                $new_faq_post = [];
                $new_faq_post['post_title']     = $faq['question'];
                $new_faq_post['post_content']   = $faq['answer'];
                $new_faq_post['post_type']      = 'ffw';
                $new_faq_post['post_status']    = 'publish';


                //create faqs.
                $faq_id = wp_insert_post($new_faq_post);

                $post_id    = (int) $faq_id;
                $product_id = (int) $product_id;

                //insert faqs by product id.
                ffw_insert_faqs_by_product($post_id, $product_id);
            }
        }

        if(!empty($faq_id)) {
            $message = esc_html__('FAQs are inserted successfully!', 'faq-for-woocommerce');

            wp_send_json_success([
                'product_id' => $product_id,
                'message' => $message,
            ]);

        }else {
            wp_send_json_error([
                'response' => false,
                'message' => esc_html__('FAQs insertion failed!', 'faq-for-woocommerce')
            ]);
        }
    }

    /**
     * Create prompt content for AI.
     *
     * @param array $args prompt data.
     * 
     * @return void
     */
    function create_prompt($args) {

        $prompt = "";

        if(isset($args['product']) && isset($args['keywords']) && isset($args['language'])) {
            $product        = $args['product'];
            $product        = wc_get_product($product);
            $product_name   = $product->get_name();

            $product        = (string) $product;

            $keywords   = $args['keywords'];
            $language   = $args['language'];

            $format = "[
                [
                    \"question\" => \"What is the price of the Cap?\",
                    \"answer\" => \"The regular price of the Cap is $18, but it is currently on sale for $16.\"
                ]
            ]";

            $final_tip = "Give me question and answer both in response.";

            $prompt = "Suppose I have a product called `{$product_name}`. This is product data for your help. 

            Product Information/Data (Given you WC_Product object in string): `$product`.

            expected answer format from you like: `{$format}`
            
            Now, show me 10 faqs for `{$product_name}` in PHP array format as `faqs` key values.
            Remember to generate faqs of the product in {$keywords} keywords context and in `{$language}` language.
            Just give me the faqs data nothing else alse {$final_tip}";
        }

        return apply_filters('ffw_prompt_content', $prompt, $args);
    }

    /**
     * Generate FAQs response.
     *
     * @param mixed $prompt prompt message.
     * 
     * @return mixed
     */
    public function generate_faqs($prompt) {
        try {
            $api_key = $this->api_key;
            $api_endpoint = $this->api_endpoint;
            $max_tokens = $this->max_token;
    
            $request_options = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $api_key,
                ),
                'body' => wp_json_encode(array(
                    'model' => $this->model,
                    'messages' => array(
                        array(
                            'role' => 'system', 
                            'content' => 'You are a helpful assistant who creates product faqs for users to output as JSON.'
                        ),
                        array(
                            'role' => 'user', 
                            'content' => $prompt
                        ),
                    ),
                    'max_tokens' => (int) $max_tokens,
                    'response_format' => [
                        'type' => "json_object"
                    ]
    
                )),
                'timeout' => 50,
            );
    
            $response = wp_remote_post($api_endpoint, $request_options);
    
            if (is_wp_error($response)) {
                return 'Error: ' . $response->get_error_message();
            } else {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
    
                if (isset($data['error']) && !empty($data['error'])) {
                    return $data['error']['message'];
                }

                $response = isset($data['choices'][0]['message']['content']) ? $data['choices'][0]['message']['content'] : [];
    
                return json_decode($response, true);
            }
        } catch (Exception $error) {
            return 'Error: ' . $error->getMessage();
        }
    }
}

return new FAQ_Woocommerce_AI_FAQs();
