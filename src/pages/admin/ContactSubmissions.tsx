import { useState, useEffect } from 'react';
import { supabase } from '../../lib/supabase';
import { X, CheckCircle, Clock, Eye } from 'lucide-react';
import { formatDate } from '../../lib/utils';

interface Submission {
  id: string;
  created_at: string;
  ad_soyad: string;
  email: string;
  mesaj: string;
  status: string;
}

export default function ContactSubmissions() {
  const [items, setItems] = useState<Submission[]>([]);
  const [loading, setLoading] = useState(true);
  const [selected, setSelected] = useState<Submission | null>(null);
  const [filter, setFilter] = useState<'all' | 'new' | 'reviewed'>('all');

  const fetchItems = async () => {
    setLoading(true);
    const { data } = await supabase
      .from('contact_submissions')
      .select('*')
      .order('created_at', { ascending: false });
    if (data) setItems(data);
    setLoading(false);
  };

  useEffect(() => { fetchItems(); }, []);

  const markReviewed = async (id: string) => {
    await supabase.from('contact_submissions').update({ status: 'reviewed' }).eq('id', id);
    setItems((prev) => prev.map((i) => i.id === id ? { ...i, status: 'reviewed' } : i));
    if (selected?.id === id) setSelected((s) => s ? { ...s, status: 'reviewed' } : s);
  };

  const filtered = filter === 'all' ? items : items.filter((i) => i.status === filter);

  return (
    <div>
      {/* Filter tabs */}
      <div className="flex items-center gap-1 mb-5 bg-white rounded-xl border border-border p-1 w-fit">
        {(['all', 'new', 'reviewed'] as const).map((f) => (
          <button
            key={f}
            onClick={() => setFilter(f)}
            className={`flex items-center gap-1.5 font-mono text-xs font-medium px-4 py-2 rounded-lg transition-all ${
              filter === f ? 'bg-text-primary text-white' : 'text-text-secondary hover:text-text-primary hover:bg-surface-2'
            }`}
          >
            {f === 'all' ? 'Hamısı' : f === 'new' ? 'Yeni' : 'Baxılmış'}
            <span className={`text-[10px] px-1.5 py-0.5 rounded-full ${
              filter === f ? 'bg-white/20' : 'bg-surface-2'
            }`}>
              {f === 'all' ? items.length : items.filter((i) => i.status === f).length}
            </span>
          </button>
        ))}
      </div>

      {loading ? (
        <div className="text-center py-16">
          <span className="font-mono text-sm text-text-muted">Yüklənir...</span>
        </div>
      ) : filtered.length === 0 ? (
        <div className="text-center py-16">
          <p className="font-mono text-sm text-text-muted">Müraciət tapılmadı.</p>
        </div>
      ) : (
        <div className="bg-white rounded-2xl border border-border overflow-hidden shadow-sm">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-border bg-surface-2">
                  <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest">Ad Soyad</th>
                  <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest hidden md:table-cell">E-poçt</th>
                  <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest hidden lg:table-cell">Tarix</th>
                  <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest">Status</th>
                  <th className="px-5 py-3 text-right font-mono text-xs text-text-muted uppercase tracking-widest">Əməliyyat</th>
                </tr>
              </thead>
              <tbody>
                {filtered.map((item) => (
                  <tr key={item.id} className="border-b border-border/50 hover:bg-surface-2 transition-colors">
                    <td className="px-5 py-4">
                      <p className="font-mono text-sm font-medium text-text-primary">{item.ad_soyad}</p>
                      <p className="font-mono text-xs text-text-muted mt-0.5 line-clamp-1 max-w-xs">{item.mesaj}</p>
                    </td>
                    <td className="px-5 py-4 hidden md:table-cell">
                      <span className="font-sans text-sm text-text-secondary">{item.email}</span>
                    </td>
                    <td className="px-5 py-4 hidden lg:table-cell">
                      <span className="font-mono text-xs text-text-muted">{formatDate(item.created_at)}</span>
                    </td>
                    <td className="px-5 py-4">
                      <div className="flex items-center gap-1.5">
                        {item.status === 'new' ? (
                          <>
                            <Clock size={11} className="text-amber-500" />
                            <span className="font-mono text-xs text-amber-600">Yeni</span>
                          </>
                        ) : (
                          <>
                            <CheckCircle size={11} className="text-emerald-500" />
                            <span className="font-mono text-xs text-emerald-600">Baxılmış</span>
                          </>
                        )}
                      </div>
                    </td>
                    <td className="px-5 py-4">
                      <div className="flex items-center gap-2 justify-end">
                        <button
                          onClick={() => setSelected(item)}
                          className="p-1.5 rounded-lg text-text-muted hover:text-text-primary hover:bg-surface-2 transition-colors"
                          title="Bax"
                        >
                          <Eye size={13} />
                        </button>
                        {item.status === 'new' && (
                          <button
                            onClick={() => markReviewed(item.id)}
                            className="p-1.5 rounded-lg text-text-muted hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                            title="Baxılmış kimi işarələ"
                          >
                            <CheckCircle size={13} />
                          </button>
                        )}
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Detail modal */}
      {selected && (
        <div className="fixed inset-0 bg-black/30 backdrop-blur-sm z-50 flex items-center justify-center px-4">
          <div className="bg-white rounded-2xl border border-border shadow-xl p-8 max-w-lg w-full">
            <div className="flex items-start justify-between mb-6">
              <div>
                <h3 className="font-mono text-lg font-bold text-text-primary">{selected.ad_soyad}</h3>
                <p className="font-sans text-sm text-text-muted mt-0.5">{selected.email}</p>
              </div>
              <button onClick={() => setSelected(null)} className="p-1.5 rounded-lg text-text-muted hover:text-text-primary hover:bg-surface-2 transition-colors">
                <X size={16} />
              </button>
            </div>
            <div className="bg-surface-2 rounded-xl p-4 mb-5">
              <p className="font-sans text-sm text-text-primary leading-relaxed">{selected.mesaj}</p>
            </div>
            <div className="flex items-center justify-between">
              <span className="font-mono text-xs text-text-muted">{formatDate(selected.created_at)}</span>
              {selected.status === 'new' ? (
                <button
                  onClick={() => markReviewed(selected.id)}
                  className="flex items-center gap-2 bg-emerald-500 text-white font-mono text-xs font-semibold px-4 py-2 rounded-lg hover:bg-emerald-600 transition-colors"
                >
                  <CheckCircle size={13} />
                  Baxılmış kimi işarələ
                </button>
              ) : (
                <div className="flex items-center gap-1.5">
                  <CheckCircle size={13} className="text-emerald-500" />
                  <span className="font-mono text-xs text-emerald-600">Baxılmış</span>
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
