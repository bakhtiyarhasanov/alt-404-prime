<?php
/**
 * Single article page — Modern layout matching the AI Studio design.
 * Features: Yellow category pill, bookmark action, share/copy action, reading time,
 * 16:9 hero image, prose typography, tags, ad banner, and related articles grid.
 */
$article = getArticleBySlug($postSlug);

if (!$article || ($categorySlug && $article['category'] !== $categorySlug)) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    return;
}

// Increment views
incrementArticleViews($article['id']);

$isHeroPage = false;
$categoryLabel = getCategoryLabel($article['category']);
$pageTitle = e($article['title']) . ' | alt404';
$pageDescription = $article['excerpt'];
$related = getRelatedArticles($article['category'], $article['id'], 4);
$readingTime = (int)($article['reading_time'] ?? 3);
if ($readingTime < 1) $readingTime = 3;
$inlineAd = getAd('inline');

require_once __DIR__ . '/../includes/header.php';
?>

<main class="w-full py-6 sm:py-10 min-h-screen">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- 1. Top Bar: Breadcrumb + Action Controls -->
    <div class="flex items-center justify-between gap-4 pb-4 mb-6 border-b border-neutral-200/80 dark:border-[#261545]">
      <!-- Breadcrumb -->
      <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400 font-medium font-mono truncate">
        <a href="/" class="hover:text-neutral-900 dark:hover:text-white transition-colors shrink-0">Ana Səhifə</a>
        <svg class="w-3 h-3 text-neutral-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        <a href="/<?= e($article['category']) ?>" class="hover:text-neutral-900 dark:hover:text-white transition-colors shrink-0"><?= e($categoryLabel) ?></a>
      </nav>

      <!-- Action buttons: Bookmark + Share -->
      <div class="flex items-center gap-2 shrink-0">
        <!-- Bookmark Button -->
        <button
          type="button"
          data-bookmark-btn
          data-id="<?= e($article['id']) ?>"
          data-title="<?= e($article['title']) ?>"
          data-slug="<?= e($article['slug']) ?>"
          data-category="<?= e($article['category']) ?>"
          data-category-name="<?= e($categoryLabel) ?>"
          data-image="<?= e($article['image_url']) ?>"
          data-date="<?= e(formatDateAz($article['created_at'])) ?>"
          class="p-2 rounded-lg bg-neutral-100 dark:bg-[#1c0d38] hover:bg-[#fcdb56]/20 dark:hover:bg-[#2c1655] text-neutral-700 dark:text-neutral-200 hover:text-[#080117] dark:hover:text-[#fcdb56] transition-colors cursor-pointer"
          title="Yadda saxla"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>
          </svg>
        </button>

        <!-- Share / Copy link -->
        <button
          id="copy-link-btn"
          type="button"
          class="p-2 rounded-lg bg-neutral-100 dark:bg-[#1c0d38] hover:bg-neutral-200 dark:hover:bg-[#2c1655] text-neutral-700 dark:text-neutral-200 transition-colors cursor-pointer"
          title="Linki kopyala"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- 2. Article Container Card -->
    <article class="bg-white dark:bg-[#0e041d] rounded-2xl border border-neutral-200 dark:border-[#261545] p-5 sm:p-8 md:p-10 shadow-xs mb-10 sm:mb-14">
      
      <!-- Category Badge -->
      <div class="mb-4">
        <a href="/<?= e($article['category']) ?>" class="inline-flex items-center px-2.5 py-0.5 rounded-xs text-[11px] font-bold uppercase tracking-wider bg-[#fcdb56] text-[#080117] hover:brightness-105 transition-all">
          <?= e($categoryLabel) ?>
        </a>
      </div>

      <!-- Title -->
      <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-[#080117] dark:text-white leading-tight mb-4">
        <?= e($article['title']) ?>
        <?php if (!empty($article['updating'])): ?>
          <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse ml-2" title="Canlı yenilənir"></span>
        <?php endif; ?>
      </h1>

      <!-- Excerpt -->
      <?php if (!empty($article['excerpt'])): ?>
        <p class="text-base sm:text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed font-medium mb-6">
          <?= e($article['excerpt']) ?>
        </p>
      <?php endif; ?>

      <!-- Article Meta (Date & Reading Time) -->
      <div class="flex items-center gap-3 pt-3 pb-6 border-t border-neutral-200 dark:border-[#261545] text-xs text-neutral-500 dark:text-neutral-400 font-mono">
        <span class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-neutral-400 dark:text-neutral-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
          </svg>
          <span class="text-neutral-600 dark:text-neutral-300 font-medium"><?= formatDateAz($article['created_at']) ?></span>
        </span>
        <span class="text-neutral-300 dark:text-neutral-700">•</span>
        <span class="text-[#080117] dark:text-[#fcdb56] font-semibold font-mono"><?= e($readingTime) ?> dəq. oxu</span>
      </div>

      <!-- Hero Article Image -->
      <div class="relative rounded-xl overflow-hidden aspect-video bg-neutral-100 dark:bg-neutral-900 shadow-md mb-8">
        <?php if (!empty($article['image_url'])): ?>
          <img
            src="<?= e($article['image_url']) ?>"
            alt="<?= e($article['title']) ?>"
            referrerpolicy="no-referrer"
            class="w-full h-full object-cover"
            loading="eager"
          />
        <?php else: ?>
          <div class="w-full h-full flex items-center justify-center bg-neutral-200 dark:bg-[#1a0c33] text-neutral-400">
            alt404
          </div>
        <?php endif; ?>
      </div>

      <!-- Full Article Content -->
      <div class="prose-reading max-w-none text-neutral-800 dark:text-neutral-200 text-sm sm:text-base leading-relaxed space-y-4">
        <?= $article['content'] ?>
      </div>

      <!-- Tags Section -->
      <?php if (!empty($article['tags'])): ?>
        <div class="pt-6 mt-8 border-t border-neutral-100 dark:border-[#261545] flex flex-wrap gap-2">
          <?php foreach ($article['tags'] as $tag): ?>
            <a href="/axtar?q=<?= urlencode($tag) ?>"
              class="px-2.5 py-1 rounded-md bg-neutral-100 dark:bg-[#1c0d38] text-neutral-700 dark:text-neutral-300 text-xs font-mono font-medium hover:bg-[#fcdb56] hover:text-[#080117] dark:hover:bg-[#fcdb56] dark:hover:text-[#080117] transition-colors">
              #<?= e($tag) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Promo Ad Banner -->
      <?php if (!empty($inlineAd) && !empty($inlineAd['enabled']) && !empty($inlineAd['image_url'])): ?>
      <aside id="article-promo-banner" class="mt-8 pt-4">
        <div class="block w-full rounded-xl overflow-hidden border border-neutral-200/80 dark:border-[#261545] shadow-2xs hover:border-[#fcdb56]/80 transition-all relative">
          <a href="<?= e($inlineAd['link_url'] ?: 'https://alt404.az') ?>" target="_blank" rel="noopener noreferrer" class="block w-full cursor-pointer relative">
            <img
              src="<?= e($inlineAd['image_url']) ?>"
              alt="Reklam Banneri"
              class="w-full h-auto max-h-[160px] sm:max-h-[180px] object-cover block"
              loading="lazy"
            />
            <span class="ad-label">Reklam</span>
          </a>
        </div>
      </aside>
      <?php endif; ?>

    </article>

    <!-- 3. Related Articles -->
    <?php if (count($related) > 0): ?>
      <section class="mt-10 sm:mt-14">
        <div class="flex items-center justify-between gap-4 mb-5 pb-3 border-b border-neutral-200/80 dark:border-[#261545]">
          <h2 class="text-sm font-bold uppercase tracking-wider text-[#080117] dark:text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-[#fcdb56]"></span>
            <span>Əlaqəli Xəbərlər</span>
          </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <?php foreach ($related as $a): ?>
            <?php include __DIR__ . '/_article_card.php'; ?>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>