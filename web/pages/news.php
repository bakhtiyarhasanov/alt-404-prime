<?php
/**
 * News line page — Modern layout matching the AI Studio design.
 * Lists all articles with pagination.
 */
$isHeroPage = false;
$pageTitle = 'Xəbər lenti — alt404';
$pageDescription = 'Ən son texnologiya, elm və startap xəbərləri. alt404.az';

$limit = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$articles = getPublishedArticles($limit, $offset);
$totalArticles = getTotalPublishedArticlesCount();
$totalPages = (int)ceil($totalArticles / $limit);

$visibleCategories = getVisibleCategories();

require_once __DIR__ . '/../includes/header.php';
?>

<main class="w-full py-6 sm:py-8 min-h-screen">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" class="hidden md:flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400 mb-6 font-medium font-mono">
      <a href="/" class="flex items-center gap-1.5 hover:text-neutral-900 dark:hover:text-white transition-colors">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <span>Ana Səhifə</span>
      </a>
      <svg class="w-3 h-3 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span class="text-[#080117] dark:text-[#fcdb56] font-bold uppercase tracking-wide">Xəbər lenti</span>
    </nav>

    <!-- Header Banner -->
    <div class="hidden md:block bg-white dark:bg-[#120726] rounded-xl border border-neutral-200/90 dark:border-[#22153e] p-5 sm:p-7 shadow-xs mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center flex-wrap gap-2.5 mb-2">
            <span class="w-3 h-3 rounded-full bg-[#fcdb56] shadow-[0_0_8px_rgba(252,219,86,0.8)]"></span>
            <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-tight text-[#080117] dark:text-white flex items-center flex-wrap gap-2">
              <span>Xəbər lenti</span>
            </h1>
          </div>
          <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 max-w-2xl leading-relaxed">
            Texnologiya, süni intellekt, elm, kosmos, avtomobil və innovasiyalar üzrə ən son operativ xəbər axını.
          </p>
        </div>

        <!-- Quick Actions -->
        <div class="hidden md:flex items-center gap-2 self-start md:self-auto">
          <a href="/" class="px-3.5 py-2 rounded-lg bg-neutral-100 dark:bg-[#1f0f3d] hover:bg-neutral-200 dark:hover:bg-[#2c1655] text-neutral-800 dark:text-neutral-100 text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer">
            <span>← Ana Səhifə</span>
          </a>
        </div>
      </div>
      

    </div>

    <!-- Articles Grid -->
    <?php if (count($articles) === 0): ?>
      <div class="text-center py-16 bg-white dark:bg-[#120726] rounded-xl border border-neutral-200 dark:border-[#22153e] p-8 shadow-xs">
        <div class="w-12 h-12 rounded-full bg-neutral-100 dark:bg-[#1c0d38] flex items-center justify-center mx-auto mb-3 text-neutral-400 dark:text-neutral-500">
          <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1">
          Heç bir material tapılmadı
        </h3>
        <p class="text-sm font-semibold text-neutral-600 dark:text-neutral-400">Hələ heç bir xəbər yoxdur.</p>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php 
        $inlineAd = getAd('inline');
        $showAd = !empty($inlineAd) && !empty($inlineAd['enabled']) && !empty($inlineAd['image_url']);
        $count = 0;
        foreach ($articles as $a): 
          $count++;
          include __DIR__ . '/_article_card.php';
          
          if ($showAd && $count % 8 === 0 && $count < count($articles)):
        ?>
          <div class="col-span-full my-4">
            <aside class="relative block w-full rounded-xl overflow-hidden border border-neutral-200/80 dark:border-[#261545] shadow-2xs hover:shadow-xs hover:border-[#fcdb56]/80 transition-all">
              <a href="<?= e($inlineAd['link_url'] ?: '#') ?>" target="_blank" rel="noopener noreferrer" class="block w-full h-auto cursor-pointer relative">
                <img src="<?= e($inlineAd['image_url']) ?>" alt="Reklam Banneri" class="w-full h-auto max-h-[200px] object-cover block" loading="lazy" />
                <span class="ad-label">Reklam</span>
              </a>
            </aside>
          </div>
        <?php 
          endif;
        endforeach; 
        ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-center gap-3 mt-12">
          <?php if ($page > 1): ?>
            <a href="/xeberler?page=<?= $page - 1 ?>" class="px-4 py-2 rounded-lg bg-neutral-100 dark:bg-[#1c0d38] hover:bg-neutral-200 dark:hover:bg-[#2c1655] text-neutral-800 dark:text-neutral-200 text-xs font-bold transition-colors flex items-center gap-1.5">
              ← Əvvəlki
            </a>
          <?php endif; ?>

          <span class="text-xs text-neutral-500 dark:text-neutral-400 font-mono px-3">
            Səhifə <?= $page ?> / <?= $totalPages ?>
          </span>

          <?php if ($page < $totalPages): ?>
            <a href="/xeberler?page=<?= $page + 1 ?>" class="px-4 py-2 rounded-lg bg-neutral-100 dark:bg-[#1c0d38] hover:bg-neutral-200 dark:hover:bg-[#2c1655] text-neutral-800 dark:text-neutral-200 text-xs font-bold transition-colors flex items-center gap-1.5">
              Sonrakı →
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
