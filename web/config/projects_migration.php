<?php
/**
 * Projects Table Migration Script
 * Creates projects table and populates it with initial seed projects.
 */

require_once __DIR__ . '/database.php';

try {
    $db = getDB();

    // 1. Create projects table
    $db->exec("
        CREATE TABLE IF NOT EXISTS `projects` (
            `id` VARCHAR(64) NOT NULL,
            `title` VARCHAR(500) NOT NULL DEFAULT '',
            `subtitle` VARCHAR(500) DEFAULT '',
            `category` VARCHAR(100) NOT NULL DEFAULT 'EKSPERİMENT',
            `image` TEXT NOT NULL,
            `duration` VARCHAR(50) DEFAULT '15:00',
            `youtube_url` TEXT NOT NULL,
            `description` TEXT DEFAULT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_projects_sort` (`sort_order`, `enabled`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "Projects table checked/created successfully.\n";

    // 2. Insert initial seed projects if table is empty
    $count = (int)$db->query("SELECT COUNT(*) FROM `projects`")->fetchColumn();
    if ($count === 0) {
        $seeds = [
            [
                'id' => 'proj-1',
                'title' => 'CHATGPT İNSAN OLSAYDI NECƏ GÖRÜNƏRDİ?',
                'subtitle' => 'Neyroşəbəkənin psixoloji portreti və insan davranışı',
                'category' => 'EKSPERİMENT',
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=600&auto=format&fit=crop',
                'duration' => '28:15',
                'youtube_url' => 'https://www.youtube.com/watch?v=bBC-nXj3Ng4',
                'description' => 'Süni intellekt alqoritmlərini insan xarakterləri ilə qarşılaşdırdıq. Əgər ChatGPT bir insan olsaydı, dostunuz, həmkarınız və ya rəqibiniz olardı?',
                'sort_order' => 1,
                'enabled' => 1,
            ],
            [
                'id' => 'proj-2',
                'title' => 'KLAVİATURA DÖYMƏLİ QADIN',
                'subtitle' => 'Kiberpank həyat tərzi və bədənə çip implantasiyası',
                'category' => 'DOKUMENTAL',
                'image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=600&auto=format&fit=crop',
                'duration' => '19:40',
                'youtube_url' => 'https://www.youtube.com/watch?v=21X5lGlDOfg',
                'description' => 'Biohakinq və texnoloji bədən modifikasiyaları haqqında Azərbaycanda ilk dəfə çəkilmiş xüsusi reportaj.',
                'sort_order' => 2,
                'enabled' => 1,
            ],
            [
                'id' => 'proj-3',
                'title' => 'GTA 6 TORRENT-DƏ YERLƏŞDİRİLDİ',
                'subtitle' => 'Qlobal saxtakarlıq dalğası və zərərli proqram tələləri',
                'category' => 'TƏDQİQAT',
                'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=600&auto=format&fit=crop',
                'duration' => '32:00',
                'youtube_url' => 'https://www.youtube.com/watch?v=QdBZY2fkU-0',
                'description' => 'Kiber cinayətkarların oyunsevərləri aldatmaq üçün qurduğu saxta torrent şəbəkələrini və təhlükələri araşdırdıq.',
                'sort_order' => 3,
                'enabled' => 1,
            ],
            [
                'id' => 'proj-4',
                'title' => 'KÜÇƏDƏ YAYIM EDƏN İNFLUENSERLƏR',
                'subtitle' => 'Şəhər həyatı, gizlilik qanunları və canlı yayım mədəniyyəti',
                'category' => 'SOSİAL MEDİA',
                'image' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=600&auto=format&fit=crop',
                'duration' => '24:50',
                'youtube_url' => 'https://www.youtube.com/watch?v=5qap5aO4i9A',
                'description' => 'İctimai yerlərdə fasiləsiz IRL yayım edənlərin digər vətəndaşların şəxsi məxfiliyi ilə toqquşması.',
                'sort_order' => 4,
                'enabled' => 1,
            ],
            [
                'id' => 'proj-5',
                'title' => 'ROBLOX QALMAQALI BÖYÜYÜR',
                'subtitle' => 'Uşaq əməyi iddiaları, virtual iqtisadiyyat və qadağalar',
                'category' => 'OYUN TƏHLİLİ',
                'image' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?q=80&w=600&auto=format&fit=crop',
                'duration' => '35:20',
                'youtube_url' => 'https://www.youtube.com/watch?v=V-_O7nl0Ii0',
                'description' => 'Roblox platformasının uşaq psixologiyasına təsirləri və virtual valyutanın yaratdığı risklər.',
                'sort_order' => 5,
                'enabled' => 1,
            ],
            [
                'id' => 'proj-6',
                'title' => 'TELEQRAM BOTLARI İLƏ MİLYONER OLANLAR',
                'subtitle' => 'Tap-to-earn dalğası, kriptovalyuta inteqrasiyaları və reallıq',
                'category' => 'KRİPTO & BİZNES',
                'image' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=600&auto=format&fit=crop',
                'duration' => '21:10',
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'description' => 'Teleqram üzərindən qurulan mini app ekosistemi və yüz milyonlarla istifadəçini cəlb edən iqtisadi model.',
                'sort_order' => 6,
                'enabled' => 1,
            ]
        ];

        $stmt = $db->prepare("
            INSERT INTO `projects` 
            (`id`, `title`, `subtitle`, `category`, `image`, `duration`, `youtube_url`, `description`, `sort_order`, `enabled`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($seeds as $p) {
            $stmt->execute([
                $p['id'], $p['title'], $p['subtitle'], $p['category'],
                $p['image'], $p['duration'], $p['youtube_url'], $p['description'],
                $p['sort_order'], $p['enabled']
            ]);
        }
        echo "Inserted 6 seed projects.\n";
    } else {
        echo "Projects table already contains {$count} records.\n";
    }

} catch (PDOException $e) {
    die("Database migration failed: " . $e->getMessage() . "\n");
}
