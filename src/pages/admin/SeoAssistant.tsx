import { useMemo } from 'react';
import { CheckCircle, AlertCircle, TrendingUp } from 'lucide-react';
import { calculateSeoScore } from '../../lib/utils';

interface Props {
  title: string;
  content: string;
  tags: string[];
}

export default function SeoAssistant({ title, content, tags }: Props) {
  const { score, tips } = useMemo(
    () => calculateSeoScore(title, content, tags),
    [title, content, tags]
  );

  const color =
    score >= 75 ? 'text-emerald-400' :
    score >= 50 ? 'text-yellow-400' :
    'text-alt-red';

  const barColor =
    score >= 75 ? 'bg-emerald-400' :
    score >= 50 ? 'bg-yellow-400' :
    'bg-alt-red';

  return (
    <div className="glass-card rounded-xl p-5">
      <div className="flex items-center gap-2 mb-4">
        <TrendingUp size={14} className="text-alt-red" />
        <span className="font-mono text-xs font-semibold text-text-secondary uppercase tracking-widest">
          SEO Köməkçisi
        </span>
        <span className={`ml-auto font-mono text-lg font-bold ${color}`}>{score}/100</span>
      </div>

      {/* Score bar */}
      <div className="h-1.5 bg-surface-3 rounded-full mb-5 overflow-hidden">
        <div
          className={`h-full rounded-full transition-all duration-500 ${barColor}`}
          style={{ width: `${score}%` }}
        />
      </div>

      {/* Tips */}
      <ul className="space-y-2">
        {tips.map((tip, i) => (
          <li key={i} className="flex items-start gap-2">
            {score === 100 || tip.startsWith('Əla') ? (
              <CheckCircle size={13} className="text-emerald-400 mt-0.5 flex-shrink-0" />
            ) : (
              <AlertCircle size={13} className="text-yellow-400 mt-0.5 flex-shrink-0" />
            )}
            <span className="font-sans text-xs text-text-secondary leading-relaxed">{tip}</span>
          </li>
        ))}
      </ul>

      {/* Word count */}
      <div className="mt-4 pt-4 border-t border-border flex items-center justify-between">
        <span className="font-mono text-xs text-text-muted">Söz sayı</span>
        <span className="font-mono text-xs text-text-secondary">
          {content.replace(/<[^>]*>/g, '').split(/\s+/).filter(Boolean).length}
        </span>
      </div>
      <div className="flex items-center justify-between mt-1.5">
        <span className="font-mono text-xs text-text-muted">Başlıq uzunluğu</span>
        <span className={`font-mono text-xs ${title.length >= 30 && title.length <= 70 ? 'text-emerald-400' : 'text-yellow-400'}`}>
          {title.length} sim
        </span>
      </div>
    </div>
  );
}
