import { ToggleLeft, ToggleRight, ExternalLink } from 'lucide-react';
import { useApp } from '../../lib/appContext';
import type { AdZone } from '../../lib/adContext';

export default function AdManager() {
  const { ads, updateAd } = useApp();

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between mb-6">
        <div>
          <h2 className="font-mono text-lg font-bold text-text-primary">Reklam İdarəetməsi</h2>
          <p className="font-sans text-sm text-text-secondary mt-1">
            Hər reklam zonasını ayrıca idarə edin
          </p>
        </div>
      </div>

      {ads.map((ad) => (
        <AdZoneEditor key={ad.id} ad={ad} onUpdate={(patch) => updateAd(ad.id, patch)} />
      ))}
    </div>
  );
}

function AdZoneEditor({ ad, onUpdate }: { ad: AdZone; onUpdate: (patch: Partial<AdZone>) => void }) {
  const isSidebar = ad.id.includes('sidebar');

  return (
    <div className={`bg-white rounded-xl border transition-all duration-200 overflow-hidden ${
      ad.enabled ? 'border-border' : 'border-border opacity-60'
    }`}>
      {/* Header row */}
      <div className="flex items-center justify-between px-5 py-4 border-b border-border">
        <div className="flex items-center gap-3">
          <div className={`w-2 h-2 rounded-full ${ad.enabled ? 'bg-emerald-500' : 'bg-text-muted'}`} />
          <div>
            <p className="font-mono text-sm font-semibold text-text-primary">{ad.label}</p>
            <p className="font-mono text-xs text-text-muted mt-0.5">
              {ad.width} × {ad.height}px
            </p>
          </div>
        </div>
        <button
          type="button"
          onClick={() => onUpdate({ enabled: !ad.enabled })}
          className="flex items-center gap-2 transition-colors"
          title={ad.enabled ? 'Söndür' : 'Yandır'}
        >
          {ad.enabled ? (
            <ToggleRight size={28} className="text-emerald-500" />
          ) : (
            <ToggleLeft size={28} className="text-text-muted" />
          )}
          <span className="font-mono text-xs text-text-secondary">
            {ad.enabled ? 'Aktiv' : 'Deaktiv'}
          </span>
        </button>
      </div>

      {/* Fields */}
      <div className="px-5 py-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label className="font-mono text-xs text-text-muted uppercase tracking-widest block mb-1.5">
            Şəkil URL
          </label>
          <input
            type="url"
            value={ad.imageUrl}
            onChange={(e) => onUpdate({ imageUrl: e.target.value })}
            placeholder="https://..."
            className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 text-xs font-sans text-text-primary placeholder-text-muted outline-none focus:border-alt-red/40 transition-colors"
          />
        </div>
        <div>
          <label className="font-mono text-xs text-text-muted uppercase tracking-widest block mb-1.5">
            Hədəf Link
          </label>
          <div className="relative">
            <input
              type="url"
              value={ad.linkUrl}
              onChange={(e) => onUpdate({ linkUrl: e.target.value })}
              placeholder="https://..."
              className="w-full bg-surface-2 border border-border rounded-lg px-3 py-2 pr-8 text-xs font-sans text-text-primary placeholder-text-muted outline-none focus:border-alt-red/40 transition-colors"
            />
            {ad.linkUrl && ad.linkUrl !== '#' && (
              <a
                href={ad.linkUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="absolute right-2.5 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-primary transition-colors"
              >
                <ExternalLink size={12} />
              </a>
            )}
          </div>
        </div>

        {/* Preview */}
        {ad.imageUrl && (
          <div className="md:col-span-2">
            <label className="font-mono text-xs text-text-muted uppercase tracking-widest block mb-1.5">
              Önizləmə
            </label>
            <div
              className="rounded-lg overflow-hidden border border-border bg-surface-2"
              style={{ height: isSidebar ? 120 : 60 }}
            >
              <img
                src={ad.imageUrl}
                alt={ad.label}
                className="w-full h-full object-cover"
              />
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
