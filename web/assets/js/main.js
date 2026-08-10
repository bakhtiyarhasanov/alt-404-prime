/**
 * alt404 Prime — Minimal JavaScript
 * Handles: mobile menu toggle, search dropdown, video modal, copy link
 */
document.addEventListener('DOMContentLoaded', function () {

  // ── Mobile menu toggle ─────────────────────────────────────────────────
  const menuBtn = document.getElementById('mobile-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  if (menuBtn && mobileMenu) {
    menuBtn.addEventListener('click', function () {
      const isOpen = mobileMenu.style.display === 'flex';
      mobileMenu.style.display = isOpen ? 'none' : 'flex';
      menuBtn.setAttribute('aria-expanded', !isOpen);
      // Toggle icon
      const openIcon = menuBtn.querySelector('.icon-menu');
      const closeIcon = menuBtn.querySelector('.icon-close');
      if (openIcon) openIcon.style.display = isOpen ? 'block' : 'none';
      if (closeIcon) closeIcon.style.display = isOpen ? 'none' : 'block';
    });
  }

  // ── Header scroll behavior ─────────────────────────────────────────────
  const header = document.querySelector('.site-header');
  const nav = header ? header.querySelector('nav') : null;
  const isHeroPage = document.body.classList.contains('hero-page');

  function updateHeaderState() {
    if (!nav) return;
    const scrolled = window.scrollY > 60;
    if (isHeroPage && !scrolled) {
      nav.classList.remove('capsule-nav');
      nav.classList.add('capsule-nav-hero');
      nav.classList.remove('shadow-capsule');
    } else {
      nav.classList.remove('capsule-nav-hero');
      nav.classList.add('capsule-nav');
      if (scrolled) nav.classList.add('shadow-capsule');
      else nav.classList.remove('shadow-capsule');
    }
    // Update nav link styles
    document.querySelectorAll('[data-nav-variant]').forEach(function (el) {
      if (isHeroPage && !scrolled) {
        el.classList.add(el.dataset.navVariant + '--dark');
        el.classList.remove(el.dataset.navVariant + '--light');
      } else {
        el.classList.add(el.dataset.navVariant + '--light');
        el.classList.remove(el.dataset.navVariant + '--dark');
      }
    });
    // Logo swap
    const logoImg = document.getElementById('header-logo-img');
    if (logoImg) {
      logoImg.src = (isHeroPage && !scrolled) ? '/assets/img/logo.png' : '/assets/img/logo-dark.png';
    }
  }
  window.addEventListener('scroll', updateHeaderState, { passive: true });
  updateHeaderState();

  // ── Search dropdown ────────────────────────────────────────────────────
  const searchBtn = document.getElementById('search-toggle');
  const searchDropdown = document.getElementById('search-dropdown');
  const searchInput = document.getElementById('search-input');
  const searchResults = document.getElementById('search-results');
  const headerInner = document.querySelector('.header-inner');
  const searchClose = document.getElementById('search-close');

  function openSearch() {
    if (headerInner) headerInner.classList.add('search-active');
    if (searchDropdown) searchDropdown.style.display = 'block';
    if (searchInput) {
      searchInput.value = '';
      searchInput.focus();
    }
    if (searchResults) searchResults.innerHTML = '';
  }

  function closeSearch() {
    if (headerInner) headerInner.classList.remove('search-active');
    if (searchDropdown) searchDropdown.style.display = 'none';
    if (searchResults) searchResults.innerHTML = '';
  }

  if (searchBtn && searchDropdown) {
    searchBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      openSearch();
    });

    if (searchClose) {
      searchClose.addEventListener('click', function (e) {
        e.stopPropagation();
        closeSearch();
      });
    }

    document.addEventListener('click', function (e) {
      if (!searchDropdown.contains(e.target) && e.target !== searchBtn) {
        closeSearch();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeSearch();
      }
    });

    // Live search suggestions
    if (searchInput && searchResults && typeof window.__articles !== 'undefined') {
      searchInput.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        if (q.length < 2) { searchResults.innerHTML = ''; return; }
        const matches = window.__articles.filter(function (a) {
          return a.title.toLowerCase().includes(q);
        }).slice(0, 5);
        searchResults.innerHTML = matches.map(function (a) {
          return '<a href="/' + a.category + '/' + a.slug + '" class="search-suggestion">'
            + '<div class="search-suggestion-img">'
            + (a.image_url ? '<img src="' + a.image_url + '" alt="">' : '<div style="width:100%;height:100%;background:var(--color-surface-2)"></div>')
            + '</div>'
            + '<span class="search-suggestion-title">' + a.title + '</span>'
            + '</a>';
        }).join('');
      });
    }
  }

  // ── Video Modal ────────────────────────────────────────────────────────
  const videoModal = document.getElementById('video-modal');
  const videoIframe = document.getElementById('video-iframe');
  const videoTitle = document.getElementById('video-modal-title');

  document.querySelectorAll('[data-video-url]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const url = this.dataset.videoUrl;
      const title = this.dataset.videoTitle || '';
      const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
      if (match && videoModal && videoIframe) {
        videoIframe.src = 'https://www.youtube.com/embed/' + match[1] + '?autoplay=1';
        if (videoTitle) videoTitle.textContent = title;
        videoModal.style.display = 'flex';
      }
    });
  });

  if (videoModal) {
    videoModal.addEventListener('click', function (e) {
      if (e.target === videoModal || e.target.closest('.video-modal-close')) {
        videoModal.style.display = 'none';
        if (videoIframe) videoIframe.src = '';
      }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && videoModal.style.display === 'flex') {
        videoModal.style.display = 'none';
        if (videoIframe) videoIframe.src = '';
      }
    });
  }

  // ── Copy Link ──────────────────────────────────────────────────────────
  const copyBtn = document.getElementById('copy-link-btn');
  if (copyBtn) {
    copyBtn.addEventListener('click', function () {
      navigator.clipboard.writeText(window.location.href).then(function () {
        copyBtn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Kopyalandı!';
        copyBtn.classList.add('copy-success');
        setTimeout(function () {
          copyBtn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Linki Kopyala';
          copyBtn.classList.remove('copy-success');
        }, 2000);
      });
    });
  }

  // ── Gallery Slider ─────────────────────────────────────────────────────
  document.querySelectorAll('.gallery-slider').forEach(function (slider) {
    let active = 0;
    const images = slider.querySelectorAll('.gallery-slide');
    const counter = slider.querySelector('.gallery-counter');
    const thumbs = slider.querySelectorAll('.gallery-thumb');

    function show(idx) {
      images.forEach(function (img, i) { img.style.display = i === idx ? 'block' : 'none'; });
      thumbs.forEach(function (t, i) { t.classList.toggle('active', i === idx); });
      if (counter) counter.textContent = (idx + 1) + ' / ' + images.length;
      active = idx;
    }

    var prev = slider.querySelector('.gallery-arrow--left');
    var next = slider.querySelector('.gallery-arrow--right');
    if (prev) prev.addEventListener('click', function () { show((active - 1 + images.length) % images.length); });
    if (next) next.addEventListener('click', function () { show((active + 1) % images.length); });
    thumbs.forEach(function (t, i) { t.addEventListener('click', function () { show(i); }); });
    show(0);
  });

});
