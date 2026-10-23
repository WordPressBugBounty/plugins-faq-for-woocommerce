<?php
/**
 * Happy FAQs → Patterns gallery.
 *
 * @var array $patterns Pattern definitions, keyed by slug.
 * @var array $groups   Group labels, keyed by group.
 *
 * @package FAQ_Woocommerce
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$ffw_styles = array(
    'cards'   => __( 'Cards', 'faq-for-woocommerce' ),
    'minimal' => __( 'Minimal', 'faq-for-woocommerce' ),
    'soft'    => __( 'Soft', 'faq-for-woocommerce' ),
);
$ffw_total  = count( $patterns );
?>
<div class="wrap ffw-patterns">
    <h1 class="screen-reader-text"><?php esc_html_e( 'FAQ Patterns', 'faq-for-woocommerce' ); ?></h1>

    <header class="ffw-pt-hero">
        <p class="ffw-pt-eyebrow"><?php esc_html_e( 'Pattern library', 'faq-for-woocommerce' ); ?></p>
        <h2 class="ffw-pt-title">
            <?php
            /* translators: %d: number of patterns */
            echo esc_html( sprintf( _n( '%d ready-made FAQ section', '%d ready-made FAQ sections', $ffw_total, 'faq-for-woocommerce' ), $ffw_total ) );
            ?>
        </h2>
        <p class="ffw-pt-lead"><?php esc_html_e( 'Designed sections with real starter questions. Create a page in one click, or insert them anywhere from the block editor. Everything is bundled, free and works offline.', 'faq-for-woocommerce' ); ?></p>

        <ol class="ffw-pt-steps">
            <li><span>1</span><?php esc_html_e( 'Pick a pattern and click Create page.', 'faq-for-woocommerce' ); ?></li>
            <li><span>2</span><?php esc_html_e( 'Edit the questions right in the editor.', 'faq-for-woocommerce' ); ?></li>
            <li><span>3</span><?php esc_html_e( 'Switch the look under Styles: Cards, Minimal or Soft.', 'faq-for-woocommerce' ); ?></li>
        </ol>
    </header>

    <div class="ffw-pt-toolbar">
        <div class="ffw-pt-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Pattern groups', 'faq-for-woocommerce' ); ?>">
            <button type="button" class="ffw-pt-tab is-active" role="tab" aria-selected="true" data-group="all">
                <?php esc_html_e( 'All', 'faq-for-woocommerce' ); ?> <span class="ffw-pt-tab-count"><?php echo esc_html( $ffw_total ); ?></span>
            </button>
            <?php foreach ( $groups as $ffw_group => $ffw_label ) : ?>
                <?php
                $ffw_count = count(
                    array_filter(
                        $patterns,
                        function ( $pattern ) use ( $ffw_group ) {
                            return isset( $pattern['group'] ) && $pattern['group'] === $ffw_group;
                        }
                    )
                );
                if ( ! $ffw_count ) {
                    continue;
                }
                ?>
                <button type="button" class="ffw-pt-tab" role="tab" aria-selected="false" data-group="<?php echo esc_attr( $ffw_group ); ?>">
                    <?php echo esc_html( $ffw_label ); ?> <span class="ffw-pt-tab-count"><?php echo esc_html( $ffw_count ); ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <label class="ffw-pt-search">
            <span class="screen-reader-text"><?php esc_html_e( 'Search patterns', 'faq-for-woocommerce' ); ?></span>
            <span class="dashicons dashicons-search" aria-hidden="true"></span>
            <input type="search" id="ffw-pt-search" placeholder="<?php esc_attr_e( 'Search patterns, e.g. shipping', 'faq-for-woocommerce' ); ?>" autocomplete="off">
        </label>
    </div>

    <p class="ffw-pt-count" id="ffw-pt-count" aria-live="polite"></p>

    <div class="ffw-pt-grid" id="ffw-pt-grid">
        <?php foreach ( $patterns as $ffw_slug => $ffw_pattern ) : ?>
            <?php
            $ffw_group_key = isset( $ffw_pattern['group'] ) ? $ffw_pattern['group'] : 'general';
            $ffw_preview   = FAQ_Woocommerce_Patterns::get_preview_url( $ffw_slug );
            $ffw_style     = '';
            if ( preg_match( '/is-style-ffw-([a-z]+)/', $ffw_pattern['content'], $ffw_match ) && isset( $ffw_styles[ $ffw_match[1] ] ) ) {
                $ffw_style = $ffw_styles[ $ffw_match[1] ];
            }
            $ffw_search = strtolower(
                $ffw_pattern['title'] . ' ' . $ffw_pattern['description'] . ' '
                . ( isset( $ffw_pattern['keywords'] ) ? implode( ' ', $ffw_pattern['keywords'] ) : '' ) . ' '
                . ( isset( $groups[ $ffw_group_key ] ) ? $groups[ $ffw_group_key ] : '' )
            );
            ?>
            <article class="ffw-pt-card" data-group="<?php echo esc_attr( $ffw_group_key ); ?>" data-search="<?php echo esc_attr( $ffw_search ); ?>">
                <a class="ffw-pt-frame" href="<?php echo esc_url( $ffw_preview ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: pattern title */ __( 'Open a full preview of %s', 'faq-for-woocommerce' ), $ffw_pattern['title'] ) ); ?>">
                    <span class="ffw-pt-frame-skeleton" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                    <iframe data-src="<?php echo esc_url( $ffw_preview ); ?>" title="<?php echo esc_attr( $ffw_pattern['title'] ); ?>" tabindex="-1" aria-hidden="true" scrolling="no"></iframe>
                    <span class="ffw-pt-frame-hint"><span class="dashicons dashicons-visibility" aria-hidden="true"></span><?php esc_html_e( 'Full preview', 'faq-for-woocommerce' ); ?></span>
                </a>

                <div class="ffw-pt-body">
                    <div class="ffw-pt-meta">
                        <?php if ( isset( $groups[ $ffw_group_key ] ) ) : ?>
                            <span class="ffw-pt-tag ffw-pt-tag-<?php echo esc_attr( $ffw_group_key ); ?>"><?php echo esc_html( $groups[ $ffw_group_key ] ); ?></span>
                        <?php endif; ?>
                        <?php if ( $ffw_style ) : ?>
                            <span class="ffw-pt-tag"><?php echo esc_html( $ffw_style ); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="ffw-pt-name"><?php echo esc_html( $ffw_pattern['title'] ); ?></h3>
                    <p class="ffw-pt-desc"><?php echo esc_html( $ffw_pattern['description'] ); ?></p>

                    <div class="ffw-pt-actions">
                        <a class="ffw-pt-btn ffw-pt-btn-primary" href="<?php echo esc_url( FAQ_Woocommerce_Patterns::get_create_url( $ffw_slug ) ); ?>"><?php esc_html_e( 'Create page', 'faq-for-woocommerce' ); ?></a>
                        <button type="button" class="ffw-pt-btn ffw-pt-btn-ghost" data-ffw-copy="<?php echo esc_attr( $ffw_slug ); ?>">
                            <span class="dashicons dashicons-admin-page" aria-hidden="true"></span><?php esc_html_e( 'Copy', 'faq-for-woocommerce' ); ?>
                        </button>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="ffw-pt-empty" id="ffw-pt-empty" hidden>
        <p><?php esc_html_e( 'No patterns match your search.', 'faq-for-woocommerce' ); ?></p>
        <button type="button" class="ffw-pt-btn ffw-pt-btn-ghost" id="ffw-pt-reset"><?php esc_html_e( 'Show all patterns', 'faq-for-woocommerce' ); ?></button>
    </div>

    <p class="ffw-pt-footnote">
        <?php esc_html_e( 'Tip: in the block editor, open the inserter (+), choose Patterns and pick the Happy FAQs category.', 'faq-for-woocommerce' ); ?>
    </p>

    <div class="ffw-pt-toast" id="ffw-pt-toast" role="status" aria-live="polite" hidden></div>
</div>
