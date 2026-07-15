document.addEventListener('DOMContentLoaded', function () {
    // Get all dropdown menus
    var langToggle = document.getElementById('kwycc-lang-toggle');
    var langMenu = document.getElementById('kwycc-lang-menu');
    var menuToggle = document.getElementById('kwycc-menu-toggle');
    var mainNav = document.getElementById('kwycc-main-nav');

    // Read menu IDs emitted by header.php (data-menu-zh-ids / data-menu-en-ids)
    var zhIds = [];
    var enIds = [];
    if (mainNav) {
        try {
            var rawZh = mainNav.getAttribute('data-menu-zh-ids') || '[]';
            var rawEn = mainNav.getAttribute('data-menu-en-ids') || '[]';
            zhIds = JSON.parse(rawZh).map(function (n) { return parseInt(n, 10); });
            enIds = JSON.parse(rawEn).map(function (n) { return parseInt(n, 10); });
        } catch (e) {
            zhIds = [];
            enIds = [];
        }
    }

    // Get current language from localStorage or default to 'zh'
    var currentLang = localStorage.getItem('revamppage_lang') || 'zh';

    // Function to close all menus
    function closeAllMenus() {
        if (langToggle) langToggle.setAttribute('aria-expanded', 'false');
        if (langMenu) langMenu.classList.remove('active');
        if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
        if (mainNav) mainNav.classList.remove('active');
    }

    // Function to get language display text
    function getLangDisplayText(lang) {
        return lang === 'zh' ? '繁' : 'EN';
    }

    // Helper: current page ID from body class (WP adds "page-id-123")
    function getCurrentPageIdFromBody() {
        try {
            var m = document.body.className.match(/page-id-(\d+)/);
            return m ? parseInt(m[1], 10) : null;
        } catch (e) {
            return null;
        }
    }

    // Helper: return anchor elements (in menu order) for a language
    function getAnchorsByLangContainer(container) {
        if (!container) return [];
        return Array.prototype.slice.call(container.querySelectorAll('a')).filter(function (a) {
            // ignore anchors that are purely menu toggles or javascript:void(0)
            var href = (a.getAttribute('href') || '').trim();
            return href && href !== '#' && href.toLowerCase().indexOf('javascript:') !== 0;
        });
    }

    // Normalize hrefs for reliable comparison (strip hash, trim trailing slashes except root)
    function normalizeHref(href) {
        try {
            var u = new URL(href, location.origin);
            u.hash = '';
            if (u.pathname && u.pathname.length > 1) {
                u.pathname = u.pathname.replace(/\/+$/, '');
            }
            // Also remove language query params for logical comparison
            removeLangQueryParamsFromURL(u);
            return u.origin + u.pathname + u.search;
        } catch (e) {
            try {
                return href.split('#')[0].replace(/\/+$/, '');
            } catch (ex) {
                return href;
            }
        }
    }

    // Remove language-like query params from a URL searchParams object or URL instance
    function removeLangQueryParamsFromURL(urlOrString) {
        try {
            var u = (typeof urlOrString === 'string') ? new URL(urlOrString, location.origin) : urlOrString;
            var candidates = ['lang', 'language', 'locale', 'site_lang', 'l', '_locale', 'hl'];
            candidates.forEach(function (k) {
                if (u.searchParams.has(k)) {
                    u.searchParams.delete(k);
                }
            });
            // If function received a URL object, we mutated it in-place; if it received string, return cleaned href
            if (typeof urlOrString === 'string') {
                return u.toString();
            }
            return u;
        } catch (e) {
            return urlOrString;
        }
    }

    // Clean external href by removing language query params (returns string)
    function cleanHrefRemoveLang(href) {
        try {
            var u = new URL(href, location.origin);
            removeLangQueryParamsFromURL(u);
            return u.toString();
        } catch (e) {
            // fallback: strip common param patterns
            return href.replace(/[?&](lang|language|locale|site_lang|l)=[^&]*(&|$)/gi, function (m, p1, p2) {
                return p2 === '&' ? p2 : '';
            }).replace(/\?$/, '');
        }
    }

    // Precompute ordered anchors (href strings) for each language once
    var anchorsMap = { zh: [], en: [] };
    try {
        var zhContainer = document.querySelector('.kwycc-menu-lang[data-lang="zh"]');
        var enContainer = document.querySelector('.kwycc-menu-lang[data-lang="en"]');
        anchorsMap.zh = getAnchorsByLangContainer(zhContainer).map(function (a) { return a.href; });
        anchorsMap.en = getAnchorsByLangContainer(enContainer).map(function (a) { return a.href; });
    } catch (e) {
        anchorsMap = { zh: [], en: [] };
    }

    function isPastActivitiesPage() {
        if (document.getElementById('revamppage-past-activities')) {
            return true;
        }

        if (window.location.pathname && /past-activities/i.test(window.location.pathname)) {
            return true;
        }

        return false;
    }
    // Function to switch language and update menu display
    // opts: { navigate: true|false } - when false, DO NOT change location/history (used for init)
    function switchLanguage(lang, opts) {
        if (!lang) return;
        lang = String(lang).toLowerCase();
        if (lang !== 'zh' && lang !== 'en') lang = 'zh';

        console.log('kwycc-nav: switchLanguage ->', lang);

        var doNavigate = !(opts && opts.navigate === false);

        // determine current page id BEFORE changing currentLang
        var currentPageId = getCurrentPageIdFromBody();

        // Remove active class from all language menus
        var allMenus = document.querySelectorAll('.kwycc-menu-lang');
        allMenus.forEach(function (menu) {
            menu.classList.remove('kwycc-menu-lang-active');
        });

        // Add active class to the selected language menu
        var selectedMenu = document.querySelector('.kwycc-menu-lang[data-lang="' + lang + '"]');
        if (selectedMenu) {
            selectedMenu.classList.add('kwycc-menu-lang-active');
        }

        // Update the nav attribute
        if (mainNav) {
            mainNav.setAttribute('data-current-lang', lang);
        }

        // Update language button text
        if (langToggle) {
            var textSpan = langToggle.querySelector('.btn-text');
            if (textSpan) {
                textSpan.textContent = getLangDisplayText(lang);
            }
        }

        // set body attribute and html lang for other scripts
        try {
            document.body.setAttribute('data-lang', lang);
            document.documentElement.setAttribute('lang', lang === 'en' ? 'en' : 'zh-HK');
        } catch (e) { /* ignore */ }

        // dispatch event for other scripts
        try {
            var ev = new CustomEvent('revamppage:languageChanged', { detail: { lang: lang } });
            document.dispatchEvent(ev);
        } catch (e) { /* ignore */ }

        // Update stored language
        currentLang = lang;
        try { localStorage.setItem('revamppage_lang', lang); } catch (e) { /* ignore */ }

        // For past activities page, stay on the same page and only update lang.
        if (isPastActivitiesPage()) {
            try {
                var url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.history.replaceState({}, '', url.toString());
            } catch (e) {
                // ignore
            }
            return;
        }

        // Map using precomputed arrays (fast, immediate)
        try {
            var sourceArr, targetArr;
            if (zhIds.length && enIds.length) {
                if (currentPageId && zhIds.indexOf(currentPageId) !== -1) {
                    sourceArr = zhIds;
                    targetArr = enIds;
                } else if (currentPageId && enIds.indexOf(currentPageId) !== -1) {
                    sourceArr = enIds;
                    targetArr = zhIds;
                } else {
                    // not found: pick based on chosen lang (will pick first item)
                    sourceArr = (lang === 'zh') ? enIds : zhIds;
                    targetArr = (lang === 'zh') ? zhIds : enIds;
                }
            }

            var targetHref = null;

            // If we can map by ID -> index -> href, do it
            if (sourceArr && currentPageId) {
                var idx = sourceArr.indexOf(currentPageId);
                if (idx !== -1) {
                    var list = anchorsMap[lang] || [];
                    if (list[idx]) targetHref = list[idx];
                }
            }

            // Fallback: use first menu item for target language
            if (!targetHref) {
                var fallbackList = anchorsMap[lang] || [];
                if (fallbackList.length) targetHref = fallbackList[0];
            }

            if (currentPageId && targetHref) {
                var cleanTargetHref = cleanHrefRemoveLang(targetHref);
                var normTarget = normalizeHref(cleanTargetHref);
                var normCurrent = normalizeHref(window.location.href);

                // If normalized URLs match, only rewrite history (if allowed) and stop.
                if (normTarget === normCurrent) {
                    if (doNavigate) {
                        try { window.history.replaceState({}, '', cleanTargetHref); } catch (e) { /* ignore */ }
                    }
                    return;
                }

                // Only perform real navigation when explicitly allowed (user action).
                if (doNavigate) {
                    window.location.assign(cleanTargetHref);
                    return;
                }

                // If navigation is disabled (init), do not change location/history.
                return;
            }

            // No menu mapping found: just update the UI and stay on the current page
            if (doNavigate) {
                try {
                    window.history.replaceState({}, '', window.location.pathname + window.location.search + window.location.hash);
                } catch (e) {
                    // ignore
                }
            }
        } catch (e) { /* ignore */ }
    }

    // Initialize with saved language without performing navigation
    switchLanguage(currentLang, { navigate: false });

    // Language toggle
    if (langToggle && langMenu) {
        langToggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // Close menu first
            if (menuToggle) menuToggle.setAttribute('aria-expanded', 'false');
            if (mainNav) mainNav.classList.remove('active');

            // Toggle language menu
            var isExpanded = langToggle.getAttribute('aria-expanded') === 'true';
            langToggle.setAttribute('aria-expanded', !isExpanded);
            langMenu.classList.toggle('active');
        });

        // Handle language selection
        var langLinks = langMenu.querySelectorAll('a');
        langLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var selectedLang = this.getAttribute('data-lang');
                // When user clicks to change language, allow navigation
                switchLanguage(selectedLang, { navigate: true });
                closeAllMenus();
            });
        });
    }

    // Menu toggle
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // Close language menu first
            if (langToggle) langToggle.setAttribute('aria-expanded', 'false');
            if (langMenu) langMenu.classList.remove('active');

            // Toggle main menu
            var isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', !isExpanded);
            mainNav.classList.toggle('active');
        });

        // Close menu when clicking a link
        var menuLinks = mainNav.querySelectorAll('a');
        menuLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                // Don't prevent default for actual menu links - let them navigate
                closeAllMenus();
            });
        });
    }

    // Close all menus when clicking outside
    document.addEventListener('click', function (e) {
        var isLangButton = langToggle && langToggle.contains(e.target);
        var isLangMenu = langMenu && langMenu.contains(e.target);
        var isMenuButton = menuToggle && menuToggle.contains(e.target);
        var isMenuArea = mainNav && mainNav.contains(e.target);

        if (!isLangButton && !isLangMenu && !isMenuButton && !isMenuArea) {
            closeAllMenus();
        }
    });
});