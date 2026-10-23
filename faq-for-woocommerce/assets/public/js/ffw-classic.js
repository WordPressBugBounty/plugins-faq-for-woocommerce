/**
 * Woocommerce FAQ - Classic Template JS
 *
 * Copyright (c) 2019 wpfeel
 * Licensed under the GPLv2+ license.
 */
jQuery(document).ready(function ($) {
    'use strict';

    $.faq_classic = {
        init: function () {
            //default hide all answers
            $('.ffw-accordion-item > button.ffw-button:not(.ffw-active)').parent('.ffw-accordion-item').children(".ffw-classic-answer").hide();

            //accordian slide js
            $(document).on('click', '.ffw-accordion-item > button.ffw-button', this.slideFAQs);
        },

        slideFAQs: function (e) {
            e.preventDefault();

            // Each list works on its own, so several lists can share a page.
            var $list = $(this).closest('.ffw-accordion');

            if ($(this).hasClass("ffw-active")) {
                $(this).removeClass("ffw-active").parent('.ffw-accordion-item').children(".ffw-classic-answer").slideUp();
            } else {
                $list.find(".ffw-accordion-item > button.ffw-button.ffw-active").parent('.ffw-accordion-item').children(".ffw-classic-answer").slideUp();
                $list.find(".ffw-accordion-item > button.ffw-button").removeClass("ffw-active");
                $(this).addClass("ffw-active").parent('.ffw-accordion-item').children(".ffw-classic-answer").slideDown();
            }

            return false;
        }


    };

    //call the class
    $.faq_classic.init();
});