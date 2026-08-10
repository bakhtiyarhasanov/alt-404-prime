<?php
/**
 * Article card partial — expects $a (article array) to be set.
 */
$cardCatLabel = getCategoryLabel($a['category']);
?>
<a href="/<?= e($a['category']) ?>/<?= e($a['slug']) ?>" class="article-card card">
  <div class="article-card-image">
    <img src="<?= e($a['image_url']) ?>" alt="<?= e($a['title']) ?>" loading="lazy" decoding="async">
  </div>
  <div class="article-card-body">
    <div class="article-card-meta">
      <span class="category-badge"><?= e($cardCatLabel) ?></span>
    </div>
    <h3 class="article-card-title">
      <?= e($a['title']) ?>
      <?php if (!empty($a['updating'])): ?>
        <span class="live-dot" style="margin-left: 6px; vertical-align: middle;"></span>
      <?php endif; ?>
    </h3>
    <p class="article-card-excerpt"><?= e($a['excerpt']) ?></p>
    <div class="article-card-footer">
      <div class="article-card-tags">
        <?php foreach (array_slice($a['tags'], 0, 2) as $tag): ?>
          <span class="tag-pill">#<?= e($tag) ?></span>
        <?php endforeach; ?>
      </div>
      <span class="article-card-date"><?= formatDateAz($a['created_at']) ?></span>
    </div>
  </div>
</a>