<?php
/**
 * Terms of Use page
 */
$isHeroPage = false;
$termsTitle = getSettingValue('terms_title', 'İstifadə Şərtləri');
$pageTitle = $termsTitle . ' | alt404';
$pageDescription = 'alt404.com saytından istifadə qaydaları və şərtləri.';
require_once __DIR__ . '/../includes/header.php';
?>
<main class="page-content dot-matrix">
  <div class="page-container">
    <h1 class="page-title"><?= e($termsTitle) ?></h1>
    <div class="page-body legal-card">
      <?= getSettingValue('terms_text') ?>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>