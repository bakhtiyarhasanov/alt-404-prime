import { useMemo, useState } from 'react';
import { Plus, Trash2, Eye, EyeOff, ArrowUp, ArrowDown } from 'lucide-react';
import { useApp } from '../../lib/appContext';
import type { CategoryConfig } from '../../lib/categoryConfig';
import { slugify } from '../../lib/utils';

function FieldRow({
  label,
  children,
}: {
  label: string;
  children: React.ReactNode;
}) {
  return (
    <div className="flex flex-col gap-2">
      <label className="font-mono text-xs text-text-muted uppercase tracking-widest">{label}</label>
      {children}
    </div>
  );
}

export default function CategoriesManager() {
  const { categories, upsertCategory, deleteCategory, updateCategory } = useApp();
  const sorted = useMemo(() => [...categories].sort((a, b) => a.sortOrder - b.sortOrder), [categories]);

  const [form, setForm] = useState<{
    label: string;
    slug: string;
    parentSlug: string;
    showOnSite: boolean;
    sortOrder: number;
    metaTitle: string;
    metaDescription: string;
    curatedTagsInput: string;
  }>({
    label: '',
    slug: '',
    parentSlug: '',
    showOnSite: false,
    sortOrder: (sorted.length ? sorted[sorted.length - 1].sortOrder : 0) + 10,
    metaTitle: '',
    metaDescription: '',
    curatedTagsInput: '',
  });

  const [deleteSlug, setDeleteSlug] = useState<string | null>(null);
  const deleteTarget = deleteSlug ? sorted.find((c) => c.slug === deleteSlug) : null;

  function submitNew() {
    const label = form.label.trim();
    const slug = form.slug.trim();
    if (!label || !slug) return;

    const curatedTags = form.curatedTagsInput
      .split(',')
      .map((t) => t.trim().replace(/^#/, ''))
      .filter(Boolean);

    const next: CategoryConfig = {
      slug,
      label,
      showOnSite: form.showOnSite,
      sortOrder: form.sortOrder,
      parentSlug: form.parentSlug.trim() || undefined,
      metaTitle: form.metaTitle.trim() || undefined,
      metaDescription: form.metaDescription.trim() || undefined,
      curatedTags: curatedTags.length ? curatedTags : [],
    };

    upsertCategory(next);
    setForm((prev) => ({
      ...prev,
      label: '',
      slug: '',
      parentSlug: '',
      metaTitle: '',
      metaDescription: '',
      curatedTagsInput: '',
      sortOrder: (sorted.length ? sorted[sorted.length - 1].sortOrder : 0) + 10,
      showOnSite: false,
    }));
  }

  return (
    <div className="space-y-6">
      <div>
        <h2 className="font-mono text-lg font-bold text-text-primary">Bölmələr (SEO / Header)</h2>
        <p className="font-sans text-sm text-text-secondary mt-1">
          Görünmə, sıra, SEO title/description və (opsional) curated tag-lar admin-dən idarə olunur.
        </p>
      </div>

      {/* Add */}
      <div className="bg-white rounded-xl border border-border p-5 shadow-sm">
        <p className="font-mono text-sm font-semibold text-text-primary mb-4">Yeni bölmə əlavə et</p>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FieldRow label="Ad">
            <input
              value={form.label}
              onChange={(e) => {
                const nextLabel = e.target.value;
                setForm((p) => ({
                  ...p,
                  label: nextLabel,
                  slug: p.slug || slugify(nextLabel),
                }));
              }}
              className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-sm font-sans text-text-primary outline-none focus:border-brand/40"
              placeholder="Məs: Elm"
            />
          </FieldRow>
          <FieldRow label="Slug (URL)">
            <input
              value={form.slug}
              onChange={(e) => setForm((p) => ({ ...p, slug: e.target.value }))}
              className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-sm font-mono text-text-primary outline-none focus:border-brand/40"
              placeholder="Məs: elm"
              style={{ direction: 'ltr' }}
            />
          </FieldRow>

          <FieldRow label="Parent slug (opsional)">
            <select
              value={form.parentSlug}
              onChange={(e) => setForm((p) => ({ ...p, parentSlug: e.target.value }))}
              className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-sm font-mono text-text-primary outline-none focus:border-brand/40"
              style={{ direction: 'ltr' }}
            >
              <option value="">(əsas bölmə)</option>
              {sorted.map((c) => (
                <option key={c.slug} value={c.slug}>
                  {c.label} ({c.slug})
                </option>
              ))}
            </select>
          </FieldRow>

          <FieldRow label="SEO title">
            <input
              value={form.metaTitle}
              onChange={(e) => setForm((p) => ({ ...p, metaTitle: e.target.value }))}
              className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-sm font-sans text-text-primary outline-none focus:border-brand/40"
              placeholder="Məs: Elm xəbərləri | alt404"
            />
          </FieldRow>

          <FieldRow label="SEO description">
            <input
              value={form.metaDescription}
              onChange={(e) => setForm((p) => ({ ...p, metaDescription: e.target.value }))}
              className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-sm font-sans text-text-primary outline-none focus:border-brand/40"
              placeholder="Qısa təsvir..."
            />
          </FieldRow>

          <FieldRow label="Curated tags (csv)">
            <input
              value={form.curatedTagsInput}
              onChange={(e) => setForm((p) => ({ ...p, curatedTagsInput: e.target.value }))}
              className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-sm font-mono text-text-primary outline-none focus:border-brand/40"
              placeholder="Məs: AI, Smartphone"
              style={{ direction: 'ltr' }}
            />
          </FieldRow>

          <div className="flex flex-col gap-2">
            <label className="font-mono text-xs text-text-muted uppercase tracking-widest">Saytda görünür</label>
            <div className="flex items-center gap-3">
              <button
                type="button"
                onClick={() => setForm((p) => ({ ...p, showOnSite: true }))}
                className={`flex items-center gap-2 px-3 py-2 rounded-lg border transition-colors ${
                  form.showOnSite ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-surface-2 border-border text-text-secondary hover:text-text-primary'
                }`}
              >
                <Eye size={14} />
                Aktiv
              </button>
              <button
                type="button"
                onClick={() => setForm((p) => ({ ...p, showOnSite: false }))}
                className={`flex items-center gap-2 px-3 py-2 rounded-lg border transition-colors ${
                  !form.showOnSite ? 'bg-alt-red/10 border-alt-red-dim text-alt-red' : 'bg-surface-2 border-border text-text-secondary hover:text-text-primary'
                }`}
              >
                <EyeOff size={14} />
                Gizli
              </button>
            </div>
          </div>
        </div>

        <div className="flex justify-end gap-3 mt-5">
          <button
            type="button"
            onClick={submitNew}
            disabled={!form.label.trim() || !form.slug.trim()}
            className="btn-primary flex items-center gap-2 disabled:opacity-50"
          >
            <Plus size={14} />
            Əlavə et
          </button>
        </div>
      </div>

      {/* List */}
      <div className="bg-white rounded-xl border border-border shadow-sm overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b border-border bg-surface-2">
                <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest">Ad</th>
                <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest hidden md:table-cell">Slug</th>
                <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest">SEO</th>
                <th className="px-5 py-3 text-left font-mono text-xs text-text-muted uppercase tracking-widest">Curated tags</th>
                <th className="px-5 py-3 text-right font-mono text-xs text-text-muted uppercase tracking-widest">Əməliyyat</th>
              </tr>
            </thead>
            <tbody>
              {sorted.map((c) => (
                <tr key={c.slug} className="border-b border-border/50 hover:bg-surface-2 transition-colors align-top">
                  <td className="px-5 py-4">
                    <div className="flex items-start gap-3">
                      <div
                        className={`w-2 h-2 rounded-full mt-2 ${
                          c.showOnSite ? 'bg-emerald-500' : 'bg-text-muted'
                        }`}
                      />
                      <div className="min-w-0">
                        <div className="flex items-center gap-2">
                          <input
                            value={c.label}
                            onChange={(e) => updateCategory(c.slug, { label: e.target.value })}
                            className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-sm font-mono text-text-primary outline-none focus:border-brand/40"
                          />
                        </div>
                        <div className="flex items-center gap-2 mt-2">
                          <button
                            type="button"
                            onClick={() => updateCategory(c.slug, { showOnSite: !c.showOnSite })}
                            className="p-1.5 rounded-lg border border-border bg-white hover:bg-surface-2 transition-colors"
                            title={c.showOnSite ? 'Gizlət' : 'Göstər'}
                          >
                            {c.showOnSite ? <Eye size={14} /> : <EyeOff size={14} />}
                          </button>

                          <button
                            type="button"
                            onClick={() => {
                              const prev = [...sorted].filter((x) => x.sortOrder < c.sortOrder).pop();
                              if (!prev) return;
                              updateCategory(c.slug, { sortOrder: prev.sortOrder });
                              updateCategory(prev.slug, { sortOrder: c.sortOrder });
                            }}
                            className="p-1.5 rounded-lg border border-border bg-white hover:bg-surface-2 transition-colors"
                            title="Sıra yuxarı"
                          >
                            <ArrowUp size={14} />
                          </button>
                          <button
                            type="button"
                            onClick={() => {
                              const next = sorted.find((x) => x.sortOrder > c.sortOrder);
                              if (!next) return;
                              updateCategory(c.slug, { sortOrder: next.sortOrder });
                              updateCategory(next.slug, { sortOrder: c.sortOrder });
                            }}
                            className="p-1.5 rounded-lg border border-border bg-white hover:bg-surface-2 transition-colors"
                            title="Sıra aşağı"
                          >
                            <ArrowDown size={14} />
                          </button>
                        </div>
                      </div>
                    </div>
                  </td>

                  <td className="px-5 py-4 hidden md:table-cell">
                    <span className="font-mono text-xs text-text-muted">{c.slug}</span>
                  </td>

                  <td className="px-5 py-4">
                    <div className="space-y-2">
                      <input
                        value={c.metaTitle || ''}
                        onChange={(e) => updateCategory(c.slug, { metaTitle: e.target.value || undefined })}
                        placeholder="meta title"
                        className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-xs font-mono text-text-primary outline-none focus:border-brand/40"
                      />
                      <input
                        value={c.metaDescription || ''}
                        onChange={(e) => updateCategory(c.slug, { metaDescription: e.target.value || undefined })}
                        placeholder="meta description"
                        className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-xs font-mono text-text-primary outline-none focus:border-brand/40"
                      />
                    </div>
                  </td>

                  <td className="px-5 py-4">
                    <input
                      value={(c.curatedTags || []).join(', ')}
                      onChange={(e) => {
                        const curatedTags = e.target.value
                          .split(',')
                          .map((t) => t.trim().replace(/^#/, ''))
                          .filter(Boolean);
                        updateCategory(c.slug, { curatedTags });
                      }}
                      className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-xs font-mono text-text-primary outline-none focus:border-brand/40"
                      placeholder="AI, Smartphone"
                      style={{ direction: 'ltr' }}
                    />
                  </td>

                  <td className="px-5 py-4 text-right">
                    <button
                      type="button"
                      onClick={() => setDeleteSlug(c.slug)}
                      className="p-1.5 rounded-lg text-text-muted hover:text-alt-red hover:bg-red-50 transition-colors"
                      title="Sil"
                    >
                      <Trash2 size={14} />
                    </button>
                  </td>
                </tr>
              ))}
              {sorted.length === 0 && (
                <tr>
                  <td colSpan={5} className="px-5 py-12 text-center">
                    <p className="font-mono text-sm text-text-muted">Kateqoriya tapılmadı.</p>
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {deleteSlug && (
        <div className="fixed inset-0 bg-black/30 backdrop-blur-sm z-50 flex items-center justify-center px-4">
          <div className="bg-white rounded-2xl border border-border shadow-xl p-8 max-w-sm w-full">
            <h3 className="font-mono text-lg font-bold text-text-primary mb-2">Silmək istəyirsiniz?</h3>
            <p className="font-sans text-sm text-text-secondary mb-6">
              {deleteTarget ? `"${deleteTarget.label}" kateqoriyası silinəcək.` : 'Bu kateqoriya silinəcək.'}
            </p>
            <div className="flex gap-3">
              <button
                onClick={() => {
                  deleteCategory(deleteSlug);
                  setDeleteSlug(null);
                }}
                className="flex-1 bg-alt-red text-white font-mono text-sm font-semibold py-2.5 rounded-lg hover:bg-alt-red-dim transition-colors"
              >
                Sil
              </button>
              <button onClick={() => setDeleteSlug(null)} className="flex-1 btn-ghost">İmtina</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

