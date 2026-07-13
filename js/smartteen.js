document.addEventListener('DOMContentLoaded', function () {
    var track = document.getElementById('smartteenCarousel');
    if (!track) return;

    var viewport = track.parentElement;
    if (!viewport || !viewport.classList.contains('smartteen-carousel-viewport')) return;

    var originals = Array.prototype.slice.call(track.querySelectorAll('.smartteen-card'));
    if (!originals.length) return;

    var btnPrev = document.querySelector('.smartteen-carousel__prev');
    var btnNext = document.querySelector('.smartteen-carousel__next');
    var overlay = document.getElementById('smartteenOverlay');
    var overlayClose = overlay ? overlay.querySelector('.smartteen-overlay__close') : null;
    var overlayPdfIframe = overlay ? overlay.querySelector('#smartteenOverlayPdf') : null;
    var overlayPdfLink = overlay ? overlay.querySelector('.smartteen-overlay__pdf-link a') : null;
    var overlayPageViewer = overlay ? overlay.querySelector('.smartteen-overlay__book-page') : null; // kept for backwards compatibility if present

    var cards = originals.slice();
    var currentIndex = 0;
    var currentCard = null;
    var activeBook = null;
    var activePage = 0;
    var resizeTimer = null;
    var scrollEndTimer = null;
    var rafId = null;
    var isSnapping = false; // true while smooth snapping/scrolling to center
    var overlayPdfFallbackTimer = null; // timer to detect iframe embed failure and fallback

    function getCurrentLang() {
        if (document.body && document.body.getAttribute('data-lang')) {
            return document.body.getAttribute('data-lang');
        }
        try {
            return localStorage.getItem('revamppage_lang') || 'zh';
        } catch (e) {
            return 'zh';
        }
    }

    function applySmartteenLanguage() {
        var lang = getCurrentLang();
        var isEn = lang === 'en';
        var cnEls = document.querySelectorAll('.smartteen-cn, .smartteen-overlay__close-cn');
        var enEls = document.querySelectorAll('.smartteen-eng, .smartteen-overlay__close-eng');
        cnEls.forEach(function (el) { el.style.display = isEn ? 'none' : ''; });
        enEls.forEach(function (el) { el.style.display = isEn ? '' : 'none'; });
    }

    function removeClones() {
        Array.prototype.slice.call(track.querySelectorAll('.smartteen-card[data-clone], .smartteen-card.clone, .smartteen-card.__clone')).forEach(function (clone) {
            if (clone && clone.parentNode) {
                clone.parentNode.removeChild(clone);
            }
        });
    }

    function cloneForInfiniteLoop() {
        removeClones();
        var fragPre = document.createDocumentFragment();
        var fragPost = document.createDocumentFragment();

        originals.forEach(function (orig) {
            var clonePre = orig.cloneNode(true);
            clonePre.dataset.clone = 'pre';
            fragPre.appendChild(clonePre);

            var clonePost = orig.cloneNode(true);
            clonePost.dataset.clone = 'post';
            fragPost.appendChild(clonePost);
        });

        track.insertBefore(fragPre, track.firstChild);
        track.appendChild(fragPost);
    }

    function refreshCardList() {
        cards = Array.prototype.slice.call(track.querySelectorAll('.smartteen-card'));
    }

    function calculateSpacing() {
        var sampleCard = cards[0];
        if (!sampleCard) return { cardWidth: 0, gap: 0, cardSpacing: 0 };
        var cardWidth = sampleCard.offsetWidth;
        var gapStr = window.getComputedStyle(track).gap;
        var gap = gapStr && gapStr !== 'normal' ? parseFloat(gapStr) : 0;
        var cardSpacing = cardWidth + gap;
        return { cardWidth: cardWidth, gap: gap, cardSpacing: cardSpacing };
    }

    function updateCardSizes() {
        var visibleCount = Math.min(originals.length, 5);
        var gap = 0;
        var trackStyle = window.getComputedStyle(track);
        var gapValue = trackStyle.gap || trackStyle.columnGap || trackStyle.webkitColumnGap;
        if (gapValue) {
            gap = parseFloat(gapValue) || 0;
        }

        var availableWidth = Math.max(0, viewport.clientWidth - gap * (visibleCount - 1));
        var cardWidth = Math.floor(availableWidth / visibleCount);

        cards.forEach(function (card) {
            card.style.width = cardWidth + 'px';
            card.style.height = '100%';
        });

        track.style.width = (cards.length * cardWidth + gap * (cards.length - 1)) + 'px';
    }

    function findClosestCard() {
        var wrapRect = viewport.getBoundingClientRect();
        var wrapCenter = wrapRect.left + wrapRect.width / 2;
        var closest = null;
        var minDist = Infinity;
        cards.forEach(function (card) {
            var rect = card.getBoundingClientRect();
            var center = rect.left + rect.width / 2;
            var dist = Math.abs(center - wrapCenter);
            if (dist < minDist) {
                minDist = dist;
                closest = card;
            }
        });
        return closest;
    }

    function updateActiveState() {
        refreshCardList();
        var closest = findClosestCard();
        if (!closest) return;
        cards.forEach(function (card) {
            card.classList.toggle('active', card === closest);
        });
    }

    function centerInitialCard() {
        refreshCardList();
        var middleCard = originals[0];
        if (!middleCard) return;
        if (cards.length !== originals.length) {
            middleCard = cards[Math.floor(cards.length / 2) - Math.floor(originals.length / 2)];
        }
        if (!middleCard) return;
        var target = middleCard.offsetLeft + middleCard.offsetWidth / 2 - viewport.clientWidth / 2;
        viewport.scrollLeft = target;
    }

    function snapToNearest() {
        refreshCardList();
        var closest = findClosestCard();
        if (!closest) return;
        var target = closest.offsetLeft + closest.offsetWidth / 2 - viewport.clientWidth / 2;
        viewport.scrollTo({ left: target, behavior: 'smooth' });
        setTimeout(updateActiveState, 320);
    }

    function maybeWrapScroll() {
        if (originals.length < 5) return;
        refreshCardList();
        var spacing = calculateSpacing();
        if (spacing.cardSpacing === 0) return;

        var totalOriginalWidth = originals.length * spacing.cardSpacing;
        if (viewport.scrollLeft <= spacing.cardSpacing * 0.5) {
            viewport.scrollLeft += totalOriginalWidth;
            return;
        }

        var maxScroll = track.scrollWidth - viewport.clientWidth;
        if (viewport.scrollLeft >= maxScroll - spacing.cardSpacing * 0.5) {
            viewport.scrollLeft -= totalOriginalWidth;
            return;
        }
    }

    function navigate(delta) {
        refreshCardList();
        var closest = findClosestCard();
        if (!closest) return;
        var current = cards.indexOf(closest);
        var targetIndex = current + delta;
        if (targetIndex < 0) targetIndex = cards.length - 1;
        if (targetIndex >= cards.length) targetIndex = 0;
        var targetCard = cards[targetIndex];
        if (!targetCard) return;
        var left = targetCard.offsetLeft + targetCard.offsetWidth / 2 - viewport.clientWidth / 2;
        viewport.scrollTo({ left: left, behavior: 'smooth' });
        setTimeout(updateActiveState, 320);
    }

    function openOverlay(book) {
        if (!overlay) return;
        // Safety guard: ensure the current centered/active card corresponds to the requested book.
        // This prevents opening the overlay for non-active books if some caller invokes openOverlay directly.
        try {
            var activeCard = track.querySelector('.smartteen-card.active');
            if (activeCard && book && typeof book.id !== 'undefined') {
                var activeBookData = activeCard.getAttribute('data-book');
                if (activeBookData) {
                    var parsed = JSON.parse(activeBookData);
                    if (parsed && parsed.id && parsed.id !== book.id) {
                        // requested book is not the active card - refuse to open
                        return;
                    }
                }
            } else if (!activeCard) {
                // no active card found - ensure the closest card matches the requested book
                var closest = findClosestCard();
                if (closest) {
                    var closestData = closest.getAttribute('data-book');
                    if (closestData) {
                        var parsedClosest = JSON.parse(closestData);
                        if (parsedClosest && parsedClosest.id && parsedClosest.id !== book.id) {
                            return;
                        }
                    }
                }
            }
        } catch (e) {
            // parsing error - fail closed
            return;
        }

        activeBook = book;
        activePage = 0;
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('smartteen-overlay-open');

        // If a PDF url is provided, show it in the iframe and link; otherwise show a simple fallback message
        if (overlayPdfIframe && book.pdf) {
            // try embedding the PDF directly
            try { overlayPdfIframe.src = book.pdf; } catch (e) { overlayPdfIframe.src = '' }
            if (overlayPdfLink) {
                overlayPdfLink.href = book.pdf;
                overlayPdfLink.textContent = 'Open PDF in new tab';
                overlayPdfLink.style.display = '';
            }
            // clear previous fallback message
            var fallbackMsgEl = overlay ? overlay.querySelector('.smartteen-overlay__pdf-fallback') : null;
            if (fallbackMsgEl) { fallbackMsgEl.style.display = 'none'; fallbackMsgEl.textContent = ''; }

            // If running on localhost/private network, don't try Google viewer (Google can't access localhost)
            var hostname = (location && location.hostname) ? location.hostname : '';
            var isLocalhost = hostname === 'localhost' || hostname === '127.0.0.1' || /^192\.168\./.test(hostname) || /^10\./.test(hostname);

            clearTimeout(overlayPdfFallbackTimer);
            if (isLocalhost) {
                // For local dev, many viewers can't embed — show link and an advisory message
                if (fallbackMsgEl) {
                    //fallbackMsgEl.textContent = 'Local file: preview may be blocked in iframe. Use "Open PDF in new tab" to view.';
                    fallbackMsgEl.style.display = '';
                }
            } else {
                // set fallback timer to detect embed failure (e.g., X-Frame-Options) and switch to Google Viewer
                overlayPdfFallbackTimer = setTimeout(function () {
                    var usable = false;
                    try {
                        // try to access iframe document to ensure content loaded and not blocked
                        var doc = overlayPdfIframe.contentDocument || (overlayPdfIframe.contentWindow && overlayPdfIframe.contentWindow.document);
                        if (doc && doc.body && doc.body.children && doc.body.children.length > 0) {
                            usable = true;
                        }
                    } catch (e) {
                        usable = false;
                    }

                    if (!usable) {
                        // fallback to Google Docs viewer
                        var googleUrl = 'https://docs.google.com/gview?embedded=true&url=' + encodeURIComponent(book.pdf);
                        try { overlayPdfIframe.src = googleUrl; } catch (e) { overlayPdfIframe.src = '' }
                        if (overlayPdfLink) {
                            overlayPdfLink.href = book.pdf;
                            overlayPdfLink.textContent = 'Open PDF in new tab';
                            overlayPdfLink.style.display = '';
                        }

                        // If Google viewer still doesn't render (we can't reliably detect cross-origin), show advisory message after a short delay
                        setTimeout(function () {
                            try {
                                var doc2 = overlayPdfIframe.contentDocument || (overlayPdfIframe.contentWindow && overlayPdfIframe.contentWindow.document);
                                if (!doc2 || !doc2.body || !(doc2.body.children && doc2.body.children.length > 0)) {
                                    if (fallbackMsgEl) {
                                        fallbackMsgEl.textContent = 'Preview not available. Use "Open PDF in new tab" to view the file.';
                                        fallbackMsgEl.style.display = '';
                                    }
                                }
                            } catch (e) {
                                if (fallbackMsgEl) {
                                    fallbackMsgEl.textContent = 'Preview not available. Use "Open PDF in new tab" to view the file.';
                                    fallbackMsgEl.style.display = '';
                                }
                            }
                        }, 900);
                    }
                }, 900);
            }

        } else {
            if (overlayPdfIframe) overlayPdfIframe.src = '';
            if (overlayPdfLink) {
                overlayPdfLink.href = '#';
                overlayPdfLink.textContent = 'No PDF available';
                overlayPdfLink.style.display = 'none';
            }
            // fallback: if overlayPageViewer exists, show intro text
            if (overlayPageViewer && book.intro) {
                overlayPageViewer.innerHTML = '<div class="smartteen-overlay__book-page-text">' + book.intro + '</div>';
            }
        }

        try { overlayClose && overlayClose.focus(); } catch (e) { }
    }

    function closeOverlay() {
        if (!overlay) return;
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('smartteen-overlay-open');
        // clear iframe src to stop loading/playing and free memory
        if (overlayPdfIframe) overlayPdfIframe.src = '';
        // clear fallback timer
        clearTimeout(overlayPdfFallbackTimer);
        overlayPdfFallbackTimer = null;
        activeBook = null;
        activePage = 0;
    }

    function setOverlayPage(index) {
        // retained for compatibility but not used when PDF preview is enabled
        if (!overlayPageViewer || !activeBook) return;
        var pages = activeBook.pages || [];
        var pageCount = pages.length || 1;
        activePage = ((index % pageCount) + pageCount) % pageCount;
        var pageData = pages[activePage] || { title: '', content: activeBook.intro || '', image: '' };
        var html = '';
        if (pageData.title) {
            html += '<h4>' + escapeHtml(pageData.title) + '</h4>';
        }
        if (pageData.content) {
            html += '<div class="smartteen-overlay__book-page-text">' + pageData.content + '</div>';
        }
        if (pageData.image) {
            html += '<img src="' + escapeHtml(pageData.image) + '" alt="">';
        }
        overlayPageViewer.innerHTML = html;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function isCardCentered(card) {
        if (!card || !viewport) return false;
        var rect = card.getBoundingClientRect();
        var center = rect.left + rect.width / 2;
        var wrapRect = viewport.getBoundingClientRect();
        var wrapCenter = wrapRect.left + wrapRect.width / 2;
        var dist = Math.abs(center - wrapCenter);
        var tolerance = Math.max(8, Math.round(wrapRect.width * 0.06)); // 6% tolerance
        return dist <= tolerance;
    }

    function attachCardEvents() {
        refreshCardList();
        cards.forEach(function (card) {
            // Skip if we've already attached handlers
            if (card.dataset.smartteenAttached === '1') {
                return;
            }

            // Skip cloned elements used for infinite looping
            if (card.getAttribute('data-clone') || card.dataset.clone) {
                return;
            }

            // Skip elements that are not visible in layout (display:none or detached)
            if (card.getClientRects().length === 0) {
                return;
            }

            function handleOpenIntent(evt, book) {
                if (isSnapping) {
                    if (evt && evt.preventDefault) evt.preventDefault();
                    return;
                }
                // If card is centered enough, open overlay
                if (isCardCentered(card)) {
                    openOverlay(book);
                    return;
                }
                // Otherwise snap to center
                var left = card.offsetLeft + card.offsetWidth / 2 - viewport.clientWidth / 2;
                isSnapping = true;
                viewport.scrollTo({ left: left, behavior: 'smooth' });
                clearTimeout(scrollEndTimer);
                scrollEndTimer = setTimeout(function () {
                    updateActiveState();
                    isSnapping = false;
                }, 420);
            }

            card.addEventListener('click', function (evt) {
                var bookData = card.getAttribute('data-book');
                if (!bookData) return;
                try {
                    var book = JSON.parse(bookData);
                } catch (e) {
                    return;
                }
                handleOpenIntent(evt, book);
            });

            // ensure the Read button follows the same rules (avoid accidental overlay opens via bubbling)
            var readBtn = card.querySelector('[data-open-overlay], .smartteen-card__read-btn');
            if (readBtn) {
                readBtn.addEventListener('click', function (evt) {
                    var bookData = card.getAttribute('data-book');
                    if (!bookData) return;
                    try { var book = JSON.parse(bookData); } catch (e) { return; }
                    // prevent bubbling to card click which may behave differently
                    evt.stopPropagation();
                    handleOpenIntent(evt, book);
                });
            }

            card.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    card.click();
                }
            });

            card.dataset.smartteenAttached = '1';
        });
    }

    function onViewportScroll() {
        if (rafId === null) {
            rafId = requestAnimationFrame(function () {
                maybeWrapScroll();
                updateActiveState();
                rafId = null;
            });
        }
        clearTimeout(scrollEndTimer);
        scrollEndTimer = setTimeout(function () {
            snapToNearest();
        }, 180);
    }

    function initializeCarousel() {
        if (originals.length >= 5) {
            cloneForInfiniteLoop();
        } else {
            removeClones();
        }
        refreshCardList();
        attachCardEvents();
        updateCardSizes();
        centerInitialCard();
        updateActiveState();
    }

    if (btnPrev) {
        btnPrev.addEventListener('click', function () {
            navigate(-1);
        });
    }
    if (btnNext) {
        btnNext.addEventListener('click', function () {
            navigate(1);
        });
    }

    if (viewport) {
        viewport.addEventListener('scroll', onViewportScroll, { passive: true });
    }

    if (overlayClose) {
        overlayClose.addEventListener('click', closeOverlay);
    }

    if (overlay) {
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                closeOverlay();
            }
        });
    }

    applySmartteenLanguage();
    window.addEventListener && window.addEventListener('revamppage:languageChanged', applySmartteenLanguage);
    document.addEventListener('keydown', function (event) {
        if (!overlay || !overlay.classList.contains('open')) return;
        if (event.key === 'Escape') {
            closeOverlay();
            return;
        }
        // If overlay is showing a PDF, ignore arrow keys for page navigation
        if (overlayPdfIframe && activeBook && activeBook.pdf) return;
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            setOverlayPage(activePage - 1);
            return;
        }
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            setOverlayPage(activePage + 1);
            return;
        }
    });

    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            initializeCarousel();
        }, 120);
    });

    initializeCarousel();
});
