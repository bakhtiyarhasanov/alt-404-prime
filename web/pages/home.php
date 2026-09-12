<?php
/**
 * Homepage for ALT404 Prime.
 * Full modern redesign matching Google AI Studio app export.
 */
$pageTitle = 'ALT404 | Azərbaycanın Peşəkar Texnologiya Mediası';
$pageDescription = 'ALT404 - Azərbaycanın peşəkar texnologiya mediası — sürətli, dərin və minimal | Xəbərlər, analitik icmallar və eksklüziv layihələr';

$rawArticles = getPublishedArticles(120);
$allArticles = $rawArticles;

// Tag filtering
$activeTag = isset($_GET['tag']) ? trim($_GET['tag']) : null;

if ($activeTag) {
  $normalizeTag = function ($t) {
    return mb_strtolower(str_replace('#', '', trim($t)), 'UTF-8');
  };
  $activeTagNorm = $normalizeTag($activeTag);
  $filtered = array_values(array_filter($allArticles, function ($a) use ($activeTagNorm, $normalizeTag) {
    if (!empty($a['tags']) && is_array($a['tags'])) {
      foreach ($a['tags'] as $t) {
        if ($normalizeTag($t) === $activeTagNorm)
          return true;
      }
    }
    return false;
  }));
} else {
  $filtered = [];
}

// Ads from database
$spotlightAd = getAd('spotlight') ?: getAd('leaderboard');
$leaderboardAd = getAd('leaderboard');
$homePromo2Ad = getAd('home-promo-2') ?: getAd('inline');
$inlineAd = getAd('inline');

$hasSpotlightAd = !empty($spotlightAd) && !empty($spotlightAd['enabled']) && !empty($spotlightAd['image_url']);

// Spotlight articles: featured news only (3 if valid ad banner exists, else up to 4 articles)
$spotlightCount = $hasSpotlightAd ? 3 : 4;
$spotlightArticles = getFeaturedArticles($spotlightCount);

// Group remaining articles by category for category news sections
$categories = getVisibleCategories();
$articlesByCategory = [];
foreach ($allArticles as $art) {
  $c = $art['category'];
  if (!isset($articlesByCategory[$c])) {
    $articlesByCategory[$c] = [];
  }
  $articlesByCategory[$c][] = $art;
}

// Videos for VİDEOLAR section (from database, no empty placeholders)
$videosList = getHomeVideos(4);

// Special projects (LAYİHƏLƏR) from database (no empty placeholders)
$specialProjects = getProjects(true, 12);

require_once __DIR__ . '/../includes/header.php';
?>

<main class="w-full flex-1">

  <?php if ($activeTag): ?>
    <!-- ═══════════════════ TAG FILTER VIEW ═══════════════════ -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
      <div
        class="flex items-center justify-between p-3.5 rounded-lg bg-[#fcdb56]/20 border border-[#fcdb56] text-[#080117] dark:text-neutral-100 text-xs sm:text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4 text-[#080117] dark:text-[#fcdb56]" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
          </svg>
          <span>
            <strong>#<?= e($activeTag) ?></strong> teqinə aid <?= count($filtered) ?> material göstərilir
          </span>
        </div>
        <a href="/"
          class="flex items-center gap-1.5 px-3 py-1 rounded bg-[#fcdb56] hover:bg-[#fcdb56]/90 text-[#080117] font-bold text-xs transition-colors shadow-xs">
          <span>Bütün xəbərlərə qayıt</span>
          <span>✕</span>
        </a>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <?php if (empty($filtered)): ?>
        <div class="text-center py-16 text-neutral-500">
          <p class="text-sm font-medium">Bu teq üzrə heç bir məqalə tapılmadı.</p>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
          <?php foreach ($filtered as $a): ?>
            <?php include __DIR__ . '/_article_card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <!-- ═══════════════════ STANDARD HOME VIEW ═══════════════════ -->

    <!-- 1. Top 4-Spotlight Hero Grid (3-4 tall articles + optional ad banner) -->
    <?php if (!empty($spotlightArticles)): ?>
      <section id="hero-spotlight-section" class="w-full pt-4 pb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">

            <?php foreach ($spotlightArticles as $index => $art): ?>
              <?php
              $sCatLabel = getCategoryLabel($art['category']);
              $sCleanCat = $sCatLabel;
              $readTime = (int) ($art['reading_time'] ?? 3);
              ?>
              <article id="spotlight-card-<?= $index + 1 ?>"
                class="group relative cursor-pointer flex flex-col bg-white dark:bg-[#120726] rounded-lg overflow-hidden border border-neutral-200/90 dark:border-[#22153e] shadow-xs hover:shadow-md hover:border-neutral-300 dark:hover:border-[#fcdb56]/60 transition-all duration-200">
                <!-- Image container (4:5 Aspect Ratio) -->
                <div class="relative w-full aspect-[4/5] overflow-hidden bg-neutral-900">
                  <a href="/<?= e($art['category']) ?>/<?= e($art['slug']) ?>" class="block w-full h-full">
                    <?php if (!empty($art['image_url'])): ?>
                      <img src="<?= e($art['image_url']) ?>" alt="<?= e($art['title']) ?>"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                        loading="eager" />
                    <?php else: ?>
                      <div class="w-full h-full bg-[#140829] flex items-center justify-center text-neutral-600">
                        <span class="text-xs font-mono">ALT404</span>
                      </div>
                    <?php endif; ?>
                  </a>

                  <!-- Category Pill -->
                  <div class="absolute top-3 left-3 z-10 pointer-events-none">
                    <span
                      class="inline-flex items-center px-2.5 py-1 rounded-xs text-[10px] font-semibold uppercase tracking-wider bg-[#fcdb56] text-[#080117] shadow-xs">
                      <?= e($sCleanCat) ?>
                    </span>
                  </div>

                  <!-- Bookmark Button -->
                  <button type="button" data-bookmark-btn data-bookmark-id="<?= e($art['id']) ?>"
                    data-id="<?= e($art['id']) ?>" data-title="<?= e($art['title']) ?>" data-slug="<?= e($art['slug']) ?>"
                    data-category="<?= e($art['category']) ?>" data-category-name="<?= e($sCleanCat) ?>"
                    data-image="<?= e($art['image_url'] ?? '') ?>" data-date="<?= e(formatDateAz($art['created_at'])) ?>"
                    class="bookmark-toggle-btn absolute top-3 right-3 z-20 p-2 rounded-full bg-black/40 hover:bg-black/80 text-white backdrop-blur-xs transition-all cursor-pointer"
                    title="Yadda saxla" aria-label="Yadda saxla">
                    <svg class="w-3.5 h-3.5 bookmark-icon pointer-events-none" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
                    </svg>
                  </button>

                  <!-- Bottom Image Overlay Details (gradient scoped strictly to text area) -->
                  <div
                    class="absolute bottom-0 inset-x-0 p-4 pt-16 text-white pointer-events-none"
                    style="background: linear-gradient(to top, rgba(8, 1, 23, 0.98) 0%, rgba(8, 1, 23, 0.88) 60%, transparent 100%);">
                    <div class="flex items-center gap-2 text-[11px] text-neutral-300 mb-1.5 font-medium drop-shadow-sm">
                      <span class="text-[#fcdb56] font-semibold"><?= $readTime ?> dəq. oxu</span>
                    </div>

                    <a href="/<?= e($art['category']) ?>/<?= e($art['slug']) ?>" class="pointer-events-auto block">
                      <h3
                        class="text-sm sm:text-base font-semibold leading-snug text-white group-hover:text-[#fcdb56] transition-colors drop-shadow-md">
                        <?= e($art['title']) ?>
                        <?php if (!empty($art['updating'])): ?>
                          <span class="w-2 h-2 rounded-full bg-[#fcdb56] inline-block ml-1 animate-ping"></span>
                        <?php endif; ?>
                      </h3>
                    </a>
                  </div>
                </div>

                <!-- Subtitle / summary footer -->
                <div
                  class="p-3 bg-neutral-50/80 dark:bg-[#120726] border-t border-neutral-100 dark:border-[#22153e] flex-1 flex flex-col justify-between">
                  <p class="text-xs text-neutral-600 dark:text-neutral-300 line-clamp-2 leading-relaxed">
                    <?= e($art['excerpt']) ?>
                  </p>
                  <div
                    class="mt-2.5 pt-2 border-t border-neutral-200/60 dark:border-[#22153e] flex items-center justify-between text-[11px] text-neutral-500 dark:text-neutral-400 font-medium">
                    <span class="font-mono text-neutral-400"><?= formatDateAz($art['created_at']) ?></span>
                    <a href="/<?= e($art['category']) ?>/<?= e($art['slug']) ?>"
                      class="text-[#080117] dark:text-[#fcdb56] font-bold group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5">
                      <span>Oxu</span>
                      <span>→</span>
                    </a>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>

            <?php if ($hasSpotlightAd): ?>
              <!-- 4th Card: Pure Clean 4:5 Advertisement Banner -->
              <aside id="spotlight-ad-banner"
                class="group relative flex flex-col bg-white dark:bg-[#120726] rounded-lg overflow-hidden border border-neutral-200/90 dark:border-[#22153e] shadow-xs hover:shadow-md transition-all duration-200">
                <a href="<?= e($spotlightAd['link_url'] ?: 'https://alt404.az') ?>" target="_blank"
                  rel="noopener noreferrer" class="flex-1 flex flex-col h-full w-full cursor-pointer relative" title="Reklam">
                  <div class="relative w-full aspect-[4/5] overflow-hidden bg-neutral-100 dark:bg-neutral-900">
                    <img src="<?= e($spotlightAd['image_url']) ?>" alt="Reklam"
                      class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-300 ease-out"
                      loading="eager" />
                    <span class="ad-label">Reklam</span>
                  </div>
                </a>
              </aside>
            <?php endif; ?>

          </div>
        </div>
      </section>
    <?php endif; ?>

    <!-- 2. VİDEOLAR Section -->
    <?php if (!empty($videosList)): ?>
      <section id="section-videolar" class="w-full py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <!-- Section Header with Yellow Dot -->
          <div class="flex items-center justify-between border-b border-neutral-200/90 dark:border-[#22153e] pb-2.5 mb-4">
            <div class="flex items-center gap-2.5">
              <span
                class="w-2.5 h-2.5 rounded-full bg-[#fcdb56] shadow-[0_0_8px_rgba(252,219,86,0.8)] animate-pulse"></span>
              <h2 class="text-base sm:text-lg font-semibold uppercase tracking-wider text-neutral-900 dark:text-white">
                VİDEOLAR
              </h2>
            </div>
          </div>

          <!-- Video Cards Grid (16:9 aspect ratio) -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <?php foreach ($videosList as $idx => $vid): ?>
              <article id="card-videolar-<?= $idx + 1 ?>"
                class="group cursor-pointer flex flex-col bg-white dark:bg-[#120726] rounded-lg overflow-hidden border border-neutral-200/80 dark:border-[#22153e] hover:border-neutral-300 dark:hover:border-[#fcdb56]/60 hover:shadow-md transition-all duration-200"
                data-video-url="<?= e($vid['youtube_url']) ?>" data-video-title="<?= e($vid['title']) ?>"
                data-video-format="landscape">
                <!-- Thumbnail with Play Button Overlay -->
                <div class="relative w-full aspect-video overflow-hidden bg-neutral-900">
                  <?php
                  $vThumb = !empty($vid['thumbnail_url']) ? $vid['thumbnail_url'] : getYouTubeThumbnail($vid['youtube_url'] ?? '');
                  ?>
                  <img src="<?= e($vThumb) ?>" alt="<?= e($vid['title']) ?>"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 ease-out"
                    loading="lazy" />
                  <div
                    class="absolute inset-0 bg-black/30 flex items-center justify-center group-hover:bg-black/40 transition-colors">
                    <div
                      class="w-11 h-11 rounded-full bg-[#fcdb56] text-[#080117] flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                      <svg class="w-5 h-5 fill-[#080117] ml-0.5" viewBox="0 0 24 24">
                        <polygon points="5 3 19 12 5 21 5 3" />
                      </svg>
                    </div>
                  </div>
                </div>

                <!-- Title Only Below Video -->
                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-center">
                  <h3
                    class="text-[13px] sm:text-sm font-semibold text-neutral-900 dark:text-white leading-snug group-hover:text-[#080117] dark:group-hover:text-[#fcdb56] transition-colors line-clamp-2">
                    <?= e($vid['title']) ?>
                  </h3>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php endif; ?>

    <!-- 3. First Hardware / Smart Device Promo Banner -->
    <?php if (!empty($leaderboardAd) && !empty($leaderboardAd['enabled']) && !empty($leaderboardAd['image_url'])): ?>
      <div class="w-full py-3 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <aside id="promo-banner-smart-device"
          class="relative block w-full rounded-xl overflow-hidden border border-neutral-200/80 dark:border-[#261545] shadow-2xs hover:shadow-xs hover:border-[#fcdb56]/80 transition-all">
          <a href="<?= e($leaderboardAd['link_url'] ?: 'https://alt404.az') ?>" target="_blank" rel="noopener noreferrer"
            class="block w-full h-auto cursor-pointer relative">
            <img src="<?= e($leaderboardAd['image_url']) ?>" alt="Texnoloji Tərəfdaş Reklam Banneri"
              class="w-full h-auto max-h-[220px] object-cover block" loading="lazy" />
            <span class="ad-label">Reklam</span>
          </a>
        </aside>
      </div>
    <?php endif; ?>

    <!-- 4. Category News Sections -->
    <?php
    $categoryIndex = 0;
    foreach ($categories as $catItem):
      $catSlug = $catItem['slug'];
      $catArticles = $articlesByCategory[$catSlug] ?? [];
      if (empty($catArticles))
        continue;

      $categoryIndex++;
      $displayFour = array_slice($catArticles, 0, 4);
      ?>
      <section id="section-<?= e($catSlug) ?>" class="w-full py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

          <!-- Category Section Header -->
          <div class="flex items-center justify-between border-b border-neutral-200/90 dark:border-[#22153e] pb-2.5 mb-4">
            <div class="flex items-center gap-2.5">
              <span class="w-2.5 h-2.5 rounded-full bg-[#fcdb56] shadow-[0_0_8px_rgba(252,219,86,0.8)]"></span>
              <h2 class="text-base sm:text-lg font-semibold uppercase tracking-wider text-neutral-900 dark:text-white">
                <?= e(mb_strtoupper_az($catItem['label'])) ?>
              </h2>
            </div>

            <a href="/<?= e($catSlug) ?>"
              class="flex items-center gap-1 text-xs font-bold text-neutral-900 dark:text-white hover:text-[#080117] dark:hover:text-[#fcdb56] transition-colors uppercase tracking-tight group cursor-pointer">
              <span>Daha çox</span>
              <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6" />
              </svg>
            </a>
          </div>

          <!-- 4 Cards Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <?php foreach ($displayFour as $a): ?>
              <?php include __DIR__ . '/_article_card.php'; ?>
            <?php endforeach; ?>
          </div>

        </div>
      </section>

      <!-- Insert Second Promo Banner after 2nd Category Section -->
      <?php if ($categoryIndex === 2 && !empty($homePromo2Ad) && !empty($homePromo2Ad['enabled']) && !empty($homePromo2Ad['image_url'])): ?>
        <div class="w-full py-3 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <aside id="promo-banner-galaxy-fold"
            class="relative block w-full rounded-xl overflow-hidden border border-neutral-200/80 dark:border-[#261545] shadow-2xs hover:shadow-xs hover:border-[#fcdb56]/80 transition-all">
            <a href="<?= e($homePromo2Ad['link_url'] ?: 'https://alt404.az') ?>" target="_blank" rel="noopener noreferrer"
              class="block w-full h-auto cursor-pointer relative">
              <img src="<?= e($homePromo2Ad['image_url']) ?>" alt="İnnovasiya və Rəqəmsal Həllər Reklam Banneri"
                class="w-full h-auto max-h-[220px] object-cover block" loading="lazy" />
              <span class="ad-label">Reklam</span>
            </a>
          </aside>
        </div>
      <?php endif; ?>

    <?php endforeach; ?>

    <!-- 5. LAYİHƏLƏR Section (ProjectsSection) -->
    <?php if (!empty($specialProjects)): ?>
      <section id="layiheler-section" class="w-full py-8 bg-[#080117] text-white border-y border-[#261545] my-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <!-- Header -->
          <div class="flex items-center justify-between border-b border-[#261545] pb-3 mb-6">
            <div class="flex items-center gap-2.5">
              <span
                class="w-2.5 h-2.5 rounded-full bg-[#fcdb56] shadow-[0_0_10px_rgba(252,219,86,0.8)] animate-pulse"></span>
              <h2 class="text-lg sm:text-xl font-semibold uppercase tracking-wider text-white">
                LAYİHƏLƏR
              </h2>
            </div>
          </div>

          <!-- 6 Poster Cards Grid (9:16 aspect ratio) -->
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <?php foreach ($specialProjects as $idx => $proj): ?>
              <div id="project-poster-<?= $idx + 1 ?>"
                class="group relative cursor-pointer flex flex-col rounded-lg overflow-hidden border border-[#261545] hover:border-[#fcdb56] bg-[#120726] shadow-lg hover:shadow-[#fcdb56]/10 transition-all duration-300 transform hover:-translate-y-1"
                data-video-url="<?= e($proj['youtube_url']) ?>"
                data-video-title="<?= e($proj['title']) ?>"
                data-video-subtitle="<?= e($proj['subtitle'] ?? '') ?>"
                data-video-category="<?= e($proj['category'] ?? 'XÜSUSİ LAYİHƏ') ?>"
                data-video-description="<?= e($proj['description'] ?? '') ?>"
                data-video-format="shorts">
                <div class="relative aspect-[9/16] w-full overflow-hidden bg-[#080117]">
                  <?php
                  $pThumb = !empty($proj['image']) ? $proj['image'] : getYouTubeThumbnail($proj['youtube_url'] ?? '');
                  if (!empty($pThumb) && extractYouTubeID($pThumb) && strpos($pThumb, 'img.youtube.com') === false) {
                      $pThumb = getYouTubeThumbnail($pThumb);
                  }
                  if (empty($pThumb) && !empty($proj['youtube_url'])) {
                      $pThumb = getYouTubeThumbnail($proj['youtube_url']);
                  }
                  
                  // Layihələr 9:16 formatındadır, buna görə həmişə oar2.jpg yoxla, yoxdursa onerror hqdefault-a qayıdacaq
                  if (!empty($pThumb) && strpos($pThumb, 'hqdefault.jpg') !== false) {
                      $pThumb = str_replace('hqdefault.jpg', 'oar2.jpg', $pThumb);
                  }
                  ?>
                  <img src="<?= e($pThumb) ?>" alt="<?= e($proj['title']) ?>"
                    onerror="if (this.src.indexOf('oar2.jpg') !== -1) { this.src = this.src.replace('oar2.jpg', 'hqdefault.jpg'); }"
                    class="w-full h-full object-cover object-top filter grayscale-[20%] group-hover:grayscale-0 group-hover:scale-105 transition-all duration-500"
                    loading="lazy" />

                  <!-- Gradient overlays -->
                  <div class="absolute inset-0 bg-gradient-to-t from-[#080117] via-[#080117]/40 to-transparent"></div>
                  <div class="absolute inset-0 bg-gradient-to-b from-[#080117]/70 via-transparent to-[#080117]"></div>

                  <!-- Top category & duration -->
                  <div class="absolute top-2 left-2 right-2 flex items-center justify-between z-10">
                    <span
                      class="px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase tracking-wider bg-[#080117]/80 text-[#fcdb56] backdrop-blur-md border border-[#261545]">
                      <?= e($proj['category']) ?>
                    </span>
                    <span
                      class="px-1.5 py-0.5 rounded text-[9px] font-mono text-neutral-300 bg-[#080117]/80 backdrop-blur-md">
                      <?= e($proj['duration']) ?>
                    </span>
                  </div>

                  <!-- Play Button on Hover -->
                  <div
                    class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-20">
                    <div
                      class="w-12 h-12 rounded-full bg-[#fcdb56] text-[#080117] flex items-center justify-center shadow-2xl transform scale-75 group-hover:scale-100 transition-transform">
                      <svg class="w-6 h-6 fill-[#080117] ml-0.5" viewBox="0 0 24 24">
                        <polygon points="5 3 19 12 5 21 5 3" />
                      </svg>
                    </div>
                  </div>

                  <!-- Bottom Title -->
                  <div class="absolute bottom-0 inset-x-0 p-2.5 z-10">
                    <h3
                      class="text-[12px] sm:text-[13px] font-semibold uppercase leading-tight tracking-tight text-[#fcdb56] group-hover:text-white transition-colors drop-shadow-md">
                      <?= e($proj['title']) ?>
                    </h3>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

        </div>
      </section>
    <?php endif; ?>

  <?php endif; ?>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>