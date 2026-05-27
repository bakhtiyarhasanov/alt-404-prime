import { useEffect } from 'react';
import { BrowserRouter, Routes, Route, useLocation, useParams, Navigate } from 'react-router-dom';
import Header from './components/Header';
import Footer from './components/Footer';
import Home from './pages/Home';
import CategoryPage from './pages/CategoryPage';
import ArticlePage from './pages/ArticlePage';
import SearchPage from './pages/SearchPage';
import AdminPage from './pages/admin/AdminPage';
import About from './pages/About';
import Contact from './pages/Contact';
import Terms from './pages/Terms';
import Cookies from './pages/Cookies';
import { AppProvider, useApp } from './lib/appContext';

function ScrollToTop() {
  const { pathname, search } = useLocation();
  useEffect(() => {
    window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
  }, [pathname, search]);
  return null;
}

function Layout({ children }: { children: React.ReactNode }) {
  return (
    <>
      <Header />
      {children}
      <Footer />
    </>
  );
}

function KeyedArticlePage() {
  const { pathname } = useLocation();
  return <ArticlePage key={pathname} />;
}

// Legacy redirect: /xeber/:slug → /:category/:slug
function LegacyXeberSlugRedirect() {
  const { slug } = useParams<{ slug: string }>();
  const { articles } = useApp();
  const article = articles.find((a) => a.slug === slug);
  if (!article) return <Navigate to="/" replace />;
  return <Navigate to={`/${article.category}/${article.slug}`} replace />;
}

// Legacy redirect: /xeber/:category/:slug → /:category/:slug
function LegacyXeberCategorySlugRedirect() {
  const { category, slug } = useParams<{ category: string; slug: string }>();
  return <Navigate to={`/${category}/${slug}`} replace />;
}

// Legacy redirect: /kateqoriya/:slug → /:slug
function LegacyKateqoriyaRedirect() {
  const { slug } = useParams<{ slug: string }>();
  return <Navigate to={`/${slug}`} replace />;
}

// Resolves /:categorySlug to CategoryPage only if the slug is a known category.
// Falls back to a 404 for any unknown path so random slugs don't silently render an empty category.
function CategoryOrNotFound() {
  const { categorySlug } = useParams<{ categorySlug: string }>();
  const { categories } = useApp();
  const isKnown = categories.some((c) => c.slug === categorySlug);
  if (!isKnown) {
    return (
      <main className="dot-matrix min-h-screen flex items-center justify-center bg-canvas">
        <div className="text-center">
          <p className="font-mono text-7xl font-bold text-text-muted/30 mb-4">404</p>
          <p className="font-mono text-sm text-text-muted mb-6">Səhifə tapılmadı</p>
          <Navigate to="/" replace />
        </div>
      </main>
    );
  }
  return <CategoryPage />;
}

export default function App() {
  return (
    <AppProvider>
      <BrowserRouter>
        <ScrollToTop />
        <Routes>
          {/* Admin — no layout chrome */}
          <Route path="/admin" element={<AdminPage />} />

          <Route
            path="/*"
            element={
              <Layout>
                <Routes>
                  {/* ── Static routes (must come before dynamic ones) ── */}
                  <Route path="/" element={<Home />} />
                  <Route path="/axtar" element={<SearchPage />} />
                  <Route path="/haqqimizda" element={<About />} />
                  <Route path="/elaqe" element={<Contact />} />
                  <Route path="/istifade-sertleri" element={<Terms />} />
                  <Route path="/cerezler" element={<Cookies />} />

                  {/* ── Legacy redirects (preserves old shared links) ── */}
                  <Route path="/kateqoriya/:slug" element={<LegacyKateqoriyaRedirect />} />
                  <Route path="/xeber/:category/:slug" element={<LegacyXeberCategorySlugRedirect />} />
                  <Route path="/xeber/:slug" element={<LegacyXeberSlugRedirect />} />

                  {/* ── Canonical clean URLs ── */}
                  {/* /:categorySlug/:postSlug  →  ArticlePage */}
                  <Route path="/:categorySlug/:postSlug" element={<KeyedArticlePage />} />
                  {/* /:categorySlug  →  CategoryPage (guarded against unknown slugs) */}
                  <Route path="/:categorySlug" element={<CategoryOrNotFound />} />
                </Routes>
              </Layout>
            }
          />
        </Routes>
      </BrowserRouter>
    </AppProvider>
  );
}
