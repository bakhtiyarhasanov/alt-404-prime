# AI Writer Platform — Alt404 Prime

**AI Writer** müxtəlif yerli və xarici texnoloji/rəsmi veb-saytlardan xəbərləri avtomatik toplayan, dublikatları (təkrarları) intellektual şəkildə aşkarlayan, xarici xəbərləri OpenAI API vasitəsilə peşəkar Azərbaycan dilinə adaptasiya edən və birbaşa alt404 saytına **Qaralama (Draft - `published = 0`)** kimi əlavə edən tam avtonom platformadır.

---

## 📁 Qovluq Strukturu (`/ai_writer`)

```
ai_writer/
├── .env                  # MySQL və sistem konfiqurasiyası
├── .env.example          # Nümunə konfiqurasiya faylı
├── database.sql          # AI Writer-in ayrıca MySQL bazasının sxemi və ilkin məlumatlar
├── index.php             # Giriş nöqtəsi (Manager UI-nı təqdim edir)
├── README.md             # Tam təlimat və arxitektura sənədi
│
├── config/
│   └── config.php        # Mühit dəyişənlərinin yüklənməsi və konfiqurasiya
│
├── core/
│   ├── Database.php          # AI Writer və Əsas Veb Sayt üçün PDO singleton və schema manager
│   ├── GrabberInterface.php  # Bütün grabber-lər üçün standart interfeys
│   ├── BaseGrabber.php       # HTTP sorğuları, DOM/XPath analizi və təmizləmə
│   ├── DuplicateDetector.php # İntellektual dublikat aşkarlama mühərriki
│   ├── AiRewriter.php        # OpenAI Chat API ilə Azərbaycan dilinə rewrite mühərriki
│   ├── Publisher.php         # Web articles cədvəlinə Draft (published=0) kimi əlavə edən modul
│   ├── GrabberManager.php    # Mənbələrin idarəsi, növbə və emal koordinatoru
│   └── grabbers/             # 12 Mənbə üçün fərdi grabber sinifləri
│       ├── DonanimHaberGrabber.php # DonanımHaber (AI Rewrite)
│       ├── KaldataGrabber.php      # Kaldata IT (AI Rewrite)
│       ├── LogGrabber.php          # Log.com.tr (AI Rewrite)
│       ├── ReutersGrabber.php      # Reuters Tech (AI Rewrite)
│       ├── DexertoGrabber.php      # Dexerto - Latest News bloku (AI Rewrite)
│       ├── CnetGrabber.php         # CNET (AI Rewrite)
│       ├── EngadgetGrabber.php     # Engadget (AI Rewrite)
│       ├── AzertagGrabber.php      # Azərtac - İqtisadiyyat/Texnologiya (Rəsmi, dəyişiksiz)
│       ├── IddaGrabber.php         # İRİA / IDDA (Rəsmi, dəyişiksiz)
│       ├── AynaGrabber.php         # AYNA (Rəsmi, dəyişiksiz)
│       ├── MincomGrabber.php       # RİNN / Mincom (Rəsmi, dəyişiksiz)
│       └── CertGrabber.php         # CERT.az (Rəsmi, dəyişiksiz)
│
├── api/
│   └── index.php         # Vue.js Manager üçün REST API (Stats, Sources, News, Settings)
│
├── cli/
│   ├── grab.php          # Konsoldan xəbərləri toplamaq üçün əmr
│   ├── process.php       # Konsoldan AI Rewrite və Qaralama etmək üçün əmr
│   └── cron.php          # Crontab üçün avtomatik dövri tapşırıq skripti
│
└── manager/              # Vue 3 + Vite əsaslı müasir İdarəetmə Paneli (UI)
    ├── .env
    ├── .env.example
    ├── package.json
    ├── vite.config.js
    ├── index.html
    └── src/
        ├── main.js
        ├── App.vue
        ├── api/index.js
        ├── stores/aiWriter.js
        ├── router/index.js
        ├── components/
        │   └── ArticleModal.vue # Orijinal vs Rewrite müqayisə pəncərəsi
        └── views/
            ├── Dashboard.vue    # Statistika və ümumi baxış
            ├── Sources.vue      # 12 Mənbənin idarəsi və yenilənmə intervalları
            ├── NewsFeed.vue     # Toplanmış xəbərlər, dublikat və status filtrləri
            └── Settings.vue     # OpenAI açarı, model və dublikat həddi tənzimləmələri
```

---

## 🌐 12 Mənbə və İlkin Konfiqurasiya

| # | Mənbə | Link | Rewrite Statusu | Default İnterval |
|---|---|---|---|---|
| 1 | **DonanımHaber** | https://www.donanimhaber.com/teknoloji-haberleri | AI Rewrite (Aktiv) | 30 dəq |
| 2 | **Kaldata** | https://www.kaldata.com/it-новини | AI Rewrite (Aktiv) | 45 dəq |
| 3 | **Log.com.tr** | https://www.log.com.tr/teknoloji-haberleri/ | AI Rewrite (Aktiv) | 30 dəq |
| 4 | **Reuters Technology** | https://www.reuters.com/technology/ | AI Rewrite (Aktiv) | 60 dəq |
| 5 | **Dexerto** | https://www.dexerto.com/ *(Latest News bloku)* | AI Rewrite (Aktiv) | 30 dəq |
| 6 | **CNET** | https://www.cnet.com/news/ | AI Rewrite (Aktiv) | 45 dəq |
| 7 | **Engadget** | https://www.engadget.com/latest/ | AI Rewrite (Aktiv) | 45 dəq |
| 8 | **Azərtac** | https://azertag.az/bolme/economy | Rəsmi (Birbaşa kopyalanır) | 30 dəq |
| 9 | **İRİA (IDDA)** | https://idda.az/az/xeberler | Rəsmi (Birbaşa kopyalanır) | 60 dəq |
| 10 | **AYNA** | https://ayna.gov.az/az/news | Rəsmi (Birbaşa kopyalanır) | 60 dəq |
| 11 | **RİNN (Mincom)** | https://mincom.gov.az/az/media/xeberler | Rəsmi (Birbaşa kopyalanır) | 60 dəq |
| 12 | **CERT.az** | https://www.cert.az/news/23 | Rəsmi (Birbaşa kopyalanır) | 60 dəq |

---

## ⚙️ Quraşdırma və Məlumat Bazası

### 1. `.env` Faylının Doldurulması
`ai_writer/.env` faylında MySQL qoşulma məlumatlarınızı daxil edin:
```env
# AI Writer-in ayrıca bazası
AI_DB_HOST=localhost
AI_DB_PORT=3306
AI_DB_NAME=alt404_ai_writer
AI_DB_USER=root
AI_DB_PASS=parolunuz

# Əsas veb saytın bazası (qaralamaların göndəriləcəyi baza)
WEB_DB_HOST=localhost
WEB_DB_PORT=3306
WEB_DB_NAME=alt404
WEB_DB_USER=root
WEB_DB_PASS=parolunuz
```

### 2. Bazanın İlkinleşdirilməsi
Əgər `alt404_ai_writer` bazasını əl ilə yaratmaq istəsəniz:
```bash
mysql -u root -p < ai_writer/database.sql
```
*(Qeyd: `Database.php` həmçinin avtomatik olaraq bazanı və cədvəlləri yarada bilir).*

---

## 🖥️ Manager UI-nın İşə Salınması

Manager UI müasir **Vue 3** və **Vite** ilə yığılmışdır.

### Dev Server ilə işə salmaq:
```bash
cd ai_writer/manager
npm run dev
```
Brauzerdə: **`http://localhost:5174`** açılacaq.

### İlkin Giriş Məlumatları (Default Login):
AI Writer tək səviyyəli istifadəçi idarəetməsi ilə qorunur (açıq qeydiyyat yoxdur):
- **İstifadəçi adı**: `admin`
- **Şifrə**: `admin`

Daxil olduqdan sonra **İstifadəçilər** (`/users`) bölməsindən yeni istifadəçilər əlavə edə, mövcud istifadəçilərin şifrəsini dəyişə və ya silə bilərsiniz.

### Production Build:
Artıq `ai_writer/manager/dist` qovluğunda komplayasiya olunmuşdur. Veb server vasitəsilə `http://localhost:8000/ai_writer/` ünvanına daxil olduqda birbaşa Manager UI açılır.

---

## 🚀 CLI Əmrləri və Cron İşləri

### 1. Xəbərləri Toplamaq (Grabber):
```bash
# Bütün aktiv mənbələrdən topla (intervalları nəzərə alaraq)
php ai_writer/cli/grab.php

# İntervalları gözləmədən məcburi (force) topla
php ai_writer/cli/grab.php --force

# Yalnız müəyyən bir mənbədən topla
php ai_writer/cli/grab.php --source=cnet
```

### 2. Toplanan Xəbərləri AI ilə Yazmaq və Qaralama Kimi Paylaşmaq:
```bash
# Növbəti 5 yeni xəbəri emal et
php ai_writer/cli/process.php --limit=5

# Konkret bir xəbər ID-sini emal et
php ai_writer/cli/process.php --id=14
```

### 3. Avtomatik Cron Tapşırığı:
Crontab-a hər 15 dəqiqədən bir icra olunmaq üçün əlavə edə bilərsiniz:
```cron
*/15 * * * * php /Users/bahramhasanov/_PHP/alt-404-prime/ai_writer/cli/cron.php >> /Users/bahramhasanov/_PHP/alt-404-prime/ai_writer/cron.log 2>&1
```

---

## 🔍 İntellektual Dublikat (Təkrar) Aşkarlanması

Sistem fərqli saytlarda çıxan eyni hadisə barədə xəbərləri 3 səviyyədə yoxlayır:
1. **URL və Xarici İD yoxlanışı**: Eyni URL-in təkrar toplanmasının qarşısını alır.
2. **Kross-Mənbə Başlıq və Token Bənzərliyi**: Bütün mənbələrdən son 14 gün ərzində toplanmış xəbərlərlə Jaccard və simmetrik leksik bənzərlik hesablanır.
3. **Saytdakı Mövcud Məqalələrlə Yoxlanış**: `alt404.articles` cədvəlində artıq mövcud olan son məqalələrlə müqayisə edilir.

Bənzərlik həddi (default **70%**) keçilərsə, xəbər `duplicate` statusu alır və avtomatik qaralamaya göndərilmir. İstifadəçi Manager UI-dan istənilən vaxt təkrar işarəsini ləğv edə və ya məcburi generasiya edə bilər.
