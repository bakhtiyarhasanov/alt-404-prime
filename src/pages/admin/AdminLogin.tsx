import { useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';
import { supabase } from '../../lib/supabase';

interface Props {
  onLogin: () => void;
}

export default function AdminLogin({ onLogin }: Props) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPw, setShowPw] = useState(false);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    const { error: err } = await supabase.auth.signInWithPassword({ email, password });
    setLoading(false);
    if (err) {
      setError('E-poçt və ya şifrə yanlışdır.');
    } else {
      onLogin();
    }
  };

  return (
    <div className="dot-matrix min-h-screen flex items-center justify-center px-4 bg-[#f8f9fa]">
      <div className="w-full max-w-sm">
        <div className="text-center mb-10">
          <div className="flex items-center justify-center mb-3">
            <img src="/logo.png" alt="alt404.com Tech News" className="h-10 w-auto object-contain" />
          </div>
          <p className="font-mono text-xs text-text-muted uppercase tracking-widest">Admin Panel</p>
        </div>

        <form onSubmit={handleSubmit} className="bg-white rounded-2xl border border-border shadow-sm p-8 space-y-5">
          <div>
            <label className="font-mono text-xs text-text-muted uppercase tracking-widest block mb-2">
              E-poçt
            </label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              className="w-full bg-surface-2 border border-border rounded-lg px-4 py-2.5 text-sm text-text-primary placeholder-text-muted outline-none focus:border-alt-red/40 transition-colors font-sans"
              placeholder="admin@alt404.com"
            />
          </div>

          <div>
            <label className="font-mono text-xs text-text-muted uppercase tracking-widest block mb-2">
              Şifrə
            </label>
            <div className="relative">
              <input
                type={showPw ? 'text' : 'password'}
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                className="w-full bg-surface-2 border border-border rounded-lg px-4 py-2.5 pr-10 text-sm text-text-primary placeholder-text-muted outline-none focus:border-alt-red/40 transition-colors font-sans"
                placeholder="••••••••"
              />
              <button
                type="button"
                onClick={() => setShowPw(!showPw)}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted hover:text-text-primary transition-colors"
              >
                {showPw ? <EyeOff size={14} /> : <Eye size={14} />}
              </button>
            </div>
          </div>

          {error && (
            <p className="font-mono text-xs text-alt-red bg-red-50 border border-red-200 rounded-lg px-3 py-2">
              {error}
            </p>
          )}

          <button
            type="submit"
            disabled={loading}
            className="btn-primary w-full py-3 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {loading ? 'Giriş edilir...' : 'Daxil Ol'}
          </button>
        </form>
      </div>
    </div>
  );
}
