import { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { ChevronRight } from 'lucide-react';
import ArticleCard from '../components/ArticleCard';
import AdZone from '../components/AdZone';
import { useApp } from '../lib/appContext';
import SeoHead from '../components/SeoHead';
import { getCategoryLabel, getVisibleCategories } from '../lib/categoryConfig';

export default function CategoryPage() {
  const { categorySlug: slug } = useParams<{ categorySlug: string }>();
  const { articles: allArticles, categories } = useApp();
  const [activeTag, setActiveTag] = useState<string | null>(null);

  const category = slug ? categories.find((c) => c.slug === slug) : undefined;
  const label = slug ? getCategoryLabel(categories, slug) : 'Kateqoriya';
  const seoTitle = category?.metaTitle || `${label} | alt404`;
  const seoDescription = category?.metaDescription || `${label} xəbərləri. alt404.az`;

  const articles = allArticles.filter((a) => a.published && a.category === slug);
  const allTags = Array.from(new Set(articles.flatMap((a) => a.tags)));
  const curatedTags = category?.curatedTags || [];
  const visibleTags = curatedTags.length ? allTags.filter((t) => curatedTags.includes(t)) : allTags;
  const filtered = activeTag ? articles.filter((a) => a.tags.includes(activeTag)) : articles;

  const visibleCategories = getVisibleCategories(categories).filter((c) => c.slug !== slug);

  return (
    <>
      <SeoHead title={seoTitle} description={seoDescription} />
      <main className="dot-matrix min-h-screen bg-canvas">
      <div className="max-w-5xl mx-auto px-4 pt-28 pb-16">

        {/* Leaderboard */}
        <div className="mb-8 h-[90px]">
          <AdZone id="leaderboard" className="h-[90px]" />
        </div>

        {/* Breadcrumb */}
        <div className="flex items-center gap-2 text-[11px] font-mono text-text-muted mb-8">
          <Link to="/" className="hover:text-text-primary transition-colors">Ana Səhifə</Link>
          <ChevronRight size={11} />
          <span className="text-text-secondary">{label}</span>
        </div>

        {/* Header */}
        <div className="mb-8">
          <div className="section-rule mb-1">
            <h1 className="font-mono text-3xl font-bold text-text-primary">{label}</h1>
          </div>
          <p className="font-sans text-sm text-text-secondary ml-[15px]">{articles.length} xəbər</p>
        </div>

        {/* Other categories */}
        <div className="flex items-center gap-2 overflow-x-auto pb-3 mb-8">
          {visibleCategories.map((cat) => (
            <Link
              key={cat.slug}
              to={`/${cat.slug}`}
              className="flex-shrink-0 font-mono text-[11px] px-4 py-1.5 rounded-full border border-border bg-surface hover:border-border-strong text-text-muted hover:text-text-secondary transition-all shadow-sm"
            >
              {cat.label}
            </Link>
          ))}
        </div>

        {/* Tag filter */}
        {visibleTags.length > 0 && (
          <div className="flex flex-wrap gap-1.5 mb-9">
            <button onClick={() => setActiveTag(null)} className={`tag-pill ${!activeTag ? 'active' : ''}`}>#Hamısı</button>
            {visibleTags.map((tag) => (
              <button key={tag} onClick={() => setActiveTag(activeTag === tag ? null : tag)} className={`tag-pill ${activeTag === tag ? 'active' : ''}`}>
                #{tag}
              </button>
            ))}
          </div>
        )}

        {filtered.length === 0 ? (
          <div className="text-center py-24">
            <p className="font-mono text-sm text-text-muted">Bu kateqoriyada hələ xəbər yoxdur.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 animate-slide-up">
            {filtered.map((article) => (
              <ArticleCard key={article.id} article={article} />
            ))}
          </div>
        )}
      </div>
      </main>
    </>
  );
}
