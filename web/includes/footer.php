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
<div id="search-overlay-modal"
  class="fixed inset-0 z-50 overflow-hidden bg-[#080117]/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-6 animate-fade-in"
  style="display:none">
  <div id="search-modal-dialog"
    class="w-full max-w-3xl h-[580px] max-h-[82vh] bg-white dark:bg-[#0e041d] text-neutral-900 dark:text-neutral-100 rounded-2xl shadow-2xl overflow-hidden border border-neutral-200 dark:border-[#261545] flex flex-col transition-none">
    <!-- 1. Search Input Bar (Fixed height) -->
    <div
      class="p-3.5 sm:p-4 border-b border-neutral-200 dark:border-[#261545] flex items-center gap-3 bg-neutral-50 dark:bg-[#140829] shrink-0">
      <svg class="w-5 h-5 text-[#080117] dark:text-[#fcdb56] shrink-0" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8" />
        <line x1="21" y1="21" x2="16.65" y2="16.65" />
      </svg>
      <input id="search-input-field" type="text"
        placeholder="Məqalə, mövzu və ya açar söz axtarın (məs: Apple, Tesla, AI)..."
        class="flex-1 bg-transparent text-sm sm:text-base text-neutral-900 dark:text-white placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-none font-medium"
        autocomplete="off" />
      <button id="search-clear-btn" type="button"
        class="p-1 px-2.5 rounded-md bg-neutral-200/80 dark:bg-[#1f0f3d] hover:bg-neutral-300 dark:hover:bg-[#2c1655] text-neutral-600 dark:text-neutral-200 hover:text-neutral-900 dark:hover:text-white text-xs font-medium cursor-pointer transition-colors"
        style="display:none">
        Təmizlə
      </button>
      <button id="search-modal-close-btn" type="button" aria-label="Axtarışı bağla"
        class="p-1.5 rounded-lg bg-neutral-200/80 dark:bg-[#1f0f3d] hover:bg-neutral-300 dark:hover:bg-[#2c1655] text-neutral-600 dark:text-neutral-200 hover:text-neutral-900 dark:hover:text-white transition-colors cursor-pointer">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
      </button>
    </div>

    <!-- 2. Quick Filter Categories (Neat horizontal scroll bar with stable height) -->
    <div
      class="px-4 py-2.5 bg-neutral-50/60 dark:bg-[#110624] border-b border-neutral-100 dark:border-[#261545] flex items-center gap-1.5 overflow-x-auto no-scrollbar shrink-0">
      <span
        class="text-[11px] text-neutral-400 dark:text-neutral-500 font-bold uppercase tracking-wider mr-1 shrink-0 flex items-center gap-1">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <polygon points="12 2 2 7 12 12 22 7 12 2" />
          <polyline points="2 17 12 22 22 17" />
          <polyline points="2 12 12 17 22 12" />
        </svg>
        BÖLMƏ:
      </span>
      <div id="search-category-tabs" class="flex items-center gap-1.5">
        <button type="button" data-category="ALL"
          class="search-cat-tab px-3 py-1 rounded-lg text-xs tracking-wide transition-all whitespace-nowrap shrink-0 cursor-pointer bg-[#fcdb56] text-[#080117] font-semibold shadow-xs">
          Hamısı
        </button>
        <?php foreach ($footerCategories as $fCat): ?>
          <button type="button" data-category="<?= e($fCat['slug']) ?>"
            class="search-cat-tab px-3 py-1 rounded-lg text-xs tracking-wide transition-all whitespace-nowrap shrink-0 cursor-pointer bg-white dark:bg-[#190b34] hover:bg-neutral-200/80 dark:hover:bg-[#25104c] text-neutral-700 dark:text-neutral-200 font-medium border border-neutral-200/80 dark:border-[#321860]">
            <?= e($fCat['label']) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 3. Results List (Scrollable news list) -->
    <div id="search-modal-results" class="flex-1 overflow-y-auto min-h-0 p-4 space-y-2.5 bg-white dark:bg-[#0e041d]">
      <!-- Filled dynamically by main.js -->
    </div>

    <!-- 4. Search Footer -->
    <div
      class="p-3 bg-neutral-50 dark:bg-[#140829] border-t border-neutral-200 dark:border-[#261545] flex items-center justify-between text-[11px] text-neutral-500 dark:text-neutral-400 shrink-0">
      <span id="search-modal-count" class="font-medium text-neutral-600 dark:text-neutral-300">0 material tapıldı</span>
      <span class="font-mono text-neutral-400 dark:text-neutral-500">ESC ilə bağla</span>
    </div>
  </div>
</div>

<!-- 2. Bookmarks Drawer (Slide-in right) -->
<div id="bookmarks-drawer-backdrop"
  class="fixed inset-0 z-50 overflow-hidden bg-[#080117]/70 backdrop-blur-xs flex justify-end" style="display:none">
  <div
    class="w-full max-w-md bg-white dark:bg-[#0e041d] text-neutral-900 dark:text-neutral-100 h-full shadow-2xl flex flex-col border-l border-neutral-200 dark:border-[#261545] animate-slide-in-right">
    <!-- Drawer Header -->
    <div
      class="p-4 sm:p-5 border-b border-neutral-200 dark:border-[#261545] flex items-center justify-between bg-neutral-50 dark:bg-[#140829]">
      <div class="flex items-center gap-2">
        <div class="p-2 rounded-lg bg-[#fcdb56]/30 text-[#080117] dark:text-[#fcdb56]">
          <svg class="w-4 h-4 fill-[#080117] dark:fill-[#fcdb56]" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2" fill="none">
            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
          </svg>
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
        <button id="bookmarks-clear-all-btn" type="button"
          class="p-2 rounded-lg text-neutral-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors text-xs flex items-center gap-1 cursor-pointer"
          title="Hamısını sil">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3 6 5 6 21 6" />
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
          </svg>
        </button>
        <button id="bookmarks-close-btn" type="button"
          class="p-2 rounded-lg bg-neutral-200/70 dark:bg-[#1f0f3d] hover:bg-neutral-300 dark:hover:bg-[#2c1655] text-neutral-700 dark:text-neutral-200 transition-colors cursor-pointer">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Drawer Items List -->
    <div id="bookmarks-list-container" class="flex-1 overflow-y-auto p-4 space-y-3">
      <!-- Filled dynamically by main.js -->
    </div>
  </div>
</div>

<!-- 3. Video Modal Player (Supports Landscape & Shorts Formats) -->
<div id="video-player-modal"
  class="fixed inset-0 bg-[#080117]/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-6"
  style="display:none; z-index: 999999;">
  <div id="video-modal-dialog"
    class="w-full max-w-4xl bg-[#080117] rounded-2xl overflow-hidden shadow-2xl border border-[#261545] relative animate-fade-in transition-all duration-300">
    <!-- Video Modal Top Header -->
    <div class="flex items-center justify-between px-3.5 py-2.5 sm:px-5 sm:py-3 border-b border-[#261545] bg-[#0c0322]/95 backdrop-blur">
      <div class="flex items-center gap-2.5 min-w-0 pr-2">
        <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse flex-shrink-0"></span>
        <span id="video-modal-type-badge" class="text-xs font-mono font-bold uppercase tracking-wider text-[#fcdb56] truncate">
          YouTube Video
        </span>
      </div>

      <div class="flex items-center gap-2 flex-shrink-0">
        <a id="video-modal-yt-btn" href="#" target="_blank" rel="noopener noreferrer"
          class="hidden items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-red-600/90 hover:bg-red-600 text-white font-bold text-xs transition-colors shadow-sm"
          title="YouTube-da bax">
          <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
          </svg>
          <span class="hidden sm:inline">YouTube</span>
        </a>

        <button id="video-modal-close-btn" type="button"
          class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#fcdb56] hover:bg-[#fcdb56]/90 text-[#080117] font-bold text-xs shadow-md transition-transform active:scale-95 cursor-pointer"
          title="Bağla (Esc)">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
          </svg>
          <span class="hidden sm:inline font-bold">Bağla</span>
        </button>
      </div>
    </div>

    <!-- Video Embed Frame -->
    <div id="video-embed-container" class="relative aspect-video w-full bg-black flex items-center justify-center overflow-hidden">
      <iframe id="video-player-iframe" src="" title="Video" class="absolute inset-0 w-full h-full border-0"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        referrerpolicy="strict-origin-when-cross-origin"
        allowfullscreen></iframe>
      <video id="video-player-native" controls playsinline class="absolute inset-0 w-full h-full object-contain hidden"></video>
    </div>

    <!-- Video Info -->
    <div id="video-info-container" class="p-3.5 sm:p-4 bg-[#0c0322] border-t border-[#261545]">
      <h2 id="video-player-title" class="text-sm sm:text-base font-semibold text-white leading-snug line-clamp-2"></h2>
      <p id="video-player-subtitle" class="text-xs text-neutral-400 mt-1 line-clamp-2 hidden"></p>
    </div>
  </div>
</div>

<!-- 4. Floating Toast Notification -->
<div id="app-toast-notification"
  class="fixed bottom-5 right-5 z-50 px-4 py-3 bg-[#080117] text-white border border-[#fcdb56]/80 rounded-xl shadow-2xl flex items-center gap-2.5 text-xs font-semibold"
  style="display:none">
  <span class="w-2 h-2 rounded-full bg-[#fcdb56] animate-ping"></span>
  <span id="app-toast-text">Məlumat yeniləndi</span>
</div>

<!-- =========================================================================
     SITE FOOTER
     ========================================================================= -->
<footer id="main-footer"
  class="w-full bg-[#FFFDF5] dark:bg-[#080117] border-t border-neutral-300 dark:border-[#261545] text-neutral-900 dark:text-neutral-100 pt-12 pb-8 mt-auto">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 pb-10 border-b border-neutral-300/80 dark:border-[#261545]">

      <!-- Col 1: Brand Info & Newsletter (5 cols) -->
      <div class="lg:col-span-5 space-y-4">
        <div class="flex items-center gap-2">
          <a href="/" class="hover:opacity-85 transition-opacity select-none" title="ALT404 - Əsas səhifə">
            <img src="/assets/img/logo-dark.png" alt="ALT404"
              class="h-8 sm:h-9 w-auto object-contain select-none dark:hidden" loading="lazy">
            <img src="/assets/img/logo.png" alt="ALT404"
              class="h-8 sm:h-9 w-auto object-contain select-none hidden dark:block" loading="lazy">
          </a>
        </div>

        <p class="text-sm text-neutral-600 dark:text-neutral-400 max-w-sm leading-relaxed">
          Azərbaycanın peşəkar texnologiya mediası — sürətli, dərin və minimal | Xəbərlər, analitik icmallar və
          eksklüziv layihələr
        </p>

        <!-- Newsletter Subscription -->
        <div class="pt-2 max-w-md">
          <label for="newsletter-email"
            class="block text-xs font-semibold uppercase tracking-wider text-neutral-800 dark:text-neutral-200 mb-2">
            Həftəlik Texno-Bülletenə Qoşul
          </label>
          <div id="newsletter-success-box"
            class="items-center gap-2 p-3 bg-[#fcdb56]/20 text-[#080117] dark:text-[#fcdb56] border border-[#fcdb56] rounded-lg text-xs font-medium"
            style="display:none">
            <svg class="w-4 h-4 text-[#080117] dark:text-[#fcdb56] shrink-0" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
              <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            <span>Təşəkkürlər! Ən son texnoloji xülasələr e-poçtunuza göndəriləcək.</span>
          </div>
          <form id="newsletter-form" class="flex items-center gap-2">
            <input id="newsletter-email" type="email" required placeholder="E-poçt ünvanınız..."
              class="flex-1 px-3.5 py-2 text-xs rounded-lg border border-neutral-300 dark:border-[#261545] bg-white dark:bg-[#120726] text-neutral-900 dark:text-white placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-[#fcdb56]" />
            <button id="newsletter-submit-btn" type="submit"
              class="px-4 py-2 rounded-lg bg-[#fcdb56] text-[#080117] font-semibold text-xs uppercase tracking-wider hover:bg-[#fcdb56]/90 transition-colors flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer">
              <span>ABUNƏ OL</span>
              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13" />
                <polygon points="22 2 15 22 11 13 2 9 22 2" />
              </svg>
            </button>
          </form>
        </div>

        <!-- Social Media Links (Loaded from settings; hidden if empty) -->
        <?php
        $socialLinks = [
          'instagram' => [
            'url' => trim(getSettingValue('instagram_link', '')),
            'title' => 'Instagram',
            'icon' => '<i class="fa-brands fa-instagram text-[15px]"></i>'
          ],
          'facebook' => [
            'url' => trim(getSettingValue('facebook_link', '')),
            'title' => 'Facebook',
            'icon' => '<i class="fa-brands fa-facebook-f text-[15px]"></i>'
          ],
          'x' => [
            'url' => trim(getSettingValue('twitter_link', '')),
            'title' => 'X (Twitter)',
            'icon' => '<i class="fa-brands fa-x-twitter text-[15px]"></i>'
          ],
          'youtube' => [
            'url' => trim(getSettingValue('youtube_link', '')),
            'title' => 'YouTube',
            'icon' => '<i class="fa-brands fa-youtube text-[15px]"></i>'
          ],
          'tiktok' => [
            'url' => trim(getSettingValue('tiktok_link', '')),
            'title' => 'TikTok',
            'icon' => '<i class="fa-brands fa-tiktok text-[15px]"></i>'
          ],
          'telegram' => [
            'url' => trim(getSettingValue('telegram_link', '')),
            'title' => 'Telegram',
            'icon' => '<i class="fa-brands fa-telegram text-[15px]"></i>'
          ],
          'linkedin' => [
            'url' => trim(getSettingValue('linkedin_link', '')),
            'title' => 'LinkedIn',
            'icon' => '<i class="fa-brands fa-linkedin-in text-[15px]"></i>'
          ],
        ];
        $activeSocialLinks = array_filter($socialLinks, fn($item) => !empty($item['url']));
        ?>

        <?php if (!empty($activeSocialLinks)): ?>
          <div class="pt-3">
            <span
              class="block text-[11px] font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-2.5">
              Bizi İzləyin
            </span>
            <div class="flex flex-wrap items-center gap-2">
              <?php foreach ($activeSocialLinks as $platform => $s): ?>
                <a id="footer-social-<?= e($platform) ?>" href="<?= e($s['url']) ?>" target="_blank" rel="noreferrer"
                  title="<?= e($s['title']) ?>" aria-label="<?= e($s['title']) ?>"
                  class="w-9 h-9 flex items-center justify-center rounded-lg bg-neutral-100 dark:bg-[#15082d] text-neutral-700 dark:text-neutral-200 hover:bg-[#fcdb56] dark:hover:bg-[#fcdb56] hover:text-[#080117] dark:hover:text-[#080117] hover:border-[#fcdb56] hover:scale-105 active:scale-95 transition-all duration-200 border border-neutral-200 dark:border-[#281648] shadow-xs cursor-pointer">
                  <?= $s['icon'] ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Col 2: Bölmələr (3 cols) -->
      <div class="lg:col-span-3">
        <h4
          class="text-xs font-semibold uppercase tracking-wider text-neutral-950 dark:text-white mb-3.5 flex items-center gap-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-[#fcdb56] shadow-[0_0_6px_rgba(252,219,86,0.8)]"></span>
          Bölmələr
        </h4>
        <ul class="space-y-2 text-xs text-neutral-700 dark:text-neutral-300">
          <li>
            <a href="/"
              class="hover:text-[#080117] dark:hover:text-[#fcdb56] hover:translate-x-0.5 transition-all font-medium flex items-center gap-1.5">
              <span class="text-neutral-400 dark:text-neutral-600 text-[10px]">›</span>
              <span>ANA SƏHİFƏ</span>
            </a>
          </li>
          <?php foreach ($footerCategories as $fCat): ?>
            <li>
              <a href="/<?= e($fCat['slug']) ?>"
                class="hover:text-[#080117] dark:hover:text-[#fcdb56] hover:translate-x-0.5 transition-all font-medium flex items-center gap-1.5">
                <span class="text-neutral-400 dark:text-neutral-600 text-[10px]">›</span>
                <span><?= e(mb_strtoupper_az($fCat['label'])) ?></span>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Col 3: Şirkət (2 cols) -->
      <div class="lg:col-span-2 space-y-3">
        <h4
          class="text-xs font-semibold uppercase tracking-wider text-neutral-950 dark:text-white mb-3.5 flex items-center gap-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-[#fcdb56] shadow-[0_0_6px_rgba(252,219,86,0.8)]"></span>
          Şirkət
        </h4>
        <ul class="space-y-2.5 text-xs text-neutral-700 dark:text-neutral-300 font-medium">
          <li>
            <a href="/haqqimizda"
              class="hover:text-[#080117] dark:hover:text-[#fcdb56] transition-colors flex items-center gap-1.5">
              <span class="text-neutral-400 dark:text-neutral-600 text-[10px]">›</span>
              <span>Haqqımızda</span>
            </a>
          </li>
          <li>
            <a href="/elaqe"
              class="hover:text-[#080117] dark:hover:text-[#fcdb56] transition-colors flex items-center gap-1.5">
              <span class="text-neutral-400 dark:text-neutral-600 text-[10px]">›</span>
              <span>Əlaqə</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- Col 4: Hüquqi (2 cols) -->
      <div class="lg:col-span-2 space-y-3">
        <h4
          class="text-xs font-semibold uppercase tracking-wider text-neutral-950 dark:text-white mb-3.5 flex items-center gap-1.5">
          <span class="w-1.5 h-1.5 rounded-full bg-[#fcdb56] shadow-[0_0_6px_rgba(252,219,86,0.8)]"></span>
          Hüquqi
        </h4>
        <ul class="space-y-2.5 text-xs text-neutral-700 dark:text-neutral-300 font-medium">
          <li>
            <a href="/istifade-sertleri"
              class="hover:text-[#080117] dark:hover:text-[#fcdb56] transition-colors flex items-center gap-1.5">
              <span class="text-neutral-400 dark:text-neutral-600 text-[10px]">›</span>
              <span>İstifadə şərtləri</span>
            </a>
          </li>
          <li>
            <a href="/cerezler"
              class="hover:text-[#080117] dark:hover:text-[#fcdb56] transition-colors flex items-center gap-1.5">
              <span class="text-neutral-400 dark:text-neutral-600 text-[10px]">›</span>
              <span>Çərəzlər</span>
            </a>
          </li>
        </ul>
      </div>

    </div>

    <!-- Bottom Bar with Copyright & Scroll to Top -->
    <div
      class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-neutral-600 dark:text-neutral-400">
      <div class="text-center sm:text-left font-medium">
        © <?= date('Y') ?> ALT404.com | Bütün hüquqlar qorunur
      </div>

      <div class="flex items-center gap-2">
        <button id="scroll-to-top-btn" type="button"
          class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-neutral-200 dark:bg-[#1a0c35] text-neutral-800 dark:text-neutral-200 hover:bg-[#fcdb56] dark:hover:bg-[#fcdb56] hover:text-[#080117] dark:hover:text-[#080117] transition-colors font-medium text-xs shadow-2xs cursor-pointer border border-transparent dark:border-[#261545]"
          title="Yuxarı qayıt">
          <span>Yuxarı</span>
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="19" x2="12" y2="5" />
            <polyline points="5 12 12 5 19 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</footer>

<!-- Interactive Client-side Script -->
<script src="/assets/js/main.js"></script>
</body>

</html>