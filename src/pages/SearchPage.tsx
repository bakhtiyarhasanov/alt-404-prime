import { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Search } from 'lucide-react';
import ArticleCard from '../components/ArticleCard';
import { useApp } from '../lib/appContext';

export default function SearchPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const initialQ = searchParams.get('q') || '';
  const [query, setQuery] = useState(initialQ);
  const { articles } = useApp();

  useEffect(() => {
    setQuery(searchParams.get('q') || '');
  }, [searchParams]);

  const results = query.length > 1
    ? articles.filter((a) =>
        a.published && (
          a.title.toLowerCase().includes(query.toLowerCase()) ||
          a.excerpt.toLowerCase().includes(query.toLowerCase()) ||
          a.tags.some((t) => t.toLowerCase().includes(query.toLowerCase()))
        )
      )
    : [];

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (query.trim()) setSearchParams({ q: query.trim() });
  };

  return (
    <main className="dot-matrix min-h-screen bg-canvas">
      <div className="max-w-5xl mx-auto px-4 pt-28 pb-16">
        <div className="mb-10">
          <h1 className="font-mono text-3xl font-bold text-text-primary mb-6">Axtarış</h1>
          <form onSubmit={handleSubmit} className="flex items-center gap-3">
            <div className="flex-1 relative">
              <Search size={15} className="absolute left-4 top-1/2 -translate-y-1/2 text-text-muted" strokeWidth={1.5} />
              <input
                type="text"
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder="Xəbər, mövzu, hashtag axtar..."
                className="w-full bg-surface border border-border rounded-xl pl-10 pr-4 py-3 text-sm text-text-primary placeholder-text-muted outline-none focus:border-brand/50 font-sans transition-colors shadow-sm"
              />
            </div>
            <button type="submit" className="btn-primary px-6 py-3">Axtar</button>
          </form>
        </div>

        {query.length > 1 && (
          <div>
            <p className="font-mono text-[11px] text-text-muted mb-6">
              "{query}" üçün <strong className="text-text-secondary">{results.length}</strong> nəticə
            </p>
            {results.length === 0 ? (
              <div className="text-center py-20">
                <p className="font-mono text-sm text-text-muted">Nəticə tapılmadı.</p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 animate-slide-up">
                {results.map((article) => (
                  <ArticleCard key={article.id} article={article} />
                ))}
              </div>
            )}
          </div>
        )}
      </div>
    </main>
  );
}
