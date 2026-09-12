<?php
/**
 * Search page — Modern layout matching the AI Studio design.
 */
$isHeroPage = false;
$pageTitle = 'Axtarış | alt404';
$pageDescription = 'alt404.az-da xəbər axtar.';
$query = trim($_GET['q'] ?? '');
$results = [];
if (mb_strlen($query) > 1) {
    $results = searchArticles($query);
}
require_once __DIR__ . '/../includes/header.php';
?>

<main class="w-full py-6 sm:py-8 min-h-screen">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400 mb-6 font-medium font-mono">
      <a href="/" class="flex items-center gap-1.5 hover:text-neutral-900 dark:hover:text-white transition-colors">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <span>Ana Səhifə</span>
      </a>
      <svg class="w-3 h-3 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span class="text-[#080117] dark:text-[#fcdb56] font-bold uppercase tracking-wide">Axtarış</span>
    </nav>

    <!-- Search Box Banner -->
    <div class="bg-white dark:bg-[#120726] rounded-2xl border border-neutral-200/90 dark:border-[#22153e] p-6 sm:p-9 shadow-xs mb-8 transition-colors">
      <div class="max-w-3xl">
        <div class="flex items-center gap-2.5 mb-2">
          <span class="w-2.5 h-2.5 rounded-full bg-[#fcdb56] shadow-[0_0_8px_rgba(252,219,86,0.8)] animate-pulse"></span>
          <span class="text-xs font-bold uppercase tracking-widest text-[#080117] dark:text-[#fcdb56] font-mono">
            Axtarış Mərkəzi
          </span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#080117] dark:text-white mb-2">
          Portal daxilində axtarış
        </h1>
        <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 mb-6">
          ALT404 arxivindəki xəbərlər, analitik icmallar və layihələr üzrə sürətli axtarış aparın.
        </p>

        <!-- Modern Unified Search Input Bar -->
        <form action="/axtar" method="GET" class="relative group">
          <div class="flex items-center rounded-xl bg-neutral-50 dark:bg-[#160a2e] border-2 border-neutral-200 dark:border-[#2a174d] hover:border-neutral-300 dark:hover:border-[#3d216d] focus-within:border-[#fcdb56] dark:focus-within:border-[#fcdb56] focus-within:ring-4 focus-within:ring-[#fcdb56]/15 shadow-sm transition-all p-1.5 sm:p-2 gap-2">
            
            <!-- Left Search Icon -->
            <div class="pl-2.5 sm:pl-3 text-neutral-400 dark:text-neutral-500 group-focus-within:text-[#080117] dark:group-focus-within:text-[#fcdb56] transition-colors shrink-0">
              <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
            </div>

            <!-- Input Field -->
            <input
              type="text"
              name="q"
              id="search-page-input"
              value="<?= e($query) ?>"
              placeholder="Xəbər başlığı, mövzu, texnologiya və ya hashtag axtarın..."
              class="flex-1 bg-transparent py-2.5 sm:py-3 px-2 text-sm sm:text-base text-neutral-900 dark:text-white placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-none font-medium"
              autofocus
            />

            <!-- Clear button (if query exists) -->
            <?php if (!empty($query)): ?>
              <a
                href="/axtar"
                class="p-2 rounded-lg text-neutral-400 hover:text-neutral-700 dark:hover:text-white hover:bg-neutral-200/60 dark:hover:bg-[#251249] transition-colors shrink-0"
                title="Təmizlə"
                aria-label="Təmizlə"
              >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </a>
            <?php endif; ?>

            <!-- Submit Button -->
            <button
              type="submit"
              class="search-page-btn px-8 sm:px-10 py-3 sm:py-3.5 rounded-xl bg-[#fcdb56] hover:bg-[#ffe57a] text-[#080117] text-xs sm:text-sm font-bold uppercase tracking-wider transition-all shadow-xs hover:shadow-md active:scale-95 shrink-0 cursor-pointer flex items-center justify-center gap-2.5"
              style="padding-left: 36px; padding-right: 36px;"
            >
              <span>Axtar</span>
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/>
                <polyline points="12 5 19 12 12 19"/>
              </svg>
            </button>
          </div>
        </form>

        <!-- Quick Trending Tags Suggestions under the Search Bar -->
        <?php 
        $popularTags = getTopTags(7, 6);
        if (!empty($popularTags)): 
        ?>
          <div class="flex items-center flex-wrap gap-2 mt-4 pt-4 border-t border-neutral-100 dark:border-[#22153e]/80 text-xs">
            <span class="text-neutral-500 dark:text-neutral-400 font-mono text-[11px] uppercase tracking-wider flex items-center gap-1 shrink-0">
              <svg class="w-3.5 h-3.5 text-[#fcdb56]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
              Populyar teqlər:
            </span>
            <div class="flex items-center flex-wrap gap-1.5">
              <?php foreach ($popularTags as $pTag): ?>
                <?php 
                  $cleanTag = str_replace('#', '', $pTag);
                  $isThisTag = (mb_strtolower($query, 'UTF-8') === mb_strtolower($cleanTag, 'UTF-8'));
                ?>
                <a
                  href="/axtar?q=<?= urlencode($cleanTag) ?>"
                  class="px-2.5 py-1 rounded-md text-[11px] font-medium transition-all <?= $isThisTag ? 'bg-[#fcdb56] text-[#080117] font-bold' : 'bg-neutral-100 hover:bg-neutral-200 dark:bg-[#1a0c33] dark:hover:bg-[#251249] text-neutral-700 dark:text-neutral-300 hover:text-[#080117] dark:hover:text-[#fcdb56] border border-neutral-200/80 dark:border-[#2a174d]' ?>"
                >
                  <?= e($pTag) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if (mb_strlen($query) > 1): ?>
          <div class="mt-4 flex items-center gap-2 text-xs font-mono text-neutral-500 dark:text-neutral-400">
            <span class="w-1.5 h-1.5 rounded-full bg-neutral-400 dark:bg-neutral-500"></span>
            <span>"<?= e($query) ?>" axtarış sorğusu üzrə tapılan material sayı:</span>
            <span class="px-2 py-0.5 rounded bg-[#fcdb56]/20 text-[#080117] dark:text-[#fcdb56] font-bold text-xs border border-[#fcdb56]/30"><?= count($results) ?></span>
          </div>
        <?php endif; ?>

      </div>
    </div>

    <!-- Results Grid -->
    <?php if (mb_strlen($query) > 1): ?>
      <?php if (count($results) === 0): ?>
        <div class="text-center py-16 bg-white dark:bg-[#120726] rounded-2xl border border-neutral-200/90 dark:border-[#22153e] p-8 shadow-xs max-w-xl mx-auto">
          <div class="w-14 h-14 rounded-full bg-neutral-100 dark:bg-[#1a0c33] text-neutral-400 dark:text-neutral-500 flex items-center justify-center mx-auto mb-4 border border-neutral-200/80 dark:border-[#2a174d]">
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
              <line x1="8" y1="11" x2="14" y2="11"/>
            </svg>
          </div>
          <h3 class="text-base sm:text-lg font-bold text-neutral-800 dark:text-neutral-100 mb-1">Uyğun nəticə tapılmadı</h3>
          <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400 max-w-sm mx-auto leading-relaxed">
            "<?= e($query) ?>" sorğusu üzrə heç bir material aşkar edilmədi. Başqa açar sözlərlə və ya yuxarıdakı populyar teqlərlə axtarış edə bilərsiniz.
          </p>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          <?php foreach ($results as $a): ?>
            <?php include __DIR__ . '/_article_card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
