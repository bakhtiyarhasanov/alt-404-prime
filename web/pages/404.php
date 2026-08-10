<?php
/**
 * 404 Not Found page
 */
$isHeroPage = false;
$pageTitle = 'Səhifə tapılmadı | alt404';
$pageDescription = 'Axtardığınız səhifə mövcud deyil.';
require_once __DIR__ . '/../includes/header.php';
?>
<main class="page-404 dot-matrix">
  <div style="text-align:center;padding:16px">
    <h1 class="page-404-code">404</h1>
    <h2 style="font-family:var(--font-main);font-size:1.5rem;font-weight:700;color:var(--color-text-primary);margin-bottom:8px">Səhifə tapılmadı</h2>
    <p class="page-404-text">Axtardığınız səhifə mövcud deyil, silinib və ya ünvanı dəyişdirilib.</p>
    <a href="/" class="btn-primary">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      Ana Səhifəyə Qayıt
    </a>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
