import { useState, useEffect, useRef } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { TrendingUp, ChevronRight, Clock, Calendar, ArrowUpRight, Play, X, ChevronDown } from 'lucide-react';
import ArticleCard from '../components/ArticleCard';
import AdZone from '../components/AdZone';
import { useApp } from '../lib/appContext';
import { supabase } from '../lib/supabase';
import { formatDate } from '../lib/utils';
import { getCategoryLabel, getVisibleCategories } from '../lib/categoryConfig';

interface HomeVideo {
  id: string;
  title: string;
  youtube_url: string;
  thumbnail_url: string;
  sort_order: number;
}

function extractYouTubeID(url: string): string | null {
  if (!url) return null;
  const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})/);
  return (match && match[1]) ? match[1] : null;
}

function VideoGallery() {
  const [videos, setVideos] = useState<HomeVideo[]>([]);
  const [activeVideo, setActiveVideo] = useState<HomeVideo | null>(null);
  const modalRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    supabase
      .from('home_videos')
      .select('*')
      .order('sort_order', { ascending: true })
      .limit(4)
      .then(({ data }) => { if (data) setVideos(data); });
  }, []);

  useEffect(() => {
    function onKey(e: KeyboardEvent) {
      if (e.key === 'Escape') setActiveVideo(null);
    }
    if (activeVideo) document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [activeVideo]);

  const embedId = activeVideo ? extractYouTubeID(activeVideo.youtube_url) : null;

  return (
    <>
      <section className="w-full bg-[#080117] py-10">
        <div className="max-w-5xl mx-auto px-4">
          <div className="section-rule mb-6" style={{ borderColor: 'rgba(252,219,86,0.35)' }}>
            <span className="font-mono text-[11px] font-semibold text-white/60 uppercase tracking-widest">Günün Videoları</span>
          </div>
          {videos.length === 0 ? (
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              {[1, 2, 3, 4].map((i) => (
                <div key={i} className="w-full rounded-xl bg-white/5 border border-white/8" style={{ aspectRatio: '4/3' }}>
                  <div className="w-full h-full flex items-center justify-center">
                    <div className="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center">
                      <Play size={16} className="text-white/30 ml-0.5" fill="currentColor" />
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {videos.map((v) => {
              const ytId = extractYouTubeID(v.youtube_url);
              const thumb = v.thumbnail_url || (ytId ? `https://img.youtube.com/vi/${ytId}/hqdefault.jpg` : '');
              return (
                <button
                  key={v.id}
                  onClick={() => setActiveVideo(v)}
                  className="group w-full text-left focus:outline-none"
                >
                  <div className="relative rounded-xl overflow-hidden bg-white/5" style={{ aspectRatio: '4/3' }}>
                    {thumb && (
                      <img
                        src={thumb}
                        alt={v.title}
                        className="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        style={{ filter: 'brightness(0.72)' }}
                      />
                    )}
                    <div className="absolute inset-0 flex items-center justify-center">
                      <div className="w-12 h-12 rounded-full bg-black/50 backdrop-blur-sm border border-white/20 flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
                        <Play size={18} className="text-white ml-0.5" fill="white" />
                      </div>
                    </div>
                    <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />
                  </div>
                  <p className="mt-2 font-mono text-[11px] font-semibold text-white/80 leading-snug line-clamp-2 group-hover:text-white transition-colors">
                    {v.title}
                  </p>
                </button>
              );
            })}
          </div>
          )}
        </div>
      </section>

      {activeVideo && (
        <div
          className="fixed inset-0 z-50 bg-black/90 backdrop-blur-sm flex items-center justify-center px-4"
          onClick={(e) => { if (e.target === e.currentTarget) setActiveVideo(null); }}
          ref={modalRef}
        >
          <div className="relative w-full max-w-3xl">
            <button
              onClick={() => setActiveVideo(null)}
              className="absolute -top-10 right-0 text-white/60 hover:text-white transition-colors"
            >
              <X size={22} />
            </button>
            <div className="relative rounded-2xl overflow-hidden bg-black" style={{ aspectRatio: '16/9' }}>
              {embedId ? (
                <iframe
                  src={`https://www.youtube.com/embed/${embedId}?autoplay=1`}
                  title={activeVideo.title}
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                  className="absolute inset-0 w-full h-full border-0"
                />
              ) : (
                <div className="absolute inset-0 flex items-center justify-center">
                  <p className="font-mono text-sm text-white/50">Keçərsiz Video Linki</p>
                </div>
              )}
            </div>
            <p className="mt-3 font-mono text-sm text-white/80 text-center">{activeVideo.title}</p>
          </div>
        </div>
      )}
    </>
  );
}

export default function Home() {
  const { articles, categories, loading } = useApp();
  const [searchParams, setSearchParams] = useSearchParams();
  const activeTag = searchParams.get('tag');
  const [visibleCount, setVisibleCount] = useState(() =>
    typeof window !== 'undefined' && window.innerWidth < 768 ? 3 : 6
  );

  const published = articles.filter((a) => a.published);
  const sorted = [...published].sort(
    (a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
  );

  const heroArticle = sorted[0];
  const bottomRowArticles = sorted.slice(1, 6);
  const feedArticles = sorted.slice(6);

  const allTags = Array.from(new Set(published.flatMap((a) => a.tags)));
  const normalizeTag = (t: string) => t.replace(/#/g, '').toLowerCase();
  const filtered = activeTag
    ? sorted.filter((a) => a.tags.some((tag) => normalizeTag(tag) === normalizeTag(activeTag)))
    : feedArticles;

  const isMobile = typeof window !== 'undefined' && window.innerWidth < 768;

  useEffect(() => {
    setVisibleCount(isMobile ? 3 : 6);
  }, [activeTag, isMobile]);

  function handleTagChange(tag: string | null) {
    if (tag) {
      setSearchParams({ tag }, { replace: true });
    } else {
      setSearchParams({}, { replace: true });
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  if (loading && published.length === 0) {
    return (
      <main className="min-h-screen bg-[#080117]">
        <section className="relative w-full overflow-hidden md:[min-height:clamp(520px,75vh,760px)]">
          <div className="relative z-10 max-w-5xl mx-auto px-4 flex flex-col pt-28 pb-16 md:[min-height:inherit]">
            <div className="max-w-3xl md:mt-auto md:pb-24 animate-pulse">
              <div className="flex items-center gap-3 mb-6">
                <div className="h-5 w-24 rounded-full bg-white/10" />
                <div className="h-3 w-20 rounded-full bg-white/10" />
              </div>
              <div className="space-y-3 mb-6">
                <div className="h-9 w-full rounded-lg bg-white/10" />
                <div className="h-9 w-3/4 rounded-lg bg-white/10" />
              </div>
              <div className="space-y-2 mb-8">
                <div className="h-3 w-full rounded bg-white/8" />
                <div className="h-3 w-2/3 rounded bg-white/8" />
              </div>
              <div className="flex gap-2">
                <div className="h-6 w-16 rounded-full bg-white/8" />
                <div className="h-6 w-16 rounded-full bg-white/8" />
                <div className="h-6 w-16 rounded-full bg-white/8" />
              </div>
            </div>
          </div>
        </section>
      </main>
    );
  }

  return (
    <main className="min-h-screen bg-canvas">

      {heroArticle && !activeTag && (
        <>
          <section
            className="relative w-full overflow-hidden md:[min-height:clamp(520px,75vh,760px)]"
          >
            <img
              src={heroArticle.image_url}
              alt={heroArticle.title}
              className="absolute inset-0 w-full h-full object-cover"
              style={{ filter: 'brightness(0.52) saturate(1.05)' }}
            />

            <div
              className="absolute inset-0"
              style={{
                background: [
                  'linear-gradient(to top, #080117 0%, rgba(8,1,23,0.92) 22%, rgba(8,1,23,0.6) 50%, rgba(8,1,23,0.15) 78%, transparent 100%)',
                  'linear-gradient(to right, rgba(8,1,23,0.35) 0%, transparent 65%)',
                ].join(', '),
              }}
            />

            <div className="absolute inset-0 dot-matrix-invert opacity-20 pointer-events-none" />

            <div
              className="relative z-10 max-w-5xl mx-auto px-4 flex flex-col pt-20 md:pt-28 pb-8 md:pb-0 md:[min-height:inherit]"
            >
              <div className="my-6 md:my-8">
                <AdZone id="leaderboard" className="opacity-70 max-w-2xl" />
              </div>

              <div className="max-w-3xl animate-slide-up md:pb-56 md:mt-auto">
                <div className="flex items-center gap-3 mb-5 flex-wrap">
                  <span className="category-badge-dark">{getCategoryLabel(categories, heroArticle.category)}</span>
                  <span className="w-px h-3 bg-white/20" />
                  <span className="flex items-center gap-1.5 font-mono text-[11px] text-white/50">
                    <Calendar size={10} strokeWidth={1.5} />
                    {formatDate(heroArticle.created_at)}
                  </span>
                  <span className="flex items-center gap-1.5 font-mono text-[11px] text-white/50">
                    <Clock size={10} strokeWidth={1.5} />
                    {heroArticle.reading_time} dəq
                  </span>
                </div>

                <Link to={`/${heroArticle.category}/${heroArticle.slug}`} className="group block mb-5">
                  <h1
                    className="font-mono font-bold text-white leading-[1.1] group-hover:text-white/88 transition-colors"
                    style={{
                      fontSize: 'clamp(1.6rem, 4.2vw, 3.4rem)',
                      textShadow: '0 2px 20px rgba(8,1,23,0.6)',
                    }}
                  >
                    {heroArticle.title}
                  </h1>
                </Link>

                <p className="font-sans text-white/60 text-sm md:text-base leading-relaxed mb-6 max-w-xl line-clamp-3 md:line-clamp-none">
                  {heroArticle.excerpt}
                </p>

                <div className="flex items-center gap-3 flex-wrap">
                  {heroArticle.tags.slice(0, 4).map((tag) => (
                    <button
                      key={tag}
                      type="button"
                      onClick={() => handleTagChange(tag)}
                      className="font-mono text-[10px] text-white/45 border border-white/12 hover:border-white/28 hover:text-white/70 px-2.5 py-0.5 rounded-full transition-all cursor-pointer"
                    >
                      #{tag}
                    </button>
                  ))}
                  <Link
                    to={`/${heroArticle.category}/${heroArticle.slug}`}
                    className="ml-auto flex items-center gap-1.5 font-mono text-[11px] font-semibold text-white/80 hover:text-white border border-white/15 hover:border-white/35 px-3 py-1.5 rounded-full transition-all"
                  >
                    Oxu <ArrowUpRight size={12} />
                  </Link>
                </div>
              </div>
            </div>

            {bottomRowArticles.length > 0 && (
              <div className="hidden md:block absolute bottom-0 left-0 right-0 z-20 px-4 pb-5">
                <div className="max-w-5xl mx-auto">
                  <div className="grid grid-cols-5 gap-2.5">
                    {bottomRowArticles.map((article) => (
                      <BottomCard key={article.id} article={article} />
                    ))}
                  </div>
                </div>
              </div>
            )}
          </section>

          {sorted.slice(1, 7).length > 0 && (
            <div
              className="md:hidden px-4 py-4"
              style={{ background: '#080117' }}
            >
              <div className="grid grid-cols-3 gap-2">
                {sorted.slice(1, 7).map((article) => (
                  <BottomCard key={article.id} article={article} compact />
                ))}
              </div>
            </div>
          )}
        </>
      )}

      {/* Video Gallery — full-width dark section */}
      <VideoGallery />

      <div className="dot-matrix">
        <div className="max-w-5xl mx-auto px-4 py-12">
          <div className="flex gap-7 items-start">

            <aside className="hidden xl:block flex-shrink-0">
              <AdZone id="sidebar-left" />
            </aside>

            <div className="flex-1 min-w-0 space-y-10">

            <div>
              <div className="flex items-center gap-2 mb-3">
                <TrendingUp size={13} strokeWidth={2} style={{ color: '#FCDB56' }} />
                <span className="font-mono text-[10.5px] font-semibold text-text-muted uppercase tracking-widest">Trend</span>
              </div>
              <div className="flex flex-wrap gap-1.5">
                <button onClick={() => handleTagChange(null)} className={`tag-pill ${!activeTag ? 'active' : ''}`}>#Hamısı</button>
                {allTags.slice(0, 16).map((tag) => (
                  <button key={tag} onClick={() => handleTagChange(activeTag === tag ? null : tag)} className={`tag-pill ${activeTag === tag ? 'active' : ''}`}>
                    #{tag}
                  </button>
                ))}
              </div>
            </div>

            <div className="flex items-center justify-between">
              <div className="section-rule">
                <span className="font-mono text-[11px] font-semibold text-text-primary uppercase tracking-widest">
                  {activeTag ? `#${activeTag}` : 'Son Xəbərlər'}
                </span>
              </div>
              <span className="font-mono text-[10px] text-text-muted">{filtered.length} xəbər</span>
            </div>

            {filtered.length === 0 ? (
              <div className="text-center py-16">
                <p className="font-mono text-sm text-text-muted">Daha çox xəbər yoxdur.</p>
              </div>
            ) : (
              <>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 stagger animate-slide-up">
                  {filtered.slice(0, Math.min(3, visibleCount)).map((article) => (
                    <ArticleCard key={article.id} article={article} />
                  ))}
                </div>

                {visibleCount > 3 && filtered.slice(3, visibleCount).length > 0 && (
                  <>
                    <AdZone id="inline" className="h-[90px]" />
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                      {filtered.slice(3, visibleCount).map((article) => (
                        <ArticleCard key={article.id} article={article} />
                      ))}
                    </div>
                  </>
                )}

                {visibleCount < filtered.length && (
                  <div className="flex justify-center pt-2 pb-4">
                    <button
                      onClick={() => setVisibleCount((prev) => prev + 3)}
                      className="group flex items-center gap-2 font-mono text-[11px] font-semibold text-text-secondary hover:text-text-primary border border-border hover:border-alt-red/40 bg-white hover:bg-[#FCDB56]/8 px-5 py-2.5 rounded-full transition-all duration-200 shadow-sm"
                    >
                      Daha çox
                      <ChevronDown size={13} className="transition-transform duration-200 group-hover:translate-y-0.5" strokeWidth={2.5} />
                    </button>
                  </div>
                )}
              </>
            )}

            {getVisibleCategories(categories).map((cat) => {
              const catArticles = published.filter((a) => a.category === cat.slug).slice(0, 3);
              if (!catArticles.length) return null;
              return (
                <section key={cat.slug}>
                  <div className="flex items-center justify-between mb-5">
                    <div className="section-rule">
                      <span className="font-mono text-[11px] font-semibold text-text-primary uppercase tracking-widest">{cat.label}</span>
                    </div>
                    <Link to={`/${cat.slug}`} className="flex items-center gap-1 font-mono text-[11px] text-text-secondary hover:text-text-primary transition-colors">
                      Hamısı <ChevronRight size={12} />
                    </Link>
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {catArticles.map((a) => <ArticleCard key={a.id} article={a} />)}
                  </div>
                </section>
              );
            })}
            </div>

            <aside className="hidden xl:block flex-shrink-0">
              <AdZone id="sidebar-right" />
            </aside>

          </div>
        </div>
      </div>
    </main>
  );
}

function BottomCard({ article, compact = false }: { article: { id: string; slug: string; image_url: string; title: string; category: string; created_at: string }; compact?: boolean }) {
  const { categories } = useApp();
  const catLabel = getCategoryLabel(categories, article.category);
  return (
    <Link
      to={`/${article.category}/${article.slug}`}
      className="group rounded-xl overflow-hidden border border-white/10 hover:border-white/20 transition-all duration-300"
    >
      <div className="relative overflow-hidden" style={{ height: compact ? 60 : 78 }}>
        {article.image_url ? (
          <img
            src={article.image_url}
            alt={article.title}
            className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            style={{ filter: 'brightness(0.72) saturate(0.85)' }}
          />
        ) : (
          <div className="w-full h-full" style={{ background: 'rgba(255,255,255,0.04)' }} />
        )}
        <div className="absolute inset-0" style={{ background: 'linear-gradient(to top, rgba(8,1,23,0.75) 0%, transparent 60%)' }} />
        {!compact && (
          <span
            className="absolute bottom-1.5 left-2 category-badge-dark"
            style={{ fontSize: '7.5px', padding: '1.5px 5px', letterSpacing: '0.08em' }}
          >
            {catLabel}
          </span>
        )}
      </div>
      <div
        className={compact ? 'px-2 py-1.5' : 'px-2.5 py-2.5'}
        style={{ background: 'linear-gradient(160deg, #2a1155 0%, #120430 100%)' }}
      >
        <h3 className={`font-mono font-semibold text-white/80 leading-snug group-hover:text-white transition-colors ${compact ? 'text-[9px]' : 'text-[11px]'}`}>
          {article.title}
        </h3>
      </div>
    </Link>
  );
}
