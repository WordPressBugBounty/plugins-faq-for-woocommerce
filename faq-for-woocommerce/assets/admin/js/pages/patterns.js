/**
 * Happy FAQs → Patterns (React): ready-made FAQ sections with live previews.
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
	var useEffect = wp.element.useEffect;
	var useRef = wp.element.useRef;
	var __ = wp.i18n.__;
	var _n = wp.i18n._n;
	var sprintf = wp.i18n.sprintf;
	var D = 'faq-for-woocommerce';
	var WIDTH = 1200; // Previews render at this width, then scale down.

	// A scaled live preview that loads when it scrolls into view.
	function Thumb( props ) {
		var wrap = useRef( null );
		var visible = useState( false );
		var scale = useState( 0.25 );
		var loaded = useState( false );

		useEffect( function () {
			var node = wrap.current;
			if ( ! node ) {
				return undefined;
			}
			function measure() {
				scale[ 1 ]( node.clientWidth / WIDTH );
			}
			measure();
			var resize = window.ResizeObserver ? new window.ResizeObserver( measure ) : null;
			if ( resize ) {
				resize.observe( node );
			}
			var seen = window.IntersectionObserver ? new window.IntersectionObserver( function ( entries ) {
				if ( entries[ 0 ].isIntersecting ) {
					visible[ 1 ]( true );
					seen.disconnect();
				}
			}, { rootMargin: '300px' } ) : null;
			if ( seen ) {
				seen.observe( node );
			} else {
				visible[ 1 ]( true );
			}
			return function () {
				if ( resize ) {
					resize.disconnect();
				}
				if ( seen ) {
					seen.disconnect();
				}
			};
		}, [] );

		return el(
			'div',
			{ ref: wrap, className: ui.cx( 'ffw-pt2-thumb', loaded[ 0 ] && 'is-loaded' ) },
			el( 'span', { className: 'ffw-pt2-skeleton', 'aria-hidden': 'true' }, el( 'i' ), el( 'i' ), el( 'i' ), el( 'i' ) ),
			visible[ 0 ] ? el( 'iframe', {
				src: props.src,
				title: props.title,
				tabIndex: -1,
				'aria-hidden': 'true',
				scrolling: 'no',
				style: { width: WIDTH + 'px', height: Math.round( 260 / scale[ 0 ] ) + 'px', transform: 'scale(' + scale[ 0 ] + ')' },
				onLoad: function () {
					loaded[ 1 ]( true );
				}
			} ) : null
		);
	}

	function copyPattern( pattern ) {
		ui.copyText( pattern.content ).then( function () {
			ui.toast( __( 'Copied. Paste it into the block editor.', D ) );
		} ).catch( function () {
			ui.toast( __( 'Copy is blocked in this browser. Use "Create page" instead.', D ), 'error' );
		} );
	}

	function PatternsPage() {
		var groupState = useState( 'all' );
		var searchState = useState( '' );
		var openState = useState( null );
		var patterns = data.patterns || [];
		var groups = data.groups || {};
		var term = searchState[ 0 ].trim().toLowerCase();

		var shown = patterns.filter( function ( pattern ) {
			if ( 'all' !== groupState[ 0 ] && pattern.group !== groupState[ 0 ] ) {
				return false;
			}
			if ( ! term ) {
				return true;
			}
			var text = [ pattern.title, pattern.description, pattern.keywords.join( ' ' ), groups[ pattern.group ] || '' ].join( ' ' ).toLowerCase();
			return term.split( /\s+/ ).every( function ( word ) {
				return text.indexOf( word ) !== -1;
			} );
		} );

		var tabs = [ { value: 'all', label: sprintf( __( 'All (%d)', D ), patterns.length ) } ];
		Object.keys( groups ).forEach( function ( key ) {
			var count = patterns.filter( function ( pattern ) {
				return pattern.group === key;
			} ).length;
			if ( count ) {
				tabs.push( { value: key, label: groups[ key ] + ' (' + count + ')' } );
			}
		} );

		var open = openState[ 0 ];

		return el(
			Fragment,
			null,
			el( ui.PageHead, {
				title: __( 'Patterns', D ),
				description: sprintf( _n( '%d ready-made FAQ section with real starter questions. Create a page in one click, or insert it from the block editor.', '%d ready-made FAQ sections with real starter questions. Create a page in one click, or insert them from the block editor.', patterns.length, D ), patterns.length )
			} ),
			el(
				'div',
				{ className: 'ffw-pt2-toolbar' },
				el( ui.Segment, { label: __( 'Pattern groups', D ), value: groupState[ 0 ], options: tabs, onChange: groupState[ 1 ] } ),
				el(
					'label',
					{ className: 'ffw-pt2-search' },
					el( ui.Icon, { name: 'search', size: 16 } ),
					el( 'span', { className: 'ffw-ui-sr' }, __( 'Search patterns', D ) ),
					el( 'input', { type: 'search', className: 'ffw-ui-input', placeholder: __( 'Search, e.g. shipping', D ), value: searchState[ 0 ], onChange: function ( event ) {
						searchState[ 1 ]( event.target.value );
					} } )
				)
			),
			shown.length ? el(
				'div',
				{ className: 'ffw-pt2-grid' },
				shown.map( function ( pattern ) {
					return el(
						'article',
						{ key: pattern.slug, className: 'ffw-pt2-card' },
						el(
							'button',
							{ type: 'button', className: 'ffw-pt2-frame', title: __( 'Large preview', D ), onClick: function () {
								openState[ 1 ]( pattern );
							} },
							el( Thumb, { src: pattern.preview, title: pattern.title } ),
							el( 'span', { className: 'ffw-pt2-hint' }, el( ui.Icon, { name: 'eye', size: 14 } ), __( 'Preview', D ) )
						),
						el(
							'div',
							{ className: 'ffw-pt2-body' },
							el(
								'div',
								{ className: 'ffw-ui-row ffw-pt2-tags' },
								groups[ pattern.group ] ? el( ui.Badge, { tone: 'brand' }, groups[ pattern.group ] ) : null,
								pattern.style ? el( ui.Badge, null, pattern.style ) : null
							),
							el( 'h3', null, pattern.title ),
							el( 'p', null, pattern.description ),
							el(
								'div',
								{ className: 'ffw-ui-row ffw-pt2-actions' },
								el( ui.Button, { variant: 'primary', small: true, icon: 'page', href: pattern.create }, __( 'Create page', D ) ),
								el( ui.Button, { small: true, icon: 'copy', onClick: function () {
									copyPattern( pattern );
								} }, __( 'Copy', D ) )
							)
						)
					);
				} )
			) : el( ui.Card, null, el( ui.Empty, { icon: 'search', title: __( 'No patterns match', D ), action: el( ui.Button, { onClick: function () {
				searchState[ 1 ]( '' );
				groupState[ 1 ]( 'all' );
			} }, __( 'Show all patterns', D ) ) } ) ),
			el( 'p', { className: 'ffw-pt2-tip' }, el( ui.Icon, { name: 'info', size: 14 } ), __( 'In the block editor: open the inserter (+), choose Patterns, then Happy FAQs.', D ) ),
			open ? el(
				ui.Modal,
				{
					wide: true,
					title: open.title,
					onClose: function () {
						openState[ 1 ]( null );
					},
					actions: el(
						Fragment,
						null,
						el( ui.Button, { icon: 'external', href: open.preview, target: '_blank' }, __( 'Open in a new tab', D ) ),
						el( ui.Button, { icon: 'copy', onClick: function () {
							copyPattern( open );
						} }, __( 'Copy', D ) ),
						el( ui.Button, { variant: 'brand', icon: 'page', href: open.create }, __( 'Create page', D ) )
					)
				},
				el( 'p', { className: 'ffw-ui-muted' }, open.description ),
				el( 'iframe', { className: 'ffw-pt2-large', src: open.preview, title: open.title } )
			) : null,
			el( ui.ClassicLink, null )
		);
	}

	ui.mount( PatternsPage );
} )( window.wp, window.ffwUI, window.ffwPage );
