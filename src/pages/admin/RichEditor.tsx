import { useRef, useEffect, useCallback } from 'react';
import {
  Bold, Italic, Quote, Image, Heading1, Heading2, Heading3,
  AlignLeft, List, LayoutGrid, Link2,
} from 'lucide-react';
import type { MediaItem } from '../../lib/appContext';

interface Props {
  value: string;
  onChange: (val: string) => void;
  onOpenMediaPicker: (onSelect: (items: MediaItem[]) => void, multi?: boolean) => void;
}

// Build the injected placeholder HTML for a gallery block
function buildGalleryPlaceholder(items: Array<{ url: string; alt: string }>): string {
  const json = JSON.stringify(items);
  const b64 = btoa(unescape(encodeURIComponent(json)));
  return (
    `<div data-gallery-b64="${b64}" contenteditable="false" ` +
    `style="margin:1.5rem 0;display:flex;align-items:center;justify-content:space-between;` +
    `gap:10px;padding:10px 14px;background:#f5f5f0;border:1px solid rgba(0,0,0,0.09);` +
    `border-radius:10px;font-family:monospace;font-size:12px;color:#666;user-select:none">` +
    // Thumbnail strip
    `<div style="display:flex;align-items:center;gap:8px;overflow:hidden">` +
    `<span style="font-size:15px">🖼</span>` +
    `<span style="color:#444;font-weight:600">Qalereya</span>` +
    `<span style="color:#aaa">—&nbsp;${items.length} şəkil</span>` +
    `<div style="display:flex;gap:4px;margin-left:4px">` +
    items.slice(0, 4).map(
      (img) =>
        `<img src="${img.url}" alt="${img.alt}" style="width:32px;height:24px;object-fit:cover;border-radius:4px;border:1px solid rgba(0,0,0,0.08)">`
    ).join('') +
    (items.length > 4 ? `<span style="font-size:11px;color:#aaa;align-self:center">+${items.length - 4}</span>` : '') +
    `</div></div>` +
    // Action buttons
    `<div style="display:flex;gap:6px;flex-shrink:0">` +
    `<button data-gallery-action="edit" ` +
    `style="padding:4px 10px;font-size:11px;font-family:monospace;border-radius:6px;` +
    `border:1px solid rgba(0,0,0,0.12);background:#fff;color:#444;cursor:pointer;white-space:nowrap">` +
    `✎ Düzəlt</button>` +
    `<button data-gallery-action="delete" ` +
    `style="padding:4px 10px;font-size:11px;font-family:monospace;border-radius:6px;` +
    `border:1px solid rgba(220,50,50,0.25);background:#fff5f5;color:#c0392b;cursor:pointer">` +
    `✕ Sil</button>` +
    `</div>` +
    `</div>`
  );
}

export default function RichEditor({ value, onChange, onOpenMediaPicker }: Props) {
  const editorRef = useRef<HTMLDivElement>(null);
  // Stable ref to onOpenMediaPicker so the delegated dblclick handler always sees the latest version
  const pickerRef = useRef(onOpenMediaPicker);
  pickerRef.current = onOpenMediaPicker;

  // Only set innerHTML from props when editor is not focused (loading a different article).
  // While focused the browser manages the DOM — prevents caret reset on every keystroke.
  useEffect(() => {
    const el = editorRef.current;
    if (!el) return;
    if (document.activeElement !== el && el.innerHTML !== value) {
      el.innerHTML = value;
    }
  }, [value]);

  const emit = useCallback(() => {
    onChange(editorRef.current?.innerHTML || '');
  }, [onChange]);

  // ── Event delegation: handle gallery block actions ──────────────────────
  useEffect(() => {
    const el = editorRef.current;
    if (!el) return;

    const handleClick = (e: MouseEvent) => {
      const target = e.target as HTMLElement;
      const action = target.getAttribute('data-gallery-action');
      if (!action) return;

      e.preventDefault();
      e.stopPropagation();

      const block = target.closest('[data-gallery-b64]') as HTMLElement | null;
      if (!block) return;

      if (action === 'delete') {
        block.remove();
        onChange(el.innerHTML);
        return;
      }

      if (action === 'edit') {
        const b64 = block.getAttribute('data-gallery-b64')!;
        let current: Array<{ url: string; alt: string }> = [];
        try { current = JSON.parse(decodeURIComponent(escape(atob(b64)))); } catch { /* ignore */ }

        pickerRef.current(
          (selectedItems) => {
            if (!selectedItems.length) return;
            const newItems = selectedItems.map((m) => ({ url: m.url, alt: m.altText || m.title }));
            block.outerHTML = buildGalleryPlaceholder(newItems);
            onChange(el.innerHTML);
          },
          true,
        );
        // Highlight current selection hint via console (no pre-selection API available)
        void current;
      }
    };

    // Double-click anywhere on the block also opens edit
    const handleDblClick = (e: MouseEvent) => {
      const target = e.target as HTMLElement;
      const block = target.closest('[data-gallery-b64]') as HTMLElement | null;
      if (!block) return;
      // Simulate clicking the edit button
      const editBtn = block.querySelector('[data-gallery-action="edit"]') as HTMLElement | null;
      editBtn?.click();
    };

    el.addEventListener('click', handleClick);
    el.addEventListener('dblclick', handleDblClick);
    return () => {
      el.removeEventListener('click', handleClick);
      el.removeEventListener('dblclick', handleDblClick);
    };
  }, [onChange]);

  const handlePaste = useCallback((e: React.ClipboardEvent<HTMLDivElement>) => {
    e.preventDefault();
    const raw = e.clipboardData.getData('text/html') || e.clipboardData.getData('text/plain');

    // Parse into a temporary DOM so we can walk and sanitize it
    const tmp = document.createElement('div');
    tmp.innerHTML = raw;

    // Tags whose entire subtree we drop
    const BLOCK_TAGS = new Set(['script', 'style', 'head', 'meta', 'link', 'font', 'span']);
    // Tags we keep (semantic only)
    const ALLOW_TAGS = new Set([
      'p', 'br', 'b', 'strong', 'i', 'em', 'u', 'a',
      'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
      'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
      'figure', 'figcaption', 'img',
    ]);

    function sanitizeNode(node: Node): Node | null {
      if (node.nodeType === Node.TEXT_NODE) return node.cloneNode();
      if (node.nodeType !== Node.ELEMENT_NODE) return null;

      const el = node as Element;
      const tag = el.tagName.toLowerCase();

      if (BLOCK_TAGS.has(tag)) return null;

      const children: Node[] = [];
      for (const child of Array.from(el.childNodes)) {
        const cleaned = sanitizeNode(child);
        if (cleaned) children.push(cleaned);
      }

      if (!ALLOW_TAGS.has(tag)) {
        // Unwrap: keep children, discard the element itself
        const frag = document.createDocumentFragment();
        children.forEach((c) => frag.appendChild(c));
        return frag;
      }

      const clean = document.createElement(tag);
      // Keep only safe attributes
      if (tag === 'a') {
        const href = el.getAttribute('href');
        if (href) { clean.setAttribute('href', href); clean.setAttribute('target', '_blank'); clean.setAttribute('rel', 'noopener noreferrer'); }
      }
      if (tag === 'img') {
        const src = el.getAttribute('src');
        const alt = el.getAttribute('alt') || '';
        if (src) { clean.setAttribute('src', src); clean.setAttribute('alt', alt); }
      }
      children.forEach((c) => clean.appendChild(c));
      return clean;
    }

    const frag = document.createDocumentFragment();
    for (const child of Array.from(tmp.childNodes)) {
      const cleaned = sanitizeNode(child);
      if (cleaned) frag.appendChild(cleaned);
    }

    const wrapper = document.createElement('div');
    wrapper.appendChild(frag);
    const cleanHtml = wrapper.innerHTML || tmp.textContent || '';

    document.execCommand('insertHTML', false, cleanHtml);
    emit();
  }, [emit]);

  const exec = useCallback((command: string, val?: string) => {
    editorRef.current?.focus();
    document.execCommand(command, false, val);
    emit();
  }, [emit]);

  const insertBlock = useCallback((html: string) => {
    editorRef.current?.focus();
    document.execCommand('insertHTML', false, html);
    emit();
  }, [emit]);

  const insertLink = useCallback(() => {
    const selection = window.getSelection();
    const selectedText = selection?.toString().trim();
    if (!selectedText) {
      window.alert('Əvvəlcə mətni seçin.');
      return;
    }
    const url = window.prompt('URL daxil edin:', 'https://');
    if (!url || url === 'https://') return;
    editorRef.current?.focus();
    document.execCommand('createLink', false, url);
    const links = editorRef.current?.querySelectorAll(`a[href="${url}"]`);
    links?.forEach((a) => {
      a.setAttribute('target', '_blank');
      a.setAttribute('rel', 'noopener noreferrer');
    });
    emit();
  }, [emit]);

  const insertSingleImage = (item: MediaItem) => {
    insertBlock(
      `<figure style="margin:1.5rem 0"><img src="${item.url}" alt="${item.altText || item.title}" style="width:100%;border-radius:12px;border:1px solid rgba(0,0,0,0.06)" />${item.altText ? `<figcaption style="font-size:0.8rem;color:#888;text-align:center;margin-top:6px">${item.altText}</figcaption>` : ''}</figure>`
    );
  };

  const insertGallery = (items: MediaItem[]) => {
    const mapped = items.map((m) => ({ url: m.url, alt: m.altText || m.title }));
    insertBlock(buildGalleryPlaceholder(mapped));
  };

  type ToolbarBtn = { icon: React.ReactNode; label: string; action: () => void } | { sep: true };

  const toolbarBtns: ToolbarBtn[] = [
    { icon: <Heading1 size={14} />, label: 'H1', action: () => exec('formatBlock', 'h1') },
    { icon: <Heading2 size={14} />, label: 'H2', action: () => exec('formatBlock', 'h2') },
    { icon: <Heading3 size={14} />, label: 'H3', action: () => exec('formatBlock', 'h3') },
    { icon: <AlignLeft size={14} />, label: 'Paragraph', action: () => exec('formatBlock', 'p') },
    { sep: true },
    { icon: <Bold size={14} />, label: 'Qalın', action: () => exec('bold') },
    { icon: <Italic size={14} />, label: 'Kursiv', action: () => exec('italic') },
    { icon: <Link2 size={14} />, label: 'Keçid (Link)', action: insertLink },
    { sep: true },
    {
      icon: <Quote size={14} />,
      label: 'Sitat',
      action: () => insertBlock(
        '<blockquote style="border-left:2px solid #FCDB56;padding:12px 20px;background:rgba(252,219,86,0.06);border-radius:0 8px 8px 0;margin:1rem 0;font-style:italic;color:#444">Sitat buraya...</blockquote>'
      ),
    },
    { icon: <List size={14} />, label: 'Siyahı', action: () => exec('insertUnorderedList') },
    { sep: true },
    {
      icon: <Image size={14} />,
      label: 'Şəkil əlavə et',
      action: () => onOpenMediaPicker((items) => { if (items[0]) insertSingleImage(items[0]); }),
    },
    {
      icon: <LayoutGrid size={14} />,
      label: 'Qalereya əlavə et (çoxlu seçim)',
      action: () => onOpenMediaPicker((items) => { if (items.length) insertGallery(items); }, true),
    },
  ];

  return (
    <div className="border border-border rounded-xl overflow-hidden">
      <div className="flex items-center gap-0.5 px-2.5 py-2 border-b border-border bg-surface-2 flex-wrap">
        {toolbarBtns.map((btn, i) =>
          'sep' in btn ? (
            <div key={i} className="w-px h-4 bg-border mx-1" />
          ) : (
            <button
              key={i}
              type="button"
              title={btn.label}
              onMouseDown={(e) => {
                e.preventDefault();
                btn.action();
              }}
              className="p-1.5 rounded text-text-secondary hover:text-text-primary hover:bg-surface-3 transition-colors"
            >
              {btn.icon}
            </button>
          )
        )}
      </div>

      <div
        ref={editorRef}
        contentEditable
        suppressContentEditableWarning
        dir="ltr"
        onInput={emit}
        onPaste={handlePaste}
        className="article-content min-h-[300px] p-5 outline-none text-sm leading-relaxed focus:ring-0 bg-white"
        style={{ direction: 'ltr', textAlign: 'left' }}
      />
    </div>
  );
}
