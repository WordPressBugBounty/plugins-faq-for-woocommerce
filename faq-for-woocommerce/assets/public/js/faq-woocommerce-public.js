/**
 * Woocommerce FAQ Public JS
 *
 * Copyright (c) 2019 wpfeel
 * Licensed under the GPLv2+ license.
 */
jQuery(document).ready(function ($) {
    'use strict';

    $.faq_woocommerce = {
        ajax_url: '',
        init: function () {
            //hide comment reply form
            $('.ffw-comment-reply-form').hide();


            $(document).on('click', '.ffw-comment-reply-button', this.addCommentReplyData);
            $(document).on('click', '.ffw-comment-header', this.faqCommentSectionToggle);
            $(document).on('click', '.ffw-btn-expand-collapse-all', this.faqExpandCollapseToggle);
            $(document).on('show_variation', 'form.variations_form', this.onChangeVariations);

            // Accessibility: keyboard support and aria-expanded for every layout.
            $(document).on('keydown', '[role="button"][aria-controls^="ffw-a-"]', this.onToggleKeydown);
            $(document).on('click', '[aria-controls^="ffw-a-"], .ffw-btn-expand-collapse-all', this.queueAriaSync);

            // Insights: count answer opens and "Was this helpful?" votes.
            $(document).on('click', '[aria-controls^="ffw-a-"]', this.trackOpen);
            // <details> toggle events don't bubble, so listen in the capture phase.
            document.addEventListener('toggle', this.trackDetailsOpen, true);
            $(document).on('click', '.ffw-helpful-btn', this.vote);
            this.restoreVotes();
        },
        sendEvent: function (faqId, event) {
            var data = new FormData();
            data.append('action', 'ffw_faq_event');
            data.append('faq_id', faqId);
            data.append('event', event);
            data.append('object_id', ffw_public_js.object_id || 0);

            if (event === 'view' && navigator.sendBeacon) {
                navigator.sendBeacon(ffw_public_js.ajaxurl, data);
                return $.Deferred().resolve().promise();
            }

            return $.ajax({ url: ffw_public_js.ajaxurl, method: 'POST', data: data, processData: false, contentType: false });
        },
        storage: function () {
            try {
                return window.sessionStorage;
            } catch (e) {
                return null;
            }
        },
        // Count each FAQ once per browser session.
        recordView: function (faqId) {
            if (!faqId || faqId < 1) {
                return;
            }
            var store = $.faq_woocommerce.storage();
            var key = 'ffw_viewed_' + faqId;
            if (store && store.getItem(key)) {
                return;
            }
            if (store) {
                store.setItem(key, '1');
            }
            $.faq_woocommerce.sendEvent(faqId, 'view');
        },
        trackOpen: function () {
            var toggle = this;
            var panel = document.getElementById(toggle.getAttribute('aria-controls'));
            // Only an answer that was closed before this click is being opened.
            var wasClosed = panel && !$(panel).is(':visible');
            setTimeout(function () {
                if (wasClosed && $(panel).is(':visible')) {
                    $.faq_woocommerce.recordView(parseInt($(toggle).closest('[data-ffw-faq-id]').attr('data-ffw-faq-id'), 10));
                }
            }, 0);
        },
        trackDetailsOpen: function (e) {
            var details = e.target || this;
            if (details && details.tagName === 'DETAILS' && details.open && details.hasAttribute('data-ffw-faq-id')) {
                $.faq_woocommerce.recordView(parseInt(details.getAttribute('data-ffw-faq-id'), 10));
            }
        },
        voteKey: function (faqId) {
            return 'ffw_voted_' + faqId;
        },
        lockVote: function ($box, choice, message) {
            $box.addClass('is-voted').find('.ffw-helpful-btn').prop('disabled', true).each(function () {
                $(this).attr('aria-pressed', $(this).data('ffw-vote') === choice ? 'true' : 'false');
            });
            $box.find('.ffw-helpful-status').text(message || '');
        },
        vote: function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $box = $btn.closest('.ffw-helpful');
            var faqId = parseInt($box.attr('data-ffw-helpful'), 10);
            var choice = $btn.data('ffw-vote');
            var thanks = (window.ffw_public_js && ffw_public_js.helpful_thanks) || 'Thanks for your feedback!';

            $.faq_woocommerce.lockVote($box, choice, thanks);
            try {
                window.localStorage.setItem($.faq_woocommerce.voteKey(faqId), choice);
            } catch (err) {}
            $.faq_woocommerce.sendEvent(faqId, choice);
        },
        // Visitors who already voted see their choice, not the buttons again.
        restoreVotes: function () {
            $('.ffw-helpful').each(function () {
                var choice = null;
                try {
                    choice = window.localStorage.getItem($.faq_woocommerce.voteKey($(this).attr('data-ffw-helpful')));
                } catch (err) {}
                if (choice) {
                    $.faq_woocommerce.lockVote($(this), choice, '');
                }
            });
        },
        /**
         * Enter and Space open non-button toggles, like a real button.
         */
        onToggleKeydown: function (e) {
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                e.preventDefault();
                $(this).trigger('click');
            }
        },
        /**
         * Update aria-expanded once the layout's slide animations finish.
         */
        queueAriaSync: function () {
            setTimeout(function () {
                // Opening panels are visible at once; closing ones settle when the slide ends.
                $.faq_woocommerce.syncAria();
                $('.ffw-classic-answer, .ffw-basic-answer, .ffw-content, .ffw-accordion-body-content')
                    .promise()
                    .done($.faq_woocommerce.syncAria);
            }, 0);
        },
        syncAria: function () {
            $('[aria-controls^="ffw-a-"]').each(function () {
                var panel = document.getElementById(this.getAttribute('aria-controls'));
                if (panel) {
                    var open = $(panel).is(':visible') && !$(panel).hasClass('ffw-hide');
                    this.setAttribute('aria-expanded', open ? 'true' : 'false');
                }
            });

            $('.ffw-btn-expand-collapse-all').each(function () {
                var $root = $(this).closest('.ffw-main-wrapper');
                var anyOpen = $root.find('[aria-controls^="ffw-a-"][aria-expanded="true"]').length > 0
                    || $root.find('details[open]').length > 0;
                this.setAttribute('aria-expanded', anyOpen ? 'true' : 'false');
            });
        },
        addCommentReplyData: function (e) {
            var product_id = $('.ffw_product_id_for_comment').val();
            //show comment reply form
            $(this).closest( ".comment" ).find('.ffw-comment-reply-form').fadeToggle();
            $(this).closest( ".comment" ).find('.ffw_product_id_for_comment_reply').val(product_id);
        },
        faqCommentSectionToggle: function() {
            $(this).closest('.ffw-comment-wrapper').find('.ffw-comment-box').slideToggle();
        },
        faqExpandCollapseToggle: function(e) {
            e.preventDefault();

            var $root = $(this).closest('.ffw-main-wrapper');
            var $answers = $root.find('.ffw-classic-answer, .ffw-basic-answer, .ffw-accordion-body-content, .ffw-content');
            var $details = $root.find('details');
            var expand = $answers.filter(':visible').length === 0 && $details.filter('[open]').length === 0;

            if (expand) {
                $details.attr('open', true);
                $answers.removeClass('ffw-hide').slideDown();
                $root.find('.ffw-button').addClass('ffw-active');
                $root.find('.ffw-accordion').addClass('ffw-accordion-active');
            } else {
                $details.removeAttr('open');
                $answers.addClass('ffw-hide').slideUp();
                $root.find('.ffw-button').removeClass('ffw-active');
                $root.find('.ffw-accordion').removeClass('ffw-accordion-active');
            }
        },
        /**
         * On change variations product
         *
         * @return void
         */
        onChangeVariations: function(event, data) {
            if( data.variation_id ) {
                console.log('variation product' + data.variation_id);
                console.log(ffw_public_js.nonce);

                let variation_id = data.variation_id;
                let nonce        = ffw_public_js.nonce;

                //show variation faqs
                let faq_data = {
                    'variation_id': variation_id,
                };

                wp.ajax.post( 'ffw_search_variation_faqs', {
                    nonce: nonce,
                    faq_data: faq_data,
                } ).then( response => {
                    console.log(response);

                    if( response.content ) {
                        $("#ffw-main-wrapper").replaceWith(response.content);
                    }

                } ).fail( error => {
                    console.log('erorr.......');
                    console.log(error);
                } );
            }

        }


    };


    $.faq_woocommerce.init();
});
