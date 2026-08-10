<?php
/**
 * About page
 */
$isHeroPage = false;
$aboutTitle = getSettingValue('about_title', 'Haqqımızda');
$pageTitle = $aboutTitle . ' | alt404';
$pageDescription = 'alt404.com haqqında ətraflı məlumat.';
require_once __DIR__ . '/../includes/header.php';
?>
<main class="page-content dot-matrix">
  <div class="page-container">
    <h1 class="page-title"><?= e($aboutTitle) ?></h1>
    <div class="page-body legal-card">
      <?= getSettingValue('about_text') ?>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>