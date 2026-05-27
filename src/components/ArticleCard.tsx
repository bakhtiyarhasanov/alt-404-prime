import { Link } from 'react-router-dom';
import { Clock } from 'lucide-react';
import type { Article } from '../lib/supabase';
import { formatDate } from '../lib/utils';
import { useApp } from '../lib/appContext';

interface Props {
  article: Article;
  variant?: 'default' | 'featured' | 'featured-small' | 'compact' | 'horizontal';
}

function articleUrl(article: Article) {
  return `/${article.category}/${article.slug}`;
}

export default function ArticleCard({ article, variant = 'default' }: Props) {
  const { categories } = useApp();
  const cat = categories.find((c) => c.slug === article.category)?.label || article.category;

  if (variant === 'featured') {
    return (
      <Link
        to={articleUrl(article)}
        className="group relative block overflow-hidden rounded-2xl h-full"
        style={{ minHeight: 340 }}
      >
        <img
          src={article.image_url}
          alt={article.title}
          className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
        />
        <div className="absolute inset-0" style={{
          background: 'linear-gradient(160deg, transparent 30%, rgba(0,0,0,0.9) 100%)',
        }} />
        <div className="absolute inset-0 dot-matrix-invert opacity-30" />
        <div className="absolute bottom-0 left-0 right-0 p-5 z-10">
          <span className="category-badge mb-3 inline-block">{cat}</span>
          <h2 className="font-mono text-base font-bold text-white leading-snug mb-1.5 group-hover:text-white/90 transition-colors line-clamp-2">
            {article.title}
          </h2>
          <p className="font-sans text-xs text-white/55">{formatDate(article.created_at)}</p>
        </div>
      </Link>
    );
  }

  if (variant === 'featured-small') {
    return (
      <Link
        to={articleUrl(article)}
        className="group flex items-start gap-3 p-3.5 rounded-xl bg-surface border border-border hover:border-border-strong transition-all duration-200 hover:shadow-card"
      >
        <div className="w-20 h-[60px] rounded-lg overflow-hidden flex-shrink-0 bg-surface-2">
          <img
            src={article.image_url}
            alt={article.title}
            className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
          />
        </div>
        <div className="flex-1 min-w-0">
          <span className="category-badge text-[9px] mb-1.5 inline-block">{cat}</span>
          <h3 className="font-mono text-[13px] font-semibold text-text-primary leading-snug line-clamp-2 group-hover:text-text-primary transition-colors">
            {article.title}
          </h3>
          <p className="font-mono text-[10px] text-text-muted mt-1">{formatDate(article.created_at)}</p>
        </div>
      </Link>
    );
  }

  if (variant === 'compact') {
    return (
      <Link
        to={articleUrl(article)}
        className="group flex items-start gap-3.5 p-4 rounded-xl card"
      >
        <div className="w-[72px] h-[54px] rounded-lg overflow-hidden flex-shrink-0">
          <img src={article.image_url} alt={article.title} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
        </div>
        <div className="flex-1 min-w-0">
          <span className="category-badge text-[9px] mb-1 inline-block">{cat}</span>
          <h3 className="font-mono text-[13px] font-semibold text-text-primary line-clamp-2 leading-snug group-hover:text-text-primary transition-colors">
            {article.title}
          </h3>
          <p className="font-mono text-[10px] text-text-muted mt-1">{formatDate(article.created_at)}</p>
        </div>
      </Link>
    );
  }

  if (variant === 'horizontal') {
    return (
      <Link
        to={articleUrl(article)}
        className="group flex items-start gap-4 p-4 rounded-2xl card"
      >
        <div className="w-32 h-24 rounded-xl overflow-hidden flex-shrink-0">
          <img src={article.image_url} alt={article.title} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
        </div>
        <div className="flex-1 min-w-0 py-1">
          <span className="category-badge text-[9px] mb-2 inline-block">{cat}</span>
          <h3 className="font-mono text-sm font-bold text-text-primary leading-snug mb-1.5 group-hover:text-text-primary transition-colors line-clamp-2">
            {article.title}
          </h3>
          <p className="font-sans text-xs text-text-secondary line-clamp-2 leading-relaxed mb-2">{article.excerpt}</p>
          <div className="flex items-center gap-3">
            <span className="font-mono text-[10px] text-text-muted">{formatDate(article.created_at)}</span>
            <span className="flex items-center gap-1 font-mono text-[10px] text-text-muted">
              <Clock size={10} />{article.reading_time} dəq
            </span>
          </div>
        </div>
      </Link>
    );
  }

  // default card
  return (
    <Link to={articleUrl(article)} className="group block rounded-2xl card overflow-hidden">
      <div className="aspect-[16/10] overflow-hidden bg-surface-2">
        <img
          src={article.image_url}
          alt={article.title}
          className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
        />
      </div>
      <div className="p-5">
        <div className="flex items-center justify-between mb-3">
          <span className="category-badge">{cat}</span>
          <span className="flex items-center gap-1 text-text-muted font-mono text-[10px]">
            <Clock size={10} />{article.reading_time} dəq
          </span>
        </div>
        <h3 className="font-mono text-[14px] font-bold text-text-primary leading-snug mb-2 group-hover:text-text-primary transition-colors line-clamp-2">
          {article.title}
        </h3>
        <p className="font-sans text-[13px] text-text-secondary leading-relaxed line-clamp-2 mb-4">
          {article.excerpt}
        </p>
        <div className="flex items-center justify-between">
          <div className="flex flex-wrap gap-1.5">
            {article.tags.slice(0, 2).map((tag) => (
              <span key={tag} className="tag-pill">#{tag}</span>
            ))}
          </div>
          <span className="font-mono text-[10px] text-text-muted">{formatDate(article.created_at)}</span>
        </div>
      </div>
    </Link>
  );
}
