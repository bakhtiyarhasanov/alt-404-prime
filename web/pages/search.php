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
    <div class="bg-white dark:bg-[#120726] rounded-xl border border-neutral-200/90 dark:border-[#22153e] p-5 sm:p-7 shadow-xs mb-8">
      <div class="flex items-center gap-2.5 mb-4">
        <span class="w-3 h-3 rounded-full bg-[#fcdb56] shadow-[0_0_8px_rgba(252,219,86,0.8)]"></span>
        <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-tight text-[#080117] dark:text-white">
          Portal daxilində axtarış
        </h1>
      </div>

      <form action="/axtar" method="GET" class="flex flex-col sm:flex-row items-center gap-3 max-w-2xl">
        <div class="relative w-full">
          <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input
            type="text"
            name="q"
            value="<?= e($query) ?>"
            placeholder="Xəbər başlığı, mövzu və ya hashtag..."
            class="w-full pl-10 pr-4 py-2.5 rounded-lg bg-neutral-50 dark:bg-[#1a0c33] border border-neutral-200 dark:border-[#2e1952] text-xs text-neutral-900 dark:text-white placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-hidden focus:ring-2 focus:ring-[#fcdb56] focus:bg-white dark:focus:bg-[#15092a] transition-all"
            autofocus
          />
        </div>
        <button
          type="submit"
          class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-[#fcdb56] text-[#080117] text-xs font-bold transition-all shadow-xs hover:brightness-105 shrink-0 cursor-pointer"
        >
          Axtar
        </button>
      </form>

      <?php if (mb_strlen($query) > 1): ?>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-4 font-mono">
          "<?= e($query) ?>" üzrə <span class="text-[#080117] dark:text-[#fcdb56] font-bold"><?= count($results) ?></span> nəticə tapıldı
        </p>
      <?php endif; ?>
    </div>

    <!-- Results Grid -->
    <?php if (mb_strlen($query) > 1): ?>
      <?php if (count($results) === 0): ?>
        <div class="text-center py-16 bg-white dark:bg-[#120726] rounded-xl border border-neutral-200 dark:border-[#22153e] p-8 shadow-xs">
          <p class="text-sm font-semibold text-neutral-600 dark:text-neutral-400">Uyğun nəticə tapılmadı.</p>
          <p class="text-xs text-neutral-400 dark:text-neutral-500 mt-1">Zəhmət olmasa başqa açar sözlərlə yenidən cəhd edin.</p>
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
