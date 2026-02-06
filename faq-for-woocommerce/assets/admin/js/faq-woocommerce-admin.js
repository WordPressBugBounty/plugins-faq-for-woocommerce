/**
 * Woocommerce FAQ Admin JS
 *
 * Copyright (c) 2019 wpfeel
 * Licensed under the GPLv2+ license.
 */
jQuery(document).ready(function ($) {
	'use strict';

    jQuery.fn.putCursorAtEnd = function() {

        return this.each(function() {

            // Cache references
            var $el = $(this),
                el = this;

            // Only focus if input isn't already
            if (!$el.is(":focus")) {
                $el.focus();
            }

            // If this function exists... (IE 9+)
            if (el.setSelectionRange) {

                // Double the length because Opera is inconsistent about whether a carriage return is one character or two.
                var len = $el.val().length * 2;

                // Timeout seems to be required for Blink
                setTimeout(function() {
                    el.setSelectionRange(len, len);
                }, 1);

            } else {

                // As a fallback, replace the contents with itself
                // Doesn't work in Chrome, but Chrome supports setSelectionRange
                $el.val($el.val());

            }

            // Scroll to the bottom, in case we're in a tall textarea
            // (Necessary for Firefox and Chrome)
            this.scrollTop = 999999;

        });

    };

	$.faq_woocommerce = {
        ajax_url: '',
		init: function () {
            //assign ajax url
            this.ajax_url = ffw_admin.ajaxurl;

            // modal form
            var $modal = $('#ffw-popup-form-wrapper .ffw-modal-frame');

            // display in pages.
            $('.ffw-display_in_pages-select2').select2({
                placeholder: "Select Woo Pages",
                // allowClear: true
            });
            
            // faq categories.
            $('.ffw-category-select2').select2({
                placeholder: "Select categories",
                // allowClear: true
            });
            
            // faq tags.
            $('.ffw-tag-select2').select2({
                placeholder: "Select tags",
                // allowClear: true
            });

            if( "product" === ffw_admin.current_post_type && "edit" === ffw_admin.current_page_action ) {

                //ffw select2 init
                let search_dom = $('.ffw_search');
                search_dom.select2({
                    placeholder: "Select a faq post",
                    allowClear: true,
                    minimumResultsForSearch: Infinity
                });

                search_dom.on("select2:select", function(e) {
                    // console.log("hello puishak");

                    let __ffw_product_id    = $('#ffw_product_page_id').val();
                    let nonce               = ffw_admin.nonce;
                    let new_item_id         = $(this).val();
                    let __ffw_products_faqs  = $('#ffw_products').val();
                    __ffw_products_faqs = JSON.parse(__ffw_products_faqs);

                    //show loader
                    $('#ffw_product_data .ffw-product-loader').addClass('ffw-visible');

                    //insert faq's data from search
                    let faq_data = {
                        'product_id': __ffw_product_id,
                        'new_item_id': new_item_id,
                        'product_faqs': __ffw_products_faqs,
                    };

                    wp.ajax.post( 'ffw_insert_data_from_search', {
                        nonce: nonce,
                        faq_insert_search_data: faq_data,
                    } ).then( response => {
                        console.log('success.......');
                        console.log(response.message);
                        console.log(response.updated_data);
                        $('#ffw_products').val(JSON.stringify(response.updated_data));
                        $('.ffw-body').html(response.faq_body);

                        //hide loader
                        $('#ffw_product_data .ffw-product-loader').removeClass('ffw-visible');

                        // FAQ sorting
                        $.faq_woocommerce.ffw_sorting_data();

                        // reset
                        search_dom.val('').trigger('change');

                    } ).fail( error => {
                        console.log('erorr.......');
                        console.log(error);

                        //hide loader
                        $('#ffw_product_data .ffw-product-loader').removeClass('ffw-visible');

                        // reset
                        search_dom.val('').trigger('change');
                    } );
                });

                //ffw sortable
                $(".ffw-sortable").sortable({
                    axis: 'y',
                    update : function (event, ui) {
                        var data = $(this).sortable('serialize');
                        console.log(data);

                    }
                });
            }

            //ffw select2 init
            if( ffw_admin.current_page_action ) {
                console.log(ffw_admin.current_page_action);
            }
            let search_dom = $('.ffw-role-select');
            search_dom.select2({
                placeholder: "Select Roles",
                allowClear: true,
            });

            //modal js
            $(document).on('click', '#ffw-product-form-header .ffw-add-new', this.openPopupForm);
            $(document).on('click', '#ffw-popup-form-wrapper .ffw-modal-close, #ffw-popup-form-wrapper input[type="button"]', this.closePopupForm);
            $modal.bind('webkitAnimationEnd oanimationend msAnimationEnd animationend', function(e){
                if($modal.hasClass('state-leave')) {
                  $modal.removeClass('state-leave');
                }
            });

            //onchange popup answer, update answer textarea value
            // wp.editor.initialize('#ffw_popup_answer', {
            //     setup: function (editor) {
            //         editor.on('change', function () {
            //             editor.save();
            //         });
            //     }
            // });


            //style inputs as color picker
            $('.ffw_question_text_color').wpColorPicker();
            $('.ffw_question_bg_color').wpColorPicker();
            $('.ffw_question_bg_secondary_color').wpColorPicker();
            $('.ffw_question_border_color').wpColorPicker();
            $('.ffw_answer_text_color').wpColorPicker();
            $('.ffw_answer_bg_color').wpColorPicker();
            $('.ffw_answer_border_color').wpColorPicker();
            $('.ffw-comments-date-time-color').wpColorPicker();
            $('.ffw-comments-content-color').wpColorPicker();
            $('.ffw-comments-author-name-color').wpColorPicker();
            $('.ffw-comments-submit-button-text-color').wpColorPicker();
            $('.ffw-comments-reply-button-text-color').wpColorPicker();
            $('.ffw-comments-submit-button-bg-color').wpColorPicker();
            $('.ffw-comments-form-border-color').wpColorPicker();
            $('.ffw-comments-form-title-color').wpColorPicker();
            $('.ffw-comments-reply-form-title-color').wpColorPicker();
            $('.ffw-comments-reply-form-submit-button-color').wpColorPicker();
            $('.ffw-comments-reply-form-submit-button-bg-color').wpColorPicker();
            $('.ffw-comments-reply-form-border-color').wpColorPicker();
            $('.ffw-comments-reply-form-submit-button-text-color').wpColorPicker();

            //metabox option js
            $(document).on('click', '#ffw_product_data .ffw-metabox h3', this.toggleOptionMetabox);
            $(document).on('click', '#ffw_modal_submit', this.submitModalForm);
            $(document).on('click', '#ffw-delete-all-faq', this.deleteAllFaq);
            $(document).on('click', '#ffw-product-form-header .ffw-single-delete-btn', this.deleteSingleFaq);
            $(document).on('click', '#ffw-product-form-header .ffw-single-edit-btn', this.openUpdatePopupForm);
            $(document).on('click', '.ffw-layout-select', this.changePreviewImage);
            $(document).on('change', '.ffw_question_font_size', this.changeQuestionRange);
            $(document).on('change', '.ffw_answer_font_size', this.changeAnswerRange);
            $(document).on('click', '.ffw-setting-wrapper .ffw-get-pro-wrapper', this.showProPopup);
            $(document).on('click', '#ffw-popup-close', this.hideProPopup);
            $(document).on('click', '.ffw-discount-notice .notice-dismiss', this.hideDiscountNotice);

            $(document).on('click', '.ffw-template-preview-button', this.showTemplatePopup);
            $(document).on('click', '.ffw-template-popup-close-button', this.hideTemplatePopup);
            $(document).on('click', '.ffw-template-active-button', this.activateTemplate);

            // Shortcodes page events.
            $(document).on('click', '.ffw-shortcode-copy-icon-box', this.copyToClickShortcode);

            //AI FAQs events.
            $(document).on('click', '.ffw-ai-result-select-all-btn', this.ai_faqs_select_all);
            $(document).on('click', '.ffw-ai-result-item-group input[type=checkbox]', this.ai_faqs_on_select);
            $(document).on('click', '.ffw-ai-back-btn', this.ai_back_to);
            $(document).on('submit', '.ffw-ai-faqs-form', this.ai_faqs_generator);
            $(document).on('submit', '.ffw-ai-result-form', this.ai_faqs_insert);

            // FAQ sorting.
            this.ffw_sorting_data();

        },

        hideDiscountNotice: function(e) {
            e.preventDefault();

            console.log("Discounted...");

            let nonce = ffw_admin.nonce;

            //data
            let data = {
                nonce: nonce,
            };

            wp.ajax.post( 'ffw_hide_discount_notice', {
                data: data
            } ).then( response => {
                console.log(response);
            } ).fail( error => {
                console.log('erorr during discounted notice dismissal.......');
            } );

        },
        openPopupForm: function(e) {
            e.preventDefault();

            $('#ffw_modal_submit').show();
            $('#ffw_modal_update').hide();

            var $modal = $('.ffw-modal-frame');
            var $overlay = $('.modal-overlay');
            $overlay.addClass('state-show');
            $modal.removeClass('state-leave').addClass('state-appear');

            //focus input
            setTimeout(function() {
                $('#ffw-popup-form-wrapper .ffw-popup-form-add-question').focus();
            }, 500);

            //reset question and answers
            $('#ffw-popup-form-wrapper .ffw-popup-form-add-question').val('');
            if ( jQuery("#wp-ffw_popup_answer-wrap").hasClass("tmce-active") ) {
                tinymce.get("ffw_popup_answer").setContent('');
            }
        },
        closePopupForm: function(e) {
            e.preventDefault();

            var $modal = $('.ffw-modal-frame');
            var $overlay = $('.modal-overlay');
            $overlay.removeClass('state-show');
            $modal.removeClass('state-appear').addClass('state-leave');
		},
        toggleOptionMetabox: function (e) {
            e.preventDefault();

            $( this ).parent( '.ffw-metabox' ).toggleClass( 'closed' ).toggleClass( 'open' );

            // If the user clicks on some form input inside the h3, the box should not be toggled
            if ( $( event.target ).filter( ':input, .ffw-sort, .ffw-handle-item' ).length ) {
                return;
            }

            $( this ).next( '.ffw-metabox-content' ).stop().slideToggle();
        },
        submitModalForm: function (e) {
            e.preventDefault();

            //hide the popup
            $('.ffw-modal-frame').removeClass('state-appear');
            $('.modal-overlay ').removeClass('state-show');

            //show loader
            $('#ffw_product_data .ffw-product-loader').addClass('ffw-visible');

            let __ffw_question  = $('#ffw-popup-form-wrapper .ffw-popup-form-add-question');
            let ffw_answer_text = '';
            if ( jQuery("#wp-ffw_popup_answer-wrap").hasClass("tmce-active") ) {
                ffw_answer_text =  tinyMCE.activeEditor.getContent();
            }
            let __ffw_product_id        = $('#ffw_product_page_id').val();
            let __ffw_products_faqs     = $('#ffw_products').val();
                __ffw_products_faqs     = JSON.parse(__ffw_products_faqs);

            let nonce = ffw_admin.nonce;
            let ffw_question_val  = __ffw_question.val();

            //insert faq's data
            let faq_data = {
                'question': ffw_question_val,
                'answer': ffw_answer_text,
                'product_id': __ffw_product_id,
                'faq_list': __ffw_products_faqs,
            };

            wp.ajax.post( 'ffw_insert_new_faq', {
                nonce: nonce,
                faq_data: faq_data,
            } ).then( response => {
                $('#ffw_products').val(JSON.stringify(response.faq_data));
                $('.ffw-body').html(response.faq);
                $('.ffw-popup-form-add-question').val('');
                $('.ffw-popup-form-add-answer').val('');

                //hide loader
                $('#ffw_product_data .ffw-product-loader').removeClass('ffw-visible');

                // FAQ sorting
                $.faq_woocommerce.ffw_sorting_data();

            } ).fail( error => {
                console.log('erorr.......');
                console.log(error);

                // FAQ sorting
                $.faq_woocommerce.ffw_sorting_data();
            } );

        },
        deleteAllFaq: function (e) {
            e.preventDefault();

            let __ffw_product_id    = $('#ffw_product_page_id').val();
            let __ffw_products_faqs  = $('#ffw_products').val();
                __ffw_products_faqs = JSON.parse(__ffw_products_faqs);
            let nonce = ffw_admin.nonce;

            let total_product = '';
            if( undefined === __ffw_products_faqs || 0 === __ffw_products_faqs.length ) {
                console.log(__ffw_products_faqs);
                total_product = 0;
            }

            //insert faq's data
            let faq_delete_data = {
                'product_id': __ffw_product_id
            };

            //delete confirm popup
            Swal.fire({
                title: 'Delete all the faqs?',
                icon: 'question',
                showCancelButton: true,
            }).then(function(isConfirm) {

                if (isConfirm.isConfirmed) {

                    wp.ajax.post( 'ffw_delete_all_faqs', {
                        nonce: nonce,
                        faq_delete_data: faq_delete_data,
                    }).then( response => {

                        if(response.success) {
                            //item hide
                            $('.ffw-metabox-item').hide();

                            //make current faqs data empty
                            $('#ffw_products').val(JSON.stringify([]));

                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'bottom-end',
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.addEventListener('mouseenter', Swal.stopTimer);
                                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                                }
                            });

                            Toast.fire({
                                icon: 'success',
                                title: 'Delete successfull.'
                            });
                        }

                    }).fail( error => {
                        console.log('erorr of delete faq.......');
                        console.log(error);
                    });

                }
            });

        },
        deleteSingleFaq: function (e) {
            e.preventDefault();
            e.stopPropagation();

            let __ffw_product_id    = $('#ffw_product_page_id').val();
            let __ffw_products_faqs  = $('#ffw_products').val();
                __ffw_products_faqs = JSON.parse(__ffw_products_faqs);
            let nonce = ffw_admin.nonce;
            let current_faq = $(this).parents('.ffw-metabox-item');
            let current_faq_id = $(this).attr('rel');

            //delete confirm popup
            Swal.fire({
                title: 'Are you sure to Delete?',
                icon: 'question',
                showCancelButton: true,
            }).then(function(isConfirm) {

                if (isConfirm.isConfirmed) {

                    const index = __ffw_products_faqs.indexOf(current_faq_id);
                    if (index > -1) {
                        __ffw_products_faqs.splice(index, 1);
                    }

                    //update faq's data
                    let faq_updated_data = {
                        'product_id': __ffw_product_id,
                        'updated_faq_list': __ffw_products_faqs
                    };

                    //hide current faq
                    current_faq.hide();

                    wp.ajax.post( 'ffw_delete_single_faq', {
                        nonce: nonce,
                        faq_updated_data: faq_updated_data,
                    }).then( response => {
                        console.log('success.......');
                        console.log(response);
                        console.log(JSON.stringify(response.update_faq_list));
                        $('#ffw_products').val(JSON.stringify(response.update_faq_list));

                    }).fail( error => {
                        console.log('erorr of delete faq.......');
                        console.log(error);
                    });

                }
            });

        },
        openUpdatePopupForm: function (e) {
            e.preventDefault();
            e.stopPropagation();

            $('#ffw_modal_submit').hide();
            $('#ffw_modal_update').show();

            //open modal
            var $modal = $('.ffw-modal-frame');
            var $overlay = $('.modal-overlay');
            $overlay.addClass('state-show');
            $modal.removeClass('state-leave').addClass('state-appear');

            let __ffw_id = $(this).attr('rel');
                $('#ffw-current-ques-id').val(__ffw_id);
            let __ffw_old_question = $(this).parent('h3').find('strong').html();
            let __ffw_old_answer = $(this).parents('.ffw-metabox-item').find('.ffw-metabox-content .data div').html();
            let __ffw_question  = $('#ffw-popup-form-wrapper .ffw-popup-form-add-question');
                __ffw_question.val(__ffw_old_question);

            //focus input
            setTimeout(function() {
                $('#ffw-popup-form-wrapper .ffw-popup-form-add-question').focus().putCursorAtEnd();
            }, 500);

            //set value for answer
            if ( jQuery("#wp-ffw_popup_answer-wrap").hasClass("tmce-active") ) {
                tinymce.get("ffw_popup_answer").setContent(__ffw_old_answer);
            }
        },
        changePreviewImage: function(e) {
            let current_image = $(this).val();

            $('.ffw-setting-layout img').each(function(item) {
               let image_layout = $(this).data('layout');
               if( image_layout === parseInt(current_image) ) {
                   $(this).removeClass('ffw-hide').addClass('ffw-visible');
               }else {
                   $(this).removeClass('ffw-visible').addClass('ffw-hide');
               }
            });
        },
        changeQuestionRange: function(e) {
            let question_range_val = $(this).val();
            $('.ffw_question_font_size_label').text(question_range_val + "px");
        },
        changeAnswerRange: function(e) {
            let answer_range_val = $(this).val();
            $('.ffw_answer_font_size_label').text(answer_range_val + "px");
        },
        ffw_sorting_data: function(e) {

            // Allow FAQ sorting
            $( '.ffw-sortable' ).sortable({
                cursor:               'move',
                axis:                 'y',
                handle:               '.ffw-sort-icon',
                scrollSensitivity:    40,
                forcePlaceholderSize: true,
                helper:               'clone',
                opacity:              0.65,
                stop:                 function() {
                    var selectedData = [];
                    $('.ffw-sortable .ffw-metabox-item').each(function() {
                        selectedData.push($(this).attr("id"));
                    });
                    $.faq_woocommerce.ffw_sortable(selectedData);
                }
            });
        },
        ffw_sortable: function(data) {
            let __ffw_product_id    = $('#ffw_product_page_id').val();
            let nonce               = ffw_admin.nonce;

            //sort faq's data
            let faq_data = {
                'product_id': __ffw_product_id,
                'faq_sorted_list': data,
            };

            wp.ajax.post( 'ffw_sort_faq_data', {
                nonce: nonce,
                faq_sorted_data: faq_data,
            } ).then( response => {
                console.log('success.......');
                console.log(response.message);
                $('#ffw_products').val(JSON.stringify(data));

            } ).fail( error => {
                console.log('erorr.......');
                console.log(error);
            } );
        },
        showProPopup: function(e) {
            e.preventDefault();

            $('.ffw-go-pro-modal').removeClass('ffw-hide');
		},
        hideProPopup: function(e) {
            e.preventDefault();

            $('.ffw-go-pro-modal').addClass('ffw-hide');
		},
        showTemplatePopup: function(e){
            e.preventDefault();

            // get & show image url in the popup
            var img_url = $(this).parents('.ffw-item-wrapper').find('.ffw-layout-img img').attr('src');
            $('.ffw-template-popup-inner img').attr('src', img_url);

            // control body overflow not to scroll
            $('body').addClass('ffw-overflow-hidden');

            // activate the popup
            $(".ffw-active").removeClass("ffw-active");
            $(".ffw-template-popup-wrapper").addClass("ffw-active");
        },
        hideTemplatePopup: function(e){
            e.preventDefault();

            // reset body overflow
            $('body').removeClass('ffw-overflow-hidden');

            $(".ffw-template-popup-wrapper").removeClass("ffw-active");
        },
        activateTemplate: function(e){
            e.preventDefault();

            $('.ffw-item-wrapper').removeClass('ffw-template-active');
            $(this).parents('.ffw-item-wrapper').addClass('ffw-template-active');

            var template_id = $(this).data('templte_id');
            let nonce       = ffw_admin.nonce;

            //set to hidden layout field
            $('input[name="ffw_general_settings[ffw_layout]"]').val(template_id);

            $.ajax(
                {
                    method: "POST",
                    url: ffw_admin.ajaxurl,
                    dataType: "json",
                    data: {
                        action: 'ffw_activate_template',
                        nonce: nonce,
                        template_id: template_id,
                    }
                }
            )
            .done(
                function ( response ) {
                    console.log('success.......');
                }
            );
        },
        copyToClickShortcode: function(e) {
            e.preventDefault();

            var $tooltip = $(this).find('.ffw-tooltip');
            var shortcode = $(this).closest('.ffw-shortcode-box').find('.ffw-shortcode').text();

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(shortcode).then(() => {
                    $tooltip.text('Copied: ' + shortcode);
                    setTimeout(() => $tooltip.text('Click to copy'), 2000);
                });
            } else {
                var $temp = $("<textarea>");
                $("body").append($temp);
                $temp.val(shortcode).select();
                document.execCommand("copy");
                $temp.remove();
                
                $tooltip.text('Copied');
                setTimeout(() => $tooltip.text('Click to copy'), 2000);
            }
        },
        ai_faqs_select_all: function(e) {
            console.log('ai_faqs_select_all');
            let ai_result_checkbox = $('.ffw-ai-result-item-group input[type=checkbox]');

            console.log(ai_result_checkbox);
            
            if(e.target.checked) {
                ai_result_checkbox.attr('checked', true);
            }else{
                ai_result_checkbox.attr('checked', false);
            }

            $.faq_woocommerce.ai_faqs_selected_item_count();
        },
        ai_faqs_selected_item_count: function(e) {
            let ai_selected_checkbox = $('.ffw-ai-result-item-group input[type=checkbox]:checked');
            let item_count_text = $('.ffw-ai-selected-count');

            console.log(ai_selected_checkbox);

            if(ai_selected_checkbox.length > 0) {
                let items_count = ai_selected_checkbox.length + ' Items Selected';
                item_count_text.text(items_count);
            }else {
                console.log(ffw_admin.strings.ai_item_selected);
                item_count_text.text(ffw_admin.strings.ai_item_selected);
            }
        },
        ai_faqs_on_select: function(e) {
            if(e.target.checked) {
                
            }

            $.faq_woocommerce.ai_faqs_selected_item_count();
        },
        ai_back_to: function(e) {
            e.preventDefault();

            $('.ffw-ai-result-content').hide();

            let submitBtn = document.querySelector('.ffw-ai-faqs-form button[type=submit] span');
            submitBtn.innerText = 'Generate FAQs';

            $('.ffw-ai-form-content').fadeIn();
        },
        ai_faqs_generator: function(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            let nonce      = ffw_admin.nonce;

            let submitBtn   = e.target.querySelector('button[type=submit] span');
            submitBtn.innerText = 'Generating...';

            //add action hook to the form data
            formData.append("action", "ffw_generate_ai_faqs");
            formData.append("nonce", nonce);

            console.log('ai faqs generation..');

            //show loader
            // $('#ffw_product_data .ffw-product-loader').addClass('ffw-visible');

            $.ajax(
                {
                    method: "POST",
                    url: ffw_admin.ajaxurl,
                    dataType: "json",
                    processData : false,
                    contentType: false,
                    data: formData,
                }
            )
            .done(
                function ( response ) {
                    console.log(response);

                    if(response.data.html.length > 0) {
                        $('.ffw-ai-result-fields').html(response.data.html);
                        $('.ffw-ai-form-content').fadeOut();

                        $('.ffw-ai-result-content-header h3').text('10 results for "' + response.data.product_title + '"');

                        $('.ffw-ai-result-content').addClass('ffw-ai-result-content-active');
                    }

                    if(response.data.product_id) {
                        $('.ffw-ai-result-form').attr('product_id', response.data.product_id);
                    }
                }
            );
        },
        ai_faqs_insert: function(e) {
            e.preventDefault();

            const formData  = new FormData(e.target);
            let nonce       = ffw_admin.nonce;

            let submitBtn   = e.target.querySelector('button[type=submit] span:last-child');
            submitBtn.innerText = 'Inserting...';

            let product_id = $('.ffw-ai-result-form').attr('product_id');

            //add action hook to the form data
            formData.append("action", "ffw_insert_ai_faqs");
            formData.append("ffw_insert_ai_product_id", product_id);
            formData.append("nonce", nonce);

            //show loader
            // $('#ffw_product_data .ffw-product-loader').addClass('ffw-visible');

            $.ajax(
                {
                    method: "POST",
                    url: ffw_admin.ajaxurl,
                    dataType: "json",
                    processData : false,
                    contentType: false,
                    data: formData,
                }
            )
            .done(
                function ( response ) {
                    if(response.success) {
                        window.location.href = ffw_admin.faw_list_page_url;
                    }
                }
            );
        }

	};


	$.faq_woocommerce.init();
});
