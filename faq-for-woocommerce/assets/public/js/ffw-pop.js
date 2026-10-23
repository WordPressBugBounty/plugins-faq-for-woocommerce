/**
 * Woocommerce FAQ - Classic Template JS
 *
 * Copyright (c) 2019 wpfeel
 * Licensed under the GPLv2+ license.
 */
jQuery(document).ready(function ($) {
    'use strict';

    $.faq_pop = {
        init: function () {
            //default hide all answers
            // Only the question toggle: spans inside an answer are content.
            $(document).on('click', '#ffw-main-wrapper .ffw-pop-container .ffw-accordion-item > span.ffw-button', this.slideFAQs);
        },

        slideFAQs: function (e) {
            e.preventDefault();

            // Each list works on its own, so several lists can share a page.
            var $list = $(this).closest('.ffw-pop-container');

            if ($(this).hasClass("ffw-active")) {
                $(this).removeClass("ffw-active").parent('.ffw-accordion-item').children(".ffw-classic-answer").slideUp();
            } else {
                $list.find(".ffw-accordion-item > .ffw-button.ffw-active").parent('.ffw-accordion-item').children(".ffw-classic-answer").slideUp();
                $list.find(".ffw-accordion-item > .ffw-button").removeClass("ffw-active");
                $(this).addClass("ffw-active").parent('.ffw-accordion-item').children(".ffw-classic-answer").slideDown();
            }

            return false;
        }


    }
    //call the class
    $.faq_pop.init();
});