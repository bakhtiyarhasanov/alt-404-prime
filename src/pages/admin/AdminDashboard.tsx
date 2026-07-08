import { useState } from 'react';
import { Plus, CreditCard as Edit2, Trash2, Eye, LogOut, Search, Filter, Megaphone, LayoutDashboard, Images, BarChart2, MessageSquare, PlaySquare, UploadCloud } from 'lucide-react';
import { supabase } from '../../lib/supabase';
import { formatDate } from '../../lib/utils';
import ArticleEditor from './ArticleEditor';
import AdManager from './AdManager';
import MediaLibrary from './MediaLibrary';
import ContactSubmissions from './ContactSubmissions';
import HomeVideos from './HomeVideos';
import { useApp } from '../../lib/appContext';
import type { Article } from '../../lib/supabase';
import CategoriesManager from './CategoriesManager';

type Tab = 'articles' | 'ads' | 'media' | 'submissions' | 'videos' | 'categories';

interface Props {
  onLogout: () => void;
}

export default function AdminDashboard({ onLogout }: Props) {
  const { articles, addArticle, updateArticle, deleteArticle, categories, migrateLocalContent } = useApp();
  const [tab, setTab] = useState<Tab>('articles');
  const [editing, setEditing] = useState<Partial<Article> | null>(null);
  const [isNew, setIsNew] = useState(false);
  const [search, setSearch] = useState('');
  const [filterCat, setFilterCat] = useState('');
  const [deleteId, setDeleteId] = useState<string | null>(null);
  const [syncing, setSyncing] = useState(false);
  const [syncMsg, setSyncMsg] = useState('');
  const hasLocalContent = typeof localStorage !== 'undefined' && !!localStorage.getItem('alt404_state');

  const handleLogout = async () => {
    await supabase.auth.signOut();
    onLogout();
  };

  const handleSync = async () => {
    if (syncing) return;
    setSyncing(true);
    setSyncMsg('');
    try {
      const r = await migrateLocalContent();
      setSyncMsg(`Köçürüldü: ${r.articles} xəbər, ${r.ads} reklam, ${r.categories} bölmə, ${r.media} media.`);
    } catch {
      setSyncMsg('Köçürmə zamanı xəta baş verdi. Konsolu yoxlayın.');
    } finally {
      setSyncing(false);
    }
  };

  const filtered = articles.filter((a) => {
    const matchesSearch = !search || a.title.toLowerCase().includes(search.toLowerCase());
    const matchesCat = !filterCat || a.category === filterCat;
    return matchesSearch && matchesCat;
  });

  const handleSave = async (data: Partial<Article>) => {
    try {
      if (isNew) {
        const newArticle: Article = {
          id: `art_${Date.now()}`,
          title: data.title || '',
          slug: data.slug || '',
          excerpt: data.excerpt || '',
          content: data.content || '',
          category: data.category || 'texnologiya',
          image_url: data.image_url || '',
          tags: data.tags || [],
          featured: data.featured || false,
          published: data.published !== false,
          views: 0,
          versions: [],
          reading_time: data.reading_time || 3,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        };
        await addArticle(newArticle);
      } else if (editing?.id) {
        await updateArticle(editing.id, data);
      }
      setEditing(null);
      setIsNew(false);
    } catch (e) {
      const msg = e instanceof Error ? e.message : 'naməlum xəta';
      alert(`Xəbər saxlanmadı: ${msg}\n\nAdmin kimi daxil olduğunuzdan və Supabase migration-larının tətbiq olunduğundan əmin olun.`);
    }
  };

  const handleDelete = async (id: string) => {
    try {
      await deleteArticle(id);
    } catch {
      /* handled via console */
    }
    setDeleteId(null);
  };

  if (editing !== null || isNew) {
    return (
      <ArticleEditor
        initial={isNew ? {} : editing || {}}
        onSave={handleSave}
        onBack={() => { setEditing(null); setIsNew(false); }}
      />
    );
  }

  const stats = {
    total: articles.length,
    published: articles.filter((a) => a.published).length,
    featured: articles.filter((a) => a.featured).length,
    drafts: articles.filter((a) => !a.published).length,
  };

  return (
    <div className="min-h-screen bg-[#f8f9fa] dot-matrix">
      {/* Top bar */}
      <div className="sticky top-0 z-40 bg-white/90 backdrop-blur-xl border-b border-border px-6 h-14 flex items-center justify-between shadow-sm">
        <div className="flex items-center gap-3">
          <svg width="72" height="24" viewBox="0 0 72 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <text x="0" y="19" fontFamily="Tomorrow, system-ui, sans-serif" fontSize="17" fontWeight="700" fill="#111111" letterSpacing="-0.5">alt</text>
            <text x="28" y="19" fontFamily="Tomorrow, system-ui, sans-serif" fontSize="17" fontWeight="700" fill="#FCDB56" letterSpacing="-0.5">404</text>
          </svg>
          <span className="font-mono text-xs text-text-muted border-l border-border pl-3">Admin</span>
        </div>
        <div className="flex items-center gap-3">
          <a href="/" target="_blank" className="flex items-center gap-1.5 btn-ghost text-xs py-1.5">
            <Eye size={13} />
            Sayta bax
          </a>
          <button onClick={handleLogout} className="flex items-center gap-1.5 text-xs text-text-secondary hover:text-alt-red transition-colors font-mono">
            <LogOut size={14} />
            Çıx
          </button>
        </div>
      </div>

      <div className="max-w-6xl mx-auto px-4 py-8">
        {/* Stats */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
          {[
            { label: 'Cəmi', value: stats.total },
            { label: 'Yayımlanmış', value: stats.published },
            { label: 'Seçilmiş', value: stats.featured },
            { label: 'Qaralama', value: stats.drafts },
          ].map((s) => (
            <div key={s.label} className="bg-white rounded-xl border border-border p-5 shadow-sm">
              <p className="font-mono text-2xl font-bold text-text-primary">{s.value}</p>
              <p className="font-mono text-xs text-text-muted mt-1 uppercase tracking-widest">{s.label}</p>
            </div>
          ))}
        </div>

        {/* Tabs */}
        <div className="flex flex-wrap items-center gap-1 mb-6 bg-white rounded-xl border border-border p-1 w-fit">
          <TabBtn active={tab === 'articles'} onClick={() => setTab('articles')} icon={<LayoutDashboard size={13} />} label="Xəbərlər" />
          <TabBtn active={tab === 'media'} onClick={() => setTab('media')} icon={<Images size={13} />} label="Media" />
          <TabBtn active={tab === 'ads'} onClick={() => setTab('ads')} icon={<Megaphone size={13} />} label="Reklamlar" />
          <TabBtn active={tab === 'videos'} onClick={() => setTab('videos')} icon={<PlaySquare size={13} />} label="Home Videolar" />
          <TabBtn active={tab === 'submissions'} onClick={() => setTab('submissions')} icon={<MessageSquare size={13} />} label="İstifadəçi Müraciətləri" />
          <TabBtn active={tab === 'categories'} onClick={() => setTab('categories')} icon={<BarChart2 size={13} />} label="Bölmələr" />
        </div>

        {tab === 'ads' && <AdManager />}
        {tab === 'media' && <MediaLibrary />}
        {tab === 'videos' && <HomeVideos />}
        {tab === 'submissions' && <ContactSubmissions />}
        {tab === 'categories' && <CategoriesManager />}

        {tab === 'articles' && (
          <>
            <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mb-5">
              <div className="relative flex-1">
                <Search size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-text-muted" />
                <input
                  type="text"
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  placeholder="Başlığa görə axtar..."
                  className="w-full bg-white border border-border rounded-xl pl-9 pr-4 py-2.5 text-sm text-text-primary placeholder-text-muted outline-none focus:border-alt-red/40 transition-colors font-sans"
                />
              </div>
              <div className="relative">
                <Filter size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-text-muted pointer-events-none" />
                <select
                  value={filterCat}
                  onChange={(e) => setFilterCat(e.target.value)}
                  className="bg-white border border-border rounded-xl pl-9 pr-4 py-2.5 text-sm font-mono text-text-primary outline-none focus:border-alt-red/40 transition-colors appearance-none"
                >
                  <option value="">Bütün kateqoriyalar</option>
                  {[...categories].sort((a, b) => a.sortOrder - b.sortOrder).map((c) => (
                    <option key={c.slug} value={c.slug}>
                      {c.label}
                    </option>
                  ))}
                </select>
              </div>
              {hasLocalContent && (
                <button
                  onClick={handleSync}
                  disabled={syncing}
                  title="Bu brauzerdə saxlanan köhnə məzmunu (xəbər, reklam, bölmə) serverə köçür ki, hamı görsün"
                  className="btn-ghost flex items-center gap-2 whitespace-nowrap disabled:opacity-50"
                >
                  <UploadCloud size={14} />
                  {syncing ? 'Köçürülür...' : 'Serverə köçür'}
                </button>
              )}
              <button
                onClick={() => { setIsNew(true); setEditing({}); }}
                className="btn-primary flex items-center gap-2 whitespace-nowrap"
              >
                <Plus size={14} />
                Yeni Xəbər
              </button>
            </div>

            {syncMsg && (
              <p className="mb-4 font-mono text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                {syncMsg}
              </p>
            )}

            <div className="bg-white rounded-2xl border border-border overflow-hidden shadow-sm">
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead>
                    <tr className="border-b border-border bg-surface-2">
                      <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest">Başlıq</th>
                      <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest hidden md:table-cell">Kateqoriya</th>
                      <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest hidden md:table-cell">Status</th>
                      <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest hidden lg:table-cell">Baxış</th>
                      <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest hidden lg:table-cell">Tarix</th>
                      <th className="px-5 py-3 text-right font-mono text-xs text-text-muted uppercase tracking-widest">Əməliyyat</th>
                    </tr>
                  </thead>
                  <tbody>
                    {filtered.map((article) => (
                      <tr key={article.id} className="border-b border-border/50 hover:bg-surface-2 transition-colors">
                        <td className="px-5 py-4">
                          <div className="flex items-start gap-3">
                            {article.image_url && (
                              <img src={article.image_url} alt="" className="w-10 h-8 rounded-md object-cover flex-shrink-0 hidden sm:block" />
                            )}
                            <div>
                              <p className="font-mono text-sm font-medium text-text-primary line-clamp-1">{article.title}</p>
                              <p className="font-mono text-xs text-text-muted mt-0.5 hidden sm:block">/{article.slug}</p>
                            </div>
                          </div>
                        </td>
                        <td className="px-5 py-4 hidden md:table-cell">
                          <span className="category-badge text-[9px]">
                            {categories.find((c) => c.slug === article.category)?.label || article.category}
                          </span>
                        </td>
                        <td className="px-5 py-4 hidden md:table-cell">
                          <div className="flex items-center gap-1.5">
                            <span className={`w-1.5 h-1.5 rounded-full ${article.published ? 'bg-emerald-500' : 'bg-yellow-400'}`} />
                            <span className="font-mono text-xs text-text-muted">
                              {article.published ? 'Yayımlanmış' : 'Qaralama'}
                            </span>
                          </div>
                        </td>
                        <td className="px-5 py-4 hidden lg:table-cell">
                          <div className="flex items-center gap-1.5">
                            <BarChart2 size={11} className="text-text-muted" />
                            <span className="font-mono text-xs text-text-primary font-medium">{(article.views || 0).toLocaleString()}</span>
                          </div>
                        </td>
                        <td className="px-5 py-4 hidden lg:table-cell">
                          <span className="font-mono text-xs text-text-muted">{formatDate(article.created_at)}</span>
                        </td>
                        <td className="px-5 py-4">
                          <div className="flex items-center gap-2 justify-end">
                            <button
                              onClick={() => { setEditing(article); setIsNew(false); }}
                              className="p-1.5 rounded-lg text-text-muted hover:text-text-primary hover:bg-surface-2 transition-colors"
                            >
                              <Edit2 size={13} />
                            </button>
                            <button
                              onClick={() => setDeleteId(article.id)}
                              className="p-1.5 rounded-lg text-text-muted hover:text-alt-red hover:bg-red-50 transition-colors"
                            >
                              <Trash2 size={13} />
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              {filtered.length === 0 && (
                <div className="text-center py-16">
                  <p className="font-mono text-text-muted text-sm">Heç bir xəbər tapılmadı.</p>
                </div>
              )}
            </div>
          </>
        )}
      </div>

      {deleteId && (
        <div className="fixed inset-0 bg-black/30 backdrop-blur-sm z-50 flex items-center justify-center px-4">
          <div className="bg-white rounded-2xl border border-border shadow-xl p-8 max-w-sm w-full">
            <h3 className="font-mono text-lg font-bold text-text-primary mb-2">Silmək istəyirsiniz?</h3>
            <p className="font-sans text-sm text-text-secondary mb-6">
              Bu əməliyyat geri alına bilməz.
            </p>
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

function TabBtn({ active, onClick, icon, label }: { active: boolean; onClick: () => void; icon: React.ReactNode; label: string }) {
  return (
    <button
      onClick={onClick}
      className={`flex items-center gap-1.5 font-mono text-xs font-medium px-4 py-2 rounded-lg transition-all ${
        active ? 'bg-text-primary text-white' : 'text-text-secondary hover:text-text-primary hover:bg-surface-2'
      }`}
    >
      {icon}
      {label}
    </button>
  );
}
