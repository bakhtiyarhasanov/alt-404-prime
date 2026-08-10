<?php
/**
 * Category page — lists articles for a category with tag filtering.
 * Expects $categorySlug from router.
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

$allTags = [];
foreach ($articles as $a) { foreach ($a['tags'] as $t) { $allTags[$t] = true; } }
$allTags = array_keys($allTags);

$curatedTags = $category['curated_tags'] ?? [];
$visibleTags = count($curatedTags) > 0
    ? array_filter($allTags, fn($t) => in_array($t, $curatedTags))
    : $allTags;

$filtered = $activeTag
    ? array_values(array_filter($articles, fn($a) => in_array($activeTag, $a['tags'])))
    : $articles;

$visibleCategories = array_values(array_filter(getVisibleCategories(), fn($c) => $c['slug'] !== $categorySlug));

require_once __DIR__ . '/../includes/header.php';
?>

<main class="page-content dot-matrix">
  <div style="max-width:var(--max-width);margin:0 auto;padding:112px 16px 64px">

    <!-- Leaderboard -->
    <div style="margin-bottom:32px;height:90px">
      <?= renderAdZone('leaderboard', 'height:90px') ?>
    </div>

    <!-- Breadcrumb -->
    <div style="display:flex;align-items:center;gap:8px;font-family:var(--font-main);font-size:11px;color:var(--color-text-muted);margin-bottom:32px">
      <a href="/" style="transition:color 0.15s" onmouseover="this.style.color='var(--color-text-primary)'" onmouseout="this.style.color=''">Ana Səhifə</a>
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
      <span style="color:var(--color-text-secondary)"><?= e($label) ?></span>
    </div>

    <!-- Header -->
    <div style="margin-bottom:32px">
      <div class="section-rule" style="margin-bottom:4px">
        <h1 style="font-family:var(--font-main);font-size:1.875rem;font-weight:700;color:var(--color-text-primary)"><?= e($label) ?></h1>
      </div>
      <p style="font-family:var(--font-main);font-size:14px;color:var(--color-text-secondary);margin-left:15px"><?= count($articles) ?> xəbər</p>
    </div>

    <!-- Other categories -->
    <div class="category-chips">
      <?php foreach ($visibleCategories as $vc): ?>
        <a href="/<?= e($vc['slug']) ?>" class="category-chip"><?= e($vc['label']) ?></a>
      <?php endforeach; ?>
    </div>

    <!-- Tag filter -->
    <?php if (count($visibleTags) > 0): ?>
      <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:36px">
        <a href="/<?= e($categorySlug) ?>" class="tag-pill <?= !$activeTag ? 'active' : '' ?>">#Hamısı</a>
        <?php foreach ($visibleTags as $tag): ?>
          <a href="/<?= e($categorySlug) ?>?tag=<?= urlencode($tag) ?>" class="tag-pill <?= $activeTag === $tag ? 'active' : '' ?>">#<?= e($tag) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Articles -->
    <?php if (count($filtered) === 0): ?>
      <div style="text-align:center;padding:96px 0">
        <p style="font-family:var(--font-main);font-size:14px;color:var(--color-text-muted)">Bu kateqoriyada hələ xəbər yoxdur.</p>
      </div>
    <?php else: ?>
      <div class="article-grid animate-slide-up">
        <?php foreach ($filtered as $a): ?>
          <?php include __DIR__ . '/_article_card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
