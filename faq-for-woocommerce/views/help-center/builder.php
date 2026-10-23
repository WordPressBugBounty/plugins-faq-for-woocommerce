<?php
/**
 * Help Center builder form and live preview.
 *
 * @package FAQ_Woocommerce
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$ffw_hc         = FAQ_Woocommerce_Help_Center::defaults();
$ffw_hc_search  = FAQ_Woocommerce_Help_Center::has_pro_search();
$ffw_hc_form    = FAQ_Woocommerce_Help_Center::has_pro_form();
$ffw_hc_cats    = count( FAQ_Woocommerce_Help_Center::categories() );
$ffw_hc_preview = FAQ_Woocommerce_Help_Center::preview_url();
$ffw_hc_pro     = '<span class="ffw-hc-pro">' . esc_html__( 'Pro', 'faq-for-woocommerce' ) . '</span>';
?>
<section class="ffw-hc-builder" aria-label="<?php esc_attr_e( 'Help Center builder', 'faq-for-woocommerce' ); ?>">
    <form class="ffw-hc-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-preview="<?php echo esc_url( $ffw_hc_preview ); ?>">
        <input type="hidden" name="action" value="<?php echo esc_attr( FAQ_Woocommerce_Help_Center::CREATE_ACTION ); ?>">
        <?php wp_nonce_field( FAQ_Woocommerce_Help_Center::CREATE_ACTION ); ?>

        <h2 class="ffw-hc-heading"><?php esc_html_e( 'Set up your page', 'faq-for-woocommerce' ); ?></h2>

        <div class="ffw-hc-field">
            <label for="ffw-hc-page-title"><?php esc_html_e( 'Page title', 'faq-for-woocommerce' ); ?></label>
            <input type="text" id="ffw-hc-page-title" name="ffw_hc[title]" value="<?php echo esc_attr( $ffw_hc['title'] ); ?>">
        </div>

        <fieldset class="ffw-hc-field">
            <legend><?php esc_html_e( 'Topics', 'faq-for-woocommerce' ); ?></legend>
            <div class="ffw-hc-choices">
                <label><input type="radio" name="ffw_hc[layout]" value="tabs" checked> <span><strong><?php esc_html_e( 'Filter tabs', 'faq-for-woocommerce' ); ?></strong><small><?php esc_html_e( 'One list with a tab for each category.', 'faq-for-woocommerce' ); ?></small></span></label>
                <label><input type="radio" name="ffw_hc[layout]" value="sidebar" <?php disabled( 0, $ffw_hc_cats ); ?>> <span><strong><?php esc_html_e( 'Category sidebar', 'faq-for-woocommerce' ); ?></strong><small>
                    <?php
                    echo $ffw_hc_cats
                        ? esc_html__( 'A topic list on the left, a section per category.', 'faq-for-woocommerce' )
                        : esc_html__( 'Add FAQ categories first.', 'faq-for-woocommerce' );
                    ?>
                </small></span></label>
            </div>
        </fieldset>

        <div class="ffw-hc-row">
            <div class="ffw-hc-field">
                <label for="ffw-hc-search"><?php esc_html_e( 'Search', 'faq-for-woocommerce' ); ?></label>
                <select id="ffw-hc-search" name="ffw_hc[search]">
                    <option value="library" <?php selected( 'library', $ffw_hc['search'] ); ?> <?php disabled( ! $ffw_hc_search ); ?>><?php echo esc_html( $ffw_hc_search ? __( 'Search all FAQs at the top', 'faq-for-woocommerce' ) : __( 'Search all FAQs at the top (Pro)', 'faq-for-woocommerce' ) ); ?></option>
                    <option value="inline" <?php selected( 'inline', $ffw_hc['search'] ); ?>><?php esc_html_e( 'Search box on the lists', 'faq-for-woocommerce' ); ?></option>
                    <option value="none"><?php esc_html_e( 'No search', 'faq-for-woocommerce' ); ?></option>
                </select>
            </div>
            <div class="ffw-hc-field">
                <label for="ffw-hc-style"><?php esc_html_e( 'Style', 'faq-for-woocommerce' ); ?></label>
                <select id="ffw-hc-style" name="ffw_hc[style]">
                    <option value="cards"><?php esc_html_e( 'Cards', 'faq-for-woocommerce' ); ?></option>
                    <option value="minimal"><?php esc_html_e( 'Minimal', 'faq-for-woocommerce' ); ?></option>
                    <option value="soft"><?php esc_html_e( 'Soft', 'faq-for-woocommerce' ); ?></option>
                </select>
            </div>
        </div>

        <label class="ffw-hc-check"><input type="checkbox" name="ffw_hc[popular]" value="1" checked> <?php esc_html_e( 'Most asked questions section', 'faq-for-woocommerce' ); ?></label>

        <div class="ffw-hc-field">
            <label for="ffw-hc-contact"><?php esc_html_e( '"Still need help?" section', 'faq-for-woocommerce' ); ?></label>
            <select id="ffw-hc-contact" name="ffw_hc[contact]">
                <option value="form" <?php selected( 'form', $ffw_hc['contact'] ); ?> <?php disabled( ! $ffw_hc_form ); ?>><?php echo esc_html( $ffw_hc_form ? __( '"Ask a question" form', 'faq-for-woocommerce' ) : __( '"Ask a question" form (Pro)', 'faq-for-woocommerce' ) ); ?></option>
                <option value="link" <?php selected( 'link', $ffw_hc['contact'] ); ?>><?php esc_html_e( 'Contact button', 'faq-for-woocommerce' ); ?></option>
                <option value="none"><?php esc_html_e( 'None', 'faq-for-woocommerce' ); ?></option>
            </select>
        </div>

        <div class="ffw-hc-field" data-ffw-hc-show="link">
            <label for="ffw-hc-contact-url"><?php esc_html_e( 'Contact button link', 'faq-for-woocommerce' ); ?></label>
            <input type="text" id="ffw-hc-contact-url" name="ffw_hc[contact_url]" value="<?php echo esc_attr( $ffw_hc['contact_url'] ); ?>" placeholder="https://… or mailto:…">
        </div>

        <?php if ( ! $ffw_hc_search || ! $ffw_hc_form ) : ?>
            <p class="ffw-hc-upsell">
                <?php echo wp_kses( $ffw_hc_pro, array( 'span' => array( 'class' => true ) ) ); ?>
                <?php esc_html_e( 'Happy FAQs Pro adds a search across every FAQ at the top and an "Ask a question" form that turns questions into FAQs.', 'faq-for-woocommerce' ); ?>
            </p>
        <?php endif; ?>

        <div class="ffw-hc-actions">
            <button type="submit" class="ffw-pt-btn ffw-pt-btn-primary"><?php esc_html_e( 'Create draft page', 'faq-for-woocommerce' ); ?></button>
            <?php if ( current_user_can( 'publish_pages' ) ) : ?>
                <button type="submit" class="ffw-pt-btn ffw-pt-btn-ghost" name="ffw_hc[publish]" value="1"><?php esc_html_e( 'Publish now', 'faq-for-woocommerce' ); ?></button>
            <?php endif; ?>
        </div>
    </form>

    <div class="ffw-hc-preview">
        <div class="ffw-hc-preview-bar" aria-hidden="true"><i></i><i></i><i></i></div>
        <div class="ffw-hc-preview-frame">
            <iframe title="<?php esc_attr_e( 'Help Center preview', 'faq-for-woocommerce' ); ?>" tabindex="-1" aria-hidden="true"></iframe>
        </div>
        <a class="ffw-hc-preview-open" href="<?php echo esc_url( $ffw_hc_preview ); ?>" target="_blank" rel="noopener"><span class="dashicons dashicons-visibility" aria-hidden="true"></span><?php esc_html_e( 'Full preview', 'faq-for-woocommerce' ); ?></a>
    </div>
</section>
