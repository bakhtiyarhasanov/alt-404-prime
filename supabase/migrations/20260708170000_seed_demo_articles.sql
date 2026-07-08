/*
  # Seed demo articles (2 per visible category)

  Populates the public site with realistic placeholder content so every section
  has at least two articles. Idempotent: dedupes on slug, so re-running does
  nothing. Delete these from /admin any time.
*/

INSERT INTO articles (title, slug, excerpt, content, category, image_url, tags, featured, published, reading_time, views, versions, created_at, updated_at) VALUES
-- ── Texnologiya Xəbərləri ────────────────────────────────────────────────
('Apple M5 çipli yeni MacBook Pro təqdim olundu', 'apple-m5-macbook-pro',
 'Apple-ın 3nm+ texnologiyası ilə hazırlanan M5 çipi əvvəlki nəslə görə 30% daha sürətli və daha enerji səmərəlidir.',
 '<h2>Yeni nəsil performans</h2><p>Apple M5 çipi 3nm+ istehsal prosesi ilə gəlir və CPU tərəfdə 30%-ə qədər sürət artımı təklif edir. Yeni MacBook Pro modelləri 14 və 16 düymlük ekranlarla təqdim olunub.</p><p>Batareya ömrü 24 saata çatır, bu isə mobil iş üçün yeni standart deməkdir.</p>',
 'texnologiya', 'https://images.pexels.com/photos/18105/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['Apple','MacBook','M5','Laptop'], true, true, 4, 0, '[]'::jsonb, now() - interval '1 hour', now() - interval '1 hour'),

('Windows 12 süni intellekt funksiyaları ilə açıqlandı', 'windows-12-ai',
 'Microsoft növbəti əməliyyat sistemini təqdim etdi — mərkəzdə tam inteqrasiya olunmuş AI köməkçisi dayanır.',
 '<h2>AI mərkəzli təcrübə</h2><p>Windows 12 sistem səviyyəsində süni intellekt köməkçisi ilə gəlir. İstifadəçilər tapşırıqları təbii dillə idarə edə bilir.</p><p>Yeni interfeys daha modul və fərdiləşdirilə bilən dizayna malikdir.</p>',
 'texnologiya', 'https://images.pexels.com/photos/4218883/pexels-photo-4218883.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['Microsoft','Windows','AI','OS'], false, true, 3, 0, '[]'::jsonb, now() - interval '2 hour', now() - interval '2 hour'),

-- ── Elm ──────────────────────────────────────────────────────────────────
('NASA Ayda su buzunun ətraflı xəritəsini çıxardı', 'nasa-ay-su-xeritesi',
 'Yeni orbital missiya Ayın cənub qütbündə gözləniləndən daha çox su buzu ehtiyatı aşkar etdi.',
 '<h2>Gələcək missiyalar üçün açar</h2><p>NASA-nın yeni məlumatları Ayda daimi baza qurmaq planları üçün kritik əhəmiyyət daşıyır. Su buzu həm içməli su, həm də yanacaq mənbəyi ola bilər.</p>',
 'elm-gundem', 'https://images.pexels.com/photos/39644/rocket-launch-rocket-take-off-soar-39644.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['NASA','Ay','Kosmos','Elm'], false, true, 5, 0, '[]'::jsonb, now() - interval '3 hour', now() - interval '3 hour'),

('Alimlər otaq temperaturunda superkeçiricilik təcrübəsini təkrarladı', 'superkecirici-otaq-temperaturu',
 'Beynəlxalq komanda enerji ötürülməsində inqilab vəd edən nəticələri müstəqil şəkildə təsdiqlədi.',
 '<h2>Enerjidə inqilab?</h2><p>Otaq temperaturunda superkeçiricilik itkisiz enerji ötürülməsi deməkdir. Bu, elektrik şəbəkələrindən tibbi cihazlara qədər hər şeyi dəyişə bilər.</p>',
 'elm-gundem', 'https://images.pexels.com/photos/2085831/pexels-photo-2085831.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['Fizika','Superkecirici','Elm','Enerji'], false, true, 6, 0, '[]'::jsonb, now() - interval '4 hour', now() - interval '4 hour'),

-- ── Süni İntellekt ───────────────────────────────────────────────────────
('Yeni açıq mənbə model bir çox testdə GPT-4 səviyyəsinə çatdı', 'acig-menbe-llm-gpt4',
 'İcma tərəfindən hazırlanan yeni dil modeli açıq lisenziya ilə gəlir və şirkətlər üçün əlçatan alternativ yaradır.',
 '<h2>Açıq AI-nın yüksəlişi</h2><p>Model müxtəlif benchmarklarda kommersiya modelləri ilə rəqabət aparır. Açıq lisenziya sayəsində istənilən şirkət onu öz serverində işlədə bilər.</p>',
 'suni-intellekt', 'https://images.pexels.com/photos/8386440/pexels-photo-8386440.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['AI','LLM','OpenSource','MachineLearning'], false, true, 4, 0, '[]'::jsonb, now() - interval '5 hour', now() - interval '5 hour'),

('Süni intellekt zülal strukturlarını saniyələrdə proqnozlaşdırır', 'ai-zulal-struktur',
 'Yeni model dərman kəşfini sürətləndirərək mürəkkəb zülal formalarını əvvəlkindən qat-qat tez müəyyən edir.',
 '<h2>Tibbdə tətbiq</h2><p>Zülal strukturlarının sürətli proqnozu yeni dərmanların hazırlanma müddətini illərlə qısalda bilər. Tədqiqatçılar bunu böyük irəliləyiş adlandırır.</p>',
 'suni-intellekt', 'https://images.pexels.com/photos/2280571/pexels-photo-2280571.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['AI','Biotex','Elm','DeepLearning'], false, true, 5, 0, '[]'::jsonb, now() - interval '6 hour', now() - interval '6 hour'),

-- ── Startap ──────────────────────────────────────────────────────────────
('Bakı startapı Avropa akseleratoruna qəbul olundu', 'baki-startap-avropa-akselerator',
 'Yerli komanda rəqabətli seçim mərhələsini keçərək nüfuzlu Avropa proqramına daxil oldu.',
 '<h2>Regional uğur</h2><p>Startap məhsulunu Avropa bazarına çıxarmaq üçün mentorluq və investisiya imkanı qazandı. Bu, yerli ekosistem üçün mühüm addımdır.</p>',
 'startap', 'https://images.pexels.com/photos/3183150/pexels-photo-3183150.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['Startap','Azerbaijan','Accelerator','Business'], false, true, 3, 0, '[]'::jsonb, now() - interval '7 hour', now() - interval '7 hour'),

('Fintex startapı 5 milyon dollar investisiya cəlb etdi', 'fintex-startap-5m-investisiya',
 'Rəqəmsal ödəniş həlləri üzərində işləyən komanda yeni raundda ciddi maliyyələşmə əldə etdi.',
 '<h2>Böyümə mərhələsi</h2><p>Vəsait komandanın genişlənməsinə və məhsulun yeni bazarlara çıxmasına yönəldiləcək. Şirkət region üzrə ödəniş infrastrukturunu inkişaf etdirir.</p>',
 'startap', 'https://images.pexels.com/photos/6693661/pexels-photo-6693661.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['Fintex','Startap','Funding','Payments'], false, true, 3, 0, '[]'::jsonb, now() - interval '8 hour', now() - interval '8 hour'),

-- ── Texnobloq ────────────────────────────────────────────────────────────
('2026-da hansı proqramlaşdırma dilini öyrənməli?', 'texnobloq-proqramlashdirma-dili-2026',
 'Karyera imkanları, icma dəstəyi və gələcək tələbat baxımından ən perspektivli dillərə nəzər salırıq.',
 '<h2>Seçim meyarları</h2><p>Dil seçərkən bazar tələbatı və layihə növü vacibdir. Web üçün JavaScript/TypeScript, data üçün Python, sistem səviyyəsi üçün Rust yaxşı seçimlərdir.</p>',
 'texnobloq', 'https://images.pexels.com/photos/1181671/pexels-photo-1181671.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['Proqramlashdirma','Karyera','Developer','Texnobloq'], false, true, 6, 0, '[]'::jsonb, now() - interval '9 hour', now() - interval '9 hour'),

('Ev ofisi üçün ən yaxşı 5 qadcet', 'texnobloq-ev-ofisi-qadcetler',
 'Məhsuldarlığı artıran və iş şəraitini rahatlaşdıran seçilmiş cihazların siyahısı.',
 '<h2>Rahat və məhsuldar</h2><p>Yaxşı monitor, mexaniki klaviatura, keyfiyyətli qulaqlıq, veb-kamera və erqonomik oturacaq — uzun iş günlərini asanlaşdırır.</p>',
 'texnobloq', 'https://images.pexels.com/photos/1029757/pexels-photo-1029757.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['Qadcet','HomeOffice','Produktivlik','Texnobloq'], false, true, 4, 0, '[]'::jsonb, now() - interval '10 hour', now() - interval '10 hour'),

-- ── Avtomobil ────────────────────────────────────────────────────────────
('BYD sərfəli qiymətli yeni elektromobilini təqdim etdi', 'byd-yeni-ucuz-elektromobil',
 'Çin istehsalçısı geniş kütlə üçün nəzərdə tutulan uzun məsafəli və əlçatan modelini açıqladı.',
 '<h2>Kütləvi elektrikləşmə</h2><p>Yeni model bir doldurma ilə 500 km-dən çox məsafə vəd edir. Rəqabətli qiymət onu region bazarında güclü seçim edir.</p>',
 'avtomobil', 'https://images.pexels.com/photos/3802510/pexels-photo-3802510.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['BYD','EV','Elektromobil','Avto'], false, true, 4, 0, '[]'::jsonb, now() - interval '11 hour', now() - interval '11 hour'),

('Avtopilot texnologiyası 5-ci səviyyəyə yaxınlaşır', 'avtopilot-5-ci-seviyye',
 'Yeni sınaqlar tam avtonom sürücülüyün reallığa bir addım da yaxınlaşdığını göstərir.',
 '<h2>Tam avtonomluq</h2><p>5-ci səviyyə sürücüsüz idarəetmə deməkdir. Şəhər şəraitindəki sınaqlar ümidverici nəticələr göstərir, lakin tənzimləmə hələ də əsas maneədir.</p>',
 'avtomobil', 'https://images.pexels.com/photos/110844/pexels-photo-110844.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['Avtopilot','SelfDriving','EV','Avto'], false, true, 5, 0, '[]'::jsonb, now() - interval '12 hour', now() - interval '12 hour'),

-- ── Texnoicmal ───────────────────────────────────────────────────────────
('iPhone 17 Pro icmalı: nələr dəyişdi?', 'texnoicmal-iphone-17-pro',
 'Yeni kamera sistemi, daha parlaq ekran və gücləndirilmiş çip — detallı baxış.',
 '<h2>Gündəlik təcrübə</h2><p>iPhone 17 Pro kamera və batareyada nəzərəçarpan irəliləyiş təklif edir. Ekran daha parlaq, korpus isə daha davamlıdır.</p>',
 'texnoicmal', 'https://images.pexels.com/photos/699122/pexels-photo-699122.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['iPhone','Apple','Icmal','Smartphone'], false, true, 5, 0, '[]'::jsonb, now() - interval '13 hour', now() - interval '13 hour'),

('Samsung Galaxy Watch 8 icmalı', 'texnoicmal-galaxy-watch-8',
 'Sağlamlıq sensorları və batareya ömrü baxımından yeni nəsil ağıllı saatı sınaqdan keçirdik.',
 '<h2>Sağlamlıq izləmə</h2><p>Galaxy Watch 8 dəqiq sağlamlıq ölçmələri və uzun batareya ömrü ilə seçilir. İnterfeys daha səlis işləyir.</p>',
 'texnoicmal', 'https://images.pexels.com/photos/437037/pexels-photo-437037.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['Samsung','Smartwatch','Icmal','Wearable'], false, true, 4, 0, '[]'::jsonb, now() - interval '14 hour', now() - interval '14 hour'),

-- ── Məqalələr ────────────────────────────────────────────────────────────
('Rəqəmsal minimalizm: texnologiya ilə sağlam münasibət', 'meqale-reqemsal-minimalizm',
 'Ekran vaxtını azaltmaq və diqqəti qorumaq üçün praktik yanaşmalar.',
 '<h2>Az, amma məqsədli</h2><p>Rəqəmsal minimalizm bütün texnologiyadan imtina deyil — onu şüurlu istifadə etməkdir. Bildirişləri azaltmaq və məqsədli istifadə açardır.</p>',
 'meqaleler', 'https://images.pexels.com/photos/1092644/pexels-photo-1092644.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['Minimalizm','Produktivlik','Meqale','Lifestyle'], false, true, 7, 0, '[]'::jsonb, now() - interval '15 hour', now() - interval '15 hour'),

('Gələcəyin iş yerləri: hansı peşələr qalacaq?', 'meqale-gelecek-is-yerleri',
 'Avtomatlaşma dövründə hansı bacarıqların dəyərini qoruyacağını araşdırırıq.',
 '<h2>Uyğunlaşma dövrü</h2><p>Təkrarlanan işlər avtomatlaşdıqca yaradıcılıq, tənqidi düşüncə və insanlararası bacarıqlar daha dəyərli olur. Davamlı öyrənmə əsas rəqabət üstünlüyüdür.</p>',
 'meqaleler', 'https://images.pexels.com/photos/3184465/pexels-photo-3184465.jpeg?auto=compress&cs=tinysrgb&w=1200',
 ARRAY['Karyera','Gelecek','Meqale','Work'], false, true, 6, 0, '[]'::jsonb, now() - interval '16 hour', now() - interval '16 hour')

ON CONFLICT (slug) DO NOTHING;
