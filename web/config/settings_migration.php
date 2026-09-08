w<?php
/**
 * Settings Migration Script
 * Run this to create settings table and populate it with initial data.
 */

require_once __DIR__ . '/database.php';

try {
    $db = getDB();

    // 1. Create settings table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `settings` (
            `key` VARCHAR(100) NOT NULL,
            `label` VARCHAR(255) NOT NULL,
            `type` VARCHAR(50) NOT NULL,
            `group_name` VARCHAR(100) NOT NULL,
            `value` LONGTEXT DEFAULT NULL,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "Settings table checked/created successfully.\n";

    // 2. Insert seed data
    $seeds = [
        // Social Group
        [
            'key' => 'facebook_link',
            'label' => 'Facebook Linki',
            'type' => 'short_text',
            'group_name' => 'social',
            'value' => 'https://www.facebook.com/alt404com'
        ],
        [
            'key' => 'instagram_link',
            'label' => 'Instagram Linki',
            'type' => 'short_text',
            'group_name' => 'social',
            'value' => 'https://www.instagram.com/alt404com/'
        ],
        [
            'key' => 'twitter_link',
            'label' => 'Twitter (X) Linki',
            'type' => 'short_text',
            'group_name' => 'social',
            'value' => 'https://x.com/alt404com'
        ],
        [
            'key' => 'linkedin_link',
            'label' => 'LinkedIn Linki',
            'type' => 'short_text',
            'group_name' => 'social',
            'value' => 'https://www.linkedin.com/company/alt404com'
        ],
        [
            'key' => 'youtube_link',
            'label' => 'YouTube Linki',
            'type' => 'short_text',
            'group_name' => 'social',
            'value' => 'https://www.youtube.com/@alt404com'
        ],
        [
            'key' => 'telegram_link',
            'label' => 'Telegram Linki',
            'type' => 'short_text',
            'group_name' => 'social',
            'value' => 'http://t.me/alt404com'
        ],

        // About Group
        [
            'key' => 'about_title',
            'label' => 'Haqqımızda Səhifə Başlığı',
            'type' => 'short_text',
            'group_name' => 'about',
            'value' => 'Haqqımızda'
        ],
        [
            'key' => 'about_text',
            'label' => 'Haqqımızda Mətni',
            'type' => 'rich_text',
            'group_name' => 'about',
            'value' => '<p>"alt404.com" Azərbaycanın rəqəmsal ekosistemində texnologiya, startap, elm və innovasiya sahələrində ən son yenilikləri, dərin təhlilləri və eksklüziv icmalları təqdim edən peşəkar media platformasıdır.</p><p>Biz "alt404" olaraq texnoloji tərəqqinin sürətli tempini Azərbaycan oxucusuna anlaşılan, obyektiv və operativ dildə çatdırmağı özümüzə missiya seçmişik. "alt404" komandası olaraq rəqəmsal dünyanın nəbzini tuturuq və hər gün minlərlə məlumat arasından ən vacib olanları seçərək sizin üçün təhlil edirik.</p><p>Komandamız rəqəmsal dünyanın qabaqcıl simalarından ibarətdir və müasir istifadəçi təcrübəsinə əsaslanan keyfiyyətli məzmun hazırlayaraq Azərbaycanın texnoloji maariflənməsinə töhfə verməyə fokuslanıb.</p><p>"alt404", sadəcə xəbər deyil, innovativ gələcəyin rəqəmsal bələdçisidir.</p><p>Saytın məsul redaktoru: Vüsal Məmmədzadə Səyyaf oğlu</p><p>Məsul redaktorla əlaqə: +994502474104</p>'
        ],

        // Terms Group
        [
            'key' => 'terms_title',
            'label' => 'İstifadə Şərtləri Başlığı',
            'type' => 'short_text',
            'group_name' => 'terms',
            'value' => 'İstifadə Şərtləri'
        ],
        [
            'key' => 'terms_text',
            'label' => 'İstifadə Şərtləri Mətni',
            'type' => 'rich_text',
            'group_name' => 'terms',
            'value' => '<p>Aşağıdakı İstifadə Şərtləri ("Şərtlər"), alt404.com ("Veb-sayt" ve ya "Platforma") tərəfindən istifadəçilərə təqdim olunan xidmət və məlumatlardan istifadə qaydalarını tənzimləyir. Veb-sayta daxil olmaqla, hər bir istifadəçi bu Şərtləri tam şəkildə qəbul etmiş sayılır.</p><h2>1. Şərtlərə Dəyişikliklər</h2><p>alt404.com, istənilən vaxt bu Şərtlərdə dəyişiklik etmək və ya onları yeniləmək hüququnu özündə saxlayır. Yenilənmiş Şərtlər Veb-saytda dərc edildiyi andan etibarən qüvvəyə minir. Şərtlərin dəyişdirilməsindən sonra Veb-saytdan istifadənin davam etdirilməsi yeni Şərtlərin qəbul edildiyi mənasına gəlir.</p><h2>2. Əqli Mülkiyyət Hüquqları</h2><p>Veb-saytda yerləşdirilən bütün materiallar, o cümlədən xəbərlər, məqalələr, təhlillər, fotoşəkillər, videolar, loqolar, qrafiklər və dizayn elementləri alt404 və ya müvafiq hüquq sahiblərinə məxsusdur və müəllif hüquqları ilə qorunur. Məzmunun icazəsiz kopyalanması, çoxaldılması, yenidən yayımlanması, daxil edilməsi və ya kommersiya məqsədləri üçün istifadəsi qəti qadağandır. İstisna hallarda, mətndə dəyişiklik edilməməsi və alt404.com-a aktiv, işlək link verilməsi şərti ilə materiallardan istifadəyə icazə verilə bilər.</p><h2>3. Məzmun Dəqiqliyi və Məsuliyyət</h2><p>Komandamız materialların dərci zamanı məlumatların dəqiqliyini yoxlamağa çalışsa da, Veb-saytda yer alan məlumatların mütləq doğruluğuna zəmanət verilmir. alt404, materialların dərci zamanı texniki və ya redaksiya xətalarına görə məsuliyyət daşımır. İstifadəçi məlumatlardan öz riski altında istifadə edir.</p><h2>4. Üçüncü Tərəf Keçidləri</h2><p>Saytımızda başqa veb-saytlara və ya resurslara keçidlər (linklər) ola bilər. alt404 həmin saytların məzmununa, təhlükəsizliyinə və ya məxfilik siyasətinə görə heç bir məsuliyyət daşımır. Keçidlərin verilməsi tövsiyə xarakteri daşımır.</p><h2>5. Kommersiya İstifadəsi</h2><p>Saytdakı materiallardan kommersiya məqsədləri üçün istifadə etmək (reklam, partnyorluq, məlumat bazası yaratmaq və s.) qəti qadağandır. Bu cür əməkdaşlıqlar üçün alt404 ilə əvvəlcədən yazılı razılıq alınmalıdır.</p><h2>6. Qanunvericilik və Yurisdiksiya</h2><p>Bu Şərtlərin təfsiri və tətbiqi Azərbaycan Respublikasının qanunvericiliyinə əsasən tənzimlənir. Müvafiq mübahisələr Azərbaycan Respublikasının müvafiq məhkəmələri tərəfindən həll edilir.</p>'
        ],

        // Cookies Group
        [
            'key' => 'cookies_title',
            'label' => 'Çərəz Siyasəti Başlığı',
            'type' => 'short_text',
            'group_name' => 'cookies',
            'value' => 'Çərəzlər barədə Bəyanat'
        ],
        [
            'key' => 'cookies_text',
            'label' => 'Çərəz Siyasəti Mətni',
            'type' => 'rich_text',
            'group_name' => 'cookies',
            'value' => '<p>Çərəzlər (Cookies) siyasətimiz, alt404.com saytını ziyarət etdiyiniz zaman çərəzlərin necə istifadə edildiyini ətraflı izah edir. Veb-saytdan istifadə etməyə davam etməklə, bu siyasəti qəbul etmiş olursunuz.</p><h2>1. Təriflər və Çərəz nədir?</h2><p>Çərəzlər brauzeriniz vasitəsilə cihazınıza (kompüter, mobil telefon, planşet) saxlanılan kiçik mətn fayllarıdır. Onlar Veb-saytın funksionallığını artırmaq, istifadəçi təcrübəsini təhlil etmək və fərdiləşdirilmiş reklamlar göstərmək üçün istifadə olunur.</p><h2>2. Biz hansı çərəzlərdən istifadə edirik?</h2><h3>Zəruri (Funksional) Çərəzlər</h3><p>Saytın əsas funksiyalarının (naviqasiya, təhlükəsizlik, məznunun yüklənməsi) işləməsini təmin etmək üçün lazımdır. Onlar brauzerinizdə saxlanılır və dəaktiv edilə bilməz.</p><h3>Analitik (Performans) Çərəzlər</h3><p>Saytın neçə istifadəçi tərəfindən ziyarət edildiyini, ən çox baxılan səhifələri və s. anonim şəkildə izləmək üçün istifadə olunur. Bu məlumatlar saytı təkmilləşdirməyə kömək edir (örnəyin, Google Analytics).</p><h3>Reklam və Marketinq Çərəzləri</h3><p>İstifadəçi maraqlarına uyğun reklamların göstərilməsini və reklam kampaniyalarının səmərəliliyini ölçməyi təmin edir.</p><h2>3. Üçüncü Tərəf Çərəzləri</h2><p>alt404, analitika və reklam məqsədləri üçün üçüncü tərəf partnyorları (örnəyin, Google Analytics, reklam şəbəkələri, sosial media plaginləri) ilə əməkdaşlıq edir. Bu partnyorların çərəz istifadəsi onların öz məxfilik siyasətləri ilə tənzimlənir.</p><h2>4. Google Analytics Tətbiqi</h2><p>Veb-saytımız Google Inc. tərəfindən təqdim olunan Google Analytics xidmətindən istifadə edir. Google Analytics məlumatları analiz etmək məqsədilə cihazınıza çərəzlər yerləşdirir. Google-un çərəz siyasəti barədə ətraflı məlumatı rəsmi saytından əldə edə bilərsiniz.</p><h2>5. Çərəzlərin İdarə Olunması</h2><p>İstənilən vaxt brauzerinizin parametrlər bölməsindən çərəzləri sıfırlaya və ya tamamilə bloklaya bilərsiniz. Lakin zəruri çərəzlərin dəaktiv edilməsi Veb-saytın bəzi funksiyalarının (örnəyin, axtarış, daxil olma) tam işləməməsinə səbəb ola bilər.</p>'
        ],
        // Contact Group
        [
            'key' => 'contact_description',
            'label' => 'Əlaqə Səhifəsi Haqqında/Təsvir Mətni',
            'type' => 'rich_text',
            'group_name' => 'contact',
            'value' => '<p>Bizimlə əlaqə saxlamaq üçün aşağıdakı formanı doldura bilərsiniz. Hər hansı sualınız, təklifiniz və ya əməkdaşlıq müraciətiniz varsa, komandamız ən qısa zamanda sizinlə əlaqə saxlayacaqdır.</p>'
        ]
    ];

    $stmt = $db->prepare("
        INSERT INTO `settings` (`key`, `label`, `type`, `group_name`, `value`) 
        VALUES (:key, :label, :type, :group_name, :value)
        ON DUPLICATE KEY UPDATE 
            `label` = VALUES(`label`), 
            `type` = VALUES(`type`),
            `group_name` = VALUES(`group_name`)
    ");

    foreach ($seeds as $seed) {
        $stmt->execute($seed);
    }

    echo "Seed settings loaded successfully.\n";

} catch (PDOException $e) {
    die("Database migration failed: " . $e->getMessage() . "\n");
}
