/**
 * Filter rendered FAQs as the visitor types, or by category.
 *
 * An item is shown only when it matches both the search text and the
 * selected category.
 *
 * @since 1.8.18
 */
(function ($) {
	'use strict';

	function getRoot($el) {
		var $root = $el.closest('#ffw-main-wrapper, .ffw-main-wrapper');

		if (!$root.length) {
			$root = $el.closest('.ffw-inline-search-wrap').parent();
		}

		return $root;
	}

	// Every layout marks its items with the FAQ ID.
	function getItems($root) {
		return $root.find('[data-ffw-faq-id], [data-ffw-custom-faq-id]');
	}

	function updateEmptyMessage($root, visible, active) {
		var $empty = $root.find('.ffw-inline-search-empty');

		if (!visible && active) {
			if (!$empty.length) {
				$root.append(
					'<p class="ffw-inline-search-empty">' +
						(window.ffw_public_js && ffw_public_js.search_empty
							? ffw_public_js.search_empty
							: 'No FAQs match your search.') +
					'</p>'
				);
			}
		} else {
			$empty.remove();
		}
	}

	// Search and category filters look through every FAQ, so "Show more"
	// paging pauses while either is in use.
	function setFiltering($root, active) {
		$root.toggleClass('ffw-is-filtering', !!active);
	}

	function countVisible($root) {
		return getItems($root).not('.ffw-faq-item-hidden, .ffw-faq-cat-hidden').length;
	}

	// Analytics (only when an add-on asks for it): send the term once the
	// visitor stops typing, once per term per page view.
	var logTimer;
	var logged = {};

	function logSearch($root, query) {
		window.clearTimeout(logTimer);
		var config = window.ffw_public_js;
		if (!config || !config.track_search || query.length < 2) {
			return;
		}

		logTimer = window.setTimeout(function () {
			if (logged[query]) {
				return;
			}
			logged[query] = true;

			var data = new FormData();
			data.append('action', 'ffw_search_event');
			data.append('term', query);
			data.append('results', countVisible($root));
			data.append('object_id', config.object_id || 0);
			if (navigator.sendBeacon) {
				navigator.sendBeacon(config.ajaxurl, data);
			} else {
				$.ajax({ url: config.ajaxurl, method: 'POST', data: data, processData: false, contentType: false });
			}
		}, 1500);
	}

	// Help Center sidebar: hide a topic heading when none of its FAQs match.
	function toggleTopics($root, active) {
		$root.find('.ffw-hc-topic-title').each(function () {
			var $title = $(this);
			var $section = $title.nextUntil('.ffw-hc-topic-title');
			var visible = getItems($section).not('.ffw-faq-item-hidden, .ffw-faq-cat-hidden').length;
			var hide = active && !visible;
			$title.toggleClass('ffw-hc-topic-hidden', hide);
			$section.toggleClass('ffw-hc-topic-hidden', hide);
		});
	}

	function filterFaqs($input) {
		var query = $.trim($input.val()).toLowerCase();
		var $root = getRoot($input);

		getItems($root).each(function () {
			var $item = $(this);
			var match = !query || $item.text().toLowerCase().indexOf(query) !== -1;
			$item.toggleClass('ffw-faq-item-hidden', !match);
		});

		var category = $root.find('.ffw-faq-filter-btn.is-active').attr('data-ffw-filter') || '';
		setFiltering($root, query || category);
		updateEmptyMessage($root, countVisible($root), !!query || !!category);
		toggleTopics($root, !!query);
		logSearch($root, query);
	}

	function filterByCategory($button) {
		var $root = getRoot($button);
		var slug = $button.attr('data-ffw-filter') || '';

		$button.siblings('.ffw-faq-filter-btn').removeClass('is-active').attr('aria-pressed', 'false');
		$button.addClass('is-active').attr('aria-pressed', 'true');

		getItems($root).each(function () {
			var cats = ($(this).attr('data-ffw-cats') || '').split(/\s+/);
			$(this).toggleClass('ffw-faq-cat-hidden', !!slug && $.inArray(slug, cats) === -1);
		});

		var query = $.trim($root.find('.ffw-inline-search').val() || '');
		setFiltering($root, query || slug);
		var visible = countVisible($root);
		updateEmptyMessage($root, visible, !!query || !!slug);

		var label = $.trim($button.clone().children().remove().end().text());
		$root.find('.ffw-faq-filter-status').text(
			(window.ffw_public_js && ffw_public_js.filter_status
				? ffw_public_js.filter_status
				: '%1$s: %2$d FAQs shown')
				.replace('%1$s', label)
				.replace('%2$d', visible)
		);
	}

	function updateShowMore($root) {
		var remaining = $root.find('[data-ffw-more]').length;
		var $wrap = $root.find('.ffw-show-more-wrap');

		if (!remaining) {
			$wrap.remove();
			$root.removeClass('ffw-has-more');
			return;
		}

		var label = (window.ffw_public_js && ffw_public_js.show_more_count) || '(%d more)';
		$wrap.find('.ffw-show-more-count').text(label.replace('%d', remaining));
	}

	function showMore($button) {
		var $root = getRoot($button);
		var step = parseInt($button.attr('data-ffw-step'), 10) || 5;
		var $next = $root.find('[data-ffw-more]').slice(0, step);

		$next.removeAttr('data-ffw-more');
		updateShowMore($root);

		// Move focus to the first new question so keyboard users continue from there.
		var $focus = $next.first().find('[aria-controls^="ffw-a-"], summary, a, button').first();
		if ($focus.length) {
			$focus.trigger('focus');
		}
	}

	// A link to a FAQ that is still behind "Show more" reveals the whole list.
	function revealHashTarget() {
		var match = String(window.location.hash || '').match(/^#(ffw-(?:custom-)?faq-\d+)$/);
		if (!match) {
			return;
		}

		var target = document.getElementById(match[1]);
		if (target && target.hasAttribute('data-ffw-more')) {
			var $root = getRoot($(target));
			$root.find('[data-ffw-more]').removeAttr('data-ffw-more');
			updateShowMore($root);
		}
	}

	$(document).on('click', '.ffw-show-more', function (e) {
		e.preventDefault();
		showMore($(this));
	});

	$(revealHashTarget);
	$(window).on('hashchange', revealHashTarget);

	$(document).on('input', '.ffw-inline-search', function () {
		filterFaqs($(this));
	});

	$(document).on('click', '.ffw-faq-filter-btn', function (e) {
		e.preventDefault();
		filterByCategory($(this));
	});
})(jQuery);
