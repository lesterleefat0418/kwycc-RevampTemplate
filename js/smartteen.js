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
    var overlayBookCoverContainer = overlay ? overlay.querySelector('.smartteen-overlay__book-cover') : null;
    var overlayBookTitle = overlay ? overlay.querySelector('.smartteen-overlay__book-title') : null;
    var overlayBookIntro = overlay ? overlay.querySelector('.smartteen-overlay__book-intro') : null;
    var overlayPagePrev = overlay ? overlay.querySelector('.smartteen-overlay__page-prev') : null;
    var overlayPageNext = overlay ? overlay.querySelector('.smartteen-overlay__page-next') : null;
    var overlayPageIndicator = overlay ? overlay.querySelector('.smartteen-overlay__page-indicator') : null;
    var overlayPageViewer = overlay ? overlay.querySelector('.smartteen-overlay__book-page') : null;

    var cards = originals.slice();
    var currentIndex = 0;
    var currentCard = null;
    var activeBook = null;
    var activePage = 0;
    var resizeTimer = null;
    var scrollEndTimer = null;
    var rafId = null;

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
        activeBook = book;
        activePage = 0;
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('smartteen-overlay-open');

        if (overlayBookCoverContainer) {
            if (book.cover_html) {
                overlayBookCoverContainer.innerHTML = book.cover_html;
                var newImg = overlayBookCoverContainer.querySelector('img');
                if (newImg) newImg.alt = book.title || '';
            } else {
                overlayBookCoverContainer.innerHTML = '';
            }
        }
        if (overlayBookTitle) {
            overlayBookTitle.textContent = book.title || '';
        }
        if (overlayBookIntro) {
            overlayBookIntro.innerHTML = book.intro || '';
        }
        setOverlayPage(activePage);
        try { overlayClose && overlayClose.focus(); } catch (e) { }
    }

    function closeOverlay() {
        if (!overlay) return;
        overlay.classList.remove('open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('smartteen-overlay-open');
        activeBook = null;
        activePage = 0;
    }

    function setOverlayPage(index) {
        if (!overlayPageViewer || !overlayPageIndicator || !activeBook) return;
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
        overlayPageIndicator.textContent = 'Page ' + (activePage + 1) + ' / ' + pageCount;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function attachCardEvents() {
        refreshCardList();
        cards.forEach(function (card) {
            if (card.dataset.smartteenAttached === '1') {
                return;
            }
            card.addEventListener('click', function () {
                var bookData = card.getAttribute('data-book');
                if (!bookData) return;
                try {
                    openOverlay(JSON.parse(bookData));
                } catch (e) {
                    // ignore malformed data
                }
            });
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

    if (overlayPagePrev) {
        overlayPagePrev.addEventListener('click', function () {
            if (!activeBook) return;
            setOverlayPage(activePage - 1);
        });
    }

    if (overlayPageNext) {
        overlayPageNext.addEventListener('click', function () {
            if (!activeBook) return;
            setOverlayPage(activePage + 1);
        });
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
