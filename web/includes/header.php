<?php
/**
 * Shared header include.
 * Variables expected: $pageTitle (optional), $isHeroPage (bool, default false)
 */
$isHeroPage = $isHeroPage ?? false;
$pageTitle = $pageTitle ?? 'alt404 — Azərbaycan Texnologiya Mediası';
$pageDescription = $pageDescription ?? 'Azərbaycanın ən sürətli texnologiya media platforması.';
$categories = getVisibleCategories();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Build a light articles JSON for search suggestions
$publishedForSearch = getPublishedArticles(50);
$searchArticlesJson = json_encode(array_map(function($a) {
    return [
        'title' => $a['title'],
        'slug' => $a['slug'],
        'category' => $a['category'],
        'image_url' => $a['image_url'],
    ];
}, $publishedForSearch), JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="az">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Tomorrow:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body class="<?= $isHeroPage ? 'hero-page' : '' ?>">

<!-- Search articles data for JS suggestions -->
<script>window.__articles = <?= $searchArticlesJson ?>;</script>

<header class="site-header">
  <nav class="<?= $isHeroPage ? 'capsule-nav-hero' : 'capsule-nav' ?>">
    <div class="header-inner">

      <!-- Logo -->
      <a href="/" class="header-logo">
        <img id="header-logo-img" src="<?= $isHeroPage ? '/assets/img/logo.png' : '/assets/img/logo-dark.png' ?>" alt="alt404.com Tech News">
        <span class="live-dot"></span>
      </a>

      <!-- Desktop Nav -->
      <div class="desktop-nav">
        <?php
        $variant = $isHeroPage ? 'dark' : 'light';
        $homeActive = ($currentPath === '/' || $currentPath === '') ? ' active' : '';
        ?>
        <a href="/" class="nav-link nav-link--<?= $variant ?><?= $homeActive ?>" data-nav-variant="nav-link">Ana Səhifə</a>
        <?php foreach ($categories as $cat): ?>
          <?php $catActive = ($currentPath === '/' . $cat['slug']) ? ' active' : ''; ?>
          <a href="/<?= e($cat['slug']) ?>" class="nav-link nav-link--<?= $variant ?><?= $catActive ?>" data-nav-variant="nav-link"><?= e($cat['slug'] === 'texnologiya' ? $cat['label'] : preg_replace('/\s+Xəbərləri$/iu', '', $cat['label'])) ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Actions -->
      <div class="header-actions">
        <!-- Search -->
        <div class="search-wrapper" style="position:relative">
          <button id="search-toggle" class="header-btn header-btn--<?= $variant ?>" data-nav-variant="header-btn" aria-label="Axtar">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </button>
          <div id="search-dropdown" class="search-dropdown <?= $isHeroPage ? 'capsule-nav-hero' : 'capsule-nav' ?>" style="display:none">
            <form action="/axtar" method="GET" style="display:flex;align-items:center;gap:4px">
              <input id="search-input" type="text" name="q" placeholder="Axtar..." class="search-input search-input--<?= $variant ?>" autocomplete="off">
              <button type="submit" class="header-btn header-btn--<?= $variant ?>" aria-label="Axtar">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              </button>
              <button id="search-close" type="button" class="header-btn header-btn--<?= $variant ?>" aria-label="Bağla">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </form>
            <div id="search-results" class="search-suggestions search-suggestions--<?= $variant ?>"></div>
          </div>
        </div>

        <!-- Mobile menu button -->
        <button id="mobile-menu-btn" class="header-btn header-btn--<?= $variant ?> mobile-menu-btn" data-nav-variant="header-btn" aria-label="Menyu" aria-expanded="false" style="display:none">
          <span class="icon-menu">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </span>
          <span class="icon-close" style="display:none">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </span>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="mobile-menu mobile-menu--<?= $variant ?>" style="display:none">
      <a href="/" class="mobile-nav-link mobile-nav-link--<?= $variant ?>" data-nav-variant="mobile-nav-link">Ana Səhifə</a>
      <?php foreach ($categories as $cat): ?>
        <a href="/<?= e($cat['slug']) ?>" class="mobile-nav-link mobile-nav-link--<?= $variant ?>" data-nav-variant="mobile-nav-link"><?= e($cat['label']) ?></a>
      <?php endforeach; ?>
    </div>
  </nav>
</header>
