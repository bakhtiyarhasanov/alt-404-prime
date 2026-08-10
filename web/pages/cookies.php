<?php
/**
 * Cookies page
 */
$isHeroPage = false;
$cookiesTitle = getSettingValue('cookies_title', 'Çərəzlər barədə Bəyanat');
$pageTitle = $cookiesTitle . ' | alt404';
$pageDescription = 'alt404.com saytında çərəzlərdən istifadə qaydaları.';
require_once __DIR__ . '/../includes/header.php';
?>
<main class="page-content dot-matrix">
  <div class="page-container">
    <h1 class="page-title"><?= e($cookiesTitle) ?></h1>
    <div class="page-body legal-card">
      <?= getSettingValue('cookies_text') ?>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>