<?php
/**
 * Homepage — Hero article, video gallery, feed with tag filtering, category sections.
 */
$isHeroPage = true;
$pageTitle = 'alt404 — Azərbaycan Texnologiya Mediası';
$pageDescription = 'Azərbaycanın ən sürətli texnologiya media platforması.';

$rawArticles = getPublishedArticles(100);
$allArticles = $rawArticles;

$heroArticle = null;
foreach ($allArticles as $index => $article) {
  if ($article['featured']) {
    $heroArticle = $article;
    unset($allArticles[$index]);
    break;
  }
}

if (!$heroArticle) {
  $heroArticle = array_shift($allArticles);
} else {
  $allArticles = array_values($allArticles);
}

$bottomRowArticles = array_slice($allArticles, 0, 5);
$feedArticles = array_slice($allArticles, 5, 20);
$videos = getHomeVideos(4);
$categories = getVisibleCategories();

// Tag filtering
$activeTag = isset($_GET['tag']) ? trim($_GET['tag']) : null;
$allTags = [];
foreach ($rawArticles as $a) {
  foreach ($a['tags'] as $t) {
    $allTags[$t] = true;
  }
}
$allTags = array_keys($allTags);

if ($activeTag) {
  $normalizeTag = function ($t) {
    return strtolower(str_replace('#', '', $t)); };
  $filtered = array_filter($allArticles, function ($a) use ($activeTag, $normalizeTag) {
    foreach ($a['tags'] as $t) {
      if ($normalizeTag($t) === $normalizeTag($activeTag))
        return true;
    }
    return false;
  });
  $filtered = array_values($filtered);
} else {
  $filtered = $feedArticles;
}

require_once __DIR__ . '/../includes/header.php';
?>

<main style="min-height:100vh;background:var(--color-canvas)">

  <?php if ($heroArticle && !$activeTag): ?>
    <!-- ═══════════════════ HERO SECTION ═══════════════════ -->
    <section class="hero-section" style="min-height:clamp(520px,75vh,760px)">
      <img src="<?= e($heroArticle['image_url']) ?>" alt="<?= e($heroArticle['title']) ?>" loading="eager"
        decoding="async" class="hero-image">
      <div class="hero-gradient"></div>
      <div class="dot-matrix-invert" style="position:absolute;inset:0;opacity:0.2;pointer-events:none"></div>

      <div class="hero-content" style="min-height:inherit;padding-bottom:0">
        <div style="margin:24px 0 32px">
          <?= renderAdZone('leaderboard', 'opacity:0.7;max-width:640px') ?>
        </div>

        <div class="animate-slide-up" style="max-width:768px;padding-bottom:224px;margin-top:auto">
          <div class="hero-meta">
            <span class="category-badge-dark"><?= e(getCategoryLabel($heroArticle['category'])) ?></span>
            <span style="width:1px;height:12px;background:rgba(255,255,255,0.2);display:inline-block"></span>
            <span class="hero-date">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
              </svg>
              <?= formatDateAz($heroArticle['created_at']) ?>
            </span>
          </div>

          <a href="/<?= e($heroArticle['category']) ?>/<?= e($heroArticle['slug']) ?>"
            style="display:block;margin-bottom:20px">
            <h1 class="hero-title">
              <?= e($heroArticle['title']) ?>
              <?php if (!empty($heroArticle['updating'])): ?>
                <span class="live-dot" style="margin-left: 8px; vertical-align: middle;"></span>
              <?php endif; ?>
            </h1>
          </a>

          <p class="hero-excerpt"><?= e($heroArticle['excerpt']) ?></p>

          <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <?php foreach (array_slice($heroArticle['tags'], 0, 4) as $tag): ?>
              <a href="/?tag=<?= urlencode($tag) ?>" class="hero-tag">#<?= e($tag) ?></a>
            <?php endforeach; ?>
            <a href="/<?= e($heroArticle['category']) ?>/<?= e($heroArticle['slug']) ?>" class="hero-read-btn">
              Oxu
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round">
                <line x1="7" y1="17" x2="17" y2="7" />
                <polyline points="7 7 17 7 17 17" />
              </svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Bottom row cards (desktop) -->
      <?php if (count($bottomRowArticles) > 0): ?>
        <div style="display:none;position:absolute;bottom:0;left:0;right:0;z-index:20;padding:0 16px 20px"
          class="hero-bottom-desktop">
          <div style="max-width:var(--max-width);margin:0 auto">
            <div class="hero-bottom-row">
              <?php foreach ($bottomRowArticles as $bc): ?>
                <?php $bcLabel = getCategoryLabel($bc['category']); ?>
                <a href="/<?= e($bc['category']) ?>/<?= e($bc['slug']) ?>" class="bottom-card">
                  <div class="bottom-card-image">
                    <?php if ($bc['image_url']): ?>
                      <img src="<?= e($bc['image_url']) ?>" alt="<?= e($bc['title']) ?>" loading="lazy" decoding="async">
                    <?php else: ?>
                      <div style="width:100%;height:100%;background:rgba(255,255,255,0.04)"></div>
                    <?php endif; ?>
                    <div class="gradient-overlay"></div>
                    <span class="category-badge-dark"
                      style="position:absolute;bottom:6px;left:8px;font-size:7.5px;padding:1.5px 5px;letter-spacing:0.08em"><?= e($bcLabel) ?></span>
                  </div>
                  <div class="bottom-card-body">
                    <h3 class="bottom-card-title">
                      <?= e($bc['title']) ?>
                      <?php if (!empty($bc['updating'])): ?>
                        <span class="live-dot" style="margin-left: 5px; vertical-align: middle;"></span>
                      <?php endif; ?>
                    </h3>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Bottom row cards (mobile) -->
      <?php if (count($allArticles) > 0): ?>
        <div class="hero-bottom-mobile" style="display:none;padding:16px;background:#080117">
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
            <?php foreach (array_slice($allArticles, 0, 6) as $mc): ?>
              <a href="/<?= e($mc['category']) ?>/<?= e($mc['slug']) ?>" class="bottom-card">
                <div class="bottom-card-image bottom-card-image--compact">
                  <?php if ($mc['image_url']): ?>
                    <img src="<?= e($mc['image_url']) ?>" alt="<?= e($mc['title']) ?>" loading="lazy" decoding="async">
                  <?php else: ?>
                    <div style="width:100%;height:100%;background:rgba(255,255,255,0.04)"></div>
                  <?php endif; ?>
                  <div class="gradient-overlay"></div>
                </div>
                <div class="bottom-card-body bottom-card-body--compact">
                  <h3 class="bottom-card-title bottom-card-title--compact">
                    <?= e($mc['title']) ?>
                    <?php if (!empty($mc['updating'])): ?>
                      <span class="live-dot" style="margin-left: 4px; vertical-align: middle;"></span>
                    <?php endif; ?>
                  </h3>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <!-- ═══════════════════ VIDEO GALLERY ═══════════════════ -->
  <?php if (count($videos) > 0 || true): ?>
    <section class="video-section">
      <div style="max-width:var(--max-width);margin:0 auto;padding:0 16px">
        <div class="section-rule" style="margin-bottom:24px;border-color:rgba(252,219,86,0.35)">
          <span
            style="font-family:var(--font-main);font-size:11px;font-weight:600;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:0.1em">Günün
            Videoları</span>
        </div>

        <?php if (count($videos) === 0): ?>
          <div class="video-grid">
            <?php for ($i = 0; $i < 4; $i++): ?>
              <div
                style="width:100%;border-radius:var(--radius-lg);background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center">
                <div
                  style="width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="rgba(255,255,255,0.3)" stroke="none">
                    <polygon points="5 3 19 12 5 21 5 3" />
                  </svg>
                </div>
              </div>
            <?php endfor; ?>
          </div>
        <?php else: ?>
          <div class="video-grid">
            <?php foreach ($videos as $v):
              $ytId = extractYouTubeID($v['youtube_url']);
              $thumb = $v['thumbnail_url'] ?: ($ytId ? "https://img.youtube.com/vi/$ytId/hqdefault.jpg" : '');
              ?>
              <button class="video-card" data-video-url="<?= e($v['youtube_url']) ?>"
                data-video-title="<?= e($v['title']) ?>">
                <div class="video-card-image">
                  <?php if ($thumb): ?>
                    <img src="<?= e($thumb) ?>" alt="<?= e($v['title']) ?>" loading="lazy" decoding="async">
                  <?php endif; ?>
                  <div class="video-play-btn">
                    <span>
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="white" stroke="none">
                        <polygon points="5 3 19 12 5 21 5 3" />
                      </svg>
                    </span>
                  </div>
                  <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,0.6),transparent)"></div>
                </div>
                <p class="video-card-title"><?= e($v['title']) ?></p>
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- ═══════════════════ FEED SECTION ═══════════════════ -->
  <div class="dot-matrix">
    <div style="max-width:var(--max-width);margin:0 auto;padding:48px 16px">
      <div class="feed-layout">

        <aside class="feed-sidebar"><?= renderAdZone('sidebar-left') ?></aside>

        <div class="feed-main">
          <!-- Trend tags -->
          <div>
            <div class="trend-label">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#FCDB56" stroke-width="2">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                <polyline points="17 6 23 6 23 12" />
              </svg>
              <span>Trend</span>
            </div>
            <div class="tag-cloud">
              <a href="/" class="tag-pill <?= !$activeTag ? 'active' : '' ?>">#Hamısı</a>
              <?php foreach (array_slice($allTags, 0, 16) as $tag): ?>
                <a href="/?tag=<?= urlencode($tag) ?>"
                  class="tag-pill <?= $activeTag === $tag ? 'active' : '' ?>">#<?= e($tag) ?></a>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Feed header -->
          <div class="feed-header">
            <div class="section-rule">
              <span class="section-rule-title"><?= $activeTag ? '#' . e($activeTag) : 'Son Xəbərlər' ?></span>
            </div>
            <span class="feed-count"><?= count($filtered) ?> xəbər</span>
          </div>

          <?php if (count($filtered) === 0): ?>
            <div style="text-align:center;padding:64px 0">
              <p style="font-family:var(--font-main);font-size:14px;color:var(--color-text-muted)">Daha çox xəbər yoxdur.
              </p>
            </div>
          <?php else: ?>
            <!-- First 2 articles -->
            <div class="article-grid article-grid--2col stagger animate-slide-up">
              <?php foreach (array_slice($filtered, 0, 2) as $a): ?>
                <?php include __DIR__ . '/_article_card.php'; ?>
              <?php endforeach; ?>
            </div>

            <!-- Inline ad + remaining articles -->
            <?php if (count($filtered) > 2): ?>
              <?= renderAdZone('inline', 'height:90px;margin:0') ?>
              <div class="article-grid article-grid--2col">
                <?php foreach (array_slice($filtered, 2) as $a): ?>
                  <?php include __DIR__ . '/_article_card.php'; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          <?php endif; ?>

          <?php if (!$activeTag): ?>
            <div class="load-more-wrapper" style="margin-top: 32px; margin-bottom: 32px;">
              <a href="/xeberler" class="load-more-btn" style="text-decoration: none;">
                Daha çox xəbər
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round">
                  <line x1="5" y1="12" x2="19" y2="12" />
                  <polyline points="12 5 19 12 12 19" />
                </svg>
              </a>
            </div>
          <?php endif; ?>

          <!-- Category sections -->
          <?php foreach ($categories as $cat):
            $catArticles = array_values(array_filter($allArticles, fn($a) => $a['category'] === $cat['slug']));
            $catArticles = array_slice($catArticles, 0, 4);
            if (empty($catArticles))
              continue;
            ?>
            <section>
              <div class="category-section-header">
                <div class="section-rule">
                  <span class="section-rule-title"><?= e($cat['label']) ?></span>
                </div>
                <a href="/<?= e($cat['slug']) ?>" class="category-see-all">
                  Hamısı
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round">
                    <polyline points="9 18 15 12 9 6" />
                  </svg>
                </a>
              </div>
              <div class="article-grid article-grid--2col">
                <?php foreach ($catArticles as $a): ?>
                  <?php include __DIR__ . '/_article_card.php'; ?>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endforeach; ?>

        </div>

        <aside class="feed-sidebar"><?= renderAdZone('sidebar-right') ?></aside>

      </div>
    </div>
  </div>

</main>

<style>
  /* Show desktop bottom row on md+ */
  @media (min-width: 768px) {
    .hero-bottom-desktop {
      display: block !important;
    }
  }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>