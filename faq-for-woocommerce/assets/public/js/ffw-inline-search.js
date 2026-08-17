/**
 * Filter rendered FAQs as the visitor types.
 *
 * @since 1.8.18
 */
(function ($) {
	'use strict';

	function getItems($root) {
		var $items = $root.find('.ffw-accordion-item, .ffw-faq-list-item, .ffw-collapse');
		var $details = $root.find('details');

		if ($details.length) {
			$items = $items.add($details);
		}

		return $items;
	}

	function filterFaqs($input) {
		var query = $.trim($input.val()).toLowerCase();
		var $root = $input.closest('#ffw-main-wrapper, .ffw-main-wrapper');

		if (!$root.length) {
			$root = $input.closest('.ffw-inline-search-wrap').parent();
		}

		var $items = getItems($root);
		var visible = 0;

		$items.each(function () {
			var $item = $(this);
			var text = $item.text().toLowerCase();
			var match = !query || text.indexOf(query) !== -1;
			$item.toggleClass('ffw-faq-item-hidden', !match);
			if (match) {
				visible += 1;
			}
		});

		var $empty = $root.find('.ffw-inline-search-empty');
		if (!visible && query) {
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

	$(document).on('input', '.ffw-inline-search', function () {
		filterFaqs($(this));
	});
})(jQuery);
