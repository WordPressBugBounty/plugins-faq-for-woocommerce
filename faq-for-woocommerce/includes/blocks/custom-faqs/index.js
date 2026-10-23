(function (wp) {
	'use strict';

	if (!wp || !wp.blocks) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var registerBlockType = wp.blocks.registerBlockType;
	var blockEditor = wp.blockEditor || wp.editor;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps = blockEditor.useBlockProps;
	var RichText = blockEditor.RichText;
	var ColorPalette = blockEditor.ColorPalette || (wp.components && wp.components.ColorPalette);
	var components = wp.components;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl || components.TextControl;
	var ToggleControl = components.ToggleControl;
	var RangeControl = components.RangeControl;
	var Button = components.Button;
	var Notice = components.Notice;
	var BaseControl = components.BaseControl;
	var __ = wp.i18n.__;
	var sprintf = wp.i18n.sprintf;
	var config = window.ffwCustomFaqsEditor || {};
	var layouts = config.layouts || [];

	var emptyStyleAttrs = {
		questionColor: '',
		questionBgColor: '',
		questionHoverColor: '',
		questionFontSize: 0,
		answerColor: '',
		answerBgColor: '',
		answerFontSize: 0,
		borderColor: '',
		borderRadius: 0,
		itemSpacing: 0,
		customCss: ''
	};

	function nextItemId() {
		return 'faq-' + Date.now() + '-' + Math.floor(Math.random() * 10000);
	}

	function getItems(attributes) {
		if (attributes.items && attributes.items.length) {
			return attributes.items.slice();
		}

		return [{ id: 'starter-1', question: '', answer: '' }];
	}

	function hasStyleOverrides(attributes) {
		return !!(
			attributes.questionColor ||
			attributes.questionBgColor ||
			attributes.questionHoverColor ||
			attributes.answerColor ||
			attributes.answerBgColor ||
			attributes.borderColor ||
			attributes.customCss ||
			attributes.questionFontSize ||
			attributes.answerFontSize ||
			attributes.borderRadius ||
			attributes.itemSpacing
		);
	}

	function ColorControl(label, value, onChange) {
		if (!ColorPalette) {
			return el(TextControl, {
				label: label,
				value: value || '',
				onChange: onChange
			});
		}

		return el(
			BaseControl,
			{ label: label, className: 'ffw-custom-faqs-color-control' },
			el(ColorPalette, {
				value: value || undefined,
				onChange: function (color) {
					onChange(color || '');
				}
			})
		);
	}

	function cssSafe(value) {
		return String(value || '').replace(/[<>{}]/g, '');
	}

	function editorPreviewCss(scope, attributes) {
		var css = '';
		var item = '.' + scope + ' .ffw-custom-faq-item';
		var question = item + ' .components-text-control__input';
		var answer = item + ' .ffw-custom-faq-answer';

		if (attributes.questionColor) {
			css += question + '{color:' + cssSafe(attributes.questionColor) + ';}';
		}
		if (attributes.questionBgColor) {
			css += item + '{background:' + cssSafe(attributes.questionBgColor) + ';}';
			css += question + '{background:' + cssSafe(attributes.questionBgColor) + ';}';
		}
		if (attributes.questionFontSize) {
			css += question + '{font-size:' + parseInt(attributes.questionFontSize, 10) + 'px;}';
		}
		if (attributes.answerColor) {
			css += answer + '{color:' + cssSafe(attributes.answerColor) + ';}';
		}
		if (attributes.answerBgColor) {
			css += answer + '{background:' + cssSafe(attributes.answerBgColor) + ';}';
		}
		if (attributes.answerFontSize) {
			css += answer + '{font-size:' + parseInt(attributes.answerFontSize, 10) + 'px;}';
		}
		if (attributes.borderColor) {
			css += item + '{border-color:' + cssSafe(attributes.borderColor) + ';}';
			css += answer + '{border-color:' + cssSafe(attributes.borderColor) + ';}';
		}
		if (attributes.borderRadius) {
			css += item + '{border-radius:' + attributes.borderRadius + 'px;}';
		}
		if (attributes.itemSpacing) {
			css += item + '{margin-bottom:' + attributes.itemSpacing + 'px;}';
		}

		return css;
	}

	function Edit(props) {
		var attributes = props.attributes;
		var setAttributes = props.setAttributes;
		var items = getItems(attributes);
		var previewClass = 'ffw-custom-faqs-editor-' + String(props.clientId || 'block').replace(/[^a-z0-9]/gi, '');
		var previewCss = editorPreviewCss(previewClass, attributes);
		var blockProps = useBlockProps
			? useBlockProps({ className: 'ffw-custom-faqs-block ' + previewClass })
			: { className: 'ffw-custom-faqs-block ' + previewClass };

		function updateItem(index, key, value) {
			var next = getItems(attributes);
			var updated = Object.assign({}, next[index]);
			updated[key] = value;
			next[index] = updated;
			setAttributes({ items: next });
		}

		function addItem() {
			var next = getItems(attributes);
			next.push({ id: nextItemId(), question: '', answer: '' });
			setAttributes({ items: next });
		}

		function removeItem(index) {
			var next = getItems(attributes);
			if (next.length < 2) {
				setAttributes({
					items: [{ id: nextItemId(), question: '', answer: '' }]
				});
				return;
			}
			next.splice(index, 1);
			setAttributes({ items: next });
		}

		function moveItem(index, direction) {
			var next = getItems(attributes);
			var target = index + direction;
			if (target < 0 || target >= next.length) {
				return;
			}
			var current = next[index];
			next[index] = next[target];
			next[target] = current;
			setAttributes({ items: next });
		}

		return el(
			Fragment,
			null,
			el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{
						title: __('Display', 'faq-for-woocommerce'),
						initialOpen: true
					},
					el(Notice, { status: 'info', isDismissible: false },
						__('These FAQs stay on this page. They are not added to the FAQ library.', 'faq-for-woocommerce')
					),
					el(SelectControl, {
						label: __('Display mode', 'faq-for-woocommerce'),
						value: attributes.displayMode,
						options: [
							{ value: 'accordion', label: __('Accordion', 'faq-for-woocommerce') },
							{ value: 'list', label: __('List', 'faq-for-woocommerce') }
						],
						onChange: function (value) {
							setAttributes({ displayMode: value });
						}
					}),
					attributes.displayMode !== 'list'
						? el(SelectControl, {
							label: __('Layout', 'faq-for-woocommerce'),
							value: String(attributes.layout),
							options: layouts,
							onChange: function (value) {
								setAttributes({ layout: parseInt(value, 10) || 0 });
							}
						})
						: null,
					attributes.displayMode !== 'list'
						? el(ToggleControl, {
							label: __('Expand first FAQ', 'faq-for-woocommerce'),
							checked: !!attributes.expandFirst,
							onChange: function (value) {
								setAttributes({ expandFirst: value });
							}
						})
						: null
				),
				el(
					PanelBody,
					{
						title: __('Search & schema', 'faq-for-woocommerce'),
						initialOpen: false
					},
					el(ToggleControl, {
						label: __('Show search box', 'faq-for-woocommerce'),
						help: __('Filters the FAQs on this page as you type.', 'faq-for-woocommerce'),
						checked: !!attributes.showSearch,
						onChange: function (value) {
							setAttributes({ showSearch: value });
						}
					}),
					el(ToggleControl, {
						label: __('Output FAQ schema', 'faq-for-woocommerce'),
						checked: !!attributes.enableSchema,
						onChange: function (value) {
							setAttributes({ enableSchema: value });
						}
					})
				),
				el(
					PanelBody,
					{
						title: __('Style', 'faq-for-woocommerce'),
						initialOpen: false
					},
					el(
						'p',
						{ className: 'ffw-custom-faqs-style-help' },
						__('Leave a control empty to keep the selected layout. Values here override layout and plugin colors for this block only.', 'faq-for-woocommerce')
					),
					ColorControl(
						__('Question color', 'faq-for-woocommerce'),
						attributes.questionColor,
						function (value) {
							setAttributes({ questionColor: value });
						}
					),
					ColorControl(
						__('Question background', 'faq-for-woocommerce'),
						attributes.questionBgColor,
						function (value) {
							setAttributes({ questionBgColor: value });
						}
					),
					ColorControl(
						__('Question hover / open', 'faq-for-woocommerce'),
						attributes.questionHoverColor,
						function (value) {
							setAttributes({ questionHoverColor: value });
						}
					),
					el(RangeControl, {
						label: __('Question size', 'faq-for-woocommerce'),
						help: __('0 keeps the layout size.', 'faq-for-woocommerce'),
						value: attributes.questionFontSize || 0,
						min: 0,
						max: 48,
						onChange: function (value) {
							setAttributes({ questionFontSize: value || 0 });
						}
					}),
					ColorControl(
						__('Answer color', 'faq-for-woocommerce'),
						attributes.answerColor,
						function (value) {
							setAttributes({ answerColor: value });
						}
					),
					ColorControl(
						__('Answer background', 'faq-for-woocommerce'),
						attributes.answerBgColor,
						function (value) {
							setAttributes({ answerBgColor: value });
						}
					),
					el(RangeControl, {
						label: __('Answer size', 'faq-for-woocommerce'),
						help: __('0 keeps the layout size.', 'faq-for-woocommerce'),
						value: attributes.answerFontSize || 0,
						min: 0,
						max: 48,
						onChange: function (value) {
							setAttributes({ answerFontSize: value || 0 });
						}
					}),
					ColorControl(
						__('Border color', 'faq-for-woocommerce'),
						attributes.borderColor,
						function (value) {
							setAttributes({ borderColor: value });
						}
					),
					el(RangeControl, {
						label: __('Corner radius', 'faq-for-woocommerce'),
						value: attributes.borderRadius || 0,
						min: 0,
						max: 48,
						onChange: function (value) {
							setAttributes({ borderRadius: value || 0 });
						}
					}),
					el(RangeControl, {
						label: __('Space between items', 'faq-for-woocommerce'),
						value: attributes.itemSpacing || 0,
						min: 0,
						max: 64,
						onChange: function (value) {
							setAttributes({ itemSpacing: value || 0 });
						}
					}),
					el(TextareaControl, {
						label: __('Custom CSS', 'faq-for-woocommerce'),
						help: __('Scoped to this block. Example: .ffw-button { letter-spacing: 0.02em; }', 'faq-for-woocommerce'),
						value: attributes.customCss || '',
						rows: 5,
						onChange: function (value) {
							setAttributes({ customCss: value });
						}
					}),
					hasStyleOverrides(attributes)
						? el(Button, {
							isSecondary: true,
							variant: 'secondary',
							isDestructive: true,
							onClick: function () {
								setAttributes(emptyStyleAttrs);
							}
						}, __('Reset styles', 'faq-for-woocommerce'))
						: null
				),
				window.ffwBlockControls ? window.ffwBlockControls.iconPanel(attributes, setAttributes) : null
			),
			el(
				'div',
				blockProps,
				previewCss ? el('style', null, previewCss) : null,
				el(
					'div',
					{ className: 'ffw-custom-faqs-editor' },
					el(
						'p',
						{ className: 'ffw-custom-faqs-editor-intro' },
						__('Write questions and answers here. Visitors see the same accordion as Happy Prebuilt FAQs. Style controls in the sidebar override this block only.', 'faq-for-woocommerce')
					),
					items.map(function (item, index) {
						return el(
							'div',
							{
								className: 'ffw-custom-faq-item',
								key: item.id || 'item-' + index
							},
							el(
								'div',
								{ className: 'ffw-custom-faq-item-toolbar' },
								el(
									'span',
									{ className: 'ffw-custom-faq-item-label' },
									sprintf(
										/* translators: %d: FAQ item number */
										__('FAQ %d', 'faq-for-woocommerce'),
										index + 1
									)
								),
								el(
									'div',
									{ className: 'ffw-custom-faq-item-actions' },
									el(Button, {
										isSmall: true,
										isSecondary: true,
										variant: 'secondary',
										disabled: index === 0,
										onClick: function () {
											moveItem(index, -1);
										}
									}, __('Up', 'faq-for-woocommerce')),
									el(Button, {
										isSmall: true,
										isSecondary: true,
										variant: 'secondary',
										disabled: index === items.length - 1,
										onClick: function () {
											moveItem(index, 1);
										}
									}, __('Down', 'faq-for-woocommerce')),
									el(Button, {
										isSmall: true,
										isDestructive: true,
										variant: 'tertiary',
										onClick: function () {
											removeItem(index);
										}
									}, __('Remove', 'faq-for-woocommerce'))
								)
							),
							el(TextControl, {
								label: __('Question', 'faq-for-woocommerce'),
								value: item.question || '',
								placeholder: __('Enter a question', 'faq-for-woocommerce'),
								onChange: function (value) {
									updateItem(index, 'question', value);
								}
							}),
							el(
								'div',
								{ className: 'components-base-control' },
								el(
									'label',
									{ className: 'components-base-control__label' },
									__('Answer', 'faq-for-woocommerce')
								),
								el(RichText, {
									tagName: 'div',
									className: 'ffw-custom-faq-answer',
									value: item.answer || '',
									placeholder: __('Write the answer…', 'faq-for-woocommerce'),
									onChange: function (value) {
										updateItem(index, 'answer', value);
									}
								})
							)
						);
					}),
					el(Button, {
						isPrimary: true,
						variant: 'primary',
						className: 'ffw-custom-faqs-add',
						onClick: addItem
					}, __('Add FAQ', 'faq-for-woocommerce'))
				)
			)
		);
	}

	registerBlockType('happy-faq/custom-faqs', {
		apiVersion: 2,
		title: __('Happy Custom FAQs', 'faq-for-woocommerce'),
		description: __('Write questions and answers on this page. They are not saved to the FAQ library.', 'faq-for-woocommerce'),
		icon: 'editor-ul',
		category: 'happy-faq',
		keywords: [
			__('faq', 'faq-for-woocommerce'),
			__('accordion', 'faq-for-woocommerce'),
			__('custom', 'faq-for-woocommerce'),
			__('questions', 'faq-for-woocommerce'),
			__('help', 'faq-for-woocommerce')
		],
		supports: {
			html: false,
			align: ['wide', 'full']
		},
		edit: Edit,
		save: function () {
			return null;
		}
	});
})(window.wp);
