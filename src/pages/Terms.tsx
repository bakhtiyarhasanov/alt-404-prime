const sections = [
  {
    title: '1. Şərtlərə Dəyişikliklər',
    body: 'alt404.com, istənilən vaxt bu Şərtlərdə dəyişiklik etmək və ya onları yeniləmək hüququnu özündə saxlayır. Yenilənmiş Şərtlər Veb-saytda dərc edildiyi andan etibarən qüvvəyə minir. Şərtlərin dəyişdirilməsindən sonra Veb-saytdan istifadənin davam etdirilməsi yeni Şərtlərin qəbul edildiyi mənasına gəlir.',
  },
  {
    title: '2. Əqli Mülkiyyət Hüquqları',
    body: 'Veb-saytda yerləşdirilən bütün materiallar, o cümlədən xəbərlər, məqalələr, təhlillər, fotoşəkillər, videolar, loqolar, qrafiklər və dizayn elementləri alt404 və ya müvafiq hüquq sahiblərinə məxsusdur və müəllif hüquqları ilə qorunur. Məzmunun icazəsiz kopyalanması, çoxaldılması, yenidən yayımlanması, daxil edilməsi və ya kommersiya məqsədləri ilə istifadəsi qəti qadağandır. İstisna hallarda, mətndə dəyişiklik edilməməsi və alt404.com-a aktiv, işlək link verilməsi şərti ilə materiallardan istifadəyə icazə verilə bilər.',
  },
  {
    title: '3. Məzmun Dəqiqliyi və Məsuliyyət',
    body: 'Komandamız materialların dərci zamanı məlumatların dəqiqliyini yoxlamağa çalışsa da, Veb-saytda yer alan məlumatların mütləq doğruluğuna zəmanət verilmir. alt404, materialların dərci zamanı texniki və ya redaksiya xətalarına görə məsuliyyət daşımır. İstifadəçi məlumatlardan öz riski altında istifadə edir.',
  },
  {
    title: '4. Üçüncü Tərəf Keçidləri',
    body: 'Saytımızda başqa veb-saytlara və ya resurslara keçidlər (linklər) ola bilər. alt404 həmin saytların məzmununa, təhlükəsizliyinə və ya məxfilik siyasətinə görə heç bir məsuliyyət daşımır. Keçidlərin verilməsi tövsiyə xarakteri daşımır.',
  },
  {
    title: '5. Kommersiya İstifadəsi',
    body: 'Saytdakı materiallardan kommersiya məqsədləri üçün istifadə etmək (reklam, partnyorluq, məlumat bazası yaratmaq və s.) qəti qadağandır. Bu cür əməkdaşlıqlar üçün alt404 ilə əvvəlcədən yazılı razılıq alınmalıdır.',
  },
  {
    title: '6. Qanunvericilik və Yurisdiksiya',
    body: 'Bu Şərtlərin təfsiri və tətbiqi Azərbaycan Respublikasının qanunvericiliyinə əsasən tənzimlənir. Müvafiq mübahisələr Azərbaycan Respublikasının müvafiq məhkəmələri tərəfindən həll edilir.',
  },
];

export default function Terms() {
  return (
    <main className="min-h-screen bg-canvas pt-28 pb-20">
      <div className="max-w-3xl mx-auto py-12 px-4">
        <h1 className="font-mono text-3xl font-bold text-text-primary mb-4">alt404 İstifadə Şərtləri</h1>
        <p className="font-sans text-[15px] text-text-secondary leading-relaxed mb-10">
          Aşağıdakı İstifadə Şərtləri ("Şərtlər"), alt404.com ("Veb-sayt" və ya "Platforma") tərəfindən
          istifadəçilərə təqdim olunan xidmət və məlumatlardan istifadə qaydalarını tənzimləyir.
          Veb-sayta daxil olmaqla, hər bir istifadəçi bu Şərtləri tam şəkildə qəbul etmiş sayılır.
        </p>
        <div className="space-y-5">
          {sections.map((s) => (
            <div key={s.title} className="rounded-xl border border-border bg-surface p-6">
              <h2 className="font-mono text-sm font-semibold text-text-primary mb-2">{s.title}</h2>
              <p className="font-sans text-[14px] text-text-secondary leading-relaxed">{s.body}</p>
            </div>
          ))}
        </div>
      </div>
    </main>
  );
}
