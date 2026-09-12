<?php
/**
 * Category page — Modern layout matching the AI Studio design.
 * Features: Breadcrumbs, category banner with glowing dot, top 20 tags, responsive article grid.
 */
$allCategories = getCategories();
$category = null;
foreach ($allCategories as $c) {
    if ($c['slug'] === $categorySlug) { $category = $c; break; }
}
$label = $category ? $category['label'] : $categorySlug;
$seoTitle = $category['meta_title'] ?? "$label | alt404";
$seoDescription = $category['meta_description'] ?? "$label xəbərləri. alt404.az";

$isHeroPage = false;
$pageTitle = $seoTitle;
$pageDescription = $seoDescription;

$articles = getArticlesByCategorySlugs($categorySlug, 100);
$activeTag = isset($_GET['tag']) ? trim($_GET['tag']) : null;
$categoryAd = getAd('category-banner');

// Collect top 20 tags sorted by count
$tagCounts = [];
foreach ($articles as $a) {
    if (!empty($a['tags'])) {
        foreach ($a['tags'] as $t) {
            $t = trim($t);
            if ($t !== '') {
                $tagCounts[$t] = ($tagCounts[$t] ?? 0) + 1;
            }
        }
    }
}
arsort($tagCounts);
$topTags = array_slice(array_keys($tagCounts), 0, 20);

$filtered = $activeTag
    ? array_values(array_filter($articles, fn($a) => in_array($activeTag, $a['tags'])))
    : $articles;

$categoryDescriptions = [
    'texnologiya'   => 'Qlobal və yerli texnoloji yeniliklər, proqram təminatı, aparat həlləri və sənaye transformasiyaları.',
    'elm'           => 'Kosmik missiyalar, kvant fizikası, biotexnologiya və gələcəyi formalaşdıran elmi kəşflər.',
    'ai'            => 'Neyroşəbəkələr, böyük dil modelləri (LLM), robototexnika və süni intellekt agentlərinin analizi.',
    'oyun'          => 'Oyun sənayesi, kiberidman, növbəti nəsil qrafika mühərrikləri və eksklüziv oyun icmalları.',
    'startap'       => 'Vençur investisiyaları, ekosistem xəbərləri, yerli və beynəlxalq innovativ layihələr, hakatonlar.',
    'avtomobil'     => 'Elektromobillər, avtonom idarəetmə sistemləri və gələcəyin mobillik texnologiyaları.',
    'texnobloq'     => 'Dərin fikirlər, tendensiyalar və texnoloji məsləhətlər.',
    'texnoicmal'    => 'Aparat və cihazların testləri, müqayisəli təhlil və texnoloji göstəricilər.',
    'resmi'         => 'Dövlət və özəl qurumlar, qanunvericilik dəyişiklikləri və rəsmi texnoloji hesabatlar.',
];
$categoryDesc = $categoryDescriptions[$categorySlug] ?? ($category['meta_description'] ?? "$label bölməsi üzrə ən son xəbərlər, analitik icmallar və hadisələr.");

require_once __DIR__ . '/../includes/header.php';
?>

<main class="w-full py-6 sm:py-8 min-h-screen">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- 1. Breadcrumbs -->
    <nav id="catalog-breadcrumbs" aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400 mb-6 font-medium font-mono">
      <a href="/" class="flex items-center gap-1.5 hover:text-neutral-900 dark:hover:text-white transition-colors">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <span>Ana Səhifə</span>
      </a>
      <svg class="w-3 h-3 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span class="text-neutral-400 font-normal">Bölmələr</span>
      <svg class="w-3 h-3 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span class="text-[#080117] dark:text-[#fcdb56] font-bold uppercase tracking-wide">
        <?= e($label) ?>
      </span>
    </nav>

    <!-- 2. Category Title & Meta Banner -->
    <div class="bg-white dark:bg-[#120726] rounded-xl border border-neutral-200/90 dark:border-[#22153e] p-5 sm:p-7 shadow-xs mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-neutral-100 dark:border-[#22153e]">
        <div>
          <div class="flex items-center flex-wrap gap-2.5 mb-2">
            <span class="w-3 h-3 rounded-full bg-[#fcdb56] shadow-[0_0_8px_rgba(252,219,86,0.8)]"></span>
            <h1 class="text-xl sm:text-2xl font-bold uppercase tracking-tight text-[#080117] dark:text-white flex items-center flex-wrap gap-2">
              <span><?= e($label) ?></span>
              <span class="text-neutral-400 dark:text-neutral-400 font-normal lowercase tracking-normal text-xs sm:text-sm font-mono">
                - <?= count($articles) ?> material
              </span>
            </h1>
          </div>
          <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 max-w-2xl leading-relaxed">
            <?= e($categoryDesc) ?>
          </p>
        </div>

        <!-- Quick Actions -->
        <div class="flex items-center gap-2 self-start md:self-auto">
          <a href="/" class="px-3.5 py-2 rounded-lg bg-neutral-100 dark:bg-[#1f0f3d] hover:bg-neutral-200 dark:hover:bg-[#2c1655] text-neutral-800 dark:text-neutral-100 text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer">
            <span>← Bütün Xəbərlər</span>
          </a>
        </div>
      </div>

      <!-- 3. Top 20 Most Used Tags for this Category -->
      <?php if (count($topTags) > 0): ?>
        <div class="mt-4 pt-3.5">
          <div class="flex items-center justify-between gap-2 mb-2.5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 flex items-center gap-1 font-mono">
              <svg class="w-3 h-3 text-[#fcdb56]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/>
              </svg>
              Ən çox istifadə olunan teqlər (<?= count($topTags) ?>):
            </span>
            <?php if ($activeTag): ?>
              <a href="/<?= e($categorySlug) ?>" class="text-[11px] font-semibold text-neutral-800 dark:text-[#fcdb56] hover:underline cursor-pointer">
                Filtri sıfırla ✕
              </a>
            <?php endif; ?>
          </div>

          <div class="flex items-center gap-1.5 flex-wrap">
            <a href="/<?= e($categorySlug) ?>"
              class="px-2.5 py-1 rounded-md text-xs font-mono font-medium transition-colors <?= !$activeTag ? 'bg-[#080117] dark:bg-[#fcdb56] text-[#fcdb56] dark:text-[#080117] font-bold shadow-xs' : 'bg-neutral-100 dark:bg-[#1c0d38] text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-[#291450]' ?>">
              Hamısı
            </a>
            <?php foreach ($topTags as $tag): ?>
              <?php $isSelected = ($activeTag === $tag); ?>
              <a href="/<?= e($categorySlug) ?>?tag=<?= urlencode($tag) ?>"
                class="px-2.5 py-1 rounded-md text-xs font-mono transition-colors <?= $isSelected ? 'bg-[#080117] dark:bg-[#fcdb56] text-[#fcdb56] dark:text-[#080117] font-bold shadow-xs' : 'bg-neutral-100 dark:bg-[#1c0d38] text-neutral-600 dark:text-neutral-300 hover:bg-neutral-200 dark:hover:bg-[#291450] hover:text-neutral-900 dark:hover:text-white' ?>">
                #<?= e($tag) ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- 3.5. Category Promo Banner (if enabled) -->
    <?php if (!empty($categoryAd) && !empty($categoryAd['enabled']) && !empty($categoryAd['image_url'])): ?>
      <div class="w-full mb-8">
        <aside id="category-promo-banner"
          class="relative block w-full rounded-xl overflow-hidden border border-neutral-200/80 dark:border-[#261545] shadow-2xs hover:shadow-xs hover:border-[#fcdb56]/80 transition-all">
          <a href="<?= e($categoryAd['link_url'] ?: '#') ?>" target="_blank" rel="noopener noreferrer"
            class="block w-full h-auto cursor-pointer relative">
            <img src="<?= e($categoryAd['image_url']) ?>" alt="Reklam Banneri"
              class="w-full h-auto max-h-[200px] object-cover block" loading="lazy" />
            <span class="ad-label">Reklam</span>
          </a>
        </aside>
      </div>
    <?php endif; ?>

    <!-- 4. Article Catalog Grid -->
    <?php if (count($filtered) === 0): ?>
      <div class="text-center py-16 bg-white dark:bg-[#120726] rounded-xl border border-neutral-200 dark:border-[#22153e] p-8 shadow-xs">
        <div class="w-12 h-12 rounded-full bg-neutral-100 dark:bg-[#1c0d38] flex items-center justify-center mx-auto mb-3 text-neutral-400 dark:text-neutral-500">
          <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-1">
          Heç bir material tapılmadı
        </h3>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 max-w-sm mx-auto mb-4">
          <?= $activeTag ? '"#' . e($activeTag) . '" teqinə uyğun xəbər tapılmadı.' : 'Bu bölmədə hələ xəbər yoxdur.' ?>
        </p>
        <?php if ($activeTag): ?>
          <a href="/<?= e($categorySlug) ?>" class="inline-block px-4 py-2 rounded-lg bg-[#fcdb56] text-[#080117] text-xs font-bold transition-colors shadow-xs">
            Filtrləri sıfırla
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php foreach ($filtered as $a): ?>
          <?php include __DIR__ . '/_article_card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
