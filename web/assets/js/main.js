/**
 * alt404 Prime — Main Frontend Engine
 * Exact ID bindings matching header.php & footer.php:
 * - Search Overlay Modal (#open-search-btn, #search-overlay-modal, #search-input-field, #search-modal-results)
 * - Bookmarks Drawer (#open-bookmarks-btn, #bookmarks-drawer-backdrop, #bookmarks-list-container)
 * - Video Modal Player ([data-video-url], #video-player-modal, #video-player-iframe)
 * - Theme Switcher (#theme-toggle-btn)
 * - Tag Carousel Scroll (#scroll-tags-left-btn, #scroll-tags-right-btn, #tag-scroll-container)
 * - Mobile Navigation Drawer (#mobile-menu-btn, #mobile-nav-drawer)
 * - Toast Notifications (#app-toast-notification, #app-toast-text)
 * - Scroll to Top (#scroll-to-top-btn)
 */

(function () {
  'use strict';

  // ═══════════════════════════════════════════════════════════════════════════
  // 1. TOAST NOTIFICATION SYSTEM
  // ═══════════════════════════════════════════════════════════════════════════
  let toastTimer = null;
  window.showToast = function (message, duration = 3000) {
    const toast = document.getElementById('app-toast-notification');
    const msgEl = document.getElementById('app-toast-text') || document.getElementById('app-toast-message');
    if (!toast) return;

    if (msgEl) msgEl.textContent = message;
    toast.style.display = 'flex';
    toast.style.opacity = '1';

    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(function () {
      toast.style.opacity = '0';
      setTimeout(function () {
        toast.style.display = 'none';
      }, 200);
    }, duration);
  };

  // ═══════════════════════════════════════════════════════════════════════════
  // 2. THEME SWITCHER
  // ═══════════════════════════════════════════════════════════════════════════
  function initTheme() {
    const themeToggleBtn = document.getElementById('theme-toggle-btn');
    if (!themeToggleBtn) return;

    themeToggleBtn.addEventListener('click', function (e) {
      e.preventDefault();
      const isDark = document.documentElement.classList.toggle('dark');
      try {
        localStorage.setItem('alt404_theme', isDark ? 'dark' : 'light');
      } catch (err) {}
      window.showToast(isDark ? 'Qaranlıq rejim aktivləşdirildi' : 'İşıqlı rejim aktivləşdirildi', 2000);
    });
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // 3. SEARCH OVERLAY MODAL
  // ═══════════════════════════════════════════════════════════════════════════
  function initSearchModal() {
    const searchModal = document.getElementById('search-overlay-modal');
    const openBtn = document.getElementById('open-search-btn');
    const closeBtn = document.getElementById('search-modal-close-btn');
    const clearBtn = document.getElementById('search-clear-btn');
    const searchInput = document.getElementById('search-input-field');
    const resultsContainer = document.getElementById('search-modal-results');
    const categoryTabs = document.querySelectorAll('.search-cat-tab');
    const countEl = document.getElementById('search-modal-count');

    let activeCategory = 'ALL';
    let debounceTimer = null;
    let activeAbortController = null;

    function openSearch() {
      if (!searchModal) return;
      searchModal.style.display = 'flex';
      document.body.style.overflow = 'hidden';

      if (searchInput) {
        searchInput.value = '';
        setTimeout(() => searchInput.focus(), 50);
      }
      if (clearBtn) clearBtn.style.display = 'none';
      renderInitialList();
    }

    function closeSearch() {
      if (!searchModal) return;
      if (activeAbortController) {
        activeAbortController.abort();
        activeAbortController = null;
      }
      if (debounceTimer) clearTimeout(debounceTimer);
      searchModal.style.display = 'none';
      document.body.style.overflow = '';
      if (searchInput) searchInput.value = '';
    }

    if (openBtn) {
      openBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openSearch();
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        closeSearch();
      });
    }

    if (searchModal) {
      searchModal.addEventListener('click', function (e) {
        if (e.target === searchModal) {
          closeSearch();
        }
      });
    }

    // Keyboard shortcut Cmd+K or Ctrl+K & Escape
    document.addEventListener('keydown', function (e) {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        if (searchModal && searchModal.style.display !== 'none' && searchModal.style.display !== '') {
          closeSearch();
        } else {
          openSearch();
        }
      } else if (e.key === 'Escape' && searchModal && searchModal.style.display !== 'none') {
        closeSearch();
      }
    });

    if (clearBtn && searchInput) {
      clearBtn.addEventListener('click', function () {
        searchInput.value = '';
        clearBtn.style.display = 'none';
        if (debounceTimer) clearTimeout(debounceTimer);
        if (activeAbortController) {
          activeAbortController.abort();
          activeAbortController = null;
        }
        searchInput.focus();
        renderInitialList();
      });
    }

    // Category Tabs Filter
    categoryTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        categoryTabs.forEach((t) => {
          t.classList.remove('bg-[#fcdb56]', 'text-[#080117]', 'shadow-xs', 'font-semibold');
          t.classList.add('bg-white', 'dark:bg-[#190b34]', 'text-neutral-700', 'dark:text-neutral-200', 'font-medium', 'border', 'border-neutral-200/80', 'dark:border-[#321860]');
        });
        tab.classList.remove('bg-white', 'dark:bg-[#190b34]', 'text-neutral-700', 'dark:text-neutral-200', 'border', 'border-neutral-200/80', 'dark:border-[#321860]');
        tab.classList.add('bg-[#fcdb56]', 'text-[#080117]', 'shadow-xs', 'font-semibold');

        activeCategory = tab.dataset.category || 'ALL';

        const val = (searchInput ? searchInput.value : '').trim();
        if (val.length >= 2) {
          performAjaxSearch(val, activeCategory);
        } else {
          renderInitialList();
        }
      });
    });

    if (searchInput) {
      searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          const trimmed = this.value.trim();
          if (trimmed.length >= 2) {
            if (debounceTimer) clearTimeout(debounceTimer);
            performAjaxSearch(trimmed, activeCategory);
          }
        }
      });

      searchInput.addEventListener('input', function () {
        const val = this.value;
        if (clearBtn) clearBtn.style.display = val.length > 0 ? 'block' : 'none';

        if (debounceTimer) clearTimeout(debounceTimer);

        const trimmed = val.trim();
        if (trimmed.length === 0) {
          if (activeAbortController) {
            activeAbortController.abort();
            activeAbortController = null;
          }
          renderInitialList();
        } else if (trimmed.length < 2) {
          if (activeAbortController) {
            activeAbortController.abort();
            activeAbortController = null;
          }
          renderNeedMoreCharsNotice();
        } else {
          debounceTimer = setTimeout(function () {
            performAjaxSearch(trimmed, activeCategory);
          }, 250);
        }
      });
    }

    function renderNeedMoreCharsNotice() {
      if (!resultsContainer) return;
      if (countEl) {
        countEl.textContent = 'Ən azı 2 hərf daxil edin';
      }
      resultsContainer.innerHTML = `
        <div class="h-full flex flex-col items-center justify-center py-14 text-center text-neutral-400 dark:text-neutral-500">
          <svg class="w-8 h-8 mx-auto mb-2 text-[#fcdb56]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <p class="text-xs sm:text-sm font-semibold text-neutral-700 dark:text-neutral-300">Axtarış üçün ən azı 2 hərf daxil edin...</p>
        </div>
      `;
    }

    function renderInitialList() {
      if (!resultsContainer) return;
      const articles = window.__articles || [];
      let initialItems = articles;

      if (activeCategory !== 'ALL') {
        initialItems = initialItems.filter(function (a) {
          return (a.category || '').toLowerCase() === activeCategory.toLowerCase();
        });
      }

      if (countEl) {
        countEl.textContent = initialItems.length + ' material tapıldı';
      }

      renderArticlesMarkup(initialItems);
    }

    function performAjaxSearch(query, category) {
      if (!resultsContainer) return;

      if (activeAbortController) {
        activeAbortController.abort();
      }
      activeAbortController = new AbortController();

      // Loading state
      resultsContainer.innerHTML = `
        <div class="h-full flex flex-col items-center justify-center py-14 text-center text-neutral-400 dark:text-neutral-500">
          <div class="w-6 h-6 border-2 border-[#fcdb56] border-t-transparent rounded-full animate-spin mx-auto mb-2.5"></div>
          <p class="text-xs text-neutral-500 font-mono">Axtarılır...</p>
        </div>
      `;

      const qParam = encodeURIComponent(query);
      const catParam = encodeURIComponent(category);
      const apiUrl = `/api/search?q=${qParam}&category=${catParam}`;

      const fetchOptions = {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        signal: activeAbortController.signal
      };

      fetch(apiUrl, fetchOptions)
        .then(function (res) {
          if (!res.ok) {
            // If rewrite route returns 404, fallback to direct search.php script
            if (res.status === 404) {
              return fetch(`/api/search.php?q=${qParam}&category=${catParam}`, fetchOptions).then(function (r2) {
                if (!r2.ok) throw new Error('Axtarış cavab vermədi');
                return r2.json();
              });
            }
            throw new Error('Axtarış cavab vermədi');
          }
          return res.json();
        })
        .then(function (data) {
          const items = (data && data.results) ? data.results : [];
          if (countEl) {
            countEl.textContent = (data && typeof data.count !== 'undefined' ? data.count : items.length) + ' material tapıldı';
          }

          if (items.length === 0) {
            resultsContainer.innerHTML = `
              <div class="h-full flex flex-col items-center justify-center py-12 text-center text-neutral-400 dark:text-neutral-500">
                <svg class="w-10 h-10 mx-auto mb-3 text-neutral-300 dark:text-neutral-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">Heç bir nəticə tapılmadı</p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 max-w-sm">
                  "${query}" üzrə uyğun nəticə tapılmadı. Digər açar sözlərlə axtarmağı və ya bölmə filtrini dəyişməyi yoxlayın.
                </p>
              </div>
            `;
          } else {
            renderArticlesMarkup(items);
          }
        })
        .catch(function (err) {
          if (err.name === 'AbortError') return;

          // Fallback to client-side database search if server API had an issue
          console.warn('AJAX search fallback to local DB:', err);
          fallbackLocalSearch(query, category);
        });
    }

    function fallbackLocalSearch(query, category) {
      const q = query.toLowerCase();
      let matches = (window.__articles || []).filter(function (a) {
        const title = (a.title || '').toLowerCase();
        const summary = (a.summary || a.excerpt || '').toLowerCase();
        const tags = (a.tags || []).join(' ').toLowerCase();
        return title.includes(q) || summary.includes(q) || tags.includes(q);
      });

      if (category !== 'ALL') {
        matches = matches.filter(function (a) {
          return (a.category || '').toLowerCase() === category.toLowerCase();
        });
      }

      if (countEl) {
        countEl.textContent = matches.length + ' material tapıldı';
      }

      if (matches.length === 0) {
        resultsContainer.innerHTML = `
          <div class="h-full flex flex-col items-center justify-center py-12 text-center text-neutral-400 dark:text-neutral-500">
            <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-200">Heç bir nəticə tapılmadı</p>
          </div>
        `;
      } else {
        renderArticlesMarkup(matches);
      }
    }

    function renderArticlesMarkup(items) {
      if (!resultsContainer) return;
      resultsContainer.innerHTML = items
        .map(function (a) {
          const url = `/${a.category}/${a.slug}`;
          return `
            <a href="${url}" class="group flex items-center gap-3.5 p-2.5 rounded-xl hover:bg-[#fcdb56]/15 dark:hover:bg-[#fcdb56]/15 border border-neutral-100 dark:border-[#261545] transition-all cursor-pointer bg-neutral-50/40 dark:bg-[#140829]/60">
              <div class="search-modal-item-thumb shrink-0">
                ${
                  a.image_url
                    ? `<img src="${a.image_url}" alt="${a.title}" loading="lazy">`
                    : `<div class="w-full h-full flex items-center justify-center text-neutral-400 text-xs font-mono">404</div>`
                }
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 text-[10px] text-neutral-500 dark:text-neutral-400 mb-1 font-mono">
                  <span class="font-bold text-[#080117] bg-[#fcdb56] px-1.5 py-0.5 rounded-xs uppercase text-[9px]">
                    ${a.category}
                  </span>
                  <span>•</span>
                  <span>${a.date || ''}</span>
                </div>
                <h4 class="text-xs sm:text-sm font-semibold text-neutral-900 dark:text-white group-hover:text-[#080117] dark:group-hover:text-[#fcdb56] transition-colors line-clamp-1">
                  ${a.title}
                </h4>
                <p class="text-xs text-neutral-500 dark:text-neutral-300 line-clamp-1 mt-0.5">
                  ${a.excerpt || a.summary || ''}
                </p>
              </div>
              <svg class="w-4 h-4 text-neutral-400 dark:text-neutral-500 group-hover:text-[#080117] dark:group-hover:text-[#fcdb56] group-hover:translate-x-1 transition-all self-center shrink-0 ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
              </svg>
            </a>
          `;
        })
        .join('');
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // 4. BOOKMARKS ENGINE
  // ═══════════════════════════════════════════════════════════════════════════
  const BOOKMARKS_STORAGE_KEY = 'alt404_saved_articles';

  function getSavedArticles() {
    try {
      const data = localStorage.getItem(BOOKMARKS_STORAGE_KEY);
      return data ? JSON.parse(data) : [];
    } catch (e) {
      return [];
    }
  }

  function setSavedArticles(articles) {
    try {
      localStorage.setItem(BOOKMARKS_STORAGE_KEY, JSON.stringify(articles));
    } catch (e) {}
    updateBookmarksUI();
  }

  window.isArticleSaved = function (id) {
    const items = getSavedArticles();
    return items.some((item) => String(item.id) === String(id));
  };

  window.toggleArticleBookmark = function (articleData) {
    if (!articleData || !articleData.id) return;
    const items = getSavedArticles();
    const index = items.findIndex((item) => String(item.id) === String(articleData.id));

    if (index >= 0) {
      items.splice(index, 1);
      setSavedArticles(items);
      window.showToast('Məqalə yaddaşdan silindi');
    } else {
      items.unshift({
        id: String(articleData.id),
        title: articleData.title || '',
        slug: articleData.slug || '',
        category: articleData.category || '',
        category_name: articleData.category_name || articleData.category || '',
        image: articleData.image || '',
        date: articleData.date || '',
      });
      setSavedArticles(items);
      window.showToast('Məqalə yadda saxlanıldı');
    }
  };

  function updateBookmarksUI() {
    const items = getSavedArticles();
    const count = items.length;

    // 1. Update header badges
    const badge = document.getElementById('bookmarks-counter-badge') || document.getElementById('bookmark-badge-count');
    if (badge) {
      badge.textContent = count;
      if (count > 0) {
        badge.style.display = 'flex';
        badge.classList.remove('hidden');
      } else {
        badge.style.display = 'none';
        badge.classList.add('hidden');
      }
    }

    // 2. Update drawer header count
    const headerCount = document.getElementById('bookmarks-header-count') || document.getElementById('drawer-bookmarks-count');
    if (headerCount) {
      headerCount.textContent = count + ' material yaddaşdadır';
    }

    // 3. Update bookmark icons on all page buttons
    document.querySelectorAll('[data-bookmark-btn], [data-bookmark-id], .bookmark-toggle-btn').forEach(function (btn) {
      const id = btn.dataset.id || btn.dataset.bookmarkId;
      const isSaved = items.some((item) => String(item.id) === String(id));
      const icon = btn.querySelector('svg');
      if (isSaved) {
        btn.classList.add('is-saved');
        btn.classList.remove('opacity-0');
        if (icon) {
          icon.setAttribute('fill', '#fcdb56');
          icon.style.fill = '#fcdb56';
          icon.classList.add('text-[#080117]', 'dark:text-[#fcdb56]');
        }
      } else {
        btn.classList.remove('is-saved');
        if (icon) {
          icon.setAttribute('fill', 'none');
          icon.style.fill = 'none';
          icon.classList.remove('text-[#080117]', 'dark:text-[#fcdb56]');
        }
      }
    });

    // 4. Render drawer bookmarks list
    const container = document.getElementById('bookmarks-list-container');
    if (container) {
      if (count === 0) {
        container.innerHTML = `
          <div class="py-16 text-center">
            <div class="w-12 h-12 rounded-full bg-neutral-100 dark:bg-[#1a0c33] flex items-center justify-center mx-auto mb-3 text-neutral-400">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>
              </svg>
            </div>
            <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Saxlanılan məqalə yoxdur</p>
            <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Xəbərlərdəki əlfəcin düyməsinə basaraq yadda saxlayın</p>
          </div>
        `;
      } else {
        container.innerHTML = items
          .map(function (item) {
            const link = item.category && item.slug ? `/${item.category}/${item.slug}` : '#';
            return `
              <div class="group relative flex items-center gap-3 p-2.5 rounded-xl border border-neutral-200/80 dark:border-[#261545] bg-neutral-50 dark:bg-[#120726] hover:border-[#fcdb56]/80 transition-all">
                <a href="${link}" class="bookmark-item-thumb">
                  ${
                    item.image
                      ? `<img src="${item.image}" alt="" loading="lazy">`
                      : `<div class="w-full h-full flex items-center justify-center text-neutral-400 text-xs font-mono">404</div>`
                  }
                </a>
                <div class="flex-1 min-w-0 pr-6">
                  <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-[#080117] dark:text-[#fcdb56] mb-0.5">
                    ${item.category_name || item.category || 'Xəbər'}
                  </span>
                  <a href="${link}" class="block text-xs font-semibold text-neutral-900 dark:text-white hover:text-[#fcdb56] truncate transition-colors">
                    ${item.title}
                  </a>
                  <span class="text-[10px] text-neutral-400 font-mono mt-0.5 block">${item.date || ''}</span>
                </div>
                <button
                  type="button"
                  data-remove-bookmark-id="${item.id}"
                  class="absolute top-2.5 right-2.5 p-1.5 rounded-md text-neutral-400 hover:text-red-500 dark:hover:text-red-400 transition-colors cursor-pointer z-20"
                  title="Sil"
                  aria-label="Sil"
                >
                  <svg class="w-4 h-4 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>
            `;
          })
          .join('');
      }
    }
  }

  function initBookmarks() {
    // 1. Direct listener on container to guarantee instant removal inside drawer
    const bookmarksList = document.getElementById('bookmarks-list-container');
    if (bookmarksList) {
      bookmarksList.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('[data-remove-bookmark-id]');
        if (removeBtn) {
          e.preventDefault();
          e.stopPropagation();
          const id = removeBtn.getAttribute('data-remove-bookmark-id');
          if (id) {
            const items = getSavedArticles().filter((item) => String(item.id) !== String(id));
            setSavedArticles(items);
            window.showToast('Məqalə siyahıdan silindi');
          }
        }
      });
    }

    // 2. Global delegate for card bookmark clicks & removals
    document.addEventListener('click', function (e) {
      // Delegate remove bookmark in drawer or elsewhere
      const removeBtn = e.target.closest('[data-remove-bookmark-id]');
      if (removeBtn) {
        e.preventDefault();
        e.stopPropagation();
        const id = removeBtn.getAttribute('data-remove-bookmark-id');
        if (id) {
          const items = getSavedArticles().filter((item) => String(item.id) !== String(id));
          setSavedArticles(items);
          window.showToast('Məqalə siyahıdan silindi');
          return;
        }
      }

      // Delegate card bookmark clicks
      const btn = e.target.closest('[data-bookmark-btn], [data-bookmark-id], .bookmark-toggle-btn');
      if (btn) {
        e.preventDefault();
        e.stopPropagation();
        const id = btn.dataset.id || btn.dataset.bookmarkId;
        if (!id) return;

        let data = {
          id: id,
          title: btn.dataset.title,
          slug: btn.dataset.slug,
          category: btn.dataset.category,
          category_name: btn.dataset.categoryName,
          image: btn.dataset.image,
          date: btn.dataset.date,
        };

        if (!data.title && window.__articles) {
          const found = window.__articles.find((a) => String(a.id) === String(id));
          if (found) {
            data.title = found.title;
            data.slug = found.slug;
            data.category = found.category;
            data.category_name = found.category_label || found.category;
            data.image = found.image_url;
            data.date = found.date;
          }
        }

        window.toggleArticleBookmark(data);
        return;
      }
    });

    // Drawer open / close handlers
    const openBtn = document.getElementById('open-bookmarks-btn');
    const drawerBackdrop = document.getElementById('bookmarks-drawer-backdrop');
    const closeBtn = document.getElementById('bookmarks-close-btn') || document.getElementById('close-bookmarks-drawer-btn');
    const clearAllBtn = document.getElementById('bookmarks-clear-all-btn') || document.getElementById('clear-all-bookmarks-btn');

    function openBookmarksDrawer() {
      if (!drawerBackdrop) return;
      drawerBackdrop.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      updateBookmarksUI();
    }

    function closeBookmarksDrawer() {
      if (!drawerBackdrop) return;
      drawerBackdrop.style.display = 'none';
      document.body.style.overflow = '';
    }

    if (openBtn) {
      openBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openBookmarksDrawer();
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', function (e) {
        e.preventDefault();
        closeBookmarksDrawer();
      });
    }

    if (drawerBackdrop) {
      drawerBackdrop.addEventListener('click', function (e) {
        if (e.target === drawerBackdrop) closeBookmarksDrawer();
      });
    }

    if (clearAllBtn) {
      clearAllBtn.addEventListener('click', function () {
        const count = getSavedArticles().length;
        if (count === 0) return;
        if (confirm('Bütün saxlanılan məqalələri silmək istədiyinizə əminsiniz?')) {
          setSavedArticles([]);
          window.showToast('Bütün saxlanılanlar silindi');
        }
      });
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && drawerBackdrop && drawerBackdrop.style.display !== 'none') {
        closeBookmarksDrawer();
      }
    });

    updateBookmarksUI();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // 5. VIDEO MODAL PLAYER (LANDSCAPE & SHORTS FORMATS)
  // ═══════════════════════════════════════════════════════════════════════════
  function initVideoModal() {
    const videoModal = document.getElementById('video-player-modal');
    const videoDialog = document.getElementById('video-modal-dialog');
    const videoEmbedContainer = document.getElementById('video-embed-container');
    const videoBadge = document.getElementById('video-modal-type-badge');
    const videoIframe = document.getElementById('video-player-iframe');
    const videoNative = document.getElementById('video-player-native');
    const videoTitle = document.getElementById('video-player-title') || document.getElementById('video-modal-title');
    const videoSubtitle = document.getElementById('video-player-subtitle');
    const videoYtBtn = document.getElementById('video-modal-yt-btn');
    const closeBtn = document.getElementById('video-modal-close-btn');

    function extractYtId(url) {
      if (!url) return null;
      let clean = String(url).trim().replace(/[\u200B-\u200D\uFEFF]/g, '').replace(/^["'`]|["'`]$/g, '');
      if (!clean) return null;
      if (/^[a-zA-Z0-9_-]{11}$/.test(clean)) return clean;
      const shortsMatch = clean.match(/\/shorts\/([a-zA-Z0-9_-]{11})/i);
      if (shortsMatch) return shortsMatch[1];
      const youtuBeMatch = clean.match(/youtu\.be\/([a-zA-Z0-9_-]{11})/i);
      if (youtuBeMatch) return youtuBeMatch[1];
      const pathMatch = clean.match(/\/(?:embed|v|vi|live)\/([a-zA-Z0-9_-]{11})/i);
      if (pathMatch) return pathMatch[1];
      const queryMatch = clean.match(/[?&]v=([a-zA-Z0-9_-]{11})/i);
      if (queryMatch) return queryMatch[1];
      const genericMatch = clean.match(/(?:youtube(?:-nocookie)?\.com|youtu\.be).*(?:[\/=])([a-zA-Z0-9_-]{11})/i);
      if (genericMatch) return genericMatch[1];
      return null;
    }

    function openVideo(url, title, opts = {}) {
      if (!videoModal) return;
      const clean = String(url || '').trim();
      const ytId = extractYtId(clean);
      const isShorts = opts.format === 'shorts' || clean.toLowerCase().includes('/shorts/') || opts.isProject;

      // 1. Configure layout dimensions & classes
      if (videoDialog && videoEmbedContainer) {
        if (isShorts) {
          // Shorts format (9:16 vertical ratio)
          videoDialog.classList.add('video-modal-shorts-dialog');
          videoDialog.classList.remove('video-modal-landscape-dialog', 'max-w-4xl');
          videoEmbedContainer.classList.add('video-modal-shorts-container');
          videoEmbedContainer.classList.remove('video-modal-landscape-container', 'aspect-video');
          if (videoBadge) {
            videoBadge.textContent = opts.category ? (opts.category + ' // SHORTS') : 'LAYİHƏ // SHORTS';
          }
        } else {
          // Standard landscape format (16:9 widescreen)
          videoDialog.classList.add('video-modal-landscape-dialog', 'max-w-4xl');
          videoDialog.classList.remove('video-modal-shorts-dialog');
          videoEmbedContainer.classList.add('video-modal-landscape-container', 'aspect-video');
          videoEmbedContainer.classList.remove('video-modal-shorts-container');
          if (videoBadge) {
            videoBadge.textContent = 'YOUTUBE VİDEO';
          }
        }
      }

      // 2. Populate text info
      if (videoTitle) {
        videoTitle.textContent = title || (isShorts ? 'Xüsusi Layihə' : 'Video İcmal');
      }
      if (videoSubtitle) {
        const subText = opts.subtitle || opts.description || '';
        if (subText) {
          videoSubtitle.textContent = subText;
          videoSubtitle.classList.remove('hidden');
        } else {
          videoSubtitle.classList.add('hidden');
        }
      }

      // 3. Load media
      if (ytId) {
        const embedUrl = `https://www.youtube.com/embed/${ytId}?autoplay=1&rel=0&playsinline=1&enablejsapi=1`;
        if (videoIframe) {
          videoIframe.src = embedUrl;
          videoIframe.style.display = 'block';
        }
        if (videoNative) {
          videoNative.style.display = 'none';
          videoNative.pause();
          videoNative.src = '';
        }
        if (videoYtBtn) {
          videoYtBtn.href = isShorts ? `https://www.youtube.com/shorts/${ytId}` : `https://www.youtube.com/watch?v=${ytId}`;
          videoYtBtn.classList.remove('hidden');
          videoYtBtn.classList.add('inline-flex');
        }
      } else if (clean.match(/\.(mp4|webm|ogg|mov)(\?.*)?$/i)) {
        // Direct video source
        if (videoIframe) {
          videoIframe.style.display = 'none';
          videoIframe.src = '';
        }
        if (videoNative) {
          videoNative.src = clean;
          videoNative.style.display = 'block';
          videoNative.play().catch(() => {});
        }
        if (videoYtBtn) {
          videoYtBtn.classList.add('hidden');
          videoYtBtn.classList.remove('inline-flex');
        }
      } else if (clean) {
        // Fallback: load raw URL in iframe
        if (videoIframe) {
          videoIframe.src = clean;
          videoIframe.style.display = 'block';
        }
        if (videoNative) {
          videoNative.style.display = 'none';
          videoNative.pause();
          videoNative.src = '';
        }
        if (videoYtBtn) {
          videoYtBtn.href = clean;
          videoYtBtn.classList.remove('hidden');
          videoYtBtn.classList.add('inline-flex');
        }
      }

      videoModal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }

    function closeVideo() {
      if (!videoModal) return;
      videoModal.style.display = 'none';
      if (videoIframe) {
        videoIframe.src = '';
      }
      if (videoNative) {
        videoNative.pause();
        videoNative.src = '';
      }
      document.body.style.overflow = '';
    }

    document.addEventListener('click', function (e) {
      const trigger = e.target.closest('[data-video-url]');
      if (trigger) {
        e.preventDefault();
        const url = trigger.dataset.videoUrl;
        const title = trigger.dataset.videoTitle || '';
        const isProject = !!trigger.closest('#layiheler-section') || (trigger.id && trigger.id.startsWith('project-poster-'));
        const format = trigger.dataset.videoFormat || (isProject ? 'shorts' : (url && url.includes('/shorts/') ? 'shorts' : 'landscape'));
        const category = trigger.dataset.videoCategory || '';
        const subtitle = trigger.dataset.videoSubtitle || '';
        const description = trigger.dataset.videoDescription || '';
        openVideo(url, title, { format, isProject, category, subtitle, description });
      }
    });

    if (closeBtn) closeBtn.addEventListener('click', closeVideo);
    if (videoModal) {
      videoModal.addEventListener('click', function (e) {
        if (e.target === videoModal) closeVideo();
      });
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && videoModal && videoModal.style.display !== 'none') {
        closeVideo();
      }
    });
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // 6. TAG BAR HORIZONTAL SCROLL & OTHER CONTROLS
  // ═══════════════════════════════════════════════════════════════════════════
  function initHeaderBehaviors() {
    const tagContainer = document.getElementById('tag-scroll-container');
    const leftBtn = document.getElementById('scroll-tags-left-btn');
    const rightBtn = document.getElementById('scroll-tags-right-btn');

    if (tagContainer && leftBtn && rightBtn) {
      leftBtn.addEventListener('click', function () {
        tagContainer.scrollBy({ left: -260, behavior: 'smooth' });
      });
      rightBtn.addEventListener('click', function () {
        tagContainer.scrollBy({ left: 260, behavior: 'smooth' });
      });
    }

    // Mobile nav drawer
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const mobileDrawer = document.getElementById('mobile-nav-drawer');
    const mobileBackdrop = document.getElementById('mobile-drawer-backdrop');
    const closeMobileBtn = document.getElementById('close-mobile-nav');

    function openMobile() {
      if (!mobileDrawer || !mobileBackdrop) return;
      mobileBackdrop.style.display = 'block';
      mobileDrawer.classList.remove('-translate-x-full');
      document.body.style.overflow = 'hidden';
    }

    function closeMobile() {
      if (!mobileDrawer || !mobileBackdrop) return;
      mobileDrawer.classList.add('-translate-x-full');
      setTimeout(() => {
        mobileBackdrop.style.display = 'none';
        document.body.style.overflow = '';
      }, 250);
    }

    if (mobileBtn) mobileBtn.addEventListener('click', openMobile);
    if (closeMobileBtn) closeMobileBtn.addEventListener('click', closeMobile);
    if (mobileBackdrop) {
      mobileBackdrop.addEventListener('click', function (e) {
        if (e.target === mobileBackdrop) closeMobile();
      });
    }

    // Scroll-to-top button
    const scrollTopBtn = document.getElementById('scroll-to-top-btn');
    if (scrollTopBtn) {
      window.addEventListener('scroll', function () {
        if (window.scrollY > 350) {
          scrollTopBtn.style.display = 'flex';
        } else {
          scrollTopBtn.style.display = 'none';
        }
      }, { passive: true });

      scrollTopBtn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }

    // Newsletter submit
    const newsletterForm = document.getElementById('newsletter-form');
    const newsletterSubmitBtn = document.getElementById('newsletter-submit-btn');
    if (newsletterForm) {
      newsletterForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const input = document.getElementById('newsletter-email');
        if (input && input.value) {
          const originalBtnText = newsletterSubmitBtn ? newsletterSubmitBtn.innerHTML : null;
          if (newsletterSubmitBtn) {
             newsletterSubmitBtn.disabled = true;
             newsletterSubmitBtn.innerHTML = '<span class="w-4 h-4 border-2 border-[#080117] border-t-transparent rounded-full animate-spin"></span>';
          }
          try {
            const formData = new FormData();
            formData.append('email', input.value);
            const response = await fetch('/api/newsletter.php', {
              method: 'POST',
              body: formData
            });
            const result = await response.json();
            if (result.success) {
              window.showToast('Bülletenimizə abunə olduğunuz üçün təşəkkür edirik!');
              input.value = '';
              const successBox = document.getElementById('newsletter-success-box');
              if (successBox) {
                successBox.style.display = 'flex';
                newsletterForm.style.display = 'none';
              }
            } else {
              window.showToast(result.error || 'Xəta baş verdi.');
            }
          } catch (error) {
            window.showToast('Bağlantı xətası. Yenidən cəhd edin.');
          } finally {
            if (newsletterSubmitBtn && originalBtnText) {
              newsletterSubmitBtn.disabled = false;
              newsletterSubmitBtn.innerHTML = originalBtnText;
            }
          }
        }
      });
    }

    // Copy link button (single article view)
    const copyBtn = document.getElementById('copy-link-btn');
    if (copyBtn) {
      copyBtn.addEventListener('click', function () {
        if (navigator.clipboard) {
          navigator.clipboard.writeText(window.location.href).then(function () {
            window.showToast('Məqalə linki panoya kopyalandı!');
          });
        }
      });
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // INITIALIZATION ON DOM READY
  // ═══════════════════════════════════════════════════════════════════════════
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', run);
  } else {
    run();
  }

  function run() {
    initTheme();
    initSearchModal();
    initBookmarks();
    initVideoModal();
    initHeaderBehaviors();
  }
})();
