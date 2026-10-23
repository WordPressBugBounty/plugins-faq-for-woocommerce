/**
 * Happy FAQs → Patterns gallery: filters, search, scaled previews, copy.
 *
 * @since 3.0.0
 */
(function () {
    'use strict';

    var data = window.ffwPatterns || { content: {}, strings: {} };
    var grid = document.getElementById('ffw-pt-grid');
    if (!grid) {
        return;
    }

    var cards = Array.prototype.slice.call(grid.querySelectorAll('.ffw-pt-card'));
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.ffw-pt-tab'));
    var search = document.getElementById('ffw-pt-search');
    var count = document.getElementById('ffw-pt-count');
    var empty = document.getElementById('ffw-pt-empty');
    var reset = document.getElementById('ffw-pt-reset');
    var toast = document.getElementById('ffw-pt-toast');
    var group = 'all';
    var toastTimer;

    // Previews render a 1000px page; scale it down to the card width.
    var PAGE_WIDTH = 1000;

    function scaleFrame(frame) {
        var width = frame.clientWidth;
        if (width) {
            frame.style.setProperty('--ffw-pt-scale', (width / PAGE_WIDTH).toFixed(4));
        }
    }

    var frames = Array.prototype.slice.call(grid.querySelectorAll('.ffw-pt-frame'));

    // Each preview is a full page request, so load them a few at a time.
    var MAX_LOADING = 3;
    var queue = [];
    var loading = 0;

    function pump() {
        while (loading < MAX_LOADING && queue.length) {
            var iframe = queue.shift();
            loading++;
            iframe.src = iframe.getAttribute('data-src');
        }
    }

    function enqueue(frame) {
        var iframe = frame.querySelector('iframe[data-src]');
        if (iframe && !iframe.getAttribute('src') && queue.indexOf(iframe) === -1) {
            queue.push(iframe);
            pump();
        }
    }

    frames.forEach(function (frame) {
        var iframe = frame.querySelector('iframe');
        scaleFrame(frame);
        if (!iframe) {
            return;
        }
        var done = function () {
            if (!frame.classList.contains('is-loaded')) {
                frame.classList.add('is-loaded');
                loading = Math.max(0, loading - 1);
                pump();
            }
        };
        iframe.addEventListener('load', function () {
            if (iframe.getAttribute('src')) {
                done();
            }
        });
        iframe.addEventListener('error', done);
    });

    // Top cards are queued first, so the first screen fills in first.
    frames.forEach(enqueue);

    if ('ResizeObserver' in window) {
        var observer = new ResizeObserver(function (entries) {
            entries.forEach(function (entry) {
                scaleFrame(entry.target);
            });
        });
        frames.forEach(function (frame) {
            observer.observe(frame);
        });
    } else {
        window.addEventListener('resize', function () {
            frames.forEach(scaleFrame);
        });
    }

    // Filters.
    function apply() {
        var term = search ? search.value.trim().toLowerCase() : '';
        var shown = 0;

        cards.forEach(function (card) {
            var inGroup = 'all' === group || card.getAttribute('data-group') === group;
            var inSearch = !term || card.getAttribute('data-search').indexOf(term) !== -1;
            var visible = inGroup && inSearch;
            card.hidden = !visible;
            if (visible) {
                shown++;
            }
        });

        if (count && data.strings.count) {
            count.textContent = data.strings.count.replace('%d', shown);
        }
        if (empty) {
            empty.hidden = shown > 0;
        }

        // Cards that became visible may have a new width, and load first.
        frames.forEach(scaleFrame);
        queue.sort(function (a, b) {
            return (a.closest('.ffw-pt-card').hidden ? 1 : 0) - (b.closest('.ffw-pt-card').hidden ? 1 : 0);
        });
    }

    function setGroup(next) {
        group = next;
        tabs.forEach(function (tab) {
            var active = tab.getAttribute('data-group') === next;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        apply();
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            setGroup(tab.getAttribute('data-group'));
        });
    });

    if (search) {
        search.addEventListener('input', apply);
    }

    if (reset) {
        reset.addEventListener('click', function () {
            if (search) {
                search.value = '';
            }
            setGroup('all');
            if (search) {
                search.focus();
            }
        });
    }

    // Copy the block markup; pasting it in the block editor inserts the blocks.
    function showToast(message) {
        if (!toast) {
            return;
        }
        toast.textContent = message;
        toast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            toast.hidden = true;
        }, 2600);
    }

    function fallbackCopy(text) {
        var area = document.createElement('textarea');
        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();
        var ok = false;
        try {
            ok = document.execCommand('copy');
        } catch (e) {
            ok = false;
        }
        document.body.removeChild(area);
        return ok;
    }

    function markDone(button) {
        button.classList.add('is-done');
        setTimeout(function () {
            button.classList.remove('is-done');
        }, 1800);
        showToast(data.strings.copied);
    }

    grid.addEventListener('click', function (event) {
        var button = event.target.closest('[data-ffw-copy]');
        if (!button) {
            return;
        }

        var text = data.content[button.getAttribute('data-ffw-copy')];
        if (!text) {
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function () {
                markDone(button);
            }, function () {
                if (fallbackCopy(text)) {
                    markDone(button);
                } else {
                    showToast(data.strings.copyFailed);
                }
            });
        } else if (fallbackCopy(text)) {
            markDone(button);
        } else {
            showToast(data.strings.copyFailed);
        }
    });

    apply();
})();
