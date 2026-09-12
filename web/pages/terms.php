<?php
/**
 * Terms of Use page — Modern layout matching the AI Studio design.
 */
$isHeroPage = false;
$termsTitle = getSettingValue('terms_title', 'İstifadə Şərtləri');
$pageTitle = $termsTitle . ' | alt404';
$pageDescription = 'alt404.com saytından istifadə qaydaları və şərtləri.';
require_once __DIR__ . '/../includes/header.php';
?>

<main class="w-full py-8 sm:py-12 min-h-screen">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400 mb-6 font-medium font-mono">
      <a href="/" class="flex items-center gap-1.5 hover:text-neutral-900 dark:hover:text-white transition-colors">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        <span>Ana Səhifə</span>
      </a>
      <svg class="w-3 h-3 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span class="text-[#080117] dark:text-[#fcdb56] font-bold uppercase tracking-wide"><?= e($termsTitle) ?></span>
    </nav>

    <div class="content-page-card bg-white dark:bg-[#0e041d] rounded-2xl border border-neutral-200 dark:border-[#261545] p-6 sm:p-10 shadow-xs">
      <div class="flex items-center gap-2.5 mb-6 pb-4 border-b border-neutral-100 dark:border-[#261545]">
        <span class="w-3 h-3 rounded-full bg-[#fcdb56] shadow-[0_0_8px_rgba(252,219,86,0.8)]"></span>
        <h1 class="text-2xl sm:text-3xl font-bold uppercase tracking-tight text-[#080117] dark:text-white">
          <?= e($termsTitle) ?>
        </h1>
      </div>

      <div class="prose-reading text-sm sm:text-base leading-relaxed text-neutral-800 dark:text-neutral-200 space-y-4">
        <?= getSettingValue('terms_text') ?>
      </div>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>