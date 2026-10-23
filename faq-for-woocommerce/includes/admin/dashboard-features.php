<?php
/**
 * Happy FAQs feature lists, shared by the Dashboard screens.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'ffw_dashboard_feature_table' ) ) {
    /**
     * Free vs Pro table: groups of rows, each row [ title, description, in free ].
     *
     * @return array
     */
    function ffw_dashboard_feature_table() {
        return (array) apply_filters( 'ffw_dashboard_feature_table', array(
        array(
            'title' => __( 'General FAQs', 'faq-for-woocommerce' ),
            'text'  => __( 'Create answers once and place them on help pages, posts, and anywhere else on the site.', 'faq-for-woocommerce' ),
            'emoji' => '💬',
            'rows'  => array(
                array( __( 'Unlimited FAQs', 'faq-for-woocommerce' ), __( 'One reusable FAQ library. No cap on how many questions you can publish.', 'faq-for-woocommerce' ), true ),
                array( __( 'One FAQs screen', 'faq-for-woocommerce' ), __( 'All FAQs, Add New and Categories on one page with no reloads: search, filters, quick add, quick edit, bulk actions, where each FAQ is shown, and the full editor in place.', 'faq-for-woocommerce' ), true ),
                array( __( 'Place FAQs on any page', 'faq-for-woocommerce' ), __( 'Show all FAQs, a FAQ category, or selected FAQs on any page or post.', 'faq-for-woocommerce' ), true ),
                array( __( 'FAQ categories', 'faq-for-woocommerce' ), __( 'Group questions into categories like Shipping or Returns, then embed that category.', 'faq-for-woocommerce' ), true ),
                array( __( 'FAQ page setting', 'faq-for-woocommerce' ), __( 'Pick a page in Settings and Happy FAQs can inject the FAQ list for you.', 'faq-for-woocommerce' ), true ),
                array( __( 'Shortcodes', 'faq-for-woocommerce' ), __( '[ffw_template] for all, category, selected IDs, and current product. Pro adds group_id.', 'faq-for-woocommerce' ), true ),
                array( __( 'Setup wizard', 'faq-for-woocommerce' ), __( 'A short guided setup on first activation, with ready-made common questions to start from.', 'faq-for-woocommerce' ), true ),
            ),
        ),
        array(
            'title' => __( 'Additional features', 'faq-for-woocommerce' ),
            'text'  => __( 'Builders, search, schema, and the extras that make the library easier to use.', 'faq-for-woocommerce' ),
            'emoji' => '✨',
            'rows'  => array(
                array( __( 'Happy Prebuilt FAQs', 'faq-for-woocommerce' ), __( 'Gutenberg block, Elementor widget, and Divi 4/5 module. Shows FAQs from your library. Same markup as the shortcode.', 'faq-for-woocommerce' ), true ),
                array( __( 'Happy Custom FAQs', 'faq-for-woocommerce' ), __( 'Write questions and answers in Gutenberg, Elementor, or Divi 4/5. They stay on that page.', 'faq-for-woocommerce' ), true ),
                array( __( 'FAQ pattern library', 'faq-for-woocommerce' ), __( '21 ready-made FAQ sections with live previews. Create a page in one click or insert them from the block editor.', 'faq-for-woocommerce' ), true ),
                array( __( 'Help Center page', 'faq-for-woocommerce' ), __( 'One click builds a page with search, most asked questions, FAQs by topic and a contact section. Pro adds search across every FAQ and an "Ask a question" form.', 'faq-for-woocommerce' ), true ),
                array( __( '22 toggle icon sets', 'faq-for-woocommerce' ), __( 'Chevrons, arrows, carets, plus/minus, folder, switch and more, with position, color and size. Site-wide or per block and widget, in every layout.', 'faq-for-woocommerce' ), true ),
                array( __( 'Per-block style controls', 'faq-for-woocommerce' ), __( 'Question and answer colors, font sizes, border, corner radius and spacing for each Happy Prebuilt FAQs and Happy Custom FAQs block.', 'faq-for-woocommerce' ), true ),
                array( __( 'FAQ block styles', 'faq-for-woocommerce' ), __( 'Cards, Minimal, and Soft styles for the Happy Prebuilt FAQs and Happy Custom FAQs blocks.', 'faq-for-woocommerce' ), true ),
                array( __( 'FAQPage schema', 'faq-for-woocommerce' ), __( 'Google FAQ schema on help pages and product pages, with duplicate questions removed.', 'faq-for-woocommerce' ), true ),
                array( __( 'Inline search', 'faq-for-woocommerce' ), __( 'A search box that filters the FAQs already on the page as you type.', 'faq-for-woocommerce' ), true ),
                array( __( 'FAQ category filter', 'faq-for-woocommerce' ), __( 'Buttons that show one FAQ category at a time.', 'faq-for-woocommerce' ), true ),
                array( __( 'Show more button', 'faq-for-woocommerce' ), __( 'Show a few FAQs first and reveal the rest on click. All FAQs stay in schema.', 'faq-for-woocommerce' ), true ),
                array( __( 'Most viewed and newest FAQs', 'faq-for-woocommerce' ), __( 'Lists that update on their own: the questions visitors open most, or the latest ones.', 'faq-for-woocommerce' ), true ),
                array( __( 'Helpful votes and view counts', 'faq-for-woocommerce' ), __( '"Was this helpful?" votes under answers, with Views and Helpful columns on All FAQs.', 'faq-for-woocommerce' ), true ),
                array( __( 'AI FAQ generator', 'faq-for-woocommerce' ), __( 'Write FAQs from a product, a page or post, or a topic. Uses the AI provider connected in WordPress (no extra key), or your OpenAI key.', 'faq-for-woocommerce' ), true ),
                array( __( 'Improve with AI', 'faq-for-woocommerce' ), __( 'Improve, shorten, expand, or change the tone of an answer in the FAQ editor, with a preview first.', 'faq-for-woocommerce' ), true ),
                array( __( 'Media in answers', 'faq-for-woocommerce' ), __( 'Add images, video, and other media inside FAQ answers.', 'faq-for-woocommerce' ), true ),
                array( __( 'Expand / collapse all', 'faq-for-woocommerce' ), __( 'One control to open or close every FAQ in the list.', 'faq-for-woocommerce' ), true ),
                array( __( 'Comments', 'faq-for-woocommerce' ), __( 'Let visitors comment on FAQs when comments are enabled.', 'faq-for-woocommerce' ), true ),
                array( __( 'Customization', 'faq-for-woocommerce' ), __( 'Layouts, colors, width, and display options in Settings.', 'faq-for-woocommerce' ), true ),
                array( __( 'Accessible accordions', 'faq-for-woocommerce' ), __( 'Keyboard and screen reader friendly, with a visible focus outline in every layout.', 'faq-for-woocommerce' ), true ),
                array( __( 'Loads only where needed', 'faq-for-woocommerce' ), __( 'CSS and JS load only on pages that show FAQs, for the layouts in use.', 'faq-for-woocommerce' ), true ),
                array( __( 'Analytics dashboard', 'faq-for-woocommerce' ), __( 'FAQ opens, helpful rate, top searches, searches with no answer, and top pages and products.', 'faq-for-woocommerce' ), false ),
                array( __( 'Visitor questions on any page', 'faq-for-woocommerce' ), __( 'An "Ask a question" form (block, Elementor widget, or shortcode) on any page or product, with a moderation queue, email replies, one-click "Publish as FAQ", and an optional "Questions from customers" list on the product or page. Works without WooCommerce.', 'faq-for-woocommerce' ), false ),
                array( __( 'Product Q&A with voting', 'faq-for-woocommerce' ), __( 'Customers answer each other\'s questions on the product. You approve answers and mark the best one, buyers get a "Verified buyer" badge, and "I have this question too" and "Helpful" votes bring the most useful ones to the top.', 'faq-for-woocommerce' ), false ),
                array( __( 'FAQ Groups', 'faq-for-woocommerce' ), __( 'Named collections of existing FAQs, with drag-and-drop order and a copyable shortcode.', 'faq-for-woocommerce' ), false ),
                array( __( 'FAQ suggestions in contact forms', 'faq-for-woocommerce' ), __( 'WPForms, Contact Form 7 and Fluent Forms show matching FAQs while visitors type, with answers in the form and tickets avoided in Analytics.', 'faq-for-woocommerce' ), false ),
                array( __( 'Nested FAQs', 'faq-for-woocommerce' ), __( 'Show FAQs inside an answer as a smaller accordion, up to three levels deep, in every layout. Good for long help topics.', 'faq-for-woocommerce' ), false ),
                array( __( 'AJAX library search', 'faq-for-woocommerce' ), __( 'Autocomplete across all published FAQs. Place it with [ffw_search], a block, or an Elementor widget.', 'faq-for-woocommerce' ), false ),
                array( __( 'Hash deep-links', 'faq-for-woocommerce' ), __( 'Open a specific FAQ with a link like #ffw-faq-123.', 'faq-for-woocommerce' ), false ),
                array( __( 'Extra layouts', 'faq-for-woocommerce' ), __( 'Glow, Smart, Wow, and Zoom layouts in addition to the five free templates.', 'faq-for-woocommerce' ), false ),
                array( __( 'Spreadsheet import / export', 'faq-for-woocommerce' ), __( 'Import CSV or Excel files with column matching, a preview of every change and undo. Export to CSV, or back up everything as JSON.', 'faq-for-woocommerce' ), false ),
                array( __( 'RTL support', 'faq-for-woocommerce' ), __( 'Display FAQs right-to-left when your language needs it.', 'faq-for-woocommerce' ), true ),
                array( __( 'Multi-column layout', 'faq-for-woocommerce' ), __( 'Show FAQs in more than one column.', 'faq-for-woocommerce' ), false ),
            ),
        ),
        array(
            'title' => __( 'Product FAQs', 'faq-for-woocommerce' ),
            'text'  => __( 'The same FAQ posts can also be assigned to WooCommerce products, categories, and tags.', 'faq-for-woocommerce' ),
            'emoji' => '🛒',
            'rows'  => array(
                array( __( 'Assign FAQs to products', 'faq-for-woocommerce' ), __( 'Attach FAQs to a product from the product screen or the FAQ screen.', 'faq-for-woocommerce' ), true ),
                array( __( 'FAQs by product categories', 'faq-for-woocommerce' ), __( 'Assign a product category to FAQs so every product in that category inherits them.', 'faq-for-woocommerce' ), true ),
                array( __( 'FAQs by product tags', 'faq-for-woocommerce' ), __( 'Assign a product tag to FAQs to connect them to matching products.', 'faq-for-woocommerce' ), true ),
                array( __( 'Sort FAQs on a product', 'faq-for-woocommerce' ), __( 'Drag FAQs into the order you want on each product.', 'faq-for-woocommerce' ), true ),
                array( __( 'Variation product FAQs', 'faq-for-woocommerce' ), __( 'Show different FAQs for variable product variations.', 'faq-for-woocommerce' ), false ),
                array( __( 'Extra product page locations', 'faq-for-woocommerce' ), __( 'Place FAQs after Add to cart, at the top or bottom of the product page, and similar spots.', 'faq-for-woocommerce' ), false ),
                array( __( 'Product archive FAQs', 'faq-for-woocommerce' ), __( 'Show FAQs on WooCommerce category and tag archives.', 'faq-for-woocommerce' ), false ),
                array( __( 'Shop page FAQs', 'faq-for-woocommerce' ), __( 'Show order, payment, or shipping FAQs on the shop page.', 'faq-for-woocommerce' ), false ),
                array( __( 'Cart page FAQs', 'faq-for-woocommerce' ), __( 'Show FAQs on the cart page.', 'faq-for-woocommerce' ), false ),
                array( __( 'Checkout page FAQs', 'faq-for-woocommerce' ), __( 'Show FAQs on the checkout page.', 'faq-for-woocommerce' ), false ),
                array( __( 'Dynamic product attributes', 'faq-for-woocommerce' ), __( 'Insert placeholders like {product_price} in an answer and show the live product value.', 'faq-for-woocommerce' ), false ),
                array( __( 'Global product FAQs', 'faq-for-woocommerce' ), __( 'Mark a FAQ as global so it appears on every product.', 'faq-for-woocommerce' ), false ),
                array( __( 'Bulk AI FAQs for products', 'faq-for-woocommerce' ), __( 'Write FAQs for every product without FAQs, or a whole category, in one run. Saved as drafts for review.', 'faq-for-woocommerce' ), false ),
                array( __( 'AI gap filling', 'faq-for-woocommerce' ), __( 'Searches with no answer and unanswered customer questions become AI draft FAQs on the right product, by hand or every night.', 'faq-for-woocommerce' ), false ),
                array( __( 'Course and lesson FAQs', 'faq-for-woocommerce' ), __( 'FAQs on Tutor LMS, LearnDash and LearnPress courses and lessons, assigned like product FAQs.', 'faq-for-woocommerce' ), false ),
                array( __( 'AJAX search on product FAQs', 'faq-for-woocommerce' ), __( 'A search box on the product FAQ list that filters those FAQs over AJAX.', 'faq-for-woocommerce' ), false ),
            ),
        ),
        ) );
    }
}

if ( ! function_exists( 'ffw_dashboard_why_pro' ) ) {
    /**
     * Short list of Pro features.
     *
     * @return array
     */
    function ffw_dashboard_why_pro() {
        return array(
                        __( 'Analytics dashboard', 'faq-for-woocommerce' ),
                        __( 'FAQ suggestions in contact forms', 'faq-for-woocommerce' ),
                        __( 'FAQ Groups', 'faq-for-woocommerce' ),
                        __( 'Nested FAQs', 'faq-for-woocommerce' ),
                        __( 'AJAX library search', 'faq-for-woocommerce' ),
                        __( 'Extra layouts', 'faq-for-woocommerce' ),
                        __( 'CSV and Excel import / export', 'faq-for-woocommerce' ),
                        __( 'Cart, Checkout and Shop FAQs', 'faq-for-woocommerce' ),
                        __( 'Visitor questions on any page', 'faq-for-woocommerce' ),
                        __( 'Variation product FAQs', 'faq-for-woocommerce' ),
                        __( 'Dynamic product attributes', 'faq-for-woocommerce' ),
                        __( 'Global product FAQs', 'faq-for-woocommerce' ),
                        __( 'Bulk AI FAQs for products', 'faq-for-woocommerce' ),
                        __( 'AI gap filling', 'faq-for-woocommerce' ),
                        __( 'Course and lesson FAQs', 'faq-for-woocommerce' ),
        );
    }
}

if ( ! function_exists( 'ffw_dashboard_free_features' ) ) {
    /**
     * Short list of free features.
     *
     * @return array
     */
    function ffw_dashboard_free_features() {
        return array(
                        __( 'Unlimited FAQs', 'faq-for-woocommerce' ),
                        __( '21 ready-made FAQ patterns', 'faq-for-woocommerce' ),
                        __( 'One-click Help Center page', 'faq-for-woocommerce' ),
                        __( 'Cards, Minimal and Soft styles', 'faq-for-woocommerce' ),
                        __( '22 toggle icon sets', 'faq-for-woocommerce' ),
                        __( 'Product FAQs', 'faq-for-woocommerce' ),
                        __( 'FAQPage schema', 'faq-for-woocommerce' ),
                        __( 'FAQs on any page', 'faq-for-woocommerce' ),
                        __( 'FAQ categories', 'faq-for-woocommerce' ),
                        __( 'Shortcodes', 'faq-for-woocommerce' ),
                        __( 'Happy Prebuilt FAQs', 'faq-for-woocommerce' ),
                        __( 'Happy Custom FAQs', 'faq-for-woocommerce' ),
                        __( 'Inline search', 'faq-for-woocommerce' ),
                        __( 'FAQ category filter', 'faq-for-woocommerce' ),
                        __( 'Show more button', 'faq-for-woocommerce' ),
                        __( 'Most viewed and newest FAQs', 'faq-for-woocommerce' ),
                        __( 'Helpful votes and view counts', 'faq-for-woocommerce' ),
                        __( 'AI FAQ generator', 'faq-for-woocommerce' ),
                        __( 'Improve answers with AI', 'faq-for-woocommerce' ),
                        __( 'FAQs by product categories and tags', 'faq-for-woocommerce' ),
                        __( 'Comments and media', 'faq-for-woocommerce' ),
                        __( 'RTL support', 'faq-for-woocommerce' ),
                        __( 'Setup wizard', 'faq-for-woocommerce' ),
        );
    }
}
