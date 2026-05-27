const sections = [
  {
    title: '1. Təriflər və Çərəz nədir?',
    body: 'Çərəzlər brauzeriniz vasitəsilə cihazınıza (kompüter, mobil telefon, planşet) saxlanılan kiçik mətn fayllarıdır. Onlar Veb-saytın funksionallığını artırmaq, istifadəçi təcrübəsini təhlil etmək və fərdiləşdirilmiş reklamlar göstərmək üçün istifadə olunur.',
  },
  {
    title: '2. Biz hansı çərəzlərdən istifadə edirik?',
    items: [
      {
        sub: 'Zəruri (Funksional) Çərəzlər',
        text: 'Saytın əsas funksiyalarının (naviqasiya, təhlükəsizlik, məzmunun yüklənməsi) işləməsini təmin etmək üçün lazımdır. Onlar brauzerinizdə saxlanılır və dəaktiv edilə bilməz.',
      },
      {
        sub: 'Analitik (Performans) Çərəzlər',
        text: 'Saytın neçə istifadəçi tərəfindən ziyarət edildiyini, ən çox baxılan səhifələri və s. anonim şəkildə izləmək üçün istifadə olunur. Bu məlumatlar saytı təkmilləşdirməyə kömək edir (örnəyin, Google Analytics).',
      },
      {
        sub: 'Reklam və Marketinq Çərəzləri',
        text: 'İstifadəçi maraqlarına uyğun reklamların göstərilməsini və reklam kampaniyalarının səmərəliliyini ölçməyi təmin edir.',
      },
    ],
  },
  {
    title: '3. Üçüncü Tərəf Çərəzləri',
    body: 'alt404, analitika və reklam məqsədləri üçün üçüncü tərəf partnyorları (örnəyin, Google Analytics, reklam şəbəkələri, sosial media plaginləri) ilə əməkdaşlıq edir. Bu partnyorların çərəz istifadəsi onların öz məxfilik siyasətləri ilə tənzimlənir.',
  },
  {
    title: '4. Google Analytics Tətbiqi',
    body: 'Veb-saytımız Google Inc. tərəfindən təqdim olunan Google Analytics xidmətindən istifadə edir. Google Analytics məlumatları analiz etmək məqsədilə cihazınıza çərəzlər yerləşdirir. Google-un çərəz siyasəti barədə ətraflı məlumatı rəsmi saytından əldə edə bilərsiniz.',
  },
  {
    title: '5. Çərəzlərin İdarə Olunması',
    body: 'İstənilən vaxt brauzerinizin parametrlər bölməsindən çərəzləri sıfırlaya və ya tamamilə bloklaya bilərsiniz. Lakin zəruri çərəzlərin dəaktiv edilməsi Veb-saytın bəzi funksiyalarının (örnəyin, axtarış, daxil olma) tam işləməməsinə səbəb ola bilər.',
  },
];

export default function Cookies() {
  return (
    <main className="min-h-screen bg-canvas pt-28 pb-20">
      <div className="max-w-3xl mx-auto py-12 px-4">
        <h1 className="font-mono text-3xl font-bold text-text-primary mb-4">alt404 Çərəzlər barədə Bəyanat</h1>
        <p className="font-sans text-[15px] text-text-secondary leading-relaxed mb-10">
          Çərəzlər (Cookies) siyasətimiz, alt404.com saytını ziyarət etdiyiniz zaman çərəzlərin necə istifadə
          edildiyini ətraflı izah edir. Veb-saytdan istifadə etməyə davam etməklə, bu siyasəti qəbul etmiş olursunuz.
        </p>
        <div className="space-y-5">
          {sections.map((s) => (
            <div key={s.title} className="rounded-xl border border-border bg-surface p-6">
              <h2 className="font-mono text-sm font-semibold text-text-primary mb-2">{s.title}</h2>
              {'body' in s && s.body && (
                <p className="font-sans text-[14px] text-text-secondary leading-relaxed">{s.body}</p>
              )}
              {'items' in s && s.items && (
                <div className="space-y-3">
                  {s.items.map((item) => (
                    <div key={item.sub}>
                      <p className="font-sans text-[13px] font-semibold text-text-primary mb-0.5">{item.sub}</p>
                      <p className="font-sans text-[14px] text-text-secondary leading-relaxed">{item.text}</p>
                    </div>
                  ))}
                </div>
              )}
            </div>
          ))}
        </div>
      </div>
    </main>
  );
}
