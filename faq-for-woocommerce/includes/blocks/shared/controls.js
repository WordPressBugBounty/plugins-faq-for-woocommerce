/**
 * Shared block controls: toggle icon picker and per-block style panel.
 *
 * Used by Happy Prebuilt FAQs and Happy Custom FAQs.
 *
 * @since 3.0.0
 */
(function (wp) {
	'use strict';

	if (!wp || !wp.element) {
		return;
	}

	var el = wp.element.createElement;
	var RawHTML = wp.element.RawHTML;
	var blockEditor = wp.blockEditor || wp.editor;
	var ColorPalette = blockEditor.ColorPalette || (wp.components && wp.components.ColorPalette);
	var components = wp.components;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var RangeControl = components.RangeControl;
	var BaseControl = components.BaseControl;
	var TextControl = components.TextControl;
	var __ = wp.i18n.__;
	var config = window.ffwBlockIcons || {};
	var sets = config.sets || [];

	function colorControl(label, value, onChange) {
		if (!ColorPalette) {
			return el(TextControl, { label: label, value: value || '', onChange: onChange });
		}

		return el(
			BaseControl,
			{ label: label, className: 'ffw-block-color-control' },
			el(ColorPalette, {
				value: value || undefined,
				onChange: function (color) {
					onChange(color || '');
				}
			})
		);
	}

	// A grid of icon buttons; each shows the closed and the open state.
	function iconGrid(value, onChange) {
		var choices = [
			{ value: '', label: __('Site setting', 'faq-for-woocommerce'), text: '•' },
			{ value: 'default', label: __('Layout default', 'faq-for-woocommerce'), text: 'Aa' },
			{ value: 'none', label: __('No icon', 'faq-for-woocommerce'), text: '—' }
		].concat(sets);

		return el(
			BaseControl,
			{ label: __('Icon', 'faq-for-woocommerce'), className: 'ffw-block-icon-control' },
			el(
				'div',
				{ className: 'ffw-block-icon-grid', role: 'radiogroup', 'aria-label': __('Toggle icon', 'faq-for-woocommerce') },
				choices.map(function (choice) {
					var selected = (value || '') === choice.value;
					return el(
						'button',
						{
							key: choice.value || 'site',
							type: 'button',
							role: 'radio',
							'aria-checked': selected ? 'true' : 'false',
							title: choice.label,
							className: 'ffw-block-icon-choice' + (selected ? ' is-selected' : ''),
							onClick: function () {
								onChange(choice.value);
							}
						},
						choice.svg
							? el(
								'span',
								{ className: 'ffw-block-icon-preview', 'aria-hidden': 'true' },
								el(RawHTML, null, choice.svg),
								el('span', { className: 'ffw-accordion-active' }, el(RawHTML, null, choice.svg))
							)
							: el('span', { className: 'ffw-block-icon-text', 'aria-hidden': 'true' }, choice.text),
						el('span', { className: 'ffw-block-icon-label' }, choice.label)
					);
				})
			)
		);
	}

	/**
	 * "Toggle icon" panel.
	 *
	 * @param {Object}   attributes    Block attributes.
	 * @param {Function} setAttributes Setter.
	 * @return {Object} Element.
	 */
	function iconPanel(attributes, setAttributes) {
		var custom = attributes.iconSet && 'default' !== attributes.iconSet && 'none' !== attributes.iconSet;

		return el(
			PanelBody,
			{ title: __('Toggle icon', 'faq-for-woocommerce'), initialOpen: false },
			el(
				'p',
				{ className: 'ffw-block-help' },
				config.siteLabel
					? __('Site setting:', 'faq-for-woocommerce') + ' ' + config.siteLabel
					: __('Choose the open and close icon for this list.', 'faq-for-woocommerce')
			),
			iconGrid(attributes.iconSet, function (value) {
				setAttributes({ iconSet: value });
			}),
			custom || !attributes.iconSet
				? [
					el(SelectControl, {
						key: 'position',
						label: __('Position', 'faq-for-woocommerce'),
						value: attributes.iconPosition || '',
						options: [
							{ value: '', label: __('Site setting', 'faq-for-woocommerce') },
							{ value: 'right', label: __('Right of the question', 'faq-for-woocommerce') },
							{ value: 'left', label: __('Left of the question', 'faq-for-woocommerce') }
						],
						onChange: function (value) {
							setAttributes({ iconPosition: value });
						}
					}),
					el(RangeControl, {
						key: 'size',
						label: __('Size', 'faq-for-woocommerce'),
						help: __('0 uses the site setting (20px by default).', 'faq-for-woocommerce'),
						value: attributes.iconSize || 0,
						min: 0,
						max: 48,
						onChange: function (value) {
							setAttributes({ iconSize: value || 0 });
						}
					}),
					el(
						'div',
						{ key: 'color' },
						colorControl(__('Color', 'faq-for-woocommerce'), attributes.iconColor, function (value) {
							setAttributes({ iconColor: value });
						})
					)
				]
				: null
		);
	}

	/**
	 * "Style" panel: colors, sizes, border and spacing for this list.
	 *
	 * @param {Object}   attributes    Block attributes.
	 * @param {Function} setAttributes Setter.
	 * @return {Object} Element.
	 */
	function stylePanel(attributes, setAttributes) {
		function color(label, key) {
			return colorControl(label, attributes[key], function (value) {
				var next = {};
				next[key] = value;
				setAttributes(next);
			});
		}

		function range(label, key, max, help) {
			return el(RangeControl, {
				label: label,
				help: help,
				value: attributes[key] || 0,
				min: 0,
				max: max,
				onChange: function (value) {
					var next = {};
					next[key] = value || 0;
					setAttributes(next);
				}
			});
		}

		var keep = __('0 keeps the layout size.', 'faq-for-woocommerce');

		return el(
			PanelBody,
			{ title: __('Style', 'faq-for-woocommerce'), initialOpen: false },
			el(
				'p',
				{ className: 'ffw-block-help' },
				__('Leave a control empty to keep the layout. Values here apply to this block only.', 'faq-for-woocommerce')
			),
			color(__('Question color', 'faq-for-woocommerce'), 'questionColor'),
			color(__('Question background', 'faq-for-woocommerce'), 'questionBgColor'),
			color(__('Question hover / open', 'faq-for-woocommerce'), 'questionHoverColor'),
			range(__('Question size', 'faq-for-woocommerce'), 'questionFontSize', 48, keep),
			color(__('Answer color', 'faq-for-woocommerce'), 'answerColor'),
			color(__('Answer background', 'faq-for-woocommerce'), 'answerBgColor'),
			range(__('Answer size', 'faq-for-woocommerce'), 'answerFontSize', 48, keep),
			color(__('Border color', 'faq-for-woocommerce'), 'borderColor'),
			range(__('Corner radius', 'faq-for-woocommerce'), 'borderRadius', 48),
			range(__('Space between items', 'faq-for-woocommerce'), 'itemSpacing', 64)
		);
	}

	window.ffwBlockControls = {
		iconPanel: iconPanel,
		stylePanel: stylePanel
	};
})(window.wp);
