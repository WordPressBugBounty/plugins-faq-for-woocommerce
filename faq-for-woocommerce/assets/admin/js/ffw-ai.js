/**
 * Happy FAQs → AI FAQs screen, and "Improve with AI" in the classic editor.
 *
 * @since 3.0.0
 */
(function ($) {
	'use strict';

	var config = window.ffwAI || {};
	var i18n = config.i18n || {};

	function post(action, data) {
		return $.ajax({
			url: config.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: $.extend({ action: action, nonce: config.nonce }, data || {})
		}).then(function (response) {
			if (response && response.success) {
				return response.data;
			}
			return $.Deferred().reject(response && response.data && response.data.message ? response.data.message : i18n.error).promise();
		}, function (xhr) {
			var message = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message;
			return $.Deferred().reject(message || i18n.error).promise();
		});
	}

	function format(text, values) {
		var i = 0;
		return String(text || '').replace(/%(\d\$)?d/g, function (match, pos) {
			var index = pos ? parseInt(pos, 10) - 1 : i++;
			return values[index];
		});
	}

	/* ------------------------------------------------------------------
	 * AI FAQs screen.
	 * ------------------------------------------------------------------ */

	function initScreen($root) {
		var $form = $root.find('#ffw-aig-form');
		var $output = $root.find('.ffw-aig-output');
		var $results = $output.find('.ffw-aig-results-form');
		var $items = $output.find('.ffw-aig-items');
		var $search = $form.find('#ffw-aig-search');
		var $list = $form.find('#ffw-aig-results');
		var $chosen = $form.find('.ffw-aig-chosen');
		var $sourceId = $form.find('[name="source_id"]');
		var lastRequest = null;
		var lastSource = '';
		var searchTimer;
		var searchXhr = 0;

		// Tabs.
		$root.on('click', '.ffw-aig-tab', function () {
			var tab = $(this).data('tab');
			$root.find('.ffw-aig-tab').removeClass('is-active').attr('aria-selected', 'false');
			$(this).addClass('is-active').attr('aria-selected', 'true');
			$root.find('.ffw-aig-panel').attr('hidden', true);
			$root.find('#ffw-aig-' + tab).removeAttr('hidden');
			$root.trigger('ffw-ai-tab', [tab]);
		});

		// Open a tab from the URL, e.g. #gaps.
		if (window.location.hash) {
			$root.find('.ffw-aig-tab[data-tab="' + window.location.hash.slice(1).replace(/[^a-z]/g, '') + '"]').trigger('click');
		}

		function source() {
			return $form.find('[name="source"]:checked').val() || 'page';
		}

		function showFor($scope, value) {
			$scope.find('[data-show-for]').each(function () {
				var shown = String($(this).data('show-for')).split(' ').indexOf(value) !== -1;
				$(this).prop('hidden', !shown);
			});
		}

		function clearChoice() {
			$sourceId.val('');
			$chosen.attr('hidden', true);
			$search.val('').closest('.ffw-aig-combo').removeAttr('hidden');
		}

		function onSourceChange() {
			var value = source();
			showFor($form, value);
			var $label = $form.find('.ffw-aig-label[for="ffw-aig-search"]');
			$label.text(value === 'product' ? $label.data('label-product') : $label.data('label-page'));
			clearChoice();
			closeList();
		}

		$form.on('change', '[name="source"]', onSourceChange);
		onSourceChange();

		// Source search (combobox).
		function closeList() {
			$list.attr('hidden', true).empty();
			$search.attr('aria-expanded', 'false');
		}

		function renderList(items) {
			$list.empty();
			if (!items.length) {
				$list.append($('<li role="option" aria-disabled="true">').text(i18n.noResults));
			}
			items.forEach(function (item, index) {
				var $li = $('<li role="option">')
					.attr({ id: 'ffw-aig-opt-' + index, 'data-id': item.id, 'data-title': item.title, 'data-type': item.type })
					.append($('<span>').text(item.title || '#' + item.id))
					.append($('<small>').text(item.type + (item.status ? ' · ' + item.status : '')));
				$list.append($li);
			});
			$list.removeAttr('hidden');
			$search.attr('aria-expanded', 'true');
		}

		function runSearch() {
			var ticket = ++searchXhr;
			post('ffw_ai_search_sources', { q: $search.val(), kind: source() }).then(function (data) {
				if (ticket === searchXhr) {
					renderList(data.items || []);
				}
			});
		}

		$search.on('input focus', function () {
			clearTimeout(searchTimer);
			searchTimer = setTimeout(runSearch, 250);
		});

		function choose($li) {
			if (!$li.length || $li.attr('aria-disabled')) {
				return;
			}
			$sourceId.val($li.data('id'));
			$chosen.find('.ffw-aig-chosen-type').text($li.data('type'));
			$chosen.find('.ffw-aig-chosen-title').text($li.data('title'));
			$chosen.removeAttr('hidden');
			$search.closest('.ffw-aig-combo').attr('hidden', true);
			closeList();
		}

		$list.on('mousedown', 'li', function (event) {
			event.preventDefault();
			choose($(this));
		});

		$search.on('keydown', function (event) {
			var $options = $list.find('li[role="option"]:not([aria-disabled])');
			var $current = $options.filter('[aria-selected="true"]');
			var index = $options.index($current);

			if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
				event.preventDefault();
				index = event.key === 'ArrowDown' ? Math.min($options.length - 1, index + 1) : Math.max(0, index - 1);
				$options.attr('aria-selected', 'false');
				var $next = $options.eq(index).attr('aria-selected', 'true');
				$search.attr('aria-activedescendant', $next.attr('id') || '');
			} else if (event.key === 'Enter') {
				if ($current.length) {
					event.preventDefault();
					choose($current);
				}
			} else if (event.key === 'Escape') {
				closeList();
			}
		});

		$search.on('blur', function () {
			setTimeout(closeList, 150);
		});

		$form.on('click', '[data-ffw-clear]', function () {
			clearChoice();
			$search.trigger('focus');
		});

		// Generate.
		function showState(state) {
			$output.find('.ffw-aig-empty').prop('hidden', state !== 'empty');
			$output.find('.ffw-aig-loading').prop('hidden', state !== 'loading');
			$results.prop('hidden', state !== 'results');
			$output.find('.ffw-aig-done').prop('hidden', state !== 'done');
		}

		function showError(message) {
			$form.find('.ffw-aig-error').text(message || i18n.error).removeAttr('hidden');
		}

		function updateCount() {
			var $checks = $items.find('input[type="checkbox"]');
			var checked = $checks.filter(':checked').length;
			$output.find('.ffw-aig-count').text(format(i18n.selected, [checked]));
			$output.find('[data-ffw-select-all]').prop('checked', checked === $checks.length);
			$items.find('.ffw-aig-item').each(function () {
				$(this).toggleClass('is-checked', $(this).find('input[type="checkbox"]').is(':checked'));
			});
			$results.find('button[type="submit"]').prop('disabled', !checked);
		}

		function renderFaqs(faqs, title) {
			$items.empty();
			faqs.forEach(function (faq, index) {
				var id = 'ffw-aig-item-' + index;
				var $li = $('<li class="ffw-aig-item is-checked">');
				$li.append($('<input type="checkbox" checked>').attr('aria-label', faq.question));
				var $body = $('<div class="ffw-aig-item-body">');
				$body.append($('<input type="text" class="ffw-aig-q">').attr('id', id).val(faq.question));
				$body.append($('<textarea class="ffw-aig-a" rows="3">').val(faq.answer));
				$li.append($body);
				$items.append($li);
			});
			$output.find('.ffw-aig-for').text(title || '');
			showFor($results, lastSource);
			updateCount();
		}

		function generate(request) {
			lastRequest = request;
			lastSource = request.source;
			$form.find('.ffw-aig-error').attr('hidden', true);
			var $button = $form.find('button[type="submit"]').prop('disabled', true);
			showState('loading');

			post('ffw_ai_generate', request).then(function (data) {
				renderFaqs(data.faqs || [], data.title);
				showState('results');
			}, function (message) {
				showState('empty');
				showError(message);
			}).always(function () {
				$button.prop('disabled', config.status && config.status.engine === 'none');
			});
		}

		$form.on('submit', function (event) {
			event.preventDefault();
			var request = {
				source: source(),
				source_id: $sourceId.val(),
				topic: $form.find('[name="topic"]').val(),
				count: $form.find('[name="count"]').val(),
				focus: $form.find('[name="focus"]').val(),
				language: $form.find('#ffw-aig-language').val()
			};

			if (request.source !== 'topic' && !request.source_id) {
				showError(i18n.chooseSource);
				$search.trigger('focus');
				return;
			}

			generate(request);
		});

		$output.on('click', '[data-ffw-regenerate]', function () {
			if (lastRequest) {
				generate(lastRequest);
			}
		});

		$output.on('change', '.ffw-aig-item input[type="checkbox"]', updateCount);
		$output.on('change', '[data-ffw-select-all]', function () {
			$items.find('input[type="checkbox"]').prop('checked', this.checked);
			updateCount();
		});

		// Save.
		$results.on('submit', function (event) {
			event.preventDefault();
			var data = {
				source: lastRequest ? lastRequest.source : '',
				source_id: lastRequest ? lastRequest.source_id : 0,
				status: $results.find('[name="status"]').val(),
				faq_cat: $results.find('[name="faq_cat"]').val() || 0,
				add_to_page: $results.find('[name="add_to_page"]').is(':checked') ? '1' : ''
			};
			var n = 0;
			$items.find('.ffw-aig-item').each(function () {
				if ($(this).find('input[type="checkbox"]').is(':checked')) {
					data['items[' + n + '][question]'] = $(this).find('.ffw-aig-q').val();
					data['items[' + n + '][answer]'] = $(this).find('.ffw-aig-a').val();
					n++;
				}
			});

			var $button = $results.find('button[type="submit"]');
			var label = $button.text();
			$button.prop('disabled', true).text(i18n.saving);

			post('ffw_ai_insert', data).then(function (result) {
				var $done = $output.find('.ffw-aig-done');
				var text = format(result.status === 'draft' ? i18n.drafted : i18n.inserted, [result.count]);
				if (result.addedToPage) {
					text += ' ' + i18n.addedToPage;
				}
				$done.find('.ffw-aig-done-text').text(text);
				var $links = $done.find('.ffw-aig-done-links').empty();
				$links.append($('<a>').attr('href', result.listUrl).text(i18n.viewFaqs));
				if (result.targetUrl) {
					$links.append($('<a target="_blank" rel="noopener">').attr('href', result.targetUrl).text(i18n.viewPage));
				}
				showState('done');
			}, function (message) {
				window.alert(message);
			}).always(function () {
				$button.prop('disabled', false).text(label);
			});
		});

		$output.on('click', '[data-ffw-start-over]', function () {
			$items.empty();
			clearChoice();
			showState('empty');
		});

		initBulk($root);
	}

	/* ------------------------------------------------------------------
	 * Bulk (endpoints from Happy FAQs Pro).
	 * ------------------------------------------------------------------ */

	function initBulk($root) {
		var $form = $root.find('#ffw-aig-bulk-form');
		if (!$form.length) {
			return;
		}

		var $out = $root.find('.ffw-aig-bulk-output');
		var $progress = $out.find('.ffw-aig-progress');
		var $log = $out.find('.ffw-aig-log');
		var stopped = false;

		function setProgress(done, total) {
			$progress.find('.ffw-aig-progress-text').text(format(i18n.bulkProgress, [done, total]));
			$progress.find('.ffw-aig-bar span').css('width', (total ? Math.round(done / total * 100) : 0) + '%');
		}

		$out.on('click', '[data-ffw-bulk-stop]', function () {
			stopped = true;
		});

		$form.on('submit', function (event) {
			event.preventDefault();
			var $button = $form.find('button[type="submit"]').prop('disabled', true);
			var count = $form.find('[name="count"]').val();
			var language = $form.find('#ffw-aig-bulk-language').val();
			var drafts = 0;
			stopped = false;

			$form.find('.ffw-aig-error').attr('hidden', true);
			$out.find('.ffw-aig-empty').attr('hidden', true);
			$progress.removeAttr('hidden');
			$progress.find('.ffw-aig-done-links').empty();
			$progress.find('[data-ffw-bulk-stop]').removeAttr('hidden');
			$log.empty();

			post('ffw_pro_ai_bulk_list', { scope: $form.find('[name="scope"]').val() }).then(function (data) {
				var products = data.products || [];
				var done = 0;
				setProgress(0, products.length);

				if (!products.length) {
					$progress.find('.ffw-aig-progress-text').text(i18n.bulkNone);
					$progress.find('[data-ffw-bulk-stop]').attr('hidden', true);
					$button.prop('disabled', false);
					return;
				}

				function next() {
					if (stopped || done >= products.length) {
						finish();
						return;
					}
					var product = products[done];
					var $row = $('<li>').append($('<span>').text(product.title)).append($('<small>').text('…'));
					$log.prepend($row);

					post('ffw_pro_ai_bulk_item', { product_id: product.id, count: count, language: language }).then(function (result) {
						drafts += result.count || 0;
						$row.addClass('is-ok').find('small').text('+' + (result.count || 0));
					}, function (message) {
						$row.addClass('is-error').find('small').text(message);
					}).always(function () {
						done++;
						setProgress(done, products.length);
						next();
					});
				}

				function finish() {
					$progress.find('.ffw-aig-progress-text').text(format(i18n.bulkDone, [drafts]));
					$progress.find('[data-ffw-bulk-stop]').attr('hidden', true);
					$progress.find('.ffw-aig-done-links').append($('<a>').attr('href', data.draftsUrl).text(i18n.viewFaqs));
					$button.prop('disabled', false);
				}

				next();
			}, function (message) {
				$form.find('.ffw-aig-error').text(message).removeAttr('hidden');
				$progress.attr('hidden', true);
				$out.find('.ffw-aig-empty').removeAttr('hidden');
				$button.prop('disabled', false);
			});
		});
	}

	/* ------------------------------------------------------------------
	 * Classic editor: "Improve with AI".
	 * ------------------------------------------------------------------ */

	function getClassicAnswer() {
		var editor = window.tinymce && window.tinymce.get('content');
		if (editor && !editor.isHidden()) {
			return editor.getContent();
		}
		return $('#content').val() || '';
	}

	function setClassicAnswer(html) {
		var editor = window.tinymce && window.tinymce.get('content');
		if (editor && !editor.isHidden()) {
			editor.setContent(html);
			editor.fire('change');
		}
		$('#content').val(html).trigger('change');
	}

	function initClassic($box) {
		var suggestion = '';
		var $message = $box.find('.ffw-ai-improve-message');
		var $result = $box.find('.ffw-ai-improve-result');

		$box.on('click', '[data-mode]', function () {
			var answer = getClassicAnswer();
			if (!$.trim($('<div>').html(answer).text())) {
				$message.text(i18n.noAnswer);
				return;
			}

			var $buttons = $box.find('[data-mode]').prop('disabled', true);
			$message.text(i18n.improving);
			$result.attr('hidden', true);

			post('ffw_ai_improve', { question: $('#title').val(), answer: answer, mode: $(this).data('mode') }).then(function (data) {
				suggestion = data.answer;
				$box.find('.ffw-ai-improve-preview').html(suggestion);
				$result.removeAttr('hidden');
				$message.text('');
			}, function (message) {
				$message.text(message);
			}).always(function () {
				$buttons.prop('disabled', false);
			});
		});

		$box.on('click', '[data-ffw-ai-apply]', function () {
			setClassicAnswer(suggestion);
			$result.attr('hidden', true);
			$message.text(i18n.replaced);
		});

		$box.on('click', '[data-ffw-ai-discard]', function () {
			$result.attr('hidden', true);
			$message.text('');
		});
	}

	$(function () {
		var $root = $('.ffw-aig');
		if ($root.length && $root.find('#ffw-aig-form').length) {
			initScreen($root);
		}

		$('[data-ffw-ai-improve]').each(function () {
			initClassic($(this));
		});
	});
})(jQuery);
