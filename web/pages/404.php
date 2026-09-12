<?php
/**
 * 404 Not Found page — Modern layout matching the AI Studio design.
 */
$isHeroPage = false;
$pageTitle = 'Səhifə tapılmadı | alt404';
$pageDescription = 'Axtardığınız səhifə mövcud deyil.';
require_once __DIR__ . '/../includes/header.php';
?>

<main class="w-full min-h-[70vh] flex items-center justify-center py-16 px-4">
  <div class="max-w-md w-full text-center bg-white dark:bg-[#120726] rounded-2xl border border-neutral-200/90 dark:border-[#22153e] p-8 sm:p-10 shadow-lg">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-[#fcdb56]/20 text-[#080117] dark:text-[#fcdb56] mb-4">
      <span class="text-2xl font-black font-mono">404</span>
    </div>
    
    <h1 class="text-2xl font-bold uppercase tracking-tight text-[#080117] dark:text-white mb-2">
      Səhifə tapılmadı
    </h1>
    
    <p class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 mb-6 leading-relaxed">
      Axtardığınız səhifə mövcud deyil, silinib və ya ünvanı dəyişdirilmiş ola bilər.
    </p>

    <a href="/" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-lg bg-[#fcdb56] text-[#080117] text-xs font-bold transition-all shadow-xs hover:brightness-105">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        <polyline points="9 22 9 12 15 12 15 22"/>
      </svg>
      <span>Ana Səhifəyə Qayıt</span>
    </a>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
