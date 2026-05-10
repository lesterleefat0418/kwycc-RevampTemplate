document.addEventListener('DOMContentLoaded', function(){
  const scroll = document.getElementById('kwycc-scroll');
  if (!scroll) return;

  // 只保留最多 5 個卡片（若 PHP 已處理，這是保險）
  const cards = Array.from(scroll.querySelectorAll('.kwycc-card')).slice(0,5);
  scroll.innerHTML = '';
  cards.forEach(c => scroll.appendChild(c));

  // 若卡片數量少於 5，置中整列
  if (cards.length < 5) {
    scroll.classList.add('centered');
  } else {
    scroll.classList.remove('centered');
  }

  // 設定 active index（中間那張）
  let activeIndex = Math.floor((cards.length - 1) / 2); // 0-based
  function updateActive(index) {
    cards.forEach((c,i) => {
      c.classList.toggle('active', i === index);
    });
    // 置中 active 卡片：計算 transform
    const wrap = scroll.parentElement;
    const wrapWidth = wrap.clientWidth;
    const activeCard = cards[index];
    const cardRect = activeCard.getBoundingClientRect();
    const scrollRect = scroll.getBoundingClientRect();
    // 計算 scroll 的偏移量（相對於 scroll 的左上）
    const offsetLeft = activeCard.offsetLeft + (activeCard.offsetWidth / 2);
    const translateX = (wrapWidth / 2) - offsetLeft;
    scroll.style.transform = `translateX(${translateX}px)`;
  }

  // 初始
  updateActive(activeIndex);

  // 自動輪播（可選）
  let autoTimer = null;
  function startAuto() {
    if (cards.length <= 1) return;
    autoTimer = setInterval(function(){
      activeIndex = (activeIndex + 1) % cards.length;
      updateActive(activeIndex);
    }, 3500);
  }
  function stopAuto() { if (autoTimer) { clearInterval(autoTimer); autoTimer = null; } }
  startAuto();

  // 懸停暫停
  scroll.parentElement.addEventListener('mouseenter', stopAuto);
  scroll.parentElement.addEventListener('mouseleave', startAuto);

  // 拖曳/觸控手動移動（簡單實作：拖曳改變 translateX）
  let isDown = false, startX = 0, startTranslate = 0;
  scroll.parentElement.addEventListener('mousedown', (e)=>{
    isDown = true; startX = e.clientX; startTranslate = getCurrentTranslateX();
    stopAuto();
  });
  window.addEventListener('mousemove', (e)=>{
    if (!isDown) return;
    const dx = e.clientX - startX;
    scroll.style.transform = `translateX(${startTranslate + dx}px)`;
  });
  window.addEventListener('mouseup', ()=>{
    if (!isDown) return;
    isDown = false;
    // 拖曳結束後，找最接近中間的卡片並設為 active
    const wrap = scroll.parentElement;
    const wrapCenter = wrap.clientWidth / 2;
    let closestIndex = 0, closestDist = Infinity;
    cards.forEach((c,i)=>{
      const center = c.offsetLeft + c.offsetWidth / 2 + getCurrentTranslateX();
      const dist = Math.abs(center - wrapCenter);
      if (dist < closestDist) { closestDist = dist; closestIndex = i; }
    });
    activeIndex = closestIndex;
    updateActive(activeIndex);
    startAuto();
  });

  // 觸控
  scroll.parentElement.addEventListener('touchstart', (e)=>{
    isDown = true; startX = e.touches[0].clientX; startTranslate = getCurrentTranslateX(); stopAuto();
  }, {passive:true});
  scroll.parentElement.addEventListener('touchmove', (e)=>{
    if (!isDown) return;
    const dx = e.touches[0].clientX - startX;
    scroll.style.transform = `translateX(${startTranslate + dx}px)`;
  }, {passive:true});
  scroll.parentElement.addEventListener('touchend', ()=>{
    if (!isDown) return;
    isDown = false;
    // snap to nearest
    const wrap = scroll.parentElement;
    const wrapCenter = wrap.clientWidth / 2;
    let closestIndex = 0, closestDist = Infinity;
    cards.forEach((c,i)=>{
      const center = c.offsetLeft + c.offsetWidth / 2 + getCurrentTranslateX();
      const dist = Math.abs(center - wrapCenter);
      if (dist < closestDist) { closestDist = dist; closestIndex = i; }
    });
    activeIndex = closestIndex;
    updateActive(activeIndex);
    startAuto();
  }, {passive:true});

  function getCurrentTranslateX() {
    const style = window.getComputedStyle(scroll);
    const matrix = new WebKitCSSMatrix(style.transform);
    return matrix.m41 || 0;
  }

  // 語言切換按鈕行為（簡單示例）
  const langBtn = document.getElementById('kwycc-lang-toggle');
  if (langBtn) {
    langBtn.addEventListener('click', function(){
      const expanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', String(!expanded));
      // 這裡可放語言切換邏輯：例如切換 cookie、重新載入或呼叫 WP REST API
      alert('切換語言（示範）');
    });
  }

  // 選單按鈕（預設收起）
  const menuBtn = document.getElementById('kwycc-menu-toggle');
  if (menuBtn) {
    menuBtn.addEventListener('click', function(){
      const expanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', String(!expanded));
      // 若你有主選單 DOM，可在這裡 toggle 顯示
      const nav = document.getElementById('kwycc-main-nav');
      if (nav) nav.style.display = expanded ? 'none' : 'block';
    });
  }
});
