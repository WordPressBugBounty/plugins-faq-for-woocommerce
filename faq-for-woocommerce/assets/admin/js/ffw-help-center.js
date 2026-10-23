/**
 * Happy FAQs: Help Center builder on Happy FAQs → Help Center Page.
 *
 * @since 3.0.0
 */
(function () {
	'use strict';

	var form = document.querySelector('.ffw-hc-form');
	if (!form) {
		return;
	}

	var frameWrap = document.querySelector('.ffw-hc-preview-frame');
	var iframe = frameWrap ? frameWrap.querySelector('iframe') : null;
	var openLink = document.querySelector('.ffw-hc-preview-open');
	var contact = form.querySelector('[name="ffw_hc[contact]"]');
	var timer;

	function previewUrl() {
		var params = [];
		Array.prototype.forEach.call(form.elements, function (field) {
			if (!field.name || field.name.indexOf('ffw_hc[') !== 0 || field.disabled || field.type === 'submit') {
				return;
			}
			if ((field.type === 'radio' || field.type === 'checkbox') && !field.checked) {
				return;
			}
			params.push(encodeURIComponent(field.name) + '=' + encodeURIComponent(field.value));
		});
		return form.getAttribute('data-preview') + '&' + params.join('&');
	}

	function scale() {
		if (frameWrap && iframe) {
			frameWrap.style.setProperty('--ffw-hc-scale', String(frameWrap.clientWidth / 1100));
		}
	}

	function refresh() {
		var url = previewUrl();
		if (openLink) {
			openLink.href = url;
		}
		if (iframe) {
			frameWrap.classList.remove('is-loaded');
			iframe.src = url;
		}
	}

	function toggleLink() {
		var show = contact && contact.value === 'link';
		Array.prototype.forEach.call(form.querySelectorAll('[data-ffw-hc-show="link"]'), function (el) {
			el.hidden = !show;
		});
	}

	if (iframe) {
		iframe.addEventListener('load', function () {
			frameWrap.classList.add('is-loaded');
		});
	}

	form.addEventListener('change', function () {
		toggleLink();
		window.clearTimeout(timer);
		timer = window.setTimeout(refresh, 250);
	});

	form.addEventListener('input', function (event) {
		if (event.target.type === 'text') {
			window.clearTimeout(timer);
			timer = window.setTimeout(refresh, 600);
		}
	});

	window.addEventListener('resize', scale);

	toggleLink();
	scale();
	refresh();
})();
