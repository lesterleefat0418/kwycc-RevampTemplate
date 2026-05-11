document.addEventListener('DOMContentLoaded', function () {
    // Get all dropdown menus
    var langToggle = document.getElementById('kwycc-lang-toggle');
    var langMenu = document.getElementById('kwycc-lang-menu');
    var menuToggle = document.getElementById('kwycc-menu-toggle');
    var mainNav = document.getElementById('kwycc-main-nav');

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

    // Function to switch language and update menu display
    function switchLanguage(lang) {
        currentLang = lang;
        localStorage.setItem('revamppage_lang', lang);

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
    }

    // Initialize with saved language
    switchLanguage(currentLang);

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
                switchLanguage(selectedLang);
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