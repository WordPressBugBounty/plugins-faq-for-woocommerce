/**
 * Happy FAQs → Help Center Page (React): build a Help Center page with a
 * live preview. Creating posts the same form as the previous screen.
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
	var useRef = wp.element.useRef;
	var __ = wp.i18n.__;
	var sprintf = wp.i18n.sprintf;
	var D = 'faq-for-woocommerce';

	function previewUrl( options ) {
		var params = new URLSearchParams();
		Object.keys( options ).forEach( function ( key ) {
			var value = options[ key ];
			if ( true === value ) {
				value = '1';
			} else if ( false === value ) {
				return;
			}
			params.append( 'ffw_hc[' + key + ']', value );
		} );
		return data.preview + '&' + params.toString();
	}

	function StyleThumb( props ) {
		return el(
			'span',
			{ className: 'ffw-hc-thumb is-' + props.style, 'aria-hidden': 'true' },
			el( 'i' ),
			el( 'i' ),
			el( 'i' )
		);
	}

	function HelpCenterPage() {
		var optsState = useState( Object.assign( {}, data.defaults ) );
		var busyState = useState( '' );
		var formRef = useRef( null );
		var o = optsState[ 0 ];

		function set( key, value ) {
			var next = Object.assign( {}, o );
			next[ key ] = value;
			optsState[ 1 ]( next );
		}

		function submit( publish ) {
			busyState[ 1 ]( publish ? 'publish' : 'draft' );
			var form = formRef.current;
			form.querySelector( '[name="ffw_hc[publish]"]' ).value = publish ? '1' : '';
			form.submit();
		}

		var hidden = Object.keys( o ).map( function ( key ) {
			var value = o[ key ];
			if ( false === value ) {
				return null;
			}
			return el( 'input', { key: key, type: 'hidden', name: 'ffw_hc[' + key + ']', value: true === value ? '1' : String( value ) } );
		} );

		var needsPro = ! data.proSearch || ! data.proForm;

		return el(
			Fragment,
			null,
			el( ui.PageHead, {
				title: __( 'Help Center page', D ),
				description: __( 'Search, the most asked questions, every FAQ by topic and a way to reach you, on one page. It stays in sync as you add answers.', D )
			} ),
			data.existing && data.existing.length ? el(
				ui.Card,
				{ title: __( 'Your Help Center pages', D ), flush: true, className: 'ffw-hc-existing' },
				el(
					'ul',
					{ className: 'ffw-hc-pages' },
					data.existing.map( function ( page ) {
						return el(
							'li',
							{ key: page.id },
							el( 'span', { className: 'ffw-hc-page-icon' }, el( ui.Icon, { name: 'page', size: 16 } ) ),
							el( 'span', { className: 'ffw-hc-page-text' }, el( 'strong', null, page.title || __( '(no title)', D ) ), el( 'small', null, sprintf( __( 'Updated %s', D ), page.modified ) ) ),
							'publish' === page.status ? el( ui.Badge, { tone: 'good' }, __( 'Published', D ) ) : el( ui.Badge, null, page.status ),
							el( 'span', { className: 'ffw-ui-row' },
								el( ui.Button, { small: true, variant: 'tertiary', icon: 'external', href: page.view, target: '_blank' }, __( 'View', D ) ),
								el( ui.Button, { small: true, variant: 'tertiary', icon: 'edit', href: page.edit }, __( 'Edit', D ) )
							)
						);
					} )
				)
			) : null,
			! data.faqCount ? el( ui.Notice, { tone: 'brand', action: el( ui.Button, { small: true, variant: 'primary', href: data.manager + '#/new' }, __( 'Add FAQ', D ) ) }, el( 'p', null, __( 'Add a few FAQs first: the page shows your FAQ library.', D ) ) ) : null,
			el(
				'div',
				{ className: 'ffw-hc-layout' },
				el(
					'div',
					{ className: 'ffw-hc-controls' },
					el(
						ui.Card,
						{ title: __( 'Build your page', D ) },
						el(
							'div',
							{ className: 'ffw-ui-fields' },
							el( ui.Field, { stacked: true, label: __( 'Page title', D ) }, el( ui.TextInput, { value: o.title, label: __( 'Page title', D ), onChange: function ( value ) {
								set( 'title', value );
							} } ) ),
							el( ui.Field, { stacked: true, label: __( 'Topics', D ) }, el( ui.ChoiceCards, {
								label: __( 'Topics', D ),
								value: o.layout,
								min: 160,
								options: [
									{ value: 'tabs', label: __( 'Filter tabs', D ), description: __( 'One list with a tab for each category.', D ) },
									{ value: 'sidebar', label: __( 'Category sidebar', D ), description: data.categories ? __( 'A topic list on the side, a section per category.', D ) : __( 'Add FAQ categories first.', D ), disabled: ! data.categories }
								],
								onChange: function ( value ) {
									set( 'layout', value );
								}
							} ) ),
							el( ui.Field, { stacked: true, label: __( 'Style', D ) }, el( ui.ChoiceCards, {
								label: __( 'Style', D ),
								value: o.style,
								min: 110,
								options: [ 'cards', 'minimal', 'soft' ].map( function ( style ) {
									return { value: style, label: { cards: __( 'Cards', D ), minimal: __( 'Minimal', D ), soft: __( 'Soft', D ) }[ style ], preview: el( StyleThumb, { style: style } ) };
								} ),
								onChange: function ( value ) {
									set( 'style', value );
								}
							} ) ),
							el( ui.Field, { stacked: true, label: __( 'Search', D ) }, el( ui.Select, {
								label: __( 'Search', D ),
								value: o.search,
								options: [
									{ value: 'library', label: data.proSearch ? __( 'Search every FAQ, at the top', D ) : __( 'Search every FAQ, at the top — Pro', D ), disabled: ! data.proSearch },
									{ value: 'inline', label: __( 'Search box on the lists', D ) },
									{ value: 'none', label: __( 'No search', D ) }
								],
								onChange: function ( value ) {
									set( 'search', value );
								}
							} ) ),
							el( ui.Field, { label: __( 'Most asked questions', D ), help: __( 'A section with the answers opened most.', D ) }, el( ui.Toggle, { checked: !! o.popular, onChange: function ( value ) {
								set( 'popular', value );
							} } ) ),
							el( ui.Field, { stacked: true, label: __( '"Still need help?"', D ) }, el( ui.Select, {
								label: __( 'Still need help', D ),
								value: o.contact,
								options: [
									{ value: 'form', label: data.proForm ? __( '"Ask a question" form', D ) : __( '"Ask a question" form — Pro', D ), disabled: ! data.proForm },
									{ value: 'link', label: __( 'Contact button', D ) },
									{ value: 'none', label: __( 'None', D ) }
								],
								onChange: function ( value ) {
									set( 'contact', value );
								}
							} ) ),
							'link' === o.contact ? el( ui.Field, { stacked: true, label: __( 'Contact button link', D ) }, el( ui.TextInput, { value: o.contact_url, label: __( 'Contact button link', D ), placeholder: 'https://… or mailto:…', onChange: function ( value ) {
								set( 'contact_url', value );
							} } ) ) : null
						),
						needsPro ? el( ui.Notice, { tone: 'brand', icon: 'crown' }, el( 'p', null, __( 'Pro adds a search across every FAQ at the top, and an "Ask a question" form that turns questions into FAQs.', D ) ) ) : null,
						el(
							'form',
							{ ref: formRef, method: 'post', action: data.action, className: 'ffw-hc-actions' },
							el( 'input', { type: 'hidden', name: 'action', value: data.createAction } ),
							el( 'input', { type: 'hidden', name: '_wpnonce', value: data.nonce } ),
							el( 'input', { type: 'hidden', name: 'ffw_hc[publish]', defaultValue: '' } ),
							hidden,
							el( ui.Button, { variant: 'brand', icon: 'page', busy: 'draft' === busyState[ 0 ], disabled: !! busyState[ 0 ], onClick: function () {
								submit( false );
							} }, __( 'Create draft page', D ) ),
							data.canPublish ? el( ui.Button, { busy: 'publish' === busyState[ 0 ], disabled: !! busyState[ 0 ], onClick: function () {
								submit( true );
							} }, __( 'Publish now', D ) ) : null
						)
					)
				),
				el( 'div', { className: 'ffw-hc-preview-col' }, el( ui.PreviewFrame, { src: previewUrl( o ), title: __( 'Live preview', D ) } ) )
			),
			el( ui.ClassicLink, null )
		);
	}

	ui.mount( HelpCenterPage );
} )( window.wp, window.ffwUI, window.ffwPage );
