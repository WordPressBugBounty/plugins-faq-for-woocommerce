/**
 * Happy FAQs → Templates (React): pick the site-wide FAQ layout, with a
 * live preview of your own FAQs in each layout.
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
	var sprintf = wp.i18n.sprintf;
	var D = 'faq-for-woocommerce';

	function TemplatesPage() {
		var activeState = useState( Number( data.layout ) || 1 );
		var viewState = useState( Number( data.layout ) || 1 );
		var busyState = useState( 0 );
		var zoomState = useState( null );
		var layouts = data.layouts || [];

		var viewing = null;
		layouts.forEach( function ( layout ) {
			if ( layout.id === viewState[ 0 ] ) {
				viewing = layout;
			}
		} );
		viewing = viewing || layouts[ 0 ];

		function activate( layout ) {
			busyState[ 1 ]( layout.id );
			ui.api( '/layout', { method: 'POST', data: { layout: layout.id } } )
				.then( function () {
					busyState[ 1 ]( 0 );
					activeState[ 1 ]( layout.id );
					ui.toast( sprintf( __( '%s is now your FAQ layout.', D ), layout.name ) );
				} )
				.catch( function ( error ) {
					busyState[ 1 ]( 0 );
					ui.toast( ui.errorText( error ), 'error' );
				} );
		}

		function preview( layout ) {
			viewState[ 1 ]( layout.id );
			window.setTimeout( function () {
				var node = document.querySelector( '.ffw-tpl-live' );
				if ( node ) {
					node.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				}
			}, 30 );
		}

		return el(
			Fragment,
			null,
			el( ui.PageHead, {
				title: __( 'Templates', D ),
				description: __( 'The layout every FAQ list uses, unless a block, widget or shortcode picks its own.', D ),
				actions: el( ui.Button, { icon: 'palette', href: data.settings }, __( 'Colors and icons', D ) )
			} ),
			el(
				'div',
				{ className: 'ffw-tpl-grid', role: 'list' },
				layouts.map( function ( layout ) {
					var active = layout.id === activeState[ 0 ];
					return el(
						'div',
						{ key: layout.id, role: 'listitem', className: ui.cx( 'ffw-tpl-card', active && 'is-active' ) },
						el(
							'button',
							{ type: 'button', className: 'ffw-tpl-shot', title: __( 'Enlarge', D ), onClick: function () {
								zoomState[ 1 ]( layout );
							} },
							layout.image ? el( 'img', { src: layout.image, alt: sprintf( __( '%s layout', D ), layout.name ), loading: 'lazy' } ) : null,
							active ? el( ui.Badge, { tone: 'brand', icon: 'check' }, __( 'In use', D ) ) : layout.locked ? el( ui.ProBadge ) : null
						),
						el(
							'div',
							{ className: 'ffw-tpl-body' },
							el( 'strong', null, layout.name ),
							el(
								'span',
								{ className: 'ffw-ui-row' },
								layout.locked ? null : el( ui.Button, { small: true, variant: 'tertiary', icon: 'eye', onClick: function () {
									preview( layout );
								} }, __( 'Preview', D ) ),
								active ? null : layout.locked
									? el( ui.Button, { small: true, variant: 'brand', icon: 'crown', href: ui.config.proUrl, target: '_blank' }, __( 'Pro', D ) )
									: el( ui.Button, { small: true, variant: 'primary', busy: busyState[ 0 ] === layout.id, onClick: function () {
										activate( layout );
									} }, __( 'Use', D ) )
							)
						)
					);
				} )
			),
			viewing && ! viewing.locked ? el(
				'div',
				{ className: 'ffw-tpl-live' },
				el( ui.PreviewFrame, {
					base: data.preview,
					code: '[ffw_template template="' + viewing.id + '" limit="4"]',
					title: sprintf( __( 'Live preview: %s', D ), viewing.name )
				} ),
				el(
					ui.Card,
					{ className: 'ffw-tpl-live-side', title: viewing.name },
					el( 'p', null, data.faqCount ? __( 'Your first four FAQs, in this layout, with your theme and Style settings.', D ) : __( 'Add FAQs to see them here.', D ) ),
					viewing.id === activeState[ 0 ]
						? el( ui.Badge, { tone: 'good', icon: 'check' }, __( 'In use on your site', D ) )
						: el( ui.Button, { variant: 'brand', busy: busyState[ 0 ] === viewing.id, onClick: function () {
							activate( viewing );
						} }, sprintf( __( 'Use %s', D ), viewing.name ) ),
					el( 'p', { className: 'ffw-ui-field-help', style: { marginTop: '14px' } }, sprintf( __( 'One list only? Add template="%d" to its shortcode.', D ), viewing.id ) )
				)
			) : null,
			zoomState[ 0 ] ? el(
				ui.Modal,
				{
					wide: true,
					title: zoomState[ 0 ].name,
					onClose: function () {
						zoomState[ 1 ]( null );
					},
					actions: el( ui.Button, { onClick: function () {
						zoomState[ 1 ]( null );
					} }, __( 'Close', D ) )
				},
				el( 'img', { src: zoomState[ 0 ].image, alt: '', style: { display: 'block', width: '100%', borderRadius: '10px', border: '1px solid #e6e6ea' } } )
			) : null,
			el( ui.ClassicLink, null )
		);
	}

	ui.mount( TemplatesPage );
} )( window.wp, window.ffwUI, window.ffwPage );
