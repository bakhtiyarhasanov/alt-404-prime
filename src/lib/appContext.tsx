import { createContext, useContext, useState, useEffect, useCallback, useRef } from 'react';
import { supabase, isSupabaseConfigured } from './supabase';
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
  loading: boolean;
  usingFallbackArticles: boolean;
  refresh: () => Promise<void>;
  migrateLocalContent: () => Promise<{ articles: number; ads: number; categories: number; media: number }>;

  setArticles: (articles: Article[]) => void;
  addArticle: (article: Article) => Promise<void>;
  updateArticle: (id: string, patch: Partial<Article>) => Promise<void>;
  saveArticleVersion: (id: string, version: ArticleVersion) => Promise<void>;
  revertToVersion: (id: string, version: ArticleVersion) => Promise<void>;
  deleteArticle: (id: string) => Promise<void>;
  incrementViews: (id: string) => void;
  updateAd: (id: string, patch: Partial<AdZone>) => Promise<void>;
  addMediaItem: (item: MediaItem) => Promise<void>;
  updateMediaItem: (id: string, patch: Partial<MediaItem>) => Promise<void>;
  deleteMediaItem: (id: string) => Promise<void>;

  upsertCategory: (category: CategoryConfig) => Promise<void>;
  updateCategory: (slug: string, patch: Partial<CategoryConfig>) => Promise<void>;
  deleteCategory: (slug: string) => Promise<void>;
};

const AppContext = createContext<AppContextType | null>(null);

/* ────────────────────────────── mappers ────────────────────────────── */

type ArticleRow = Omit<Article, 'versions'> & { versions: ArticleVersion[] | null };

function rowToArticle(r: ArticleRow): Article {
  return {
    ...r,
    tags: r.tags ?? [],
    views: r.views ?? 0,
    versions: r.versions ?? [],
    category: r.category === 'elm-sosial' ? 'elm-gundem' : r.category,
  };
}

// Only the writable columns of the articles table (no id / timestamps).
function articleToRow(a: Partial<Article>) {
  const row: Record<string, unknown> = {};
  const keys: (keyof Article)[] = [
    'title', 'slug', 'excerpt', 'content', 'category', 'image_url',
    'tags', 'featured', 'published', 'reading_time', 'views', 'versions',
  ];
  for (const k of keys) if (a[k] !== undefined) row[k] = a[k];
  return row;
}

type AdRow = {
  id: string; label: string; enabled: boolean;
  image_url: string; link_url: string; width: number; height: number;
};

function rowToAd(r: AdRow): AdZone {
  return {
    id: r.id,
    label: r.label,
    enabled: r.enabled,
    imageUrl: r.image_url,
    linkUrl: r.link_url,
    width: r.width,
    height: r.height,
  };
}

function adToRow(a: Partial<AdZone>) {
  const row: Record<string, unknown> = {};
  if (a.id !== undefined) row.id = a.id;
  if (a.label !== undefined) row.label = a.label;
  if (a.enabled !== undefined) row.enabled = a.enabled;
  if (a.imageUrl !== undefined) row.image_url = a.imageUrl;
  if (a.linkUrl !== undefined) row.link_url = a.linkUrl;
  if (a.width !== undefined) row.width = a.width;
  if (a.height !== undefined) row.height = a.height;
  return row;
}

type CategoryRow = {
  slug: string; label: string; show_on_site: boolean; sort_order: number;
  parent_slug: string | null; meta_title: string | null;
  meta_description: string | null; curated_tags: string[] | null;
};

function rowToCategory(r: CategoryRow): CategoryConfig {
  return {
    slug: r.slug,
    label: r.label,
    showOnSite: r.show_on_site,
    sortOrder: r.sort_order,
    parentSlug: r.parent_slug ?? undefined,
    metaTitle: r.meta_title ?? undefined,
    metaDescription: r.meta_description ?? undefined,
    curatedTags: r.curated_tags ?? [],
  };
}

function categoryToRow(c: Partial<CategoryConfig>) {
  const row: Record<string, unknown> = {};
  if (c.slug !== undefined) row.slug = c.slug;
  if (c.label !== undefined) row.label = c.label;
  if (c.showOnSite !== undefined) row.show_on_site = c.showOnSite;
  if (c.sortOrder !== undefined) row.sort_order = c.sortOrder;
  if (c.parentSlug !== undefined) row.parent_slug = c.parentSlug;
  if (c.metaTitle !== undefined) row.meta_title = c.metaTitle;
  if (c.metaDescription !== undefined) row.meta_description = c.metaDescription;
  if (c.curatedTags !== undefined) row.curated_tags = c.curatedTags;
  return row;
}

type MediaRow = { id: string; url: string; alt_text: string; title: string; file_name: string; created_at: string };

function rowToMedia(r: MediaRow): MediaItem {
  return {
    id: r.id,
    url: r.url,
    altText: r.alt_text ?? '',
    title: r.title ?? '',
    fileName: r.file_name ?? '',
    createdAt: r.created_at,
  };
}

function mediaToRow(m: Partial<MediaItem>) {
  const row: Record<string, unknown> = {};
  if (m.url !== undefined) row.url = m.url;
  if (m.altText !== undefined) row.alt_text = m.altText;
  if (m.title !== undefined) row.title = m.title;
  if (m.fileName !== undefined) row.file_name = m.fileName;
  return row;
}

/* ────────────────────────────── provider ────────────────────────────── */

export function AppProvider({ children }: { children: React.ReactNode }) {
  const [articles, setArticlesState] = useState<Article[]>(mockArticles);
  const [ads, setAds] = useState<AdZone[]>(DEFAULT_ADS);
  const [categories, setCategories] = useState<CategoryConfig[]>(DEFAULT_CATEGORIES);
  const [mediaLibrary, setMediaLibrary] = useState<MediaItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [usingFallbackArticles, setUsingFallbackArticles] = useState(true);
  const didInit = useRef(false);

  const refresh = useCallback(async () => {
    if (!isSupabaseConfigured) {
      setLoading(false);
      return;
    }
    try {
      const [artRes, adRes, catRes, medRes] = await Promise.all([
        supabase.from('articles').select('*').order('created_at', { ascending: false }),
        supabase.from('ads').select('*'),
        supabase.from('categories').select('*').order('sort_order', { ascending: true }),
        supabase.from('media_library').select('*').order('created_at', { ascending: false }),
      ]);

      if (!artRes.error && artRes.data) {
        if (artRes.data.length > 0) {
          setArticlesState((artRes.data as ArticleRow[]).map(rowToArticle));
          setUsingFallbackArticles(false);
        } else {
          setArticlesState(mockArticles);
          setUsingFallbackArticles(true);
        }
      }
      if (!adRes.error && adRes.data && adRes.data.length > 0) {
        setAds((adRes.data as AdRow[]).map(rowToAd));
      }
      if (!catRes.error && catRes.data && catRes.data.length > 0) {
        setCategories((catRes.data as CategoryRow[]).map(rowToCategory));
      }
      if (!medRes.error && medRes.data) {
        setMediaLibrary((medRes.data as MediaRow[]).map(rowToMedia));
      }
    } catch (e) {
      console.warn('[alt404] Failed to load content from Supabase.', e);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    if (didInit.current) return;
    didInit.current = true;
    refresh();
  }, [refresh]);

  // Keep content fresh when the tab regains focus (so admin edits show up
  // on other devices without a manual reload).
  useEffect(() => {
    const onFocus = () => { if (document.visibilityState === 'visible') refresh(); };
    document.addEventListener('visibilitychange', onFocus);
    return () => document.removeEventListener('visibilitychange', onFocus);
  }, [refresh]);

  /* ── articles ── */

  const setArticles = (next: Article[]) => setArticlesState(next);

  const addArticle = async (article: Article) => {
    const row = articleToRow({ ...article, views: 0, versions: [] });
    const { data, error } = await supabase.from('articles').insert(row).select().single();
    if (error) {
      console.error('[alt404] addArticle failed:', error.message);
      throw error;
    }
    const created = rowToArticle(data as ArticleRow);
    setArticlesState((prev) => [created, ...prev.filter((a) => a.id !== created.id)]);
    setUsingFallbackArticles(false);
  };

  const updateArticle = async (id: string, patch: Partial<Article>) => {
    const row = { ...articleToRow(patch), updated_at: new Date().toISOString() };
    const { data, error } = await supabase.from('articles').update(row).eq('id', id).select().single();
    if (error) {
      console.error('[alt404] updateArticle failed:', error.message);
      throw error;
    }
    const updated = rowToArticle(data as ArticleRow);
    setArticlesState((prev) => prev.map((a) => (a.id === id ? updated : a)));
  };

  const saveArticleVersion = async (id: string, version: ArticleVersion) => {
    const current = articles.find((a) => a.id === id);
    const versions = [version, ...(current?.versions || [])].slice(0, 10);
    setArticlesState((prev) => prev.map((a) => (a.id === id ? { ...a, versions } : a)));
    const { error } = await supabase.from('articles').update({ versions }).eq('id', id);
    if (error) console.warn('[alt404] saveArticleVersion failed:', error.message);
  };

  const revertToVersion = async (id: string, version: ArticleVersion) => {
    await updateArticle(id, {
      title: version.title,
      excerpt: version.excerpt,
      content: version.content,
    });
  };

  const deleteArticle = async (id: string) => {
    const { error } = await supabase.from('articles').delete().eq('id', id);
    if (error) {
      console.error('[alt404] deleteArticle failed:', error.message);
      throw error;
    }
    setArticlesState((prev) => prev.filter((a) => a.id !== id));
  };

  const incrementViews = (id: string) => {
    setArticlesState((prev) => prev.map((a) => (a.id === id ? { ...a, views: (a.views || 0) + 1 } : a)));
    supabase.rpc('increment_article_views', { article_id: id }).then(({ error }) => {
      if (error) console.warn('[alt404] increment views failed:', error.message);
    });
  };

  /* ── ads ── */

  const updateAd = async (id: string, patch: Partial<AdZone>) => {
    setAds((prev) => prev.map((ad) => (ad.id === id ? { ...ad, ...patch } : ad)));
    const { error } = await supabase
      .from('ads')
      .upsert({ id, ...adToRow(patch), updated_at: new Date().toISOString() }, { onConflict: 'id' });
    if (error) console.error('[alt404] updateAd failed:', error.message);
  };

  /* ── media ── */

  const addMediaItem = async (item: MediaItem) => {
    const { data, error } = await supabase.from('media_library').insert(mediaToRow(item)).select().single();
    if (error) {
      console.error('[alt404] addMediaItem failed:', error.message);
      throw error;
    }
    setMediaLibrary((prev) => [rowToMedia(data as MediaRow), ...prev]);
  };

  const updateMediaItem = async (id: string, patch: Partial<MediaItem>) => {
    setMediaLibrary((prev) => prev.map((m) => (m.id === id ? { ...m, ...patch } : m)));
    const { error } = await supabase.from('media_library').update(mediaToRow(patch)).eq('id', id);
    if (error) console.error('[alt404] updateMediaItem failed:', error.message);
  };

  const deleteMediaItem = async (id: string) => {
    setMediaLibrary((prev) => prev.filter((m) => m.id !== id));
    const { error } = await supabase.from('media_library').delete().eq('id', id);
    if (error) console.error('[alt404] deleteMediaItem failed:', error.message);
  };

  /* ── categories ── */

  const upsertCategory = async (category: CategoryConfig) => {
    setCategories((prev) =>
      prev.some((c) => c.slug === category.slug)
        ? prev.map((c) => (c.slug === category.slug ? { ...c, ...category } : c))
        : [...prev, category]
    );
    const { error } = await supabase.from('categories').upsert(categoryToRow(category), { onConflict: 'slug' });
    if (error) console.error('[alt404] upsertCategory failed:', error.message);
  };

  const updateCategory = async (slug: string, patch: Partial<CategoryConfig>) => {
    setCategories((prev) => prev.map((c) => (c.slug === slug ? { ...c, ...patch, slug: c.slug } : c)));
    const { error } = await supabase.from('categories').update(categoryToRow(patch)).eq('slug', slug);
    if (error) console.error('[alt404] updateCategory failed:', error.message);
  };

  const deleteCategory = async (slug: string) => {
    setCategories((prev) => prev.filter((c) => c.slug !== slug));
    const { error } = await supabase.from('categories').delete().eq('slug', slug);
    if (error) console.error('[alt404] deleteCategory failed:', error.message);
  };

  /* ── one-time migration: localStorage → Supabase ── */

  const migrateLocalContent = async () => {
    const result = { articles: 0, ads: 0, categories: 0, media: 0 };
    let parsed: Partial<AppState> | null = null;
    try {
      const raw = localStorage.getItem('alt404_state');
      if (raw) parsed = JSON.parse(raw) as Partial<AppState>;
    } catch {
      parsed = null;
    }
    if (!parsed) return result;

    if (parsed.categories?.length) {
      const rows = parsed.categories.map((c) => categoryToRow(c));
      const { error } = await supabase.from('categories').upsert(rows, { onConflict: 'slug' });
      if (!error) result.categories = rows.length;
      else console.error('[alt404] migrate categories failed:', error.message);
    }

    if (parsed.ads?.length) {
      const rows = parsed.ads.map((a) => ({ id: a.id, ...adToRow(a) }));
      const { error } = await supabase.from('ads').upsert(rows, { onConflict: 'id' });
      if (!error) result.ads = rows.length;
      else console.error('[alt404] migrate ads failed:', error.message);
    }

    if (parsed.articles?.length) {
      // Omit client ids (localStorage used non-uuid ids); dedupe on slug.
      const rows = parsed.articles.map((a) => articleToRow(a));
      const { error } = await supabase.from('articles').upsert(rows, { onConflict: 'slug' });
      if (!error) result.articles = rows.length;
      else console.error('[alt404] migrate articles failed:', error.message);
    }

    if (parsed.mediaLibrary?.length) {
      const rows = parsed.mediaLibrary.map((m) => mediaToRow(m));
      const { error } = await supabase.from('media_library').insert(rows);
      if (!error) result.media = rows.length;
      else console.error('[alt404] migrate media failed:', error.message);
    }

    await refresh();
    return result;
  };

  return (
    <AppContext.Provider
      value={{
        articles,
        ads,
        categories,
        mediaLibrary,
        loading,
        usingFallbackArticles,
        refresh,
        migrateLocalContent,
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
