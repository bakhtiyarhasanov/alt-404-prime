<?php
/**
 * Article card partial — expects $a (article array) to be set.
 */
$cardCatLabel = getCategoryLabel($a['category']);
$cleanCatLabel = preg_replace('/\s+Xəbərləri$/iu', '', $cardCatLabel);
$readTime = (int)($a['reading_time'] ?? 3);
if ($readTime < 1) $readTime = 1;
?>
<article
  class="group cursor-pointer flex flex-col bg-white dark:bg-[#120726] rounded-lg overflow-hidden border border-neutral-200/80 dark:border-[#22153e] hover:border-neutral-300 dark:hover:border-[#fcdb56]/60 hover:shadow-md transition-all duration-200 relative"
>
  <!-- Thumbnail -->
  <div class="relative w-full aspect-video overflow-hidden bg-neutral-900">
    <a href="/<?= e($a['category']) ?>/<?= e($a['slug']) ?>" class="block w-full h-full">
      <?php if (!empty($a['image_url'])): ?>
        <img
          src="<?= e($a['image_url']) ?>"
          alt="<?= e($a['title']) ?>"
          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300 ease-out"
          loading="lazy"
        />
      <?php else: ?>
        <div class="w-full h-full bg-[#140829] flex items-center justify-center text-neutral-600">
          <span class="text-xs font-mono">ALT404</span>
        </div>
      <?php endif; ?>
    </a>

    <!-- Category Pill -->
    <div class="absolute top-2 left-2 z-10 pointer-events-none">
      <span class="inline-flex items-center px-2 py-0.5 rounded-xs text-[9px] font-semibold uppercase tracking-wider bg-[#fcdb56] text-[#080117] shadow-xs">
        <?= e($cleanCatLabel) ?>
      </span>
    </div>

    <!-- Bookmark Button -->
    <button
      type="button"
      data-bookmark-btn
      data-bookmark-id="<?= e($a['id']) ?>"
      data-id="<?= e($a['id']) ?>"
      data-title="<?= e($a['title']) ?>"
      data-slug="<?= e($a['slug']) ?>"
      data-category="<?= e($a['category']) ?>"
      data-category-name="<?= e($cleanCatLabel) ?>"
      data-image="<?= e($a['image_url'] ?? '') ?>"
      data-date="<?= e(formatDateAz($a['created_at'])) ?>"
      class="bookmark-toggle-btn absolute top-2 right-2 z-20 p-1.5 rounded-full bg-white/90 dark:bg-[#080117]/80 hover:bg-white dark:hover:bg-[#080117] text-neutral-700 dark:text-neutral-200 hover:text-[#080117] dark:hover:text-[#fcdb56] backdrop-blur-xs shadow-xs transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 cursor-pointer"
      title="Yadda saxla"
      aria-label="Yadda saxla"
    >
      <svg class="w-3.5 h-3.5 bookmark-icon pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
    </button>
  </div>

  <!-- Card Body -->
  <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
    <div>
      <div class="flex items-center gap-2 text-[11px] text-neutral-500 dark:text-neutral-400 mb-1.5 font-medium font-mono">
        <span class="text-[#080117] dark:text-[#fcdb56] font-semibold"><?= $readTime ?> dəq oxu</span>
      </div>

      <a href="/<?= e($a['category']) ?>/<?= e($a['slug']) ?>" class="block">
        <h3 class="text-[13px] sm:text-sm font-semibold text-neutral-900 dark:text-white leading-snug group-hover:text-[#080117] dark:group-hover:text-[#fcdb56] transition-colors line-clamp-2">
          <?= e($a['title']) ?>
          <?php if (!empty($a['updating'])): ?>
            <span class="w-1.5 h-1.5 rounded-full bg-[#fcdb56] inline-block ml-1 animate-ping"></span>
          <?php endif; ?>
        </h3>
      </a>

      <?php if (!empty($a['excerpt'])): ?>
        <p class="mt-1.5 text-xs text-neutral-500 dark:text-neutral-300 line-clamp-2 leading-relaxed">
          <?= e($a['excerpt']) ?>
        </p>
      <?php endif; ?>
    </div>

    <!-- Footer Meta -->
    <div class="mt-3 pt-2.5 border-t border-neutral-100 dark:border-[#22153e] flex items-center justify-between text-[11px] text-neutral-500 dark:text-neutral-400">
      <span class="font-mono text-neutral-400"><?= formatDateAz($a['created_at']) ?></span>
      <a href="/<?= e($a['category']) ?>/<?= e($a['slug']) ?>" class="text-[#080117] dark:text-[#fcdb56] font-bold text-[11px] group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5">
        <span>Oxu</span>
        <span>→</span>
      </a>
    </div>
  </div>
</article>