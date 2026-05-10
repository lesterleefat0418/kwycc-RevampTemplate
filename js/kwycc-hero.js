// kwycc-hero.js — Updated: 用箭頭按鈕控制 snap
document.addEventListener('DOMContentLoaded', function () {
    var wrap = document.querySelector('.kwycc-scroll-wrap');
    var scroll = document.querySelector('.kwycc-scroll');

    if (!wrap || !scroll) return;

    if (scroll.dataset.kwyccInit === '1') return;
    scroll.dataset.kwyccInit = '1';

    function removeClones() {
        Array.from(scroll.querySelectorAll('.kwycc-card[data-clone], .kwycc-card.clone, .kwycc-card.__clone')).forEach(function (n) {
            if (n && n.parentNode) n.parentNode.removeChild(n);
        });
    }
    removeClones();

    var originals = Array.prototype.slice.call(scroll.querySelectorAll('.kwycc-card:not([data-clone])'));
    var N = originals.length;
    if (N === 0) return;

    var enableInfinite = N >= 5;
    var enableSnap = true;
    var allCards = [];
    var middleStart = 0;
    var middleOriginals = [];

    if (enableInfinite) {
        if (scroll.querySelectorAll('.kwycc-card[data-clone]').length === 0) {
            var fragPre = document.createDocumentFragment();
            var fragPost = document.createDocumentFragment();
            originals.forEach(function (orig) {
                var cpre = orig.cloneNode(true);
                cpre.dataset.clone = 'pre';
                if (orig.dataset.index) cpre.dataset.index = orig.dataset.index;
                fragPre.appendChild(cpre);

                var cpost = orig.cloneNode(true);
                cpost.dataset.clone = 'post';
                if (orig.dataset.index) cpost.dataset.index = orig.dataset.index;
                fragPost.appendChild(cpost);
            });
            scroll.insertBefore(fragPre, scroll.firstChild);
            scroll.appendChild(fragPost);
        }
    } else {
        removeClones();
    }

    function refreshCardLists() {
        allCards = Array.prototype.slice.call(scroll.querySelectorAll('.kwycc-card'));
        middleStart = enableInfinite ? originals.length : 0;
        middleOriginals = enableInfinite ? allCards.slice(middleStart, middleStart + originals.length) : originals;
    }
    refreshCardLists();

    function calculateSpacing() {
        if (!middleOriginals[0]) return { cardWidth: 0, gap: 0, cardSpacing: 0 };
        var cardWidth = middleOriginals[0].offsetWidth;
        var gapStr = window.getComputedStyle(scroll).gap;
        var gap = gapStr !== 'normal' ? parseFloat(gapStr) : 0;
        var cardSpacing = cardWidth + gap;
        return { cardWidth: cardWidth, gap: gap, cardSpacing: cardSpacing };
    }

    function findClosest(cards) {
        var wrapRect = wrap.getBoundingClientRect();
        var wrapCenter = wrapRect.left + wrapRect.width / 2;
        var closest = null;
        var minDist = Infinity;
        cards.forEach(function (card) {
            var r = card.getBoundingClientRect();
            var cardCenter = r.left + r.width / 2;
            var dist = Math.abs(cardCenter - wrapCenter);
            if (dist < minDist) {
                minDist = dist;
                closest = card;
            }
        });
        return closest;
    }

    function updateActiveImmediate() {
        refreshCardLists();
        var cardsLive = Array.prototype.slice.call(scroll.querySelectorAll('.kwycc-card'));
        if (cardsLive.length === 0) return;
        var closest = findClosest(cardsLive);
        if (!closest) return;
        cardsLive.forEach(function (c) {
            c.classList.toggle('active', c === closest);
        });
        scroll.classList.add('has-active');
    }

    function centerFirstCard() {
        refreshCardLists();
        if (!middleOriginals[0]) return;
        var card = middleOriginals[0];
        var target = card.offsetLeft + (card.offsetWidth / 2) - (wrap.clientWidth / 2);
        wrap.scrollLeft = target;
    }

    function snapToClosest() {
        refreshCardLists();
        var cardsLive = Array.prototype.slice.call(scroll.querySelectorAll('.kwycc-card'));
        var closest = findClosest(cardsLive);
        if (!closest) return;
        var target = closest.offsetLeft + (closest.offsetWidth / 2) - (wrap.clientWidth / 2);
        wrap.scrollTo({ left: target, behavior: 'smooth' });
        setTimeout(updateActiveImmediate, 300);
    }

    // ✅ 箭頭按鈕控制
    function snapNext() {
        refreshCardLists();
        var cardsLive = Array.prototype.slice.call(scroll.querySelectorAll('.kwycc-card'));
        var closest = findClosest(cardsLive);
        if (!closest) return;

        var currentIndex = cardsLive.indexOf(closest);
        var nextIndex = currentIndex + 1;
        if (nextIndex >= cardsLive.length) nextIndex = 0; // 無限循環

        var nextCard = cardsLive[nextIndex];
        var target = nextCard.offsetLeft + (nextCard.offsetWidth / 2) - (wrap.clientWidth / 2);
        wrap.scrollTo({ left: target, behavior: 'smooth' });
        setTimeout(updateActiveImmediate, 300);
    }

    function snapPrev() {
        refreshCardLists();
        var cardsLive = Array.prototype.slice.call(scroll.querySelectorAll('.kwycc-card'));
        var closest = findClosest(cardsLive);
        if (!closest) return;

        var currentIndex = cardsLive.indexOf(closest);
        var prevIndex = currentIndex - 1;
        if (prevIndex < 0) prevIndex = cardsLive.length - 1; // 無限循環

        var prevCard = cardsLive[prevIndex];
        var target = prevCard.offsetLeft + (prevCard.offsetWidth / 2) - (wrap.clientWidth / 2);
        wrap.scrollTo({ left: target, behavior: 'smooth' });
        setTimeout(updateActiveImmediate, 300);
    }

    // ✅ 綁定箭頭按鈕事件
    var btnLeft = document.querySelector('.kwycc-scroll-nav-left');
    var btnRight = document.querySelector('.kwycc-scroll-nav-right');

    if (btnLeft) btnLeft.addEventListener('click', snapPrev);
    if (btnRight) btnRight.addEventListener('click', snapNext);

    if (btnLeft && btnLeft.disabled) {
        btnLeft.style.pointerEvents = 'none';
        btnLeft.style.opacity = '0.5';
    }

    if (btnRight && btnRight.disabled) {
        btnRight.style.pointerEvents = 'none';
        btnRight.style.opacity = '0.5';
    }

    function handleInfiniteLoop() {
        if (!enableInfinite) return;
        refreshCardLists();
        var spacing = calculateSpacing();
        if (spacing.cardSpacing === 0) return;
        var totalOriginalWidth = N * spacing.cardSpacing;

        if (wrap.scrollLeft <= spacing.cardSpacing * 0.5) {
            wrap.scrollLeft += totalOriginalWidth;
            return;
        }

        var maxScroll = scroll.scrollWidth - wrap.clientWidth;
        if (wrap.scrollLeft >= maxScroll - spacing.cardSpacing * 0.5) {
            wrap.scrollLeft -= totalOriginalWidth;
            return;
        }
    }

    var rafId = null;
    var scrollEndTimer = null;
    function onScroll() {
        if (rafId === null) {
            rafId = requestAnimationFrame(function () {
                handleInfiniteLoop();
                updateActiveImmediate();
                rafId = null;
            });
        }
        clearTimeout(scrollEndTimer);
        scrollEndTimer = setTimeout(function () {
            snapToClosest();
        }, 150);
    }
    wrap.addEventListener('scroll', onScroll, { passive: true });

    // ✅ 直接允許 click 導航（不需要判斷拖動）
    var links = scroll.querySelectorAll('.card-link');
    links.forEach(function (link) {
        link.addEventListener('dragstart', function (e) {
            e.preventDefault();
        });
    });

    setTimeout(function () {
        centerFirstCard();
        updateActiveImmediate();
    }, 60);

    window.addEventListener('load', function () {
        centerFirstCard();
        updateActiveImmediate();
    });

    var resizeTimer = null;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            centerFirstCard();
            updateActiveImmediate();
        }, 120);
    });

    window.kwyccDebug = function () {
        console.log('N', N,
            'total', document.querySelectorAll('.kwycc-card').length,
            'clones', document.querySelectorAll('.kwycc-card[data-clone]').length,
            'scrollLeft', wrap.scrollLeft);
    };

    if (enableSnap) {
        wrap.style.scrollSnapType = 'x mandatory';
    } else {
        wrap.style.scrollSnapType = 'none';
    }
});