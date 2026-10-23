<?php
/**
 * Happy FAQs → Analytics: Pro preview with sample data.
 *
 * @var bool $needs_update An older Happy FAQs Pro is active.
 *
 * @package FAQ_Woocommerce
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$ffw_views    = array( 42, 58, 51, 66, 73, 61, 80, 92, 85, 104, 98, 117, 126, 112 );
$ffw_searches = array( 12, 18, 15, 21, 19, 24, 27, 22, 30, 28, 34, 31, 38, 36 );
$ffw_area     = FAQ_Woocommerce_Analytics_Teaser::sample_path( $ffw_views, 140 );

$ffw_benefits = array(
    array( 'chart-area', __( 'FAQ opens and helpful rate', 'faq-for-woocommerce' ), __( 'See which answers people read and which ones work.', 'faq-for-woocommerce' ) ),
    array( 'search', __( 'What visitors search for', 'faq-for-woocommerce' ), __( 'Top search terms from your FAQ search boxes.', 'faq-for-woocommerce' ) ),
    array( 'lightbulb', __( 'Searches with no answer', 'faq-for-woocommerce' ), __( 'Your next FAQs, ready to add in one click.', 'faq-for-woocommerce' ) ),
    array( 'cart', __( 'Top pages and products', 'faq-for-woocommerce' ), __( 'Where FAQs help most, including product pages.', 'faq-for-woocommerce' ) ),
);

$ffw_kpis = array(
    array( __( 'FAQ opens', 'faq-for-woocommerce' ), '1,265', '+18%' ),
    array( __( 'Helpful rate', 'faq-for-woocommerce' ), '86%', '+4 pts' ),
    array( __( 'Searches', 'faq-for-woocommerce' ), '355', '+9%' ),
    array( __( 'No-result searches', 'faq-for-woocommerce' ), '7%', '−3 pts' ),
);

$ffw_top = array(
    array( __( 'How long does delivery take?', 'faq-for-woocommerce' ), 212, '92%' ),
    array( __( 'How do I return an item?', 'faq-for-woocommerce' ), 168, '88%' ),
    array( __( 'Which payment methods do you accept?', 'faq-for-woocommerce' ), 131, '95%' ),
    array( __( 'Can I change or cancel my order?', 'faq-for-woocommerce' ), 97, '71%' ),
    array( __( 'Do you ship internationally?', 'faq-for-woocommerce' ), 76, '84%' ),
);

$ffw_misses = array(
    array( __( 'gift wrapping', 'faq-for-woocommerce' ), 14 ),
    array( __( 'student discount', 'faq-for-woocommerce' ), 9 ),
    array( __( 'size guide', 'faq-for-woocommerce' ), 7 ),
    array( __( 'pick up in store', 'faq-for-woocommerce' ), 5 ),
    array( __( 'warranty', 'faq-for-woocommerce' ), 4 ),
);
?>
<div class="wrap ffw-ant">
    <h1 class="screen-reader-text"><?php esc_html_e( 'FAQ Analytics', 'faq-for-woocommerce' ); ?></h1>

    <section class="ffw-ant-pro" aria-labelledby="ffw-ant-pro-title">
        <div class="ffw-ant-pro-text">
            <p class="ffw-ant-badge"><span class="dashicons dashicons-star-filled" aria-hidden="true"></span><?php esc_html_e( 'Pro feature', 'faq-for-woocommerce' ); ?></p>
            <h2 id="ffw-ant-pro-title"><?php esc_html_e( 'Know which FAQs help, and what is missing', 'faq-for-woocommerce' ); ?></h2>
            <p class="ffw-ant-lead">
                <?php
                echo $needs_update
                    ? esc_html__( 'Analytics is part of Happy FAQs Pro. Update Happy FAQs Pro to the latest version to open it.', 'faq-for-woocommerce' )
                    : esc_html__( 'FAQ Analytics is part of Happy FAQs Pro. Track opens, helpful votes and searches, and turn unanswered searches into new FAQs.', 'faq-for-woocommerce' );
                ?>
            </p>
            <div class="ffw-ant-actions">
                <?php if ( $needs_update ) : ?>
                    <a class="ffw-ant-btn ffw-ant-btn-primary" href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"><?php esc_html_e( 'Go to Plugins', 'faq-for-woocommerce' ); ?></a>
                <?php else : ?>
                    <a class="ffw-ant-btn ffw-ant-btn-primary" href="<?php echo esc_url( FFW_PRO_URL ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Upgrade to Pro', 'faq-for-woocommerce' ); ?></a>
                <?php endif; ?>
                <span class="ffw-ant-note"><?php esc_html_e( 'Daily totals only. No IP addresses stored.', 'faq-for-woocommerce' ); ?></span>
            </div>
        </div>
        <ul class="ffw-ant-benefits">
            <?php foreach ( $ffw_benefits as $ffw_benefit ) : ?>
                <li>
                    <span class="dashicons dashicons-<?php echo esc_attr( $ffw_benefit[0] ); ?>" aria-hidden="true"></span>
                    <strong><?php echo esc_html( $ffw_benefit[1] ); ?></strong>
                    <span><?php echo esc_html( $ffw_benefit[2] ); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <div class="ffw-ant-preview" aria-hidden="true" inert>
        <div class="ffw-ant-head">
            <div>
                <p class="ffw-ant-eyebrow"><?php esc_html_e( 'Analytics', 'faq-for-woocommerce' ); ?></p>
                <p class="ffw-ant-title"><?php esc_html_e( 'How your FAQs are doing', 'faq-for-woocommerce' ); ?></p>
            </div>
            <div class="ffw-ant-range"><span><?php esc_html_e( '7 days', 'faq-for-woocommerce' ); ?></span><span class="is-active"><?php esc_html_e( '30 days', 'faq-for-woocommerce' ); ?></span><span><?php esc_html_e( '90 days', 'faq-for-woocommerce' ); ?></span></div>
        </div>

        <div class="ffw-ant-kpis">
            <?php foreach ( $ffw_kpis as $ffw_kpi ) : ?>
                <div class="ffw-ant-card">
                    <p class="ffw-ant-kpi-label"><?php echo esc_html( $ffw_kpi[0] ); ?></p>
                    <p class="ffw-ant-kpi-value"><?php echo esc_html( $ffw_kpi[1] ); ?> <span class="ffw-ant-delta"><?php echo esc_html( $ffw_kpi[2] ); ?></span></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="ffw-ant-card ffw-ant-chart">
            <p class="ffw-ant-card-title"><?php esc_html_e( 'Activity', 'faq-for-woocommerce' ); ?></p>
            <svg viewBox="0 0 600 160" preserveAspectRatio="none" focusable="false">
                <line x1="0" y1="40" x2="600" y2="40" class="ffw-ant-grid" />
                <line x1="0" y1="80" x2="600" y2="80" class="ffw-ant-grid" />
                <line x1="0" y1="120" x2="600" y2="120" class="ffw-ant-grid" />
                <path class="ffw-ant-area" d="<?php echo esc_attr( $ffw_area . ' L600 160 L0 160 Z' ); ?>" />
                <path class="ffw-ant-line" d="<?php echo esc_attr( $ffw_area ); ?>" />
                <path class="ffw-ant-line is-searches" d="<?php echo esc_attr( FAQ_Woocommerce_Analytics_Teaser::sample_path( $ffw_searches, 140 ) ); ?>" />
            </svg>
        </div>

        <div class="ffw-ant-grid-2">
            <div class="ffw-ant-card">
                <p class="ffw-ant-card-title"><?php esc_html_e( 'Top FAQs', 'faq-for-woocommerce' ); ?></p>
                <?php foreach ( $ffw_top as $ffw_row ) : ?>
                    <div class="ffw-ant-row">
                        <span class="ffw-ant-name"><?php echo esc_html( $ffw_row[0] ); ?></span>
                        <b><?php echo esc_html( number_format_i18n( $ffw_row[1] ) ); ?></b>
                        <span class="ffw-ant-pill"><?php echo esc_html( $ffw_row[2] ); ?></span>
                    </div>
                    <span class="ffw-ant-bar"><i style="width:<?php echo esc_attr( (string) round( $ffw_row[1] / 212 * 100 ) ); ?>%"></i></span>
                <?php endforeach; ?>
            </div>
            <div class="ffw-ant-card">
                <p class="ffw-ant-card-title"><?php esc_html_e( 'Searches with no answer', 'faq-for-woocommerce' ); ?></p>
                <?php foreach ( $ffw_misses as $ffw_row ) : ?>
                    <div class="ffw-ant-row">
                        <span class="ffw-ant-name">“<?php echo esc_html( $ffw_row[0] ); ?>”</span>
                        <b><?php echo esc_html( number_format_i18n( $ffw_row[1] ) ); ?></b>
                        <span class="ffw-ant-pill is-outline"><?php esc_html_e( 'Add FAQ', 'faq-for-woocommerce' ); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
