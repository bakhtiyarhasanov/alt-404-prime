import { useState } from 'react';
import { supabase } from '../lib/supabase';

export default function Contact() {
  const [form, setForm] = useState({ ad_soyad: '', email: '', mesaj: '' });
  const [submitted, setSubmitted] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    const { error: err } = await supabase.from('contact_submissions').insert({
      ad_soyad: form.ad_soyad,
      email: form.email,
      mesaj: form.mesaj,
      status: 'new',
    });
    setLoading(false);
    if (err) {
      setError('Göndərmə zamanı xəta baş verdi. Zəhmət olmasa yenidən cəhd edin.');
    } else {
      setSubmitted(true);
    }
  };

  return (
    <main className="min-h-screen bg-canvas pt-28 pb-20">
      <div className="max-w-3xl mx-auto py-12 px-4">
        <h1 className="font-mono text-3xl font-bold text-text-primary mb-8">alt404 Bizimlə Əlaqə</h1>

        <div className="font-sans text-[15px] text-text-secondary leading-relaxed mb-10 space-y-4">
          <p>
            alt404.com, oxucular, partnyorlar və texnologiya həvəskarları ilə davamlı dialoqa inanır.
            Hər hansı bir təklif, irad, xəbər xülasəsi, reklam əməkdaşlığı və ya ümumi sorğu ilə
            bağlı bizə müraciət etmək çox asandır.
          </p>
          <p>
            Aşağıdakı formu dolduraraq müraciətinizi dərhal komandamıza çatdıra bilərsiniz.
            Müraciətlər adətən 24 saat ərzində nəzərdən keçirilir.
          </p>
          <p>
            Bundan əlavə, rəsmi e-poçt ünvanımız və sosial media hesablarımız üzərindən də bizimlə
            birbaşa əlaqə yarada bilərsiniz.
          </p>
          <div className="flex flex-wrap gap-x-6 gap-y-1 font-sans text-[14px]">
            <span><strong className="text-text-primary font-semibold">E-poçt:</strong>{' '}
              <a href="mailto:info@alt404.com" className="hover:underline">info@alt404.com</a>
            </span>
            <span><strong className="text-text-primary font-semibold">Ünvan:</strong> Bakı, Azərbaycan</span>
          </div>
        </div>

        {submitted ? (
          <div className="rounded-2xl border border-border bg-surface p-10 text-center">
            <div className="flex items-center justify-center gap-2 mb-3">
              <span className="live-dot" />
              <span className="font-mono text-[10px] text-text-muted uppercase tracking-widest">Müraciət</span>
            </div>
            <p className="font-mono text-xl font-bold text-text-primary mb-2">GÖNDƏRİLDİ</p>
            <p className="font-sans text-[13px] text-text-secondary">Müraciətiniz qəbul edildi. Ən qısa zamanda sizinlə əlaqə saxlayacağıq.</p>
          </div>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label className="block font-mono text-[11px] font-semibold text-text-muted uppercase tracking-widest mb-2">Ad Soyad</label>
              <input
                type="text"
                required
                value={form.ad_soyad}
                onChange={(e) => setForm({ ...form, ad_soyad: e.target.value })}
                placeholder="Adınız və soyadınız"
                className="w-full rounded-xl border border-border bg-surface-2 px-4 py-3 font-sans text-sm text-text-primary placeholder-text-muted outline-none focus:border-border-strong transition-colors"
              />
            </div>
            <div>
              <label className="block font-mono text-[11px] font-semibold text-text-muted uppercase tracking-widest mb-2">E-poçt</label>
              <input
                type="email"
                required
                value={form.email}
                onChange={(e) => setForm({ ...form, email: e.target.value })}
                placeholder="email@example.com"
                className="w-full rounded-xl border border-border bg-surface-2 px-4 py-3 font-sans text-sm text-text-primary placeholder-text-muted outline-none focus:border-border-strong transition-colors"
              />
            </div>
            <div>
              <label className="block font-mono text-[11px] font-semibold text-text-muted uppercase tracking-widest mb-2">Mesaj</label>
              <textarea
                required
                rows={5}
                value={form.mesaj}
                onChange={(e) => setForm({ ...form, mesaj: e.target.value })}
                placeholder="Müraciətinizi yazın..."
                className="w-full rounded-xl border border-border bg-surface-2 px-4 py-3 font-sans text-sm text-text-primary placeholder-text-muted outline-none focus:border-border-strong transition-colors resize-none"
              />
            </div>
            {error && <p className="font-sans text-sm text-red-500">{error}</p>}
            <button
              type="submit"
              disabled={loading}
              className="rounded-xl bg-text-primary text-canvas font-mono text-sm font-semibold px-6 py-3 hover:opacity-80 transition-opacity disabled:opacity-50"
            >
              {loading ? 'Göndərilir...' : 'Göndər'}
            </button>
          </form>
        )}
      </div>
    </main>
  );
}
