/**
 * Happy FAQs → FAQs: All FAQs, Add New and Categories on one screen.
 *
 * React (wp.element) without a build step. Routes live in the URL hash:
 * #/list?status=&search=&category=&orderby=&order=&page=, #/new, #/edit/123,
 * #/categories. Add New and Edit load WordPress's FAQ editor in an iframe
 * (embed mode), so every field, Pro option and saved setting keeps working.
 *
 * @since 3.0.0
 */
( function ( wp, config ) {
	'use strict';

	if ( ! wp || ! wp.element || ! config ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var useRef = wp.element.useRef;
	var useCallback = wp.element.useCallback;
	var __ = wp.i18n.__;
	var _n = wp.i18n._n;
	var sprintf = wp.i18n.sprintf;
	var apiFetch = wp.apiFetch;
	var D = 'faq-for-woocommerce';

	/* ------------------------------------------------------------------
	 * Helpers.
	 * ------------------------------------------------------------------ */

	function api( path, options ) {
		options = options || {};
		var url = config.root + path;
		if ( options.query ) {
			url = wp.url.addQueryArgs( url, options.query );
		}
		return apiFetch( {
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
				var value = window.localStorage.getItem( 'ffw_mgr_' + key );
				return null === value ? fallback : JSON.parse( value );
			} catch ( e ) {
				return fallback;
			}
		},
		set: function ( key, value ) {
			try {
				window.localStorage.setItem( 'ffw_mgr_' + key, JSON.stringify( value ) );
			} catch ( e ) {}
		}
	};

	/* ------------------------------------------------------------------
	 * Routing (URL hash).
	 * ------------------------------------------------------------------ */

	function parseRoute() {
		var hash = window.location.hash.replace( /^#/, '' ) || '/list';
		var parts = hash.split( '?' );
		var path = parts[ 0 ];
		var query = {};
		new URLSearchParams( parts[ 1 ] || '' ).forEach( function ( value, key ) {
			query[ key ] = value;
		} );

		var edit = path.match( /^\/edit\/(\d+)/ );
		if ( edit ) {
			return { view: 'editor', id: Number( edit[ 1 ] ), query: query };
		}
		if ( '/new' === path ) {
			return { view: 'editor', id: 0, query: query };
		}
		if ( '/categories' === path ) {
			return { view: 'categories', query: query };
		}
		return { view: 'list', query: query };
	}

	function buildHash( view, query, id ) {
		var path = 'editor' === view ? ( id ? '/edit/' + id : '/new' ) : '/' + view;
		var search = new URLSearchParams();
		Object.keys( query || {} ).forEach( function ( key ) {
			var value = query[ key ];
			if ( value !== '' && value !== undefined && value !== null && value !== 0 && value !== '0' ) {
				search.set( key, value );
			}
		} );
		var qs = search.toString();
		return '#' + path + ( qs ? '?' + qs : '' );
	}

	// One guard at a time (the editor), asked before the route changes.
	var leaveGuard = null;

	function navigate( view, query, id, replace ) {
		var hash = buildHash( view, query, id );
		if ( hash === window.location.hash ) {
			return;
		}
		if ( leaveGuard && ! leaveGuard() ) {
			return;
		}
		leaveGuard = null;
		if ( replace ) {
			window.history.replaceState( null, '', hash );
			window.dispatchEvent( new HashChangeEvent( 'hashchange' ) );
		} else {
			window.location.hash = hash;
		}
	}

	/* ------------------------------------------------------------------
	 * Shared UI.
	 * ------------------------------------------------------------------ */

	function Icon( props ) {
		var paths = {
			plus: 'M12 5v14M5 12h14',
			search: 'M11 4a7 7 0 1 0 0 14 7 7 0 0 0 0-14zm10 17-5.2-5.2',
			back: 'M15 18l-6-6 6-6',
			external: 'M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5',
			copy: 'M9 9h10v10H9zM5 15V5h10',
			check: 'M5 12l5 5L20 7',
			close: 'M6 6l12 12M18 6 6 18',
			edit: 'M4 20h4L19 9l-4-4L4 16v4z',
			folder: 'M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z',
			list: 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01',
			sparkle: 'M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M6 18l2.5-2.5M15.5 8.5 18 6',
			trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3'
		};
		return el(
			'svg',
			{ className: cx( 'ffw-mgr-icon', props.className ), viewBox: '0 0 24 24', width: props.size || 16, height: props.size || 16, 'aria-hidden': 'true', focusable: 'false' },
			el( 'path', { d: paths[ props.name ], fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' } )
		);
	}

	function Button( props ) {
		var variant = props.variant || 'secondary';
		return el(
			'button',
			{
				type: props.type || 'button',
				className: cx( 'ffw-mgr-btn', 'is-' + variant, props.small && 'is-small', props.className ),
				onClick: props.onClick,
				disabled: props.disabled || props.busy,
				title: props.title,
				'aria-label': props.label,
				'aria-pressed': props.pressed
			},
			props.busy ? el( 'span', { className: 'ffw-mgr-spin', 'aria-hidden': 'true' } ) : props.icon ? el( Icon, { name: props.icon } ) : null,
			props.children
		);
	}

	function Spinner( props ) {
		return el( 'div', { className: 'ffw-mgr-loading-block', role: 'status' }, el( 'span', { className: 'ffw-mgr-spin is-large', 'aria-hidden': 'true' } ), el( 'span', null, props.text || __( 'Loading…', D ) ) );
	}

	function Modal( props ) {
		var ref = useRef( null );
		useEffect( function () {
			var previous = document.activeElement;
			var focusable = ref.current && ref.current.querySelector( 'input, select, textarea, button.is-primary, button.is-danger' );
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
				className: 'ffw-mgr-modal-backdrop',
				onMouseDown: function ( event ) {
					if ( event.target === event.currentTarget ) {
						props.onClose();
					}
				}
			},
			el(
				'div',
				{ className: 'ffw-mgr-modal', role: 'dialog', 'aria-modal': 'true', 'aria-labelledby': 'ffw-mgr-modal-title', ref: ref },
				el( 'h2', { id: 'ffw-mgr-modal-title' }, props.title ),
				el( 'div', { className: 'ffw-mgr-modal-body' }, props.children ),
				el( 'div', { className: 'ffw-mgr-modal-actions' }, props.actions )
			)
		);
	}

	// Promise-based confirm in our own dialog.
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
		var setDialog = state[ 1 ];
		useEffect( function () {
			confirmSetter = setDialog;
			return function () {
				confirmSetter = null;
			};
		}, [] );
		if ( ! dialog ) {
			return null;
		}
		function close( answer ) {
			dialog.resolve( answer );
			setDialog( null );
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

	// Toasts.
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
			{ className: 'ffw-mgr-toasts', role: 'status', 'aria-live': 'polite' },
			items.map( function ( item ) {
				return el( 'div', { key: item.key, className: 'ffw-mgr-toast is-' + item.type }, el( Icon, { name: 'error' === item.type ? 'close' : 'check' } ), item.message );
			} )
		);
	}

	function StatusPill( props ) {
		return el( 'span', { className: 'ffw-mgr-pill is-' + props.status }, props.label );
	}

	function CategorySelect( props ) {
		return el(
			'select',
			{
				className: props.className,
				value: props.value,
				'aria-label': props.label,
				onChange: function ( event ) {
					props.onChange( Number( event.target.value ) );
				}
			},
			props.allLabel ? el( 'option', { value: 0 }, props.allLabel ) : null,
			props.uncategorized ? el( 'option', { value: -1 }, __( 'Uncategorized', D ) ) : null,
			( props.categories || [] ).map( function ( cat ) {
				if ( props.exclude && props.exclude( cat ) ) {
					return null;
				}
				return el( 'option', { key: cat.id, value: cat.id }, ' '.repeat( cat.depth ) + cat.name );
			} )
		);
	}

	function CategoryChecklist( props ) {
		var selected = props.value || [];
		var search = useState( '' );
		var list = ( props.categories || [] ).filter( function ( cat ) {
			return ! search[ 0 ] || cat.name.toLowerCase().indexOf( search[ 0 ].toLowerCase() ) !== -1;
		} );
		return el(
			'div',
			{ className: 'ffw-mgr-checklist' },
			( props.categories || [] ).length > 8 ? el( 'input', { type: 'search', className: 'ffw-mgr-input is-small', placeholder: __( 'Find a category', D ), value: search[ 0 ], onChange: function ( event ) {
				search[ 1 ]( event.target.value );
			} } ) : null,
			list.length ? el(
				'ul',
				null,
				list.map( function ( cat ) {
					var checked = selected.indexOf( cat.id ) !== -1;
					return el(
						'li',
						{ key: cat.id, style: { paddingInlineStart: cat.depth * 16 + 'px' } },
						el(
							'label',
							null,
							el( 'input', { type: 'checkbox', checked: checked, onChange: function () {
								props.onChange( checked ? selected.filter( function ( id ) {
									return id !== cat.id;
								} ) : selected.concat( [ cat.id ] ) );
							} } ),
							' ',
							cat.name
						)
					);
				} )
			) : el( 'p', { className: 'ffw-mgr-muted' }, props.categories && props.categories.length ? __( 'No match.', D ) : __( 'No categories yet.', D ) )
		);
	}

	/* ------------------------------------------------------------------
	 * Data shared between tabs.
	 * ------------------------------------------------------------------ */

	var shared = {
		listStale: false,
		categories: null,
		counts: null
	};

	function useCategories() {
		var state = useState( shared.categories );
		var setState = state[ 1 ];
		var load = useCallback( function () {
			return api( '/categories' ).then( function ( data ) {
				shared.categories = data;
				setState( data );
				return data;
			} );
		}, [] );
		useEffect( function () {
			if ( ! shared.categories ) {
				load().catch( function () {} );
			}
		}, [] );
		return [ state[ 0 ], load, function ( data ) {
			shared.categories = data;
			setState( data );
		} ];
	}

	/* ------------------------------------------------------------------
	 * All FAQs.
	 * ------------------------------------------------------------------ */

	var SORTS = [
		{ value: '', label: __( 'Newest first', D ) },
		{ value: 'date:asc', label: __( 'Oldest first', D ) },
		{ value: 'title:asc', label: __( 'Question A–Z', D ) },
		{ value: 'modified:desc', label: __( 'Recently updated', D ) },
		{ value: 'views:desc', label: __( 'Most viewed', D ) },
		{ value: 'helpful:desc', label: __( 'Most helpful', D ) },
		{ value: 'menu_order:asc', label: __( 'Custom order', D ) }
	];

	function Placements( props ) {
		var item = props.item;
		var chips = [];
		if ( item.global ) {
			chips.push( el( 'span', { key: 'g', className: 'ffw-mgr-chip is-accent', title: __( 'Shown on every product', D ) }, __( 'Global', D ) ) );
		}
		if ( item.products && item.products.length ) {
			chips.push(
				el(
					'span',
					{ key: 'p', className: 'ffw-mgr-chip', title: item.products.map( function ( p ) {
						return p.name;
					} ).join( ', ' ) },
					1 === item.products.length ? item.products[ 0 ].name : sprintf( _n( '%s product', '%s products', item.products.length, D ), number( item.products.length ) )
				)
			);
		}
		if ( item.productCats && item.productCats.length ) {
			chips.push( el( 'span', { key: 'c', className: 'ffw-mgr-chip', title: item.productCats.join( ', ' ) }, 1 === item.productCats.length ? sprintf( __( 'Category: %s', D ), item.productCats[ 0 ] ) : sprintf( _n( '%s product category', '%s product categories', item.productCats.length, D ), number( item.productCats.length ) ) ) );
		}
		if ( item.productTags && item.productTags.length ) {
			chips.push( el( 'span', { key: 't', className: 'ffw-mgr-chip', title: item.productTags.join( ', ' ) }, 1 === item.productTags.length ? sprintf( __( 'Tag: %s', D ), item.productTags[ 0 ] ) : sprintf( _n( '%s product tag', '%s product tags', item.productTags.length, D ), number( item.productTags.length ) ) ) );
		}
		( item.pages || [] ).forEach( function ( page ) {
			chips.push( el( 'span', { key: 'pg' + page, className: 'ffw-mgr-chip is-soft' }, page ) );
		} );
		( item.badges || [] ).forEach( function ( badge, index ) {
			chips.push( el( 'span', { key: 'b' + index, className: 'ffw-mgr-chip is-soft' }, String( badge ) ) );
		} );
		if ( ! chips.length ) {
			return el( 'span', { className: 'ffw-mgr-muted', title: __( 'Use a shortcode, block or FAQ page to show it, or assign products in the editor.', D ) }, __( 'Shortcodes and blocks', D ) );
		}
		return el( 'div', { className: 'ffw-mgr-chips' }, chips );
	}

	function Helpful( props ) {
		var votes = props.item.yes + props.item.no;
		if ( ! votes ) {
			return el( 'span', { className: 'ffw-mgr-muted' }, '—' );
		}
		var rate = Math.round( ( props.item.yes / votes ) * 100 );
		return el( 'span', { className: cx( 'ffw-mgr-rate', rate >= 70 ? 'is-good' : rate < 50 ? 'is-bad' : '' ), title: sprintf( _n( '%s vote', '%s votes', votes, D ), number( votes ) ) }, rate + '%' );
	}

	function QuickEdit( props ) {
		var item = props.item;
		var title = useState( item.title );
		var status = useState( item.status );
		var cats = useState( item.categories.map( function ( c ) {
			return c.id;
		} ) );
		var order = useState( item.order );
		var busy = useState( false );

		function save( event ) {
			event.preventDefault();
			busy[ 1 ]( true );
			api( '/faqs/' + item.id, { method: 'POST', data: { title: title[ 0 ], status: status[ 0 ], categories: cats[ 0 ], order: order[ 0 ] } } )
				.then( function ( data ) {
					toast( __( 'FAQ updated.', D ) );
					props.onSaved( data.item );
				} )
				.catch( function ( error ) {
					busy[ 1 ]( false );
					toast( errorText( error ), 'error' );
				} );
		}

		return el(
			'tr',
			{ className: 'ffw-mgr-quickedit' },
			el(
				'td',
				{ colSpan: props.columns },
				el(
					'form',
					{ onSubmit: save, onKeyDown: function ( event ) {
						if ( 'Escape' === event.key ) {
							props.onCancel();
						}
					} },
					el( 'p', { className: 'ffw-mgr-eyebrow' }, __( 'Quick edit', D ) ),
					el(
						'div',
						{ className: 'ffw-mgr-qe-grid' },
						el(
							'div',
							null,
							el( 'label', { className: 'ffw-mgr-label', htmlFor: 'ffw-qe-title-' + item.id }, __( 'Question', D ) ),
							el( 'input', { id: 'ffw-qe-title-' + item.id, className: 'ffw-mgr-input', value: title[ 0 ], autoFocus: true, onChange: function ( event ) {
								title[ 1 ]( event.target.value );
							} } ),
							el(
								'div',
								{ className: 'ffw-mgr-qe-row' },
								el(
									'label',
									null,
									el( 'span', { className: 'ffw-mgr-label' }, __( 'Status', D ) ),
									el(
										'select',
										{ className: 'ffw-mgr-input', value: status[ 0 ], onChange: function ( event ) {
											status[ 1 ]( event.target.value );
										} },
										el( 'option', { value: 'publish' }, __( 'Published', D ) ),
										el( 'option', { value: 'draft' }, __( 'Draft', D ) ),
										el( 'option', { value: 'pending' }, __( 'Pending review', D ) ),
										el( 'option', { value: 'private' }, __( 'Private', D ) )
									)
								),
								el(
									'label',
									null,
									el( 'span', { className: 'ffw-mgr-label' }, __( 'Order', D ) ),
									el( 'input', { type: 'number', className: 'ffw-mgr-input is-number', value: order[ 0 ], onChange: function ( event ) {
										order[ 1 ]( event.target.value );
									} } )
								)
							),
							el( 'p', { className: 'ffw-mgr-help' }, __( 'Products, store pages and Pro settings are in the full editor.', D ) )
						),
						el(
							'div',
							null,
							el( 'span', { className: 'ffw-mgr-label' }, __( 'FAQ categories', D ) ),
							el( CategoryChecklist, { categories: props.categories, value: cats[ 0 ], onChange: cats[ 1 ] } )
						)
					),
					el(
						'div',
						{ className: 'ffw-mgr-actions' },
						el( Button, { variant: 'primary', type: 'submit', busy: busy[ 0 ] }, __( 'Save', D ) ),
						el( Button, { onClick: props.onCancel }, __( 'Cancel', D ) ),
						el( 'a', { className: 'ffw-mgr-link', href: '#/edit/' + item.id }, __( 'Open in editor', D ) )
					)
				)
			)
		);
	}

	function QuickAdd( props ) {
		var title = useState( '' );
		var answer = useState( '' );
		var cats = useState( [] );
		var status = useState( config.canPublish ? 'publish' : 'pending' );
		var busy = useState( false );
		var titleRef = useRef( null );

		function submit( event ) {
			event.preventDefault();
			if ( ! title[ 0 ].trim() ) {
				titleRef.current.focus();
				return;
			}
			busy[ 1 ]( true );
			api( '/faqs', { method: 'POST', data: { title: title[ 0 ], content: answer[ 0 ], categories: cats[ 0 ], status: status[ 0 ] } } )
				.then( function ( data ) {
					toast( sprintf( __( 'Added “%s”.', D ), data.item.title ) );
					title[ 1 ]( '' );
					answer[ 1 ]( '' );
					busy[ 1 ]( false );
					titleRef.current.focus();
					props.onAdded( data.item );
				} )
				.catch( function ( error ) {
					busy[ 1 ]( false );
					toast( errorText( error ), 'error' );
				} );
		}

		return el(
			'form',
			{ className: 'ffw-mgr-quickadd', onSubmit: submit },
			el(
				'div',
				{ className: 'ffw-mgr-quickadd-head' },
				el( 'div', null, el( 'h3', null, __( 'Quick add', D ) ), el( 'p', { className: 'ffw-mgr-muted' }, __( 'Add FAQs one after another. Press Enter in the question to jump to the answer.', D ) ) ),
				el( Button, { variant: 'tertiary', icon: 'close', label: __( 'Close quick add', D ), onClick: props.onClose } )
			),
			el(
				'div',
				{ className: 'ffw-mgr-quickadd-grid' },
				el(
					'div',
					null,
					el( 'label', { className: 'ffw-mgr-label', htmlFor: 'ffw-qa-title' }, __( 'Question', D ) ),
					el( 'input', {
						id: 'ffw-qa-title',
						ref: titleRef,
						className: 'ffw-mgr-input',
						value: title[ 0 ],
						autoFocus: true,
						placeholder: __( 'How long does shipping take?', D ),
						onChange: function ( event ) {
							title[ 1 ]( event.target.value );
						},
						onKeyDown: function ( event ) {
							if ( 'Enter' === event.key ) {
								event.preventDefault();
								document.getElementById( 'ffw-qa-answer' ).focus();
							}
						}
					} ),
					el( 'label', { className: 'ffw-mgr-label', htmlFor: 'ffw-qa-answer' }, __( 'Answer', D ) ),
					el( 'textarea', { id: 'ffw-qa-answer', className: 'ffw-mgr-input', rows: 4, value: answer[ 0 ], placeholder: __( 'Orders ship within 2 business days.', D ), onChange: function ( event ) {
						answer[ 1 ]( event.target.value );
					}, onKeyDown: function ( event ) {
						if ( 'Enter' === event.key && ( event.metaKey || event.ctrlKey ) ) {
							submit( event );
						}
					} } ),
					el( 'p', { className: 'ffw-mgr-help' }, __( 'Blank lines start new paragraphs. Ctrl/⌘ + Enter adds the FAQ.', D ) )
				),
				el(
					'div',
					null,
					el( 'span', { className: 'ffw-mgr-label' }, __( 'FAQ categories', D ) ),
					el( CategoryChecklist, { categories: props.categories, value: cats[ 0 ], onChange: cats[ 1 ] } ),
					el(
						'label',
						{ className: 'ffw-mgr-inline' },
						el( 'span', { className: 'ffw-mgr-label' }, __( 'Status', D ) ),
						el(
							'select',
							{ className: 'ffw-mgr-input', value: status[ 0 ], onChange: function ( event ) {
								status[ 1 ]( event.target.value );
							} },
							config.canPublish ? el( 'option', { value: 'publish' }, __( 'Publish', D ) ) : el( 'option', { value: 'pending' }, __( 'Submit for review', D ) ),
							el( 'option', { value: 'draft' }, __( 'Save as draft', D ) )
						)
					)
				)
			),
			el( 'div', { className: 'ffw-mgr-actions' }, el( Button, { variant: 'primary', type: 'submit', icon: 'plus', busy: busy[ 0 ] }, __( 'Add FAQ', D ) ) )
		);
	}

	function EmptyState( props ) {
		return el(
			'div',
			{ className: 'ffw-mgr-empty' },
			el( 'div', { className: 'ffw-mgr-empty-icon' }, el( Icon, { name: props.icon || 'list', size: 28 } ) ),
			el( 'h3', null, props.title ),
			props.text ? el( 'p', null, props.text ) : null,
			props.actions ? el( 'div', { className: 'ffw-mgr-actions is-center' }, props.actions ) : null
		);
	}

	function ListView( props ) {
		var query = Object.assign( {}, props.route.query );
		delete query.trashed;
		var status = query.status || '';
		var category = Number( query.category || 0 );
		var sort = query.orderby ? query.orderby + ':' + ( query.order || 'desc' ) : '';
		var page = Number( query.page || 1 );

		var dataState = useState( null );
		var data = dataState[ 0 ];
		var setData = dataState[ 1 ];
		var loadingState = useState( true );
		var loading = loadingState[ 0 ];
		var setLoading = loadingState[ 1 ];
		var errorState = useState( '' );
		var selectedState = useState( [] );
		var selected = selectedState[ 0 ];
		var setSelected = selectedState[ 1 ];
		var editingState = useState( 0 );
		var editing = editingState[ 0 ];
		var setEditing = editingState[ 1 ];
		var addState = useState( false );
		var bulkState = useState( '' );
		var bulkCatState = useState( 0 );
		var bulkBusy = useState( false );
		var perPageState = useState( store.get( 'perPage', config.perPage ) );
		var perPage = perPageState[ 0 ];
		var searchState = useState( query.search || '' );
		var search = searchState[ 0 ];
		var setSearch = searchState[ 1 ];
		var cats = props.categories;
		var catList = ( cats && cats.items ) || [];
		var reloadKey = useState( 0 );
		var requestRef = useRef( 0 );

		function setQuery( changes ) {
			var next = Object.assign( {}, query, changes );
			if ( ! ( 'page' in changes ) ) {
				delete next.page;
			}
			navigate( 'list', next );
		}

		// Search box: update the route a moment after typing stops.
		useEffect( function () {
			if ( search === ( query.search || '' ) ) {
				return;
			}
			var timer = window.setTimeout( function () {
				setQuery( { search: search } );
			}, 350 );
			return function () {
				window.clearTimeout( timer );
			};
		}, [ search ] );

		useEffect( function () {
			setSearch( query.search || '' );
		}, [ query.search ] );

		var load = useCallback( function () {
			var request = ++requestRef.current;
			var order = sort ? sort.split( ':' ) : [];
			setLoading( true );
			errorState[ 1 ]( '' );
			return api( '/faqs', {
				query: {
					status: status,
					search: query.search || '',
					category: category,
					orderby: order[ 0 ] || '',
					order: order[ 1 ] || '',
					page: page,
					per_page: perPage
				}
			} )
				.then( function ( response ) {
					if ( request !== requestRef.current ) {
						return;
					}
					shared.counts = response.counts;
					shared.listStale = false;
					props.onCounts( response.counts );
					setData( response );
					setLoading( false );
					// Past the last page after deleting: go back one.
					if ( ! response.items.length && page > 1 && response.pages ) {
						setQuery( { page: response.pages } );
					}
				} )
				.catch( function ( error ) {
					if ( request === requestRef.current ) {
						setLoading( false );
						errorState[ 1 ]( errorText( error ) );
					}
				} );
		}, [ status, query.search, category, sort, page, perPage ] );

		useEffect( function () {
			setSelected( [] );
			setEditing( 0 );
			load();
		}, [ load, reloadKey[ 0 ] ] );

		function reload() {
			reloadKey[ 1 ]( function ( n ) {
				return n + 1;
			} );
		}

		function replaceItem( item ) {
			setData( function ( current ) {
				return current ? Object.assign( {}, current, { items: current.items.map( function ( entry ) {
					return entry.id === item.id ? item : entry;
				} ) } ) : current;
			} );
		}

		function runBulk( action, ids, extra ) {
			/* translators: %s: number of FAQs, e.g. "2 FAQs". */
			var labels = {
				publish: __( '%s published', D ),
				draft: __( '%s moved to drafts', D ),
				trash: __( '%s moved to Trash', D ),
				restore: __( '%s restored', D ),
				delete: __( '%s deleted permanently', D ),
				add_category: __( '%s added to the category', D ),
				remove_category: __( '%s removed from the category', D )
			};
			bulkBusy[ 1 ]( true );
			return api( '/faqs/bulk', { method: 'POST', data: Object.assign( { action: action, ids: ids }, extra || {} ) } )
				.then( function ( response ) {
					bulkBusy[ 1 ]( false );
					toast( sprintf( labels[ action ], sprintf( _n( '%s FAQ', '%s FAQs', response.done, D ), number( response.done ) ) ) + ( response.failed ? ' · ' + sprintf( __( '%s not allowed', D ), number( response.failed ) ) : '' ), response.failed && ! response.done ? 'error' : 'success' );
					bulkState[ 1 ]( '' );
					if ( 'add_category' === action || 'remove_category' === action ) {
						props.reloadCategories();
					}
					reload();
				} )
				.catch( function ( error ) {
					bulkBusy[ 1 ]( false );
					toast( errorText( error ), 'error' );
				} );
		}

		function applyBulk() {
			var action = bulkState[ 0 ];
			if ( ! action || ! selected.length ) {
				return;
			}
			if ( ( 'add_category' === action || 'remove_category' === action ) && ! bulkCatState[ 0 ] ) {
				toast( __( 'Choose a category.', D ), 'error' );
				return;
			}
			if ( 'delete' === action ) {
				confirmDialog( {
					title: __( 'Delete permanently?', D ),
					message: sprintf( _n( '%s FAQ will be deleted for good. This cannot be undone.', '%s FAQs will be deleted for good. This cannot be undone.', selected.length, D ), number( selected.length ) ),
					confirm: __( 'Delete permanently', D ),
					danger: true
				} ).then( function ( yes ) {
					if ( yes ) {
						runBulk( action, selected );
					}
				} );
				return;
			}
			runBulk( action, selected, { category: bulkCatState[ 0 ] } );
		}

		function rowAction( action, item ) {
			if ( 'duplicate' === action ) {
				api( '/faqs/' + item.id + '/duplicate', { method: 'POST' } )
					.then( function ( response ) {
						toast( sprintf( __( 'Draft copy created: “%s”.', D ), response.item.title ) );
						reload();
					} )
					.catch( function ( error ) {
						toast( errorText( error ), 'error' );
					} );
			} else if ( 'shortcode' === action ) {
				copyText( '[ffw_template faq_ids="' + item.id + '"]' ).then( function () {
					toast( __( 'Shortcode copied.', D ) );
				} );
			} else if ( 'delete' === action ) {
				confirmDialog( {
					title: __( 'Delete permanently?', D ),
					message: sprintf( __( '“%s” will be deleted for good. This cannot be undone.', D ), item.title ),
					confirm: __( 'Delete permanently', D ),
					danger: true
				} ).then( function ( yes ) {
					if ( yes ) {
						runBulk( 'delete', [ item.id ] );
					}
				} );
			} else {
				runBulk( action, [ item.id ] );
			}
		}

		var counts = ( data && data.counts ) || shared.counts || {};
		var statusTabs = [
			{ value: '', label: __( 'All', D ), count: counts.all },
			{ value: 'publish', label: __( 'Published', D ), count: counts.publish },
			{ value: 'draft', label: __( 'Drafts', D ), count: counts.draft },
			{ value: 'pending', label: __( 'Pending', D ), count: counts.pending },
			{ value: 'private', label: __( 'Private', D ), count: counts.private },
			{ value: 'future', label: __( 'Scheduled', D ), count: counts.future },
			{ value: 'trash', label: __( 'Trash', D ), count: counts.trash }
		].filter( function ( tab ) {
			return ! tab.value || tab.count || tab.value === status;
		} );

		var items = ( data && data.items ) || [];
		var allChecked = items.length > 0 && items.every( function ( item ) {
			return selected.indexOf( item.id ) !== -1;
		} );
		var inTrash = 'trash' === status;
		var filtered = !! ( query.search || category || status );
		var columns = config.votes ? 7 : 6;

		var bulkOptions = inTrash
			? [ [ 'restore', __( 'Restore', D ) ], [ 'delete', __( 'Delete permanently', D ) ] ]
			: [ [ 'publish', __( 'Publish', D ) ], [ 'draft', __( 'Move to drafts', D ) ], [ 'add_category', __( 'Add to category…', D ) ], [ 'remove_category', __( 'Remove from category…', D ) ], [ 'trash', __( 'Move to Trash', D ) ] ];

		return el(
			'div',
			{ className: 'ffw-mgr-panel' },
			el(
				'div',
				{ className: 'ffw-mgr-toolbar' },
				el(
					'nav',
					{ className: 'ffw-mgr-statuses', 'aria-label': __( 'Filter by status', D ) },
					statusTabs.map( function ( tab ) {
						return el(
							'button',
							{
								key: tab.value || 'all',
								type: 'button',
								className: cx( 'ffw-mgr-status', tab.value === status && 'is-active' ),
								'aria-current': tab.value === status ? 'true' : undefined,
								onClick: function () {
									setQuery( { status: tab.value } );
								}
							},
							tab.label,
							el( 'span', { className: 'ffw-mgr-count' }, number( tab.count ) )
						);
					} )
				),
				el(
					'div',
					{ className: 'ffw-mgr-toolbar-end' },
					el(
						'div',
						{ className: 'ffw-mgr-search' },
						el( Icon, { name: 'search' } ),
						el( 'input', { type: 'search', className: 'ffw-mgr-input', placeholder: __( 'Search questions and answers', D ), 'aria-label': __( 'Search FAQs', D ), value: search, onChange: function ( event ) {
							setSearch( event.target.value );
						} } )
					),
					inTrash ? null : el( Button, { icon: 'plus', pressed: addState[ 0 ], onClick: function () {
						addState[ 1 ]( ! addState[ 0 ] );
					} }, __( 'Quick add', D ) )
				)
			),
			el(
				'div',
				{ className: 'ffw-mgr-filters' },
				el( CategorySelect, { categories: catList, value: category, label: __( 'Filter by category', D ), allLabel: __( 'All categories', D ), uncategorized: true, className: 'ffw-mgr-input is-select', onChange: function ( value ) {
					setQuery( { category: value } );
				} } ),
				el(
					'select',
					{ className: 'ffw-mgr-input is-select', value: sort, 'aria-label': __( 'Sort', D ), onChange: function ( event ) {
						var parts = event.target.value.split( ':' );
						setQuery( { orderby: parts[ 0 ] || '', order: parts[ 1 ] || '' } );
					} },
					SORTS.filter( function ( option ) {
						return config.votes || 'helpful:desc' !== option.value;
					} ).map( function ( option ) {
						return el( 'option', { key: option.value, value: option.value }, option.label );
					} )
				),
				filtered ? el( Button, { variant: 'tertiary', small: true, onClick: function () {
					setSearch( '' );
					navigate( 'list', {} );
				} }, __( 'Clear filters', D ) ) : null,
				el( 'span', { className: 'ffw-mgr-total' }, data ? sprintf( _n( '%s FAQ', '%s FAQs', data.total, D ), number( data.total ) ) : '' )
			),
			addState[ 0 ] && ! inTrash ? el( QuickAdd, {
				categories: catList,
				onClose: function () {
					addState[ 1 ]( false );
				},
				onAdded: function () {
					props.reloadCategories();
					reload();
				}
			} ) : null,
			selected.length ? el(
				'div',
				{ className: 'ffw-mgr-bulk', role: 'region', 'aria-label': __( 'Bulk actions', D ) },
				el( 'strong', null, sprintf( _n( '%s selected', '%s selected', selected.length, D ), number( selected.length ) ) ),
				el(
					'select',
					{ className: 'ffw-mgr-input is-select', value: bulkState[ 0 ], 'aria-label': __( 'Bulk action', D ), onChange: function ( event ) {
						bulkState[ 1 ]( event.target.value );
					} },
					el( 'option', { value: '' }, __( 'Choose an action', D ) ),
					bulkOptions.map( function ( option ) {
						return el( 'option', { key: option[ 0 ], value: option[ 0 ] }, option[ 1 ] );
					} )
				),
				'add_category' === bulkState[ 0 ] || 'remove_category' === bulkState[ 0 ] ? el( CategorySelect, { categories: catList, value: bulkCatState[ 0 ], allLabel: __( 'Choose a category', D ), label: __( 'Category', D ), className: 'ffw-mgr-input is-select', onChange: bulkCatState[ 1 ] } ) : null,
				el( Button, { variant: 'delete' === bulkState[ 0 ] ? 'danger' : 'primary', disabled: ! bulkState[ 0 ], busy: bulkBusy[ 0 ], onClick: applyBulk }, __( 'Apply', D ) ),
				el( Button, { variant: 'tertiary', onClick: function () {
					setSelected( [] );
				} }, __( 'Clear selection', D ) )
			) : null,
			errorState[ 0 ] ? el( 'div', { className: 'ffw-mgr-notice is-error' }, errorState[ 0 ], ' ', el( Button, { small: true, onClick: reload }, __( 'Try again', D ) ) ) : null,
			! data && loading ? el( Spinner, { text: __( 'Loading FAQs…', D ) } ) : null,
			data && ! items.length && ! loading
				? filtered
					? el( EmptyState, { icon: 'search', title: inTrash ? __( 'Trash is empty', D ) : __( 'No FAQs match', D ), text: inTrash ? '' : __( 'Try another search or category.', D ), actions: el( Button, { onClick: function () {
						setSearch( '' );
						navigate( 'list', {} );
					} }, __( 'Clear filters', D ) ) } )
					: el( EmptyState, { icon: 'sparkle', title: __( 'Add your first FAQ', D ), text: __( 'Write questions and answers once, then show them on pages, posts and products.', D ), actions: el( Fragment, null, el( Button, { variant: 'primary', icon: 'plus', onClick: function () {
						navigate( 'editor', {}, 0 );
					} }, __( 'Add New FAQ', D ) ), el( Button, { onClick: function () {
						addState[ 1 ]( true );
					} }, __( 'Quick add', D ) ) ) } )
				: null,
			items.length ? el(
				'div',
				{ className: cx( 'ffw-mgr-table-wrap', loading && 'is-loading' ) },
				el(
					'table',
					{ className: 'ffw-mgr-table' },
					el(
						'thead',
						null,
						el(
							'tr',
							null,
							el( 'th', { className: 'is-check' }, el( 'input', { type: 'checkbox', 'aria-label': __( 'Select all', D ), checked: allChecked, onChange: function () {
								setSelected( allChecked ? [] : items.map( function ( item ) {
									return item.id;
								} ) );
							} } ) ),
							el( 'th', null, __( 'Question', D ) ),
							el( 'th', { className: 'is-where' }, __( 'Shown on', D ) ),
							el( 'th', null, __( 'Status', D ) ),
							el( 'th', { className: 'is-num' }, __( 'Views', D ) ),
							config.votes ? el( 'th', { className: 'is-num' }, __( 'Helpful', D ) ) : null,
							el( 'th', null, __( 'Date', D ) )
						)
					),
					el(
						'tbody',
						null,
						items.map( function ( item ) {
							if ( editing === item.id ) {
								return el( QuickEdit, {
									key: item.id,
									item: item,
									columns: columns,
									categories: catList,
									onCancel: function () {
										setEditing( 0 );
									},
									onSaved: function ( updated ) {
										setEditing( 0 );
										props.reloadCategories();
										if ( updated.status !== item.status && status ) {
											reload();
										} else {
											replaceItem( updated );
										}
									}
								} );
							}
							var checked = selected.indexOf( item.id ) !== -1;
							return el(
								'tr',
								{ key: item.id, className: cx( checked && 'is-selected' ) },
								el( 'td', { className: 'is-check' }, el( 'input', { type: 'checkbox', checked: checked, 'aria-label': sprintf( __( 'Select “%s”', D ), item.title ), onChange: function () {
									setSelected( checked ? selected.filter( function ( id ) {
										return id !== item.id;
									} ) : selected.concat( [ item.id ] ) );
								} } ) ),
								el(
									'td',
									{ className: 'is-main' },
									inTrash || ! item.canEdit
										? el( 'strong', { className: 'ffw-mgr-title' }, item.title || __( '(no question)', D ) )
										: el( 'a', { className: 'ffw-mgr-title', href: '#/edit/' + item.id }, item.title || __( '(no question)', D ) ),
									item.excerpt ? el( 'p', { className: 'ffw-mgr-excerpt' }, item.excerpt ) : null,
									item.categories.length ? el(
										'div',
										{ className: 'ffw-mgr-cats' },
										item.categories.map( function ( cat ) {
											return el( 'button', { key: cat.id, type: 'button', className: 'ffw-mgr-cat', title: __( 'Show FAQs in this category', D ), onClick: function () {
												setQuery( { category: cat.id } );
											} }, el( Icon, { name: 'folder', size: 12 } ), cat.name );
										} )
									) : null,
									el(
										'div',
										{ className: 'ffw-mgr-row-actions' },
										inTrash
											? el( Fragment, null,
												item.canDelete ? el( 'button', { type: 'button', onClick: function () {
													rowAction( 'restore', item );
												} }, __( 'Restore', D ) ) : null,
												item.canDelete ? el( 'button', { type: 'button', className: 'is-danger', onClick: function () {
													rowAction( 'delete', item );
												} }, __( 'Delete permanently', D ) ) : null )
											: el( Fragment, null,
												item.canEdit ? el( 'a', { href: '#/edit/' + item.id }, __( 'Edit', D ) ) : null,
												item.canEdit ? el( 'button', { type: 'button', onClick: function () {
													setEditing( item.id );
												} }, __( 'Quick edit', D ) ) : null,
												el( 'button', { type: 'button', onClick: function () {
													rowAction( 'duplicate', item );
												} }, __( 'Duplicate', D ) ),
												el( 'button', { type: 'button', onClick: function () {
													rowAction( 'shortcode', item );
												} }, __( 'Copy shortcode', D ) ),
												item.canDelete ? el( 'button', { type: 'button', className: 'is-danger', onClick: function () {
													rowAction( 'trash', item );
												} }, __( 'Trash', D ) ) : null )
									)
								),
								el( 'td', { className: 'is-where' }, el( Placements, { item: item } ) ),
								el( 'td', null, el( StatusPill, { status: item.status, label: item.statusLabel } ) ),
								el( 'td', { className: 'is-num' }, item.views ? number( item.views ) : el( 'span', { className: 'ffw-mgr-muted' }, '0' ) ),
								config.votes ? el( 'td', { className: 'is-num' }, el( Helpful, { item: item } ) ) : null,
								el( 'td', { className: 'is-date' }, el( 'span', null, item.date ), el( 'span', { className: 'ffw-mgr-muted' }, sprintf( __( 'Updated %s ago', D ), item.modified ) ) )
							);
						} )
					)
				)
			) : null,
			data && data.pages > 1 ? el(
				'div',
				{ className: 'ffw-mgr-pagination' },
				el( Button, { small: true, disabled: page <= 1, onClick: function () {
					setQuery( { page: page - 1 } );
				} }, __( 'Previous', D ) ),
				el( 'span', null, sprintf( __( 'Page %1$s of %2$s', D ), number( page ), number( data.pages ) ) ),
				el( Button, { small: true, disabled: page >= data.pages, onClick: function () {
					setQuery( { page: page + 1 } );
				} }, __( 'Next', D ) ),
				el(
					'select',
					{ className: 'ffw-mgr-input is-select is-small', value: perPage, 'aria-label': __( 'FAQs per page', D ), onChange: function ( event ) {
						var value = Number( event.target.value );
						store.set( 'perPage', value );
						perPageState[ 1 ]( value );
						setQuery( { page: 1 } );
					} },
					[ 20, 50, 100 ].map( function ( n ) {
						return el( 'option', { key: n, value: n }, sprintf( __( '%s per page', D ), n ) );
					} )
				)
			) : null
		);
	}

	/* ------------------------------------------------------------------
	 * Add New / Edit: WordPress's FAQ editor, embedded.
	 * ------------------------------------------------------------------ */

	function EditorView( props ) {
		var id = props.route.id;
		var frameRef = useRef( null );
		var currentId = useRef( id );
		var srcState = useState( function () {
			return id ? wp.url.addQueryArgs( config.editUrl, { post: id } ) : config.newUrl;
		} );
		var loadingState = useState( true );
		var heightState = useState( 640 );
		var titleState = useState( '' );

		// A different FAQ (not the one we just saved) loads a new editor.
		useEffect( function () {
			if ( id !== currentId.current ) {
				currentId.current = id;
				loadingState[ 1 ]( true );
				srcState[ 1 ]( id ? wp.url.addQueryArgs( config.editUrl, { post: id } ) : config.newUrl + '&t=' + Date.now() );
			}
		}, [ id ] );

		// Bring the editor to the top of the window, then fill the window with it.
		// Also once the frame loads: the page's load event can reset the scroll.
		function scrollIntoPlace() {
			var bar = document.querySelector( '.ffw-mgr-head' );
			if ( bar ) {
				window.scrollTo( 0, Math.max( 0, window.pageYOffset + bar.getBoundingClientRect().top - 46 ) );
			}
		}

		useEffect( function () {
			var timer = window.setTimeout( scrollIntoPlace, 60 );
			return function () {
				window.clearTimeout( timer );
			};
		}, [ id ] );

		useEffect( function () {
			function size() {
				var frame = frameRef.current;
				if ( frame ) {
					// The whole window, less the admin bar and the editor bar above the frame.
					heightState[ 1 ]( Math.max( 560, window.innerHeight - 110 ) );
				}
			}
			size();
			window.addEventListener( 'resize', size );
			return function () {
				window.removeEventListener( 'resize', size );
			};
		}, [] );

		// The editor tells us about unsaved changes (it may run isolated, so we can't look inside).
		var dirtyRef = useRef( false );

		function isDirty() {
			return dirtyRef.current;
		}

		// Ask before leaving with unsaved changes.
		useEffect( function () {
			leaveGuard = function () {
				return ! isDirty() || window.confirm( __( 'This FAQ has unsaved changes. Leave without saving?', D ) );
			};
			function beforeUnload( event ) {
				if ( isDirty() ) {
					event.preventDefault();
					event.returnValue = '';
				}
			}
			window.addEventListener( 'beforeunload', beforeUnload );
			return function () {
				leaveGuard = null;
				window.removeEventListener( 'beforeunload', beforeUnload );
			};
		}, [] );

		// Messages from the editor.
		useEffect( function () {
			function onMessage( event ) {
				if ( event.origin !== window.location.origin || ! event.data || 'ffw-faq-editor' !== event.data.source ) {
					return;
				}
				if ( event.source !== ( frameRef.current && frameRef.current.contentWindow ) ) {
					return;
				}
				if ( 'state' === event.data.type || 'ready' === event.data.type ) {
					dirtyRef.current = !! event.data.dirty;
					titleState[ 1 ]( event.data.title || '' );
					loadingState[ 1 ]( false );
				}
				if ( 'left' === event.data.type ) {
					// The editor went elsewhere, for example after Move to Trash.
					leaveGuard = null;
					dirtyRef.current = false;
					shared.listStale = true;
					toast( event.data.trashed ? __( 'FAQ moved to Trash.', D ) : __( 'Back to all FAQs.', D ) );
					navigate( 'list', props.lastListQuery || {} );
					return;
				}
				if ( 'saved' === event.data.type && event.data.id ) {
					dirtyRef.current = false;
					shared.listStale = true;
					props.reloadCategories();
					toast( __( 'FAQ saved.', D ) );
					if ( event.data.id !== currentId.current ) {
						// A new FAQ got its ID: show it as an edit without reloading the editor.
						currentId.current = event.data.id;
						navigate( 'editor', {}, event.data.id, true );
					}
				}
			}
			window.addEventListener( 'message', onMessage );
			return function () {
				window.removeEventListener( 'message', onMessage );
			};
		}, [] );

		function onLoad() {
			loadingState[ 1 ]( false );
			window.setTimeout( scrollIntoPlace, 30 );
		}

		var fullUrl = id ? wp.url.addQueryArgs( config.fullEditUrl, { post: id } ) : config.adminUrl + 'post-new.php?post_type=ffw&ffw_classic=1';

		return el(
			'div',
			{ className: 'ffw-mgr-panel is-editor' },
			el(
				'div',
				{ className: 'ffw-mgr-editor-bar' },
				el( Button, { variant: 'tertiary', icon: 'back', onClick: function () {
					navigate( 'list', props.lastListQuery || {} );
				} }, __( 'All FAQs', D ) ),
				el( 'strong', { className: 'ffw-mgr-editor-title' }, id ? __( 'Edit FAQ', D ) : __( 'Add New FAQ', D ) ),
				el( 'span', { className: 'ffw-mgr-muted ffw-mgr-editor-hint' }, __( 'Products, categories, store pages and every FAQ setting are in the Happy FAQs Panel below the answer.', D ) ),
				el(
					'span',
					{ className: 'ffw-mgr-editor-end' },
					id ? el( Button, { variant: 'tertiary', icon: 'plus', onClick: function () {
						navigate( 'editor', {}, 0 );
					} }, __( 'Add another', D ) ) : null,
					el( 'a', { className: 'ffw-mgr-btn is-tertiary', href: fullUrl, target: '_blank', rel: 'noopener' }, el( Icon, { name: 'external' } ), __( 'Open in a new tab', D ) )
				)
			),
			loadingState[ 0 ] ? el( 'div', { className: 'ffw-mgr-editor-loading' }, el( Spinner, { text: __( 'Opening the editor…', D ) } ) ) : null,
			el( 'iframe', {
				ref: frameRef,
				src: srcState[ 0 ],
				title: id ? __( 'Edit FAQ', D ) : __( 'Add New FAQ', D ),
				className: cx( 'ffw-mgr-frame', loadingState[ 0 ] && 'is-loading' ),
				style: { height: heightState[ 0 ] + 'px' },
				onLoad: onLoad
			} )
		);
	}

	/* ------------------------------------------------------------------
	 * Categories.
	 * ------------------------------------------------------------------ */

	function CategoryForm( props ) {
		var cat = props.category || {};
		var name = useState( cat.name || '' );
		var slug = useState( cat.slug || '' );
		var parent = useState( cat.parent || 0 );
		var description = useState( cat.description || '' );
		var busy = useState( false );
		var more = useState( !! cat.id );
		var all = props.categories || [];

		// Not itself or one of its own sub-categories.
		function isOwnBranch( candidate ) {
			if ( ! cat.id ) {
				return false;
			}
			var current = candidate;
			while ( current ) {
				if ( current.id === cat.id ) {
					return true;
				}
				current = all.filter( function ( c ) {
					return c.id === current.parent;
				} )[ 0 ];
			}
			return false;
		}

		function submit( event ) {
			event.preventDefault();
			if ( ! name[ 0 ].trim() ) {
				return;
			}
			busy[ 1 ]( true );
			api( '/categories' + ( cat.id ? '/' + cat.id : '' ), { method: 'POST', data: { name: name[ 0 ], slug: slug[ 0 ], parent: parent[ 0 ], description: description[ 0 ] } } )
				.then( function ( response ) {
					busy[ 1 ]( false );
					toast( cat.id ? __( 'Category updated.', D ) : sprintf( __( 'Added “%s”.', D ), name[ 0 ] ) );
					if ( ! cat.id ) {
						name[ 1 ]( '' );
						slug[ 1 ]( '' );
						description[ 1 ]( '' );
					}
					props.onSaved( response );
				} )
				.catch( function ( error ) {
					busy[ 1 ]( false );
					toast( errorText( error ), 'error' );
				} );
		}

		return el(
			'form',
			{ className: 'ffw-mgr-cat-form', onSubmit: submit, onKeyDown: function ( event ) {
				if ( 'Escape' === event.key && props.onCancel ) {
					props.onCancel();
				}
			} },
			el( 'label', { className: 'ffw-mgr-label', htmlFor: 'ffw-cat-name-' + ( cat.id || 'new' ) }, __( 'Name', D ) ),
			el( 'input', { id: 'ffw-cat-name-' + ( cat.id || 'new' ), className: 'ffw-mgr-input', value: name[ 0 ], required: true, autoFocus: !! cat.id, placeholder: cat.id ? '' : __( 'Shipping', D ), onChange: function ( event ) {
				name[ 1 ]( event.target.value );
			} } ),
			el( 'label', { className: 'ffw-mgr-label', htmlFor: 'ffw-cat-parent-' + ( cat.id || 'new' ) }, __( 'Parent category', D ) ),
			el(
				'select',
				{ id: 'ffw-cat-parent-' + ( cat.id || 'new' ), className: 'ffw-mgr-input', value: parent[ 0 ], onChange: function ( event ) {
					parent[ 1 ]( Number( event.target.value ) );
				} },
				el( 'option', { value: 0 }, __( 'None (top level)', D ) ),
				all.filter( function ( c ) {
					return ! isOwnBranch( c );
				} ).map( function ( c ) {
					return el( 'option', { key: c.id, value: c.id }, ' '.repeat( c.depth ) + c.name );
				} )
			),
			more[ 0 ] ? el(
				Fragment,
				null,
				el( 'label', { className: 'ffw-mgr-label', htmlFor: 'ffw-cat-desc-' + ( cat.id || 'new' ) }, __( 'Description', D ) ),
				el( 'textarea', { id: 'ffw-cat-desc-' + ( cat.id || 'new' ), className: 'ffw-mgr-input', rows: 3, value: description[ 0 ], onChange: function ( event ) {
					description[ 1 ]( event.target.value );
				} } ),
				el( 'label', { className: 'ffw-mgr-label', htmlFor: 'ffw-cat-slug-' + ( cat.id || 'new' ) }, __( 'Slug', D ) ),
				el( 'input', { id: 'ffw-cat-slug-' + ( cat.id || 'new' ), className: 'ffw-mgr-input', value: slug[ 0 ], placeholder: __( 'Made from the name', D ), onChange: function ( event ) {
					slug[ 1 ]( event.target.value );
				} } )
			) : el( 'button', { type: 'button', className: 'ffw-mgr-link', onClick: function () {
				more[ 1 ]( true );
			} }, __( 'Add a description or slug', D ) ),
			el(
				'div',
				{ className: 'ffw-mgr-actions' },
				el( Button, { variant: 'primary', type: 'submit', busy: busy[ 0 ], icon: cat.id ? null : 'plus' }, cat.id ? __( 'Save', D ) : __( 'Add category', D ) ),
				props.onCancel ? el( Button, { onClick: props.onCancel }, __( 'Cancel', D ) ) : null
			)
		);
	}

	function CategoriesView( props ) {
		var cats = props.categories;
		var items = ( cats && cats.items ) || [];
		var editing = useState( 0 );
		var search = useState( '' );

		function saved( response ) {
			props.setCategories( { items: response.items, uncategorized: cats ? cats.uncategorized : 0 } );
			editing[ 1 ]( 0 );
			props.reloadCategories();
		}

		function remove( cat ) {
			confirmDialog( {
				title: sprintf( __( 'Delete “%s”?', D ), cat.name ),
				message: cat.count
					? sprintf( _n( 'Its %s FAQ stays, just without this category. Sub-categories move up one level.', 'Its %s FAQs stay, just without this category. Sub-categories move up one level.', cat.count, D ), number( cat.count ) )
					: __( 'No FAQs use it. Sub-categories move up one level.', D ),
				confirm: __( 'Delete category', D ),
				danger: true
			} ).then( function ( yes ) {
				if ( ! yes ) {
					return;
				}
				api( '/categories/' + cat.id, { method: 'DELETE' } )
					.then( function () {
						toast( __( 'Category deleted.', D ) );
						shared.listStale = true;
						props.reloadCategories();
					} )
					.catch( function ( error ) {
						toast( errorText( error ), 'error' );
					} );
			} );
		}

		var query = search[ 0 ].toLowerCase();
		var visible = items.filter( function ( cat ) {
			return ! query || ( cat.name + ' ' + cat.slug + ' ' + cat.description ).toLowerCase().indexOf( query ) !== -1;
		} );

		return el(
			'div',
			{ className: 'ffw-mgr-panel is-categories' },
			el(
				'div',
				{ className: 'ffw-mgr-cats-layout' },
				config.canCats ? el(
					'aside',
					{ className: 'ffw-mgr-card' },
					el( 'h3', null, __( 'Add a category', D ) ),
					el( 'p', { className: 'ffw-mgr-muted' }, __( 'Group FAQs by topic, then show a whole category with one shortcode or block.', D ) ),
					el( CategoryForm, { categories: items, onSaved: saved } )
				) : null,
				el(
					'section',
					{ className: 'ffw-mgr-card is-flush' },
					el(
						'div',
						{ className: 'ffw-mgr-card-head' },
						el( 'h3', null, sprintf( _n( '%s category', '%s categories', items.length, D ), number( items.length ) ) ),
						items.length > 6 ? el(
							'div',
							{ className: 'ffw-mgr-search' },
							el( Icon, { name: 'search' } ),
							el( 'input', { type: 'search', className: 'ffw-mgr-input', placeholder: __( 'Search categories', D ), value: search[ 0 ], onChange: function ( event ) {
								search[ 1 ]( event.target.value );
							} } )
						) : null
					),
					! cats ? el( Spinner, null ) : ! items.length
						? el( EmptyState, { icon: 'folder', title: __( 'No categories yet', D ), text: __( 'Add one on the left, for example Shipping, Returns or Payments.', D ) } )
						: el(
							'table',
							{ className: 'ffw-mgr-table is-cats' },
							el( 'thead', null, el( 'tr', null, el( 'th', null, __( 'Name', D ) ), el( 'th', { className: 'is-num' }, __( 'FAQs', D ) ), el( 'th', null, __( 'Shortcode', D ) ), el( 'th', { className: 'is-actions' }, el( 'span', { className: 'screen-reader-text' }, __( 'Actions', D ) ) ) ) ),
							el(
								'tbody',
								null,
								visible.map( function ( cat ) {
									if ( editing[ 0 ] === cat.id ) {
										return el( 'tr', { key: cat.id, className: 'ffw-mgr-quickedit' }, el( 'td', { colSpan: 4 }, el( 'p', { className: 'ffw-mgr-eyebrow' }, __( 'Edit category', D ) ), el( CategoryForm, { category: cat, categories: items, onSaved: saved, onCancel: function () {
											editing[ 1 ]( 0 );
										} } ) ) );
									}
									var shortcode = '[ffw_template faq_cat="' + cat.id + '"]';
									return el(
										'tr',
										{ key: cat.id },
										el(
											'td',
											{ className: 'is-main' },
											el( 'span', { className: 'ffw-mgr-cat-name', style: { paddingInlineStart: cat.depth * 18 + 'px' } }, cat.depth ? el( 'span', { className: 'ffw-mgr-muted', 'aria-hidden': 'true' }, '↳ ' ) : null, el( 'strong', null, cat.name ) ),
											cat.description ? el( 'p', { className: 'ffw-mgr-excerpt', style: { paddingInlineStart: cat.depth * 18 + 'px' } }, cat.description ) : null,
											el( 'span', { className: 'ffw-mgr-muted ffw-mgr-slug', style: { paddingInlineStart: cat.depth * 18 + 'px' } }, cat.slug )
										),
										el( 'td', { className: 'is-num' }, el( 'a', { href: buildHash( 'list', { category: cat.id } ), className: 'ffw-mgr-count-link', title: __( 'Show these FAQs', D ) }, number( cat.count ) ) ),
										el( 'td', null, el( 'button', { type: 'button', className: 'ffw-mgr-code', title: __( 'Copy shortcode', D ), onClick: function () {
											copyText( shortcode ).then( function () {
												toast( __( 'Shortcode copied.', D ) );
											} );
										} }, el( 'code', null, shortcode ), el( Icon, { name: 'copy', size: 14 } ) ) ),
										el(
											'td',
											{ className: 'is-actions' },
											config.canCats ? el( Button, { variant: 'tertiary', small: true, icon: 'edit', label: sprintf( __( 'Edit “%s”', D ), cat.name ), title: __( 'Edit', D ), onClick: function () {
												editing[ 1 ]( cat.id );
											} } ) : null,
											el( 'a', { className: 'ffw-mgr-btn is-tertiary is-small', href: config.termUrl + cat.id, title: __( 'All category settings', D ), 'aria-label': sprintf( __( 'All settings for “%s”', D ), cat.name ) }, el( Icon, { name: 'external' } ) ),
											config.canCats ? el( Button, { variant: 'tertiary', small: true, icon: 'trash', className: 'is-danger-text', label: sprintf( __( 'Delete “%s”', D ), cat.name ), title: __( 'Delete', D ), onClick: function () {
												remove( cat );
											} } ) : null
										)
									);
								} )
							)
						),
					cats && cats.uncategorized ? el( 'p', { className: 'ffw-mgr-card-foot' }, el( 'a', { href: buildHash( 'list', { category: -1 } ) }, sprintf( _n( '%s FAQ has no category', '%s FAQs have no category', cats.uncategorized, D ), number( cats.uncategorized ) ) ) ) : null
				)
			)
		);
	}

	/* ------------------------------------------------------------------
	 * App.
	 * ------------------------------------------------------------------ */

	function App() {
		var routeState = useState( parseRoute );
		var route = routeState[ 0 ];
		var countsState = useState( shared.counts );
		var categories = useCategories();
		var lastListQuery = useRef( {} );

		useEffect( function () {
			function onHash() {
				var next = parseRoute();
				routeState[ 1 ]( next );
			}
			window.addEventListener( 'hashchange', onHash );
			if ( ! window.location.hash ) {
				window.history.replaceState( null, '', '#/list' );
			}
			return function () {
				window.removeEventListener( 'hashchange', onHash );
			};
		}, [] );

		useEffect( function () {
			if ( 'list' === route.view ) {
				lastListQuery.current = route.query;
			}
			// Keep the page title in step with the tab.
			var titles = { list: __( 'All FAQs', D ), editor: route.id ? __( 'Edit FAQ', D ) : __( 'Add New FAQ', D ), categories: __( 'FAQ Categories', D ) };
			document.title = document.title.replace( /^[^‹]*‹/, titles[ route.view ] + ' ‹' );
		}, [ route ] );

		// Counts for the tab when not on the list.
		useEffect( function () {
			if ( ! shared.counts ) {
				api( '/faqs', { query: { per_page: 5 } } ).then( function ( data ) {
					shared.counts = data.counts;
					countsState[ 1 ]( data.counts );
				} ).catch( function () {} );
			}
		}, [] );

		var counts = countsState[ 0 ] || {};
		var cats = categories[ 0 ];
		var tabs = [
			{ view: 'list', label: __( 'All FAQs', D ), count: counts.all, icon: 'list' },
			{ view: 'editor', label: __( 'Add New', D ), icon: 'plus', isNew: true },
			{ view: 'categories', label: __( 'Categories', D ), count: cats ? cats.items.length : undefined, icon: 'folder' }
		];

		var content;
		if ( 'editor' === route.view ) {
			content = el( EditorView, { route: route, reloadCategories: categories[ 1 ], lastListQuery: lastListQuery.current } );
		} else if ( 'categories' === route.view ) {
			content = el( CategoriesView, { categories: cats, reloadCategories: categories[ 1 ], setCategories: categories[ 2 ] } );
		} else {
			content = el( ListView, { route: route, categories: cats, reloadCategories: categories[ 1 ], onCounts: countsState[ 1 ] } );
		}

		return el(
			Fragment,
			null,
			el(
				'header',
				{ className: 'ffw-mgr-head' },
				el( 'div', null, el( 'h2', { className: 'ffw-mgr-heading' }, __( 'FAQs', D ) ), el( 'p', { className: 'ffw-mgr-muted' }, __( 'Write, organize and find every FAQ in one place.', D ) ) ),
				el(
					'nav',
					{ className: 'ffw-mgr-tabs', role: 'tablist', 'aria-label': __( 'FAQs', D ) },
					tabs.map( function ( tab ) {
						// Editing a FAQ belongs to All FAQs; Add New is only for a new one.
						var active = 'list' === tab.view ? 'list' === route.view || ( 'editor' === route.view && route.id ) : 'editor' === tab.view ? 'editor' === route.view && ! route.id : tab.view === route.view;
						return el(
							'button',
							{
								key: tab.view,
								type: 'button',
								role: 'tab',
								'aria-selected': active ? 'true' : 'false',
								className: cx( 'ffw-mgr-tab', active && 'is-active' ),
								onClick: function () {
									if ( 'list' === tab.view ) {
										navigate( 'list', lastListQuery.current );
									} else if ( 'editor' === tab.view ) {
										navigate( 'editor', {}, 0 );
									} else {
										navigate( tab.view, {} );
									}
								}
							},
							el( Icon, { name: tab.icon } ),
							tab.label,
							undefined !== tab.count ? el( 'span', { className: 'ffw-mgr-count' }, number( tab.count ) ) : null
						);
					} )
				)
			),
			content,
			el(
				'p',
				{ className: 'ffw-mgr-foot' },
				__( 'Prefer the classic screens?', D ),
				' ',
				el( 'a', { href: config.classicList }, __( 'All FAQs', D ) ),
				' · ',
				el( 'a', { href: config.classicCats }, __( 'Categories', D ) )
			),
			el( Toasts, null ),
			el( ConfirmHost, null )
		);
	}

	function mount() {
		var root = document.getElementById( 'ffw-faq-manager' );
		if ( ! root ) {
			return;
		}
		// Loaded inside the editor frame: the editor left (for example Move to Trash).
		var inManager = false;
		try {
			inManager = window.parent !== window && /page=ffw-faqs/.test( window.parent.location.href || '' );
		} catch ( e ) {
			inManager = false;
		}
		if ( inManager ) {
			window.parent.postMessage( { source: 'ffw-faq-editor', type: 'left', trashed: /[?&]trashed=/.test( window.location.href ) || /trashed=/.test( window.location.hash ) }, window.location.origin );
			root.innerHTML = '';
			return;
		}
		if ( wp.element.createRoot ) {
			wp.element.createRoot( root ).render( el( App ) );
		} else {
			wp.element.render( el( App ), root );
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', mount );
	} else {
		mount();
	}
} )( window.wp, window.ffwManager );
