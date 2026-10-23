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

	function StylePreview( props ) {
		var values = props.values;
		var icon = null;
		var set = values.ffw_icon_set || 'default';
		( data.icons || [] ).some( function ( item ) {
			if ( item.value === set ) {
				icon = item;
				return true;
			}
			return false;
		} );
		var left = 'left' === values.ffw_icon_position;
		var iconStyle = {
			color: values.ffw_icon_color || values.ffw_question_text_color || undefined,
			width: ( values.ffw_icon_size || 18 ) + 'px',
			height: ( values.ffw_icon_size || 18 ) + 'px'
		};
		function iconFor( open ) {
			if ( 'none' === set ) {
				return null;
			}
			if ( ! icon || ! icon.svg ) {
				return el( 'span', { className: 'ffw-set-pv-icon is-text', style: { color: iconStyle.color } }, open ? '−' : '+' );
			}
			return el( 'span', {
				className: ui.cx( 'ffw-set-pv-icon', open && 'ffw-accordion-active' ),
				style: iconStyle,
				dangerouslySetInnerHTML: { __html: icon.svg }
			} );
		}
		var question = {
			color: values.ffw_question_text_color || undefined,
			background: values.ffw_question_bg_color || undefined,
			borderColor: values.ffw_question_border_color || undefined,
			fontSize: values.ffw_question_font_size ? values.ffw_question_font_size + 'px' : undefined,
			flexDirection: left ? 'row-reverse' : 'row',
			justifyContent: left ? 'flex-end' : 'space-between'
		};
		var answer = {
			color: values.ffw_answer_text_color || undefined,
			background: values.ffw_answer_bg_color || undefined,
			borderColor: values.ffw_answer_border_color || undefined
		};
		var items = [
			[ __( 'How long does shipping take?', D ), __( 'Orders ship within 1–2 business days. Delivery takes 3–5 days in most areas.', D ), true ],
			[ __( 'Can I return an item?', D ), '', false ],
			[ __( 'Do you ship internationally?', D ), '', false ]
		];
		return el(
			'div',
			{ className: 'ffw-set-preview' },
			el( 'div', { className: 'ffw-set-preview-head' }, el( ui.Icon, { name: 'eye', size: 15 } ), __( 'Preview', D ) ),
			el(
				'div',
				{ className: 'ffw-set-preview-canvas' },
				el(
					'div',
					{ className: 'ffw-set-pv', style: { width: values.ffw_width ? Math.min( 100, Number( values.ffw_width ) ) + '%' : undefined } },
					items.map( function ( item, index ) {
						return el(
							'div',
							{ key: index, className: ui.cx( 'ffw-set-pv-item', item[ 2 ] && 'is-open' ) },
							el( 'div', { className: 'ffw-set-pv-q', style: question }, el( 'span', null, item[ 0 ] ), iconFor( item[ 2 ] ) ),
							item[ 2 ] ? el( 'div', { className: 'ffw-set-pv-a', style: answer }, item[ 1 ] ) : null
						);
					} )
				)
			),
			el( 'p', { className: 'ffw-set-preview-note' }, __( 'A sketch of your colors and icon. Each layout adds its own shape.', D ) )
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
		var found = LAYOUT.some( function ( section ) {
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
		LAYOUT.forEach( function ( item ) {
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
			body = found.length ? found : el( ui.Card, null, el( ui.Empty, { icon: 'search', title: __( 'No settings match', D ), text: sprintf( __( 'Nothing found for "%s". Try another word.', D ), search ) } ) );
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
						items: LAYOUT.map( function ( sec ) {
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
					showPreview ? el( 'aside', { className: 'ffw-set-aside' }, el( StylePreview, { values: values } ) ) : null
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
