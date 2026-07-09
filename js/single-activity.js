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

        // Related title toggle (CN/EN)
        $all('.related-title-cn', section).forEach(function (el) { el.style.display = isEn ? 'none' : ''; });
        $all('.related-title-eng', section).forEach(function (el) { el.style.display = isEn ? '' : 'none'; });

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
// Overlay gallery behavior (replace existing overlay block in single-activity.js)
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

    var originalCount = items.length || 0;
    var looped = false;
    var isSyncing = false;

    // --- Helpers for scrolling/centering ---
    function centerElementInstant(el) {
        if (!track || !el) return;
        var left = el.offsetLeft - (track.clientWidth - el.offsetWidth) / 2;
        track.scrollLeft = left;
    }

    function centerElementSmooth(el) {
        if (!track || !el) return;
        var left = el.offsetLeft - (track.clientWidth - el.offsetWidth) / 2;
        // prefer track.scrollTo to avoid inconsistent scrollIntoView behavior across browsers
        try {
            track.scrollTo({ left: left, behavior: 'smooth' });
        } catch (e) {
            track.scrollLeft = left;
        }
    }

    // preserve visual continuity: move scrollLeft by delta between two elements' centers
    function centerElementPreserveVisual(targetEl, currentEl) {
        if (!track || !targetEl || !currentEl) return;
        var currentCenterX = currentEl.offsetLeft + currentEl.offsetWidth / 2;
        var targetCenterX = targetEl.offsetLeft + targetEl.offsetWidth / 2;
        var delta = targetCenterX - currentCenterX;
        // apply relative shift so the target ends up visually where the clone was
        track.scrollLeft += delta;
    }

    // --- Setup loop clones and data attributes ---
    function setupLoop() {
        if (looped || originalCount < 2 || !track) return;
        looped = true;

        // mark originals with data attributes
        for (var k = 0; k < originalCount; k++) {
            items[k].dataset.origIndex = String(k);
            items[k].dataset.role = 'original';
        }

        // prepend clones: clone last..first so left side reads last,...,first
        var fragPre = document.createDocumentFragment();
        for (var i = originalCount - 1; i >= 0; i--) {
            var clone = items[i].cloneNode(true);
            clone.classList.add('sag-overlay-clone');
            clone.dataset.origIndex = String(i);
            clone.dataset.role = 'clone';
            fragPre.appendChild(clone);
        }
        track.insertBefore(fragPre, track.firstChild);

        // append clones: clone first..last so right side reads first,...,last
        var fragPost = document.createDocumentFragment();
        for (var j = 0; j < originalCount; j++) {
            var clone2 = items[j].cloneNode(true);
            clone2.classList.add('sag-overlay-clone');
            clone2.dataset.origIndex = String(j);
            clone2.dataset.role = 'clone';
            fragPost.appendChild(clone2);
        }
        track.appendChild(fragPost);

        // refresh items list to include clones
        items = qsa('.sag-overlay__item', overlay);
    }

    // find index of item whose center is closest to track center
    function findClosestIdx() {
        if (!track || !items.length) return 0;
        var centerX = track.scrollLeft + track.clientWidth / 2;
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

    // map item index -> original index via data attribute
    function mapCloneToOriginalIndex(idx) {
        if (!items[idx]) return 0;
        var di = items[idx].dataset.origIndex;
        return di ? parseInt(di, 10) : 0;
    }

    // find the original element in the middle block that matches origIndex
    function findOriginalElementByOrigIndex(origIndex) {
        // originals are the ones with data-role="original"
        for (var i = 0; i < items.length; i++) {
            var it = items[i];
            if (it.dataset && it.dataset.role === 'original' && String(it.dataset.origIndex) === String(origIndex)) {
                return it;
            }
        }
        return null;
    }

    // update active class and perform silent sync if centered item is a clone
    function updateActiveByCenter() {
        if (!track || !items.length) return;
        var idx = findClosestIdx();
        var current = items[idx];
        if (!current) return;

        // mark visible item active immediately
        items.forEach(function (it, i) {
            if (i === idx) it.classList.add('active'); else it.classList.remove('active');
        });

        // if not looped, nothing else to do
        if (!looped) return;

        // if current is a clone, find its original and silently sync
        if (current.dataset && current.dataset.role === 'clone') {
            if (isSyncing) return;
            isSyncing = true;

            var origIndex = mapCloneToOriginalIndex(idx);
            var target = findOriginalElementByOrigIndex(origIndex);

            // small delay to let smooth scroll finish visually, then perform relative jump
            setTimeout(function () {
                requestAnimationFrame(function () {
                    if (target && current) {
                        // preserve visual continuity: move the original to the same visual center
                        centerElementPreserveVisual(target, current);

                        // update active to the original element
                        items.forEach(function (it) {
                            if (it === target) it.classList.add('active'); else it.classList.remove('active');
                        });
                    }
                    isSyncing = false;
                });
            }, 100);
        }
    }

    var debouncedUpdateActive = (function () {
        var t;
        return function () {
            clearTimeout(t);
            t = setTimeout(updateActiveByCenter, 90);
        };
    })();

    // open overlay at original thumbnail index
    function openOverlay(index) {
        if (!overlay) return;
        // ensure loop set up before opening
        setupLoop();

        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('revamp-overlay-open');

        if (track) track.focus();

        // compute target index in items list: originals are in the middle block
        var targetIndex = index;
        if (looped) {
            // find the original element that has origIndex === index
            var targetOriginal = findOriginalElementByOrigIndex(index);
            if (targetOriginal) {
                // center smoothly
                centerElementSmooth(targetOriginal);
                // ensure active updates after scroll finishes
                setTimeout(function () { updateActiveByCenter(); }, 300);
                return;
            }
            // fallback: try index + originalCount
            targetIndex = Math.max(0, Math.min(items.length - 1, index + originalCount));
        } else {
            targetIndex = Math.max(0, Math.min(items.length - 1, index));
        }

        var el = items[targetIndex];
        if (el) {
            centerElementSmooth(el);
            setTimeout(function () { updateActiveByCenter(); }, 300);
        }
    }

    function closeOverlay() {
        if (!overlay) return;
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('revamp-overlay-open');
        items.forEach(function (it) { it.classList.remove('active'); });
    }

    // scroll by delta items (delta = ±1)
    function scrollToIndex(delta) {
        if (!track || !items.length) return;
        var closestIdx = findClosestIdx();
        var targetIdx = closestIdx + delta;

        // allow moving into clone region when looped
        if (!looped) {
            targetIdx = Math.max(0, Math.min(items.length - 1, targetIdx));
        } else {
            if (targetIdx < 0) targetIdx = 0;
            if (targetIdx >= items.length) targetIdx = items.length - 1;
        }

        var el = items[targetIdx];
        if (el) {
            centerElementSmooth(el);
            // after smooth scroll finished, sync to original if needed
            setTimeout(function () { updateActiveByCenter(); }, 260);
        }
    }

    // wire thumbnail clicks
    thumbs.forEach(function (thumb, idx) {
        thumb.addEventListener('click', function (e) {
            e.preventDefault();
            openOverlay(idx);
        }, { passive: false });
        var img = thumb.querySelector('img');
        if (img) img.style.cursor = 'zoom-in';
    });

    // controls
    if (btnClose) btnClose.addEventListener('click', closeOverlay);
    if (btnPrev) btnPrev.addEventListener('click', function () { scrollToIndex(-1); });
    if (btnNext) btnNext.addEventListener('click', function () { scrollToIndex(1); });

    // backdrop click
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeOverlay();
    });

    // scroll / touch / mouse handling
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

    // keyboard handling
    document.addEventListener('keydown', function (e) {
        if (!overlay.classList.contains('open')) return;
        if (e.key === 'Escape') { closeOverlay(); return; }
        if (e.key === 'ArrowLeft') { e.preventDefault(); scrollToIndex(-1); return; }
        if (e.key === 'ArrowRight') { e.preventDefault(); scrollToIndex(1); return; }
    });

    // prevent page scroll while overlay open (mobile)
    overlay.addEventListener('touchmove', function (e) {
        if (!overlay.classList.contains('open')) return;
        // intentionally empty: keep default horizontal swipe behavior on track
    }, { passive: true });

    // ensure setupLoop runs early so initial calculations are correct
    // but do not force open overlay; just prepare clones if overlay exists
    try { setupLoop(); } catch (e) { /* ignore */ }

    // do not call updateActiveByCenter here unconditionally; wait until user opens overlay or scroll occurs
})();

