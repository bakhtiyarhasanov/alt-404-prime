import { useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface GalleryImage {
  url: string;
  alt: string;
}

interface Props {
  images: GalleryImage[];
}

export default function GallerySlider({ images }: Props) {
  const [active, setActive] = useState(0);

  if (!images.length) return null;

  const prev = () => setActive((i) => (i - 1 + images.length) % images.length);
  const next = () => setActive((i) => (i + 1) % images.length);

  return (
    <div className="my-8 rounded-xl overflow-hidden border border-border shadow-card">
      {/* ── Main image viewer ── */}
      <div
        className="relative flex items-center justify-center"
        style={{ background: '#080117', height: 420 }}
      >
        <img
          key={active}
          src={images[active].url}
          alt={images[active].alt}
          style={{
            maxHeight: '100%',
            maxWidth: '100%',
            objectFit: 'contain',
            display: 'block',
          }}
        />

        {images.length > 1 && (
          <>
            {/* Left arrow */}
            <button
              onClick={prev}
              aria-label="Əvvəlki"
              className="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full flex items-center justify-center bg-black/60 hover:bg-black/85 text-white border border-white/10 transition-all hover:scale-105"
            >
              <ChevronLeft size={20} />
            </button>
            {/* Right arrow */}
            <button
              onClick={next}
              aria-label="Növbəti"
              className="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full flex items-center justify-center bg-black/60 hover:bg-black/85 text-white border border-white/10 transition-all hover:scale-105"
            >
              <ChevronRight size={20} />
            </button>

            {/* Counter badge */}
            <span className="absolute bottom-3 right-3 font-mono text-[11px] text-white/70 bg-black/50 px-2.5 py-1 rounded-full border border-white/10">
              {active + 1} / {images.length}
            </span>
          </>
        )}

        {/* Caption */}
        {images[active].alt && (
          <span className="absolute bottom-3 left-3 font-sans text-[12px] text-white/55 max-w-[60%] truncate">
            {images[active].alt}
          </span>
        )}
      </div>

      {/* ── Thumbnails ── */}
      {images.length > 1 && (
        <div
          className="flex gap-2 p-3 overflow-x-auto"
          style={{ background: '#0d0d1a' }}
        >
          {images.map((img, i) => (
            <button
              key={i}
              onClick={() => setActive(i)}
              aria-label={img.alt || `Şəkil ${i + 1}`}
              style={{
                flexShrink: 0,
                width: 68,
                height: 48,
                borderRadius: 6,
                overflow: 'hidden',
                border: i === active ? '2px solid #FCDB56' : '2px solid transparent',
                opacity: i === active ? 1 : 0.5,
                transition: 'border-color 0.15s, opacity 0.15s',
                padding: 0,
                cursor: 'pointer',
                background: '#080117',
              }}
            >
              <img
                src={img.url}
                alt={img.alt}
                style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }}
              />
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
