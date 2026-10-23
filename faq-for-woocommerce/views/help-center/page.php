<?php
/**
 * Happy FAQs → Help Center Page.
 *
 * @package FAQ_Woocommerce
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wrap ffw-patterns ffw-hc-page">
    <h1 class="screen-reader-text"><?php esc_html_e( 'Help Center Page', 'faq-for-woocommerce' ); ?></h1>

    <header class="ffw-pt-hero">
        <p class="ffw-pt-eyebrow"><?php esc_html_e( 'Help Center Page', 'faq-for-woocommerce' ); ?></p>
        <h2 class="ffw-pt-title"><?php esc_html_e( 'Build a Help Center page in one click', 'faq-for-woocommerce' ); ?></h2>
        <p class="ffw-pt-lead"><?php esc_html_e( 'Search, the most asked questions, every FAQ by topic and a way to reach you, on one page. It stays in sync with your FAQ library as you add answers.', 'faq-for-woocommerce' ); ?></p>

        <ol class="ffw-pt-steps">
            <li><span>1</span><?php esc_html_e( 'Pick filter tabs or a category sidebar.', 'faq-for-woocommerce' ); ?></li>
            <li><span>2</span><?php esc_html_e( 'Choose the sections and watch the preview.', 'faq-for-woocommerce' ); ?></li>
            <li><span>3</span><?php esc_html_e( 'Create a draft or publish right away.', 'faq-for-woocommerce' ); ?></li>
        </ol>
    </header>

    <?php include __DIR__ . '/builder.php'; ?>
</div>
