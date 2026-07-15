(function () {
    function $(sel, root) {
        return (root || document).querySelector(sel);
    }

    function $all(sel, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(sel));
    }

    function normalizeLang(lang) {
        if (!lang) {
            return 'zh';
        }

        lang = String(lang).toLowerCase();
        return (lang.indexOf('en') === 0) ? 'en' : 'zh';
    }

    function getCurrentLang() {
        var body = document.body;
        if (body && body.getAttribute('data-lang')) {
            return normalizeLang(body.getAttribute('data-lang'));
        }
        try {
            return normalizeLang(localStorage.getItem('revamppage_lang') || 'zh');
        } catch (e) {
            return 'zh';
        }
    }

    function applyLanguage(lang) {
        var isEn = (normalizeLang(lang) === 'en');
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

        $all('.pa-tag-cn').forEach(function (el) {
            el.style.display = isEn ? 'none' : 'inline';
        });
        $all('.pa-tag-en').forEach(function (el) {
            el.style.display = isEn ? 'inline' : 'none';
        });

        $all('.pa-title-cn').forEach(function (el) {
            el.style.display = isEn ? 'none' : 'inline';
        });
        $all('.pa-title-en').forEach(function (el) {
            el.style.display = isEn ? 'inline' : 'none';
        });

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
            search.placeholder = isEn ? (search.getAttribute('data-en-placeholder') || 'Search') : (search.getAttribute('data-cn-placeholder') || '搜尋活動');
        }

        var paginationInfo = document.querySelector('.pa-pagination-info');
        if (paginationInfo) {
            paginationInfo.textContent = isEn ? paginationInfo.getAttribute('data-en') : paginationInfo.getAttribute('data-cn');
        }
    }

    function setFormLang(form, lang) {
        if (!form) {
            return;
        }

        var langInput = form.querySelector('input[name="lang"]');
        if (langInput) {
            langInput.value = normalizeLang(lang);
        }
    }

    function buildFilterParams(form, pageNum) {
        var params = new URLSearchParams();

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
        }

        if (pageNum) {
            params.set('paged', pageNum);
        }

        return params;
    }

    function updateBrowserUrl(params) {
        var url = new URL(window.location.href);

        ['cat', 'year', 'month', 's', 'paged', 'page_id', 'lang'].forEach(function (key) {
            url.searchParams.delete(key);
        });

        params.forEach(function (value, key) {
            if (value) {
                url.searchParams.set(key, value);
            }
        });

        window.history.pushState({}, '', url.toString());
    }

    function requestPastActivities(pageNum) {
        var form = document.getElementById('pa-filter-form');
        if (!form) {
            return;
        }

        var currentLang = getCurrentLang();
        setFormLang(form, currentLang);

        var params = buildFilterParams(form, pageNum);

        var pageIdInput = form.querySelector('input[name="page_id"]');
        if (pageIdInput && pageIdInput.value) {
            params.set('page_id', pageIdInput.value);
        }

        var langInput = form.querySelector('input[name="lang"]');
        if (langInput && langInput.value) {
            params.set('lang', langInput.value);
        }

        params.set('action', window.revamppagePastActivities && window.revamppagePastActivities.ajax_action ? window.revamppagePastActivities.ajax_action : 'revamppage_filter_past_activities');
        params.set('nonce', window.revamppagePastActivities && window.revamppagePastActivities.nonce ? window.revamppagePastActivities.nonce : '');

        updateBrowserUrl(params);

        var grid = document.getElementById('pa-grid');
        if (grid) {
            grid.setAttribute('aria-busy', 'true');
        }

        fetch((window.revamppagePastActivities && window.revamppagePastActivities.ajax_url) || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: params.toString(),
            credentials: 'same-origin'
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (result) {
            if (result && result.success && result.data) {
                if (grid && result.data.grid_html) {
                    grid.innerHTML = result.data.grid_html;
                }

                var existingPagination = document.querySelector('.pa-pagination');
                if (result.data.pagination_html) {
                    if (existingPagination) {
                        existingPagination.outerHTML = result.data.pagination_html;
                    } else {
                        var wrapper = document.createElement('div');
                        wrapper.innerHTML = result.data.pagination_html;
                        if (grid && grid.nextSibling) {
                            grid.parentNode.insertBefore(wrapper.firstElementChild, grid.nextSibling);
                        } else if (grid) {
                            grid.parentNode.appendChild(wrapper.firstElementChild);
                        }
                    }
                } else if (existingPagination) {
                    existingPagination.remove();
                }

                applyLanguage(currentLang);
            }
        })
        .catch(function () {
            if (grid) {
                grid.setAttribute('aria-busy', 'false');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('pa-filter-form');

        if (form) {
            setFormLang(form, getCurrentLang());

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                requestPastActivities('1');
            });

            var search = $('#pa-s', form);
            if (search) {
                var timeout;

                search.addEventListener('input', function () {
                    clearTimeout(timeout);
                    timeout = setTimeout(function () {
                        // keep current behavior: submit only on Enter
                    }, 600);
                });

                search.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        clearTimeout(timeout);
                        requestPastActivities('1');
                    }
                });
            }
        }

        document.addEventListener('click', function (e) {
            var paginationLink = e.target.closest('.pa-pagination a');
            if (!paginationLink) {
                return;
            }

            var href = paginationLink.getAttribute('href');
            if (!href) {
                return;
            }

            e.preventDefault();

            var url = new URL(href, window.location.href);
            var pageNum = url.searchParams.get('paged');
            if (pageNum) {
                requestPastActivities(pageNum);
            }
        });

        applyLanguage(getCurrentLang());
    });

    document.addEventListener('revamppage:languageChanged', function (e) {
        var lang = (e && e.detail && e.detail.lang) ? e.detail.lang : getCurrentLang();
        applyLanguage(lang);

        var form = document.getElementById('pa-filter-form');
        if (form) {
            setFormLang(form, lang);
            requestPastActivities('1');
        }
    });
})();