import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import type { Article, ArticleVersion } from './supabase';
import { mockArticles } from '../data/mockArticles';
import type { AdZone } from './adContext';
import type { CategoryConfig } from './categoryConfig';
import { DEFAULT_CATEGORIES } from './categoryConfig';

export type MediaItem = {
  id: string;
  url: string;
  altText: string;
  title: string;
  fileName: string;
  createdAt: string;
};

const DEFAULT_ADS: AdZone[] = [
  {
    id: 'leaderboard',
    label: 'Leaderboard (üst banner)',
    enabled: true,
    imageUrl: 'https://images.pexels.com/photos/1779487/pexels-photo-1779487.jpeg?auto=compress&cs=tinysrgb&w=900&h=90&fit=crop',
    linkUrl: '#',
    width: 900,
    height: 90,
  },
  {
    id: 'sidebar-left',
    label: 'Sol Panel',
    enabled: true,
    imageUrl: 'https://images.pexels.com/photos/3861969/pexels-photo-3861969.jpeg?auto=compress&cs=tinysrgb&w=160&h=600&fit=crop',
    linkUrl: '#',
    width: 160,
    height: 600,
  },
  {
    id: 'sidebar-right',
    label: 'Sağ Panel',
    enabled: true,
    imageUrl: 'https://images.pexels.com/photos/2599244/pexels-photo-2599244.jpeg?auto=compress&cs=tinysrgb&w=160&h=600&fit=crop',
    linkUrl: '#',
    width: 160,
    height: 600,
  },
  {
    id: 'inline',
    label: 'Xəbər içi reklam',
    enabled: true,
    imageUrl: 'https://images.pexels.com/photos/7974/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=728&h=90&fit=crop',
    linkUrl: '#',
    width: 728,
    height: 90,
  },
];

type AppState = {
  articles: Article[];
  ads: AdZone[];
  mediaLibrary: MediaItem[];
  categories: CategoryConfig[];
};

type AppContextType = AppState & {
  setArticles: (articles: Article[]) => void;
  addArticle: (article: Article) => void;
  updateArticle: (id: string, patch: Partial<Article>) => void;
  saveArticleVersion: (id: string, version: ArticleVersion) => void;
  revertToVersion: (id: string, version: ArticleVersion) => void;
  deleteArticle: (id: string) => void;
  incrementViews: (id: string) => void;
  updateAd: (id: string, patch: Partial<AdZone>) => void;
  addMediaItem: (item: MediaItem) => void;
  updateMediaItem: (id: string, patch: Partial<MediaItem>) => void;
  deleteMediaItem: (id: string) => void;

  upsertCategory: (category: CategoryConfig) => void;
  updateCategory: (slug: string, patch: Partial<CategoryConfig>) => void;
  deleteCategory: (slug: string) => void;
};

const AppContext = createContext<AppContextType | null>(null);

function ensureViews(articles: Article[]): Article[] {
  return articles.map((a) => ({
    ...a,
    views: a.views ?? 0,
    versions: a.versions ?? [],
    category: a.category === 'elm-sosial' ? 'elm-gundem' : a.category,
  }));
}

function loadState(): AppState {
  try {
    const raw = localStorage.getItem('alt404_state');
    if (raw) {
      const parsed = JSON.parse(raw) as AppState;
      return {
        articles: ensureViews(parsed.articles?.length ? parsed.articles : mockArticles),
        ads: parsed.ads?.length ? parsed.ads : DEFAULT_ADS,
        mediaLibrary: parsed.mediaLibrary || [],
        categories: parsed.categories?.length ? parsed.categories : DEFAULT_CATEGORIES,
      };
    }
  } catch {
    // ignore
  }
  return { articles: ensureViews(mockArticles), ads: DEFAULT_ADS, mediaLibrary: [], categories: DEFAULT_CATEGORIES };
}

export function AppProvider({ children }: { children: React.ReactNode }) {
  const [state, setState] = useState<AppState>(loadState);

  const persist = useCallback((next: AppState) => {
    try {
      const serialized = JSON.stringify(next);
      localStorage.setItem('alt404_state', serialized);
      // Broadcast to same-tab listeners (storage event only fires in other tabs)
      window.dispatchEvent(new CustomEvent('alt404_state_update', { detail: next }));
    } catch {
      // localStorage quota exceeded — keep in-memory state anyway
      console.warn('alt404_state could not be persisted (likely storage quota).');
    }
    return next;
  }, []);

  const updateState = useCallback((updater: (prev: AppState) => AppState) => {
    setState((prev) => persist(updater(prev)));
  }, [persist]);

  useEffect(() => {
    const handler = (e: StorageEvent) => {
      if (e.key === 'alt404_state' && e.newValue) {
        try {
          setState(JSON.parse(e.newValue));
        } catch {
          // ignore
        }
      }
    };
    window.addEventListener('storage', handler);
    return () => window.removeEventListener('storage', handler);
  }, []);

  const setArticles = (articles: Article[]) => updateState((prev) => ({ ...prev, articles }));

  const addArticle = (article: Article) =>
    updateState((prev) => ({ ...prev, articles: [{ ...article, views: 0, versions: [] }, ...prev.articles] }));

  const updateArticle = (id: string, patch: Partial<Article>) =>
    updateState((prev) => ({
      ...prev,
      articles: prev.articles.map((a) =>
        a.id === id ? { ...a, ...patch, updated_at: new Date().toISOString() } : a
      ),
    }));

  const saveArticleVersion = (id: string, version: ArticleVersion) =>
    updateState((prev) => ({
      ...prev,
      articles: prev.articles.map((a) => {
        if (a.id !== id) return a;
        const existing = a.versions || [];
        // Keep last 10 versions
        const versions = [version, ...existing].slice(0, 10);
        return { ...a, versions };
      }),
    }));

  const revertToVersion = (id: string, version: ArticleVersion) =>
    updateState((prev) => ({
      ...prev,
      articles: prev.articles.map((a) =>
        a.id === id
          ? { ...a, title: version.title, excerpt: version.excerpt, content: version.content, updated_at: new Date().toISOString() }
          : a
      ),
    }));

  const deleteArticle = (id: string) =>
    updateState((prev) => ({ ...prev, articles: prev.articles.filter((a) => a.id !== id) }));

  const incrementViews = (id: string) =>
    updateState((prev) => ({
      ...prev,
      articles: prev.articles.map((a) =>
        a.id === id ? { ...a, views: (a.views || 0) + 1 } : a
      ),
    }));

  const updateAd = (id: string, patch: Partial<AdZone>) =>
    updateState((prev) => ({
      ...prev,
      ads: prev.ads.map((ad) => (ad.id === id ? { ...ad, ...patch } : ad)),
    }));

  const addMediaItem = (item: MediaItem) =>
    updateState((prev) => ({ ...prev, mediaLibrary: [item, ...prev.mediaLibrary] }));

  const updateMediaItem = (id: string, patch: Partial<MediaItem>) =>
    updateState((prev) => ({
      ...prev,
      mediaLibrary: prev.mediaLibrary.map((m) => (m.id === id ? { ...m, ...patch } : m)),
    }));

  const deleteMediaItem = (id: string) =>
    updateState((prev) => ({ ...prev, mediaLibrary: prev.mediaLibrary.filter((m) => m.id !== id) }));

  const upsertCategory = (category: CategoryConfig) => {
    updateState((prev) => ({
      ...prev,
      categories: prev.categories.some((c) => c.slug === category.slug)
        ? prev.categories.map((c) => (c.slug === category.slug ? { ...c, ...category } : c))
        : [...prev.categories, category],
    }));
  };

  const updateCategory = (slug: string, patch: Partial<CategoryConfig>) => {
    updateState((prev) => ({
      ...prev,
      categories: prev.categories.map((c) => (c.slug === slug ? { ...c, ...patch, slug: c.slug } : c)),
    }));
  };

  const deleteCategory = (slug: string) => {
    updateState((prev) => ({
      ...prev,
      categories: prev.categories.filter((c) => c.slug !== slug),
    }));
  };

  return (
    <AppContext.Provider
      value={{
        ...state,
        setArticles,
        addArticle,
        updateArticle,
        saveArticleVersion,
        revertToVersion,
        deleteArticle,
        incrementViews,
        updateAd,
        addMediaItem,
        updateMediaItem,
        deleteMediaItem,
        upsertCategory,
        updateCategory,
        deleteCategory,
      }}
    >
      {children}
    </AppContext.Provider>
  );
}

export function useApp() {
  const ctx = useContext(AppContext);
  if (!ctx) throw new Error('useApp must be used within AppProvider');
  return ctx;
}

export function useAd(id: string): AdZone | undefined {
  return useApp().ads.find((a) => a.id === id);
}
