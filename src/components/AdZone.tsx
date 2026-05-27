import { useAd } from '../lib/appContext';

interface Props {
  id: string;
  className?: string;
}

export default function AdZone({ id, className = '' }: Props) {
  const ad = useAd(id);
  if (!ad || !ad.enabled) return null;

  const isSidebar = id.includes('sidebar');
  const h = isSidebar ? 600 : 90;

  return (
    <a
      href={ad.linkUrl || '#'}
      target="_blank"
      rel="noopener noreferrer"
      className={`relative block overflow-hidden rounded-xl neo-inset group transition-all hover:shadow-card ${className}`}
      style={{ width: isSidebar ? 160 : '100%', height: h }}
    >
      {ad.imageUrl ? (
        <img
          src={ad.imageUrl}
          alt="Reklam"
          className="w-full h-full object-cover opacity-90 group-hover:opacity-100 transition-opacity"
        />
      ) : (
        <div className="w-full h-full flex flex-col items-center justify-center gap-1.5">
          <span className="font-mono text-[10px] text-text-muted uppercase tracking-widest">Reklam</span>
          <span className="font-mono text-[9px] text-text-muted">{ad.width}×{ad.height}</span>
        </div>
      )}
      <span className="ad-label">Reklam</span>
    </a>
  );
}
