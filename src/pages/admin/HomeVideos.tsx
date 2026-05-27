import { useState, useEffect } from 'react';
import { supabase } from '../../lib/supabase';
import { Plus, Trash2, GripVertical } from 'lucide-react';

interface HomeVideo {
  id: string;
  created_at: string;
  title: string;
  youtube_url: string;
  thumbnail_url: string;
  sort_order: number;
}

const emptyForm = { title: '', youtube_url: '', thumbnail_url: '' };

function getYouTubeId(url: string): string {
  const match = url.match(/(?:v=|youtu\.be\/|embed\/|shorts\/)([A-Za-z0-9_-]{11})/);
  return match ? match[1] : '';
}

export default function HomeVideos() {
  const [videos, setVideos] = useState<HomeVideo[]>([]);
  const [loading, setLoading] = useState(true);
  const [form, setForm] = useState(emptyForm);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [deleteId, setDeleteId] = useState<string | null>(null);

  const fetchVideos = async () => {
    setLoading(true);
    const { data } = await supabase
      .from('home_videos')
      .select('*')
      .order('sort_order', { ascending: true });
    if (data) setVideos(data);
    setLoading(false);
  };

  useEffect(() => { fetchVideos(); }, []);

  const handleAdd = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!form.title.trim() || !form.youtube_url.trim()) {
      setError('Başlıq və YouTube URL mütləqdir.');
      return;
    }
    setSaving(true);
    setError('');
    const ytId = getYouTubeId(form.youtube_url);
    const thumbnail_url = form.thumbnail_url.trim() || (ytId ? `https://img.youtube.com/vi/${ytId}/hqdefault.jpg` : '');
    const { error: err } = await supabase.from('home_videos').insert({
      title: form.title.trim(),
      youtube_url: form.youtube_url.trim(),
      thumbnail_url,
      sort_order: videos.length,
    });
    setSaving(false);
    if (err) {
      setError(`Əlavə zamanı xəta baş verdi: ${err.message}`);
    } else {
      setForm(emptyForm);
      fetchVideos();
    }
  };

  const handleDelete = async (id: string) => {
    await supabase.from('home_videos').delete().eq('id', id);
    setDeleteId(null);
    setVideos((prev) => prev.filter((v) => v.id !== id));
  };

  const ytPreviewId = getYouTubeId(form.youtube_url);

  return (
    <div className="space-y-8">
      {/* Add form */}
      <div className="bg-white rounded-2xl border border-border p-6 shadow-sm">
        <p className="font-mono text-sm font-semibold text-text-primary mb-5">Yeni Video Əlavə Et</p>
        <form onSubmit={handleAdd} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block font-mono text-[10px] font-semibold text-text-muted uppercase tracking-widest mb-1.5">Başlıq *</label>
              <input
                type="text"
                required
                value={form.title}
                onChange={(e) => setForm({ ...form, title: e.target.value })}
                placeholder="Video başlığı"
                className="w-full rounded-xl border border-border bg-surface-2 px-4 py-2.5 font-sans text-sm text-text-primary placeholder-text-muted outline-none focus:border-border-strong transition-colors"
              />
            </div>
            <div>
              <label className="block font-mono text-[10px] font-semibold text-text-muted uppercase tracking-widest mb-1.5">YouTube URL *</label>
              <input
                type="text"
                required
                value={form.youtube_url}
                onChange={(e) => setForm({ ...form, youtube_url: e.target.value })}
                placeholder="https://www.youtube.com/watch?v=..."
                className="w-full rounded-xl border border-border bg-surface-2 px-4 py-2.5 font-sans text-sm text-text-primary placeholder-text-muted outline-none focus:border-border-strong transition-colors"
              />
            </div>
          </div>
          <div className="flex gap-4 items-end">
            <div className="flex-1">
              <label className="block font-mono text-[10px] font-semibold text-text-muted uppercase tracking-widest mb-1.5">Thumbnail URL (boş buraxılsa avtomatik)</label>
              <input
                type="text"
                value={form.thumbnail_url}
                onChange={(e) => setForm({ ...form, thumbnail_url: e.target.value })}
                placeholder="https://..."
                className="w-full rounded-xl border border-border bg-surface-2 px-4 py-2.5 font-sans text-sm text-text-primary placeholder-text-muted outline-none focus:border-border-strong transition-colors"
              />
            </div>
            {ytPreviewId && (
              <div className="flex-shrink-0 w-20 h-14 rounded-lg overflow-hidden bg-surface-2 border border-border">
                <img
                  src={`https://img.youtube.com/vi/${ytPreviewId}/hqdefault.jpg`}
                  alt="preview"
                  className="w-full h-full object-cover"
                />
              </div>
            )}
          </div>
          {error && <p className="font-sans text-xs text-red-500">{error}</p>}
          <div className="flex justify-end">
            <button
              type="submit"
              disabled={saving}
              className="flex items-center gap-2 btn-primary"
            >
              <Plus size={14} />
              {saving ? 'Əlavə edilir...' : 'Əlavə et'}
            </button>
          </div>
        </form>
      </div>

      {/* Video list */}
      {loading ? (
        <div className="text-center py-10">
          <span className="font-mono text-sm text-text-muted">Yüklənir...</span>
        </div>
      ) : videos.length === 0 ? (
        <div className="text-center py-10">
          <p className="font-mono text-sm text-text-muted">Video tapılmadı. Yuxarıdan əlavə edin.</p>
        </div>
      ) : (
        <div className="bg-white rounded-2xl border border-border overflow-hidden shadow-sm">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b border-border bg-surface-2">
                  <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest w-8"></th>
                  <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest">Video</th>
                  <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest hidden md:table-cell">YouTube URL</th>
                  <th className="px-5 py-3 text-right font-mono text-xs text-text-muted uppercase tracking-widest">Əməliyyat</th>
                </tr>
              </thead>
              <tbody>
                {videos.map((v) => {
                  const ytId = getYouTubeId(v.youtube_url);
                  const thumb = v.thumbnail_url || (ytId ? `https://img.youtube.com/vi/${ytId}/hqdefault.jpg` : '');
                  return (
                    <tr key={v.id} className="border-b border-border/50 hover:bg-surface-2 transition-colors">
                      <td className="px-3 py-4 text-text-muted">
                        <GripVertical size={14} />
                      </td>
                      <td className="px-5 py-4">
                        <div className="flex items-center gap-3">
                          {thumb && (
                            <div className="w-16 h-11 rounded-lg overflow-hidden flex-shrink-0 bg-surface-2">
                              <img src={thumb} alt="" className="w-full h-full object-cover" />
                            </div>
                          )}
                          <p className="font-mono text-sm font-medium text-text-primary line-clamp-2">{v.title}</p>
                        </div>
                      </td>
                      <td className="px-5 py-4 hidden md:table-cell">
                        <a href={v.youtube_url} target="_blank" rel="noopener noreferrer" className="font-sans text-xs text-text-secondary hover:text-text-primary underline truncate max-w-xs block">
                          {v.youtube_url}
                        </a>
                      </td>
                      <td className="px-5 py-4">
                        <div className="flex items-center gap-2 justify-end">
                          <button
                            onClick={() => setDeleteId(v.id)}
                            className="p-1.5 rounded-lg text-text-muted hover:text-alt-red hover:bg-red-50 transition-colors"
                          >
                            <Trash2 size={13} />
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {deleteId && (
        <div className="fixed inset-0 bg-black/30 backdrop-blur-sm z-50 flex items-center justify-center px-4">
          <div className="bg-white rounded-2xl border border-border shadow-xl p-8 max-w-sm w-full">
            <h3 className="font-mono text-lg font-bold text-text-primary mb-2">Silmək istəyirsiniz?</h3>
            <p className="font-sans text-sm text-text-secondary mb-6">Bu video siyahıdan silinəcək.</p>
            <div className="flex gap-3">
              <button
                onClick={() => handleDelete(deleteId)}
                className="flex-1 bg-alt-red text-white font-mono text-sm font-semibold py-2.5 rounded-lg hover:bg-alt-red-dim transition-colors"
              >
                Sil
              </button>
              <button onClick={() => setDeleteId(null)} className="flex-1 btn-ghost">İmtina</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
