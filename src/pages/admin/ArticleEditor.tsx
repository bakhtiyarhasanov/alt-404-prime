import { useState, useEffect, useRef, useCallback } from 'react';
import { ArrowLeft, Save, Eye, X, Plus, Images, History, RotateCcw, Clock } from 'lucide-react';
import RichEditor from './RichEditor';
import SeoAssistant from './SeoAssistant';
import MediaLibrary from './MediaLibrary';
import { slugify, estimateReadingTime, formatDate } from '../../lib/utils';
import type { Article, ArticleVersion } from '../../lib/supabase';
import type { MediaItem } from '../../lib/appContext';
import { useApp } from '../../lib/appContext';

interface Props {
  initial?: Partial<Article>;
  onSave: (article: Partial<Article>) => Promise<void>;
  onBack: () => void;
}

type MediaPickerCallback = (items: MediaItem[]) => void;

export default function ArticleEditor({ initial, onSave, onBack }: Props) {
  const { saveArticleVersion, revertToVersion, categories } = useApp();

  const [title, setTitle] = useState(initial?.title || '');
  const [slug, setSlug] = useState(initial?.slug || '');
  const [excerpt, setExcerpt] = useState(initial?.excerpt || '');
  const [content, setContent] = useState(initial?.content || '');
  const [category, setCategory] = useState(initial?.category || 'texnologiya');
  const [imageUrl, setImageUrl] = useState(initial?.image_url || '');
  const [imageAlt, setImageAlt] = useState('');
  const [tags, setTags] = useState<string[]>(initial?.tags || []);
  const [tagInput, setTagInput] = useState('');
  const [featured, setFeatured] = useState(initial?.featured || false);
  const [published, setPublished] = useState(initial?.published !== false);
  const [saving, setSaving] = useState(false);
  const [preview, setPreview] = useState(false);

  // Media picker state — single or multi mode
  const [mediaOpen, setMediaOpen] = useState(false);
  const [mediaMulti, setMediaMulti] = useState(false);
  const mediaCallbackRef = useRef<MediaPickerCallback | null>(null);

  // Version history panel
  const [historyOpen, setHistoryOpen] = useState(false);
  const versions: ArticleVersion[] = initial?.versions || [];

  const isDirty = useRef(false);

  // Mark dirty on any change
  useEffect(() => { isDirty.current = true; }, [title, excerpt, content, imageUrl, category, tags, featured, published]);

  // Auto-save as draft every 45s if dirty
  const autoSaveTimer = useRef<ReturnType<typeof setInterval> | null>(null);
  useEffect(() => {
    autoSaveTimer.current = setInterval(() => {
      if (isDirty.current && title && initial?.id) {
        const version: ArticleVersion = {
          savedAt: new Date().toISOString(),
          title,
          excerpt,
          content,
        };
        saveArticleVersion(initial.id, version);
        isDirty.current = false;
      }
    }, 45000);
    return () => {
      if (autoSaveTimer.current) clearInterval(autoSaveTimer.current);
    };
  }, [title, excerpt, content, initial?.id, saveArticleVersion]);

  const handleBack = useCallback(() => {
    // Save version snapshot when exiting with unsaved changes
    if (isDirty.current && title && initial?.id) {
      const version: ArticleVersion = {
        savedAt: new Date().toISOString(),
        title,
        excerpt,
        content,
      };
      saveArticleVersion(initial.id, version);
    }
    onBack();
  }, [title, excerpt, content, initial?.id, saveArticleVersion, onBack]);

  const handleTitleChange = (val: string) => {
    setTitle(val);
    if (!initial?.slug) setSlug(slugify(val));
  };

  const addTag = () => {
    const t = tagInput.trim().replace(/^#/, '');
    if (t && !tags.includes(t)) setTags([...tags, t]);
    setTagInput('');
  };

  const removeTag = (tag: string) => setTags(tags.filter((t) => t !== tag));

  // Called when cover image selected
  const handleCoverSelect = (item: MediaItem) => {
    setImageUrl(item.url);
    setImageAlt(item.altText || item.title);
    setMediaOpen(false);
    mediaCallbackRef.current = null;
  };

  // Open media picker from toolbar (single or multi)
  const openMediaPicker = useCallback((callback: MediaPickerCallback, multi = false) => {
    mediaCallbackRef.current = callback;
    setMediaMulti(multi);
    setMediaOpen(true);
  }, []);

  // Called when media library returns a selection
  const handleMediaLibrarySelect = (item: MediaItem, selectedItems?: MediaItem[]) => {
    if (mediaCallbackRef.current) {
      // Toolbar-originated pick
      const items = mediaMulti && selectedItems ? selectedItems : [item];
      mediaCallbackRef.current(items);
      mediaCallbackRef.current = null;
      setMediaOpen(false);
    } else {
      // Cover image pick
      handleCoverSelect(item);
    }
  };

  const handleSave = async () => {
    setSaving(true);
    // Save a version snapshot before overwriting
    if (initial?.id && title) {
      const version: ArticleVersion = {
        savedAt: new Date().toISOString(),
        title,
        excerpt,
        content,
      };
      saveArticleVersion(initial.id, version);
    }
    await onSave({
      title,
      slug: slug || slugify(title),
      excerpt,
      content,
      category,
      image_url: imageUrl,
      tags,
      featured,
      published,
      reading_time: estimateReadingTime(content),
    });
    isDirty.current = false;
    setSaving(false);
  };

  const handleRevert = (v: ArticleVersion) => {
    if (initial?.id) {
      revertToVersion(initial.id, v);
      setTitle(v.title);
      setExcerpt(v.excerpt);
      setContent(v.content);
      setHistoryOpen(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#f8f9fa] dot-matrix">
      {/* Top bar */}
      <div className="sticky top-0 z-40 bg-white/90 backdrop-blur-xl border-b border-border shadow-sm px-6 h-14 flex items-center justify-between">
        <button
          onClick={handleBack}
          className="flex items-center gap-2 font-mono text-xs text-text-secondary hover:text-text-primary transition-colors group"
        >
          <ArrowLeft size={14} className="group-hover:-translate-x-0.5 transition-transform" />
          Dashboard
        </button>
        <div className="flex items-center gap-2">
          {initial?.id && versions.length > 0 && (
            <button
              type="button"
              onClick={() => setHistoryOpen(true)}
              className="btn-ghost flex items-center gap-1.5 text-xs py-1.5"
            >
              <History size={13} />
              Tarixçə ({versions.length})
            </button>
          )}
          <button
            type="button"
            onClick={() => setPreview(!preview)}
            className="btn-ghost flex items-center gap-1.5 text-xs py-1.5"
          >
            <Eye size={13} />
            {preview ? 'Redaktə' : 'Önizləmə'}
          </button>
          <button
            onClick={handleSave}
            disabled={saving || !title}
            className="btn-primary flex items-center gap-1.5 text-xs py-1.5 disabled:opacity-50"
          >
            <Save size={13} />
            {saving ? 'Saxlanır...' : 'Saxla'}
          </button>
        </div>
      </div>

      <div className="max-w-6xl mx-auto px-4 py-8">
        {preview ? (
          <PreviewPane title={title} excerpt={excerpt} content={content} imageUrl={imageUrl} imageAlt={imageAlt} tags={tags} category={category} />
        ) : (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Main editor */}
            <div className="lg:col-span-2 space-y-5">
              <div>
                <label className="font-mono text-xs text-text-muted uppercase tracking-widest block mb-2">Başlıq</label>
                <input
                  type="text"
                  value={title}
                  onChange={(e) => handleTitleChange(e.target.value)}
                  placeholder="Xəbər başlığı..."
                  dir="ltr"
                  className="w-full bg-white border border-border rounded-xl px-4 py-3 text-lg font-mono font-bold text-text-primary placeholder-text-muted outline-none focus:border-brand/40 transition-colors"
                  style={{ direction: 'ltr', textAlign: 'left' }}
                />
              </div>

              <div>
                <label className="font-mono text-xs text-text-muted uppercase tracking-widest block mb-2">Xülasə</label>
                <textarea
                  value={excerpt}
                  onChange={(e) => setExcerpt(e.target.value)}
                  rows={3}
                  placeholder="Qısa xülasə (axtarış nəticəsi və paylaşma üçün)..."
                  dir="ltr"
                  className="w-full bg-white border border-border rounded-xl px-4 py-3 text-sm text-text-primary placeholder-text-muted outline-none focus:border-brand/40 transition-colors font-sans resize-none"
                  style={{ direction: 'ltr', textAlign: 'left' }}
                />
              </div>

              <div>
                <label className="font-mono text-xs text-text-muted uppercase tracking-widest block mb-2">Məzmun</label>
                <RichEditor value={content} onChange={setContent} onOpenMediaPicker={openMediaPicker} />
              </div>
            </div>

            {/* Sidebar */}
            <div className="space-y-5">
              <SeoAssistant title={title} content={content} tags={tags} />

              {/* Slug */}
              <div className="bg-white rounded-xl border border-border p-5 shadow-sm">
                <label className="font-mono text-xs text-text-muted uppercase tracking-widest block mb-2">URL Slug</label>
                <input
                  type="text"
                  value={slug}
                  onChange={(e) => setSlug(e.target.value)}
                  className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-xs font-mono text-text-secondary placeholder-text-muted outline-none focus:border-brand/40 transition-colors"
                  placeholder="xeber-basligi"
                />
              </div>

              {/* Category */}
              <div className="bg-white rounded-xl border border-border p-5 shadow-sm">
                <label className="font-mono text-xs text-text-muted uppercase tracking-widest block mb-2">Kateqoriya</label>
                <select
                  value={category}
                  onChange={(e) => setCategory(e.target.value)}
                  className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-sm font-mono text-text-primary outline-none focus:border-brand/40 transition-colors"
                >
                  {[...categories]
                    .sort((a, b) => a.sortOrder - b.sortOrder)
                    .map((cat) => (
                      <option key={cat.slug} value={cat.slug}>
                        {cat.label}
                      </option>
                    ))}
                </select>
              </div>

              {/* Cover Image */}
              <div className="bg-white rounded-xl border border-border p-5 shadow-sm">
                <div className="flex items-center justify-between mb-3">
                  <label className="font-mono text-xs text-text-muted uppercase tracking-widest">Örtük Şəkli</label>
                  <button
                    type="button"
                    onClick={() => { mediaCallbackRef.current = null; setMediaMulti(false); setMediaOpen(true); }}
                    className="flex items-center gap-1.5 font-mono text-xs text-text-secondary hover:text-text-primary transition-colors"
                  >
                    <Images size={13} />
                    Media Seç
                  </button>
                </div>

                {imageUrl ? (
                  <div className="space-y-2">
                    <div className="relative rounded-lg overflow-hidden border border-border group">
                      <img src={imageUrl} alt={imageAlt} className="w-full aspect-video object-cover" />
                      <button
                        type="button"
                        onClick={() => { setImageUrl(''); setImageAlt(''); }}
                        className="absolute top-2 right-2 w-6 h-6 bg-black/50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                      >
                        <X size={11} className="text-white" />
                      </button>
                    </div>
                    <div>
                      <label className="font-mono text-[10px] text-text-muted uppercase tracking-widest block mb-1">Alt mətn (SEO)</label>
                      <input
                        type="text"
                        value={imageAlt}
                        onChange={(e) => setImageAlt(e.target.value)}
                        placeholder="Şəkil alt mətni..."
                        className="w-full bg-surface-2 border border-border rounded px-2.5 py-1.5 text-xs font-sans text-text-primary outline-none focus:border-brand/40 transition-colors"
                      />
                    </div>
                  </div>
                ) : (
                  <button
                    type="button"
                    onClick={() => { mediaCallbackRef.current = null; setMediaMulti(false); setMediaOpen(true); }}
                    className="w-full aspect-video bg-surface-2 border-2 border-dashed border-border rounded-lg flex flex-col items-center justify-center gap-2 hover:border-brand/40 transition-colors"
                  >
                    <Images size={20} className="text-text-muted" />
                    <span className="font-mono text-xs text-text-muted">Media kitabxanasından seçin</span>
                  </button>
                )}
              </div>

              {/* Tags */}
              <div className="bg-white rounded-xl border border-border p-5 shadow-sm">
                <label className="font-mono text-xs text-text-muted uppercase tracking-widest block mb-3">Hashteqlər</label>
                <div className="flex items-center gap-2 mb-3">
                  <input
                    type="text"
                    value={tagInput}
                    onChange={(e) => setTagInput(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), addTag())}
                    placeholder="#mövzu"
                    className="flex-1 bg-surface-2 border border-border rounded-lg px-3 py-1.5 text-xs font-mono text-text-primary placeholder-text-muted outline-none focus:border-brand/40 transition-colors"
                  />
                  <button
                    type="button"
                    onClick={addTag}
                    className="p-1.5 rounded-lg border border-border text-text-secondary hover:text-text-primary hover:border-border-strong transition-colors"
                  >
                    <Plus size={14} />
                  </button>
                </div>
                <div className="flex flex-wrap gap-1.5">
                  {tags.map((tag) => (
                    <span key={tag} className="tag-pill flex items-center gap-1">
                      #{tag}
                      <button type="button" onClick={() => removeTag(tag)} className="hover:text-text-primary transition-colors">
                        <X size={10} />
                      </button>
                    </span>
                  ))}
                </div>
              </div>

              {/* Settings */}
              <div className="bg-white rounded-xl border border-border p-5 shadow-sm space-y-3">
                <label className="font-mono text-xs text-text-muted uppercase tracking-widest block mb-1">Parametrlər</label>
                <Toggle label="Seçilmiş xəbər" checked={featured} onChange={setFeatured} />
                <Toggle label="Yayımlanmış" checked={published} onChange={setPublished} />
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Media Manager Modal */}
      {mediaOpen && (
        <div className="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
          <div className="bg-white rounded-2xl border border-border shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col p-6 overflow-y-auto">
            <MediaLibrary
              onSelect={handleMediaLibrarySelect}
              onClose={() => { setMediaOpen(false); mediaCallbackRef.current = null; }}
              multiSelect={mediaMulti}
            />
          </div>
        </div>
      )}

      {/* Version History Drawer */}
      {historyOpen && (
        <div className="fixed inset-0 z-50 bg-black/30 backdrop-blur-sm flex items-end md:items-center justify-center p-4">
          <div className="bg-white rounded-2xl border border-border shadow-xl w-full max-w-md max-h-[70vh] flex flex-col">
            <div className="flex items-center justify-between px-6 py-4 border-b border-border">
              <div className="flex items-center gap-2">
                <History size={16} className="text-text-muted" />
                <h3 className="font-mono text-sm font-bold text-text-primary">Versiya Tarixçəsi</h3>
              </div>
              <button onClick={() => setHistoryOpen(false)} className="p-1.5 rounded-lg hover:bg-surface-2 transition-colors">
                <X size={14} className="text-text-muted" />
              </button>
            </div>
            <div className="overflow-y-auto flex-1 divide-y divide-border">
              {versions.length === 0 ? (
                <p className="font-mono text-xs text-text-muted text-center py-8">Versiya yoxdur.</p>
              ) : (
                versions.map((v, i) => (
                  <div key={i} className="px-6 py-4 hover:bg-surface-2 transition-colors">
                    <div className="flex items-start justify-between gap-3">
                      <div className="min-w-0">
                        <p className="font-mono text-xs font-semibold text-text-primary line-clamp-1">{v.title || 'Başlıqsız'}</p>
                        <div className="flex items-center gap-1.5 mt-1">
                          <Clock size={10} className="text-text-muted" />
                          <p className="font-mono text-[10px] text-text-muted">{formatDate(v.savedAt)}</p>
                        </div>
                      </div>
                      <button
                        onClick={() => handleRevert(v)}
                        className="flex-shrink-0 flex items-center gap-1.5 font-mono text-[10px] font-semibold px-3 py-1.5 rounded-lg border border-border hover:border-border-strong hover:bg-surface-2 transition-colors text-text-secondary hover:text-text-primary"
                      >
                        <RotateCcw size={10} />
                        Bərpa et
                      </button>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

function Toggle({ label, checked, onChange }: { label: string; checked: boolean; onChange: (v: boolean) => void }) {
  return (
    <button
      type="button"
      onClick={() => onChange(!checked)}
      className="flex items-center justify-between w-full group"
    >
      <span className="font-sans text-sm text-text-secondary group-hover:text-text-primary transition-colors">{label}</span>
      <div className={`w-9 h-5 rounded-full transition-colors relative ${checked ? 'bg-brand' : 'bg-surface-3 border border-border'}`}>
        <div className={`absolute top-0.5 w-4 h-4 rounded-full bg-white transition-transform shadow-sm ${checked ? 'translate-x-4' : 'translate-x-0.5'}`} />
      </div>
    </button>
  );
}

function PreviewPane({ title, excerpt, content, imageUrl, imageAlt, tags, category }: {
  title: string; excerpt: string; content: string; imageUrl: string; imageAlt: string; tags: string[]; category: string;
}) {
  const { categories } = useApp();
  return (
    <div className="max-w-2xl mx-auto">
      {imageUrl && (
        <div className="rounded-2xl overflow-hidden mb-6 border border-border">
          <img src={imageUrl} alt={imageAlt || title} className="w-full aspect-video object-cover" />
        </div>
      )}
      <div className="mb-4">
        <span className="category-badge">{categories.find((c) => c.slug === category)?.label || category}</span>
      </div>
      <h1 className="font-mono text-3xl font-bold text-text-primary mb-3">{title || 'Başlıq...'}</h1>
      <p className="text-text-secondary font-sans mb-6">{excerpt}</p>
      <div
        className="article-content bg-white rounded-2xl border border-border p-6"
        dangerouslySetInnerHTML={{ __html: content || '<p>Məzmun yoxdur...</p>' }}
      />
      <div className="mt-8 flex flex-wrap gap-2">
        {tags.map((t) => <span key={t} className="tag-pill">#{t}</span>)}
      </div>
    </div>
  );
}
