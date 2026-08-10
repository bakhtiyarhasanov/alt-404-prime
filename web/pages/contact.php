<?php
/**
 * Contact page with form handling and DB submission
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
<main class="page-content dot-matrix">
  <div class="page-container">
    <h1 class="page-title">Əlaqə</h1>

    <?php $contactDescription = getSettingValue('contact_description'); ?>
    <?php if (!empty($contactDescription)): ?>
      <div class="page-body legal-card" style="margin-bottom: 24px; padding: 24px;">
        <?= $contactDescription ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="form-success">
        <div style="width:48px;height:48px;border-radius:50%;background:rgba(252,219,86,0.12);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-brand)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h2 style="font-family:var(--font-main);font-size:16px;font-weight:600;color:var(--color-text-primary);margin-bottom:8px">Mesajınız göndərildi!</h2>
        <p style="font-family:var(--font-main);font-size:14px;color:var(--color-text-secondary)">Tezliklə sizinlə əlaqə saxlayacağıq.</p>
      </div>
    <?php else: ?>
      <div class="legal-card" style="padding:32px">
        <?php if ($error): ?>
          <div class="form-error"><?= e($error) ?></div>
        <?php endif; ?>
        <form action="/elaqe" method="POST">
          <div class="form-group">
            <label class="form-label" for="ad_soyad">Ad Soyad</label>
            <input type="text" id="ad_soyad" name="ad_soyad" class="form-input" placeholder="Adınız və soyadınız" required value="<?= e($_POST['ad_soyad'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="email">E-poçt</label>
            <input type="email" id="email" name="email" class="form-input" placeholder="E-poçt ünvanınız" required value="<?= e($_POST['email'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="mesaj">Mesaj</label>
            <textarea id="mesaj" name="mesaj" rows="6" class="form-input" placeholder="Mesajınız..." required><?= e($_POST['mesaj'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="form-submit btn-primary" style="width:100%">Göndər</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
