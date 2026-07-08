import { useState, useEffect, useRef } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { Search, X, Menu } from 'lucide-react';
import { useApp } from '../lib/appContext';
import { getVisibleCategories } from '../lib/categoryConfig';

export default function Header() {
  const [scrolled, setScrolled] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [menuOpen, setMenuOpen] = useState(false);
  const location = useLocation();
  const navigate = useNavigate();
  const { articles, categories } = useApp();
  const searchRef = useRef<HTMLDivElement>(null);

  // pages that have a dark hero — header starts transparent/dark
  // Matches home page and article pages (two path segments: /:category/:slug)
  const pathSegments = location.pathname.split('/').filter(Boolean);
  const isHeroPage = location.pathname === '/' || pathSegments.length === 2;

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 60);
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  useEffect(() => {
    setMenuOpen(false);
    setSearchOpen(false);
    setScrolled(window.scrollY > 60);
  }, [location]);

  const published = articles.filter((a) => a.published);
  const suggestions = searchQuery.trim().length > 0
    ? published
        .filter((a) => a.title.toLowerCase().includes(searchQuery.toLowerCase()))
        .slice(0, 5)
    : [];

  useEffect(() => {
    function handleClickOutside(e: MouseEvent) {
      if (searchRef.current && !searchRef.current.contains(e.target as Node)) {
        setSearchOpen(false);
        setSearchQuery('');
      }
    }
    if (searchOpen) document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, [searchOpen]);

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      navigate(`/axtar?q=${encodeURIComponent(searchQuery.trim())}`);
      setSearchOpen(false);
      setSearchQuery('');
    }
  };

  const handleSuggestionClick = (category: string, slug: string) => {
    navigate(`/${category}/${slug}`);
    setSearchOpen(false);
    setSearchQuery('');
  };

  const dark = isHeroPage && !scrolled;
  const visibleCategories = getVisibleCategories(categories);
  const goHome = () => {
    if (location.pathname === '/' && !location.search) {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }
    navigate({ pathname: '/', search: '' });
  };

  const headerCategories = visibleCategories.map((cat) => ({
    ...cat,
    navLabel:
      cat.slug === 'texnologiya'
        ? cat.label
        : cat.label.replace(/\s+Xəbərləri$/i, ''),
  }));

  return (
    <header className="fixed top-0 left-0 right-0 z-50 flex justify-center pt-4 px-4">
      <nav
        className={`rounded-2xl w-full max-w-5xl transition-all duration-400 ${
          dark ? 'capsule-nav-hero' : 'capsule-nav'
        } ${scrolled ? 'shadow-capsule' : ''}`}
      >
        <div className="flex items-center gap-3 px-4 lg:px-5 h-14">

          {/* Logo */}
          <Link
            to="/"
            onClick={goHome}
            className="flex items-center gap-2 flex-shrink-0 mr-2"
          >
            <Logo dark={dark} />
          </Link>

          {/* Desktop Nav */}
          <div className="hidden md:flex flex-1 min-w-0 items-center justify-start gap-0.5 overflow-hidden">
            <NavLink
              to="/"
              label="Ana Səhifə"
              active={location.pathname === '/' && !location.search}
              dark={dark}
              onClick={goHome}
            />
            {headerCategories.map((cat) => (
              <NavLink
                key={cat.slug}
                to={`/${cat.slug}`}
                label={cat.navLabel}
                active={location.pathname === `/${cat.slug}`}
                dark={dark}
              />
            ))}
          </div>

          {/* Actions — mobil: sağda */}
          <div className="flex items-center gap-1 flex-shrink-0 ml-auto">
            {/* Search — dropdown opens BELOW the button */}
            <div ref={searchRef} className="relative">
              <button
                onClick={() => { setSearchOpen((v) => !v); setSearchQuery(''); }}
                aria-label="Axtar"
                className={`p-2 rounded-lg transition-colors ${dark ? 'text-white/70 hover:text-white hover:bg-white/10' : 'text-text-secondary hover:text-text-primary hover:bg-surface-2'}`}
              >
                {searchOpen ? <X size={15} /> : <Search size={15} />}
              </button>

              {searchOpen && (
                <div
                  className={`absolute top-full right-0 mt-2 w-72 max-w-[calc(100vw-3rem)] rounded-xl shadow-xl border z-50 p-2 animate-fade-in ${
                    dark ? 'bg-[#0f0825] border-white/12' : 'bg-surface border-border'
                  }`}
                >
                  <form onSubmit={handleSearch} className="flex items-center gap-1.5">
                    <input
                      autoFocus
                      type="text"
                      value={searchQuery}
                      onChange={(e) => setSearchQuery(e.target.value)}
                      placeholder="Axtar..."
                      className={`flex-1 min-w-0 rounded-lg px-3 py-2 text-base md:text-sm outline-none transition-all font-sans border ${
                        dark
                          ? 'bg-white/10 border-white/20 text-white placeholder-white/40 focus:border-white/40'
                          : 'bg-surface-2 border-border text-text-primary placeholder-text-muted focus:border-brand/50'
                      }`}
                    />
                    <button
                      type="submit"
                      aria-label="Axtar"
                      className={`p-2 rounded-lg flex-shrink-0 transition-colors ${dark ? 'text-white/70 hover:text-white hover:bg-white/10' : 'text-text-secondary hover:text-text-primary hover:bg-surface-2'}`}
                    >
                      <Search size={15} />
                    </button>
                  </form>

                  {suggestions.length > 0 && (
                    <div className="mt-2 -mx-2 border-t pt-1 overflow-hidden border-inherit">
                      {suggestions.map((article) => (
                        <button
                          key={article.id}
                          onMouseDown={() => handleSuggestionClick(article.category, article.slug)}
                          className={`w-full flex items-center gap-3 px-3 py-2.5 text-left transition-colors ${
                            dark ? 'hover:bg-white/8' : 'hover:bg-surface-2'
                          }`}
                        >
                          <div className="w-9 h-9 rounded-lg overflow-hidden flex-shrink-0">
                            {article.image_url ? (
                              <img src={article.image_url} alt="" className="w-full h-full object-cover" />
                            ) : (
                              <div className={`w-full h-full ${dark ? 'bg-white/10' : 'bg-surface-2'}`} />
                            )}
                          </div>
                          <span className={`font-mono text-[11px] font-medium leading-snug line-clamp-2 ${
                            dark ? 'text-white/85' : 'text-text-primary'
                          }`}>
                            {article.title}
                          </span>
                        </button>
                      ))}
                    </div>
                  )}
                </div>
              )}
            </div>

            <button
              className={`md:hidden p-2 rounded-lg transition-colors ${dark ? 'text-white/70 hover:text-white hover:bg-white/10' : 'text-text-secondary hover:text-text-primary hover:bg-surface-2'}`}
              onClick={() => setMenuOpen(!menuOpen)}
            >
              {menuOpen ? <X size={15} /> : <Menu size={15} />}
            </button>
          </div>
        </div>

        {/* Mobile Menu */}
        {menuOpen && (
          <div className={`md:hidden border-t px-5 py-3 flex flex-col gap-0.5 animate-fade-in ${dark ? 'border-white/10' : 'border-border'}`}>
            <MobileNavLink to="/" label="Ana Səhifə" dark={dark} onClick={goHome} />
            {visibleCategories.map((cat) => (
              <MobileNavLink key={cat.slug} to={`/${cat.slug}`} label={cat.label} dark={dark} />
            ))}
          </div>
        )}
      </nav>
    </header>
  );
}

function Logo({ dark }: { dark: boolean }) {
  return (
    <div className="flex items-center gap-1.5 min-w-0">
      <img
        src="/logo.png"
        alt="alt404.com Tech News"
        className={`h-8 w-auto max-w-[150px] object-contain transition-all rounded-sm ${dark ? 'brightness-0 invert' : ''}`}
      />
      <span className="live-dot" />
    </div>
  );
}

function NavLink({
  to,
  label,
  active,
  dark,
  onClick,
}: {
  to: string;
  label: string;
  active: boolean;
  dark: boolean;
  onClick?: () => void;
}) {
  return (
    <Link
      to={to}
      onClick={onClick}
      className={`font-mono text-[11px] font-medium px-2 py-1.5 rounded-lg transition-all duration-200 tracking-wide whitespace-nowrap ${
        dark
          ? active
            ? 'text-white bg-white/15 border-b-2 border-brand/60'
            : 'text-white/70 hover:text-white hover:bg-white/10'
          : active
            ? 'text-text-primary bg-surface-2'
            : 'text-text-secondary hover:text-text-primary hover:bg-surface-2'
      }`}
    >
      {label}
    </Link>
  );
}

function MobileNavLink({
  to,
  label,
  dark,
  onClick,
}: {
  to: string;
  label: string;
  dark: boolean;
  onClick?: () => void;
}) {
  return (
    <Link
      to={to}
      onClick={onClick}
      className={`font-mono text-sm py-2 px-2 rounded-lg transition-colors ${
        dark ? 'text-white/70 hover:text-white hover:bg-white/10' : 'text-text-secondary hover:text-text-primary hover:bg-surface-2'
      }`}
    >
      {label}
    </Link>
  );
}
