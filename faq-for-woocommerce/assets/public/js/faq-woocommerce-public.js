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
