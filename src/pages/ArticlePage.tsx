import { useParams, Link, useNavigate } from 'react-router-dom';
import { Calendar, ArrowLeft, Copy, Check } from 'lucide-react';
import { useState, useEffect, useRef } from 'react';
import { useApp, useAd } from '../lib/appContext';
import { formatDate } from '../lib/utils';
import ArticleCard from '../components/ArticleCard';
import GallerySlider from '../components/GallerySlider';
import SeoHead from '../components/SeoHead';
import { getCategoryLabel } from '../lib/categoryConfig';

interface GalleryImage { url: string; alt: string; }
type ContentSegment =
  | { type: 'html'; html: string }
  | { type: 'gallery'; images: GalleryImage[] }
  | { type: 'inline-ad' };

// Matches the entire gallery placeholder <div> including all nested admin UI inside it.
// The outer div always has data-gallery-b64 and contenteditable="false".
const GALLERY_RE = /<div\s[^>]*data-gallery-b64="([A-Za-z0-9+/=]+)"[^>]*>[\s\S]*?<\/div>/g;

// Strip admin-only buttons that were injected into gallery placeholders so they never
// appear in the public reader view (covers edge-cases where the regex leaves residue).
const ADMIN_BTN_RE = /<button\s[^>]*data-gallery-action[^>]*>[\s\S]*?<\/button>/g;

function parseContentSegments(rawHtml: string, injectAd: boolean): ContentSegment[] {
  const raw: ContentSegment[] = [];
  let last = 0;

  for (const match of rawHtml.matchAll(GALLERY_RE)) {
    const [fullMatch, b64] = match;
    const start = match.index!;

    const before = rawHtml.slice(last, start).replace(ADMIN_BTN_RE, '');
    if (before.trim()) raw.push({ type: 'html', html: before });

    try {
      const json = decodeURIComponent(escape(atob(b64)));
      const images: GalleryImage[] = JSON.parse(json);
      if (images.length) raw.push({ type: 'gallery', images });
    } catch { /* skip malformed */ }

    last = start + fullMatch.length;
  }

  const tail = rawHtml.slice(last).replace(ADMIN_BTN_RE, '');
  if (tail.trim()) raw.push({ type: 'html', html: tail });

  if (!injectAd) return raw;

  // Insert the inline ad after the 2nd html-type segment (or at end if fewer exist)
  const result: ContentSegment[] = [];
  let htmlCount = 0;
  let adInserted = false;
  for (const seg of raw) {
    result.push(seg);
    if (seg.type === 'html') {
      htmlCount++;
      if (htmlCount === 2 && !adInserted) {
        result.push({ type: 'inline-ad' });
        adInserted = true;
      }
    }
  }
  if (!adInserted) result.push({ type: 'inline-ad' });

  return result;
}

export default function ArticlePage() {
  const { categorySlug, postSlug } = useParams<{ categorySlug: string; postSlug: string }>();
  const navigate = useNavigate();
  const { articles, categories, incrementViews, loadArticleContent } = useApp();
  const inlineAd = useAd('inline');
  const [copied, setCopied] = useState(false);
  const article = articles.find(
    (a) => a.slug === postSlug && (!categorySlug || a.category === categorySlug)
  );
  const viewCounted = useRef(false);

  useEffect(() => {
    if (article && !viewCounted.current) {
      viewCounted.current = true;
      incrementViews(article.id);
    }
  }, [article, incrementViews]);

  // The list query omits article bodies for speed; hydrate the full content
  // (and version history) the first time this article is opened.
  const contentRequested = useRef<string | null>(null);
  useEffect(() => {
    if (postSlug && article && !article.content && contentRequested.current !== postSlug) {
      contentRequested.current = postSlug;
      loadArticleContent(postSlug);
    }
  }, [postSlug, article, loadArticleContent]);

  const handleCopy = () => {
    navigator.clipboard?.writeText(window.location.href).then(() => {
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    });
  };

  if (!article) {
    return (
      <main className="dot-matrix min-h-screen flex items-center justify-center bg-canvas">
        <div className="text-center">
          <p className="font-mono text-7xl font-bold text-text-muted/30 mb-4">404</p>
          <p className="font-mono text-sm text-text-muted mb-6">Bu xəbər tapılmadı</p>
          <button onClick={() => navigate('/')} className="btn-primary">Ana Səhifəyə Qayıt</button>
        </div>
      </main>
    );
  }

  const related = articles.filter((a) => a.published && a.category === article.category && a.id !== article.id).slice(0, 3);
  const categoryLabel = getCategoryLabel(categories, article.category);

  return (
    <>
      <SeoHead title={`${article.title} | alt404`} description={article.excerpt} />
      <main className="min-h-screen bg-canvas">

      {/* ══════════════════════════════════════════════════════
          FULL-BLEED HERO — merges seamlessly under header
      ══════════════════════════════════════════════════════ */}
      <div className="relative w-full overflow-hidden" style={{ minHeight: 600 }}>

        {/* Cover image */}
        <div className="absolute inset-0">
          {article.image_url ? (
            <img
              src={article.image_url}
              alt={article.title}
              loading="eager"
              decoding="async"
              className="w-full h-full object-cover"
              style={{ filter: 'brightness(0.5) saturate(1.05)' }}
            />
          ) : (
            <div className="w-full h-full bg-gray-900" />
          )}
        </div>

        {/* Multi-layer gradient — strong bottom, subtle top for header see-through */}
        <div className="absolute inset-0" style={{
          background: [
            'linear-gradient(to top, #080117 0%, rgba(8,1,23,0.88) 30%, rgba(8,1,23,0.45) 60%, rgba(8,1,23,0.08) 90%, transparent 100%)',
            'linear-gradient(to right, rgba(8,1,23,0.25) 0%, transparent 70%)',
          ].join(', '),
        }} />

        {/* Dot overlay */}
        <div className="absolute inset-0 dot-matrix-invert opacity-20 pointer-events-none" />

        {/* Accent bar */}
        <div className="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-brand/50 via-brand/12 to-transparent" />

        {/* Hero content */}
        <div className="relative z-10 max-w-3xl mx-auto px-4 flex flex-col justify-end pb-12 pt-32" style={{ minHeight: 600 }}>

          {/* Breadcrumb */}
          <div className="flex items-center gap-2.5 mb-8">
            <button
              onClick={() => navigate(-1)}
              className="flex items-center gap-1.5 text-white/50 hover:text-white/90 transition-colors font-mono text-[11px] group"
            >
              <ArrowLeft size={12} className="group-hover:-translate-x-0.5 transition-transform" />
              Geri
            </button>
            <span className="text-white/20 font-mono">/</span>
            <Link
              to={`/${article.category}`}
              className="font-mono text-[11px] text-white/50 hover:text-white/90 transition-colors"
            >
              {categoryLabel}
            </Link>
          </div>

          {/* Article header */}
          <div className="animate-slide-up">
            <div className="flex items-center gap-2.5 mb-5 flex-wrap">
              <span className="category-badge-dark">{categoryLabel}</span>
              {article.featured && (
                <span className="font-mono text-[9px] font-bold px-2 py-0.5 rounded-md bg-white/8 border border-white/12 text-white/55 uppercase tracking-widest">
                  Seçilmiş
                </span>
              )}
            </div>

            <h1
              className="font-mono font-bold text-white mb-5 leading-[1.1]"
              style={{
                fontSize: 'clamp(1.55rem, 3.5vw, 2.75rem)',
                textShadow: '0 2px 24px rgba(0,0,0,0.5)',
              }}
            >
              {article.title}
            </h1>

            <p className="font-sans text-white/60 text-[15px] leading-relaxed mb-6 max-w-2xl hidden md:block">
              {article.excerpt}
            </p>

            <div className="flex flex-wrap items-center gap-4">
              <div className="flex items-center gap-1.5 font-mono text-[11px] text-white/45">
                <Calendar size={11} strokeWidth={1.5} />
                {formatDate(article.created_at)}
              </div>
              <div className="flex items-center gap-2 flex-wrap">
                {article.tags.slice(0, 4).map((tag) => (
                  <span key={tag} className="font-mono text-[10px] text-white/35 border border-white/10 px-2 py-0.5 rounded-full">
                    #{tag}
                  </span>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* ══════════════════════════════════════════════════════
          READING BODY
      ══════════════════════════════════════════════════════ */}
      <div className="dot-matrix">
        <div className="max-w-2xl mx-auto px-4 py-12">

          {/* Article body card */}
          <article className="bg-surface rounded-2xl border border-border shadow-card overflow-hidden">
            {/* Subtle top accent */}
            <div className="h-[2px] bg-gradient-to-r from-brand/60 to-transparent" />
            <div className="px-7 md:px-10 py-9">
              {parseContentSegments(article.content, !!(inlineAd?.enabled)).map((seg, i) => {
                if (seg.type === 'gallery') return <GallerySlider key={i} images={seg.images} />;
                if (seg.type === 'inline-ad' && inlineAd?.enabled) {
                  return (
                    <a
                      key={i}
                      href={inlineAd.linkUrl || '#'}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="relative block overflow-hidden rounded-xl neo-inset group transition-all hover:shadow-card"
                      style={{ margin: '2rem 0', height: 90 }}
                    >
                      {inlineAd.imageUrl ? (
                        <img
                          src={inlineAd.imageUrl}
                          alt="Reklam"
                          className="w-full h-full object-cover opacity-90 group-hover:opacity-100 transition-opacity"
                        />
                      ) : (
                        <div className="w-full h-full flex items-center justify-center">
                          <span className="font-mono text-[10px] text-text-muted uppercase tracking-widest">Reklam</span>
                        </div>
                      )}
                      <span className="ad-label">Reklam</span>
                    </a>
                  );
                }
                return <div key={i} className="prose-reading" dangerouslySetInnerHTML={{ __html: (seg as { type: 'html'; html: string }).html }} />;
              })}
            </div>
          </article>

          {/* Tags */}
          <div className="mt-8 flex flex-wrap gap-2">
            {article.tags.map((tag) => (
              <Link key={tag} to={`/axtar?q=${encodeURIComponent(tag)}`} className="tag-pill">
                #{tag}
              </Link>
            ))}
          </div>

          {/* Share / copy */}
          <div className="mt-6 bg-surface rounded-xl border border-border shadow-card p-5 flex items-center justify-between">
            <div>
              <p className="font-mono text-sm font-semibold text-text-primary">Bu xəbəri paylaşın</p>
              <p className="font-mono text-[10px] text-text-muted mt-0.5">alt404.az</p>
            </div>
            <button
              onClick={handleCopy}
              className={`flex items-center gap-2 font-mono text-xs font-medium px-4 py-2 rounded-lg border transition-all ${
                copied
                  ? 'bg-emerald-50 border-emerald-200 text-emerald-700'
                  : 'btn-ghost'
              }`}
            >
              {copied ? <Check size={13} /> : <Copy size={13} />}
              {copied ? 'Kopyalandı!' : 'Linki Kopyala'}
            </button>
          </div>

          {/* Related articles */}
          {related.length > 0 && (
            <section className="mt-14">
              <div className="section-rule mb-6">
                <span className="font-mono text-[11px] font-semibold text-text-primary uppercase tracking-widest">
                  Əlaqəli Xəbərlər
                </span>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {related.map((a) => <ArticleCard key={a.id} article={a} />)}
              </div>
            </section>
          )}
        </div>
      </div>
      </main>
    </>
  );
}
