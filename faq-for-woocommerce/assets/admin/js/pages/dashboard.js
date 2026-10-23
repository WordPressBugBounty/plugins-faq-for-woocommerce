/**
 * Happy FAQs → Dashboard (React).
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
	var useState = wp.element.useState;
	var __ = wp.i18n.__;
	var _n = wp.i18n._n;
	var sprintf = wp.i18n.sprintf;
	var D = 'faq-for-woocommerce';
	var urls = data.urls || {};
	var stats = data.stats || {};

	function editUrl( id ) {
		return urls.manager + '#/edit/' + id;
	}

	function Checklist() {
		var steps = data.steps || [];
		var done = steps.filter( function ( step ) {
			return step.done;
		} ).length;
		var hidden = useState( ui.store.get( 'dash_steps_hidden', false ) );
		if ( hidden[ 0 ] ) {
			return null;
		}
		var all = done === steps.length;
		return el(
			ui.Card,
			{
				className: 'ffw-dash-steps',
				title: all ? __( 'You are all set', D ) : __( 'Get the most out of Happy FAQs', D ),
				description: sprintf( __( '%1$d of %2$d done', D ), done, steps.length ),
				actions: all ? el( ui.Button, { small: true, variant: 'tertiary', onClick: function () {
					ui.store.set( 'dash_steps_hidden', true );
					hidden[ 1 ]( true );
				} }, __( 'Hide', D ) ) : null
			},
			el( 'div', { className: 'ffw-dash-progress', role: 'progressbar', 'aria-valuemin': 0, 'aria-valuemax': steps.length, 'aria-valuenow': done }, el( 'i', { style: { width: ( 100 * done / Math.max( 1, steps.length ) ) + '%' } } ) ),
			el(
				'ol',
				{ className: 'ffw-dash-steplist' },
				steps.map( function ( step, index ) {
					return el(
						'li',
						{ key: step.id, className: step.done ? 'is-done' : '' },
						el( 'span', { className: 'ffw-dash-step-mark', 'aria-hidden': 'true' }, step.done ? el( ui.Icon, { name: 'check', size: 14, stroke: 3 } ) : index + 1 ),
						el( 'div', { className: 'ffw-dash-step-text' }, el( 'strong', null, step.title ), el( 'span', null, step.text ) ),
						step.done ? el( ui.Badge, { tone: 'good' }, __( 'Done', D ) ) : el( ui.Button, { small: true, href: step.url }, step.cta )
					);
				} )
			)
		);
	}

	function FaqTable( props ) {
		if ( ! props.items.length ) {
			return el( ui.Empty, { icon: props.icon, title: props.emptyTitle, text: props.emptyText, action: props.emptyAction } );
		}
		return el(
			'div',
			{ className: 'ffw-ui-table-wrap' },
			el(
				'table',
				{ className: 'ffw-ui-table' },
				el( 'thead', null, el( 'tr', null,
					el( 'th', null, __( 'Question', D ) ),
					props.mode === 'top' ? el( 'th', { className: 'is-num' }, __( 'Views', D ) ) : el( 'th', null, __( 'Updated', D ) ),
					props.mode === 'top' && data.votesOn ? el( 'th', { className: 'is-num' }, __( 'Helpful', D ) ) : null
				) ),
				el( 'tbody', null, props.items.map( function ( item ) {
					return el(
						'tr',
						{ key: item.id },
						el( 'td', null, el( 'a', { className: 'ffw-dash-faq', href: editUrl( item.id ) }, item.title || __( '(no question)', D ) ) ),
						props.mode === 'top' ? el( 'td', { className: 'is-num' }, ui.number( item.views ) ) : el( 'td', { className: 'ffw-ui-muted' }, item.modified ),
						props.mode === 'top' && data.votesOn ? el( 'td', { className: 'is-num' }, null === item.helpful ? el( 'span', { className: 'ffw-ui-muted' }, '—' ) : item.helpful + '%' ) : null
					);
				} ) )
			)
		);
	}

	// Brand marks, drawn simply, for "works with your page builder".
	var BUILDER_LOGOS = {
		gutenberg: el( 'svg', { viewBox: '0 0 48 48', width: 40, height: 40, 'aria-hidden': 'true' },
			el( 'rect', { width: 48, height: 48, rx: 12, fill: '#1e1e1e' } ),
			el( 'path', { d: 'M31.5 17.6A10 10 0 1 0 34 26h-9.5', fill: 'none', stroke: '#fff', strokeWidth: 3.6, strokeLinecap: 'round' } )
		),
		elementor: el( 'svg', { viewBox: '0 0 48 48', width: 40, height: 40, 'aria-hidden': 'true' },
			el( 'circle', { cx: 24, cy: 24, r: 24, fill: '#92003b' } ),
			el( 'rect', { x: 15, y: 14, width: 4, height: 20, fill: '#fff' } ),
			el( 'rect', { x: 22, y: 14, width: 11, height: 4, fill: '#fff' } ),
			el( 'rect', { x: 22, y: 22, width: 11, height: 4, fill: '#fff' } ),
			el( 'rect', { x: 22, y: 30, width: 11, height: 4, fill: '#fff' } )
		),
		divi: el( 'svg', { viewBox: '0 0 48 48', width: 40, height: 40, 'aria-hidden': 'true' },
			el( 'circle', { cx: 24, cy: 24, r: 24, fill: '#8c3bd9' } ),
			el( 'path', { d: 'M16 13h8.5a11 11 0 0 1 0 22H16z M20.5 17.5v13h4a6.5 6.5 0 0 0 0-13z', fill: '#fff', fillRule: 'evenodd' } )
		)
	};

	function Builders() {
		var builders = [
			{ key: 'gutenberg', name: __( 'Gutenberg', D ), text: __( 'Blocks', D ) },
			{ key: 'elementor', name: __( 'Elementor', D ), text: __( 'Widgets', D ) },
			{ key: 'divi', name: __( 'Divi', D ), text: __( 'Divi 4 and 5 modules', D ) }
		];
		return el(
			ui.Card,
			{ className: 'ffw-dash-builders', title: __( 'Works with your page builder', D ), description: __( 'Happy Prebuilt FAQs and Happy Custom FAQs are ready in each one.', D ) },
			el(
				'ul',
				{ className: 'ffw-dash-builder-list' },
				builders.map( function ( builder ) {
					return el(
						'li',
						{ key: builder.key, className: 'is-' + builder.key },
						el( 'span', { className: 'ffw-dash-builder-logo' }, BUILDER_LOGOS[ builder.key ] ),
						el( 'strong', null, builder.name ),
						el( 'span', null, builder.text ),
						el( 'span', { className: 'ffw-dash-builder-ok' }, el( ui.Icon, { name: 'check', size: 12, stroke: 3 } ), __( 'Supported', D ) )
					);
				} )
			)
		);
	}

	function Compare() {
		var groups = data.table || [];
		var proOnly = 0;
		var total = 0;
		groups.forEach( function ( group ) {
			group.rows.forEach( function ( row ) {
				total++;
				if ( ! row.free ) {
					proOnly++;
				}
			} );
		} );
		var view = useState( 'pro' );

		// "Pro features" lists only what Pro adds; "Everything" shows all, Pro rows first.
		var shown = groups.map( function ( group ) {
			var pro = group.rows.filter( function ( row ) {
				return ! row.free;
			} );
			var free = group.rows.filter( function ( row ) {
				return row.free;
			} );
			return Object.assign( {}, group, { rows: 'pro' === view[ 0 ] ? pro : pro.concat( free ) } );
		} ).filter( function ( group ) {
			return group.rows.length;
		} );

		return el(
			ui.Card,
			{
				className: 'ffw-dash-compare',
				title: __( 'Free vs Pro', D ),
				description: __( 'One FAQ library for your site and your store. Pro adds store extras, analytics, groups and more.', D ),
				actions: el( ui.Button, { variant: 'brand', small: true, icon: 'crown', href: ui.config.proUrl, target: '_blank' }, __( 'Upgrade to Pro', D ) ),
				flush: true
			},
			el(
				'div',
				{ className: 'ffw-dash-compare-bar' },
				el(
					'div',
					{ className: 'ffw-dash-compare-sum' },
					el( 'span', { className: 'ffw-dash-compare-num' }, '+' + proOnly ),
					el( 'span', null, el( 'strong', null, sprintf( _n( '%d feature only in Pro', '%d features only in Pro', proOnly, D ), proOnly ) ), el( 'small', null, sprintf( __( 'Pro includes all %d features. Free includes %d.', D ), total, total - proOnly ) ) )
				),
				el( ui.Segment, {
					label: __( 'Show', D ),
					value: view[ 0 ],
					options: [ { value: 'pro', label: sprintf( __( 'Pro features (%d)', D ), proOnly ) }, { value: 'all', label: sprintf( __( 'Everything (%d)', D ), total ) } ],
					onChange: view[ 1 ]
				} )
			),
			el(
				'div',
				{ className: 'ffw-ui-table-wrap' },
				el(
					'table',
					{ className: 'ffw-ui-table ffw-dash-compare-table' },
					el( 'thead', null, el( 'tr', null, el( 'th', null, __( 'Feature', D ) ), el( 'th', { className: 'is-mark' }, __( 'Free', D ) ), el( 'th', { className: 'is-mark is-pro-col' }, el( 'span', { className: 'ffw-dash-pro-head' }, el( ui.Icon, { name: 'crown', size: 12 } ), __( 'Pro', D ) ) ) ) ),
					shown.map( function ( group ) {
						return el(
							'tbody',
							{ key: group.title },
							el( 'tr', { className: 'ffw-dash-compare-group' }, el( 'td', { colSpan: 3 }, el( 'strong', null, group.emoji + ' ' + group.title ), el( 'span', null, group.text ) ) ),
							group.rows.map( function ( row ) {
								return el(
									'tr',
									{ key: row.title, className: row.free ? '' : 'is-pro-only' },
									el( 'td', null, el( 'strong', null, row.title, row.free ? null : el( Fragment, null, ' ', el( ui.ProBadge ) ) ), el( 'span', null, row.text ) ),
									el( 'td', { className: 'is-mark' }, row.free
										? el( 'span', { className: 'ffw-dash-yes', title: __( 'Included', D ) }, el( ui.Icon, { name: 'check', size: 15, stroke: 3 } ) )
										: el( 'span', { className: 'ffw-dash-no', title: __( 'Not in Free', D ) }, el( ui.Icon, { name: 'lock', size: 14 } ) ) ),
									el( 'td', { className: 'is-mark is-pro-col' }, el( 'span', { className: 'ffw-dash-yes is-pro', title: __( 'Included', D ) }, el( ui.Icon, { name: 'check', size: 15, stroke: 3 } ) ) )
								);
							} )
						);
					} )
				)
			),
			el( 'div', { className: 'ffw-ui-card-foot' }, el( 'span', { className: 'ffw-ui-muted' }, __( '14-day money-back guarantee.', D ) ), el( ui.Button, { small: true, variant: 'brand', icon: 'crown', href: ui.config.proUrl, target: '_blank' }, sprintf( __( 'Unlock %d Pro features', D ), proOnly ) ) )
		);
	}

	function Dashboard() {
		var votes = stats.yes + stats.no;
		var helpful = votes ? Math.round( 100 * stats.yes / votes ) : null;

		var quick = [
			{ icon: 'grid', label: __( 'Patterns', D ), text: __( '21 ready-made FAQ sections', D ), url: urls.patterns },
			{ icon: 'code', label: __( 'Shortcodes', D ), text: __( 'Place FAQs anywhere', D ), url: urls.shortcodes },
			{ icon: 'sparkle', label: __( 'AI FAQs', D ), text: __( 'Write FAQs from a product or page', D ), url: urls.ai },
			{ icon: 'chart', label: __( 'Analytics', D ), text: data.pro ? __( 'What visitors open and search', D ) : __( 'See what visitors look for', D ), url: urls.analytics, pro: ! data.pro }
		];

		return el(
			Fragment,
			null,
			el(
				'div',
				{ className: 'ffw-dash-hero' },
				el(
					'div',
					null,
					el( 'p', { className: 'ffw-dash-eyebrow' }, data.pro ? __( 'Happy FAQs Pro', D ) : __( 'Happy FAQs', D ) ),
					el( 'h1', null, sprintf( __( 'Welcome back, %s', D ), data.user ) ),
					el( 'p', null, __( 'Answer questions once, and show them on your pages, products and forms.', D ) )
				),
				el(
					'div',
					{ className: 'ffw-ui-row' },
					el( ui.Button, { variant: 'brand', icon: 'plus', href: urls.manager + '#/new' }, __( 'Add FAQ', D ) ),
					el( ui.Button, { icon: 'page', href: urls.helpCenter }, __( 'Help Center page', D ) ),
					el( ui.Button, { variant: 'tertiary', icon: 'settings', href: urls.settings }, __( 'Settings', D ) )
				)
			),
			el(
				'div',
				{ className: 'ffw-ui-grid is-4 ffw-dash-stats' },
				el( ui.Stat, {
					icon: 'question',
					label: __( 'Published FAQs', D ),
					value: ui.number( stats.published ),
					note: [ sprintf( _n( '%s category', '%s categories', stats.categories, D ), ui.number( stats.categories ) ), stats.drafts ? sprintf( _n( '%s draft', '%s drafts', stats.drafts, D ), ui.number( stats.drafts ) ) : null ].filter( Boolean ).join( ' · ' )
				} ),
				el( ui.Stat, { icon: 'eye', label: __( 'Answer views', D ), value: ui.number( stats.views ), note: __( 'All time', D ) } ),
				el( ui.Stat, {
					icon: 'thumb',
					label: __( 'Found helpful', D ),
					value: null === helpful ? '—' : helpful + '%',
					note: data.votesOn ? sprintf( _n( '%s vote', '%s votes', votes, D ), ui.number( votes ) ) : el( 'a', { href: urls.settings + '#general' }, __( 'Turn on votes', D ) )
				} ),
				data.woo ? el( ui.Stat, {
					icon: 'store',
					label: __( 'Products with FAQs', D ),
					value: ui.number( stats.products ),
					note: stats.questions ? el( 'a', { href: urls.questions + '&post_status=pending' }, sprintf( _n( '%s question to answer', '%s questions to answer', stats.questions, D ), ui.number( stats.questions ) ) ) : __( 'No questions waiting', D )
				} ) : el( ui.Stat, {
					icon: 'chat',
					label: __( 'Questions to answer', D ),
					value: ui.number( stats.questions ),
					note: el( 'a', { href: urls.questions }, __( 'Customer questions', D ) )
				} )
			),
			el(
				'div',
				{ className: 'ffw-dash-cols' },
				el(
					'div',
					{ className: 'ffw-dash-main' },
					el( Checklist ),
					el(
						ui.Card,
						{
							title: __( 'Most viewed FAQs', D ),
							description: __( 'The answers visitors open most.', D ),
							actions: el( ui.Button, { small: true, variant: 'tertiary', href: urls.manager + '#/list?orderby=views' }, __( 'All FAQs', D ) ),
							flush: true
						},
						el( FaqTable, {
							mode: 'top',
							items: ( data.top || [] ).filter( function ( item ) {
								return item.views > 0;
							} ),
							icon: 'eye',
							emptyTitle: __( 'No views yet', D ),
							emptyText: __( 'Views are counted when visitors open an answer.', D )
						} )
					),
					el(
						ui.Card,
						{
							title: __( 'Recently updated', D ),
							actions: el( ui.Button, { small: true, variant: 'tertiary', href: urls.manager }, __( 'All FAQs', D ) ),
							flush: true
						},
						el( FaqTable, {
							mode: 'recent',
							items: data.recent || [],
							icon: 'question',
							emptyTitle: __( 'No FAQs yet', D ),
							emptyText: __( 'Start with the questions customers ask most.', D ),
							emptyAction: el( ui.Button, { variant: 'brand', icon: 'plus', href: urls.manager + '#/new' }, __( 'Add FAQ', D ) )
						} )
					),
					// In the main column, so it fills the space beside the sidebar.
					! data.pro ? el( Compare ) : null
				),
				el(
					'aside',
					{ className: 'ffw-dash-side' },
					el(
						ui.Card,
						{ title: __( 'Shortcuts', D ), flush: true },
						el(
							'ul',
							{ className: 'ffw-dash-links' },
							quick.map( function ( item ) {
								return el(
									'li',
									{ key: item.label },
									el(
										'a',
										{ href: item.url },
										el( 'span', { className: 'ffw-dash-link-icon' }, el( ui.Icon, { name: item.icon, size: 17 } ) ),
										el( 'span', { className: 'ffw-dash-link-text' }, el( 'strong', null, item.label, item.pro ? el( Fragment, null, ' ', el( ui.ProBadge ) ) : null ), el( 'span', null, item.text ) ),
										el( ui.Icon, { name: 'next', size: 15, className: 'ffw-dash-link-arrow' } )
									)
								);
							} )
						)
					),
					! data.pro ? el(
						ui.Upsell,
						{ title: __( 'Do more with Pro', D ), text: __( 'Everything in Free, plus:', D ) },
						el( 'ul', { className: 'ffw-dash-why' }, ( data.whyPro || [] ).slice( 0, 8 ).map( function ( item ) {
							return el( 'li', { key: item }, el( ui.Icon, { name: 'check', size: 14, stroke: 3 } ), item );
						} ) )
					) : null,
					el( Builders ),
					el(
						ui.Card,
						{ title: __( 'Help and resources', D ), flush: true },
						el(
							'ul',
							{ className: 'ffw-dash-links' },
							[
								{ icon: 'play', label: __( 'Video tour', D ), text: __( 'See it in a few minutes', D ), url: urls.videos },
								{ icon: 'book', label: __( 'Documentation', D ), text: __( 'Guides for every feature', D ), url: urls.docs },
								{ icon: 'help', label: __( 'Support', D ), text: __( 'We answer within a day', D ), url: urls.support },
								{ icon: 'star', label: __( 'Rate Happy FAQs', D ), text: __( 'A review helps a lot', D ), url: urls.review }
							].map( function ( item ) {
								return el(
									'li',
									{ key: item.label },
									el(
										'a',
										{ href: item.url, target: '_blank', rel: 'noopener' },
										el( 'span', { className: 'ffw-dash-link-icon' }, el( ui.Icon, { name: item.icon, size: 17 } ) ),
										el( 'span', { className: 'ffw-dash-link-text' }, el( 'strong', null, item.label ), el( 'span', null, item.text ) ),
										el( ui.Icon, { name: 'external', size: 14, className: 'ffw-dash-link-arrow' } )
									)
								);
							} )
						)
					)
				)
			),
			el( ui.ClassicLink, null )
		);
	}

	ui.mount( Dashboard );
} )( window.wp, window.ffwUI, window.ffwPage );
