<?php
/**
 * Shared footer include for ALT404 Prime.
 */
$footerCategories = getVisibleCategories();
?>

<!-- =========================================================================
     GLOBAL MODALS & DRAWERS
     ========================================================================= -->

<!-- 1. Search Modal (Cmd+K / Search button) -->
<div
  id="search-overlay-modal"
  class="fixed inset-0 z-50 overflow-hidden bg-[#080117]/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6 animate-fade-in"
  style="display:none"
>
  <div
    id="search-modal-dialog"
    class="w-full max-w-3xl h-[580px] max-h-[82vh] bg-white dark:bg-[#0e041d] text-neutral-900 dark:text-neutral-100 rounded-2xl shadow-2xl overflow-hidden border border-neutral-200 dark:border-[#261545] flex flex-col transition-none"
  >
    <!-- 1. Search Input Bar (Fixed height) -->
    <div class="p-3.5 sm:p-4 border-b border-neutral-200 dark:border-[#261545] flex items-center gap-3 bg-neutral-50 dark:bg-[#140829] shrink-0">
      <svg class="w-5 h-5 text-[#080117] dark:text-[#fcdb56] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input
        id="search-input-field"
        type="text"
        placeholder="Məqalə, mövzu və ya açar söz axtarın (məs: Apple, Tesla, AI)..."
        class="flex-1 bg-transparent text-sm sm:text-base text-neutral-900 dark:text-white placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-none font-medium"
        autocomplete="off"
      />
      <button
        id="search-clear-btn"
        type="button"
        class="p-1 px-2.5 rounded-md bg-neutral-200/80 dark:bg-[#1f0f3d] hover:bg-neutral-300 dark:hover:bg-[#2c1655] text-neutral-600 dark:text-neutral-200 hover:text-neutral-900 dark:hover:text-white text-xs font-medium cursor-pointer transition-colors"
        style="display:none"
      >
        Təmizlə
      </button>
      <button
        id="search-modal-close-btn"
        type="button"
        aria-label="Axtarışı bağla"
        class="p-1.5 rounded-lg bg-neutral-200/80 dark:bg-[#1f0f3d] hover:bg-neutral-300 dark:hover:bg-[#2c1655] text-neutral-600 dark:text-neutral-200 hover:text-neutral-900 dark:hover:text-white transition-colors cursor-pointer"
      >
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <!-- 2. Quick Filter Categories (Neat horizontal scroll bar with stable height) -->
    <div class="px-4 py-2.5 bg-neutral-50/60 dark:bg-[#110624] border-b border-neutral-100 dark:border-[#261545] flex items-center gap-1.5 overflow-x-auto no-scrollbar shrink-0">
      <span class="text-[11px] text-neutral-400 dark:text-neutral-500 font-bold uppercase tracking-wider mr-1 shrink-0 flex items-center gap-1">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
        BÖLMƏ:
      </span>
      <div id="search-category-tabs" class="flex items-center gap-1.5">
        <button
          type="button"
          data-category="ALL"
          class="search-cat-tab px-3 py-1 rounded-lg text-xs tracking-wide transition-all whitespace-nowrap shrink-0 cursor-pointer bg-[#fcdb56] text-[#080117] font-semibold shadow-xs"
        >
          Hamısı
        </button>
        <?php foreach ($footerCategories as $fCat): ?>
          <button
            type="button"
            data-category="<?= e($fCat['slug']) ?>"
            class="search-cat-tab px-3 py-1 rounded-lg text-xs tracking-wide transition-all whitespace-nowrap shrink-0 cursor-pointer bg-white dark:bg-[#190b34] hover:bg-neutral-200/80 dark:hover:bg-[#25104c] text-neutral-700 dark:text-neutral-200 font-medium border border-neutral-200/80 dark:border-[#321860]"
          >
            <?= e(preg_replace('/\s+Xəbərləri$/iu', '', $fCat['label'])) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 3. Results List (Scrollable news list) -->
    <div id="search-modal-results" class="flex-1 overflow-y-auto min-h-0 p-4 space-y-2.5 bg-white dark:bg-[#0e041d]">
      <!-- Filled dynamically by main.js -->
    </div>

    <!-- 4. Search Footer -->
    <div class="p-3 bg-neutral-50 dark:bg-[#140829] border-t border-neutral-200 dark:border-[#261545] flex items-center justify-between text-[11px] text-neutral-500 dark:text-neutral-400 shrink-0">
      <span id="search-modal-count" class="font-medium text-neutral-600 dark:text-neutral-300">0 material tapıldı</span>
      <span class="font-mono text-neutral-400 dark:text-neutral-500">ESC ilə bağla</span>
    </div>
  </div>
</div>

<!-- 2. Bookmarks Drawer (Slide-in right) -->
<div
  id="bookmarks-drawer-backdrop"
  class="fixed inset-0 z-50 overflow-hidden bg-[#080117]/70 backdrop-blur-xs flex justify-end"
  style="display:none"
>
  <div
    class="w-full max-w-md bg-white dark:bg-[#0e041d] text-neutral-900 dark:text-neutral-100 h-full shadow-2xl flex flex-col border-l border-neutral-200 dark:border-[#261545] animate-slide-in-right"
  >
    <!-- Drawer Header -->
    <div class="p-4 sm:p-5 border-b border-neutral-200 dark:border-[#261545] flex items-center justify-between bg-neutral-50 dark:bg-[#140829]">
      <div class="flex items-center gap-2">
        <div class="p-2 rounded-lg bg-[#fcdb56]/30 text-[#080117] dark:text-[#fcdb56]">
          <svg class="w-4 h-4 fill-[#080117] dark:fill-[#fcdb56]" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
        </div>
        <div>
          <h3 class="font-semibold text-sm uppercase tracking-wider text-neutral-900 dark:text-white">
            Saxlanılan Materiallar
          </h3>
          <p id="bookmarks-header-count" class="text-[11px] text-neutral-500 dark:text-neutral-400 font-mono">
            0 material yaddaşdadır
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          id="bookmarks-clear-all-btn"
          type="button"
          class="p-2 rounded-lg text-neutral-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors text-xs flex items-center gap-1 cursor-pointer"
          title="Hamısını sil"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
        </button>
        <button
          id="bookmarks-close-btn"
          type="button"
          class="p-2 rounded-lg bg-neutral-200/70 dark:bg-[#1f0f3d] hover:bg-neutral-300 dark:hover:bg-[#2c1655] text-neutral-700 dark:text-neutral-200 transition-colors cursor-pointer"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>

    <!-- Drawer Items List -->
    <div id="bookmarks-list-container" class="flex-1 overflow-y-auto p-4 space-y-3">
      <!-- Filled dynamically by main.js -->
    </div>
  </div>
</div>

<!-- 3. Video Modal Player -->
<div
  id="video-player-modal"
  class="fixed inset-0 z-50 bg-[#080117]/85 backdrop-blur-md flex items-center justify-center p-4 sm:p-6"
  style="display:none"
>
  <div
    id="video-modal-dialog"
    class="w-full max-w-4xl bg-[#080117] rounded-2xl overflow-hidden shadow-2xl border border-[#261545] relative animate-fade-in"
  >
    <!-- Video Modal Top Header -->
    <div class="flex items-center justify-between px-4 py-3 sm:px-6 sm:py-3.5 border-b border-[#261545] bg-[#080117]">
      <div class="flex items-center gap-2.5">
        <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
        <span class="text-xs font-mono font-bold uppercase tracking-wider text-[#fcdb56]">
          YouTube Video Pleyeri
        </span>
      </div>

      <button
        id="video-modal-close-btn"
        type="button"
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#fcdb56] hover:bg-[#fcdb56]/90 text-[#080117] font-bold text-xs shadow-md transition-transform active:scale-95 cursor-pointer"
        title="Bağla (Esc)"
      >
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        <span class="hidden sm:inline font-bold">Bağla</span>
      </button>
    </div>

    <!-- Video Embed Frame -->
    <div class="relative aspect-video w-full bg-black">
      <iframe
        id="video-player-iframe"
        src=""
        title="Video"
        class="w-full h-full border-0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen
      ></iframe>
    </div>

    <!-- Video Info -->
    <div class="p-4 sm:p-5 bg-[#0c0322]">
      <h2 id="video-player-title" class="text-sm sm:text-base md:text-lg font-semibold text-white leading-snug"></h2>
    </div>
  </div>
</div>

<!-- 4. Floating Toast Notification -->
<div
  id="app-toast-notification"
  class="fixed bottom-5 right-5 z-50 px-4 py-3 bg-[#080117] text-white border border-[#fcdb56]/80 rounded-xl shadow-2xl flex items-center gap-2.5 text-xs font-semibold"
  style="display:none"
>
  <span class="w-2 h-2 rounded-full bg-[#fcdb56] animate-ping"></span>
  <span id="app-toast-text">Məlumat yeniləndi</span>
</div>

<!-- =========================================================================
     SITE FOOTER
     ========================================================================= -->
<footer id="main-footer" class="w-full bg-[#FFFDF5] dark:bg-[#080117] border-t border-neutral-300 dark:border-[#261545] text-neutral-900 dark:text-neutral-100 pt-12 pb-8 mt-auto">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 pb-10 border-b border-neutral-300/80 dark:border-[#261545]">
      
      <!-- Col 1: Brand Info & Newsletter (5 cols) -->
      <div class="lg:col-span-5 space-y-4">
        <div class="flex items-center gap-2">
          <a href="/" class="hover:opacity-85 transition-opacity select-none" title="ALT404 - Əsas səhifə">
            <img src="/assets/img/logo-dark.png" alt="ALT404" class="h-8 sm:h-9 w-auto object-contain select-none dark:hidden" loading="lazy">
            <img src="/assets/img/logo.png" alt="ALT404" class="h-8 sm:h-9 w-auto object-contain select-none hidden dark:block" loading="lazy">
          </a>
        </div>

        <p class="text-sm text-neutral-600 dark:text-neutral-400 max-w-sm leading-relaxed">
          Azərbaycanın peşəkar texnologiya mediası — sürətli, dərin və minimal | Xəbərlər, analitik icmallar və eksklüziv layihələr
        </p>

        <!-- Newsletter Subscription -->
        <div class="pt-2 max-w-md">
          <label for="newsletter-email" class="block text-xs font-semibold uppercase tracking-wider text-neutral-800 dark:text-neutral-200 mb-2">
            Həftəlik Texno-Bülletenə Qoşul
          </label>
          <div id="newsletter-success-box" class="items-center gap-2 p-3 bg-[#fcdb56]/20 text-[#080117] dark:text-[#fcdb56] border border-[#fcdb56] rounded-lg text-xs font-medium" style="display:none">
            <svg class="w-4 h-4 text-[#080117] dark:text-[#fcdb56] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span>Təşəkkürlər! Ən son texnoloji xülasələr e-poçtunuza göndəriləcək.</span>
          </div>
          <form id="newsletter-form" class="flex items-center gap-2">
            <input
              id="newsletter-email"
              type="email"
              required
              placeholder="E-poçt ünvanınız..."
              class="flex-1 px-3.5 py-2 text-xs rounded-lg border border-neutral-300 dark:border-[#261545] bg-white dark:bg-[#120726] text-neutral-900 dark:text-white placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-[#fcdb56]"
            />
            <button
              id="newsletter-submit-btn"
              type="submit"
              class="px-4 py-2 rounded-lg bg-[#fcdb56] text-[#080117] font-semibold text-xs uppercase tracking-wider hover:bg-[#fcdb56]/90 transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer"
            >
              <span>ABUNƏ OL</span>
              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
          </form>
        </div>

        <!-- Social Media Links (Loaded from settings; hidden if empty) -->
        <?php
          $socialLinks = [
            'instagram' => [
              'url'   => trim(getSettingValue('instagram_link', '')),
              'title' => 'Instagram',
              'icon'  => '<svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>'
            ],
            'facebook' => [
              'url'   => trim(getSettingValue('facebook_link', '')),
              'title' => 'Facebook',
              'icon'  => '<svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>'
            ],
            'x' => [
              'url'   => trim(getSettingValue('twitter_link', '')),
              'title' => 'X',
              'icon'  => '<svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>'
            ],
            'youtube' => [
              'url'   => trim(getSettingValue('youtube_link', '')),
              'title' => 'YouTube',
              'icon'  => '<svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/></svg>'
            ],
            'tiktok' => [
              'url'   => trim(getSettingValue('tiktok_link', '')),
              'title' => 'TikTok',
              'icon'  => '<svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.86 4.43 6.28 6.28 0 0 0 1.93-4.47V8.5a8.28 8.28 0 0 0 4.8 1.54V6.69h-1z" /></svg>'
            ],
            'telegram' => [
              'url'   => trim(getSettingValue('telegram_link', '')),
              'title' => 'Telegram',
              'icon'  => '<svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="m20.665 3.717-17.73 6.837c-1.21.486-1.203 1.161-.222 1.462l4.552 1.42 10.532-6.645c.498-.303.953-.14.579.192l-8.533 7.701h-.002l-.313 4.672c.46 0 .664-.211.921-.46l2.21-2.15 4.597 3.397c.848.467 1.457.227 1.668-.785l3.019-14.228c.309-1.239-.473-1.8-1.282-1.434z" /></svg>'
            ],
            'linkedin' => [
              'url'   => trim(getSettingValue('linkedin_link', '')),
              'title' => 'LinkedIn',
              'icon'  => '<svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>'
            ],
          ];
          $activeSocialLinks = array_filter($socialLinks, fn($item) => !empty($item['url']));
        ?>

        <?php if (!empty($activeSocialLinks)): ?>
        <div class="pt-3">
          <span class="block text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-2.5">
            Bizi İzləyin
          </span>
          <div class="flex flex-wrap items-center gap-2">
            <?php foreach ($activeSocialLinks as $platform => $s): ?>
              <a
                id="footer-social-<?= e($platform) ?>"
                href="<?= e($s['url']) ?>"
                target="_blank"
                rel="noreferrer"
                title="<?= e($s['title']) ?>"
                aria-label="<?= e($s['title']) ?>"
                class="w-9 h-9 flex items-center justify-center rounded-lg bg-neutral-100 dark:bg-[#15082d] text-neutral-700 dark:text-neutral-200 hover:bg-[#fcdb56] dark:hover:bg-[#fcdb56] hover:text-[#080117] dark:hover:text-[#080117] hover:border-[#fcdb56] hover:scale-105 active:scale-95 transition-all duration-200 border border-neutral-200 dark:border-[#281648] shadow-xs cursor-pointer"
              >
                <?= $s['icon'] ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Col 2: Bölmələr (3 cols) -->
      <div class="lg:col-span-3">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-neutral-950 dark:text-white mb-3.5 flex items-center gap-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-[#fcdb56] shadow-[0_0_6px_rgba(252,219,86,0.8)]"></span>
          Bölmələr
        </h4>
        <ul class="space-y-2 text-xs text-neutral-700 dark:text-neutral-300">
          <li>
            <a href="/" class="hover:text-[#080117] dark:hover:text-[#fcdb56] hover:translate-x-0.5 transition-all font-medium flex items-center gap-1.5">
              <span class="text-neutral-400 dark:text-neutral-600 text-[10px]">›</span>
              <span>ANA SƏHİFƏ</span>
            </a>
          </li>
          <?php foreach ($footerCategories as $fCat): ?>
            <li>
              <a href="/<?= e($fCat['slug']) ?>" class="hover:text-[#080117] dark:hover:text-[#fcdb56] hover:translate-x-0.5 transition-all font-medium flex items-center gap-1.5">
                <span class="text-neutral-400 dark:text-neutral-600 text-[10px]">›</span>
                <span><?= e(mb_strtoupper(preg_replace('/\s+Xəbərləri$/iu', '', $fCat['label']), 'UTF-8')) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Col 3: Şirkət (2 cols) -->
      <div class="lg:col-span-2 space-y-3">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-neutral-950 dark:text-white mb-3.5 flex items-center gap-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-[#fcdb56] shadow-[0_0_6px_rgba(252,219,86,0.8)]"></span>
          Şirkət
        </h4>
        <ul class="space-y-2.5 text-xs text-neutral-700 dark:text-neutral-300 font-medium">
          <li>
            <a href="/haqqimizda" class="hover:text-[#080117] dark:hover:text-[#fcdb56] transition-colors flex items-center gap-1.5">
              <span class="text-neutral-400 dark:text-neutral-600 text-[10px]">›</span>
              <span>Haqqımızda</span>
            </a>
          </li>
          <li>
            <a href="/elaqe" class="hover:text-[#080117] dark:hover:text-[#fcdb56] transition-colors flex items-center gap-1.5">
              <span class="text-neutral-400 dark:text-neutral-600 text-[10px]">›</span>
              <span>Əlaqə</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- Col 4: Hüquqi (2 cols) -->
      <div class="lg:col-span-2 space-y-3">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-neutral-950 dark:text-white mb-3.5 flex items-center gap-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-[#fcdb56] shadow-[0_0_6px_rgba(252,219,86,0.8)]"></span>
          Hüquqi
        </h4>
        <ul class="space-y-2.5 text-xs text-neutral-700 dark:text-neutral-300 font-medium">
          <li>
            <a href="/istifade-sertleri" class="hover:text-[#080117] dark:hover:text-[#fcdb56] transition-colors flex items-center gap-1.5">
              <span class="text-neutral-400 dark:text-neutral-600 text-[10px]">›</span>
              <span>İstifadə şərtləri</span>
            </a>
          </li>
          <li>
            <a href="/cerezler" class="hover:text-[#080117] dark:hover:text-[#fcdb56] transition-colors flex items-center gap-1.5">
              <span class="text-neutral-400 dark:text-neutral-600 text-[10px]">›</span>
              <span>Çərəzlər</span>
            </a>
          </li>
        </ul>
      </div>

    </div>

    <!-- Bottom Bar with Copyright & Scroll to Top -->
    <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-neutral-600 dark:text-neutral-400">
      <div class="text-center sm:text-left font-medium">
        © <?= date('Y') ?> ALT404.com | Bütün hüquqlar qorunur
      </div>

      <div class="flex items-center gap-2">
        <button
          id="scroll-to-top-btn"
          type="button"
          class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-neutral-200 dark:bg-[#1a0c35] text-neutral-800 dark:text-neutral-200 hover:bg-[#fcdb56] dark:hover:bg-[#fcdb56] hover:text-[#080117] dark:hover:text-[#080117] transition-colors font-medium text-xs shadow-2xs cursor-pointer border border-transparent dark:border-[#261545]"
          title="Yuxarı qayıt"
        >
          <span>Yuxarı</span>
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
        </button>
      </div>
    </div>
  </div>
</footer>

<!-- Interactive Client-side Script -->
<script src="/assets/js/main.js"></script>
</body>
</html>
