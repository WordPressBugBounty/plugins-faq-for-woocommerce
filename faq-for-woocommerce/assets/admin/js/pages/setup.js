/**
 * Happy FAQs setup wizard (React).
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
	var urls = data.urls || {};

	function ajax( action, fields ) {
		var body = new window.FormData();
		body.append( 'action', action );
		body.append( 'nonce', data.nonce );
		Object.keys( fields || {} ).forEach( function ( key ) {
			var value = fields[ key ];
			if ( Array.isArray( value ) ) {
				value.forEach( function ( item, index ) {
					if ( item && 'object' === typeof item ) {
						Object.keys( item ).forEach( function ( sub ) {
							body.append( key + '[' + index + '][' + sub + ']', item[ sub ] );
						} );
					} else {
						body.append( key + '[]', item );
					}
				} );
			} else if ( value !== undefined && value !== null ) {
				body.append( key, value );
			}
		} );
		return window.fetch( data.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( result ) {
				if ( ! result || ! result.success ) {
					throw new Error( ( result && result.data && result.data.message ) || __( 'Could not save. Please try again.', D ) );
				}
				return result.data || {};
			} );
	}

	function samplesFor( goal ) {
		return ( data.samples[ goal ] || [] ).map( function ( pair ) {
			return { question: pair[ 0 ], answer: pair[ 1 ], saved: false };
		} );
	}

	function ProductPicker( props ) {
		var term = useState( '' );
		var results = useState( [] );
		var names = useState( {} );
		useEffect( function () {
			var timer = window.setTimeout( function () {
				ui.api( '/lookup', { query: { type: 'product', search: term[ 0 ].trim() } } ).then( function ( response ) {
					results[ 1 ]( response.items || [] );
					var next = Object.assign( {}, names[ 0 ] );
					( response.items || [] ).forEach( function ( item ) {
						next[ item.id ] = item.title;
					} );
					names[ 1 ]( next );
				} ).catch( function () {} );
			}, term[ 0 ] ? 300 : 0 );
			return function () {
				window.clearTimeout( timer );
			};
		}, [ term[ 0 ] ] );
		var value = props.value;
		return el(
			'div',
			{ className: 'ffw-sw2-picker' },
			value.length ? el( 'div', { className: 'ffw-sw2-chips' }, value.map( function ( id ) {
				return el( 'span', { key: id, className: 'ffw-sw2-chip' }, names[ 0 ][ id ] || '#' + id, el( 'button', { type: 'button', 'aria-label': __( 'Remove', D ), onClick: function () {
					props.onChange( value.filter( function ( item ) {
						return item !== id;
					} ) );
				} }, '×' ) );
			} ) ) : null,
			el( ui.TextInput, { type: 'search', value: term[ 0 ], placeholder: __( 'Search for a product…', D ), label: __( 'Search for a product', D ), onChange: term[ 1 ] } ),
			results[ 0 ].length ? el( 'ul', { className: 'ffw-sw2-results' }, results[ 0 ].slice( 0, 6 ).map( function ( item ) {
				var on = value.indexOf( item.id ) !== -1;
				return el( 'li', { key: item.id }, el( 'button', { type: 'button', className: on ? 'is-on' : '', onClick: function () {
					props.onChange( on ? value.filter( function ( id ) {
						return id !== item.id;
					} ) : value.concat( [ item.id ] ) );
				} }, el( 'span', { className: 'ffw-sw2-tick' }, on ? '✓' : '' ), item.title ) );
			} ) ) : null
		);
	}

	function Wizard() {
		var steps = [ 'goal', 'faqs', 'display' ];
		if ( ( data.extras || [] ).length && data.canInstall ) {
			steps.push( 'extras' );
		}
		steps.push( 'ready' );
		var labels = { goal: __( 'Goal', D ), faqs: __( 'First FAQs', D ), display: __( 'Display', D ), extras: __( 'Extras', D ), ready: __( 'Ready', D ) };

		var stepState = useState( 0 );
		var goalState = useState( data.goal || 'general' );
		var faqsState = useState( samplesFor( data.goal || 'general' ) );
		var categoryState = useState( '' );
		var productsState = useState( [] );
		var errorState = useState( '' );
		var savingState = useState( false );
		var resultState = useState( { shortcode: '[ffw_template]', productUrl: '', count: 0 } );
		var extrasState = useState( ( data.extras || [] ).map( function ( extra ) {
			return extra.key;
		} ) );
		var toolsState = useState( '' );
		var finished = useRef( false );

		var step = steps[ stepState[ 0 ] ];
		var goal = goalState[ 0 ];
		var faqs = faqsState[ 0 ];

		function go( index ) {
			errorState[ 1 ]( '' );
			stepState[ 1 ]( Math.max( 0, Math.min( steps.length - 1, index ) ) );
			window.scrollTo( 0, 0 );
		}

		function finish( status ) {
			if ( finished.current ) {
				return Promise.resolve();
			}
			finished.current = true;
			return ajax( 'ffw_setup_finish', { status: status, goal: goal } ).catch( function () {} );
		}

		useEffect( function () {
			if ( 'ready' === step ) {
				finish( 'completed' );
			}
		}, [ step ] );

		function chooseGoal( value ) {
			goalState[ 1 ]( value );
			// Fresh starter questions for the new goal, unless some are saved.
			if ( ! faqs.some( function ( faq ) {
				return faq.saved;
			} ) ) {
				faqsState[ 1 ]( samplesFor( value ) );
			}
		}

		function updateFaq( index, patch ) {
			faqsState[ 1 ]( faqs.map( function ( faq, i ) {
				return i === index ? Object.assign( {}, faq, patch ) : faq;
			} ) );
		}

		function saveFaqs() {
			var fresh = faqs.filter( function ( faq ) {
				return ! faq.saved && faq.question.trim() && faq.answer.trim();
			} );
			if ( ! fresh.length ) {
				if ( faqs.some( function ( faq ) {
					return faq.saved;
				} ) ) {
					go( stepState[ 0 ] + 1 );
					return;
				}
				errorState[ 1 ]( __( 'Add at least one question, or skip this step.', D ) );
				return;
			}
			savingState[ 1 ]( true );
			ajax( 'ffw_setup_create_faqs', {
				goal: goal,
				category: 'general' === goal ? categoryState[ 0 ].trim() : '',
				products: 'product' === goal ? productsState[ 0 ] : [],
				faqs: fresh.map( function ( faq ) {
					return { question: faq.question, answer: faq.answer };
				} )
			} ).then( function ( response ) {
				savingState[ 1 ]( false );
				// Saved rows stay but lock, so going back never duplicates them.
				faqsState[ 1 ]( faqs.map( function ( faq ) {
					return fresh.indexOf( faq ) !== -1 ? Object.assign( {}, faq, { saved: true } ) : faq;
				} ) );
				resultState[ 1 ]( {
					shortcode: response.shortcode || '[ffw_template]',
					productUrl: response.product_url || '',
					count: resultState[ 0 ].count + ( response.faq_ids || [] ).length
				} );
				go( stepState[ 0 ] + 1 );
			} ).catch( function ( error ) {
				savingState[ 1 ]( false );
				errorState[ 1 ]( error.message );
			} );
		}

		function installExtras() {
			if ( extrasState[ 0 ].length ) {
				toolsState[ 1 ]( 'working' );
				ajax( 'ffw_setup_install_extras', { extras: extrasState[ 0 ] } ).then( function ( response ) {
					var results = response.results || {};
					var failed = Object.keys( results ).some( function ( key ) {
						return 'active' !== results[ key ].status;
					} );
					toolsState[ 1 ]( failed ? 'partial' : 'ready' );
				} ).catch( function () {
					toolsState[ 1 ]( 'partial' );
				} );
			}
			go( stepState[ 0 ] + 1 );
		}

		function actions( primary, opts ) {
			opts = opts || {};
			return el(
				'div',
				{ className: 'ffw-sw2-actions' },
				stepState[ 0 ] > 0 ? el( ui.Button, { variant: 'tertiary', icon: 'back', onClick: function () {
					go( stepState[ 0 ] - 1 );
				} }, __( 'Back', D ) ) : el( 'span' ),
				el(
					'span',
					{ className: 'ffw-ui-row' },
					opts.noSkip ? null : el( ui.Button, { variant: 'tertiary', onClick: function () {
						go( stepState[ 0 ] + 1 );
					} }, __( 'Skip this step', D ) ),
					primary
				)
			);
		}

		var body;
		if ( 'goal' === step ) {
			body = el(
				Fragment,
				null,
				el( 'h1', null, __( 'What would you like to create first?', D ) ),
				el( 'p', { className: 'ffw-sw2-lead' }, __( 'Pick a starting point. You can create both kinds of FAQs later.', D ) ),
				el( ui.ChoiceCards, {
					label: __( 'Goal', D ),
					value: goal,
					min: 240,
					options: [
						{ value: 'general', label: __( 'General FAQs', D ), description: __( 'For help pages, posts and landing pages. Show them anywhere with a shortcode, block or widget.', D ), preview: el( 'span', { className: 'ffw-sw2-icon' }, el( ui.Icon, { name: 'question', size: 22 } ) ) },
						{ value: 'product', label: __( 'Product FAQs', D ), description: data.hasWoo ? __( 'Answer buyer questions right on your WooCommerce product pages, in their own FAQs tab.', D ) : __( 'Needs WooCommerce. Install WooCommerce to add FAQs to your products.', D ), disabled: ! data.hasWoo, preview: el( 'span', { className: 'ffw-sw2-icon' }, el( ui.Icon, { name: 'store', size: 22 } ) ) }
					],
					onChange: chooseGoal
				} ),
				actions( el( ui.Button, { variant: 'brand', onClick: function () {
					go( 1 );
				} }, __( 'Continue', D ) ) )
			);
		} else if ( 'faqs' === step ) {
			body = el(
				Fragment,
				null,
				el( 'h1', null, __( 'Create your very first FAQs', D ) ),
				el( 'p', { className: 'ffw-sw2-lead' }, 'product' === goal ? __( 'We added the questions shoppers ask most. Edit the replies to match your store, pick products, or just continue.', D ) : __( 'We added the questions visitors ask most. Edit the replies to match your business, or just continue.', D ) ),
				'general' === goal ? el( ui.Field, { stacked: true, label: __( 'FAQ category (optional)', D ) }, el( ui.TextInput, { value: categoryState[ 0 ], placeholder: __( 'e.g. Shipping, Returns, Getting started', D ), label: __( 'FAQ category', D ), onChange: categoryState[ 1 ] } ) ) : null,
				'product' === goal && data.hasWoo ? el( ui.Field, { stacked: true, label: __( 'Show on these products (optional)', D ), help: __( 'You can also assign FAQs to product categories and tags later, from any FAQ.', D ), helpBelow: true }, el( ProductPicker, { value: productsState[ 0 ], onChange: productsState[ 1 ] } ) ) : null,
				el(
					'div',
					{ className: 'ffw-sw2-faqs' },
					faqs.map( function ( faq, index ) {
						return el(
							'div',
							{ key: index, className: ui.cx( 'ffw-sw2-faq', faq.saved && 'is-saved' ) },
							el( 'div', { className: 'ffw-sw2-faq-head' }, el( 'span', null, faq.saved ? __( 'Saved', D ) : sprintf( __( 'FAQ %d', D ), index + 1 ) ), faq.saved ? el( ui.Icon, { name: 'check', size: 14 } ) : el( ui.Button, { small: true, variant: 'tertiary', iconOnly: true, icon: 'trash', label: __( 'Remove this FAQ', D ), onClick: function () {
								faqsState[ 1 ]( faqs.filter( function ( item, i ) {
									return i !== index;
								} ) );
							} } ) ),
							el( 'input', { type: 'text', className: 'ffw-ui-input ffw-sw2-q', value: faq.question, readOnly: faq.saved, 'aria-label': __( 'Question', D ), onChange: function ( event ) {
								updateFaq( index, { question: event.target.value } );
							} } ),
							el( 'textarea', { className: 'ffw-ui-textarea', rows: 3, value: faq.answer, readOnly: faq.saved, 'aria-label': __( 'Answer', D ), onChange: function ( event ) {
								updateFaq( index, { answer: event.target.value } );
							} } )
						);
					} )
				),
				el( ui.Button, { icon: 'plus', variant: 'tertiary', onClick: function () {
					var blank = data.samples.blank || [ '', '' ];
					faqsState[ 1 ]( faqs.concat( [ { question: '', answer: '', saved: false, placeholder: blank } ] ) );
				} }, __( 'Add another FAQ', D ) ),
				errorState[ 0 ] ? el( ui.Notice, { tone: 'bad' }, el( 'p', null, errorState[ 0 ] ) ) : null,
				actions( el( ui.Button, { variant: 'brand', busy: savingState[ 0 ], onClick: saveFaqs }, __( 'Save and continue', D ) ) )
			);
		} else if ( 'display' === step ) {
			body = el(
				Fragment,
				null,
				el( 'h1', null, __( 'Your shortcode is ready', D ) ),
				el( 'p', { className: 'ffw-sw2-lead' }, __( 'Copy it into any page or post, or use the block or widget below.', D ) ),
				el( ui.CopyField, { value: resultState[ 0 ].shortcode, className: 'ffw-sw2-code' } ),
				'product' === goal ? el( ui.Notice, { tone: 'good', icon: 'check' }, el( 'p', null, el( 'strong', null, __( 'Already live on your products. ', D ) ), __( 'Product FAQs appear in a FAQs tab on each product they are assigned to. No shortcode needed.', D ), resultState[ 0 ].productUrl ? el( Fragment, null, ' ', el( 'a', { href: resultState[ 0 ].productUrl, target: '_blank', rel: 'noopener' }, __( 'View the product page', D ) ) ) : null ) ) : null,
				el(
					'div',
					{ className: 'ffw-sw2-ways' },
					[
						[ 'code', __( 'Shortcode', D ), __( 'Paste the shortcode into any page, post or text widget.', D ) ],
						[ 'layout', __( 'Block', D ), __( 'Edit a page, click +, and search for Happy Prebuilt FAQs.', D ) ],
						[ 'grid', __( 'Elementor widget', D ), data.elementor ? __( 'Search the widgets panel for Happy Prebuilt FAQs.', D ) : __( 'When Elementor is active, search its widgets for Happy Prebuilt FAQs.', D ) ]
					].map( function ( way ) {
						return el( 'div', { key: way[ 1 ], className: 'ffw-sw2-way' }, el( 'span', { className: 'ffw-sw2-icon' }, el( ui.Icon, { name: way[ 0 ], size: 18 } ) ), el( 'strong', null, way[ 1 ] ), el( 'p', null, way[ 2 ] ) );
					} )
				),
				actions( el( ui.Button, { variant: 'brand', onClick: function () {
					go( stepState[ 0 ] + 1 );
				} }, __( 'Continue', D ) ) )
			);
		} else if ( 'extras' === step ) {
			body = el(
				Fragment,
				null,
				el( 'h1', null, __( 'Recommended for your site', D ) ),
				el( 'p', { className: 'ffw-sw2-lead' }, __( 'Free tools that work well alongside Happy FAQs. Keep the ones you want.', D ) ),
				el(
					'div',
					{ className: 'ffw-sw2-extras' },
					data.extras.map( function ( extra ) {
						var on = extrasState[ 0 ].indexOf( extra.key ) !== -1;
						return el(
							'label',
							{ key: extra.key, className: ui.cx( 'ffw-sw2-extra', on && 'is-on' ) },
							el( 'span', { className: 'ffw-sw2-extra-text' }, el( 'strong', null, extra.title ), el( 'span', null, extra.description ) ),
							el( ui.Toggle, { checked: on, label: null, onChange: function ( value ) {
								extrasState[ 1 ]( value ? extrasState[ 0 ].concat( [ extra.key ] ) : extrasState[ 0 ].filter( function ( key ) {
									return key !== extra.key;
								} ) );
							} } )
						);
					} )
				),
				el( 'p', { className: 'ffw-sw2-fine' }, __( 'Selected tools are free and will be installed from WordPress.org. You can deactivate them any time from Plugins.', D ) ),
				actions( el( ui.Button, { variant: 'brand', onClick: installExtras }, __( 'Continue', D ) ) )
			);
		} else {
			var count = resultState[ 0 ].count;
			body = el(
				'div',
				{ className: 'ffw-sw2-ready' },
				el( 'span', { className: 'ffw-sw2-badge' }, el( ui.Icon, { name: 'check', size: 30, stroke: 3 } ) ),
				el( 'h1', null, __( 'Welcome to Happy FAQs!', D ) ),
				el( 'p', { className: 'ffw-sw2-lead' }, __( 'You are all set. Build your FAQ library, show it anywhere, and let customers find answers on their own.', D ) ),
				el(
					'ul',
					{ className: 'ffw-sw2-summary' },
					count ? el( 'li', null, el( ui.Icon, { name: 'check', size: 15 } ), 1 === count ? __( '1 FAQ created', D ) : sprintf( __( '%d FAQs created', D ), count ) ) : null,
					'working' === toolsState[ 0 ] ? el( 'li', null, el( 'span', { className: 'ffw-ui-spin' } ), __( 'Setting up your selected tools…', D ) ) : null,
					'ready' === toolsState[ 0 ] ? el( 'li', null, el( ui.Icon, { name: 'check', size: 15 } ), __( 'Your selected tools are installed and active.', D ) ) : null,
					'partial' === toolsState[ 0 ] ? el( 'li', { className: 'is-warn' }, el( ui.Icon, { name: 'info', size: 15 } ), __( 'Some tools could not be set up automatically. You can add them later from Plugins.', D ) ) : null
				),
				el(
					'div',
					{ className: 'ffw-ui-row ffw-sw2-center' },
					el( ui.Button, { variant: 'brand', href: urls.dashboard }, __( 'Go to dashboard', D ) ),
					el( ui.Button, { icon: 'plus', href: urls.manager + '#/new' }, __( 'Add a new FAQ', D ) )
				),
				el(
					'p',
					{ className: 'ffw-sw2-links' },
					el( 'a', { href: urls.manager }, __( 'All FAQs', D ) ),
					el( 'a', { href: urls.helpCenter }, __( 'Help Center page', D ) ),
					el( 'a', { href: urls.patterns }, __( 'FAQ patterns', D ) ),
					el( 'a', { href: urls.settings }, __( 'Settings', D ) ),
					el( 'a', { href: urls.docs, target: '_blank', rel: 'noopener' }, __( 'Documentation', D ) )
				)
			);
		}

		return el(
			'div',
			{ className: 'ffw-sw2' },
			el(
				'header',
				{ className: 'ffw-sw2-header' },
				el( 'span', { className: 'ffw-sw2-brand' }, el( 'span', { className: 'ffw-sw2-mark', 'aria-hidden': 'true' }, '?' ), __( 'Happy FAQs', D ) ),
				el(
					'ol',
					{ className: 'ffw-sw2-progress', 'aria-label': __( 'Setup steps', D ) },
					steps.map( function ( key, index ) {
						var state = index < stepState[ 0 ] ? 'is-done' : index === stepState[ 0 ] ? 'is-current' : '';
						return el( 'li', { key: key, className: state, 'aria-current': index === stepState[ 0 ] ? 'step' : undefined }, el( 'span', { className: 'ffw-sw2-dot' }, index < stepState[ 0 ] ? '✓' : index + 1 ), el( 'span', { className: 'ffw-sw2-label' }, labels[ key ] ) );
					} )
				)
			),
			el( 'main', { className: 'ffw-sw2-card' }, body ),
			el(
				'footer',
				{ className: 'ffw-sw2-footer' },
				el( 'a', { href: urls.dashboard, onClick: function ( event ) {
					event.preventDefault();
					finish( 'skipped' ).then( function () {
						window.location.href = urls.dashboard;
					} );
				} }, __( 'Exit setup and go to the dashboard', D ) )
			)
		);
	}

	ui.mount( Wizard );
} )( window.wp, window.ffwUI, window.ffwPage );
