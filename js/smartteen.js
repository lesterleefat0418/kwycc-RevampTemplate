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
    var sectionTitle = document.querySelector('#revamppage-smartteen .section-title');
    var sectionTitleCn = sectionTitle ? sectionTitle.querySelector('.smartteen-cn') : null;
    var sectionTitleEng = sectionTitle ? sectionTitle.querySelector('.smartteen-eng') : null;
    var originalSectionTitleCn = sectionTitleCn ? sectionTitleCn.textContent : '';
    var originalSectionTitleEng = sectionTitleEng ? sectionTitleEng.textContent : '';

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
    var pdfJsLoadPromise = null;

    // PDF.js state
    var pdfDoc = null; // PDFDocumentProxy
    var pdfCurrentPage = 1;
    var pdfPageCount = 0;
    var pdfRenderingTask = null;
    var pdfRenderPage = null; // function reference for external controls
    var pdfPrevBtn = overlay ? overlay.querySelector('.smartteen-overlay__page-nav--prev') : null;
    var pdfNextBtn = overlay ? overlay.querySelector('.smartteen-overlay__page-nav--next') : null;
    var pdfIndicator = overlay ? overlay.querySelector('.smartteen-overlay__page-indicator') : null;
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

    function loadPdfJs() {
        return new Promise(function (resolve, reject) {
            if (window.pdfjsLib) {
                return resolve(window.pdfjsLib);
            }
            if (pdfJsLoadPromise) {
                return pdfJsLoadPromise.then(resolve).catch(reject);
            }
            pdfJsLoadPromise = new Promise(function (innerResolve, innerReject) {
                var script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js';
                script.onload = function () {
                    try {
                        if (window.pdfjsLib && window.pdfjsLib.GlobalWorkerOptions) {
                            window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
                        }
                    } catch (e) {
                        // ignore
                    }
                    if (window.pdfjsLib) {
                        innerResolve(window.pdfjsLib);
                    } else {
                        innerReject(new Error('pdf.js failed to initialize'));
                    }
                };
                script.onerror = function () {
                    innerReject(new Error('Failed to load pdf.js'));
                };
                document.head.appendChild(script);
            });
            pdfJsLoadPromise.then(resolve).catch(reject);
        });
    }

    function renderPdfCoverPreview(card, book) {
        if (!card || !book || !book.pdf || card.dataset.pdfCoverRendered === '1') {
            return;
        }
        if (card.dataset.pdfCoverRendered === 'pending') {
            return;
        }
        card.dataset.pdfCoverRendered = 'pending';
        var coverEl = card.querySelector('.smartteen-card__cover');
        if (!coverEl) {
            card.dataset.pdfCoverRendered = '0';
            return;
        }

        loadPdfJs().then(function (pdfjsLib) {
            return pdfjsLib.getDocument(book.pdf).promise;
        }).then(function (pdf) {
            return pdf.getPage(1);
        }).then(function (page) {
            var wrapWidth = Math.max(coverEl.clientWidth, coverEl.offsetWidth, 220);
            var viewport = page.getViewport({ scale: 1 });
            var scale = wrapWidth / viewport.width;
            var pageViewport = page.getViewport({ scale: scale });
            var canvas = document.createElement('canvas');
            canvas.className = 'smartteen-pdf-card-preview';
            canvas.width = Math.floor(pageViewport.width);
            canvas.height = Math.floor(pageViewport.height);
            canvas.style.display = 'block';
            canvas.style.width = '100%';
            var ctx = canvas.getContext('2d');
            return page.render({ canvasContext: ctx, viewport: pageViewport }).promise.then(function () {
                return canvas;
            });
        }).then(function (canvas) {
            if (!coverEl) {
                return;
            }
            coverEl.innerHTML = '';
            coverEl.appendChild(canvas);
            card.dataset.pdfCoverRendered = '1';
        }).catch(function () {
            card.dataset.pdfCoverRendered = '0';
        });
    }

    function renderPdfCoverPreviews() {
        refreshCardList();
        cards.forEach(function (card) {
            var bookData = card.getAttribute('data-book');
            if (!bookData) {
                return;
            }
            try {
                var book = JSON.parse(bookData);
            } catch (e) {
                return;
            }
            renderPdfCoverPreview(card, book);
        });
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
        if (!overlay) {
            console.warn('openOverlay: no overlay element');
            return;
        }
        // Safety guard: ensure the current centered/active card corresponds to the requested book.
        // This prevents opening the overlay for non-active books if some caller invokes openOverlay directly.
        try {
            var activeCard = track.querySelector('.smartteen-card.active');
            var requestedId = (book && typeof book.id !== 'undefined') ? String(book.id) : null;
            if (activeCard && requestedId) {
                var activeBookData = activeCard.getAttribute('data-book');
                if (activeBookData) {
                    var parsed = JSON.parse(activeBookData);
                    var activeId = parsed && typeof parsed.id !== 'undefined' ? String(parsed.id) : null;
                    if (activeId && requestedId && activeId !== requestedId) {
                        console.warn('openOverlay refused: active card id != requested id', { activeId: activeId, requestedId: requestedId });
                        return;
                    }
                }
            } else if (!activeCard && requestedId) {
                // no active card found - ensure the closest card matches the requested book
                var closest = findClosestCard();
                if (closest) {
                    var closestData = closest.getAttribute('data-book');
                    if (closestData) {
                        var parsedClosest = JSON.parse(closestData);
                        var closestId = parsedClosest && typeof parsedClosest.id !== 'undefined' ? String(parsedClosest.id) : null;
                        if (closestId && requestedId && closestId !== requestedId) {
                            console.warn('openOverlay refused: closest card id != requested id', { closestId: closestId, requestedId: requestedId });
                            return;
                        }
                    }
                }
            }
        } catch (e) {
            // parsing error - fail closed but log for debugging
            console.error('openOverlay: JSON parse error or other exception', e);
            return;
        }

        activeBook = book;
        activePage = 0;
        if (sectionTitleCn) {
            sectionTitleCn.textContent = book.title || originalSectionTitleCn;
        }
        if (sectionTitleEng) {
            sectionTitleEng.textContent = book.title || originalSectionTitleEng;
        }
        overlay.classList.add('open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('smartteen-overlay-open');

        // If a PDF url is provided, render it via PDF.js (preferred) into overlayPageViewer; otherwise show a simple fallback message
        if (book.pdf) {
            if (overlayPdfLink) {
                overlayPdfLink.href = book.pdf;
                overlayPdfLink.textContent = 'Open PDF in new tab';
                overlayPdfLink.style.display = '';
            }

            var fallbackMsgEl = overlay ? overlay.querySelector('.smartteen-overlay__pdf-fallback') : null;
            if (fallbackMsgEl) { fallbackMsgEl.style.display = 'none'; fallbackMsgEl.textContent = ''; }

            // render using PDF.js
            if (overlayPageViewer) {
                // clear previous viewer
                overlayPageViewer.innerHTML = '';

                function renderPdfUrl(url) {
                    loadPdfJs().then(function (pdfjsLib) {
                        // fetch PDF document
                        var loadingTask;
                        try {
                            loadingTask = pdfjsLib.getDocument(url);
                        } catch (e) {
                            if (fallbackMsgEl) { fallbackMsgEl.textContent = 'Preview not available (failed to start PDF load). Use "Open PDF in new tab".'; fallbackMsgEl.style.display = ''; }
                            return;
                        }

                        loadingTask.promise.then(function (pdf) {
                            // store document and initialize pagination
                            pdfDoc = pdf;
                            pdfPageCount = pdf.numPages || 0;
                            pdfCurrentPage = 1;

                            // helpers
                            function updatePdfControls() {
                                var currentSpread = Math.ceil(pdfCurrentPage / 2);
                                var totalSpreads = Math.ceil(pdfPageCount / 2);
                                if (pdfIndicator) pdfIndicator.textContent = currentSpread + ' / ' + totalSpreads;
                                if (pdfPrevBtn) pdfPrevBtn.disabled = pdfCurrentPage <= 1;
                                if (pdfNextBtn) pdfNextBtn.disabled = pdfCurrentPage >= (pdfPageCount || 1);
                            }

                            function renderSpread(startPage) {
                                if (!pdfDoc) return;
                                // normalize to integer
                                startPage = Math.floor(Number(startPage) || 1);
                                // clamp to valid range
                                if (startPage < 1) startPage = 1;
                                if (startPage > pdfPageCount) startPage = Math.max(1, pdfPageCount - ((pdfPageCount % 2 === 0) ? 1 : 0));
                                // Maintain left page as startPage; right page is startPage+1
                                pdfCurrentPage = startPage;
                                var leftPageNum = startPage;
                                var rightPageNum = startPage + 1;

                                // prepare layout: two columns
                                overlayPageViewer.innerHTML = '';
                                var leftWrap = document.createElement('div');
                                leftWrap.className = 'pdf-page pdf-page--left';
                                leftWrap.style.boxSizing = 'border-box';
                                leftWrap.style.overflow = 'auto';
                                leftWrap.style.webkitOverflowScrolling = 'touch';

                                var rightWrap = document.createElement('div');
                                rightWrap.className = 'pdf-page pdf-page--right';
                                rightWrap.style.boxSizing = 'border-box';
                                rightWrap.style.overflow = 'auto';
                                rightWrap.style.webkitOverflowScrolling = 'touch';

                                overlayPageViewer.appendChild(leftWrap);
                                overlayPageViewer.appendChild(rightWrap);

                                function renderOne(pageNum, wrapEl) {
                                    if (!pageNum || pageNum < 1 || pageNum > pdfPageCount) {
                                        // empty page placeholder
                                        wrapEl.innerHTML = '<div style="color:#ddd; text-align:center; padding:2rem;">&nbsp;</div>';
                                        return Promise.resolve();
                                    }
                                    return pdfDoc.getPage(pageNum).then(function (page) {
                                        var wrapWidth = Math.floor(Math.max(0, wrapEl.clientWidth - 12)) || Math.floor(overlayPageViewer.clientWidth / 2) || 400; // subtract small gap when present
                                        var viewportForScale = page.getViewport({ scale: 1 });
                                        var scale = (wrapWidth / viewportForScale.width) * 1.0;
                                        var viewport = page.getViewport({ scale: scale });

                                        var canvas = document.createElement('canvas');
                                        canvas.className = 'smartteen-pdf-canvas';
                                        var ctx = canvas.getContext('2d');
                                        canvas.width = Math.floor(viewport.width);
                                        canvas.height = Math.floor(viewport.height);
                                        canvas.style.display = 'block';
                                        canvas.style.width = '100%';
                                        canvas.style.height = 'auto';

                                        wrapEl.innerHTML = '';
                                        wrapEl.appendChild(canvas);

                                        var renderContext = { canvasContext: ctx, viewport: viewport };
                                        pdfRenderingTask = page.render(renderContext);
                                        return pdfRenderingTask.promise;
                                    });
                                }

                                // render both pages in parallel
                                var leftPromise = renderOne(leftPageNum, leftWrap);
                                var rightPromise = renderOne(rightPageNum, rightWrap);

                                return Promise.all([leftPromise, rightPromise]).then(function () {
                                    // update controls
                                    if (pdfIndicator) {
                                        var currentSpread = Math.ceil(pdfCurrentPage / 2);
                                        var totalSpreads = Math.ceil(pdfPageCount / 2);
                                        pdfIndicator.textContent = currentSpread + ' / ' + totalSpreads;
                                    }
                                    if (pdfPrevBtn) pdfPrevBtn.disabled = leftPageNum <= 1;
                                    if (pdfNextBtn) pdfNextBtn.disabled = rightPageNum >= pdfPageCount;
                                }).catch(function (err) {
                                    if (fallbackMsgEl) { fallbackMsgEl.textContent = 'Preview not available (render failed). Use "Open PDF in new tab".'; fallbackMsgEl.style.display = ''; }
                                });
                            }

                            // attach prev/next handlers once (advance by 2 pages for a spread)
                            if (pdfPrevBtn && !pdfPrevBtn.dataset.revAttached) {
                                pdfPrevBtn.addEventListener('click', function () { renderSpread(pdfCurrentPage - 2); });
                                pdfPrevBtn.dataset.revAttached = '1';
                            }
                            if (pdfNextBtn && !pdfNextBtn.dataset.revAttached) {
                                pdfNextBtn.addEventListener('click', function () { renderSpread(pdfCurrentPage + 2); });
                                pdfNextBtn.dataset.revAttached = '1';
                            }

                            // expose renderSpread to outer scope so keyboard handlers can use it
                            pdfRenderPage = renderSpread;

                            // initial render as a spread (left=1, right=2)
                            renderSpread(1);

                        }).catch(function (err) {
                            if (fallbackMsgEl) { fallbackMsgEl.textContent = 'Preview not available. Use "Open PDF in new tab" to view the file.'; fallbackMsgEl.style.display = ''; }
                        });
                    });
                }

                renderPdfUrl(book.pdf);

            } else if (overlayPdfIframe) {
                // fallback to iframe behavior when overlayPageViewer is not present
                try { overlayPdfIframe.src = book.pdf; } catch (e) { overlayPdfIframe.src = '' }
                if (fallbackMsgEl) { fallbackMsgEl.textContent = 'If preview may be blocked in iframe. Use "Open PDF in new tab" to view.'; fallbackMsgEl.style.display = ''; }
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
        // clear PDF.js viewer content and state
        if (overlayPageViewer) {
            overlayPageViewer.innerHTML = '';
        }
        if (pdfRenderingTask && pdfRenderingTask.cancel) {
            try { pdfRenderingTask.cancel(); } catch (e) { }
        }
        pdfRenderingTask = null;
        pdfDoc = null;
        pdfRenderPage = null;
        pdfPageCount = 0;
        pdfCurrentPage = 1;
        // clear fallback timer
        clearTimeout(overlayPdfFallbackTimer);
        overlayPdfFallbackTimer = null;
        if (sectionTitleCn) {
            sectionTitleCn.textContent = originalSectionTitleCn;
        }
        if (sectionTitleEng) {
            sectionTitleEng.textContent = originalSectionTitleEng;
        }
        activeBook = null;
        activePage = 0;
        // restore active carousel state after the carousel becomes visible again
        window.requestAnimationFrame ? window.requestAnimationFrame(updateActiveState) : setTimeout(updateActiveState, 0);
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
            function handleOpenIntent(evt, book) {
                if (isSnapping) {
                    if (evt && evt.preventDefault) evt.preventDefault();
                    return;
                }
                cards.forEach(function (c) {
                    c.classList.toggle('active', c === card);
                });
                openOverlay(book);
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
        renderPdfCoverPreviews();
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

        // If a PDF is rendered via PDF.js, use arrow keys to navigate pages
        if (pdfDoc && pdfRenderPage) {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                // step backward by a two-page spread
                pdfRenderPage(pdfCurrentPage - 2);
                return;
            }
            if (event.key === 'ArrowRight') {
                event.preventDefault();
                // step forward by a two-page spread
                pdfRenderPage(pdfCurrentPage + 2);
                return;
            }
            return;
        }

        // fallback behavior for non-PDF overlays: use existing page logic
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
