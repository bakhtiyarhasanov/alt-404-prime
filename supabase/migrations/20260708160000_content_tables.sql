/*
  # Shared content tables (ads, categories, media) + admin membership

  Moves admin-managed content OFF per-browser localStorage and INTO Supabase so
  every visitor sees the same articles, ads and categories.

  1. articles: add `versions` (jsonb) column used by the editor history feature.
  2. ads: banner zones (public read, admin write).
  3. categories: site sections / SEO config (public read, admin write).
  4. media_library: uploaded media items (public read, admin write).
  5. Ensure the admin auth user has a row in `admin_users` so the existing
     admin-only write RLS policies on `articles` (and the new tables) pass.
  6. Seed default ads + categories so the public site is populated immediately.
*/

-- 1. Article version history column ----------------------------------------
ALTER TABLE articles ADD COLUMN IF NOT EXISTS versions jsonb DEFAULT '[]'::jsonb;

-- Helper predicate reused by policies: current user is a registered admin.
-- (Inlined per-policy below to avoid needing a separate function.)

-- 2. ads --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ads (
  id text PRIMARY KEY,
  label text NOT NULL DEFAULT '',
  enabled boolean NOT NULL DEFAULT true,
  image_url text NOT NULL DEFAULT '',
  link_url text NOT NULL DEFAULT '#',
  width integer NOT NULL DEFAULT 0,
  height integer NOT NULL DEFAULT 0,
  updated_at timestamptz DEFAULT now()
);

ALTER TABLE ads ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "Public can read ads" ON ads;
CREATE POLICY "Public can read ads"
  ON ads FOR SELECT
  TO anon, authenticated
  USING (true);

DROP POLICY IF EXISTS "Admins can write ads" ON ads;
CREATE POLICY "Admins can write ads"
  ON ads FOR ALL
  TO authenticated
  USING (EXISTS (SELECT 1 FROM admin_users WHERE admin_users.id = auth.uid()))
  WITH CHECK (EXISTS (SELECT 1 FROM admin_users WHERE admin_users.id = auth.uid()));

-- 3. categories -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  slug text PRIMARY KEY,
  label text NOT NULL DEFAULT '',
  show_on_site boolean NOT NULL DEFAULT true,
  sort_order integer NOT NULL DEFAULT 0,
  parent_slug text,
  meta_title text,
  meta_description text,
  curated_tags text[] DEFAULT '{}',
  updated_at timestamptz DEFAULT now()
);

ALTER TABLE categories ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "Public can read categories" ON categories;
CREATE POLICY "Public can read categories"
  ON categories FOR SELECT
  TO anon, authenticated
  USING (true);

DROP POLICY IF EXISTS "Admins can write categories" ON categories;
CREATE POLICY "Admins can write categories"
  ON categories FOR ALL
  TO authenticated
  USING (EXISTS (SELECT 1 FROM admin_users WHERE admin_users.id = auth.uid()))
  WITH CHECK (EXISTS (SELECT 1 FROM admin_users WHERE admin_users.id = auth.uid()));

-- 4. media_library ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS media_library (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  url text NOT NULL DEFAULT '',
  alt_text text DEFAULT '',
  title text DEFAULT '',
  file_name text DEFAULT '',
  created_at timestamptz DEFAULT now()
);

ALTER TABLE media_library ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "Public can read media" ON media_library;
CREATE POLICY "Public can read media"
  ON media_library FOR SELECT
  TO anon, authenticated
  USING (true);

DROP POLICY IF EXISTS "Admins can write media" ON media_library;
CREATE POLICY "Admins can write media"
  ON media_library FOR ALL
  TO authenticated
  USING (EXISTS (SELECT 1 FROM admin_users WHERE admin_users.id = auth.uid()))
  WITH CHECK (EXISTS (SELECT 1 FROM admin_users WHERE admin_users.id = auth.uid()));

-- 5. Ensure admin membership so admin-only write policies pass --------------
INSERT INTO admin_users (id, email)
SELECT id, email FROM auth.users WHERE email = 'admin@alt404.com'
ON CONFLICT (id) DO NOTHING;

-- 6. Seed default categories ------------------------------------------------
INSERT INTO categories (slug, label, show_on_site, sort_order, meta_title, meta_description, curated_tags) VALUES
  ('texnologiya', 'Texnologiya Xəbərləri', true, 10, 'Texnologiya xəbərləri - Smartfonlar, Süni intellekt, Startap | alt404', 'Azərbaycanda texnologiya xəbərləri, smartfon icmalları, süni intellekt və startap yenilikləri. alt404.az', '{}'),
  ('elm-gundem', 'Elm', true, 20, 'Elm xəbərləri | alt404', 'Elm, araşdırma və texnologiyanın elmi tərəfinə dair yeniliklər. alt404.az', '{}'),
  ('suni-intellekt', 'Süni İntellekt', true, 30, 'Süni intellekt xəbərləri | alt404', 'Süni intellekt alqoritmləri, modellər və tətbiqlər barədə xəbərlər. alt404.az', '{AI}'),
  ('startap', 'Startap', true, 40, 'Startap xəbərləri | alt404', 'Yeni startaplar, investisiyalar və məhsul yenilikləri. alt404.az', '{}'),
  ('texnobloq', 'Texnobloq', true, 50, 'Texnobloq | alt404', 'Texnologiya üzrə analiz və icmal məqalələri. alt404.az', '{}'),
  ('avtomobil', 'Avtomobil', true, 60, 'Avtomobil xəbərləri | alt404', 'Elektromobillər, avtomobil texnologiyası və sənaye yenilikləri. alt404.az', '{}'),
  ('texnoicmal', 'Texnoicmal', true, 65, 'Texnoicmal | alt404', 'Cihazlar və texnologiya üzrə icmallar. alt404.az', '{}'),
  ('meqaleler', 'Məqalələr', true, 70, 'Məqalələr | alt404', 'Texnologiya və elm mövzularında məqalələr. alt404.az', '{}'),
  ('cihazlar', 'Cihazlar', false, 5, 'Cihazlar | alt404', 'Smartfonlar və digər cihaz yenilikləri. alt404.az', '{}')
ON CONFLICT (slug) DO NOTHING;

-- 7. Seed default ad zones --------------------------------------------------
INSERT INTO ads (id, label, enabled, image_url, link_url, width, height) VALUES
  ('leaderboard', 'Leaderboard (üst banner)', true, 'https://images.pexels.com/photos/1779487/pexels-photo-1779487.jpeg?auto=compress&cs=tinysrgb&w=900&h=90&fit=crop', '#', 900, 90),
  ('sidebar-left', 'Sol Panel', true, 'https://images.pexels.com/photos/3861969/pexels-photo-3861969.jpeg?auto=compress&cs=tinysrgb&w=160&h=600&fit=crop', '#', 160, 600),
  ('sidebar-right', 'Sağ Panel', true, 'https://images.pexels.com/photos/2599244/pexels-photo-2599244.jpeg?auto=compress&cs=tinysrgb&w=160&h=600&fit=crop', '#', 160, 600),
  ('inline', 'Xəbər içi reklam', true, 'https://images.pexels.com/photos/7974/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=728&h=90&fit=crop', '#', 728, 90)
ON CONFLICT (id) DO NOTHING;
