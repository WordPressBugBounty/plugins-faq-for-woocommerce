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
	var components = wp.components;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;
	var ToggleControl = components.ToggleControl;
	var RangeControl = components.RangeControl;
	var Disabled = components.Disabled;
	var Notice = components.Notice;
	var __ = wp.i18n.__;
	var ServerSideRender = wp.serverSideRender
		? wp.serverSideRender
		: wp.components.ServerSideRender;
	var config = window.ffwBlockEditor || {};
	var layouts = config.layouts || [];
	var categories = config.categories || [];
	var groups = config.groups || [];
	var wooActive = !!config.wooActive;

	function sourceOptions() {
		if (config.sources && config.sources.length) {
			return config.sources;
		}

		var options = [
			{ value: 'all', label: __('All FAQs', 'faq-for-woocommerce') },
			{ value: 'faq_cat', label: __('FAQ category', 'faq-for-woocommerce') },
			{ value: 'faq_ids', label: __('Selected FAQ IDs', 'faq-for-woocommerce') }
		];

		if (wooActive) {
			options.push({
				value: 'current_product',
				label: __('Current product', 'faq-for-woocommerce')
			});
			options.push({
				value: 'product',
				label: __('Product ID', 'faq-for-woocommerce')
			});
		}

		return options;
	}

	function Edit(props) {
		var attributes = props.attributes;
		var setAttributes = props.setAttributes;
		var blockProps = useBlockProps
			? useBlockProps({ className: 'ffw-display-faqs-block' })
			: { className: 'ffw-display-faqs-block' };

		var sourceHelp = '';
		if (attributes.source === 'current_product') {
			sourceHelp = __('These FAQs appear on product pages.', 'faq-for-woocommerce');
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
						title: __('FAQ source', 'faq-for-woocommerce'),
						initialOpen: true
					},
					el(SelectControl, {
						label: __('Source', 'faq-for-woocommerce'),
						value: attributes.source,
						options: sourceOptions(),
						onChange: function (value) {
							setAttributes({ source: value });
						}
					}),
					attributes.source === 'faq_cat'
						? el(SelectControl, {
							label: __('FAQ category', 'faq-for-woocommerce'),
							value: attributes.faqCat,
							options: [{ value: '', label: __('Select a category', 'faq-for-woocommerce') }].concat(categories),
							onChange: function (value) {
								setAttributes({ faqCat: value });
							}
						})
						: null,
					attributes.source === 'faq_ids'
						? el(TextControl, {
							label: __('FAQ IDs', 'faq-for-woocommerce'),
							help: __('Comma-separated FAQ post IDs.', 'faq-for-woocommerce'),
							value: attributes.faqIds,
							onChange: function (value) {
								setAttributes({ faqIds: value });
							}
						})
						: null,
					attributes.source === 'group'
						? el(SelectControl, {
							label: __('FAQ Group', 'faq-for-woocommerce'),
							value: attributes.groupId ? String(attributes.groupId) : '',
							options: [{ value: '', label: __('Select a group', 'faq-for-woocommerce') }].concat(groups),
							onChange: function (value) {
								setAttributes({ groupId: parseInt(value, 10) || 0 });
							}
						})
						: null,
					attributes.source === 'product' && wooActive
						? el(TextControl, {
							label: __('Product ID', 'faq-for-woocommerce'),
							type: 'number',
							value: attributes.productId || '',
							onChange: function (value) {
								setAttributes({ productId: parseInt(value, 10) || 0 });
							}
						})
						: null,
					sourceHelp
						? el(Notice, { status: 'info', isDismissible: false }, sourceHelp)
						: null
				),
				el(
					PanelBody,
					{
						title: __('Display', 'faq-for-woocommerce'),
						initialOpen: true
					},
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
					el(RangeControl, {
						label: __('Limit', 'faq-for-woocommerce'),
						help: __('0 shows all matching FAQs.', 'faq-for-woocommerce'),
						value: attributes.limit,
						min: 0,
						max: 50,
						onChange: function (value) {
							setAttributes({ limit: value || 0 });
						}
					}),
					el(RangeControl, {
						label: __('FAQs before "Show more"', 'faq-for-woocommerce'),
						help: __('0 shows all FAQs at once. Hidden FAQs still count for search, filters and schema.', 'faq-for-woocommerce'),
						value: attributes.perPage,
						min: 0,
						max: 50,
						onChange: function (value) {
							setAttributes({ perPage: value || 0 });
						}
					}),
					el(SelectControl, {
						label: __('Order by', 'faq-for-woocommerce'),
						value: attributes.orderBy,
						options: [
							{ value: 'id', label: __('ID', 'faq-for-woocommerce') },
							{ value: 'title', label: __('Question', 'faq-for-woocommerce') },
							{ value: 'date', label: __('Date', 'faq-for-woocommerce') }
						],
						onChange: function (value) {
							setAttributes({ orderBy: value });
						}
					}),
					el(SelectControl, {
						label: __('Order', 'faq-for-woocommerce'),
						value: attributes.order,
						options: [
							{ value: 'ASC', label: __('Ascending', 'faq-for-woocommerce') },
							{ value: 'DESC', label: __('Descending', 'faq-for-woocommerce') }
						],
						onChange: function (value) {
							setAttributes({ order: value });
						}
					}),
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
						title: __('Search, filter & schema', 'faq-for-woocommerce'),
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
						label: __('Show category filter', 'faq-for-woocommerce'),
						help: __('Buttons above the FAQs to show one FAQ category at a time.', 'faq-for-woocommerce'),
						checked: !!attributes.showFilter,
						onChange: function (value) {
							setAttributes({ showFilter: value });
						}
					}),
					el(ToggleControl, {
						label: __('Output FAQ schema', 'faq-for-woocommerce'),
						checked: !!attributes.enableSchema,
						onChange: function (value) {
							setAttributes({ enableSchema: value });
						}
					})
				)
			),
			el(
				'div',
				blockProps,
				el(
					Disabled,
					null,
					el(ServerSideRender, {
						block: 'happy-faq/display-faqs',
						attributes: attributes
					})
				)
			)
		);
	}

	registerBlockType('happy-faq/display-faqs', {
		apiVersion: 2,
		title: __('Happy Prebuilt FAQs', 'faq-for-woocommerce'),
		description: __('Display FAQs from your Happy FAQs library.', 'faq-for-woocommerce'),
		icon: 'editor-help',
		category: 'happy-faq',
		keywords: [
			__('faq', 'faq-for-woocommerce'),
			__('accordion', 'faq-for-woocommerce'),
			__('prebuilt', 'faq-for-woocommerce'),
			__('library', 'faq-for-woocommerce'),
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
