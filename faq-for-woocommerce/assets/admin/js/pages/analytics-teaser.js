/**
 * Happy FAQs → Analytics (free): your numbers, and a preview of Pro analytics.
 *
 * @since 3.0.0
 */
( function ( wp, ui, data ) {
	'use strict';

	if ( ! ui || ! data ) {
		return;
	}

	var el = ui.el;
	var Fragment = ui.Fragment;
	var __ = wp.i18n.__;
	var _n = wp.i18n._n;
	var sprintf = wp.i18n.sprintf;
	var D = 'faq-for-woocommerce';

	// Sample data for the locked preview.
	var SAMPLE = [ 42, 58, 51, 66, 73, 61, 80, 92, 85, 104, 98, 117, 126, 112 ];

	function samplePath( values, max, close ) {
		var points = values.map( function ( value, i ) {
			return ( i ? 'L' : 'M' ) + Math.round( i / ( values.length - 1 ) * 600 ) + ' ' + Math.round( 160 - value / max * 150 );
		} ).join( ' ' );
		return close ? points + ' L600 160 L0 160 Z' : points;
	}

	function Teaser() {
		var votes = data.yes + data.no;
		var helpful = votes ? Math.round( 100 * data.yes / votes ) : null;
		var top = ( data.top || [] ).filter( function ( item ) {
			return item.views > 0;
		} );

		return el(
			Fragment,
			null,
			el( ui.PageHead, {
				title: __( 'Analytics', D ),
				description: __( 'How visitors use your FAQs.', D ),
				actions: data.needsUpdate
					? el( ui.Button, { variant: 'primary', href: data.pluginsUrl }, __( 'Update Happy FAQs Pro', D ) )
					: el( ui.Button, { variant: 'brand', icon: 'crown', href: ui.config.proUrl, target: '_blank' }, __( 'Get full analytics', D ) )
			} ),
			data.needsUpdate ? el( ui.Notice, { tone: 'info' }, el( 'p', null, __( 'Analytics is part of Happy FAQs Pro. Update Happy FAQs Pro to the latest version to open it.', D ) ) ) : null,
			el(
				'div',
				{ className: 'ffw-ui-grid is-3 ffw-an-stats' },
				el( ui.Stat, { icon: 'question', label: __( 'Published FAQs', D ), value: ui.number( data.published ) } ),
				el( ui.Stat, { icon: 'eye', label: __( 'Answer views', D ), value: ui.number( data.views ), note: __( 'All time', D ) } ),
				el( ui.Stat, {
					icon: 'thumb',
					label: __( 'Found helpful', D ),
					value: null === helpful ? '—' : helpful + '%',
					note: data.votesOn ? sprintf( _n( '%s vote', '%s votes', votes, D ), ui.number( votes ) ) : el( 'a', { href: data.settingsUrl }, __( 'Turn on "Was this helpful?" votes', D ) )
				} )
			),
			el(
				ui.Card,
				{ title: __( 'Most viewed answers', D ), description: __( 'Counted when a visitor opens an answer.', D ), flush: true },
				top.length ? el(
					'div',
					{ className: 'ffw-ui-table-wrap' },
					el(
						'table',
						{ className: 'ffw-ui-table' },
						el( 'thead', null, el( 'tr', null, el( 'th', null, __( 'Question', D ) ), el( 'th', { className: 'is-num' }, __( 'Views', D ) ), el( 'th', { className: 'is-num' }, __( 'Helpful', D ) ), el( 'th', { className: 'ffw-an-barcol' } ) ) ),
						el( 'tbody', null, top.map( function ( item ) {
							return el(
								'tr',
								{ key: item.id },
								el( 'td', null, el( 'a', { className: 'ffw-an-faq', href: data.manager + '#/edit/' + item.id }, item.title ) ),
								el( 'td', { className: 'is-num' }, ui.number( item.views ) ),
								el( 'td', { className: 'is-num' }, null === item.helpful ? el( 'span', { className: 'ffw-ui-muted' }, '—' ) : item.helpful + '%' ),
								el( 'td', { className: 'ffw-an-barcol' }, el( 'span', { className: 'ffw-an-bar' }, el( 'i', { style: { width: Math.max( 4, Math.round( 100 * item.views / top[ 0 ].views ) ) + '%' } } ) ) )
							);
						} ) )
					)
				) : el( ui.Empty, { icon: 'eye', title: __( 'No views yet', D ), text: __( 'Views appear here once visitors open your answers.', D ) } )
			),
			el(
				'section',
				{ className: 'ffw-an-locked', 'aria-label': __( 'Pro analytics preview', D ) },
				el(
					'div',
					{ className: 'ffw-an-sample', 'aria-hidden': 'true' },
					el(
						'div',
						{ className: 'ffw-ui-grid is-4' },
						[ [ __( 'FAQ opens', D ), '1,265', '+18%' ], [ __( 'Helpful rate', D ), '86%', '+4 pts' ], [ __( 'Searches', D ), '355', '+9%' ], [ __( 'No-result searches', D ), '7%', '−3 pts' ] ].map( function ( kpi ) {
							return el( ui.Stat, { key: kpi[ 0 ], label: kpi[ 0 ], value: kpi[ 1 ], note: kpi[ 2 ] } );
						} )
					),
					el(
						'div',
						{ className: 'ffw-ui-card ffw-an-chart' },
						el( 'svg', { viewBox: '0 0 600 160', preserveAspectRatio: 'none' },
							el( 'path', { d: samplePath( SAMPLE, 140, true ), fill: 'rgba(255,199,44,.25)' } ),
							el( 'path', { d: samplePath( SAMPLE, 140, false ), fill: 'none', stroke: '#111113', strokeWidth: 2.5, vectorEffect: 'non-scaling-stroke' } )
						)
					),
					el(
						'div',
						{ className: 'ffw-ui-grid is-2' },
						el( 'div', { className: 'ffw-ui-card ffw-an-list' }, el( 'strong', null, __( 'Top searches', D ) ), [ 'delivery time', 'returns', 'payment', 'sizes', 'tracking' ].map( function ( term, i ) {
							return el( 'span', { key: term }, term, el( 'b', null, 40 - i * 6 ) );
						} ) ),
						el( 'div', { className: 'ffw-ui-card ffw-an-list' }, el( 'strong', null, __( 'Searches with no answer', D ) ), [ 'gift wrapping', 'student discount', 'size guide', 'pick up in store', 'warranty' ].map( function ( term, i ) {
							return el( 'span', { key: term }, term, el( 'b', null, 14 - i * 2 ) );
						} ) )
					)
				),
				el(
					'div',
					{ className: 'ffw-an-overlay' },
					el(
						ui.Upsell,
						{ title: __( 'Know which FAQs help, and what is missing', D ), text: __( 'Pro tracks opens, helpful votes and searches over time, shows what visitors searched for and could not find, and turns those searches into new FAQs.', D ) },
						el( 'ul', { className: 'ffw-an-benefits' },
							[ __( 'Opens and helpful rate over time', D ), __( 'Top search terms', D ), __( 'Searches with no answer, one click to a new FAQ', D ), __( 'Top pages and products', D ), __( 'Tickets avoided by contact form suggestions', D ) ].map( function ( item ) {
								return el( 'li', { key: item }, el( ui.Icon, { name: 'check', size: 14, stroke: 3 } ), item );
							} )
						)
					)
				)
			)
		);
	}

	ui.mount( Teaser );
} )( window.wp, window.ffwUI, window.ffwPage );
