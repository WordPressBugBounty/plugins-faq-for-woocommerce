/**
 * Happy FAQs admin UI kit (wp.element, no build step).
 *
 * Shared by the React admin pages: window.ffwUI.
 *
 * @since 3.0.0
 */
( function ( wp, config ) {
	'use strict';

	if ( ! wp || ! wp.element ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var useRef = wp.element.useRef;
	var __ = wp.i18n.__;
	var D = 'faq-for-woocommerce';
	config = config || {};

	/* ------------------------------------------------------------------
	 * Helpers.
	 * ------------------------------------------------------------------ */

	function api( path, options ) {
		options = options || {};
		var url = ( options.root || config.root ) + path;
		if ( options.query ) {
			url = wp.url.addQueryArgs( url, options.query );
		}
		return wp.apiFetch( {
			url: url,
			method: options.method || 'GET',
			data: options.data
		} );
	}

	function errorText( error ) {
		return ( error && error.message ) || __( 'Something went wrong. Please try again.', D );
	}

	function cx() {
		return Array.prototype.filter.call( arguments, Boolean ).join( ' ' );
	}

	function number( value ) {
		return Number( value || 0 ).toLocaleString();
	}

	function copyText( text ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( text );
		}
		var area = document.createElement( 'textarea' );
		area.value = text;
		area.setAttribute( 'readonly', '' );
		area.style.position = 'fixed';
		area.style.opacity = '0';
		document.body.appendChild( area );
		area.select();
		try {
			document.execCommand( 'copy' );
		} catch ( e ) {}
		document.body.removeChild( area );
		return Promise.resolve();
	}

	var store = {
		get: function ( key, fallback ) {
			try {
				var value = window.localStorage.getItem( 'ffw_ui_' + key );
				return null === value ? fallback : JSON.parse( value );
			} catch ( e ) {
				return fallback;
			}
		},
		set: function ( key, value ) {
			try {
				window.localStorage.setItem( 'ffw_ui_' + key, JSON.stringify( value ) );
			} catch ( e ) {}
		}
	};

	function same( a, b ) {
		return JSON.stringify( a ) === JSON.stringify( b );
	}

	/* ------------------------------------------------------------------
	 * Icons (stroke, 24px grid).
	 * ------------------------------------------------------------------ */

	var ICONS = {
		plus: 'M12 5v14M5 12h14',
		search: 'M11 4a7 7 0 1 0 0 14 7 7 0 0 0 0-14zm10 17-5.2-5.2',
		back: 'M15 18l-6-6 6-6',
		next: 'M9 18l6-6-6-6',
		down: 'M6 9l6 6 6-6',
		external: 'M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5',
		copy: 'M9 9h10v10H9zM5 15V5h10',
		check: 'M5 12l5 5L20 7',
		close: 'M6 6l12 12M18 6 6 18',
		edit: 'M4 20h4L19 9l-4-4L4 16v4z',
		folder: 'M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z',
		list: 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01',
		sparkle: 'M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8zM19 16l.8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8z',
		trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3',
		settings: 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z',
		palette: 'M12 21a9 9 0 1 1 9-9c0 2.2-1.8 3-3.5 3H16a2 2 0 0 0-1.4 3.4c.4.4.4 1 0 1.4-.6.6-1.6 1.2-2.6 1.2zM7.5 11h.01M10 7.5h.01M14.5 7.5h.01M17 11h.01',
		code: 'M8 7l-5 5 5 5M16 7l5 5-5 5M14 4l-4 16',
		chat: 'M21 12a8 8 0 0 1-11.7 7.1L4 20l1-4.6A8 8 0 1 1 21 12z',
		layout: 'M4 4h16v16H4zM4 10h16M10 10v10',
		store: 'M4 9l1.5-5h13L20 9M4 9v11h16V9M4 9h16M9 20v-6h6v6',
		chart: 'M4 20V10M10 20V4M16 20v-8M22 20H2',
		eye: 'M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z',
		thumb: 'M7 11v9H4v-9zM7 11l4-8c1.7 0 2.5 1 2.5 2.5V9h5.2a2 2 0 0 1 2 2.3l-1.2 6.5a2 2 0 0 1-2 1.7H7',
		question: 'M9.1 9a3 3 0 1 1 4.2 2.8c-.8.4-1.3 1-1.3 1.9v.3M12 17.5h.01M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z',
		book: 'M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2zM4 21a2 2 0 0 0 2 2h13v-4',
		help: 'M4 14v-2a8 8 0 0 1 16 0v2M4 14a2 2 0 0 1 2-2h1v6H6a2 2 0 0 1-2-2zm16 0a2 2 0 0 0-2-2h-1v6h1a2 2 0 0 0 2-2z',
		crown: 'M3 8l4.5 4L12 5l4.5 7L21 8l-2 11H5z',
		lock: 'M6 11h12v10H6zM8 11V7a4 4 0 0 1 8 0v4',
		info: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 16v-4M12 8h.01',
		warning: 'M12 3l10 18H2zM12 10v4M12 18h.01',
		globe: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20',
		upload: 'M12 16V4M7 9l5-5 5 5M4 16v4h16v-4',
		download: 'M12 4v12M7 11l5 5 5-5M4 20h16',
		page: 'M6 3h9l4 4v14H6zM14 3v5h5',
		bolt: 'M13 2 4 14h7l-1 8 9-12h-7z',
		users: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8',
		grid: 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
		star: 'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.5 2.9 1-6.1L3.2 9.5l6.1-.9z',
		play: 'M7 4l13 8-13 8z',
		refresh: 'M20 11a8 8 0 0 0-14.9-3M4 4v4h4M4 13a8 8 0 0 0 14.9 3M20 20v-4h-4',
		image: 'M4 5h16v14H4zM4 15l4-4 4 4 3-3 5 5M15.5 9.5h.01',
		mail: 'M3 6h18v12H3zM3 7l9 6 9-6'
	};

	function Icon( props ) {
		var d = ICONS[ props.name ] || ICONS.info;
		var filled = 'crown' === props.name || 'play' === props.name || 'bolt' === props.name;
		return el(
			'svg',
			{ className: cx( 'ffw-ui-icon', props.className ), viewBox: '0 0 24 24', width: props.size || 16, height: props.size || 16, 'aria-hidden': 'true', focusable: 'false' },
			el( 'path', { d: d, fill: filled ? 'currentColor' : 'none', stroke: filled ? 'none' : 'currentColor', strokeWidth: props.stroke || 2, strokeLinecap: 'round', strokeLinejoin: 'round' } )
		);
	}

	/* ------------------------------------------------------------------
	 * Basics.
	 * ------------------------------------------------------------------ */

	function Button( props ) {
		var variant = props.variant || 'secondary';
		var className = cx( 'ffw-ui-btn', 'is-' + variant, props.small && 'is-small', props.iconOnly && 'is-icon', props.className );
		var inner = [
			props.busy ? el( 'span', { key: 'spin', className: 'ffw-ui-spin', 'aria-hidden': 'true' } ) : props.icon ? el( Icon, { key: 'icon', name: props.icon } ) : null,
			props.children
		];
		if ( props.href ) {
			return el( 'a', {
				className: className,
				href: props.href,
				target: props.target,
				rel: props.target ? 'noopener' : undefined,
				title: props.title,
				'aria-label': props.label,
				onClick: props.onClick
			}, inner );
		}
		return el(
			'button',
			{
				type: props.type || 'button',
				className: className,
				onClick: props.onClick,
				disabled: props.disabled || props.busy,
				title: props.title,
				'aria-label': props.label,
				'aria-pressed': props.pressed
			},
			inner
		);
	}

	function Loading( props ) {
		return el( 'div', { className: 'ffw-ui-loading', role: 'status' }, el( 'span', { className: 'ffw-ui-spin is-large', 'aria-hidden': 'true' } ), el( 'span', null, props.text || __( 'Loading…', D ) ) );
	}

	function PageHead( props ) {
		return el(
			'div',
			{ className: 'ffw-ui-head' },
			el( 'div', null, el( 'h1', null, props.title ), props.description ? el( 'p', null, props.description ) : null ),
			props.actions ? el( 'div', { className: 'ffw-ui-head-actions' }, props.actions ) : null
		);
	}

	function Card( props ) {
		var hasHead = props.title || props.description || props.actions;
		return el(
			'section',
			{ className: cx( 'ffw-ui-card', props.className ), id: props.id },
			hasHead ? el(
				'div',
				{ className: 'ffw-ui-card-head' },
				el( 'div', null, props.title ? el( 'h2', null, props.title, props.badge ? el( Fragment, null, ' ', props.badge ) : null ) : null, props.description ? el( 'p', null, props.description ) : null ),
				props.actions ? el( 'div', { className: 'ffw-ui-row' }, props.actions ) : null
			) : null,
			props.flush ? props.children : el( 'div', { className: 'ffw-ui-card-body' }, props.children ),
			props.footer ? el( 'div', { className: 'ffw-ui-card-foot' }, props.footer ) : null
		);
	}

	function Badge( props ) {
		return el( 'span', { className: cx( 'ffw-ui-badge', props.tone && 'is-' + props.tone ) }, props.icon ? el( Icon, { name: props.icon, size: 12 } ) : null, props.children );
	}

	function ProBadge() {
		return el( Badge, { tone: 'pro' }, __( 'Pro', D ) );
	}

	function Notice( props ) {
		var icons = { info: 'info', good: 'check', warn: 'warning', bad: 'warning', brand: 'sparkle' };
		return el(
			'div',
			{ className: cx( 'ffw-ui-notice', props.tone && 'is-' + props.tone ), role: 'bad' === props.tone ? 'alert' : undefined },
			el( Icon, { name: props.icon || icons[ props.tone ] || 'info', size: 18 } ),
			el( 'div', { className: 'ffw-ui-notice-body' }, props.children ),
			props.action || null
		);
	}

	function Stat( props ) {
		return el(
			'div',
			{ className: 'ffw-ui-stat' },
			el( 'div', { className: 'ffw-ui-stat-label' }, props.icon ? el( Icon, { name: props.icon, size: 14 } ) : null, props.label ),
			el( 'div', { className: 'ffw-ui-stat-value' }, props.value ),
			props.note ? el( 'div', { className: 'ffw-ui-stat-note' }, props.note ) : null
		);
	}

	function Empty( props ) {
		return el(
			'div',
			{ className: 'ffw-ui-empty' },
			el( 'span', { className: 'ffw-ui-empty-icon' }, el( Icon, { name: props.icon || 'question', size: 22 } ) ),
			el( 'h3', null, props.title ),
			props.text ? el( 'p', null, props.text ) : null,
			props.action || null
		);
	}

	function CopyField( props ) {
		var state = useState( false );
		function copy() {
			copyText( props.value ).then( function () {
				state[ 1 ]( true );
				window.setTimeout( function () {
					state[ 1 ]( false );
				}, 1600 );
			} );
		}
		return el(
			'div',
			{ className: cx( 'ffw-ui-copy', props.className ) },
			el( 'code', { title: props.value }, props.value ),
			el( 'button', { type: 'button', onClick: copy, className: state[ 0 ] ? 'is-done' : '' }, el( Icon, { name: state[ 0 ] ? 'check' : 'copy', size: 14 } ), state[ 0 ] ? __( 'Copied', D ) : __( 'Copy', D ) )
		);
	}

	function Upsell( props ) {
		return el(
			'div',
			{ className: cx( 'ffw-ui-upsell', props.className ) },
			el( Badge, { tone: 'brand', icon: 'crown' }, __( 'Happy FAQs Pro', D ) ),
			el( 'h2', { style: { marginTop: '12px' } }, props.title ),
			props.text ? el( 'p', null, props.text ) : null,
			props.children,
			el( Button, { variant: 'brand', href: props.href || config.proUrl, target: '_blank', icon: 'crown' }, props.button || __( 'Upgrade to Pro', D ) )
		);
	}

	function ClassicLink( props ) {
		return el(
			'p',
			{ className: 'ffw-ui-classic-link' },
			props.text || __( 'Prefer the previous screen?', D ),
			' ',
			el( 'a', { href: props.href || config.classicUrl }, props.label || __( 'Open it', D ) )
		);
	}

	/* ------------------------------------------------------------------
	 * Form controls.
	 * ------------------------------------------------------------------ */

	var uid = 0;
	function useId( prefix ) {
		var ref = useRef( null );
		if ( null === ref.current ) {
			uid += 1;
			ref.current = ( prefix || 'ffw-ui' ) + '-' + uid;
		}
		return ref.current;
	}

	function Field( props ) {
		return el(
			'div',
			{ className: cx( 'ffw-ui-field', props.stacked && 'is-stacked', props.dim && 'is-dim', props.className ), id: props.anchor },
			el(
				'div',
				null,
				el( props.htmlFor ? 'label' : 'div', { className: 'ffw-ui-field-label', htmlFor: props.htmlFor }, props.label, props.badge || null ),
				props.help && ! props.helpBelow ? el( 'p', { className: 'ffw-ui-field-help' }, props.help ) : null
			),
			el(
				'div',
				{ className: 'ffw-ui-field-control' },
				props.children,
				props.help && props.helpBelow ? el( 'p', { className: 'ffw-ui-field-help' }, props.help ) : null
			)
		);
	}

	function Toggle( props ) {
		var id = useId( 'ffw-toggle' );
		return el(
			'label',
			{ className: 'ffw-ui-toggle', htmlFor: props.id || id },
			el( 'input', {
				type: 'checkbox',
				id: props.id || id,
				checked: !! props.checked,
				disabled: props.disabled,
				onChange: function ( event ) {
					props.onChange( event.target.checked );
				}
			} ),
			el( 'span', { className: 'ffw-ui-toggle-track', 'aria-hidden': 'true' } ),
			props.label ? el( 'span', { className: 'ffw-ui-toggle-label' }, props.label ) : null
		);
	}

	function TextInput( props ) {
		return el( 'input', {
			type: props.type || 'text',
			id: props.id,
			className: cx( 'ffw-ui-input', props.size && 'is-' + props.size, props.className ),
			value: null === props.value || undefined === props.value ? '' : props.value,
			placeholder: props.placeholder,
			min: props.min,
			max: props.max,
			step: props.step,
			disabled: props.disabled,
			onKeyDown: props.onKeyDown,
			'aria-label': props.label,
			onChange: function ( event ) {
				props.onChange( event.target.value );
			}
		} );
	}

	function Textarea( props ) {
		return el( 'textarea', {
			id: props.id,
			className: cx( 'ffw-ui-textarea', props.code && 'is-code', props.className ),
			value: props.value || '',
			rows: props.rows || 4,
			placeholder: props.placeholder,
			spellCheck: props.code ? false : undefined,
			'aria-label': props.label,
			onChange: function ( event ) {
				props.onChange( event.target.value );
			}
		} );
	}

	function Select( props ) {
		var options = props.options || [];
		return el(
			'select',
			{
				id: props.id,
				className: cx( 'ffw-ui-select', props.className ),
				value: null === props.value || undefined === props.value ? '' : String( props.value ),
				disabled: props.disabled,
				'aria-label': props.label,
				style: props.width ? { width: props.width } : undefined,
				onChange: function ( event ) {
					props.onChange( event.target.value );
				}
			},
			options.map( function ( option ) {
				return el( 'option', { key: String( option.value ), value: String( option.value ), disabled: option.disabled }, option.label );
			} )
		);
	}

	function Segment( props ) {
		return el(
			'div',
			{ className: 'ffw-ui-segment', role: 'radiogroup', 'aria-label': props.label },
			( props.options || [] ).map( function ( option ) {
				var active = String( props.value ) === String( option.value );
				return el( 'button', {
					key: String( option.value ),
					type: 'button',
					role: 'radio',
					'aria-checked': active,
					className: active ? 'is-active' : '',
					onClick: function () {
						props.onChange( option.value );
					}
				}, option.label );
			} )
		);
	}

	function ChoiceCards( props ) {
		return el(
			'div',
			{ className: cx( 'ffw-ui-cards', props.className ), role: 'radiogroup', 'aria-label': props.label, style: props.min ? { gridTemplateColumns: 'repeat(auto-fill, minmax(' + props.min + 'px, 1fr))' } : undefined },
			( props.options || [] ).map( function ( option ) {
				var active = String( props.value ) === String( option.value );
				return el(
					'button',
					{
						key: String( option.value ),
						type: 'button',
						role: 'radio',
						'aria-checked': active,
						className: cx( 'ffw-ui-choice', active && 'is-selected' ),
						disabled: option.disabled,
						onClick: function () {
							if ( option.locked ) {
								window.open( config.proUrl, '_blank', 'noopener' );
								return;
							}
							props.onChange( option.value );
						}
					},
					el( 'span', { className: 'ffw-ui-choice-check' }, el( Icon, { name: 'check', size: 12, stroke: 3 } ) ),
					option.preview || null,
					el( 'span', { className: 'ffw-ui-choice-title' }, option.label, option.locked ? el( Fragment, null, ' ', el( ProBadge ) ) : null ),
					option.description ? el( 'span', { className: 'ffw-ui-choice-desc' }, option.description ) : null
				);
			} )
		);
	}

	function Checks( props ) {
		var value = Array.isArray( props.value ) ? props.value.map( String ) : [];
		return el(
			'div',
			{ className: 'ffw-ui-checks', role: 'group', 'aria-label': props.label },
			( props.options || [] ).map( function ( option ) {
				var checked = value.indexOf( String( option.value ) ) !== -1;
				return el(
					'label',
					{ key: String( option.value ), className: cx( 'ffw-ui-check', checked && 'is-checked' ) },
					el( 'input', {
						type: 'checkbox',
						checked: checked,
						onChange: function ( event ) {
							var next = value.filter( function ( item ) {
								return item !== String( option.value );
							} );
							if ( event.target.checked ) {
								next.push( String( option.value ) );
							}
							props.onChange( next );
						}
					} ),
					option.label
				);
			} )
		);
	}

	function ColorInput( props ) {
		var value = props.value || '';
		var hex = /^#[0-9a-f]{6}$/i.test( value ) ? value : /^#[0-9a-f]{3}$/i.test( value ) ? '#' + value.slice( 1 ).split( '' ).map( function ( c ) {
			return c + c;
		} ).join( '' ) : '#000000';
		return el(
			'div',
			{ className: 'ffw-ui-color' },
			el(
				'span',
				{ className: 'ffw-ui-color-swatch' },
				el( 'span', { style: { background: value || 'transparent' } } ),
				el( 'input', {
					type: 'color',
					value: hex,
					'aria-label': props.label,
					onChange: function ( event ) {
						props.onChange( event.target.value );
					}
				} )
			),
			el( 'input', {
				type: 'text',
				className: 'ffw-ui-input',
				value: value,
				placeholder: props.placeholder || __( 'Default', D ),
				'aria-label': props.label,
				onChange: function ( event ) {
					props.onChange( event.target.value.trim() );
				}
			} ),
			value ? el( Button, { variant: 'tertiary', small: true, onClick: function () {
				props.onChange( '' );
			} }, __( 'Clear', D ) ) : null
		);
	}

	/* ------------------------------------------------------------------
	 * Navigation.
	 * ------------------------------------------------------------------ */

	function Tabs( props ) {
		return el(
			'div',
			{ className: 'ffw-ui-tabs', role: 'tablist', 'aria-label': props.label },
			( props.tabs || [] ).map( function ( tab ) {
				var active = props.value === tab.id;
				return el(
					'button',
					{
						key: tab.id,
						type: 'button',
						role: 'tab',
						'aria-selected': active,
						className: cx( 'ffw-ui-tab', active && 'is-active' ),
						onClick: function () {
							props.onChange( tab.id );
						}
					},
					tab.icon ? el( Icon, { name: tab.icon, size: 15 } ) : null,
					tab.label,
					tab.badge || null,
					tab.dot ? el( 'span', { className: 'ffw-ui-dot', title: __( 'Unsaved changes', D ) } ) : null
				);
			} )
		);
	}

	function SideNav( props ) {
		return el(
			'ul',
			{ className: 'ffw-ui-sidenav', role: 'tablist', 'aria-orientation': 'vertical', 'aria-label': props.label },
			( props.items || [] ).map( function ( item ) {
				var active = props.value === item.id;
				return el(
					'li',
					{ key: item.id },
					el(
						'button',
						{
							type: 'button',
							role: 'tab',
							'aria-selected': active,
							className: active ? 'is-active' : '',
							onClick: function () {
								props.onChange( item.id );
							}
						},
						item.icon ? el( Icon, { name: item.icon, size: 16 } ) : null,
						item.label,
						item.badge || null,
						item.dot ? el( 'span', { className: 'ffw-ui-dot', title: __( 'Unsaved changes', D ) } ) : null
					)
				);
			} )
		);
	}

	/* ------------------------------------------------------------------
	 * Modal, confirm, toasts.
	 * ------------------------------------------------------------------ */

	function Modal( props ) {
		var ref = useRef( null );
		useEffect( function () {
			var previous = document.activeElement;
			var focusable = ref.current && ref.current.querySelector( 'input, select, textarea, .is-primary, .is-danger, .is-brand' );
			if ( focusable ) {
				focusable.focus();
			}
			function onKey( event ) {
				if ( 'Escape' === event.key ) {
					props.onClose();
				}
			}
			document.addEventListener( 'keydown', onKey );
			return function () {
				document.removeEventListener( 'keydown', onKey );
				if ( previous && previous.focus ) {
					previous.focus();
				}
			};
		}, [] );

		return el(
			'div',
			{
				className: 'ffw-ui-backdrop ffw-ui-portal',
				onMouseDown: function ( event ) {
					if ( event.target === event.currentTarget ) {
						props.onClose();
					}
				}
			},
			el(
				'div',
				{ className: cx( 'ffw-ui-modal', props.wide && 'is-wide' ), role: 'dialog', 'aria-modal': 'true', 'aria-label': props.title, ref: ref },
				el( 'h2', null, props.title ),
				el( 'div', { className: 'ffw-ui-modal-body' }, props.children ),
				props.actions ? el( 'div', { className: 'ffw-ui-modal-actions' }, props.actions ) : null
			)
		);
	}

	var confirmSetter = null;
	function confirmDialog( options ) {
		return new Promise( function ( resolve ) {
			if ( ! confirmSetter ) {
				resolve( window.confirm( options.message ) );
				return;
			}
			confirmSetter( {
				title: options.title,
				message: options.message,
				confirm: options.confirm || __( 'Confirm', D ),
				danger: options.danger,
				resolve: resolve
			} );
		} );
	}

	function ConfirmHost() {
		var state = useState( null );
		var dialog = state[ 0 ];
		useEffect( function () {
			confirmSetter = state[ 1 ];
			return function () {
				confirmSetter = null;
			};
		}, [] );
		if ( ! dialog ) {
			return null;
		}
		function close( answer ) {
			dialog.resolve( answer );
			state[ 1 ]( null );
		}
		return el(
			Modal,
			{
				title: dialog.title,
				onClose: function () {
					close( false );
				},
				actions: el(
					Fragment,
					null,
					el( Button, { onClick: function () {
						close( false );
					} }, __( 'Cancel', D ) ),
					el( Button, { variant: dialog.danger ? 'danger' : 'primary', onClick: function () {
						close( true );
					} }, dialog.confirm )
				)
			},
			el( 'p', null, dialog.message )
		);
	}

	var toastAdder = null;
	function toast( message, type ) {
		if ( toastAdder ) {
			toastAdder( { message: message, type: type || 'success' } );
		}
	}

	function Toasts() {
		var state = useState( [] );
		var items = state[ 0 ];
		var setItems = state[ 1 ];
		useEffect( function () {
			var id = 0;
			toastAdder = function ( item ) {
				id += 1;
				var key = id;
				setItems( function ( list ) {
					return list.concat( [ Object.assign( { key: key }, item ) ] ).slice( -3 );
				} );
				window.setTimeout( function () {
					setItems( function ( list ) {
						return list.filter( function ( entry ) {
							return entry.key !== key;
						} );
					} );
				}, 'error' === item.type ? 7000 : 3500 );
			};
			return function () {
				toastAdder = null;
			};
		}, [] );
		return el(
			'div',
			{ className: 'ffw-ui-toasts ffw-ui-portal', 'aria-live': 'polite' },
			items.map( function ( item ) {
				return el(
					'div',
					{ key: item.key, className: cx( 'ffw-ui-toast', 'error' === item.type && 'is-error' ), role: 'error' === item.type ? 'alert' : 'status' },
					el( Icon, { name: 'error' === item.type ? 'warning' : 'check', size: 16 } ),
					item.message
				);
			} )
		);
	}

	/* ------------------------------------------------------------------
	 * Save bar and unsaved-changes guard.
	 * ------------------------------------------------------------------ */

	function useLeaveGuard( dirty ) {
		useEffect( function () {
			if ( ! dirty ) {
				return undefined;
			}
			function onLeave( event ) {
				event.preventDefault();
				event.returnValue = '';
				return '';
			}
			window.addEventListener( 'beforeunload', onLeave );
			return function () {
				window.removeEventListener( 'beforeunload', onLeave );
			};
		}, [ dirty ] );
	}

	function SaveBar( props ) {
		useLeaveGuard( props.dirty );
		useEffect( function () {
			if ( ! props.onSave ) {
				return undefined;
			}
			function onKey( event ) {
				if ( ( event.metaKey || event.ctrlKey ) && 's' === event.key.toLowerCase() ) {
					event.preventDefault();
					if ( props.dirty && ! props.saving ) {
						props.onSave();
					}
				}
			}
			document.addEventListener( 'keydown', onKey );
			return function () {
				document.removeEventListener( 'keydown', onKey );
			};
		}, [ props.dirty, props.saving, props.onSave ] );

		if ( ! props.dirty ) {
			return null;
		}
		return el(
			'div',
			{ className: 'ffw-ui-savebar', role: 'region', 'aria-label': __( 'Unsaved changes', D ) },
			el( 'span', { className: 'ffw-ui-savebar-text' }, el( 'span', { className: 'ffw-ui-dot', 'aria-hidden': 'true' } ), props.text || __( 'You have unsaved changes', D ) ),
			el(
				'span',
				{ className: 'ffw-ui-savebar-actions' },
				props.onDiscard ? el( Button, { variant: 'tertiary', onClick: props.onDiscard, disabled: props.saving }, __( 'Discard', D ) ) : null,
				el( Button, { variant: 'brand', onClick: props.onSave, busy: props.saving }, props.saveLabel || __( 'Save changes', D ) )
			)
		);
	}

	/* ------------------------------------------------------------------
	 * Server-rendered fields (from other plugins) inside a page.
	 * ------------------------------------------------------------------ */

	// Renders HTML once and runs its inline scripts, like the page would.
	function LegacyHTML( props ) {
		var ref = useRef( null );
		useEffect( function () {
			var node = ref.current;
			if ( ! node ) {
				return;
			}
			node.innerHTML = props.html || '';
			Array.prototype.forEach.call( node.querySelectorAll( 'script' ), function ( old ) {
				var script = document.createElement( 'script' );
				Array.prototype.forEach.call( old.attributes, function ( attr ) {
					script.setAttribute( attr.name, attr.value );
				} );
				script.text = old.textContent;
				old.parentNode.replaceChild( script, old );
			} );
			if ( props.onMount ) {
				props.onMount( node );
			}
			function changed() {
				if ( props.onChange ) {
					props.onChange( node );
				}
			}
			node.addEventListener( 'input', changed );
			node.addEventListener( 'change', changed );
			// jQuery plugins (select2, color pickers) trigger jQuery events only.
			if ( window.jQuery ) {
				window.jQuery( node ).on( 'change.ffwui input.ffwui', changed );
			}
			return function () {
				node.removeEventListener( 'input', changed );
				node.removeEventListener( 'change', changed );
				if ( window.jQuery ) {
					window.jQuery( node ).off( '.ffwui' );
				}
			};
		}, [ props.html ] );
		return el( 'div', { ref: ref, className: cx( 'ffw-ui-legacy', props.className ) } );
	}

	// Form values of the inputs inside a node, like a form submit would send.
	function serializeFields( node ) {
		var pairs = [];
		var names = {};
		Array.prototype.forEach.call( node.querySelectorAll( 'input[name], select[name], textarea[name]' ), function ( field ) {
			names[ field.name ] = true;
			if ( field.disabled ) {
				return;
			}
			var type = ( field.type || '' ).toLowerCase();
			if ( 'button' === type || 'submit' === type || 'file' === type || 'reset' === type ) {
				return;
			}
			if ( ( 'checkbox' === type || 'radio' === type ) && ! field.checked ) {
				return;
			}
			if ( 'select-multiple' === type ) {
				Array.prototype.forEach.call( field.options, function ( option ) {
					if ( option.selected ) {
						pairs.push( [ field.name, option.value ] );
					}
				} );
				return;
			}
			pairs.push( [ field.name, field.value ] );
		} );
		return { pairs: pairs, names: Object.keys( names ) };
	}

	/* ------------------------------------------------------------------
	 * Live preview of a shortcode (Shortcodes, Templates).
	 * ------------------------------------------------------------------ */

	function PreviewFrame( props ) {
		var deviceState = useState( props.device || 'desktop' );
		var srcState = useState( '' );
		var loadingState = useState( true );

		var url = props.src || props.base + '&code=' + encodeURIComponent( props.code );

		useEffect( function () {
			var timer = window.setTimeout( function () {
				loadingState[ 1 ]( true );
				srcState[ 1 ]( url );
			}, 450 );
			return function () {
				window.clearTimeout( timer );
			};
		}, [ url ] );

		return el(
			'div',
			{ className: 'ffw-sc-preview' },
			el(
				'div',
				{ className: 'ffw-sc-preview-bar' },
				el( 'span', { className: 'ffw-sc-preview-title' }, el( Icon, { name: 'eye', size: 15 } ), props.title || __( 'Live preview', D ), loadingState[ 0 ] ? el( 'span', { className: 'ffw-ui-spin', 'aria-label': __( 'Loading', D ) } ) : null ),
				el(
					'span',
					{ className: 'ffw-ui-row' },
					el( Segment, {
						label: __( 'Preview width', D ),
						value: deviceState[ 0 ],
						options: [ { value: 'desktop', label: __( 'Desktop', D ) }, { value: 'mobile', label: __( 'Mobile', D ) } ],
						onChange: deviceState[ 1 ]
					} ),
					srcState[ 0 ] ? el( Button, { small: true, variant: 'tertiary', iconOnly: true, icon: 'external', href: srcState[ 0 ], target: '_blank', label: __( 'Open preview in a new tab', D ), title: __( 'Open preview in a new tab', D ) } ) : null
				)
			),
			el(
				'div',
				{ className: cx( 'ffw-sc-preview-stage', 'is-' + deviceState[ 0 ] ) },
				srcState[ 0 ] ? el( 'iframe', {
					src: srcState[ 0 ],
					title: __( 'Preview', D ),
					onLoad: function () {
						loadingState[ 1 ]( false );
					}
				} ) : null
			)
		);
	}

	/* ------------------------------------------------------------------
	 * Mount.
	 * ------------------------------------------------------------------ */

	function mount( Page ) {
		function start() {
			var root = document.getElementById( 'ffw-app' );
			if ( ! root ) {
				return;
			}
			var app = el( Fragment, null, el( Page ), el( Toasts ), el( ConfirmHost ) );
			if ( wp.element.createRoot ) {
				wp.element.createRoot( root ).render( app );
			} else {
				wp.element.render( app, root );
			}
		}
		if ( 'loading' === document.readyState ) {
			document.addEventListener( 'DOMContentLoaded', start );
		} else {
			start();
		}
	}

	window.ffwUI = {
		config: config,
		el: el,
		Fragment: Fragment,
		api: api,
		errorText: errorText,
		cx: cx,
		number: number,
		copyText: copyText,
		store: store,
		same: same,
		useId: useId,
		useLeaveGuard: useLeaveGuard,
		Icon: Icon,
		Button: Button,
		Loading: Loading,
		PageHead: PageHead,
		Card: Card,
		Badge: Badge,
		ProBadge: ProBadge,
		Notice: Notice,
		Stat: Stat,
		Empty: Empty,
		CopyField: CopyField,
		Upsell: Upsell,
		ClassicLink: ClassicLink,
		Field: Field,
		Toggle: Toggle,
		TextInput: TextInput,
		Textarea: Textarea,
		Select: Select,
		Segment: Segment,
		ChoiceCards: ChoiceCards,
		Checks: Checks,
		ColorInput: ColorInput,
		Tabs: Tabs,
		SideNav: SideNav,
		Modal: Modal,
		confirm: confirmDialog,
		toast: toast,
		SaveBar: SaveBar,
		LegacyHTML: LegacyHTML,
		PreviewFrame: PreviewFrame,
		serializeFields: serializeFields,
		mount: mount
	};
} )( window.wp, window.ffwUIConfig );
