/**
 * Happy FAQs → Shortcodes (React): a [ffw_template] builder with a live
 * preview, and the shortcode reference.
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
	var sprintf = wp.i18n.sprintf;
	var D = 'faq-for-woocommerce';

	/* ------------------------------------------------------------------
	 * Pickers.
	 * ------------------------------------------------------------------ */

	function Picker( props ) {
		var searchState = useState( '' );
		var resultsState = useState( [] );
		var namesState = useState( {} );
		var busyState = useState( false );
		var value = props.value || [];

		// Titles of preselected items.
		useEffect( function () {
			var missing = value.filter( function ( id ) {
				return ! namesState[ 0 ][ id ];
			} );
			if ( ! missing.length ) {
				return;
			}
			ui.api( '/lookup', { query: { type: props.type, ids: missing.join( ',' ) } } ).then( function ( response ) {
				var next = Object.assign( {}, namesState[ 0 ] );
				response.items.forEach( function ( item ) {
					next[ item.id ] = item.title;
				} );
				namesState[ 1 ]( next );
			} );
		}, [ value.join( ',' ) ] );

		useEffect( function () {
			var term = searchState[ 0 ].trim();
			var timer = window.setTimeout( function () {
				busyState[ 1 ]( true );
				ui.api( '/lookup', { query: { type: props.type, search: term } } ).then( function ( response ) {
					busyState[ 1 ]( false );
					resultsState[ 1 ]( response.items );
					var next = Object.assign( {}, namesState[ 0 ] );
					response.items.forEach( function ( item ) {
						next[ item.id ] = item.title;
					} );
					namesState[ 1 ]( next );
				} ).catch( function () {
					busyState[ 1 ]( false );
				} );
			}, term ? 300 : 0 );
			return function () {
				window.clearTimeout( timer );
			};
		}, [ searchState[ 0 ], props.type ] );

		function toggle( id ) {
			if ( ! props.multiple ) {
				props.onChange( [ id ] );
				return;
			}
			props.onChange( value.indexOf( id ) === -1 ? value.concat( [ id ] ) : value.filter( function ( item ) {
				return item !== id;
			} ) );
		}

		return el(
			'div',
			{ className: 'ffw-sc-picker' },
			value.length ? el(
				'div',
				{ className: 'ffw-sc-chips' },
				value.map( function ( id ) {
					return el(
						'span',
						{ key: id, className: 'ffw-sc-chip' },
						namesState[ 0 ][ id ] || '#' + id,
						el( 'button', { type: 'button', 'aria-label': __( 'Remove', D ), onClick: function () {
							props.onChange( value.filter( function ( item ) {
								return item !== id;
							} ) );
						} }, el( ui.Icon, { name: 'close', size: 12 } ) )
					);
				} )
			) : null,
			el( ui.TextInput, { type: 'search', value: searchState[ 0 ], placeholder: props.placeholder, label: props.placeholder, onChange: searchState[ 1 ] } ),
			el(
				'ul',
				{ className: 'ffw-sc-results', role: 'listbox', 'aria-multiselectable': !! props.multiple },
				busyState[ 0 ] && ! resultsState[ 0 ].length ? el( 'li', { className: 'ffw-ui-muted' }, __( 'Searching…', D ) ) : null,
				! busyState[ 0 ] && ! resultsState[ 0 ].length ? el( 'li', { className: 'ffw-ui-muted' }, __( 'Nothing found.', D ) ) : null,
				resultsState[ 0 ].map( function ( item ) {
					var selected = value.indexOf( item.id ) !== -1;
					return el(
						'li',
						{ key: item.id },
						el(
							'button',
							{ type: 'button', role: 'option', 'aria-selected': selected, className: selected ? 'is-selected' : '', onClick: function () {
								toggle( item.id );
							} },
							el( 'span', { className: 'ffw-sc-check' }, selected ? el( ui.Icon, { name: 'check', size: 12, stroke: 3 } ) : null ),
							el( 'span', { className: 'ffw-sc-result-title' }, item.title || '#' + item.id ),
							el( 'span', { className: 'ffw-ui-muted' }, '#' + item.id )
						)
					);
				} )
			)
		);
	}

	/* ------------------------------------------------------------------
	 * Builder.
	 * ------------------------------------------------------------------ */

	function buildCode( state ) {
		var attrs = [];
		switch ( state.source ) {
			case 'category':
				if ( state.cats.length ) {
					attrs.push( 'faq_cat="' + state.cats.join( ',' ) + '"' );
				}
				break;
			case 'faqs':
				if ( state.faqs.length ) {
					attrs.push( 'faq_ids="' + state.faqs.join( ',' ) + '"' );
				}
				break;
			case 'popular':
			case 'recent':
				attrs.push( 'source="' + state.source + '"' );
				if ( state.cats.length ) {
					attrs.push( 'faq_cat="' + state.cats.join( ',' ) + '"' );
				}
				break;
			case 'product':
				if ( state.product.length ) {
					attrs.push( 'id="' + state.product[ 0 ] + '"' );
				}
				break;
			case 'current':
				attrs.push( 'dynamic_post="true"' );
				break;
			case 'group':
				if ( state.group.length ) {
					attrs.push( 'group_id="' + state.group[ 0 ] + '"' );
				}
				break;
		}
		if ( state.layout ) {
			attrs.push( 'template="' + state.layout + '"' );
		}
		if ( state.filter ) {
			attrs.push( 'filter="true"' );
		}
		if ( 'all' !== state.count && Number( state.number ) > 0 ) {
			attrs.push( state.count + '="' + Number( state.number ) + '"' );
		}
		if ( state.icon ) {
			attrs.push( 'icon="' + state.icon + '"' );
		}
		if ( state.iconPosition ) {
			attrs.push( 'icon_position="' + state.iconPosition + '"' );
		}
		return '[ffw_template' + ( attrs.length ? ' ' + attrs.join( ' ' ) : '' ) + ']';
	}

	var INITIAL = { source: 'all', cats: [], faqs: [], product: [], group: [], layout: '', filter: false, count: 'all', number: 5, icon: '', iconPosition: '' };

	function Builder() {
		var state = useState( INITIAL );
		var s = state[ 0 ];
		function set( key, value ) {
			var next = Object.assign( {}, s );
			next[ key ] = value;
			state[ 1 ]( next );
		}
		var code = buildCode( s );
		var previewCode = 'current' === s.source ? '[ffw_template]' : code;

		var sources = [
			{ value: 'all', label: __( 'All FAQs', D ), description: __( 'Every published FAQ', D ) },
			{ value: 'category', label: __( 'Categories', D ), description: __( 'FAQs from chosen categories', D ) },
			{ value: 'faqs', label: __( 'Hand-picked', D ), description: __( 'Only the FAQs you choose', D ) },
			{ value: 'popular', label: __( 'Most viewed', D ), description: __( 'Updates by itself', D ) },
			{ value: 'recent', label: __( 'Newest', D ), description: __( 'Latest FAQs first', D ) }
		];
		if ( data.woo ) {
			sources.push( { value: 'product', label: __( 'A product', D ), description: __( 'That product\'s FAQs, anywhere', D ) } );
			sources.push( { value: 'current', label: __( 'Current product', D ), description: __( 'For product templates', D ) } );
		}
		sources.push( { value: 'group', label: __( 'FAQ Group', D ), description: __( 'A saved collection', D ), locked: ! data.pro || ! data.groups } );

		var layouts = [ { value: '', label: __( 'Same as Settings', D ) } ].concat( ( data.layouts || [] ).map( function ( layout ) {
			return { value: String( layout.id ), label: layout.name + ( layout.locked ? ' — ' + __( 'Pro', D ) : '' ), disabled: layout.locked };
		} ) );

		var problems = [];
		if ( 'category' === s.source && ! s.cats.length ) {
			problems.push( __( 'Choose at least one category.', D ) );
		}
		if ( 'faqs' === s.source && ! s.faqs.length ) {
			problems.push( __( 'Choose at least one FAQ.', D ) );
		}
		if ( 'product' === s.source && ! s.product.length ) {
			problems.push( __( 'Choose a product.', D ) );
		}
		if ( 'group' === s.source && ! s.group.length ) {
			problems.push( __( 'Choose a FAQ Group.', D ) );
		}

		return el(
			'div',
			{ className: 'ffw-sc-builder' },
			el(
				'div',
				{ className: 'ffw-sc-controls' },
				el(
					ui.Card,
					{ title: __( '1. What to show', D ) },
					el( ui.ChoiceCards, { label: __( 'What to show', D ), value: s.source, options: sources, min: 140, onChange: function ( value ) {
						set( 'source', value );
					} } ),
					'category' === s.source || 'popular' === s.source || 'recent' === s.source ? el(
						'div',
						{ className: 'ffw-sc-sub' },
						el( 'span', { className: 'ffw-sc-sublabel' }, 'category' === s.source ? __( 'Categories', D ) : __( 'Only from these categories (optional)', D ) ),
						( data.categories || [] ).length ? el( ui.Checks, {
							label: __( 'Categories', D ),
							value: s.cats.map( String ),
							options: data.categories.map( function ( cat ) {
								return { value: String( cat.id ), label: cat.name + ' (' + cat.count + ')' };
							} ),
							onChange: function ( value ) {
								set( 'cats', value.map( Number ) );
							}
						} ) : el( 'p', { className: 'ffw-ui-muted' }, __( 'No FAQ categories yet.', D ), ' ', el( 'a', { href: data.manager + '#/categories' }, __( 'Add one', D ) ) )
					) : null,
					'faqs' === s.source ? el( 'div', { className: 'ffw-sc-sub' }, el( Picker, { type: 'faq', multiple: true, value: s.faqs, placeholder: __( 'Search FAQs', D ), onChange: function ( value ) {
						set( 'faqs', value );
					} } ) ) : null,
					'product' === s.source ? el( 'div', { className: 'ffw-sc-sub' }, el( Picker, { type: 'product', value: s.product, placeholder: __( 'Search products', D ), onChange: function ( value ) {
						set( 'product', value );
					} } ) ) : null,
					'group' === s.source && data.pro && data.groups ? el( 'div', { className: 'ffw-sc-sub' }, el( Picker, { type: 'group', value: s.group, placeholder: __( 'Search FAQ Groups', D ), onChange: function ( value ) {
						set( 'group', value );
					} } ) ) : null,
					'current' === s.source ? el( ui.Notice, { tone: 'info' }, el( 'p', null, __( 'Shows the FAQs of the product being viewed. Place it in a product template or a builder\'s single product layout. The preview shows all FAQs instead.', D ) ) ) : null
				),
				el(
					ui.Card,
					{ title: __( '2. How it looks', D ) },
					el(
						'div',
						{ className: 'ffw-ui-fields' },
						el( ui.Field, { stacked: true, label: __( 'Layout', D ), help: __( 'Overrides the layout from Settings for this list.', D ) }, el( ui.Select, { value: s.layout, options: layouts, label: __( 'Layout', D ), width: '260px', onChange: function ( value ) {
							set( 'layout', value );
						} } ) ),
						el( ui.Field, { stacked: true, label: __( 'Category filter', D ), help: __( 'Buttons above the list to show one category at a time.', D ) }, el( ui.Toggle, { checked: s.filter, onChange: function ( value ) {
							set( 'filter', value );
						} } ) ),
						el(
							ui.Field,
							{ stacked: true, label: __( 'How many', D ), help: 'per_page' === s.count ? __( 'The rest appear with a "Show more" button. Search and schema still include every FAQ.', D ) : null },
							el(
								'div',
								{ className: 'ffw-ui-row' },
								el( ui.Segment, {
									label: __( 'How many', D ),
									value: s.count,
									options: [ { value: 'all', label: __( 'All', D ) }, { value: 'limit', label: __( 'Only', D ) }, { value: 'per_page', label: __( 'Then "Show more"', D ) } ],
									onChange: function ( value ) {
										set( 'count', value );
									}
								} ),
								'all' !== s.count ? el( ui.TextInput, { type: 'number', className: 'is-short', min: 1, value: s.number, label: __( 'Number of FAQs', D ), onChange: function ( value ) {
									set( 'number', value );
								} } ) : null
							)
						),
						el(
							ui.Field,
							{ stacked: true, label: __( 'Toggle icon', D ), help: __( 'Overrides the icon from Settings.', D ) },
							el(
								'div',
								{ className: 'ffw-ui-row' },
								el( ui.Select, {
									label: __( 'Toggle icon', D ),
									value: s.icon,
									width: '220px',
									options: [ { value: '', label: __( 'Same as Settings', D ) } ].concat( data.icons || [] ),
									onChange: function ( value ) {
										set( 'icon', value );
									}
								} ),
								el( ui.Segment, {
									label: __( 'Icon position', D ),
									value: s.iconPosition,
									options: [ { value: '', label: __( 'Default', D ) }, { value: 'left', label: __( 'Left', D ) }, { value: 'right', label: __( 'Right', D ) } ],
									onChange: function ( value ) {
										set( 'iconPosition', value );
									}
								} )
							)
						)
					)
				)
			),
			el(
				'div',
				{ className: 'ffw-sc-output' },
				el(
					ui.Card,
					{
						className: 'ffw-sc-code-card',
						title: __( 'Your shortcode', D ),
						actions: ui.same( s, INITIAL ) ? null : el( ui.Button, { small: true, variant: 'tertiary', icon: 'refresh', onClick: function () {
							state[ 1 ]( INITIAL );
						} }, __( 'Reset', D ) )
					},
					el( ui.CopyField, { value: code, className: 'ffw-sc-code' } ),
					problems.length ? el( 'p', { className: 'ffw-sc-problem' }, el( ui.Icon, { name: 'info', size: 14 } ), problems[ 0 ] ) : el( 'p', { className: 'ffw-ui-field-help' }, __( 'Paste it into a Shortcode block, a page builder\'s shortcode widget, or a classic editor.', D ) )
				),
				el( ui.PreviewFrame, { base: data.preview, code: previewCode } )
			)
		);
	}

	function Reference() {
		return el(
			ui.Card,
			{ title: __( 'Shortcode reference', D ), description: __( 'Every way to use [ffw_template], with an example of each.', D ), flush: true },
			el(
				'ul',
				{ className: 'ffw-sc-ref' },
				( data.reference || [] ).map( function ( item ) {
					return el(
						'li',
						{ key: item.code },
						el( 'div', { className: 'ffw-sc-ref-text' }, el( 'strong', null, item.title ), el( 'span', null, item.description ) ),
						el( ui.CopyField, { value: item.code } )
					);
				} )
			)
		);
	}

	function ShortcodesPage() {
		return el(
			Fragment,
			null,
			el( ui.PageHead, {
				title: __( 'Shortcodes', D ),
				description: __( 'Build a FAQ list for any page, post, widget or page builder, and see it before you paste it.', D )
			} ),
			! data.faqCount ? el( ui.Notice, { tone: 'brand', action: el( ui.Button, { small: true, variant: 'primary', icon: 'plus', href: data.manager + '#/new' }, __( 'Add FAQ', D ) ) }, el( 'p', null, __( 'You have no published FAQs yet, so lists will be empty.', D ) ) ) : null,
			el( Builder ),
			el( 'div', { style: { height: '18px' } } ),
			el( Reference ),
			el( ui.ClassicLink, null )
		);
	}

	ui.mount( ShortcodesPage );
} )( window.wp, window.ffwUI, window.ffwPage );
