/**
 * Happy FAQs → Settings (React).
 *
 * Saves only the keys you change. Switches store "on" or are removed, and
 * selects keep their stored values, the same as the previous screen.
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
	var useMemo = wp.element.useMemo;
	var __ = wp.i18n.__;
	var sprintf = wp.i18n.sprintf;
	var D = 'faq-for-woocommerce';

	var FIELDS = data.fields || {};
	var LAYOUT = data.layout || [];

	// Not a set of fields: its own panel, last in the list.
	var TRANSFER = { id: 'import-export', label: __( 'Import / Export', D ), icon: 'download', groups: [] };
	var SECTIONS = data.transfer ? LAYOUT.concat( [ TRANSFER ] ) : LAYOUT;

	// Old tab anchors still open the right section.
	var OLD_TABS = { 'ffw-general': 'general', 'ffw-style': 'style', 'ffw-schema': 'seo', 'ffw-comment': 'comments', 'ffw-ai-faqs': 'ai' };

	function plain( values ) {
		return JSON.parse( JSON.stringify( values || {} ) );
	}

	function isOn( value ) {
		if ( true === value ) {
			return true;
		}
		return [ 'on', '1', 'yes', 'true' ].indexOf( String( value || '' ).toLowerCase() ) !== -1;
	}

	// Value shown for a key, with the field's default.
	function read( values, field, key ) {
		key = key || field.key;
		var value = values[ key ];
		if ( undefined === value || null === value ) {
			return undefined === field.default ? '' : field.default;
		}
		if ( field.array ) {
			return Array.isArray( value ) ? ( value[ 0 ] || field.default ) : value;
		}
		return value;
	}

	function fieldKeys( field ) {
		if ( field.keys ) {
			return field.keys;
		}
		return field.key ? [ field.key ] : [];
	}

	function dimmed( values, field ) {
		if ( ! field.dimUnless ) {
			return false;
		}
		var key = field.dimUnless[ 0 ];
		var want = field.dimUnless[ 1 ];
		var value = values[ key ];
		if ( 'on' === want ) {
			return ! isOn( value );
		}
		var owner = null;
		Object.keys( FIELDS ).some( function ( id ) {
			if ( FIELDS[ id ].key === key ) {
				owner = FIELDS[ id ];
				return true;
			}
			return false;
		} );
		var shown = owner ? read( values, owner ) : value;
		return String( shown ) !== String( want );
	}

	// Words to search a field by: its label, help, group, section and kind.
	var CONTEXT = {};
	LAYOUT.forEach( function ( section ) {
		section.groups.forEach( function ( group ) {
			group.fields.forEach( function ( id ) {
				CONTEXT[ id ] = section.label + ' ' + group.title;
			} );
		} );
	} );
	var KIND = {
		color: __( 'color colour', D ),
		range: __( 'font size', D ),
		icons: __( 'icon', D ),
		icon_style: __( 'icon color size', D ),
		richtext: __( 'text content html', D ),
		code: __( 'css code', D ),
		secret: __( 'api key', D )
	};

	function matches( field, term ) {
		if ( ! term ) {
			return true;
		}
		var text = [ field.label, field.help, CONTEXT[ field.id ], KIND[ field.type ], field.key ].join( ' ' ).toLowerCase();
		return term.toLowerCase().split( /\s+/ ).every( function ( word ) {
			return text.indexOf( word ) !== -1;
		} );
	}

	/* ------------------------------------------------------------------
	 * Rich text (the WordPress editor).
	 * ------------------------------------------------------------------ */

	function RichText( props ) {
		var id = props.id;
		var changeRef = useRef( props.onChange );
		changeRef.current = props.onChange;
		var lastRef = useRef( props.value || '' );

		useEffect( function () {
			var textarea = document.getElementById( id );
			if ( ! textarea ) {
				return undefined;
			}
			function emit( content ) {
				lastRef.current = content;
				changeRef.current( content );
			}
			function onText() {
				emit( textarea.value );
			}
			textarea.addEventListener( 'input', onText );

			if ( wp.editor && wp.editor.initialize ) {
				wp.editor.initialize( id, {
					tinymce: {
						wpautop: true,
						toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink',
						height: 160
					},
					quicktags: true,
					mediaButtons: true
				} );
				window.setTimeout( function () {
					var editor = window.tinymce && window.tinymce.get( id );
					if ( editor ) {
						editor.on( 'change keyup undo redo input NodeChange', function () {
							var content = editor.getContent();
							if ( content !== lastRef.current ) {
								emit( content );
							}
						} );
					}
				}, 0 );
			}

			return function () {
				textarea.removeEventListener( 'input', onText );
				if ( wp.editor && wp.editor.remove ) {
					wp.editor.remove( id );
				}
			};
		}, [] );

		// Discard: put the saved content back.
		useEffect( function () {
			var value = props.value || '';
			if ( value === lastRef.current ) {
				return;
			}
			lastRef.current = value;
			var editor = window.tinymce && window.tinymce.get( id );
			if ( editor && ! editor.isHidden() ) {
				editor.setContent( value );
			}
			var textarea = document.getElementById( id );
			if ( textarea ) {
				textarea.value = value;
			}
		}, [ props.value ] );

		return el( 'div', { className: 'ffw-set-richtext' }, el( 'textarea', { id: id, className: 'wp-editor-area', defaultValue: props.value || '', rows: 6 } ) );
	}

	/* ------------------------------------------------------------------
	 * Controls by type.
	 * ------------------------------------------------------------------ */

	function SecretInput( props ) {
		var shown = useState( false );
		return el(
			'div',
			{ className: 'ffw-ui-input-group ffw-set-secret' },
			el( 'input', {
				type: shown[ 0 ] ? 'text' : 'password',
				className: 'ffw-ui-input is-medium',
				autoComplete: 'off',
				spellCheck: false,
				value: props.value || '',
				placeholder: props.placeholder,
				'aria-label': props.label,
				onChange: function ( event ) {
					props.onChange( event.target.value.trim() );
				}
			} ),
			el( ui.Button, { small: true, variant: 'tertiary', onClick: function () {
				shown[ 1 ]( ! shown[ 0 ] );
			} }, shown[ 0 ] ? __( 'Hide', D ) : __( 'Show', D ) )
		);
	}

	function RangeInput( props ) {
		var value = '' === props.value || undefined === props.value || null === props.value ? '' : String( props.value );
		return el(
			'div',
			{ className: 'ffw-set-range' },
			el( 'input', {
				type: 'range',
				min: props.min,
				max: props.max,
				step: 1,
				value: '' === value ? 16 : value,
				className: '' === value ? 'is-default' : '',
				'aria-label': props.label,
				onChange: function ( event ) {
					props.onChange( event.target.value );
				}
			} ),
			el( 'input', {
				type: 'number',
				className: 'ffw-ui-input is-short',
				min: props.min,
				max: props.max,
				value: value,
				placeholder: __( 'Default', D ),
				'aria-label': props.label,
				onChange: function ( event ) {
					props.onChange( event.target.value );
				}
			} ),
			el( 'span', { className: 'ffw-ui-suffix' }, 'px' ),
			'' !== value ? el( ui.Button, { small: true, variant: 'tertiary', onClick: function () {
				props.onChange( '' );
			} }, __( 'Default', D ) ) : null
		);
	}

	function IconPicker( props ) {
		return el(
			'div',
			{ className: 'ffw-set-icons', role: 'radiogroup', 'aria-label': props.label },
			( data.icons || [] ).map( function ( icon ) {
				var active = props.value === icon.value;
				return el(
					'button',
					{
						key: icon.value,
						type: 'button',
						role: 'radio',
						'aria-checked': active,
						className: ui.cx( 'ffw-set-icon', active && 'is-selected' ),
						onClick: function () {
							props.onChange( icon.value );
						}
					},
					el(
						'span',
						{ className: 'ffw-set-icon-preview', 'aria-hidden': 'true' },
						icon.svg ? el( Fragment, null,
							el( 'span', { className: 'ffw-set-icon-closed', dangerouslySetInnerHTML: { __html: icon.svg } } ),
							el( 'span', { className: 'ffw-accordion-active ffw-set-icon-open', dangerouslySetInnerHTML: { __html: icon.svg } } )
						) : el( 'span', { className: 'ffw-set-icon-text' }, 'default' === icon.value ? 'Aa' : '—' )
					),
					el( 'span', { className: 'ffw-set-icon-label' }, icon.label )
				);
			} )
		);
	}

	function PagePicker( props ) {
		var options = [ { value: '0', label: __( '— No page —', D ) } ].concat( ( data.pages || [] ).map( function ( page ) {
			return { value: String( page.id ), label: page.title + ( page.state ? ' (' + page.state + ')' : '' ) };
		} ) );
		var id = String( props.value || '0' );
		return el(
			'div',
			{ className: 'ffw-ui-row' },
			el( ui.Select, { value: id, options: options, onChange: props.onChange, label: props.label, width: '320px' } ),
			'0' !== id ? el( ui.Button, { small: true, variant: 'tertiary', icon: 'external', href: data.viewUrl + id, target: '_blank' }, __( 'View', D ) ) : null,
			'0' !== id ? el( ui.Button, { small: true, variant: 'tertiary', icon: 'edit', href: data.pageUrl + id, target: '_blank' }, __( 'Edit', D ) ) : null
		);
	}

	function Control( props ) {
		var field = props.field;
		var values = props.values;
		var set = props.set;
		var label = field.label;

		if ( field.locked ) {
			return el(
				'div',
				{ className: 'ffw-set-locked' },
				'switch' === field.type || 'toggle' === field.type
					? el( ui.Toggle, { checked: false, disabled: true, onChange: function () {} } )
					: el( ui.Select, { value: '', disabled: true, options: [ { value: '', label: ( field.options && field.options[ 0 ] && field.options[ 0 ].label ) || '—' } ], onChange: function () {} } ),
				el( ui.Button, { small: true, variant: 'brand', icon: 'crown', href: ui.config.proUrl, target: '_blank' }, __( 'Unlock with Pro', D ) )
			);
		}

		switch ( field.type ) {
			case 'toggle':
				return el( ui.Toggle, {
					checked: String( read( values, field ) ) === String( field.map.on ),
					label: label,
					onChange: function ( on ) {
						set( field.key, on ? field.map.on : field.map.off );
					}
				} );
			case 'switch':
				return el( ui.Toggle, {
					checked: isOn( values[ field.key ] ),
					label: label,
					onChange: function ( on ) {
						set( field.key, on ? 'on' : null );
					}
				} );
			case 'select':
				return el( ui.Select, {
					value: read( values, field ),
					label: label,
					width: '360px',
					options: ( field.options || [] ).map( function ( option ) {
						return { value: option.value, label: option.label + ( option.locked ? ' — ' + __( 'Pro', D ) : '' ), disabled: option.locked };
					} ),
					onChange: function ( value ) {
						set( field.key, field.array ? [ value ] : value );
					}
				} );
			case 'segment':
				return el( ui.Segment, {
					value: read( values, field ),
					label: label,
					options: field.options,
					onChange: function ( value ) {
						set( field.key, field.array ? [ value ] : value );
					}
				} );
			case 'text':
				return el( ui.TextInput, { value: read( values, field ), placeholder: field.placeholder, label: label, size: field.size || 'medium', onChange: function ( value ) {
					set( field.key, value );
				} } );
			case 'number':
				return el(
					'span',
					{ className: 'ffw-ui-input-group' },
					el( ui.TextInput, { type: 'number', className: 'is-short', value: read( values, field ), placeholder: field.placeholder, min: field.min, max: field.max, step: field.step, label: label, onChange: function ( value ) {
						set( field.key, value );
					} } ),
					field.suffix ? el( 'span', { className: 'ffw-ui-suffix' }, field.suffix ) : null
				);
			case 'secret':
				return el(
					Fragment,
					null,
					el( SecretInput, { value: read( values, field ), placeholder: field.placeholder, label: label, onChange: function ( value ) {
						set( field.key, value );
					} } ),
					field.link ? el( 'p', { className: 'ffw-ui-field-help' }, el( 'a', { href: field.link.url, target: '_blank', rel: 'noopener' }, field.link.label ) ) : null
				);
			case 'color':
				return el( ui.ColorInput, { value: read( values, field ), label: label, placeholder: field.default || __( 'Default', D ), onChange: function ( value ) {
					set( field.key, value );
				} } );
			case 'range':
				return el( RangeInput, { value: read( values, field ), min: field.min, max: field.max, label: label, onChange: function ( value ) {
					set( field.key, value );
				} } );
			case 'code':
				return el( ui.Textarea, { code: true, rows: 10, value: read( values, field ), label: label, placeholder: '.ffw-accordion { }', onChange: function ( value ) {
					set( field.key, value );
				} } );
			case 'richtext':
				return el( RichText, { id: 'ffw-set-' + field.id, value: read( values, field ), onChange: function ( value ) {
					set( field.key, value );
				} } );
			case 'page':
				return el( PagePicker, { value: read( values, field ), label: label, onChange: function ( value ) {
					set( field.key, value );
				} } );
			case 'roles':
				var roles = values[ field.key ];
				return el( ui.Checks, {
					value: Array.isArray( roles ) ? roles : field.default,
					options: data.roles,
					label: label,
					onChange: function ( value ) {
						set( field.key, value );
					}
				} );
			case 'icons':
				return el( IconPicker, { value: read( values, field ), label: label, onChange: function ( value ) {
					set( field.key, value );
				} } );
			case 'icon_style':
				var position = 'left' === values.ffw_icon_position ? 'left' : 'right';
				var size = values.ffw_icon_size ? String( values.ffw_icon_size ) : '';
				return el(
					'div',
					{ className: 'ffw-set-iconstyle' },
					el( 'div', null, el( 'span', { className: 'ffw-set-sublabel' }, __( 'Position', D ) ), el( ui.Segment, {
						value: position,
						label: __( 'Icon position', D ),
						options: [ { value: 'left', label: __( 'Left', D ) }, { value: 'right', label: __( 'Right', D ) } ],
						onChange: function ( value ) {
							set( 'ffw_icon_position', value );
						}
					} ) ),
					el( 'div', null, el( 'span', { className: 'ffw-set-sublabel' }, __( 'Size', D ) ), el( 'span', { className: 'ffw-ui-input-group' }, el( ui.TextInput, {
						type: 'number',
						className: 'is-short',
						min: 10,
						max: 48,
						value: size,
						placeholder: '20',
						label: __( 'Icon size', D ),
						onChange: function ( value ) {
							set( 'ffw_icon_size', value );
						}
					} ), el( 'span', { className: 'ffw-ui-suffix' }, 'px' ) ) ),
					el( 'div', null, el( 'span', { className: 'ffw-set-sublabel' }, __( 'Color', D ) ), el( ui.ColorInput, {
						value: values.ffw_icon_color || '',
						label: __( 'Icon color', D ),
						placeholder: __( 'Question color', D ),
						onChange: function ( value ) {
							set( 'ffw_icon_color', value );
						}
					} ) )
				);
			case 'legacy':
				return el( ui.LegacyHTML, {
					key: props.legacyKey,
					html: field.html,
					onMount: function ( node ) {
						props.legacyRefs.current[ field.id ] = node;
					},
					onChange: function () {
						props.onLegacyChange( field.id );
					}
				} );
		}
		return null;
	}

	function FieldRow( props ) {
		var field = props.field;
		// Toggles carry their label beside the switch; the row label stays for scanning.
		var help = field.help;
		var extra = [];
		if ( field.info ) {
			extra.push( el( 'p', { key: 'info', className: 'ffw-ui-field-help' }, field.info ) );
		}
		if ( field.status ) {
			extra.push( el(
				'div',
				{ key: 'status', className: ui.cx( 'ffw-set-status', field.status.ok ? 'is-ok' : 'is-off' ) },
				el( ui.Icon, { name: field.status.ok ? 'check' : 'info', size: 15 } ),
				el( 'span', null, el( 'strong', null, field.status.label ), ' ', field.status.help, ' ', field.status.connectors ? el( 'a', { href: field.status.connectors }, __( 'Open Connectors', D ) ) : null )
			) );
		}
		var toggleLike = ! field.locked && ( 'toggle' === field.type || 'switch' === field.type );
		return el(
			ui.Field,
			{
				label: field.label,
				help: help,
				helpBelow: false,
				stacked: field.stacked,
				dim: dimmed( props.values, field ),
				badge: field.pro && ! data.pro ? el( Fragment, null, ' ', el( ui.ProBadge ) ) : null,
				anchor: 'ffw-field-' + field.id,
				className: ui.cx( toggleLike && 'is-toggle', 'legacy' === field.type && 'is-legacy' )
			},
			el( Control, {
				field: toggleLike ? Object.assign( {}, field, { label: null } ) : field,
				values: props.values,
				set: props.set,
				legacyRefs: props.legacyRefs,
				legacyKey: props.legacyKey,
				onLegacyChange: props.onLegacyChange
			} ),
			extra
		);
	}

	/* ------------------------------------------------------------------
	 * Style preview.
	 * ------------------------------------------------------------------ */

	// Colors and sizes are swapped in place. These change the markup, so they reload it.
	var RELOAD_KEYS = [ 'ffw_icon_set', 'ffw_icon_position' ];

	function pick( values, keys ) {
		var out = {};
		keys.forEach( function ( key ) {
			if ( undefined !== values[ key ] && null !== values[ key ] ) {
				out[ key ] = String( values[ key ] );
			}
		} );
		return out;
	}

	// The site's layout and theme in a frame, with the values being edited.
	function StylePreview( props ) {
		var config = data.stylePreview;
		var frame = useRef( null );
		var loaded = useRef( false );
		var first = useRef( true );
		var request = useRef( 0 );
		var sent = useRef( JSON.stringify( pick( props.values, RELOAD_KEYS ) ) );
		var reload = useRef( false );
		var reply = useRef( null );
		var heightState = useState( 220 );
		var busyState = useState( true );
		var errorState = useState( '' );

		var current = JSON.stringify( pick( props.values, config.keys ) );

		function post( message ) {
			if ( frame.current && frame.current.contentWindow ) {
				frame.current.contentWindow.postMessage( message, new window.URL( config.url, window.location.href ).origin );
			}
		}

		function show() {
			if ( reply.current && loaded.current ) {
				post( Object.assign( { ffwStylePreview: 'css' }, reply.current ) );
			}
		}

		function update() {
			var id = ++request.current;
			var markup = JSON.stringify( pick( props.values, RELOAD_KEYS ) );
			busyState[ 1 ]( true );
			ui.api( '/settings/style-preview', { method: 'POST', data: { values: JSON.parse( current ) } } ).then( function ( result ) {
				if ( id !== request.current ) {
					return;
				}
				errorState[ 1 ]( '' );
				reply.current = result;
				if ( markup !== sent.current ) {
					sent.current = markup;
					if ( loaded.current ) {
						post( { ffwStylePreview: 'reload' } );
					} else {
						reload.current = true;
					}
					return; // onLoad clears the spinner.
				}
				show();
				busyState[ 1 ]( false );
			} ).catch( function ( error ) {
				if ( id === request.current ) {
					errorState[ 1 ]( ( error && error.message ) || __( 'The preview could not be updated.', D ) );
					busyState[ 1 ]( false );
				}
			} );
		}

		useEffect( function () {
			// The frame opens with the saved values.
			if ( first.current ) {
				first.current = false;
				if ( ! props.dirty ) {
					return undefined;
				}
				sent.current = '';
			}
			var timer = window.setTimeout( update, 250 );
			return function () {
				window.clearTimeout( timer );
			};
		}, [ current ] );

		useEffect( function () {
			function onMessage( event ) {
				if ( ! frame.current || event.source !== frame.current.contentWindow || ! event.data || 'size' !== event.data.ffwStylePreview ) {
					return;
				}
				heightState[ 1 ]( Math.max( 120, Math.min( 900, Number( event.data.height ) || 0 ) ) );
			}
			window.addEventListener( 'message', onMessage );
			return function () {
				window.removeEventListener( 'message', onMessage );
			};
		}, [] );

		return el(
			'div',
			{ className: 'ffw-set-preview' },
			el(
				'div',
				{ className: 'ffw-set-preview-head' },
				el( ui.Icon, { name: 'eye', size: 15 } ),
				__( 'Live preview', D ),
				busyState[ 0 ] ? el( 'span', { className: 'ffw-ui-spin', 'aria-label': __( 'Loading', D ) } ) : null
			),
			el( 'iframe', {
				ref: frame,
				className: 'ffw-set-preview-frame',
				src: config.url,
				title: __( 'Preview of your FAQ style', D ),
				style: { height: heightState[ 0 ] + 'px' },
				onLoad: function () {
					loaded.current = true;
					if ( reload.current ) {
						reload.current = false;
						post( { ffwStylePreview: 'reload' } );
						return;
					}
					busyState[ 1 ]( false );
					show();
				}
			} ),
			el(
				'p',
				{ className: 'ffw-set-preview-note' },
				errorState[ 0 ] || __( 'Your layout and theme with sample questions. Changes show here before you save.', D )
			)
		);
	}

	/* ------------------------------------------------------------------
	 * Import / Export.
	 * ------------------------------------------------------------------ */

	function download( name, contents ) {
		var url = window.URL.createObjectURL( new window.Blob( [ contents ], { type: 'application/json' } ) );
		var link = document.createElement( 'a' );
		link.href = url;
		link.download = name;
		document.body.appendChild( link );
		link.click();
		document.body.removeChild( link );
		window.setTimeout( function () {
			window.URL.revokeObjectURL( url );
		}, 1000 );
	}

	// Fields drawn by other plugins are HTML: put the new values in it.
	function legacyHtmlWith( html, values ) {
		var box = document.createElement( 'template' );
		box.innerHTML = html || '';
		Array.prototype.forEach.call( box.content.querySelectorAll( '[name^="ffw_general_settings["]' ), function ( input ) {
			var match = input.name.match( /^ffw_general_settings\[([^\]]+)\](\[\])?$/ );
			if ( ! match ) {
				return;
			}
			var value = values[ match[ 1 ] ];
			var list = ( Array.isArray( value ) ? value : undefined === value || null === value ? [] : [ value ] ).map( String );
			if ( 'checkbox' === input.type || 'radio' === input.type ) {
				// A form posts "on" for a box without a value.
				var own = input.hasAttribute( 'value' ) ? input.value : 'on';
				input.toggleAttribute( 'checked', list.indexOf( own ) !== -1 || ( 'checkbox' === input.type && ! match[ 2 ] && list.length > 0 && isOn( list[ 0 ] ) ) );
			} else if ( 'SELECT' === input.tagName ) {
				Array.prototype.forEach.call( input.options, function ( option ) {
					option.toggleAttribute( 'selected', list.indexOf( option.value ) !== -1 );
				} );
			} else if ( 'TEXTAREA' === input.tagName ) {
				input.textContent = list[ 0 ] || '';
			} else if ( 'hidden' !== input.type ) {
				input.setAttribute( 'value', list[ 0 ] || '' );
			}
		} );
		return box.innerHTML;
	}

	function ChangeList( props ) {
		var sections = [];
		props.changes.forEach( function ( change ) {
			var last = sections[ sections.length - 1 ];
			if ( ! last || last.label !== change.section ) {
				last = { label: change.section, items: [] };
				sections.push( last );
			}
			last.items.push( change );
		} );
		return el(
			'div',
			{ className: 'ffw-set-changes', tabIndex: 0, role: 'region', 'aria-label': __( 'Changes', D ) },
			sections.map( function ( section, index ) {
				return el(
					'div',
					{ key: index, className: 'ffw-set-changes-section' },
					el( 'h3', null, section.label, ' ', el( ui.Badge, null, section.items.length ) ),
					el( 'ul', null, section.items.map( function ( change ) {
						return el(
							'li',
							{ key: change.key },
							el( 'span', { className: 'ffw-set-change-label' }, change.label ),
							el( 'span', { className: 'ffw-set-change-values' },
								el( 'del', null, el( 'span', { className: 'ffw-ui-sr' }, __( 'From:', D ), ' ' ), change.from ),
								el( 'span', { className: 'ffw-set-change-arrow', 'aria-hidden': 'true' }, '→' ),
								el( 'ins', null, el( 'span', { className: 'ffw-ui-sr' }, __( 'To:', D ), ' ' ), change.to )
							)
						);
					} ) )
				);
			} )
		);
	}

	function Notes( props ) {
		return props.notes && props.notes.length ? el(
			ui.Notice,
			{ tone: 'info' },
			1 === props.notes.length
				? el( 'p', null, props.notes[ 0 ] )
				: el( 'ul', { className: 'ffw-set-notes' }, props.notes.map( function ( note, index ) {
					return el( 'li', { key: index }, note );
				} ) )
		) : null;
	}

	function Transfer( props ) {
		var transfer = props.transfer;
		var secretsState = useState( false );
		var busyState = useState( '' );
		var errorState = useState( '' );
		var overState = useState( false );
		var fileState = useState( null );
		var doneState = useState( null );
		var file = fileState[ 0 ];
		var done = doneState[ 0 ];
		var busy = busyState[ 0 ];

		function runExport() {
			busyState[ 1 ]( 'export' );
			ui.api( '/settings/export', { query: { secrets: secretsState[ 0 ] && transfer.hasSecret ? 1 : 0 } } ).then( function ( result ) {
				busyState[ 1 ]( '' );
				download( result.name, JSON.stringify( result.file, null, 2 ) );
				ui.toast( sprintf( __( 'Downloaded %s', D ), result.name ) );
			} ).catch( function ( error ) {
				busyState[ 1 ]( '' );
				ui.toast( ui.errorText( error ), 'error' );
			} );
		}

		function choose( picked ) {
			if ( ! picked || busy ) {
				return;
			}
			errorState[ 1 ]( '' );
			doneState[ 1 ]( null );
			if ( picked.size > transfer.maxSize ) {
				errorState[ 1 ]( __( 'That file is too large to be a Happy FAQs settings file.', D ) );
				return;
			}
			busyState[ 1 ]( 'read' );
			var reader = new window.FileReader();
			reader.onerror = function () {
				busyState[ 1 ]( '' );
				errorState[ 1 ]( __( 'Your browser could not read that file.', D ) );
			};
			reader.onload = function () {
				ui.api( '/settings/import', { method: 'POST', data: { file: String( reader.result ), preview: true } } ).then( function ( result ) {
					busyState[ 1 ]( '' );
					fileState[ 1 ]( { name: picked.name, text: String( reader.result ), preview: result } );
				} ).catch( function ( error ) {
					busyState[ 1 ]( '' );
					errorState[ 1 ]( ui.errorText( error ) );
				} );
			};
			reader.readAsText( picked );
		}

		function runImport() {
			busyState[ 1 ]( 'import' );
			ui.api( '/settings/import', { method: 'POST', data: { file: file.text, preview: false } } ).then( function ( result ) {
				busyState[ 1 ]( '' );
				fileState[ 1 ]( null );
				doneState[ 1 ]( { count: result.changes.length, notes: result.notes } );
				props.onSaved( result );
				ui.toast( __( 'Settings imported.', D ) );
			} ).catch( function ( error ) {
				busyState[ 1 ]( '' );
				ui.toast( ui.errorText( error ), 'error' );
			} );
		}

		function undo() {
			ui.confirm( {
				title: __( 'Undo the import?', D ),
				message: __( 'Every setting goes back to how it was before the import, including settings you changed since.', D ),
				confirm: __( 'Undo import', D ),
				danger: true
			} ).then( function ( ok ) {
				if ( ! ok ) {
					return;
				}
				busyState[ 1 ]( 'undo' );
				ui.api( '/settings/restore', { method: 'POST' } ).then( function ( result ) {
					busyState[ 1 ]( '' );
					doneState[ 1 ]( null );
					fileState[ 1 ]( null );
					props.onSaved( result );
					ui.toast( __( 'Import undone. Your previous settings are back.', D ) );
				} ).catch( function ( error ) {
					busyState[ 1 ]( '' );
					ui.toast( ui.errorText( error ), 'error' );
				} );
			} );
		}

		var importBody;
		if ( file ) {
			var changes = file.preview.changes;
			var from = [
				file.preview.site ? file.preview.site.replace( /^https?:\/\//, '' ).replace( /\/$/, '' ) : '',
				file.preview.exported,
				file.preview.version ? sprintf( __( 'Happy FAQs %s', D ), file.preview.version ) : ''
			].filter( Boolean ).join( ' · ' );
			importBody = el(
				'div',
				{ className: 'ffw-set-import' },
				el( 'div', { className: 'ffw-set-file' },
					el( ui.Icon, { name: 'page', size: 18 } ),
					el( 'div', null, el( 'strong', null, file.name ), from ? el( 'span', null, from ) : null )
				),
				changes.length
					? el( Fragment, null,
						el( 'p', { className: 'ffw-set-import-lead', role: 'status' }, 1 === changes.length ? __( '1 setting will change:', D ) : sprintf( __( '%d settings will change:', D ), changes.length ) ),
						el( ChangeList, { changes: changes } )
					)
					: el( ui.Notice, { tone: 'good' }, el( 'p', null, __( 'This site already has these settings. There is nothing to import.', D ) ) ),
				el( Notes, { notes: file.preview.notes } ),
				props.dirty && changes.length ? el( ui.Notice, { tone: 'warn' }, el( 'p', null, __( 'You have changes on this page that are not saved. Importing discards them.', D ) ) ) : null,
				el( 'div', { className: 'ffw-ui-row ffw-set-import-actions' },
					changes.length ? el( ui.Button, { variant: 'brand', icon: 'upload', busy: 'import' === busy, onClick: runImport }, 1 === changes.length ? __( 'Import 1 change', D ) : sprintf( __( 'Import %d changes', D ), changes.length ) ) : null,
					el( ui.Button, { disabled: !! busy, onClick: function () {
						fileState[ 1 ]( null );
					} }, changes.length ? __( 'Cancel', D ) : __( 'Choose another file', D ) )
				)
			);
		} else {
			importBody = el(
				'div',
				{ className: 'ffw-set-import' },
				done ? el( ui.Notice, { tone: 'good' }, el( 'p', null, 1 === done.count ? __( 'Imported. 1 setting changed.', D ) : sprintf( __( 'Imported. %d settings changed.', D ), done.count ) ) ) : null,
				done ? el( Notes, { notes: done.notes } ) : null,
				errorState[ 0 ] ? el( ui.Notice, { tone: 'bad' }, el( 'p', null, errorState[ 0 ] ) ) : null,
				el(
					'label',
					{
						className: ui.cx( 'ffw-set-drop', overState[ 0 ] && 'is-over', 'read' === busy && 'is-busy' ),
						onDragOver: function ( event ) {
							event.preventDefault();
							overState[ 1 ]( true );
						},
						onDragLeave: function () {
							overState[ 1 ]( false );
						},
						onDrop: function ( event ) {
							event.preventDefault();
							overState[ 1 ]( false );
							choose( event.dataTransfer.files && event.dataTransfer.files[ 0 ] );
						}
					},
					el( 'input', { type: 'file', accept: '.json,application/json', onChange: function ( event ) {
						choose( event.target.files && event.target.files[ 0 ] );
						event.target.value = '';
					} } ),
					el( 'span', { className: 'ffw-set-drop-icon' }, 'read' === busy ? el( 'span', { className: 'ffw-ui-spin is-large' } ) : el( ui.Icon, { name: 'upload', size: 24 } ) ),
					el( 'strong', null, 'read' === busy ? __( 'Reading…', D ) : __( 'Drop a settings file here, or click to choose one', D ) ),
					el( 'span', { className: 'ffw-ui-muted' }, __( 'You see every change before anything is saved.', D ) )
				)
			);
		}

		return el(
			Fragment,
			null,
			el(
				ui.Card,
				{
					key: 'export',
					title: __( 'Export settings', D ),
					description: __( 'Download every Happy FAQs setting as one file, to keep as a backup or use on another site.', D )
				},
				el(
					'div',
					{ className: 'ffw-set-export' },
					transfer.hasSecret ? el( 'div', null,
						el( ui.Toggle, { checked: secretsState[ 0 ], label: __( 'Include the API key', D ), onChange: secretsState[ 1 ] } ),
						el( 'p', { className: 'ffw-ui-field-help' }, secretsState[ 0 ] ? __( 'The file will hold your key as plain text. Share it only with people you trust.', D ) : __( 'Your AI key stays out of the file.', D ) )
					) : null,
					props.dirty ? el( ui.Notice, { tone: 'warn' }, el( 'p', null, __( 'You have changes on this page that are not saved. Save them first to have them in the file.', D ) ) ) : null,
					el( ui.Button, { variant: 'primary', icon: 'download', busy: 'export' === busy, disabled: !! busy, onClick: runExport }, __( 'Download settings file', D ) )
				)
			),
			el(
				ui.Card,
				{
					key: 'import',
					title: __( 'Import settings', D ),
					description: __( 'Make this site\u2019s settings the same as a settings file.', D )
				},
				importBody
			),
			transfer.backup ? el(
				ui.Card,
				{
					key: 'undo',
					title: __( 'Undo the last import', D ),
					description: sprintf( __( 'The settings from before the import on %s are kept.', D ), transfer.backup.date ),
					actions: el( ui.Button, { icon: 'refresh', busy: 'undo' === busy, disabled: !! busy, onClick: undo }, __( 'Undo import', D ) ),
					flush: true
				}
			) : null,
			el(
				'p',
				{ className: 'ffw-set-transfer-note' },
				__( 'These files hold settings only, not your FAQs.', D ),
				' ',
				transfer.faqsUrl ? el( 'a', { href: transfer.faqsUrl }, __( 'Move FAQs on the Import / Export page', D ) ) : null
			)
		);
	}

	/* ------------------------------------------------------------------
	 * Page.
	 * ------------------------------------------------------------------ */

	function sectionFromHash() {
		var hash = window.location.hash.replace( /^#/, '' );
		if ( OLD_TABS[ hash ] ) {
			return OLD_TABS[ hash ];
		}
		var found = SECTIONS.some( function ( section ) {
			return section.id === hash;
		} );
		return found ? hash : ( LAYOUT[ 0 ] && LAYOUT[ 0 ].id );
	}

	function SettingsPage() {
		var initial = useMemo( function () {
			return plain( data.values );
		}, [] );
		var savedState = useState( initial );
		var valuesState = useState( initial );
		var sectionState = useState( sectionFromHash );
		var searchState = useState( '' );
		var savingState = useState( false );
		var legacyDirtyState = useState( {} );
		var legacyKeyState = useState( 0 );
		var openGroupsState = useState( {} );
		var transferState = useState( data.transfer );
		var legacyRefs = useRef( {} );

		var saved = savedState[ 0 ];
		var values = valuesState[ 0 ];
		var section = sectionState[ 0 ];
		var search = searchState[ 0 ].trim();

		useEffect( function () {
			function onHash() {
				sectionState[ 1 ]( sectionFromHash() );
			}
			window.addEventListener( 'hashchange', onHash );
			return function () {
				window.removeEventListener( 'hashchange', onHash );
			};
		}, [] );

		function set( key, value ) {
			valuesState[ 1 ]( function ( current ) {
				var next = Object.assign( {}, current );
				if ( null === value ) {
					delete next[ key ];
				} else {
					next[ key ] = value;
				}
				return next;
			} );
		}

		// Keys whose value differs from the saved one.
		function changedKeys() {
			var keys = {};
			Object.keys( FIELDS ).forEach( function ( id ) {
				var field = FIELDS[ id ];
				if ( field.locked || 'legacy' === field.type ) {
					return;
				}
				fieldKeys( field ).forEach( function ( key ) {
					if ( ! ui.same( values[ key ], saved[ key ] ) ) {
						keys[ key ] = true;
					}
				} );
			} );
			return Object.keys( keys );
		}

		var changed = changedKeys();
		var legacyDirty = Object.keys( legacyDirtyState[ 0 ] );
		var dirty = changed.length > 0 || legacyDirty.length > 0;

		function sectionDirty( sec ) {
			return sec.groups.some( function ( group ) {
				return group.fields.some( function ( id ) {
					var field = FIELDS[ id ];
					if ( ! field ) {
						return false;
					}
					if ( 'legacy' === field.type ) {
						return !! legacyDirtyState[ 0 ][ id ];
					}
					return fieldKeys( field ).some( function ( key ) {
						return changed.indexOf( key ) !== -1;
					} );
				} );
			} );
		}

		function save() {
			var payload = {};
			changed.forEach( function ( key ) {
				payload[ key ] = undefined === values[ key ] ? null : values[ key ];
			} );
			var legacy = { names: [], pairs: [] };
			legacyDirty.forEach( function ( id ) {
				var node = legacyRefs.current[ id ];
				if ( node ) {
					var form = ui.serializeFields( node );
					legacy.names = legacy.names.concat( form.names );
					legacy.pairs = legacy.pairs.concat( form.pairs );
				}
			} );
			savingState[ 1 ]( true );
			return ui.api( '/settings', { method: 'POST', data: { values: payload, legacy: legacy } } )
				.then( function ( response ) {
					var next = plain( response.values );
					savedState[ 1 ]( next );
					valuesState[ 1 ]( next );
					legacyDirtyState[ 1 ]( {} );
					savingState[ 1 ]( false );
					ui.toast( __( 'Settings saved.', D ) );
				} )
				.catch( function ( error ) {
					savingState[ 1 ]( false );
					ui.toast( ui.errorText( error ), 'error' );
				} );
		}

		// Settings saved by an import or its undo: show them everywhere on the page.
		function onTransferred( response ) {
			var next = plain( response.values );
			Object.keys( FIELDS ).forEach( function ( id ) {
				if ( 'legacy' === FIELDS[ id ].type ) {
					FIELDS[ id ].html = legacyHtmlWith( FIELDS[ id ].html, next );
				}
				// A model that is not in the list is still a choice, like on page load.
				var model = 'ffw_ai_faqs_model' === FIELDS[ id ].key ? next.ffw_ai_faqs_model : '';
				if ( model && FIELDS[ id ].options && ! FIELDS[ id ].options.some( function ( option ) {
					return option.value === model;
				} ) ) {
					FIELDS[ id ].options.push( { value: model, label: model } );
				}
			} );
			savedState[ 1 ]( next );
			valuesState[ 1 ]( next );
			legacyDirtyState[ 1 ]( {} );
			legacyKeyState[ 1 ]( legacyKeyState[ 0 ] + 1 );
			transferState[ 1 ]( response.transfer );
		}

		function discard() {
			valuesState[ 1 ]( plain( saved ) );
			if ( legacyDirty.length ) {
				legacyDirtyState[ 1 ]( {} );
				legacyKeyState[ 1 ]( legacyKeyState[ 0 ] + 1 );
			}
		}

		function onLegacyChange( id ) {
			legacyDirtyState[ 1 ]( function ( current ) {
				if ( current[ id ] ) {
					return current;
				}
				var next = Object.assign( {}, current );
				next[ id ] = true;
				return next;
			} );
		}

		function go( id ) {
			window.history.replaceState( null, '', '#' + id );
			sectionState[ 1 ]( id );
			searchState[ 1 ]( '' );
		}

		function renderGroup( group, index, sectionId ) {
			var ids = group.fields.filter( function ( id ) {
				return FIELDS[ id ] && matches( FIELDS[ id ], search );
			} );
			if ( ! ids.length ) {
				return null;
			}
			var groupKey = sectionId + ':' + index;
			var collapsed = group.collapsed && ! search && ! openGroupsState[ 0 ][ groupKey ];
			var rows = ids.map( function ( id ) {
				return el( FieldRow, {
					key: id + ':' + legacyKeyState[ 0 ],
					field: FIELDS[ id ],
					values: values,
					set: set,
					legacyRefs: legacyRefs,
					legacyKey: legacyKeyState[ 0 ],
					onLegacyChange: onLegacyChange
				} );
			} );
			return el(
				ui.Card,
				{
					key: groupKey,
					title: group.title,
					description: group.text,
					actions: group.collapsed && ! search ? el( ui.Button, {
						small: true,
						variant: 'tertiary',
						icon: collapsed ? 'down' : 'back',
						onClick: function () {
							var next = Object.assign( {}, openGroupsState[ 0 ] );
							next[ groupKey ] = collapsed;
							openGroupsState[ 1 ]( next );
						}
					}, collapsed ? sprintf( __( 'Show %d settings', D ), ids.length ) : __( 'Hide', D ) ) : null
				},
				collapsed ? null : el( 'div', { className: 'ffw-ui-fields' }, rows )
			);
		}

		var current = null;
		SECTIONS.forEach( function ( item ) {
			if ( item.id === section ) {
				current = item;
			}
		} );
		current = current || LAYOUT[ 0 ];

		var body;
		if ( search ) {
			var found = LAYOUT.map( function ( sec ) {
				var groups = sec.groups.map( function ( group, index ) {
					return renderGroup( group, index, sec.id );
				} ).filter( Boolean );
				return groups.length ? el( 'div', { key: sec.id, className: 'ffw-set-results' }, el( 'h2', { className: 'ffw-set-results-title' }, sec.label ), groups ) : null;
			} ).filter( Boolean );
			var transferWords = [ TRANSFER.label, __( 'settings backup restore move copy file json', D ) ].join( ' ' ).toLowerCase();
			if ( SECTIONS !== LAYOUT && search.toLowerCase().split( /\s+/ ).every( function ( word ) {
				return transferWords.indexOf( word ) !== -1;
			} ) ) {
				found.push( el( 'div', { key: TRANSFER.id, className: 'ffw-set-results' }, el( ui.Card, {
					title: TRANSFER.label,
					description: __( 'Download every setting as a file, or import one.', D ),
					actions: el( ui.Button, { icon: 'next', onClick: function () {
						go( TRANSFER.id );
					} }, __( 'Open', D ) ),
					flush: true
				} ) ) );
			}
			body = found.length ? found : el( ui.Card, null, el( ui.Empty, { icon: 'search', title: __( 'No settings match', D ), text: sprintf( __( 'Nothing found for "%s". Try another word.', D ), search ) } ) );
		} else if ( current === TRANSFER ) {
			body = el( Transfer, { transfer: transferState[ 0 ], dirty: dirty, onSaved: onTransferred } );
		} else {
			body = current.groups.map( function ( group, index ) {
				return renderGroup( group, index, current.id );
			} );
			if ( 'store' === current.id && ! data.woo ) {
				body.unshift( el( ui.Notice, { key: 'woo', tone: 'info' }, el( 'p', null, __( 'WooCommerce is not active. These settings apply once it is.', D ) ) ) );
			}
		}

		var showPreview = ! search && current.preview;

		return el(
			Fragment,
			null,
			el( ui.PageHead, {
				title: __( 'Settings', D ),
				description: __( 'How your FAQs look and behave across the site.', D ),
				actions: el(
					'label',
					{ className: 'ffw-set-search' },
					el( ui.Icon, { name: 'search', size: 16 } ),
					el( 'span', { className: 'ffw-ui-sr' }, __( 'Search settings', D ) ),
					el( 'input', {
						type: 'search',
						className: 'ffw-ui-input',
						placeholder: __( 'Search settings', D ),
						value: searchState[ 0 ],
						onChange: function ( event ) {
							searchState[ 1 ]( event.target.value );
						}
					} )
				)
			} ),
			el(
				'div',
				{ className: 'ffw-set-layout' },
				el(
					'aside',
					{ className: 'ffw-set-nav' },
					el( ui.SideNav, {
						label: __( 'Settings sections', D ),
						value: search ? '' : current.id,
						onChange: go,
						items: SECTIONS.map( function ( sec ) {
							return { id: sec.id, label: sec.label, icon: sec.icon, dot: sectionDirty( sec ) };
						} )
					} ),
					! data.pro ? el(
						'div',
						{ className: 'ffw-set-nav-pro' },
						el( 'strong', null, __( 'Happy FAQs Pro', D ) ),
						el( 'p', null, __( 'Store page FAQs, customer questions, contact form suggestions and more.', D ) ),
						el( ui.Button, { small: true, variant: 'brand', icon: 'crown', href: ui.config.proUrl, target: '_blank' }, __( 'See Pro', D ) )
					) : null
				),
				el(
					'div',
					{ className: ui.cx( 'ffw-set-main', showPreview && 'has-preview' ) },
					el( 'div', { className: 'ffw-set-groups' }, body ),
					showPreview ? el( 'aside', { className: 'ffw-set-aside' }, el( StylePreview, { values: values, dirty: dirty } ) ) : null
				)
			),
			el( ui.SaveBar, {
				dirty: dirty,
				saving: savingState[ 0 ],
				onSave: save,
				onDiscard: discard,
				text: changed.length + legacyDirty.length === 1 ? __( 'You changed 1 setting', D ) : sprintf( __( 'You changed %d settings', D ), changed.length + legacyDirty.length )
			} ),
			el( ui.ClassicLink, null )
		);
	}

	ui.mount( SettingsPage );
} )( window.wp, window.ffwUI, window.ffwPage );
