(function () {
    // helper: select nodes with optional root
    function $all(sel, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(sel));
    }

    function getCurrentLang() {
        var body = document.body;
        if (body && body.getAttribute('data-lang')) {
            return body.getAttribute('data-lang');
        }
        try {
            return localStorage.getItem('revamppage_lang') || 'zh';
        } catch (e) {
            return 'zh';
        }
    }

    // --- Fit text utilities ---
    function debounce(fn, wait) {
        var t;
        return function () {
            clearTimeout(t);
            t = setTimeout(fn, wait);
        };
    }

    // Fit text into maxLines by reducing font-size (works with wrapping)
    function fitTextToContainer(el, options) {
        options = options || {};
        var minPx = options.minPx || 12;
        var step = options.step || 1;
        var maxLines = options.maxLines || null;

        // reset to CSS base first
        el.style.fontSize = '';
        var comp = window.getComputedStyle(el);
        var current = parseFloat(comp.fontSize) || 16;

        // if not visible or no width, skip
        if (comp.display === 'none' || el.offsetWidth === 0) return;

        // determine allowed maxHeight from maxLines
        if (!maxLines) {
            // try CSS custom property --sag-title-max-lines
            var cssMaxLines = el.style.getPropertyValue('--sag-title-max-lines') || comp.getPropertyValue('--sag-title-max-lines') || null;
            if (cssMaxLines) {
                maxLines = parseInt(cssMaxLines, 10) || 2;
            } else {
                maxLines = 2; // default
            }
        }

        // compute one line height (use computed line-height; if 'normal' fallback to font-size * 1.2)
        var lineHeight = parseFloat(comp.lineHeight);
        if (!lineHeight || isNaN(lineHeight)) {
            lineHeight = current * 1.2;
        }

        var maxHeight = lineHeight * maxLines;

        // If already fits within maxHeight, nothing to do
        if (el.scrollHeight <= maxHeight + 1) {
            return;
        }

        // Reduce font-size until fits or min reached
        var iterations = 0;
        while (el.scrollHeight > maxHeight + 1 && current > minPx && iterations < 80) {
            current = Math.max(minPx, current - step);
            el.style.fontSize = current + 'px';
            iterations++;
        }
    }

    function fitAllTitles() {
        var section = document.querySelector('.single-activity-gallery');
        if (!section) return;

        var els = $all('.title-cn, .title-eng, .sag-title', section);
        els.forEach(function (el) {
            var cs = window.getComputedStyle(el);
            if (cs.display === 'none' || el.offsetWidth === 0) return;
            // allow per-element override via data-max-lines attribute or CSS custom property
            var maxLinesAttr = el.getAttribute('data-max-lines');
            var maxLines = maxLinesAttr ? parseInt(maxLinesAttr, 10) : null;
            fitTextToContainer(el, { minPx: 12, step: 1, maxLines: maxLines });
        });
    }

    // --- Language toggle ---
    function applyLanguage(lang) {
        var isEn = (lang === 'en');
        var section = document.querySelector('.single-activity-gallery') || document;

        // Title / labelled spans
        $all('.title-cn', section).forEach(function (el) { el.style.display = isEn ? 'none' : ''; });
        $all('.title-eng', section).forEach(function (el) { el.style.display = isEn ? '' : 'none'; });

        $all('.pa-title-cn, .sag-title-cn', section).forEach(function (el) { el.style.display = isEn ? 'none' : ''; });
        $all('.pa-title-en, .sag-title-en', section).forEach(function (el) { el.style.display = isEn ? '' : 'none'; });

        $all('.sag-date-cn', section).forEach(function (el) { el.style.display = isEn ? 'none' : ''; });
        $all('.sag-date-en', section).forEach(function (el) { el.style.display = isEn ? '' : 'none'; });

        $all('.date-cn', section).forEach(function (el) { el.style.display = isEn ? 'none' : ''; });
        $all('.date-eng', section).forEach(function (el) { el.style.display = isEn ? '' : 'none'; });

        $all('.smartteen-cn', section).forEach(function (el) { el.style.display = isEn ? 'none' : ''; });
        $all('.smartteen-eng', section).forEach(function (el) { el.style.display = isEn ? '' : 'none'; });

        // Related title toggle (CN/EN)
        $all('.related-title-cn', section).forEach(function (el) { el.style.display = isEn ? 'none' : ''; });
        $all('.related-title-eng', section).forEach(function (el) { el.style.display = isEn ? '' : 'none'; });

        $all('.sag-overlay__close-cn, .smartteen-overlay__close-cn', section).forEach(function (el) { el.style.display = isEn ? 'none' : ''; });
        $all('.sag-overlay__close-eng, .smartteen-overlay__close-eng', section).forEach(function (el) { el.style.display = isEn ? '' : 'none'; });

        // Swap elements using data-cn / data-en attributes
        $all('[data-cn]', section).forEach(function (el) {
            try {
                var cn = el.getAttribute('data-cn') || '';
                var en = el.getAttribute('data-en') || '';
                el.textContent = isEn ? (en || el.textContent) : (cn || el.textContent);
            } catch (e) { /* ignore */ }
        });

        // After language swap, re-fit title sizes
        setTimeout(fitAllTitles, 60);
    }

    // DOM ready
    document.addEventListener('DOMContentLoaded', function () {
        applyLanguage(getCurrentLang());
        setTimeout(fitAllTitles, 60);
    });

    // Window resize
    window.addEventListener('resize', debounce(fitAllTitles, 150));

    // Language change event from your site
    document.addEventListener('revamppage:languageChanged', function (e) {
        var lang = (e && e.detail && e.detail.lang) ? e.detail.lang : getCurrentLang();
        applyLanguage(lang);
    });

    // Expose for manual debug if needed
    window.__revamppage_fitAllTitles = fitAllTitles;
})();

// Overlay gallery behavior (append to single-activity.js)
(function () {
    function qs(sel, root) { return (root || document).querySelector(sel); }
    function qsa(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

    var overlay = qs('#sag-overlay');
    if (!overlay) return;

    var track = qs('.sag-overlay__track', overlay);
    var items = qsa('.sag-overlay__item', overlay);
    var thumbs = qsa('.sag-gallery .sag-gallery-item', document);
    var btnClose = qs('.sag-overlay__close', overlay);
    var btnPrev = qs('.sag-overlay__prev', overlay);
    var btnNext = qs('.sag-overlay__next', overlay);

    var currentIndex = 0;
    var isProgrammaticScroll = false;
    var programmaticScrollTimer = null;

    function refreshItems() {
        items = qsa('.sag-overlay__item', overlay);
    }

    function clampIndex(index) {
        if (!items.length) return 0;
        return Math.max(0, Math.min(items.length - 1, index));
    }

    function updateNavButtons() {
        if (!btnPrev || !btnNext || !items.length) return;
        btnPrev.disabled = currentIndex <= 0;
        btnNext.disabled = currentIndex >= items.length - 1;
    }

    function disableScrollSnap(disabled) {
        if (!track) return;
        if (disabled) {
            track.classList.add('revamp-overlay-no-snap');
        } else {
            track.classList.remove('revamp-overlay-no-snap');
        }
    }

    function getCenteredScrollLeft(el) {
        if (!track || !el) return 0;
        var left = el.offsetLeft - (track.clientWidth - el.offsetWidth) / 2;
        var maxLeft = Math.max(0, track.scrollWidth - track.clientWidth);
        return Math.max(0, Math.min(left, maxLeft));
    }

    function scheduleProgrammaticUpdate(delay) {
        if (programmaticScrollTimer) {
            clearTimeout(programmaticScrollTimer);
        }
        isProgrammaticScroll = true;
        programmaticScrollTimer = setTimeout(function () {
            isProgrammaticScroll = false;
            programmaticScrollTimer = null;
        }, delay);
    }

    function updateCarouselPadding() {
        if (!track || !items.length) return;
        var item = items[0];
        if (!item.offsetWidth || !track.clientWidth) return;

        var gap = Math.max(0, (track.clientWidth - item.offsetWidth) / 2);
        track.style.paddingInlineStart = gap + 'px';
        track.style.paddingInlineEnd = gap + 'px';
        track.style.scrollPaddingInline = gap + 'px';
    }

    function centerElement(el, smooth) {
        if (!track || !el) return;

        try {
            el.scrollIntoView({ inline: 'center', block: 'nearest', behavior: smooth ? 'smooth' : 'auto' });
        } catch (e) {
            var left = getCenteredScrollLeft(el);
            if (smooth) {
                try {
                    track.scrollTo({ left: left, behavior: 'smooth' });
                } catch (err) {
                    track.scrollLeft = left;
                }
            } else {
                track.scrollLeft = left;
            }
        }

        if (smooth) {
            scheduleProgrammaticUpdate(360);
        } else {
            scheduleProgrammaticUpdate(300);
        }
    }

    function findClosestIdx() {
        if (!track || !items.length) return 0;

        var scrollLeft = track.scrollLeft;
        var centerX = scrollLeft + track.clientWidth / 2;
        var closestIdx = 0;
        var closestDist = Infinity;

        items.forEach(function (it, i) {
            var mid = it.offsetLeft + it.offsetWidth / 2;
            var dist = Math.abs(mid - centerX);
            if (dist < closestDist) {
                closestDist = dist;
                closestIdx = i;
            }
        });

        return closestIdx;
    }

    function setActive(index) {
        refreshItems();
        if (!items.length) return;

        var safeIndex = clampIndex(index);
        items.forEach(function (it, i) {
            it.classList.toggle('active', i === safeIndex);
        });
        currentIndex = safeIndex;
        updateNavButtons();
    }

    function updateActiveByCenter() {
        if (!track || !items.length || isProgrammaticScroll) return;
        setActive(findClosestIdx());
    }

    var debouncedUpdateActive = (function () {
        var t;
        return function () {
            if (isProgrammaticScroll) return;
            clearTimeout(t);
            t = setTimeout(updateActiveByCenter, 80);
        };
    })();

    function showIndex(index, smooth) {
        refreshItems();
        if (!items.length) return;

        currentIndex = clampIndex(index);
        var el = items[currentIndex];
        if (el) {
            setActive(currentIndex);
            centerElement(el, !!smooth);
        }
    }

    function openOverlay(index) {
        if (!overlay) return;

        refreshItems();
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('revamp-overlay-open');

        function activateOverlay() {
            refreshItems();
            updateCarouselPadding();
            showIndex(index, false);
            updateNavButtons();
            if (track) track.focus();
        }

        var scheduleActivate = function () {
            activateOverlay();
        };

        if (window.requestAnimationFrame) {
            window.requestAnimationFrame(function () {
                setTimeout(scheduleActivate, 100);
            });
        } else {
            setTimeout(scheduleActivate, 120);
        }
    }

    function closeOverlay() {
        if (!overlay) return;
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('revamp-overlay-open');

        if (programmaticScrollTimer) {
            clearTimeout(programmaticScrollTimer);
            programmaticScrollTimer = null;
            isProgrammaticScroll = false;
        }

        refreshItems();
        items.forEach(function (it) {
            it.classList.remove('active');
        });
    }

    function scrollToIndex(delta) {
        if (!track || !items.length) return;

        showIndex(currentIndex + delta, true);
    }

    thumbs.forEach(function (thumb, idx) {
        thumb.setAttribute('data-gallery-index', idx);
        thumb.addEventListener('click', function (e) {
            e.preventDefault();
            var thumbEl = e.currentTarget;
            var clickedIndex = parseInt(
                thumbEl.getAttribute('data-id') ||
                thumbEl.getAttribute('data-index') ||
                thumbEl.getAttribute('data-gallery-index') ||
                idx,
                10
            );
            if (isNaN(clickedIndex)) clickedIndex = idx;
            openOverlay(clickedIndex);
        }, { passive: false });

        var img = thumb.querySelector('img');
        if (img) img.style.cursor = 'zoom-in';
    });

    if (btnClose) btnClose.addEventListener('click', closeOverlay);
    if (btnPrev) btnPrev.addEventListener('click', function () { scrollToIndex(-1); });
    if (btnNext) btnNext.addEventListener('click', function () { scrollToIndex(1); });

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeOverlay();
    });

    if (track) {
        track.addEventListener('scroll', debouncedUpdateActive, { passive: true });

        var touchTimer;
        track.addEventListener('touchstart', function () { clearTimeout(touchTimer); }, { passive: true });
        track.addEventListener('touchend', function () {
            clearTimeout(touchTimer);
            touchTimer = setTimeout(updateActiveByCenter, 140);
        }, { passive: true });

        track.addEventListener('mouseup', function () {
            setTimeout(updateActiveByCenter, 80);
        }, { passive: true });
    }

    document.addEventListener('keydown', function (e) {
        if (!overlay.classList.contains('open')) return;
        if (e.key === 'Escape') {
            closeOverlay();
            return;
        }
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            scrollToIndex(-1);
            return;
        }
        if (e.key === 'ArrowRight') {
            e.preventDefault();
            scrollToIndex(1);
            return;
        }
    });

    overlay.addEventListener('touchmove', function (e) {
        if (!overlay.classList.contains('open')) return;
    }, { passive: true });
})();

