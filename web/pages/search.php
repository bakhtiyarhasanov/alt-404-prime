<?php
/**
 * Search page
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
<main class="page-content dot-matrix">
  <div style="max-width:var(--max-width);margin:0 auto;padding:112px 16px 64px">
    <div style="margin-bottom:40px">
      <h1 class="page-title">Axtarış</h1>
      <form action="/axtar" method="GET" style="display:flex;align-items:center;gap:12px">
        <div class="search-page-input-wrapper">
          <span class="search-page-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </span>
          <input type="text" name="q" value="<?= e($query) ?>" placeholder="Xəbər, mövzu, hashtag axtar..." class="search-page-input">
        </div>
        <button type="submit" class="btn-primary" style="padding:12px 24px">Axtar</button>
      </form>
    </div>

    <?php if (mb_strlen($query) > 1): ?>
      <p class="search-results-count">"<?= e($query) ?>" üçün <strong style="color:var(--color-text-secondary)"><?= count($results) ?></strong> nəticə</p>
      <?php if (count($results) === 0): ?>
        <div style="text-align:center;padding:80px 0">
          <p style="font-family:var(--font-main);font-size:14px;color:var(--color-text-muted)">Nəticə tapılmadı.</p>
        </div>
      <?php else: ?>
        <div class="article-grid animate-slide-up">
          <?php foreach ($results as $a): ?>
            <?php include __DIR__ . '/_article_card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
