<?php
/**
 * News line page — lists all articles with pagination.
 */
$isHeroPage = false;
$pageTitle = 'Son Xəbərlər — alt404';
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
      <span style="color:var(--color-text-secondary)">Xəbər Lenti</span>
    </div>

    <!-- Header -->
    <div style="margin-bottom:32px">
      <div class="section-rule" style="margin-bottom:4px">
        <h1 style="font-family:var(--font-main);font-size:1.875rem;font-weight:700;color:var(--color-text-primary)">Son Xəbərlər</h1>
      </div>
      <p style="font-family:var(--font-main);font-size:14px;color:var(--color-text-secondary);margin-left:15px"><?= $totalArticles ?> xəbər</p>
    </div>

    <!-- Categories -->
    <div class="category-chips" style="margin-bottom:36px">
      <?php foreach ($visibleCategories as $vc): ?>
        <a href="/<?= e($vc['slug']) ?>" class="category-chip"><?= e($vc['label']) ?></a>
      <?php endforeach; ?>
    </div>

    <!-- Articles Grid (2 columns) -->
    <?php if (count($articles) === 0): ?>
      <div style="text-align:center;padding:96px 0">
        <p style="font-family:var(--font-main);font-size:14px;color:var(--color-text-muted)">Hələ heç bir xəbər yoxdur.</p>
      </div>
    <?php else: ?>
      <div class="article-grid article-grid--2col animate-slide-up">
        <?php foreach ($articles as $a): ?>
          <?php include __DIR__ . '/_article_card.php'; ?>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="load-more-wrapper" style="margin-top:48px;gap:8px">
          <?php if ($page > 1): ?>
            <a href="/xeberler?page=<?= $page - 1 ?>" class="load-more-btn" style="text-decoration:none">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
              Əvvəlki
            </a>
          <?php endif; ?>

          <span style="font-family:var(--font-main);font-size:12px;color:var(--color-text-secondary);display:flex;align-items:center;padding:0 12px">
            Səhifə <?= $page ?> / <?= $totalPages ?>
          </span>

          <?php if ($page < $totalPages): ?>
            <a href="/xeberler?page=<?= $page + 1 ?>" class="load-more-btn" style="text-decoration:none">
              Sonrakı
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
