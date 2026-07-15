(function () {
    function $(sel, root) {
        return (root || document).querySelector(sel);
    }

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

    function applyLanguage(lang) {
        var isEn = (lang === 'en');
        var section = document.querySelector('.revamppage-past-activities');

        if (section) {
            var titleCn = section.querySelector('.title-cn');
            var titleEn = section.querySelector('.title-eng');

            if (titleCn) {
                titleCn.style.display = isEn ? 'none' : 'inline-block';
            }

            if (titleEn) {
                titleEn.style.display = isEn ? 'inline-block' : 'none';
            }

            var sectionTitleCn = section.getAttribute('data-cn-title') || '';
            var sectionTitleEn = section.getAttribute('data-en-title') || '';

            if (titleCn && sectionTitleCn) {
                titleCn.textContent = sectionTitleCn;
            }

            if (titleEn && sectionTitleEn) {
                titleEn.textContent = sectionTitleEn;
            }
        }

        $all('.pa-label').forEach(function (el) {
            el.textContent = isEn ? (el.getAttribute('data-en') || el.textContent) : (el.getAttribute('data-cn') || el.textContent);
        });

        $all('.pa-btn').forEach(function (btn) {
            btn.textContent = isEn ? (btn.getAttribute('data-en') || btn.textContent) : (btn.getAttribute('data-cn') || btn.textContent);
        });

        $all('.pa-tag-cn').forEach(function (el) {
            el.style.display = isEn ? 'none' : 'inline';
        });
        $all('.pa-tag-en').forEach(function (el) {
            el.style.display = isEn ? 'inline' : 'none';
        });

        $all('.pa-card-cat-cn').forEach(function (el) {
            el.style.display = isEn ? 'none' : 'inline';
        });
        $all('.pa-card-cat-en').forEach(function (el) {
            el.style.display = isEn ? 'inline' : 'none';
        });

        // Toggle card title language spans
        $all('.pa-title-cn').forEach(function (el) {
            el.style.display = isEn ? 'none' : 'inline';
        });
        $all('.pa-title-en').forEach(function (el) {
            el.style.display = isEn ? 'inline' : 'none';
        });

        // Toggle date language spans
        $all('.pa-date-cn').forEach(function (el) {
            el.style.display = isEn ? 'none' : 'inline';
        });
        $all('.pa-date-en').forEach(function (el) {
            el.style.display = isEn ? 'inline' : 'none';
        });

        $all('#pa-cat option').forEach(function (opt) {
            var text = isEn ? (opt.getAttribute('data-en') || opt.textContent) : (opt.getAttribute('data-cn') || opt.textContent);
            opt.textContent = text;
        });

        $all('#pa-year option').forEach(function (opt) {
            var text = isEn ? (opt.getAttribute('data-en') || opt.textContent) : (opt.getAttribute('data-cn') || opt.textContent);
            opt.textContent = text;
        });

        $all('#pa-month option').forEach(function (opt) {
            var text = isEn ? (opt.getAttribute('data-en') || opt.textContent) : (opt.getAttribute('data-cn') || opt.textContent);
            opt.textContent = text;
        });

        var search = document.getElementById('pa-s');
        if (search) {
            search.placeholder = isEn ? (search.getAttribute('data-en-placeholder') || 'Search') : (search.getAttribute('data-cn-placeholder') || '??');
        }

        var paginationInfo = document.querySelector('.pa-pagination-info');
        if (paginationInfo) {
            paginationInfo.textContent = isEn ? paginationInfo.getAttribute('data-en') : paginationInfo.getAttribute('data-cn');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('pa-filter-form');

        if (form) {
            var data = new FormData(form);

            data.forEach(function (value, key) {
                if (typeof value === 'string' && value.trim() === '') {
                    params.delete(key);
                    return;
                }

                if (key === 'paged') {
                    return;
                }

                if (value) {
                    params.set(key, value);
                }
            });

            var search = $('#pa-s', form);
            if (search) {
                var timeout;
                search.addEventListener('input', function () {
                    clearTimeout(timeout);
                    timeout = setTimeout(function () {
                        // removed auto submit while typing; keep only on Enter
                    }, 600);
                });

                search.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(timeout);
                        form.submit();
                    }
                });
            }
        }

        applyLanguage(getCurrentLang());
    });

    document.addEventListener('revamppage:languageChanged', function (e) {
        var lang = (e && e.detail && e.detail.lang) ? e.detail.lang : getCurrentLang();
        applyLanguage(lang);
    });
})();