import { useRef, useState } from 'react';
import { Upload, Trash2, Check, X, Image as ImageIcon } from 'lucide-react';
import { useApp } from '../../lib/appContext';
import type { MediaItem } from '../../lib/appContext';

interface Props {
  /** When provided, renders as a picker modal */
  onSelect?: (item: MediaItem, selectedItems?: MediaItem[]) => void;
  onClose?: () => void;
  multiSelect?: boolean;
}

export default function MediaLibrary({ onSelect, onClose, multiSelect = false }: Props) {
  const { mediaLibrary, addMediaItem, updateMediaItem, deleteMediaItem } = useApp();
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [editAlt, setEditAlt] = useState('');
  const [editTitle, setEditTitle] = useState('');
  const [uploading, setUploading] = useState(false);
  const [uploadError, setUploadError] = useState('');
  const [selectedId, setSelectedId] = useState<string | null>(null);
  const [selectedIds, setSelectedIds] = useState<string[]>([]);

  const isPicker = !!onSelect;

  const toggleMultiSelect = (id: string) => {
    setSelectedIds((prev) =>
      prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]
    );
  };

  const handleFiles = async (files: FileList | null) => {
    if (!files) return;
    setUploading(true);
    setUploadError('');
    for (const file of Array.from(files)) {
      if (file.size > 5 * 1024 * 1024) {
        setUploadError(`"${file.name}" 5MB limitini aşır və yüklənmədi.`);
        continue;
      }
      const url = await readFileAsDataUrl(file);
      const item: MediaItem = {
        id: `media_${Date.now()}_${Math.random().toString(36).slice(2)}`,
        url,
        altText: file.name.replace(/\.[^.]+$/, '').replace(/[-_]/g, ' '),
        title: file.name.replace(/\.[^.]+$/, '').replace(/[-_]/g, ' '),
        fileName: file.name,
        createdAt: new Date().toISOString(),
      };
      addMediaItem(item);
    }
    setUploading(false);
  };

  const startEdit = (item: MediaItem) => {
    setEditingId(item.id);
    setEditAlt(item.altText);
    setEditTitle(item.title);
  };

  const saveEdit = (id: string) => {
    updateMediaItem(id, { altText: editAlt, title: editTitle });
    setEditingId(null);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    handleFiles(e.dataTransfer.files);
  };

  const confirmSelect = () => {
    if (!onSelect) return;
    if (multiSelect) {
      const items = mediaLibrary.filter((m) => selectedIds.includes(m.id));
      if (items.length) onSelect(items[0], items);
    } else {
      const item = mediaLibrary.find((m) => m.id === selectedId);
      if (item) onSelect(item);
    }
  };

  return (
    <div className={isPicker ? 'flex flex-col h-full' : ''}>
      {/* Header */}
      <div className="flex items-center justify-between mb-5">
        <div>
          <h2 className="font-mono text-lg font-bold text-text-primary">
            {isPicker ? 'Şəkil Seçin' : 'Media Kitabxanası'}
          </h2>
          <p className="font-sans text-sm text-text-secondary mt-0.5">
            {mediaLibrary.length} şəkil
          </p>
        </div>
        <div className="flex items-center gap-2">
          <button
            onClick={() => fileInputRef.current?.click()}
            className="btn-primary flex items-center gap-2 text-xs py-2"
          >
            <Upload size={13} />
            Yüklə
          </button>
          {isPicker && onClose && (
            <button onClick={onClose} className="btn-ghost text-xs py-2">
              <X size={14} />
            </button>
          )}
        </div>
      </div>

      <input
        ref={fileInputRef}
        type="file"
        accept="image/*"
        multiple
        className="hidden"
        onChange={(e) => handleFiles(e.target.files)}
      />

      {/* Drop zone */}
      <div
        onDrop={handleDrop}
        onDragOver={(e) => e.preventDefault()}
        onClick={() => mediaLibrary.length === 0 && fileInputRef.current?.click()}
        className={`border-2 border-dashed border-border rounded-xl p-6 text-center mb-5 transition-colors ${
          mediaLibrary.length === 0 ? 'cursor-pointer hover:border-brand/40 hover:bg-brand/5' : 'border-transparent p-0 mb-0'
        }`}
      >
        {mediaLibrary.length === 0 && !uploading && (
          <div className="flex flex-col items-center gap-2 py-8">
            <ImageIcon size={32} className="text-text-muted" />
            <p className="font-mono text-sm text-text-muted">Şəkilləri bura sürüşdürün və ya klikləyin</p>
            <p className="font-sans text-xs text-text-muted">PNG, JPG, WebP — maksimum 5 MB</p>
          </div>
        )}
        {uploading && (
          <p className="font-mono text-sm text-text-muted py-4">Yüklənir...</p>
        )}
      </div>
      {uploadError && (
        <p className="font-sans text-xs text-alt-red mb-4">{uploadError}</p>
      )}

      {/* Grid */}
      {mediaLibrary.length > 0 && (
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 flex-1 overflow-y-auto">
          {mediaLibrary.map((item) => (
            <div
              key={item.id}
              onClick={() => {
                if (!isPicker) return;
                if (multiSelect) toggleMultiSelect(item.id);
                else setSelectedId(item.id);
              }}
              className={`group relative rounded-xl overflow-hidden border transition-all duration-200 ${
                isPicker ? 'cursor-pointer' : ''
              } ${
                (multiSelect ? selectedIds.includes(item.id) : selectedId === item.id)
                  ? 'border-brand ring-2 ring-brand/30'
                  : 'border-border hover:border-border-strong'
              }`}
            >
              {/* Image */}
              <div className="aspect-square bg-surface-2">
                <img
                  src={item.url}
                  alt={item.altText}
                  className="w-full h-full object-cover"
                />
              </div>

              {/* Selected check */}
              {(multiSelect ? selectedIds.includes(item.id) : selectedId === item.id) && (
                <div className="absolute top-2 right-2 w-5 h-5 bg-brand rounded-full flex items-center justify-center">
                  <Check size={11} className="text-black" />
                </div>
              )}
              {multiSelect && selectedIds.includes(item.id) && (
                <div className="absolute top-2 left-2 w-5 h-5 bg-brand/90 rounded-full flex items-center justify-center font-mono text-[9px] font-bold text-black">
                  {selectedIds.indexOf(item.id) + 1}
                </div>
              )}

              {/* Edit overlay (non-picker) */}
              {!isPicker && editingId !== item.id && (
                <div className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-end">
                  <div className="w-full p-2 translate-y-full group-hover:translate-y-0 transition-transform flex items-center gap-1.5">
                    <button
                      onClick={(e) => { e.stopPropagation(); startEdit(item); }}
                      className="flex-1 bg-white/90 font-mono text-xs text-text-primary py-1.5 rounded-lg hover:bg-white transition-colors"
                    >
                      Redaktə
                    </button>
                    <button
                      onClick={(e) => { e.stopPropagation(); deleteMediaItem(item.id); }}
                      className="p-1.5 bg-black/70 rounded-lg hover:bg-black/90 transition-colors"
                    >
                      <Trash2 size={12} className="text-white" />
                    </button>
                  </div>
                </div>
              )}

              {/* SEO edit form */}
              {editingId === item.id && (
                <div className="absolute inset-0 bg-white/95 p-3 flex flex-col gap-2">
                  <label className="font-mono text-[10px] text-text-muted uppercase tracking-widest">Alt mətn</label>
                  <input
                    autoFocus
                    value={editAlt}
                    onChange={(e) => setEditAlt(e.target.value)}
                    className="w-full bg-surface-2 border border-border rounded px-2 py-1 text-xs font-sans text-text-primary outline-none"
                    onClick={(e) => e.stopPropagation()}
                  />
                  <label className="font-mono text-[10px] text-text-muted uppercase tracking-widest">Başlıq</label>
                  <input
                    value={editTitle}
                    onChange={(e) => setEditTitle(e.target.value)}
                    className="w-full bg-surface-2 border border-border rounded px-2 py-1 text-xs font-sans text-text-primary outline-none"
                    onClick={(e) => e.stopPropagation()}
                  />
                  <div className="flex gap-1.5 mt-auto">
                    <button
                      onClick={(e) => { e.stopPropagation(); saveEdit(item.id); }}
                      className="flex-1 bg-text-primary text-white font-mono text-xs py-1.5 rounded-lg"
                    >
                      Saxla
                    </button>
                    <button
                      onClick={(e) => { e.stopPropagation(); setEditingId(null); }}
                      className="p-1.5 border border-border rounded-lg text-text-muted"
                    >
                      <X size={11} />
                    </button>
                  </div>
                </div>
              )}

              {/* File name */}
              {!editingId && (
                <div className="p-2 bg-white border-t border-border">
                  <p className="font-mono text-[10px] text-text-muted truncate" title={item.fileName}>
                    {item.fileName}
                  </p>
                  {item.altText && (
                    <p className="font-sans text-[10px] text-text-secondary truncate mt-0.5" title={item.altText}>
                      alt: {item.altText}
                    </p>
                  )}
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {/* Picker confirm bar */}
      {isPicker && (
        <div className="mt-4 pt-4 border-t border-border flex items-center justify-between">
          <p className="font-sans text-sm text-text-secondary">
            {multiSelect
              ? selectedIds.length > 0 ? `${selectedIds.length} şəkil seçildi` : 'Şəkil seçilməyib'
              : selectedId ? '1 şəkil seçildi' : 'Şəkil seçilməyib'}
          </p>
          <div className="flex gap-2">
            {onClose && (
              <button onClick={onClose} className="btn-ghost text-xs py-2">İmtina</button>
            )}
            <button
              onClick={confirmSelect}
              disabled={multiSelect ? selectedIds.length === 0 : !selectedId}
              className="btn-primary text-xs py-2 disabled:opacity-40"
            >
              Seç
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

function readFileAsDataUrl(file: File): Promise<string> {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result as string);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
}
