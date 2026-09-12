<?php
/**
 * Contact page with form handling and DB submission — Modern layout matching the AI Studio design.
 */
$isHeroPage = false;
$pageTitle = 'Əlaqə | alt404';
$pageDescription = 'alt404.com ilə əlaqə saxlayın.';

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad_soyad = trim($_POST['ad_soyad'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mesaj = trim($_POST['mesaj'] ?? '');

    if (empty($ad_soyad) || empty($email) || empty($mesaj)) {
        $error = 'Zəhmət olmasa bütün xanaları doldurun.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Düzgün e-poçt ünvanı daxil edin.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare('INSERT INTO contact_submissions (ad_soyad, email, mesaj) VALUES (?, ?, ?)');
            $stmt->execute([$ad_soyad, $email, $mesaj]);
            $success = true;
        } catch (Exception $e) {
            $error = 'Sistem xətası baş verdi. Zəhmət olmasa bir az sonra yenidən yoxlayın.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="w-full py-8 sm:py-12 min-h-screen">
  <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

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
      <span class="text-[#080117] dark:text-[#fcdb56] font-bold uppercase tracking-wide">Əlaqə</span>
    </nav>

    <div class="content-page-card bg-white dark:bg-[#0e041d] rounded-2xl border border-neutral-200 dark:border-[#261545] p-6 sm:p-10 shadow-xs">
      <div class="flex items-center gap-2.5 mb-6 pb-4 border-b border-neutral-100 dark:border-[#261545]">
        <span class="w-3 h-3 rounded-full bg-[#fcdb56] shadow-[0_0_8px_rgba(252,219,86,0.8)]"></span>
        <h1 class="text-2xl sm:text-3xl font-bold uppercase tracking-tight text-[#080117] dark:text-white">
          Bizimlə Əlaqə
        </h1>
      </div>

      <?php $contactDescription = getSettingValue('contact_description'); ?>
      <?php if (!empty($contactDescription)): ?>
        <div class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed mb-6">
          <?= $contactDescription ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="py-10 text-center">
          <div class="w-12 h-12 rounded-full bg-[#fcdb56]/20 text-[#080117] dark:text-[#fcdb56] flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <h2 class="text-base font-bold text-neutral-900 dark:text-white mb-1">Mesajınız göndərildi!</h2>
          <p class="text-xs text-neutral-500 dark:text-neutral-400">Tezliklə sizinlə əlaqə saxlayacağıq.</p>
        </div>
      <?php else: ?>
        <?php if ($error): ?>
          <div class="p-3.5 mb-6 rounded-lg bg-red-500/10 border border-red-500/30 text-red-600 dark:text-red-400 text-xs font-semibold">
            <?= e($error) ?>
          </div>
        <?php endif; ?>

        <form action="/elaqe" method="POST" class="space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5 font-mono" for="ad_soyad">
              Ad Soyad
            </label>
            <input
              type="text"
              id="ad_soyad"
              name="ad_soyad"
              placeholder="Adınız və soyadınız"
              required
              value="<?= e($_POST['ad_soyad'] ?? '') ?>"
              class="w-full px-3.5 py-2.5 rounded-lg bg-neutral-50 dark:bg-[#1a0c33] border border-neutral-200 dark:border-[#2e1952] text-xs text-neutral-900 dark:text-white placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-hidden focus:ring-2 focus:ring-[#fcdb56] focus:bg-white dark:focus:bg-[#15092a] transition-all"
            />
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5 font-mono" for="email">
              E-poçt
            </label>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="E-poçt ünvanınız"
              required
              value="<?= e($_POST['email'] ?? '') ?>"
              class="w-full px-3.5 py-2.5 rounded-lg bg-neutral-50 dark:bg-[#1a0c33] border border-neutral-200 dark:border-[#2e1952] text-xs text-neutral-900 dark:text-white placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-hidden focus:ring-2 focus:ring-[#fcdb56] focus:bg-white dark:focus:bg-[#15092a] transition-all"
            />
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 dark:text-neutral-300 mb-1.5 font-mono" for="mesaj">
              Mesaj
            </label>
            <textarea
              id="mesaj"
              name="mesaj"
              rows="5"
              placeholder="Mesajınız..."
              required
              class="w-full px-3.5 py-2.5 rounded-lg bg-neutral-50 dark:bg-[#1a0c33] border border-neutral-200 dark:border-[#2e1952] text-xs text-neutral-900 dark:text-white placeholder:text-neutral-400 dark:placeholder:text-neutral-500 focus:outline-hidden focus:ring-2 focus:ring-[#fcdb56] focus:bg-white dark:focus:bg-[#15092a] transition-all"
            ><?= e($_POST['mesaj'] ?? '') ?></textarea>
          </div>

          <button
            type="submit"
            class="w-full py-3 rounded-lg bg-[#fcdb56] text-[#080117] text-xs font-bold uppercase tracking-wider transition-all shadow-xs hover:brightness-105 cursor-pointer font-mono"
          >
            Göndər
          </button>
        </form>
      <?php endif; ?>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
