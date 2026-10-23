/**
 * Happy FAQs → Customer Questions (free): what the Pro feature does.
 *
 * @since 3.0.0
 */
( function ( wp, ui ) {
	'use strict';

	if ( ! ui ) {
		return;
	}

	var el = ui.el;
	var Fragment = ui.Fragment;
	var __ = wp.i18n.__;
	var D = 'faq-for-woocommerce';

	var SAMPLE = [
		{ q: __( 'Does this jacket run small?', D ), who: 'Maya', where: __( 'Rain Jacket', D ), status: 'pending' },
		{ q: __( 'Can I return a sale item?', D ), who: 'Leo', where: __( 'Returns page', D ), status: 'answered' },
		{ q: __( 'Is the charger included?', D ), who: 'Sam', where: __( 'Wireless Speaker', D ), status: 'faq' }
	];

	function Teaser() {
		return el(
			Fragment,
			null,
			el( ui.PageHead, {
				title: __( 'Customer Questions', D ),
				description: __( 'Let visitors ask what your FAQs do not answer yet, and turn good questions into FAQs.', D ),
				actions: el( ui.Button, { variant: 'brand', icon: 'crown', href: ui.config.proUrl, target: '_blank' }, __( 'Get Customer Questions', D ) )
			} ),
			el(
				'div',
				{ className: 'ffw-q-teaser' },
				el(
					ui.Card,
					{ className: 'ffw-q-mock', title: __( 'Your questions inbox', D ), description: __( 'A preview with sample questions.', D ), badge: el( ui.ProBadge ), flush: true },
					el(
						'ul',
						{ className: 'ffw-q-list', 'aria-hidden': 'true' },
						SAMPLE.map( function ( item ) {
							return el(
								'li',
								{ key: item.q },
								el( 'span', { className: 'ffw-q-avatar' }, item.who.charAt( 0 ) ),
								el( 'span', { className: 'ffw-q-text' }, el( 'strong', null, item.q ), el( 'small', null, item.who + ' · ' + item.where ) ),
								'pending' === item.status ? el( ui.Badge, { tone: 'warn' }, __( 'Waiting', D ) ) : 'answered' === item.status ? el( ui.Badge, { tone: 'good' }, __( 'Answered', D ) ) : el( ui.Badge, { tone: 'brand' }, __( 'Now a FAQ', D ) ),
								el( 'span', { className: 'ffw-q-fake-btn' }, 'pending' === item.status ? __( 'Answer', D ) : __( 'Edit', D ) )
							);
						} )
					)
				),
				el(
					ui.Upsell,
					{ title: __( 'Questions become answers', D ), text: __( 'Happy FAQs Pro adds an "Ask a question" form to any page or product.', D ) },
					el(
						'ol',
						{ className: 'ffw-q-steps' },
						[
							__( 'A visitor asks on a product or page.', D ),
							__( 'You get an email and answer from this screen.', D ),
							__( 'They get your answer by email.', D ),
							__( 'Publish it as a FAQ, or show it on the product.', D )
						].map( function ( step, index ) {
							return el( 'li', { key: index }, el( 'span', null, index + 1 ), step );
						} )
					)
				)
			)
		);
	}

	ui.mount( Teaser );
} )( window.wp, window.ffwUI );
