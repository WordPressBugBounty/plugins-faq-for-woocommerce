/**
 * Happy FAQs setup wizard.
 *
 * Every step can be skipped. Step 2 saves FAQs, step 4 installs the
 * selected tools in the background, step 5 marks the wizard as done.
 */
jQuery(function ($) {
    'use strict';

    var config = window.ffwSetupWizard || {};
    var strings = config.strings || {};
    var $wizard = $('#ffw-setup-wizard');

    if (!$wizard.length) {
        return;
    }

    var $steps = $wizard.find('.ffw-sw-step');
    var order = $steps.map(function () {
        return $(this).data('step');
    }).get();

    var state = {
        index: 0,
        goal: $wizard.data('goal') || 'general',
        shortcode: '',
        productUrl: '',
        faqCount: 0,
        tools: null,      // jqXHR while installing.
        toolsResult: null,
        finished: false
    };

    // Common questions per goal, plus placeholders for blank rows.
    var samples = {};
    try {
        samples = JSON.parse($('#ffw-sw-samples').text() || '{}');
    } catch (e) {
        samples = {};
    }

    function post(action, data) {
        return $.post(config.ajaxurl, $.extend({ action: action, nonce: config.nonce }, data || {}));
    }

    /* ---------- Navigation ---------- */

    function applyGoal() {
        $wizard.attr('data-goal', state.goal);
        $wizard.find('[data-ffw-goal-only]').each(function () {
            this.hidden = $(this).data('ffw-goal-only') !== state.goal;
        });
        syncSamples();
    }

    function show(index) {
        state.index = Math.max(0, Math.min(index, order.length - 1));
        var key = order[state.index];

        $steps.each(function () {
            this.hidden = $(this).data('step') !== key;
        });

        var isLast = state.index === order.length - 1;
        $wizard.find('.ffw-sw-progress-item').each(function (i) {
            $(this)
                .toggleClass('is-active', i === state.index && !isLast)
                .toggleClass('is-done', i < state.index || isLast);
        });

        // The welcome step has its own way out.
        $wizard.find('.ffw-sw-footer').prop('hidden', isLast);

        applyGoal();

        if (onEnter[key]) {
            onEnter[key]();
        }

        // Move focus to the new heading for screen readers, then show the top of the page.
        var heading = $steps.filter('[data-step="' + key + '"]').find('h1').get(0);
        if (heading) {
            heading.setAttribute('tabindex', '-1');
            heading.focus({ preventScroll: true });
        }
        window.scrollTo(0, 0);
    }

    function next() {
        show(state.index + 1);
    }

    function back() {
        show(state.index - 1);
    }

    var onEnter = {
        faqs: function () {
            if (!$('#ffw-sw-faqs .ffw-sw-faq').length) {
                (samples[state.goal] || []).forEach(function (sample, i) {
                    addRow({ sample: i });
                });

                if (!$('#ffw-sw-faqs .ffw-sw-faq').length) {
                    addRow();
                }
            }
        },
        display: function () {
            var shortcode = state.shortcode || '[ffw_template]';
            $('#ffw-sw-shortcode').text(shortcode);

            var $productLink = $('#ffw-sw-product-link');
            if (state.productUrl) {
                $productLink.attr('href', state.productUrl).prop('hidden', false);
            } else {
                $productLink.prop('hidden', true);
            }
        },
        ready: function () {
            finish('completed');
            renderSummary();
        }
    };

    $wizard.on('click', '[data-ffw-next]', next);
    $wizard.on('click', '[data-ffw-back]', back);
    $wizard.on('click', '[data-ffw-skip]', next);

    $wizard.on('change', 'input[name="ffw_sw_goal"]', function () {
        state.goal = this.value;
        applyGoal();
    });

    /* ---------- Step 2: FAQ rows ---------- */

    var rowTemplate = document.getElementById('ffw-sw-faq-template');

    // Prefilled rows the user has not edited follow the selected goal.
    function syncSamples() {
        var list = samples[state.goal] || [];
        $('#ffw-sw-faqs .ffw-sw-faq.is-prefilled').not('.is-saved').each(function () {
            var sample = list[$(this).data('sample')];
            if (sample) {
                $(this).find('.ffw-sw-faq-question').val(sample[0]);
                $(this).find('.ffw-sw-faq-answer').val(sample[1]);
            }
        });
    }

    function renumber() {
        var $rows = $('#ffw-sw-faqs .ffw-sw-faq');
        $rows.each(function (i) {
            $(this).find('.ffw-sw-faq-num').text('FAQ ' + (i + 1));
        });

        $rows.find('.ffw-sw-faq-remove').prop('disabled', $rows.length <= 1);
    }

    /**
     * Add a FAQ row. options.sample prefills it with that common question.
     */
    function addRow(options) {
        options = options || {};
        if (!rowTemplate) {
            return;
        }

        var blank = samples.blank || ['', ''];
        var $row = $(rowTemplate.content.cloneNode(true)).children().first();
        $row.find('.ffw-sw-faq-question').attr('placeholder', blank[0]);
        $row.find('.ffw-sw-faq-answer').attr('placeholder', blank[1]);

        if (typeof options.sample === 'number') {
            $row.addClass('is-prefilled').data('sample', options.sample);
        }

        $('#ffw-sw-faqs').append($row);
        syncSamples();
        renumber();

        if (options.focus) {
            $row.find('.ffw-sw-faq-question').trigger('focus');
        }
    }

    $('#ffw-sw-add-faq').on('click', function () {
        addRow({ focus: true });
    });

    // Once edited, a prefilled row is the user's own and stops following the goal.
    $wizard.on('input', '.ffw-sw-faq-question, .ffw-sw-faq-answer', function () {
        $(this).closest('.ffw-sw-faq').removeClass('is-prefilled');
    });

    $wizard.on('click', '.ffw-sw-faq-remove', function () {
        var $rows = $('#ffw-sw-faqs .ffw-sw-faq');
        if ($rows.length <= 1) {
            return;
        }

        var $row = $(this).closest('.ffw-sw-faq');
        var $focusTarget = $row.prev('.ffw-sw-faq').length ? $row.prev('.ffw-sw-faq') : $row.next('.ffw-sw-faq');
        $row.remove();
        renumber();
        $focusTarget.find('.ffw-sw-faq-question').trigger('focus');
    });

    function showError(message) {
        $('#ffw-sw-faqs-error').text(message).prop('hidden', !message);
    }

    $wizard.on('input', '.ffw-sw-faq-question', function () {
        showError('');
    });

    $('#ffw-sw-save-faqs').on('click', function () {
        var $button = $(this);
        var $unsaved = $('#ffw-sw-faqs .ffw-sw-faq').not('.is-saved');
        var faqs = [];

        $unsaved.each(function () {
            var question = $.trim($(this).find('.ffw-sw-faq-question').val());
            if (question) {
                faqs.push({
                    $row: $(this),
                    question: question,
                    answer: $.trim($(this).find('.ffw-sw-faq-answer').val())
                });
            }
        });

        // Everything was saved on an earlier visit: just move on.
        if (!faqs.length && state.faqCount) {
            next();
            return;
        }

        if (!faqs.length) {
            showError(strings.needQuestion);
            $unsaved.first().find('.ffw-sw-faq-question').trigger('focus');
            return;
        }

        showError('');

        var data = {
            goal: state.goal,
            category: state.goal === 'general' ? $.trim($('#ffw-sw-category').val()) : '',
            products: state.goal === 'product' ? ($('#ffw-sw-products').val() || []) : [],
            faqs: faqs.map(function (faq) {
                return { question: faq.question, answer: faq.answer };
            })
        };

        var label = $button.text();
        $button.prop('disabled', true).text(strings.saving);

        post('ffw_setup_create_faqs', data)
            .done(function (response) {
                if (!response || !response.success) {
                    showError((response && response.data && response.data.message) || strings.saveFailed);
                    return;
                }

                state.shortcode = response.data.shortcode || '';
                state.productUrl = response.data.product_url || '';
                state.faqCount += (response.data.faq_ids || []).length;

                // Saved rows stay visible but locked, so going back never duplicates them.
                faqs.forEach(function (faq) {
                    faq.$row.addClass('is-saved').find('input, textarea').prop('readonly', true);
                    faq.$row.find('.ffw-sw-faq-remove').remove();
                });

                next();
            })
            .fail(function (xhr) {
                var message = xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message;
                showError(message || strings.saveFailed);
            })
            .always(function () {
                $button.prop('disabled', false).text(label);
            });
    });

    /* ---------- Step 3: copy shortcode ---------- */

    $('#ffw-sw-copy').on('click', function () {
        var $button = $(this);
        var text = $('#ffw-sw-shortcode').text();

        var setLabel = function (label, className) {
            $button.addClass(className || '').find('.ffw-sw-copy-label').text(label);
            setTimeout(function () {
                $button.removeClass('is-copied').find('.ffw-sw-copy-label').text(strings.copy);
            }, 2400);
        };

        var copied = function () {
            setLabel(strings.copied, 'is-copied');
        };

        // Clipboard blocked: select the shortcode so a keyboard copy works.
        var manual = function () {
            var range = document.createRange();
            range.selectNodeContents(document.getElementById('ffw-sw-shortcode'));
            var selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            setLabel(/Mac|iPhone|iPad/.test(navigator.platform) ? strings.copyManualMac : strings.copyManual);
        };

        var fallback = function () {
            if (legacyCopy(text)) {
                copied();
            } else {
                manual();
            }
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(copied, fallback);
        } else {
            fallback();
        }
    });

    function legacyCopy(text) {
        var $temp = $('<textarea readonly>').val(text).css({ position: 'fixed', top: '-1000px' }).appendTo('body');
        $temp[0].select();
        var ok = false;
        try {
            ok = document.execCommand('copy');
        } catch (e) {
            ok = false;
        }
        $temp.remove();
        return ok;
    }

    /* ---------- Step 4: recommended tools ---------- */

    $('#ffw-sw-install-extras').on('click', function () {
        var extras = $wizard.find('.ffw-sw-extra-check:checked').map(function () {
            return this.value;
        }).get();

        if (extras.length) {
            // Runs in the background; the welcome step shows its progress.
            state.tools = post('ffw_setup_install_extras', { extras: extras })
                .done(function (response) {
                    var results = response && response.success && response.data ? response.data.results : null;
                    var failed = !results || Object.keys(results).some(function (key) {
                        return results[key].status !== 'active';
                    });
                    state.toolsResult = failed ? 'partial' : 'ready';
                })
                .fail(function () {
                    state.toolsResult = 'partial';
                })
                .always(function () {
                    state.tools = null;
                    renderSummary();
                });
        }

        next();
    });

    /* ---------- Step 5: welcome ---------- */

    function renderSummary() {
        var $faqs = $('#ffw-sw-summary-faqs');
        if (state.faqCount) {
            var text = state.faqCount === 1 ? strings.oneFaqCreated : (strings.faqsCreated || '%d').replace('%d', state.faqCount);
            $faqs.prop('hidden', false).find('.ffw-sw-summary-text').text(text);
        }

        var $tools = $('#ffw-sw-summary-tools');
        if (state.tools) {
            $tools.prop('hidden', false).attr('class', 'is-working').find('.ffw-sw-summary-text').text(strings.toolsWorking);
        } else if (state.toolsResult === 'ready') {
            $tools.prop('hidden', false).attr('class', 'is-ready')
                .find('.dashicons').attr('class', 'dashicons dashicons-yes-alt').end()
                .find('.ffw-sw-summary-text').text(strings.toolsReady);
        } else if (state.toolsResult === 'partial') {
            $tools.prop('hidden', false).attr('class', 'is-warning')
                .find('.dashicons').attr('class', 'dashicons dashicons-info-outline').end()
                .find('.ffw-sw-summary-text').text(strings.toolsPartial);
        }
    }

    function finish(status) {
        if (state.finished) {
            return $.Deferred().resolve().promise();
        }
        state.finished = true;
        return post('ffw_setup_finish', { status: status, goal: state.goal });
    }

    $('#ffw-sw-exit').on('click', function (e) {
        var href = this.href;
        e.preventDefault();
        finish('skipped').always(function () {
            window.location.href = href;
        });
    });

    /* ---------- Boot ---------- */

    // Product search uses WooCommerce's own selectWoo field when available.
    if (config.hasWoo) {
        $(document.body).trigger('wc-enhanced-select-init');
    }

    show(0);
});
