<?php
/**
 * Single article page — full hero + reading body.
 * Expects $categorySlug and $postSlug from router.
 */
$article = getArticleBySlug($postSlug);

if (!$article || ($categorySlug && $article['category'] !== $categorySlug)) {
  http_response_code(404);
  require_once __DIR__ . '/404.php';
  return;
}

// Increment views
incrementArticleViews($article['id']);

$isHeroPage = true;
$categoryLabel = getCategoryLabel($article['category']);
$pageTitle = e($article['title']) . ' | alt404';
$pageDescription = $article['excerpt'];
$related = getRelatedArticles($article['category'], $article['id'], 4);
$inlineAd = getAd('inline');

require_once __DIR__ . '/../includes/header.php';
?>

<main style="min-height:100vh;background:var(--color-canvas)">

  <!-- ═══════════════════ ARTICLE HERO ═══════════════════ -->
  <div class="article-hero">
    <div class="article-hero-image">
      <?php if ($article['image_url']): ?>
        <img src="<?= e($article['image_url']) ?>" alt="<?= e($article['title']) ?>" loading="eager" decoding="async">
      <?php else: ?>
        <div style="width:100%;height:100%;background:#1a1a2e"></div>
      <?php endif; ?>
    </div>
    <div class="article-hero-gradient"></div>
    <div class="dot-matrix-invert" style="position:absolute;inset:0;opacity:0.2;pointer-events:none"></div>
    <div class="article-hero-accent"></div>

    <div class="article-hero-content">
      <!-- Breadcrumb -->
      <div class="breadcrumb">
        <a href="javascript:history.back()">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round">
            <line x1="19" y1="12" x2="5" y2="12" />
            <polyline points="12 19 5 12 12 5" />
          </svg>
          Geri
        </a>
        <span class="breadcrumb-sep">/</span>
        <a href="/<?= e($article['category']) ?>"><?= e($categoryLabel) ?></a>
      </div>

      <div class="animate-slide-up">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;flex-wrap:wrap">
          <span class="category-badge-dark"><?= e($categoryLabel) ?></span>
        </div>

        <h1 class="article-hero-title">
          <?= e($article['title']) ?>
          <?php if (!empty($article['updating'])): ?>
            <span class="live-dot" style="margin-left: 8px; vertical-align: middle;"></span>
          <?php endif; ?>
        </h1>

        <p class="article-hero-excerpt"><?= e($article['excerpt']) ?></p>

        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:16px">
          <span class="hero-date">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            <?= formatDateAz($article['created_at']) ?>
          </span>
          <div class="article-hero-tags">
            <?php foreach (array_slice($article['tags'], 0, 4) as $tag): ?>
              <span class="article-hero-tag">#<?= e($tag) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════════ READING BODY ═══════════════════ -->
  <div class="dot-matrix">
    <div class="article-body-wrapper">
      <article class="article-body-card">
        <div class="article-body-accent"></div>
        <div class="article-body-content">
          <div class="prose-reading">
            <?= $article['content'] ?>
          </div>
        </div>
      </article>

      <!-- Tags -->
      <div style="margin-top:32px;display:flex;flex-wrap:wrap;gap:8px">
        <?php foreach ($article['tags'] as $tag): ?>
          <a href="/axtar?q=<?= urlencode($tag) ?>" class="tag-pill">#<?= e($tag) ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Share / copy -->
      <div class="share-bar" style="margin-top:24px">
        <div>
          <p class="share-bar-title">Bu xəbəri paylaşın</p>
          <p class="share-bar-subtitle">alt404.az</p>
        </div>
        <button id="copy-link-btn" class="btn-ghost">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
          </svg>
          Linki Kopyala
        </button>
      </div>

      <!-- Related articles -->
      <?php if (count($related) > 0): ?>
        <section style="margin-top:56px">
          <div class="section-rule" style="margin-bottom:24px">
            <span class="section-rule-title">Əlaqəli Xəbərlər</span>
          </div>
          <div class="article-grid article-grid--2col">
            <?php foreach ($related as $a): ?>
              <?php include __DIR__ . '/_article_card.php'; ?>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </div>
  </div>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>