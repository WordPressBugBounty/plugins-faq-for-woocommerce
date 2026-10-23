/**
 * Happy FAQs → AI FAQs (React).
 *
 * Generate (free), Bulk for products and Fill gaps (Pro), on the same AJAX
 * actions as the previous screen.
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
	var engine = ( data.status && data.status.engine ) || 'none';
	var ready = 'none' !== engine;

	// admin-ajax with the AI nonce. Nested values become items[0][question].
	function ajax( action, fields ) {
		var body = new window.FormData();
		body.append( 'action', action );
		body.append( 'nonce', data.nonce );
		function add( key, value ) {
			if ( Array.isArray( value ) ) {
				value.forEach( function ( item, index ) {
					if ( item && 'object' === typeof item ) {
						Object.keys( item ).forEach( function ( sub ) {
							add( key + '[' + index + '][' + sub + ']', item[ sub ] );
						} );
					} else {
						body.append( key + '[]', item );
					}
				} );
				return;
			}
			if ( value === undefined || value === null ) {
				return;
			}
			body.append( key, true === value ? '1' : false === value ? '0' : value );
		}
		Object.keys( fields || {} ).forEach( function ( key ) {
			add( key, fields[ key ] );
		} );
		return window.fetch( data.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
			.then( function ( response ) {
				return response.json().catch( function () {
					throw new Error( __( 'The server sent an unexpected answer. Please try again.', D ) );
				} );
			} )
			.then( function ( result ) {
				if ( ! result || ! result.success ) {
					throw new Error( ( result && result.data && result.data.message ) || __( 'Something went wrong. Please try again.', D ) );
				}
				return result.data || {};
			} );
	}

	function LanguageSelect( props ) {
		return el( ui.Select, { label: __( 'Language', D ), value: props.value, options: data.languages || [], onChange: props.onChange } );
	}

	function Progress( props ) {
		var pct = props.total ? Math.round( 100 * props.done / props.total ) : 0;
		return el(
			'div',
			{ className: 'ffw-ai-progress' },
			el( 'div', { className: 'ffw-ai-progress-head' }, el( 'strong', null, props.text ), props.action || null ),
			el( 'div', { className: 'ffw-ai-bar', role: 'progressbar', 'aria-valuemin': 0, 'aria-valuemax': props.total, 'aria-valuenow': props.done }, el( 'i', { style: { width: pct + '%' } } ) ),
			props.log && props.log.length ? el(
				'ol',
				{ className: 'ffw-ai-log' },
				props.log.map( function ( row, index ) {
					return el( 'li', { key: index, className: row.ok ? 'is-ok' : 'is-error' }, el( ui.Icon, { name: row.ok ? 'check' : 'warning', size: 14 } ), el( 'span', null, row.title ), el( 'small', null, row.text ) );
				} )
			) : null
		);
	}

	/* ------------------------------------------------------------------
	 * Generate.
	 * ------------------------------------------------------------------ */

	function SourcePicker( props ) {
		var termState = useState( '' );
		var itemsState = useState( [] );
		var openState = useState( false );
		var busyState = useState( false );

		useEffect( function () {
			if ( ! openState[ 0 ] ) {
				return undefined;
			}
			var timer = window.setTimeout( function () {
				busyState[ 1 ]( true );
				ajax( 'ffw_ai_search_sources', { kind: props.kind, q: termState[ 0 ].trim() } ).then( function ( result ) {
					busyState[ 1 ]( false );
					itemsState[ 1 ]( result.items || [] );
				} ).catch( function () {
					busyState[ 1 ]( false );
				} );
			}, termState[ 0 ] ? 300 : 0 );
			return function () {
				window.clearTimeout( timer );
			};
		}, [ termState[ 0 ], props.kind, openState[ 0 ] ] );

		if ( props.value ) {
			return el(
				'div',
				{ className: 'ffw-ai-chosen' },
				el( ui.Icon, { name: 'product' === props.kind ? 'store' : 'page', size: 16 } ),
				el( 'span', null, el( 'strong', null, props.value.title || '#' + props.value.id ), el( 'small', null, props.value.type ) ),
				el( ui.Button, { small: true, variant: 'tertiary', onClick: function () {
					props.onChange( null );
					openState[ 1 ]( true );
				} }, __( 'Change', D ) )
			);
		}

		return el(
			'div',
			{ className: 'ffw-ai-combo' },
			el( ui.TextInput, {
				type: 'search',
				value: termState[ 0 ],
				placeholder: 'product' === props.kind ? __( 'Search products by name', D ) : __( 'Search pages and posts by title', D ),
				label: __( 'Search', D ),
				onChange: function ( value ) {
					termState[ 1 ]( value );
					openState[ 1 ]( true );
				},
				onKeyDown: function () {
					openState[ 1 ]( true );
				}
			} ),
			el(
				'ul',
				{ className: 'ffw-ai-results', role: 'listbox', onMouseEnter: function () {
					openState[ 1 ]( true );
				} },
				! openState[ 0 ] ? el( 'li', null, el( 'button', { type: 'button', className: 'ffw-ai-browse', onClick: function () {
					openState[ 1 ]( true );
				} }, el( ui.Icon, { name: 'list', size: 14 } ), 'product' === props.kind ? __( 'Browse recent products', D ) : __( 'Browse recent pages', D ) ) ) : null,
				openState[ 0 ] && busyState[ 0 ] && ! itemsState[ 0 ].length ? el( 'li', { className: 'ffw-ui-muted ffw-ai-note' }, __( 'Searching…', D ) ) : null,
				openState[ 0 ] && ! busyState[ 0 ] && ! itemsState[ 0 ].length ? el( 'li', { className: 'ffw-ui-muted ffw-ai-note' }, __( 'Nothing found.', D ) ) : null,
				openState[ 0 ] ? itemsState[ 0 ].map( function ( item ) {
					return el(
						'li',
						{ key: item.id },
						el( 'button', { type: 'button', role: 'option', onClick: function () {
							props.onChange( item );
						} }, el( 'span', null, item.title || '#' + item.id ), el( 'small', null, item.type + ( item.status ? ' · ' + item.status : '' ) ) )
					);
				} ) : null
			)
		);
	}

	function GenerateTab() {
		var woo = !! data.woo;
		var form = useState( { source: woo ? 'product' : 'page', chosen: null, topic: '', count: '6', language: data.language || '', focus: '' } );
		var phase = useState( 'form' );
		var errorState = useState( '' );
		var faqsState = useState( [] );
		var forState = useState( '' );
		var saveOpts = useState( { status: 'publish', faqCat: '0', addToPage: true } );
		var savingState = useState( false );
		var resultState = useState( null );
		var improvingState = useState( -1 );
		var f = form[ 0 ];
		var faqs = faqsState[ 0 ];

		function setForm( key, value ) {
			var next = Object.assign( {}, f );
			next[ key ] = value;
			if ( 'source' === key ) {
				next.chosen = null;
			}
			form[ 1 ]( next );
		}

		function generate() {
			errorState[ 1 ]( '' );
			if ( 'topic' === f.source ? f.topic.trim().length < 3 : ! f.chosen ) {
				errorState[ 1 ]( 'topic' === f.source ? __( 'Describe the topic in a few words.', D ) : 'product' === f.source ? __( 'Choose a product.', D ) : __( 'Choose a page or post.', D ) );
				return;
			}
			phase[ 1 ]( 'loading' );
			ajax( 'ffw_ai_generate', {
				source: f.source,
				source_id: f.chosen ? f.chosen.id : 0,
				topic: f.topic,
				count: f.count,
				language: f.language,
				focus: f.focus
			} ).then( function ( result ) {
				faqsState[ 1 ]( ( result.faqs || [] ).map( function ( faq ) {
					return { question: faq.question, answer: faq.answer, on: true };
				} ) );
				forState[ 1 ]( result.title || '' );
				phase[ 1 ]( 'results' );
			} ).catch( function ( error ) {
				phase[ 1 ]( 'form' );
				errorState[ 1 ]( error.message );
			} );
		}

		function updateFaq( index, patch ) {
			faqsState[ 1 ]( faqs.map( function ( faq, i ) {
				return i === index ? Object.assign( {}, faq, patch ) : faq;
			} ) );
		}

		function improve( index, mode ) {
			var faq = faqs[ index ];
			improvingState[ 1 ]( index );
			ajax( 'ffw_ai_improve', { question: faq.question, answer: faq.answer, mode: mode } ).then( function ( result ) {
				improvingState[ 1 ]( -1 );
				if ( result.answer ) {
					updateFaq( index, { answer: result.answer.replace( /<[^>]+>/g, '' ).trim() } );
				}
			} ).catch( function ( error ) {
				improvingState[ 1 ]( -1 );
				ui.toast( error.message, 'error' );
			} );
		}

		var selected = faqs.filter( function ( faq ) {
			return faq.on && faq.question.trim() && faq.answer.trim();
		} );

		function save() {
			if ( ! selected.length ) {
				ui.toast( __( 'Select at least one FAQ.', D ), 'error' );
				return;
			}
			savingState[ 1 ]( true );
			ajax( 'ffw_ai_insert', {
				items: selected.map( function ( faq ) {
					return { question: faq.question, answer: faq.answer };
				} ),
				source: f.source,
				source_id: f.chosen ? f.chosen.id : 0,
				status: saveOpts[ 0 ].status,
				faq_cat: saveOpts[ 0 ].faqCat,
				add_to_page: 'page' === f.source && saveOpts[ 0 ].addToPage ? '1' : ''
			} ).then( function ( result ) {
				savingState[ 1 ]( false );
				resultState[ 1 ]( result );
				phase[ 1 ]( 'done' );
			} ).catch( function ( error ) {
				savingState[ 1 ]( false );
				ui.toast( error.message, 'error' );
			} );
		}

		function startOver() {
			faqsState[ 1 ]( [] );
			resultState[ 1 ]( null );
			phase[ 1 ]( 'form' );
		}

		var sources = [];
		if ( woo ) {
			sources.push( { value: 'product', label: __( 'A product', D ) } );
		}
		sources.push( { value: 'page', label: __( 'A page or post', D ) } );
		sources.push( { value: 'topic', label: __( 'A topic', D ) } );

		var counts = [];
		for ( var i = 3; i <= ( data.maxFaqs || 10 ); i++ ) {
			counts.push( { value: String( i ), label: String( i ) } );
		}

		var formCard = el(
			ui.Card,
			{ className: 'ffw-ai-form', title: __( 'What should the FAQs be about?', D ) },
			el(
				'div',
				{ className: 'ffw-ui-fields' },
				el( ui.Field, { stacked: true, label: __( 'Write FAQs for', D ) }, el( ui.Segment, { label: __( 'Write FAQs for', D ), value: f.source, options: sources, onChange: function ( value ) {
					setForm( 'source', value );
				} } ) ),
				'topic' === f.source
					? el( ui.Field, { stacked: true, label: __( 'Topic', D ), help: __( 'A few sentences work best: the facts customers should know.', D ) }, el( ui.Textarea, { rows: 4, value: f.topic, label: __( 'Topic', D ), placeholder: __( 'For example: our 30-day return policy, free shipping over $50, and how to track an order.', D ), onChange: function ( value ) {
						setForm( 'topic', value );
					} } ) )
					: el( ui.Field, { stacked: true, label: 'product' === f.source ? __( 'Product', D ) : __( 'Page or post', D ), help: 'product' === f.source ? __( 'The AI reads its title, description, attributes and price.', D ) : __( 'The AI reads its title and content.', D ) }, el( SourcePicker, { kind: 'product' === f.source ? 'product' : 'page', value: f.chosen, onChange: function ( value ) {
						setForm( 'chosen', value );
					} } ) ),
				el(
					'div',
					{ className: 'ffw-ai-row' },
					el( ui.Field, { stacked: true, label: __( 'How many', D ) }, el( ui.Select, { label: __( 'How many', D ), value: f.count, options: counts, onChange: function ( value ) {
						setForm( 'count', value );
					} } ) ),
					el( ui.Field, { stacked: true, label: __( 'Language', D ) }, el( LanguageSelect, { value: f.language, onChange: function ( value ) {
						setForm( 'language', value );
					} } ) )
				),
				el( ui.Field, { stacked: true, label: __( 'Focus on (optional)', D ) }, el( ui.TextInput, { value: f.focus, label: __( 'Focus on', D ), placeholder: __( 'shipping, sizing, care, warranty', D ), onChange: function ( value ) {
					setForm( 'focus', value );
				} } ) )
			),
			errorState[ 0 ] ? el( ui.Notice, { tone: 'bad' }, el( 'p', null, errorState[ 0 ] ) ) : null,
			el( ui.Button, { variant: 'brand', icon: 'sparkle', busy: 'loading' === phase[ 0 ], disabled: ! ready, onClick: generate, className: 'ffw-ai-go' }, 'results' === phase[ 0 ] ? __( 'Generate again', D ) : __( 'Generate FAQs', D ) )
		);

		var output;
		if ( 'loading' === phase[ 0 ] ) {
			output = el( ui.Card, { className: 'ffw-ai-output' }, el( 'div', { className: 'ffw-ai-writing' }, el( 'span', { className: 'ffw-ui-spin is-large' } ), el( 'strong', null, __( 'Writing FAQs…', D ) ), el( 'span', { className: 'ffw-ui-muted' }, __( 'This takes 10 to 30 seconds.', D ) ) ) );
		} else if ( 'results' === phase[ 0 ] ) {
			output = el(
				ui.Card,
				{
					className: 'ffw-ai-output',
					title: sprintf( _n( '%d FAQ', '%d FAQs', faqs.length, D ), faqs.length ),
					description: forState[ 0 ] ? sprintf( __( 'For "%s". Edit anything before you save.', D ), forState[ 0 ] ) : __( 'Edit anything before you save.', D ),
					actions: el( ui.Toggle, { label: __( 'Select all', D ), checked: faqs.length > 0 && faqs.every( function ( faq ) {
						return faq.on;
					} ), onChange: function ( on ) {
						faqsState[ 1 ]( faqs.map( function ( faq ) {
							return Object.assign( {}, faq, { on: on } );
						} ) );
					} } ),
					footer: el(
						Fragment,
						null,
						el(
							'div',
							{ className: 'ffw-ui-row' },
							el( ui.Select, { label: __( 'Save as', D ), value: saveOpts[ 0 ].status, width: '190px', options: [ { value: 'publish', label: __( 'Published FAQs', D ) }, { value: 'draft', label: __( 'Drafts to review', D ) } ], onChange: function ( value ) {
								saveOpts[ 1 ]( Object.assign( {}, saveOpts[ 0 ], { status: value } ) );
							} } ),
							( data.faqCats || [] ).length ? el( ui.Select, { label: __( 'FAQ category', D ), value: saveOpts[ 0 ].faqCat, width: '190px', options: [ { value: '0', label: __( 'No category', D ) } ].concat( data.faqCats ), onChange: function ( value ) {
								saveOpts[ 1 ]( Object.assign( {}, saveOpts[ 0 ], { faqCat: value } ) );
							} } ) : null,
							'page' === f.source ? el( ui.Toggle, { label: __( 'Add them to the end of the page', D ), checked: saveOpts[ 0 ].addToPage, onChange: function ( value ) {
								saveOpts[ 1 ]( Object.assign( {}, saveOpts[ 0 ], { addToPage: value } ) );
							} } ) : null
						),
						el( ui.Button, { variant: 'primary', busy: savingState[ 0 ], disabled: ! selected.length, onClick: save }, sprintf( _n( 'Save %d FAQ', 'Save %d FAQs', selected.length, D ), selected.length ) )
					)
				},
				el(
					'ol',
					{ className: 'ffw-ai-items' },
					faqs.map( function ( faq, index ) {
						return el(
							'li',
							{ key: index, className: faq.on ? '' : 'is-off' },
							el( 'input', { type: 'checkbox', checked: faq.on, 'aria-label': __( 'Include this FAQ', D ), onChange: function ( event ) {
								updateFaq( index, { on: event.target.checked } );
							} } ),
							el(
								'div',
								{ className: 'ffw-ai-item-body' },
								el( 'input', { type: 'text', className: 'ffw-ui-input ffw-ai-q', value: faq.question, 'aria-label': __( 'Question', D ), onChange: function ( event ) {
									updateFaq( index, { question: event.target.value } );
								} } ),
								el( 'textarea', { className: 'ffw-ui-textarea ffw-ai-a', rows: 3, value: faq.answer, 'aria-label': __( 'Answer', D ), onChange: function ( event ) {
									updateFaq( index, { answer: event.target.value } );
								} } ),
								el(
									'div',
									{ className: 'ffw-ai-improve' },
									improvingState[ 0 ] === index ? el( 'span', { className: 'ffw-ui-muted' }, el( 'span', { className: 'ffw-ui-spin' } ), ' ', __( 'Rewriting…', D ) ) : ( data.modes || [] ).map( function ( mode ) {
										return el( ui.Button, { key: mode.value, small: true, variant: 'tertiary', disabled: improvingState[ 0 ] !== -1, onClick: function () {
											improve( index, mode.value );
										} }, mode.label );
									} )
								)
							),
							el( ui.Button, { small: true, variant: 'tertiary', iconOnly: true, icon: 'trash', label: __( 'Remove', D ), title: __( 'Remove', D ), onClick: function () {
								faqsState[ 1 ]( faqs.filter( function ( item, i ) {
									return i !== index;
								} ) );
							} } )
						);
					} )
				)
			);
		} else if ( 'done' === phase[ 0 ] && resultState[ 0 ] ) {
			var result = resultState[ 0 ];
			output = el(
				ui.Card,
				{ className: 'ffw-ai-output' },
				el(
					'div',
					{ className: 'ffw-ai-done' },
					el( 'span', { className: 'ffw-ai-done-icon' }, el( ui.Icon, { name: 'check', size: 26, stroke: 3 } ) ),
					el( 'h3', null, 'draft' === result.status ? sprintf( _n( '%d draft saved', '%d drafts saved', result.count, D ), result.count ) : sprintf( _n( '%d FAQ published', '%d FAQs published', result.count, D ), result.count ) ),
					el( 'p', { className: 'ffw-ui-muted' }, 'product' === f.source ? __( 'They are attached to the product.', D ) : result.addedToPage ? __( 'A FAQ block with them was added to the end of the page.', D ) : '' ),
					el(
						'div',
						{ className: 'ffw-ui-row' },
						el( ui.Button, { href: data.manager + '#/list' + ( 'draft' === result.status ? '?status=draft' : '' ) }, __( 'View FAQs', D ) ),
						result.targetUrl ? el( ui.Button, { href: result.targetUrl, target: '_blank', icon: 'external' }, __( 'View page', D ) ) : null,
						el( ui.Button, { variant: 'brand', icon: 'sparkle', onClick: startOver }, __( 'Write more FAQs', D ) )
					)
				)
			);
		} else {
			output = el( ui.Card, { className: 'ffw-ai-output' }, el( ui.Empty, { icon: 'sparkle', title: __( 'Your FAQs appear here', D ), text: __( 'You can edit every question and answer, or rewrite an answer, before saving.', D ) } ) );
		}

		return el( 'div', { className: 'ffw-ai-grid' }, formCard, output );
	}

	/* ------------------------------------------------------------------
	 * Bulk (Pro).
	 * ------------------------------------------------------------------ */

	function BulkTab() {
		var scopeState = useState( 'no_faqs' );
		var countState = useState( '4' );
		var langState = useState( data.language || '' );
		var runState = useState( null );
		var stopRef = useRef( false );

		if ( ! data.bulk ) {
			return el( ui.Upsell, { title: __( 'Write FAQs for many products at once', D ), text: __( 'Pick a product category, or every product without FAQs, and Pro writes FAQs for each one. They are saved as drafts on the right product, ready to review.', D ) } );
		}
		if ( ! data.woo ) {
			return el( ui.Card, null, el( ui.Empty, { icon: 'store', title: __( 'Bulk FAQs need WooCommerce', D ), text: __( 'Bulk generation writes FAQs for products. Activate WooCommerce, or use Generate for pages and topics.', D ) } ) );
		}

		var run = runState[ 0 ];

		function start() {
			stopRef.current = false;
			runState[ 1 ]( { phase: 'listing', total: 0, done: 0, created: 0, log: [] } );
			ajax( 'ffw_pro_ai_bulk_list', { scope: scopeState[ 0 ] } ).then( function ( result ) {
				var products = result.products || [];
				var state = { phase: 'running', total: products.length, done: 0, created: 0, log: [], draftsUrl: result.draftsUrl };
				runState[ 1 ]( Object.assign( {}, state ) );
				if ( ! products.length ) {
					runState[ 1 ]( Object.assign( {}, state, { phase: 'done' } ) );
					return;
				}
				var index = 0;
				function next() {
					if ( stopRef.current || index >= products.length ) {
						state.phase = 'done';
						runState[ 1 ]( Object.assign( {}, state ) );
						return;
					}
					var product = products[ index ];
					index++;
					ajax( 'ffw_pro_ai_bulk_item', { product_id: product.id, count: countState[ 0 ], language: langState[ 0 ] } ).then( function ( item ) {
						state.created += item.count || 0;
						state.log = state.log.concat( [ { ok: true, title: product.title, text: sprintf( _n( '%d draft', '%d drafts', item.count || 0, D ), item.count || 0 ) } ] );
					} ).catch( function ( error ) {
						state.log = state.log.concat( [ { ok: false, title: product.title, text: error.message } ] );
					} ).then( function () {
						state.done = index;
						runState[ 1 ]( Object.assign( {}, state ) );
						next();
					} );
				}
				next();
			} ).catch( function ( error ) {
				runState[ 1 ]( null );
				ui.toast( error.message, 'error' );
			} );
		}

		var running = run && ( 'running' === run.phase || 'listing' === run.phase );
		var scopes = [ { value: 'no_faqs', label: __( 'Products without FAQs yet', D ) } ].concat( ( data.categories || [] ).map( function ( cat ) {
			return { value: 'cat:' + cat.id, label: sprintf( __( 'Category: %1$s (%2$d)', D ), cat.name, cat.count ) };
		} ) );

		return el(
			'div',
			{ className: 'ffw-ai-grid' },
			el(
				ui.Card,
				{ className: 'ffw-ai-form', title: __( 'Write FAQs for products', D ), description: __( 'Saved as drafts on each product, up to 50 products per run, so you can review them first.', D ) },
				el(
					'div',
					{ className: 'ffw-ui-fields' },
					el( ui.Field, { stacked: true, label: __( 'Products', D ) }, el( ui.Select, { label: __( 'Products', D ), value: scopeState[ 0 ], options: scopes, onChange: scopeState[ 1 ] } ) ),
					el(
						'div',
						{ className: 'ffw-ai-row' },
						el( ui.Field, { stacked: true, label: __( 'FAQs per product', D ) }, el( ui.Select, { label: __( 'FAQs per product', D ), value: countState[ 0 ], options: [ '3', '4', '5', '6' ].map( function ( n ) {
							return { value: n, label: n };
						} ), onChange: countState[ 1 ] } ) ),
						el( ui.Field, { stacked: true, label: __( 'Language', D ) }, el( LanguageSelect, { value: langState[ 0 ], onChange: langState[ 1 ] } ) )
					)
				),
				el( ui.Button, { variant: 'brand', icon: 'bolt', busy: running, disabled: ! ready, onClick: start, className: 'ffw-ai-go' }, __( 'Start', D ) )
			),
			el(
				ui.Card,
				{ className: 'ffw-ai-output' },
				! run ? el( ui.Empty, { icon: 'store', title: __( 'Progress shows here', D ), text: __( 'Each product is written one at a time. You can stop at any moment.', D ) } ) : el(
					Fragment,
					null,
					el( Progress, {
						total: run.total,
						done: run.done,
						text: 'listing' === run.phase ? __( 'Finding products…', D ) : 'done' === run.phase ? ( run.total ? sprintf( _n( 'Done: %d draft written.', 'Done: %d drafts written.', run.created, D ), run.created ) : __( 'No products match.', D ) ) : sprintf( __( '%1$d of %2$d products', D ), run.done, run.total ),
						action: running ? el( ui.Button, { small: true, variant: 'tertiary', onClick: function () {
							stopRef.current = true;
						} }, __( 'Stop', D ) ) : null,
						log: run.log
					} ),
					'done' === run.phase && run.created ? el( ui.Button, { href: data.manager + '#/list?status=draft', icon: 'list' }, __( 'Review drafts', D ) ) : null
				)
			)
		);
	}

	/* ------------------------------------------------------------------
	 * Fill gaps (Pro).
	 * ------------------------------------------------------------------ */

	function GapsTab() {
		var gapsInfo = data.gaps;
		var listState = useState( null );
		var pickedState = useState( {} );
		var langState = useState( data.language || '' );
		var runState = useState( null );
		var draftsState = useState( gapsInfo ? gapsInfo.drafts : 0 );
		var autoState = useState( gapsInfo ? { on: !! gapsInfo.auto, next: gapsInfo.next } : null );
		var autoBusy = useState( false );

		function load() {
			listState[ 1 ]( null );
			ajax( 'ffw_pro_ai_gaps_list' ).then( function ( result ) {
				var gaps = result.gaps || [];
				var picked = {};
				gaps.forEach( function ( gap ) {
					picked[ gap.key ] = true;
				} );
				pickedState[ 1 ]( picked );
				listState[ 1 ]( gaps );
				draftsState[ 1 ]( result.drafts || 0 );
			} ).catch( function ( error ) {
				listState[ 1 ]( [] );
				ui.toast( error.message, 'error' );
			} );
		}

		useEffect( function () {
			if ( gapsInfo ) {
				load();
			}
		}, [] );

		if ( ! gapsInfo ) {
			if ( data.gapsLegacy ) {
				return el( ui.Notice, { tone: 'info', action: el( ui.Button, { small: true, href: data.classicUrl }, __( 'Open Fill gaps', D ) ) }, el( 'p', null, __( 'Update Happy FAQs Pro to use Fill gaps on this screen. It is still available on the previous screen.', D ) ) );
			}
			return el( ui.Upsell, { title: __( 'Answer what visitors could not find', D ), text: __( 'Pro turns searches that found nothing and unanswered customer questions into draft FAQs, attached to the right product. Run it any time or every night.', D ) } );
		}

		var gaps = listState[ 0 ];
		var picked = pickedState[ 0 ];
		var chosen = ( gaps || [] ).filter( function ( gap ) {
			return picked[ gap.key ];
		} );
		var running = runState[ 0 ] && 'running' === runState[ 0 ].phase;

		function dismiss( gap ) {
			ajax( 'ffw_pro_ai_gaps_dismiss', { key: gap.key } ).then( function () {
				listState[ 1 ]( gaps.filter( function ( item ) {
					return item.key !== gap.key;
				} ) );
			} ).catch( function ( error ) {
				ui.toast( error.message, 'error' );
			} );
		}

		function draft() {
			var groups = {};
			chosen.forEach( function ( gap ) {
				var id = gap.object_id || 0;
				groups[ id ] = groups[ id ] || [];
				groups[ id ].push( gap );
			} );
			var jobs = [];
			var per = gapsInfo.perRequest || 8;
			Object.keys( groups ).forEach( function ( id ) {
				for ( var i = 0; i < groups[ id ].length; i += per ) {
					jobs.push( groups[ id ].slice( i, i + per ) );
				}
			} );
			var state = { phase: 'running', total: chosen.length, done: 0, created: 0, log: [] };
			runState[ 1 ]( Object.assign( {}, state ) );
			var index = 0;
			function next() {
				if ( index >= jobs.length ) {
					state.phase = 'done';
					runState[ 1 ]( Object.assign( {}, state ) );
					load();
					return;
				}
				var job = jobs[ index ];
				index++;
				var title = job[ 0 ].object_id ? job[ 0 ].object : __( 'Site-wide', D );
				ajax( 'ffw_pro_ai_gaps_draft', { keys: job.map( function ( gap ) {
					return gap.key;
				} ), language: langState[ 0 ] } ).then( function ( result ) {
					state.created += result.created || 0;
					draftsState[ 1 ]( result.drafts || 0 );
					state.log = state.log.concat( [ { ok: true, title: title, text: sprintf( _n( '%d draft', '%d drafts', result.created || 0, D ), result.created || 0 ) } ] );
				} ).catch( function ( error ) {
					state.log = state.log.concat( [ { ok: false, title: title, text: error.message } ] );
				} ).then( function () {
					state.done += job.length;
					runState[ 1 ]( Object.assign( {}, state ) );
					next();
				} );
			}
			next();
		}

		function setAuto( on ) {
			autoBusy[ 1 ]( true );
			ajax( 'ffw_pro_ai_gaps_auto', { on: on ? '1' : '0' } ).then( function ( result ) {
				autoBusy[ 1 ]( false );
				autoState[ 1 ]( { on: !! result.on, next: result.next } );
				ui.toast( result.on ? __( 'New gaps will be drafted every night.', D ) : __( 'Nightly drafts are off.', D ) );
			} ).catch( function ( error ) {
				autoBusy[ 1 ]( false );
				ui.toast( error.message, 'error' );
			} );
		}

		var list;
		if ( null === gaps ) {
			list = el( ui.Loading, { text: __( 'Looking for gaps…', D ) } );
		} else if ( ! gaps.length ) {
			list = el( ui.Empty, { icon: 'check', title: __( 'No open gaps', D ), text: sprintf( __( 'Every search in the last %d days found a FAQ, and every customer question has an answer.', D ), gapsInfo.days || 30 ) } );
		} else {
			list = el(
				'ul',
				{ className: 'ffw-ai-gaps' },
				gaps.map( function ( gap ) {
					var what = 'search' === gap.type
						? sprintf( _n( 'Searched once', 'Searched %d times', gap.count, D ), gap.count )
						: ( gap.name ? sprintf( __( 'Asked by %s', D ), gap.name ) : __( 'Asked by a visitor', D ) );
					return el(
						'li',
						{ key: gap.key, className: picked[ gap.key ] ? '' : 'is-off' },
						el( 'input', { type: 'checkbox', checked: !! picked[ gap.key ], 'aria-label': gap.text, onChange: function ( event ) {
							var next = Object.assign( {}, picked );
							next[ gap.key ] = event.target.checked;
							pickedState[ 1 ]( next );
						} } ),
						el(
							'div',
							{ className: 'ffw-ai-gap-body' },
							el( 'strong', null, gap.text ),
							el(
								'span',
								{ className: 'ffw-ai-gap-meta' },
								el( ui.Badge, { tone: 'search' === gap.type ? 'info' : 'warn', icon: 'search' === gap.type ? 'search' : 'chat' }, 'search' === gap.type ? __( 'Search', D ) : __( 'Question', D ) ),
								what,
								' · ',
								gap.object_id ? el( 'a', { href: gap.object_url, target: '_blank', rel: 'noopener' }, gap.object ) : __( 'Site-wide', D ),
								gap.date ? ' · ' + gap.date : ''
							)
						),
						el( ui.Button, { small: true, variant: 'tertiary', iconOnly: true, icon: 'close', label: __( 'Never suggest this again', D ), title: __( 'Never suggest this again', D ), onClick: function () {
							dismiss( gap );
						} } )
					);
				} )
			);
		}

		return el(
			'div',
			{ className: 'ffw-ai-gaps-layout' },
			el(
				ui.Card,
				{
					title: __( 'Open gaps', D ) + ( gaps && gaps.length ? ' (' + gaps.length + ')' : '' ),
					description: sprintf( __( 'Searches that found no FAQ in the last %d days, and customer questions nobody has answered.', D ), gapsInfo.days || 30 ),
					actions: el( ui.Button, { small: true, variant: 'tertiary', icon: 'refresh', onClick: load, disabled: running }, __( 'Refresh', D ) ),
					flush: true,
					footer: gaps && gaps.length ? el(
						Fragment,
						null,
						el( ui.Toggle, { label: __( 'Select all', D ), checked: chosen.length === gaps.length, onChange: function ( on ) {
							var next = {};
							gaps.forEach( function ( gap ) {
								next[ gap.key ] = on;
							} );
							pickedState[ 1 ]( next );
						} } ),
						el( 'div', { className: 'ffw-ui-row' }, el( LanguageSelect, { value: langState[ 0 ], onChange: langState[ 1 ] } ), el( ui.Button, { variant: 'brand', icon: 'sparkle', busy: running, disabled: ! ready || ! chosen.length, onClick: draft }, sprintf( _n( 'Draft %d FAQ', 'Draft %d FAQs', chosen.length, D ), chosen.length ) ) )
					) : null
				},
				list
			),
			el(
				'aside',
				{ className: 'ffw-ai-gaps-side' },
				runState[ 0 ] ? el( ui.Card, null, el( Progress, {
					total: runState[ 0 ].total,
					done: runState[ 0 ].done,
					text: running ? sprintf( __( '%1$d of %2$d gaps', D ), runState[ 0 ].done, runState[ 0 ].total ) : sprintf( _n( 'Done: %d draft written.', 'Done: %d drafts written.', runState[ 0 ].created, D ), runState[ 0 ].created ),
					log: runState[ 0 ].log
				} ) ) : null,
				el(
					ui.Card,
					{ title: __( 'Review queue', D ) },
					el( 'div', { className: 'ffw-ui-stat-value' }, ui.number( draftsState[ 0 ] ) ),
					el( 'p', { className: 'ffw-ui-muted' }, __( 'AI drafts waiting for review. They go live on their product when you publish them.', D ) ),
					el( ui.Button, { href: gapsInfo.reviewUrl, icon: 'list' }, __( 'Review drafts', D ) )
				),
				el(
					ui.Card,
					{ title: __( 'Every night', D ) },
					el( ui.Toggle, { label: __( 'Draft new gaps every night', D ), checked: autoState[ 0 ].on, disabled: autoBusy[ 0 ], onChange: setAuto } ),
					el( 'p', { className: 'ffw-ui-field-help' }, __( 'Up to 30 gaps a night, saved as drafts. You get an email when new drafts are ready.', D ), autoState[ 0 ].on && autoState[ 0 ].next ? ' ' + sprintf( __( 'Next run: %s.', D ), autoState[ 0 ].next ) : '' ),
					gapsInfo.last ? el( 'p', { className: 'ffw-ui-field-help' }, sprintf( _n( 'Last run: %1$s, %2$d draft.', 'Last run: %1$s, %2$d drafts.', gapsInfo.last.created, D ), gapsInfo.last.date, gapsInfo.last.created ), gapsInfo.last.error ? ' ' + gapsInfo.last.error : '' ) : null
				)
			)
		);
	}

	/* ------------------------------------------------------------------
	 * Page.
	 * ------------------------------------------------------------------ */

	function AIPage() {
		var tabState = useState( function () {
			var hash = window.location.hash.replace( '#', '' );
			return [ 'generate', 'bulk', 'gaps' ].indexOf( hash ) !== -1 ? hash : 'generate';
		} );

		function go( id ) {
			window.history.replaceState( null, '', '#' + id );
			tabState[ 1 ]( id );
		}

		var status = data.status || {};
		var head = el( ui.PageHead, {
			title: __( 'AI FAQs', D ),
			description: __( 'Write FAQs from a product, a page or a topic. Review and edit them, then save the ones you like.', D ),
			actions: el(
				'div',
				{ className: ui.cx( 'ffw-ai-status', 'is-' + engine ) },
				el( 'span', { className: 'ffw-ai-status-dot', 'aria-hidden': 'true' } ),
				el( 'span', null, el( 'strong', null, status.label ), el( 'small', null, status.help ) ),
				el( ui.Button, { small: true, variant: 'tertiary', icon: 'settings', href: data.aiSettings }, __( 'AI settings', D ) )
			)
		} );

		if ( ! data.enabled ) {
			return el( Fragment, null, head, el( ui.Card, null, el( ui.Empty, { icon: 'sparkle', title: __( 'AI FAQs are turned off', D ), text: __( 'Turn them on in Settings → AI.', D ), action: el( ui.Button, { variant: 'primary', href: data.aiSettings }, __( 'Open AI settings', D ) ) } ) ) );
		}

		return el(
			Fragment,
			null,
			head,
			! ready ? el( ui.Notice, { tone: 'warn', action: el( 'span', { className: 'ffw-ui-row' }, data.connectorsUrl ? el( ui.Button, { small: true, href: data.connectorsUrl }, __( 'Connectors', D ) ) : null, el( ui.Button, { small: true, variant: 'primary', href: data.aiSettings }, __( 'Add an API key', D ) ) ) }, el( 'p', null, status.help ) ) : null,
			el( ui.Tabs, {
				label: __( 'AI FAQs', D ),
				value: tabState[ 0 ],
				onChange: go,
				tabs: [
					{ id: 'generate', label: __( 'Generate', D ), icon: 'sparkle' },
					{ id: 'bulk', label: __( 'Bulk for products', D ), icon: 'store', badge: data.bulk ? null : el( ui.ProBadge ) },
					{ id: 'gaps', label: __( 'Fill gaps', D ), icon: 'search', badge: data.gaps || data.gapsLegacy ? null : el( ui.ProBadge ) }
				]
			} ),
			'generate' === tabState[ 0 ] ? el( GenerateTab ) : null,
			'bulk' === tabState[ 0 ] ? el( BulkTab ) : null,
			'gaps' === tabState[ 0 ] ? el( GapsTab ) : null,
			el( ui.ClassicLink, null )
		);
	}

	ui.mount( AIPage );
} )( window.wp, window.ffwUI, window.ffwPage );
