<?php
/**
 * Setup wizard markup.
 *
 * @var bool  $has_woo       WooCommerce is active.
 * @var bool  $has_elementor Elementor is active.
 * @var array $extras        Recommended tools to offer.
 * @var array $state         Saved wizard state.
 *
 * @package FAQ_Woocommerce\Admin
 * @since   2.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$steps = array(
    'goal'    => __( 'Goal', 'faq-for-woocommerce' ),
    'faqs'    => __( 'First FAQs', 'faq-for-woocommerce' ),
    'display' => __( 'Display', 'faq-for-woocommerce' ),
);
if ( ! empty( $extras ) ) {
    $steps['extras'] = __( 'Extras', 'faq-for-woocommerce' );
}
$steps['ready'] = __( 'Ready', 'faq-for-woocommerce' );

$default_goal = 'product' === $state['goal'] && $has_woo ? 'product' : 'general';
?>
<div class="ffw-sw" id="ffw-setup-wizard" data-goal="<?php echo esc_attr( $default_goal ); ?>">

    <header class="ffw-sw-header">
        <div class="ffw-sw-brand">
            <?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage -- Static plugin logo, not a media library image. ?>
            <img src="<?php echo esc_url( FFW_PLUGIN_URL . '/assets/admin/images/Logo.png' ); ?>" alt="" width="40" height="40" />
            <span><?php esc_html_e( 'Happy FAQs', 'faq-for-woocommerce' ); ?></span>
        </div>

        <ol class="ffw-sw-progress" aria-label="<?php esc_attr_e( 'Setup steps', 'faq-for-woocommerce' ); ?>">
            <?php
            $index = 1;
            foreach ( $steps as $key => $label ) :
                ?>
                <li class="ffw-sw-progress-item" data-step="<?php echo esc_attr( $key ); ?>">
                    <span class="ffw-sw-progress-dot"><span class="ffw-sw-progress-num"><?php echo esc_html( (string) $index ); ?></span><span class="dashicons dashicons-yes" aria-hidden="true"></span></span>
                    <span class="ffw-sw-progress-label"><?php echo esc_html( $label ); ?></span>
                </li>
                <?php
                $index++;
            endforeach;
            ?>
        </ol>
    </header>

    <main class="ffw-sw-card">

        <?php // Step 1: goal. ?>
        <section class="ffw-sw-step" data-step="goal" hidden>
            <h1><?php esc_html_e( 'What would you like to create first?', 'faq-for-woocommerce' ); ?></h1>
            <p class="ffw-sw-lead"><?php esc_html_e( 'Pick a starting point. You can create both kinds of FAQs later.', 'faq-for-woocommerce' ); ?></p>

            <div class="ffw-sw-choices" role="radiogroup">
                <label class="ffw-sw-choice">
                    <input type="radio" name="ffw_sw_goal" value="general" <?php checked( 'general', $default_goal ); ?> />
                    <span class="ffw-sw-choice-icon"><span class="dashicons dashicons-editor-help" aria-hidden="true"></span></span>
                    <span class="ffw-sw-choice-title"><?php esc_html_e( 'General FAQs', 'faq-for-woocommerce' ); ?></span>
                    <span class="ffw-sw-choice-text"><?php esc_html_e( 'For help pages, posts and landing pages. Show them anywhere with a shortcode, block or widget.', 'faq-for-woocommerce' ); ?></span>
                </label>

                <label class="ffw-sw-choice<?php echo $has_woo ? '' : ' is-disabled'; ?>">
                    <input type="radio" name="ffw_sw_goal" value="product" <?php checked( 'product', $default_goal ); ?> <?php disabled( ! $has_woo ); ?> />
                    <span class="ffw-sw-choice-icon"><span class="dashicons dashicons-cart" aria-hidden="true"></span></span>
                    <span class="ffw-sw-choice-title"><?php esc_html_e( 'Product FAQs', 'faq-for-woocommerce' ); ?></span>
                    <span class="ffw-sw-choice-text">
                        <?php
                        if ( $has_woo ) {
                            esc_html_e( 'Answer buyer questions right on your WooCommerce product pages, in their own FAQs tab.', 'faq-for-woocommerce' );
                        } else {
                            esc_html_e( 'Needs WooCommerce. Install WooCommerce to add FAQs to your products.', 'faq-for-woocommerce' );
                        }
                        ?>
                    </span>
                </label>
            </div>

            <div class="ffw-sw-actions">
                <span></span>
                <div class="ffw-sw-actions-right">
                    <button type="button" class="ffw-sw-link" data-ffw-skip><?php esc_html_e( 'Skip this step', 'faq-for-woocommerce' ); ?></button>
                    <button type="button" class="ffw-sw-btn ffw-sw-btn-primary" data-ffw-next><?php esc_html_e( 'Continue', 'faq-for-woocommerce' ); ?></button>
                </div>
            </div>
        </section>

        <?php // Step 2: first FAQs. ?>
        <section class="ffw-sw-step" data-step="faqs" hidden>
            <h1><?php esc_html_e( 'Create your very first FAQs', 'faq-for-woocommerce' ); ?></h1>
            <p class="ffw-sw-lead">
                <span data-ffw-goal-only="general"><?php esc_html_e( 'We added the questions visitors ask most. Edit the replies to match your business, or just continue.', 'faq-for-woocommerce' ); ?></span>
                <span data-ffw-goal-only="product"><?php esc_html_e( 'We added the questions shoppers ask most. Edit the replies to match your store, pick products, or just continue.', 'faq-for-woocommerce' ); ?></span>
            </p>

            <div class="ffw-sw-field" data-ffw-goal-only="general">
                <label for="ffw-sw-category"><?php esc_html_e( 'FAQ category', 'faq-for-woocommerce' ); ?> <span class="ffw-sw-optional"><?php esc_html_e( '(optional)', 'faq-for-woocommerce' ); ?></span></label>
                <input type="text" id="ffw-sw-category" class="ffw-sw-input" placeholder="<?php esc_attr_e( 'e.g. Shipping, Returns, Getting started', 'faq-for-woocommerce' ); ?>" />
            </div>

            <?php if ( $has_woo ) : ?>
                <div class="ffw-sw-field" data-ffw-goal-only="product">
                    <label for="ffw-sw-products"><?php esc_html_e( 'Show on these products', 'faq-for-woocommerce' ); ?> <span class="ffw-sw-optional"><?php esc_html_e( '(optional)', 'faq-for-woocommerce' ); ?></span></label>
                    <select id="ffw-sw-products" class="wc-product-search" multiple="multiple" style="width:100%;" data-placeholder="<?php esc_attr_e( 'Search for a product…', 'faq-for-woocommerce' ); ?>" data-action="woocommerce_json_search_products_and_variations"></select>
                    <p class="ffw-sw-help"><?php esc_html_e( 'You can also assign FAQs to product categories and tags later, from any FAQ.', 'faq-for-woocommerce' ); ?></p>
                </div>
            <?php endif; ?>

            <div class="ffw-sw-faqs" id="ffw-sw-faqs"></div>

            <button type="button" class="ffw-sw-add" id="ffw-sw-add-faq">
                <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                <?php esc_html_e( 'Add another FAQ', 'faq-for-woocommerce' ); ?>
            </button>

            <p class="ffw-sw-error" id="ffw-sw-faqs-error" role="alert" hidden></p>

            <div class="ffw-sw-actions">
                <button type="button" class="ffw-sw-link" data-ffw-back><span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span><?php esc_html_e( 'Back', 'faq-for-woocommerce' ); ?></button>
                <div class="ffw-sw-actions-right">
                    <button type="button" class="ffw-sw-link" data-ffw-skip><?php esc_html_e( 'Skip this step', 'faq-for-woocommerce' ); ?></button>
                    <button type="button" class="ffw-sw-btn ffw-sw-btn-primary" id="ffw-sw-save-faqs"><?php esc_html_e( 'Save and continue', 'faq-for-woocommerce' ); ?></button>
                </div>
            </div>

            <template id="ffw-sw-faq-template">
                <div class="ffw-sw-faq">
                    <div class="ffw-sw-faq-head">
                        <span class="ffw-sw-faq-num"></span>
                        <button type="button" class="ffw-sw-faq-remove" aria-label="<?php esc_attr_e( 'Remove this FAQ', 'faq-for-woocommerce' ); ?>" title="<?php esc_attr_e( 'Remove this FAQ', 'faq-for-woocommerce' ); ?>">
                            <span class="dashicons dashicons-minus" aria-hidden="true"></span>
                        </button>
                    </div>
                    <input type="text" class="ffw-sw-input ffw-sw-faq-question" aria-label="<?php esc_attr_e( 'Question', 'faq-for-woocommerce' ); ?>" />
                    <textarea class="ffw-sw-input ffw-sw-faq-answer" rows="3" aria-label="<?php esc_attr_e( 'Answer', 'faq-for-woocommerce' ); ?>"></textarea>
                </div>
            </template>
            <?php // Common questions prefilled per goal; users can edit them or just continue. ?>
            <script type="application/json" id="ffw-sw-samples"><?php
                echo wp_json_encode( FAQ_Woocommerce_Setup_Wizard::samples() );
            ?></script>
        </section>

        <?php // Step 3: display. ?>
        <section class="ffw-sw-step" data-step="display" hidden>
            <h1><?php esc_html_e( 'Your shortcode is ready', 'faq-for-woocommerce' ); ?></h1>
            <p class="ffw-sw-lead" data-ffw-shortcode-lead><?php esc_html_e( 'Copy it into any page or post, or use the block or widget below.', 'faq-for-woocommerce' ); ?></p>

            <div class="ffw-sw-shortcode">
                <code id="ffw-sw-shortcode">[ffw_template]</code>
                <button type="button" class="ffw-sw-btn ffw-sw-btn-soft" id="ffw-sw-copy">
                    <span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
                    <span class="ffw-sw-copy-label"><?php esc_html_e( 'Copy', 'faq-for-woocommerce' ); ?></span>
                </button>
            </div>

            <div class="ffw-sw-note" data-ffw-goal-only="product">
                <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
                <div>
                    <strong><?php esc_html_e( 'Already live on your products', 'faq-for-woocommerce' ); ?></strong>
                    <p><?php esc_html_e( 'Product FAQs appear automatically in a FAQs tab on each product they are assigned to. No shortcode needed.', 'faq-for-woocommerce' ); ?></p>
                    <a href="#" class="ffw-sw-inline-link" id="ffw-sw-product-link" target="_blank" rel="noopener" hidden><?php esc_html_e( 'View the product page', 'faq-for-woocommerce' ); ?></a>
                </div>
            </div>

            <div class="ffw-sw-ways">
                <div class="ffw-sw-way">
                    <span class="ffw-sw-way-icon"><span class="dashicons dashicons-shortcode" aria-hidden="true"></span></span>
                    <strong><?php esc_html_e( 'Shortcode', 'faq-for-woocommerce' ); ?></strong>
                    <p><?php esc_html_e( 'Paste the shortcode above into any page, post or text widget.', 'faq-for-woocommerce' ); ?></p>
                </div>
                <div class="ffw-sw-way">
                    <span class="ffw-sw-way-icon"><span class="dashicons dashicons-block-default" aria-hidden="true"></span></span>
                    <strong><?php esc_html_e( 'Gutenberg block', 'faq-for-woocommerce' ); ?></strong>
                    <p>
                        <?php
                        printf(
                            /* translators: %s: block name */
                            esc_html__( 'Edit a page, click +, and search for %s.', 'faq-for-woocommerce' ),
                            '<em>' . esc_html__( 'Happy Prebuilt FAQs', 'faq-for-woocommerce' ) . '</em>'
                        );
                        ?>
                    </p>
                </div>
                <div class="ffw-sw-way">
                    <span class="ffw-sw-way-icon"><span class="dashicons dashicons-welcome-widgets-menus" aria-hidden="true"></span></span>
                    <strong><?php esc_html_e( 'Elementor widget', 'faq-for-woocommerce' ); ?></strong>
                    <p>
                        <?php
                        printf(
                            /* translators: %s: widget name */
                            esc_html__( 'In Elementor, search the widgets panel for %s.', 'faq-for-woocommerce' ),
                            '<em>' . esc_html__( 'Happy Prebuilt FAQs', 'faq-for-woocommerce' ) . '</em>'
                        );
                        ?>
                    </p>
                    <?php if ( ! $has_elementor ) : ?>
                        <span class="ffw-sw-tag"><?php esc_html_e( 'When Elementor is active', 'faq-for-woocommerce' ); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="ffw-sw-actions">
                <button type="button" class="ffw-sw-link" data-ffw-back><span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span><?php esc_html_e( 'Back', 'faq-for-woocommerce' ); ?></button>
                <div class="ffw-sw-actions-right">
                    <button type="button" class="ffw-sw-link" data-ffw-skip><?php esc_html_e( 'Skip this step', 'faq-for-woocommerce' ); ?></button>
                    <button type="button" class="ffw-sw-btn ffw-sw-btn-primary" data-ffw-next><?php esc_html_e( 'Continue', 'faq-for-woocommerce' ); ?></button>
                </div>
            </div>
        </section>

        <?php if ( ! empty( $extras ) ) : ?>
            <?php // Step 4: recommended tools. ?>
            <section class="ffw-sw-step" data-step="extras" hidden>
                <h1><?php esc_html_e( 'Recommended for your site', 'faq-for-woocommerce' ); ?></h1>
                <p class="ffw-sw-lead"><?php esc_html_e( 'Free tools that work well alongside Happy FAQs. Keep the ones you want.', 'faq-for-woocommerce' ); ?></p>

                <div class="ffw-sw-extras">
                    <?php foreach ( $extras as $key => $extra ) : ?>
                        <label class="ffw-sw-extra">
                            <span class="ffw-sw-extra-icon"><span class="dashicons <?php echo esc_attr( $extra['icon'] ); ?>" aria-hidden="true"></span></span>
                            <span class="ffw-sw-extra-copy">
                                <span class="ffw-sw-extra-title"><?php echo esc_html( $extra['title'] ); ?></span>
                                <span class="ffw-sw-extra-text"><?php echo esc_html( $extra['description'] ); ?></span>
                            </span>
                            <input type="checkbox" class="ffw-sw-extra-check" value="<?php echo esc_attr( $key ); ?>" checked="checked" />
                            <span class="ffw-sw-check" aria-hidden="true"><span class="dashicons dashicons-yes"></span></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <p class="ffw-sw-fineprint"><?php esc_html_e( 'Selected tools are free and will be installed from WordPress.org. You can deactivate them any time from Plugins.', 'faq-for-woocommerce' ); ?></p>

                <div class="ffw-sw-actions">
                    <button type="button" class="ffw-sw-link" data-ffw-back><span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span><?php esc_html_e( 'Back', 'faq-for-woocommerce' ); ?></button>
                    <div class="ffw-sw-actions-right">
                        <button type="button" class="ffw-sw-link" data-ffw-skip><?php esc_html_e( 'Skip this step', 'faq-for-woocommerce' ); ?></button>
                        <button type="button" class="ffw-sw-btn ffw-sw-btn-primary" id="ffw-sw-install-extras"><?php esc_html_e( 'Continue', 'faq-for-woocommerce' ); ?></button>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php // Step 5: welcome. ?>
        <section class="ffw-sw-step ffw-sw-step-ready" data-step="ready" hidden>
            <div class="ffw-sw-ready-badge"><span class="dashicons dashicons-yes" aria-hidden="true"></span></div>
            <h1><?php esc_html_e( 'Welcome to Happy FAQs!', 'faq-for-woocommerce' ); ?></h1>
            <p class="ffw-sw-lead"><?php esc_html_e( 'You are all set. Build your FAQ library, show it anywhere, and let customers find answers on their own.', 'faq-for-woocommerce' ); ?></p>

            <ul class="ffw-sw-summary">
                <li id="ffw-sw-summary-faqs" hidden><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><span class="ffw-sw-summary-text"></span></li>
                <li id="ffw-sw-summary-tools" hidden><span class="dashicons dashicons-update" aria-hidden="true"></span><span class="ffw-sw-summary-text"></span></li>
            </ul>

            <div class="ffw-sw-ready-actions">
                <a class="ffw-sw-btn ffw-sw-btn-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=ffw-dashboard' ) ); ?>"><?php esc_html_e( 'Go to dashboard', 'faq-for-woocommerce' ); ?></a>
                <a class="ffw-sw-btn ffw-sw-btn-soft" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ffw' ) ); ?>"><?php esc_html_e( 'Add a new FAQ', 'faq-for-woocommerce' ); ?></a>
            </div>

            <div class="ffw-sw-ready-links">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ffw' ) ); ?>"><?php esc_html_e( 'All FAQs', 'faq-for-woocommerce' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ffw&page=ffw-patterns' ) ); ?>"><?php esc_html_e( 'FAQ patterns', 'faq-for-woocommerce' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ffw&page=woocommerce-faq' ) ); ?>"><?php esc_html_e( 'Settings', 'faq-for-woocommerce' ); ?></a>
                <a href="<?php echo esc_url( FFW_DOC_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Documentation', 'faq-for-woocommerce' ); ?></a>
            </div>
        </section>
    </main>

    <footer class="ffw-sw-footer">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=ffw-dashboard' ) ); ?>" id="ffw-sw-exit"><?php esc_html_e( 'Exit setup and go to the dashboard', 'faq-for-woocommerce' ); ?></a>
    </footer>
</div>
