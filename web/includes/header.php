<?php
/**
 * Shared header include for ALT404 Prime.
 * Variables expected: $pageTitle (optional), $pageDescription (optional), $categorySlug (optional)
 */
$pageTitle = $pageTitle ?? 'ALT404 | Azərbaycanın Peşəkar Texnologiya Mediası';
$pageDescription = $pageDescription ?? 'ALT404 - Azərbaycanın peşəkar texnologiya mediası — sürətli, dərin və minimal | Xəbərlər, analitik icmallar və eksklüziv layihələr';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentPath = rtrim($currentPath, '/') ?: '/';
$activeCategorySlug = $categorySlug ?? null;
$activeTag = isset($_GET['tag']) ? trim($_GET['tag']) : null;

// Categories for navigation
$navCategories = getVisibleCategories();

// Fetch articles for ticker & client-side live search / bookmarks
$publishedArticles = getPublishedArticles(80);
$tickerArticles = array_slice($publishedArticles, 0, 10);

// Rank top tags from published articles
$tagCounts = [];
foreach ($publishedArticles as $pa) {
    if (!empty($pa['tags']) && is_array($pa['tags'])) {
        foreach ($pa['tags'] as $t) {
            $cleaned = trim($t);
            if ($cleaned === '') continue;
            $formatted = strpos($cleaned, '#') === 0 ? $cleaned : ('#' . $cleaned);
            $lower = mb_strtolower($formatted, 'UTF-8');
            $tagCounts[$lower] = ($tagCounts[$lower] ?? 0) + 1;
        }
    }
}
arsort($tagCounts);
$topRankedTags = array_slice(array_keys($tagCounts), 0, 12);

// Light JSON articles for client search and bookmarks drawer
$clientArticlesJson = json_encode(array_map(function($a) {
    $cleanTags = array_map(function($t) {
        $t = trim($t);
        return strpos($t, '#') === 0 ? $t : ('#' . $t);
    }, $a['tags'] ?? []);
    
    return [
        'id' => (string)$a['id'],
        'title' => $a['title'],
        'slug' => $a['slug'],
        'category' => $a['category'],
        'category_label' => getCategoryLabel($a['category']),
        'excerpt' => $a['excerpt'] ?? '',
        'image_url' => $a['image_url'] ?? '',
        'reading_time' => (int)($a['reading_time'] ?? 3) . ' dəq',
        'date' => formatDateAz($a['created_at']),
        'tags' => $cleanTags,
    ];
}, $publishedArticles), JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="az">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Tomorrow:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">

  <!-- Immediate Theme Check to avoid FOUC -->
  <script>
    (function() {
      try {
        var stored = localStorage.getItem('alt404_theme');
        if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      } catch (e) {}
    })();
  </script>

  <!-- Client-side Articles Database -->
  <script>
    window.__articles = <?= !empty($clientArticlesJson) ? $clientArticlesJson : '[]' ?>;
  </script>
</head>
<body class="bg-[#FAF8F5] text-neutral-900 dark:bg-[#080117] dark:text-neutral-100 font-sans antialiased selection:bg-[#fcdb56] selection:text-[#080117] transition-colors duration-300 min-h-screen flex flex-col">

<!-- Top Sticky Bar: Ticker + Main Header + Category Navigation -->
<div class="sticky top-0 z-40 w-full shadow-xs">

  <!-- 1. Top Breaking News Ticker (Canlı Xətt) -->
  <div id="live-news-ticker" class="bg-[#080117] text-neutral-100 text-xs py-1.5 px-4 border-b border-[#22153e] flex items-center overflow-hidden">
    <div class="flex items-center gap-2 shrink-0 z-10 bg-[#080117] pr-3 shadow-[10px_0_12px_#080117]">
      <span class="flex items-center gap-1.5 font-bold uppercase tracking-wider text-[#fcdb56] text-[11px]">
        <span class="w-2 h-2 rounded-full bg-[#fcdb56] animate-pulse"></span>
        Canlı Xətt:
      </span>
    </div>

    <!-- Marquee Ticker Stream -->
    <div class="flex-1 overflow-hidden relative group">
      <div class="animate-ticker-marquee flex items-center gap-6 text-[11px]">
        <?php foreach ([$tickerArticles, $tickerArticles] as $loopSet): ?>
          <?php foreach ($loopSet as $tArt): ?>
            <?php $tCatLabel = getCategoryLabel($tArt['category']); ?>
            <a href="/<?= e($tArt['category']) ?>/<?= e($tArt['slug']) ?>" class="inline-flex items-center gap-2 text-neutral-200 hover:text-[#fcdb56] transition-colors cursor-pointer text-left whitespace-nowrap group/item">
              <span class="px-1.5 py-0.5 rounded-xs bg-[#fcdb56]/20 text-[#fcdb56] font-mono text-[9px] font-bold uppercase border border-[#fcdb56]/30">
                <?= e(preg_replace('/\s+Xəbərləri$/iu', '', $tCatLabel)) ?>
              </span>
              <span class="font-medium group-hover/item:underline underline-offset-2">
                <?= e($tArt['title']) ?>
              </span>
              <span class="text-neutral-500 font-bold ml-2">•</span>
            </a>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- 2. Main Header Bar -->
  <header id="main-header" class="w-full border-b transition-colors duration-200 backdrop-blur-md bg-white/95 border-neutral-200 text-neutral-900 dark:bg-[#080117]/95 dark:border-[#22153e] dark:text-neutral-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between gap-2.5 sm:gap-4">
      
      <!-- Left: Logo (Retaining existing logos with light/dark adaptive display) -->
      <div class="flex items-center shrink-0">
        <a href="/" id="brand-logo-link" class="group flex items-center hover:opacity-85 transition-opacity select-none py-1" title="ALT404 - Əsas səhifə">
          <img src="/assets/img/logo-dark.png" alt="ALT404" class="h-8 sm:h-9 w-auto object-contain select-none dark:hidden" loading="eager">
          <img src="/assets/img/logo.png" alt="ALT404" class="h-8 sm:h-9 w-auto object-contain select-none hidden dark:block" loading="eager">
        </a>
      </div>

      <?php if (!empty($topRankedTags)): ?>
      <!-- Left Scroll Button for Hashtags -->
      <button
        id="scroll-tags-left-btn"
        type="button"
        class="w-9 h-9 hidden md:flex items-center justify-center rounded-lg border transition-all text-xs font-medium cursor-pointer shrink-0 border-neutral-200 bg-neutral-100/90 text-neutral-700 hover:text-black hover:border-neutral-300 hover:bg-neutral-200/80 dark:border-[#2a1a4a] dark:bg-[#130728] dark:text-neutral-200 dark:hover:text-[#fcdb56] dark:hover:border-[#fcdb56]/40 dark:hover:bg-[#1e0d3d]"
        title="Sola sürüşdür"
        aria-label="Sola sürüşdür"
      >
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>

      <!-- Center: Dynamically Ranked Hashtags Bar with infinite scroll wrap -->
      <div
        id="tag-scroll-container"
        class="hidden md:flex items-center flex-1 overflow-x-hidden scroll-smooth h-9 py-1 text-xs gap-1.5 min-w-0 select-none"
      >
        <?php if ($activeTag): ?>
          <a
            id="clear-tag-btn"
            href="<?= $currentPath === '/' ? '/' : $currentPath ?>"
            class="px-2.5 py-1 rounded bg-[#fcdb56] text-[#080117] font-bold text-xs whitespace-nowrap flex items-center gap-1.5 shadow-xs shrink-0"
            title="Teq filtrini təmizlə"
          >
            <span><?= e($activeTag) ?></span>
            <span class="text-xs">✕</span>
          </a>
        <?php endif; ?>

        <?php 
        // Render tags duplicated for smooth loop
        $tagsLoop = array_merge($topRankedTags, $topRankedTags, $topRankedTags);
        foreach ($tagsLoop as $idx => $tag): 
          $isCurrentTag = ($activeTag && strtolower(str_replace('#', '', $activeTag)) === strtolower(str_replace('#', '', $tag)));
          $tagUrl = '/?tag=' . urlencode(str_replace('#', '', $tag));
        ?>
          <a
            href="<?= e($tagUrl) ?>"
            class="px-2.5 py-1 rounded text-xs tracking-tight whitespace-nowrap transition-all duration-150 shrink-0 font-medium cursor-pointer <?= $isCurrentTag ? 'bg-[#fcdb56] text-[#080117] font-bold ring-1 ring-[#fcdb56] shadow-xs' : 'text-neutral-600 hover:text-[#080117] hover:bg-[#fcdb56]/20 bg-neutral-100/70 border border-neutral-200/60 dark:text-neutral-300 dark:hover:text-[#fcdb56] dark:hover:bg-[#1a0f35] dark:bg-[#120726] dark:border-[#2a1a4a]' ?>"
          >
            <?= e($tag) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Right Scroll Button for Hashtags -->
      <button
        id="scroll-tags-right-btn"
        type="button"
        class="w-9 h-9 hidden md:flex items-center justify-center rounded-lg border transition-all text-xs font-medium cursor-pointer shrink-0 border-neutral-200 bg-neutral-100/90 text-neutral-700 hover:text-black hover:border-neutral-300 hover:bg-neutral-200/80 dark:border-[#2a1a4a] dark:bg-[#130728] dark:text-neutral-200 dark:hover:text-[#fcdb56] dark:hover:border-[#fcdb56]/40 dark:hover:bg-[#1e0d3d]"
        title="Sağa sürüşdür"
        aria-label="Sağa sürüşdür"
      >
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
      <?php else: ?>
      <div class="flex-1"></div>
      <?php endif; ?>

      <!-- Right: Action Buttons (Search Square, Bookmarks, Theme Toggle) -->
      <div class="flex items-center gap-2 shrink-0">
        
        <!-- Search Square Button -->
        <button
          id="open-search-btn"
          type="button"
          class="w-9 h-9 flex items-center justify-center rounded-lg border transition-all text-xs font-medium cursor-pointer border-neutral-200 bg-neutral-100/90 text-neutral-700 hover:text-black hover:border-neutral-300 hover:bg-neutral-200/80 dark:border-[#2a1a4a] dark:bg-[#130728] dark:text-neutral-200 dark:hover:text-[#fcdb56] dark:hover:border-[#fcdb56]/40 dark:hover:bg-[#1e0d3d]"
          title="Axtarış (Cmd+K)"
          aria-label="Axtarış"
        >
          <svg class="w-4 h-4 text-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </button>

        <!-- Bookmarks Button -->
        <button
          id="open-bookmarks-btn"
          type="button"
          class="relative w-9 h-9 flex items-center justify-center rounded-lg border transition-all cursor-pointer border-neutral-200 bg-neutral-100/90 text-neutral-700 hover:text-black hover:border-neutral-300 hover:bg-[#fcdb56]/20 dark:border-[#2a1a4a] dark:bg-[#130728] dark:text-neutral-200 dark:hover:text-[#fcdb56] dark:hover:border-[#fcdb56]/40 dark:hover:bg-[#1e0d3d]"
          title="Saxlanılan məqalələr"
          aria-label="Saxlanılan məqalələr"
        >
          <svg class="w-4 h-4 text-current" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
          <span
            id="bookmarks-counter-badge"
            class="hidden absolute -top-1 -right-1 w-4 h-4 bg-[#fcdb56] text-[#080117] text-[10px] font-bold rounded-full items-center justify-center shadow-xs"
          >
            0
          </span>
        </button>

        <!-- Theme Toggle Button -->
        <button
          id="theme-toggle-btn"
          type="button"
          class="w-9 h-9 flex items-center justify-center rounded-lg border transition-all cursor-pointer border-neutral-200 bg-neutral-100/90 text-neutral-700 hover:text-black hover:border-neutral-300 hover:bg-[#fcdb56]/20 dark:border-[#2a1a4a] dark:bg-[#130728] dark:text-neutral-200 dark:hover:text-[#fcdb56] dark:hover:border-[#fcdb56]/40 dark:hover:bg-[#1e0d3d]"
          title="Rejimi dəyişdir"
          aria-label="Rejimi dəyişdir"
        >
          <!-- Sun icon (Light mode) -->
          <svg class="theme-sun-icon w-4 h-4 text-[#080117] dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
          <!-- Moon icon (Dark mode) -->
          <svg class="theme-moon-icon w-4 h-4 text-[#fcdb56] hidden dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>
      </div>

    </div>
  </header>

  <!-- 3. Category Navigation Bar (Sticky under header) -->
  <nav
    id="category-navigation"
    aria-label="Əsas kateqoriyalar"
    class="w-full border-b transition-colors duration-200 backdrop-blur-md bg-[#FFFDF5]/95 border-neutral-200/90 text-neutral-900 shadow-[0_1px_3px_rgba(0,0,0,0.03)] dark:bg-[#0a021c]/95 dark:border-[#22153e] dark:text-neutral-200 dark:shadow-[0_4px_12px_rgba(0,0,0,0.25)]"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center space-x-1 sm:space-x-2 overflow-x-auto no-scrollbar py-2.5">
        <?php 
        $isHome = ($currentPath === '/' || $currentPath === '');
        ?>
        <a
          href="/"
          id="nav-item-home"
          class="px-3 py-1.5 rounded-sm text-xs sm:text-[13px] tracking-wider uppercase transition-all duration-150 whitespace-nowrap shrink-0 cursor-pointer <?= $isHome ? 'bg-[#fcdb56] text-[#080117] shadow-sm ring-1 ring-[#fcdb56] font-bold' : 'text-neutral-800 hover:text-neutral-950 hover:bg-neutral-200/50 dark:text-neutral-300 dark:hover:text-white dark:hover:bg-[#1a0f35] font-medium' ?>"
        >
          ANA SƏHİFƏ
        </a>

        <?php foreach ($navCategories as $cat): ?>
          <?php 
            $isCatActive = ($activeCategorySlug === $cat['slug'] || $currentPath === '/' . $cat['slug']);
            $cleanLabel = preg_replace('/\s+Xəbərləri$/iu', '', $cat['label']);
          ?>
          <a
            href="/<?= e($cat['slug']) ?>"
            id="nav-item-<?= e($cat['slug']) ?>"
            class="px-3 py-1.5 rounded-sm text-xs sm:text-[13px] tracking-wider uppercase transition-all duration-150 whitespace-nowrap shrink-0 cursor-pointer <?= $isCatActive ? 'bg-[#fcdb56] text-[#080117] shadow-sm ring-1 ring-[#fcdb56] font-bold' : 'text-neutral-800 hover:text-neutral-950 hover:bg-neutral-200/50 dark:text-neutral-300 dark:hover:text-white dark:hover:bg-[#1a0f35] font-medium' ?>"
          >
            <?= e($cleanLabel) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </nav>

</div>
