<?php
/**
 * Happy FAQs → AI FAQs.
 *
 * @package FAQ_Woocommerce
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! FAQ_Woocommerce_AI::is_enabled() ) {
    ?>
    <div class="wrap ffw-aig">
        <div class="ffw-aig-card ffw-aig-off">
            <h2><?php esc_html_e( 'AI FAQs are turned off', 'faq-for-woocommerce' ); ?></h2>
            <p><?php esc_html_e( 'Turn them on in Happy FAQs → Settings.', 'faq-for-woocommerce' ); ?></p>
        </div>
    </div>
    <?php
    return;
}

$ffw_status   = FAQ_Woocommerce_AI::status();
$ffw_woo      = function_exists( 'wc_get_product' );
$ffw_bulk     = defined( 'FFW_PRO_AI_BULK' );
$ffw_gaps     = has_action( 'ffw_ai_gaps_panel' );
$ffw_faq_cats = taxonomy_exists( 'ffw-category' ) ? get_terms(
    array(
        'taxonomy'   => 'ffw-category',
        'hide_empty' => false,
    )
) : array();
$ffw_faq_cats = is_wp_error( $ffw_faq_cats ) ? array() : $ffw_faq_cats;
$ffw_max      = (int) apply_filters( 'ffw_ai_max_faqs', FAQ_Woocommerce_AI::MAX_FAQS );

$ffw_language_select = static function ( $id ) {
    $languages = get_available_languages();
    $locale    = get_locale();
    wp_dropdown_languages(
        array(
            'name'      => $id,
            'id'        => $id,
            'selected'  => in_array( $locale, $languages, true ) ? $locale : '',
            'languages' => $languages,
        )
    );
};
?>
<div class="wrap ffw-aig">
    <h1 class="screen-reader-text"><?php esc_html_e( 'AI FAQs', 'faq-for-woocommerce' ); ?></h1>

    <header class="ffw-aig-head">
        <div>
            <p class="ffw-aig-eyebrow"><?php esc_html_e( 'AI FAQs', 'faq-for-woocommerce' ); ?></p>
            <h2 class="ffw-aig-title"><?php esc_html_e( 'Write FAQs with AI', 'faq-for-woocommerce' ); ?></h2>
            <p class="ffw-aig-sub"><?php esc_html_e( 'Pick a product, a page or a topic. Review and edit the answers, then save the ones you like.', 'faq-for-woocommerce' ); ?></p>
        </div>
        <div class="ffw-aig-status is-<?php echo esc_attr( $ffw_status['engine'] ); ?>">
            <span class="ffw-aig-status-dot" aria-hidden="true"></span>
            <div>
                <strong><?php echo esc_html( $ffw_status['label'] ); ?></strong>
                <span><?php echo esc_html( $ffw_status['help'] ); ?></span>
                <span class="ffw-aig-status-links">
                    <?php if ( FAQ_Woocommerce_AI::wordpress_ai_exists() ) : ?>
                        <a href="<?php echo esc_url( admin_url( 'options-connectors.php' ) ); ?>"><?php esc_html_e( 'Connectors', 'faq-for-woocommerce' ); ?></a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ffw&page=woocommerce-faq#ffw-ai-faqs' ) ); ?>"><?php esc_html_e( 'AI settings', 'faq-for-woocommerce' ); ?></a>
                </span>
            </div>
        </div>
    </header>

    <div class="ffw-aig-tabs" role="tablist">
        <button type="button" role="tab" class="ffw-aig-tab is-active" aria-selected="true" aria-controls="ffw-aig-generate" data-tab="generate"><?php esc_html_e( 'Generate', 'faq-for-woocommerce' ); ?></button>
        <button type="button" role="tab" class="ffw-aig-tab" aria-selected="false" aria-controls="ffw-aig-bulk" data-tab="bulk">
            <?php esc_html_e( 'Bulk for products', 'faq-for-woocommerce' ); ?>
            <?php if ( ! $ffw_bulk ) : ?>
                <span class="ffw-aig-pro"><?php esc_html_e( 'Pro', 'faq-for-woocommerce' ); ?></span>
            <?php endif; ?>
        </button>
        <button type="button" role="tab" class="ffw-aig-tab" aria-selected="false" aria-controls="ffw-aig-gaps" data-tab="gaps">
            <?php esc_html_e( 'Fill gaps', 'faq-for-woocommerce' ); ?>
            <?php if ( ! $ffw_gaps ) : ?>
                <span class="ffw-aig-pro"><?php esc_html_e( 'Pro', 'faq-for-woocommerce' ); ?></span>
            <?php endif; ?>
        </button>
    </div>

    <section id="ffw-aig-generate" class="ffw-aig-panel" role="tabpanel">
        <div class="ffw-aig-grid">
            <form class="ffw-aig-card ffw-aig-form" id="ffw-aig-form" novalidate>
                <fieldset class="ffw-aig-field">
                    <legend><?php esc_html_e( 'Write FAQs for', 'faq-for-woocommerce' ); ?></legend>
                    <div class="ffw-aig-segment">
                        <?php if ( $ffw_woo ) : ?>
                            <label><input type="radio" name="source" value="product" checked> <span><?php esc_html_e( 'A product', 'faq-for-woocommerce' ); ?></span></label>
                        <?php endif; ?>
                        <label><input type="radio" name="source" value="page" <?php checked( ! $ffw_woo ); ?>> <span><?php esc_html_e( 'A page or post', 'faq-for-woocommerce' ); ?></span></label>
                        <label><input type="radio" name="source" value="topic"> <span><?php esc_html_e( 'A topic', 'faq-for-woocommerce' ); ?></span></label>
                    </div>
                </fieldset>

                <div class="ffw-aig-field" data-show-for="product page">
                    <label for="ffw-aig-search" class="ffw-aig-label" data-label-product="<?php esc_attr_e( 'Product', 'faq-for-woocommerce' ); ?>" data-label-page="<?php esc_attr_e( 'Page or post', 'faq-for-woocommerce' ); ?>"><?php echo $ffw_woo ? esc_html__( 'Product', 'faq-for-woocommerce' ) : esc_html__( 'Page or post', 'faq-for-woocommerce' ); ?></label>
                    <div class="ffw-aig-combo">
                        <input type="search" id="ffw-aig-search" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="ffw-aig-results" aria-autocomplete="list" placeholder="<?php esc_attr_e( 'Search by title…', 'faq-for-woocommerce' ); ?>">
                        <ul class="ffw-aig-results" id="ffw-aig-results" role="listbox" hidden></ul>
                    </div>
                    <div class="ffw-aig-chosen" hidden>
                        <span class="ffw-aig-chosen-type"></span>
                        <strong class="ffw-aig-chosen-title"></strong>
                        <button type="button" class="ffw-aig-link" data-ffw-clear><?php esc_html_e( 'Change', 'faq-for-woocommerce' ); ?></button>
                    </div>
                    <input type="hidden" name="source_id" value="">
                </div>

                <div class="ffw-aig-field" data-show-for="topic" hidden>
                    <label for="ffw-aig-topic" class="ffw-aig-label"><?php esc_html_e( 'Topic', 'faq-for-woocommerce' ); ?></label>
                    <textarea id="ffw-aig-topic" name="topic" rows="3" placeholder="<?php esc_attr_e( 'For example: our 30-day return policy, free shipping over $50, and how to track an order.', 'faq-for-woocommerce' ); ?>"></textarea>
                </div>

                <div class="ffw-aig-row">
                    <div class="ffw-aig-field">
                        <label for="ffw-aig-count" class="ffw-aig-label"><?php esc_html_e( 'How many', 'faq-for-woocommerce' ); ?></label>
                        <select id="ffw-aig-count" name="count">
                            <?php for ( $ffw_i = 3; $ffw_i <= $ffw_max; $ffw_i++ ) : ?>
                                <option value="<?php echo esc_attr( (string) $ffw_i ); ?>" <?php selected( 6, $ffw_i ); ?>><?php echo esc_html( (string) $ffw_i ); ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="ffw-aig-field">
                        <label for="ffw-aig-language" class="ffw-aig-label"><?php esc_html_e( 'Language', 'faq-for-woocommerce' ); ?></label>
                        <?php $ffw_language_select( 'ffw-aig-language' ); ?>
                    </div>
                </div>

                <div class="ffw-aig-field">
                    <label for="ffw-aig-focus" class="ffw-aig-label"><?php esc_html_e( 'Focus on (optional)', 'faq-for-woocommerce' ); ?></label>
                    <input type="text" id="ffw-aig-focus" name="focus" placeholder="<?php esc_attr_e( 'shipping, sizing, care, warranty', 'faq-for-woocommerce' ); ?>">
                </div>

                <p class="ffw-aig-error" role="alert" hidden></p>

                <button type="submit" class="ffw-aig-btn ffw-aig-btn-primary" <?php disabled( 'none', $ffw_status['engine'] ); ?>>
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="currentColor"><path d="M18.5 4.8l-.9-2.1c-.4-1-1.9-1-2.3 0l-.9 2.1c-.1.3-.4.5-.7.7l-2.1.9c-1 .4-1 1.9 0 2.3l2.1.9c.3.1.5.4.7.7l.9 2.1c.4 1 1.9 1 2.3 0l.9-2.1c.1-.3.4-.5.7-.7l2.1-.9c1-.4 1-1.9 0-2.3l-2.1-.9c-.3-.1-.5-.4-.7-.7zM6.5 9.9l.6 1.4c0 .2.2.4.4.4l1.4.6c.7.3.7 1.3 0 1.6l-1.4.6c-.2 0-.4.2-.4.4l-.6 1.4c-.3.7-1.3.7-1.6 0l-.6-1.4c0-.2-.2-.4-.4-.4l-1.4-.6c-.7-.3-.7-1.3 0-1.6l1.4-.6c.2 0 .4-.2.4-.4l.6-1.4c.3-.7 1.3-.7 1.6 0z"/></svg>
                    <span><?php esc_html_e( 'Generate FAQs', 'faq-for-woocommerce' ); ?></span>
                </button>
            </form>

            <section class="ffw-aig-card ffw-aig-output" aria-live="polite">
                <div class="ffw-aig-empty">
                    <span class="dashicons dashicons-format-chat" aria-hidden="true"></span>
                    <p><?php esc_html_e( 'Your FAQs will appear here. You can edit every question and answer before saving.', 'faq-for-woocommerce' ); ?></p>
                </div>

                <div class="ffw-aig-loading" hidden>
                    <span class="ffw-aig-spinner" aria-hidden="true"></span>
                    <p><?php esc_html_e( 'Writing FAQs…', 'faq-for-woocommerce' ); ?></p>
                </div>

                <form class="ffw-aig-results-form" hidden>
                    <div class="ffw-aig-results-head">
                        <label class="ffw-aig-check"><input type="checkbox" data-ffw-select-all checked> <span><?php esc_html_e( 'Select all', 'faq-for-woocommerce' ); ?></span></label>
                        <span class="ffw-aig-count"></span>
                        <button type="button" class="ffw-aig-link" data-ffw-regenerate><?php esc_html_e( 'Generate again', 'faq-for-woocommerce' ); ?></button>
                    </div>
                    <p class="ffw-aig-for"></p>
                    <ol class="ffw-aig-items"></ol>

                    <div class="ffw-aig-save">
                        <div class="ffw-aig-row">
                            <div class="ffw-aig-field">
                                <label for="ffw-aig-status" class="ffw-aig-label"><?php esc_html_e( 'Save as', 'faq-for-woocommerce' ); ?></label>
                                <select id="ffw-aig-status" name="status">
                                    <option value="publish"><?php esc_html_e( 'Published FAQs', 'faq-for-woocommerce' ); ?></option>
                                    <option value="draft"><?php esc_html_e( 'Drafts to review', 'faq-for-woocommerce' ); ?></option>
                                </select>
                            </div>
                            <?php if ( $ffw_faq_cats ) : ?>
                                <div class="ffw-aig-field">
                                    <label for="ffw-aig-faq-cat" class="ffw-aig-label"><?php esc_html_e( 'FAQ category', 'faq-for-woocommerce' ); ?></label>
                                    <select id="ffw-aig-faq-cat" name="faq_cat">
                                        <option value="0"><?php esc_html_e( 'None', 'faq-for-woocommerce' ); ?></option>
                                        <?php foreach ( $ffw_faq_cats as $ffw_cat ) : ?>
                                            <option value="<?php echo esc_attr( (string) $ffw_cat->term_id ); ?>"><?php echo esc_html( $ffw_cat->name ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                        </div>
                        <label class="ffw-aig-check" data-show-for="page" hidden><input type="checkbox" name="add_to_page" value="1" checked> <span><?php esc_html_e( 'Add a FAQ block with these FAQs to the end of the page', 'faq-for-woocommerce' ); ?></span></label>
                        <p class="ffw-aig-hint" data-show-for="product"><?php esc_html_e( 'The FAQs are added to this product.', 'faq-for-woocommerce' ); ?></p>
                        <button type="submit" class="ffw-aig-btn ffw-aig-btn-primary"><?php esc_html_e( 'Save selected FAQs', 'faq-for-woocommerce' ); ?></button>
                    </div>
                </form>

                <div class="ffw-aig-done" hidden>
                    <span class="ffw-aig-done-icon" aria-hidden="true">✓</span>
                    <p class="ffw-aig-done-text"></p>
                    <p class="ffw-aig-done-links"></p>
                    <button type="button" class="ffw-aig-btn" data-ffw-start-over><?php esc_html_e( 'Write more FAQs', 'faq-for-woocommerce' ); ?></button>
                </div>
            </section>
        </div>
    </section>

    <section id="ffw-aig-bulk" class="ffw-aig-panel" role="tabpanel" hidden>
        <?php if ( $ffw_bulk && $ffw_woo ) : ?>
            <div class="ffw-aig-grid">
                <form class="ffw-aig-card ffw-aig-form" id="ffw-aig-bulk-form" novalidate>
                    <div class="ffw-aig-field">
                        <label for="ffw-aig-bulk-scope" class="ffw-aig-label"><?php esc_html_e( 'Products', 'faq-for-woocommerce' ); ?></label>
                        <select id="ffw-aig-bulk-scope" name="scope">
                            <option value="no_faqs"><?php esc_html_e( 'Products without FAQs yet', 'faq-for-woocommerce' ); ?></option>
                            <?php foreach ( FAQ_Woocommerce_AI_FAQs::script_data()['categories'] as $ffw_pcat ) : ?>
                                <option value="cat:<?php echo esc_attr( (string) $ffw_pcat['id'] ); ?>">
                                    <?php
                                    /* translators: 1: category name, 2: number of products */
                                    echo esc_html( sprintf( __( 'Category: %1$s (%2$d)', 'faq-for-woocommerce' ), $ffw_pcat['name'], $ffw_pcat['count'] ) );
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ffw-aig-row">
                        <div class="ffw-aig-field">
                            <label for="ffw-aig-bulk-count" class="ffw-aig-label"><?php esc_html_e( 'FAQs per product', 'faq-for-woocommerce' ); ?></label>
                            <select id="ffw-aig-bulk-count" name="count">
                                <?php for ( $ffw_i = 3; $ffw_i <= 6; $ffw_i++ ) : ?>
                                    <option value="<?php echo esc_attr( (string) $ffw_i ); ?>" <?php selected( 4, $ffw_i ); ?>><?php echo esc_html( (string) $ffw_i ); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="ffw-aig-field">
                            <label for="ffw-aig-bulk-language" class="ffw-aig-label"><?php esc_html_e( 'Language', 'faq-for-woocommerce' ); ?></label>
                            <?php $ffw_language_select( 'ffw-aig-bulk-language' ); ?>
                        </div>
                    </div>
                    <p class="ffw-aig-hint"><?php esc_html_e( 'FAQs are saved as drafts on each product, up to 50 products per run, so you can review them before they go live.', 'faq-for-woocommerce' ); ?></p>
                    <p class="ffw-aig-error" role="alert" hidden></p>
                    <button type="submit" class="ffw-aig-btn ffw-aig-btn-primary" <?php disabled( 'none', $ffw_status['engine'] ); ?>><?php esc_html_e( 'Start', 'faq-for-woocommerce' ); ?></button>
                </form>

                <section class="ffw-aig-card ffw-aig-bulk-output" aria-live="polite">
                    <div class="ffw-aig-empty">
                        <span class="dashicons dashicons-products" aria-hidden="true"></span>
                        <p><?php esc_html_e( 'Progress shows here while FAQs are written for each product.', 'faq-for-woocommerce' ); ?></p>
                    </div>
                    <div class="ffw-aig-progress" hidden>
                        <div class="ffw-aig-progress-head">
                            <strong class="ffw-aig-progress-text"></strong>
                            <button type="button" class="ffw-aig-link" data-ffw-bulk-stop><?php esc_html_e( 'Stop', 'faq-for-woocommerce' ); ?></button>
                        </div>
                        <div class="ffw-aig-bar"><span></span></div>
                        <ol class="ffw-aig-log"></ol>
                        <p class="ffw-aig-done-links"></p>
                    </div>
                </section>
            </div>
        <?php elseif ( $ffw_bulk ) : ?>
            <div class="ffw-aig-card ffw-aig-locked">
                <h3><?php esc_html_e( 'Bulk FAQs need WooCommerce', 'faq-for-woocommerce' ); ?></h3>
                <p><?php esc_html_e( 'Bulk generation writes FAQs for products. Activate WooCommerce to use it, or use the Generate tab for pages and topics.', 'faq-for-woocommerce' ); ?></p>
            </div>
        <?php else : ?>
            <div class="ffw-aig-card ffw-aig-locked">
                <span class="ffw-aig-pro"><?php esc_html_e( 'Pro', 'faq-for-woocommerce' ); ?></span>
                <h3><?php esc_html_e( 'Write FAQs for many products at once', 'faq-for-woocommerce' ); ?></h3>
                <p><?php esc_html_e( 'Pick a product category, or every product without FAQs, and Happy FAQs Pro writes FAQs for each one. They are saved as drafts on the right product, ready for you to review.', 'faq-for-woocommerce' ); ?></p>
                <?php if ( defined( 'FFW_PRO_URL' ) ) : ?>
                    <a class="ffw-aig-btn ffw-aig-btn-primary" href="<?php echo esc_url( FFW_PRO_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Upgrade to Pro', 'faq-for-woocommerce' ); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <section id="ffw-aig-gaps" class="ffw-aig-panel" role="tabpanel" hidden>
        <?php if ( $ffw_gaps ) : ?>
            <?php
            /**
             * Fill gaps panel, rendered by Happy FAQs Pro.
             *
             * @since 3.0.0
             */
            do_action( 'ffw_ai_gaps_panel' );
            ?>
        <?php else : ?>
            <div class="ffw-aig-card ffw-aig-locked">
                <span class="ffw-aig-pro"><?php esc_html_e( 'Pro', 'faq-for-woocommerce' ); ?></span>
                <h3><?php esc_html_e( 'Answer what your visitors could not find', 'faq-for-woocommerce' ); ?></h3>
                <p><?php esc_html_e( 'Happy FAQs Pro turns searches that found nothing and unanswered customer questions into draft FAQs, already attached to the right product. Run it any time or every night, then review and publish.', 'faq-for-woocommerce' ); ?></p>
                <?php if ( defined( 'FFW_PRO_URL' ) ) : ?>
                    <a class="ffw-aig-btn ffw-aig-btn-primary" href="<?php echo esc_url( FFW_PRO_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Upgrade to Pro', 'faq-for-woocommerce' ); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
