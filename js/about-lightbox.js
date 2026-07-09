(function () {
    'use strict';

    var overlay = document.getElementById('revamppage-lightbox');
    if (!overlay) {
        return;
    }

    var about = document.getElementById('revamppage-about');

    var overlayImg = overlay.querySelector('.revamppage-lightbox__img');
    var closeBtn = overlay.querySelector('.revamppage-lightbox__close');

    var aboutTextCn = about.querySelector('.about-cn');
    var aboutTextEng = about.querySelector('.about-eng');
    var closeTextCn = overlay.querySelector('.revamppage-lightbox__close-cn');
    var closeTextEng = overlay.querySelector('.revamppage-lightbox__close-eng');

    // resolve theme url provided by PHP (wp_localize_script)
    var themeUrl = '';
    if (typeof revamppage_about !== 'undefined' && revamppage_about.theme_url) {
        themeUrl = revamppage_about.theme_url.replace(/\/$/, '');
    }

    // Try multiple reliable ways to compute the theme base URL when localization is missing
    function resolveThemeBase() {
        if (themeUrl) {
            return themeUrl;
        }

        // 1) derive from the current script src (works when script is loaded from theme/js/)
        var scripts = document.getElementsByTagName('script');
        for (var i = 0; i < scripts.length; i++) {
            var src = scripts[i].src || '';
            if (src.indexOf('about-lightbox.js') !== -1) {
                return src.replace(/\/js\/about-lightbox\.js(?:\?.*)?$/, '');
            }
        }

        // 2) derive from a stylesheet link that lives in the theme (common)
        var link = document.querySelector('link[href*="/wp-content/themes/"]');
        if (link && link.href) {
            // strip everything after the theme folder
            var m = link.href.match(/^(https?:\/\/[^\/]+\/.*?\/wp-content\/themes\/[^\/]+)/);
            if (m && m[1]) {
                return m[1];
            }
        }

        // 3) last-resort fallback: assume standard WP path and theme folder name "RevampPage"
        return location.origin + '/wp-content/themes/RevampPage';
    }

    // Resolve once and set close button image path immediately (if image element present)
    var resolvedBase = resolveThemeBase();
    var closeImgEl = overlay.querySelector('.revamppage-lightbox__close-img');
    if (closeImgEl && resolvedBase) {
        closeImgEl.src = resolvedBase + '/images/close_lightbox_btn.png';
        closeImgEl.alt = 'Close';
    }

    // Update the small CN/ENG label to reflect selected language
    function updatePageText(lang) {
        var effective = (lang || localStorage.getItem('revamppage_lang') || document.body.getAttribute('data-lang') || '').toString().toLowerCase();
        if (effective === 'en') {
            if (aboutTextEng) aboutTextEng.style.display = 'block';
            if (aboutTextCn) aboutTextCn.style.display = 'none';
            if (closeTextEng) closeTextEng.style.display = 'block';
            if (closeTextCn) closeTextCn.style.display = 'none';
        } else {
            // default to Chinese
            if (aboutTextEng) aboutTextEng.style.display = 'none';
            if (aboutTextCn) aboutTextCn.style.display = 'block';
            if (closeTextEng) closeTextEng.style.display = 'none';
            if (closeTextCn) closeTextCn.style.display = 'block';
        }
    }

    // Listen for global language changed events dispatched by kwycc-nav.js
    if (window.addEventListener) {
        window.addEventListener('revamppage:languageChanged', function (e) {
            var lang = (e && e.detail && e.detail.lang) ? e.detail.lang : null;
            updatePageText(lang);
        }, false);
    }

    // Initialize label immediately
    updatePageText();

    // Open lightbox: always show the actual-size theme asset as requested
    function openLightbox(originalSrc, alt) {
        var peoplePath = resolvedBase + '/images/people_actual_size.png';
        overlayImg.src = peoplePath;
        overlayImg.alt = alt || '';
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        try { closeBtn.focus(); } catch (e) { }
        document.addEventListener('keydown', onKeyDown);
    }

    function closeLightbox() {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        overlayImg.src = '';
        overlayImg.alt = '';
        document.removeEventListener('keydown', onKeyDown);
    }

    function onKeyDown(e) {
        if (e.key === 'Escape' || e.key === 'Esc') {
            closeLightbox();
        }
    }

    // Click on image inside entry-text opens lightbox (delegation)
    document.addEventListener('click', function (ev) {
        var target = ev.target;
        // only images within .entry-text blocks
        if (!target || target.tagName !== 'IMG' || !target.closest('.entry-text')) {
            return;
        }
        ev.preventDefault();
        var largeSrc = target.getAttribute('data-large') || target.src || target.getAttribute('src');
        var alt = target.getAttribute('alt') || '';
        openLightbox(largeSrc, alt);
    });

    // Close button
    if (closeBtn) {
        closeBtn.addEventListener('click', function (ev) {
            ev.preventDefault();
            closeLightbox();
        });
    }

    // Clicking outside the image closes the overlay
    overlay.addEventListener('click', function (ev) {
        if (ev.target === overlayImg || ev.target === closeBtn) {
            return;
        }
        closeLightbox();
    });

    // Prevent clicks on image from bubbling to overlay
    overlayImg.addEventListener('click', function (ev) {
        ev.stopPropagation();
    });
})();