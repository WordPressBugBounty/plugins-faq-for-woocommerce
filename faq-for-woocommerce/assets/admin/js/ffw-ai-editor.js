/**
 * Happy FAQs: "Improve with AI" panel in the FAQ block editor.
 *
 * @since 3.0.0
 */
(function (wp) {
	'use strict';

	if (!wp || !wp.plugins || !wp.data) {
		return;
	}

	var el = wp.element.createElement;
	var useState = wp.element.useState;
	var RawHTML = wp.element.RawHTML;
	var Panel = (wp.editor && wp.editor.PluginDocumentSettingPanel) || (wp.editPost && wp.editPost.PluginDocumentSettingPanel);
	var Button = wp.components.Button;
	var Notice = wp.components.Notice;
	var Spinner = wp.components.Spinner;
	var __ = wp.i18n.__;
	var config = window.ffwAI || {};
	var i18n = config.i18n || {};

	if (!Panel) {
		return;
	}

	function request(data) {
		var body = new window.FormData();
		body.append('action', 'ffw_ai_improve');
		body.append('nonce', config.nonce);
		Object.keys(data).forEach(function (key) {
			body.append(key, data[key]);
		});

		return window.fetch(config.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (response) {
				return response.json().catch(function () {
					return {};
				});
			})
			.then(function (result) {
				if (result && result.success) {
					return result.data.answer;
				}
				throw new Error((result && result.data && result.data.message) || i18n.error);
			});
	}

	function ImprovePanel() {
		var busyState = useState(false);
		var busy = busyState[0];
		var setBusy = busyState[1];
		var suggestionState = useState('');
		var suggestion = suggestionState[0];
		var setSuggestion = suggestionState[1];
		var messageState = useState(null);
		var message = messageState[0];
		var setMessage = messageState[1];

		var status = config.status || {};
		var ready = status.engine && status.engine !== 'none';

		function run(mode) {
			var editor = wp.data.select('core/editor');
			var answer = editor.getEditedPostContent();
			var text = answer.replace(/<!--[\s\S]*?-->/g, '').replace(/<[^>]+>/g, '').trim();

			if (!text) {
				setMessage({ status: 'warning', text: i18n.noAnswer });
				return;
			}

			setBusy(true);
			setMessage(null);
			setSuggestion('');

			request({ question: editor.getEditedPostAttribute('title') || '', answer: answer, mode: mode })
				.then(function (html) {
					setSuggestion(html);
				})
				.catch(function (error) {
					setMessage({ status: 'error', text: error.message });
				})
				.then(function () {
					setBusy(false);
				});
		}

		function apply() {
			var blocks = wp.blocks.rawHandler ? wp.blocks.rawHandler({ HTML: suggestion }) : wp.blocks.parse(suggestion);
			wp.data.dispatch('core/block-editor').resetBlocks(blocks);
			setSuggestion('');
			setMessage({ status: 'success', text: i18n.replaced });
		}

		var children = [];

		if (!ready) {
			children.push(
				el('p', { key: 'setup', className: 'ffw-ai-panel-help' }, status.label + '. ' + status.help)
			);
			if (config.connectorsUrl) {
				children.push(el(Button, { key: 'connect', variant: 'link', href: config.connectorsUrl }, __('Open Connectors', 'faq-for-woocommerce')));
			}
			children.push(el(Button, { key: 'settings', variant: 'link', href: config.settingsUrl }, __('AI settings', 'faq-for-woocommerce')));
			return el(Panel, { name: 'ffw-ai-improve', title: __('Improve with AI', 'faq-for-woocommerce') }, children);
		}

		children.push(
			el('p', { key: 'help', className: 'ffw-ai-panel-help' }, __('Rewrite the answer. You can review it before it replaces the current one.', 'faq-for-woocommerce'))
		);

		children.push(
			el(
				'div',
				{ key: 'modes', style: { display: 'flex', flexWrap: 'wrap', gap: '6px' } },
				(config.modes || []).map(function (mode) {
					return el(Button, {
						key: mode.value,
						variant: 'secondary',
						size: 'compact',
						disabled: busy,
						onClick: function () {
							run(mode.value);
						}
					}, mode.label);
				})
			)
		);

		if (busy) {
			children.push(el('p', { key: 'busy', style: { marginTop: '12px' } }, el(Spinner), ' ', i18n.improving));
		}

		if (message) {
			children.push(
				el(Notice, { key: 'message', status: message.status, isDismissible: true, onRemove: function () { setMessage(null); } }, message.text)
			);
		}

		if (suggestion) {
			children.push(
				el(
					'div',
					{ key: 'suggestion', style: { marginTop: '12px' } },
					el(
						'div',
						{
							className: 'ffw-ai-panel-preview',
							style: { maxHeight: '280px', overflowY: 'auto', padding: '8px 10px', border: '1px solid #e0e0e0', borderRadius: '4px', background: '#f6f7f7', fontSize: '13px' }
						},
						el(RawHTML, null, suggestion)
					),
					el(
						'div',
						{ style: { display: 'flex', gap: '8px', marginTop: '8px' } },
						el(Button, { variant: 'primary', onClick: apply }, __('Replace answer', 'faq-for-woocommerce')),
						el(Button, { variant: 'tertiary', onClick: function () { setSuggestion(''); } }, __('Discard', 'faq-for-woocommerce'))
					)
				)
			);
		}

		return el(Panel, { name: 'ffw-ai-improve', title: __('Improve with AI', 'faq-for-woocommerce'), initialOpen: true }, children);
	}

	wp.plugins.registerPlugin('ffw-ai-improve', {
		render: ImprovePanel,
		icon: 'superhero'
	});
})(window.wp);
