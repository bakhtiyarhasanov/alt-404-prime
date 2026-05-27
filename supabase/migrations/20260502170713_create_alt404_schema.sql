/*
  # alt404 Media Platform Schema

  1. New Tables
    - `articles`
      - `id` (uuid, primary key)
      - `title` (text, not null)
      - `slug` (text, unique, not null)
      - `excerpt` (text)
      - `content` (text)
      - `category` (text) — texnologiya, elm-sosial, startap, avtomobil, meqaleler
      - `image_url` (text)
      - `tags` (text[]) — hashtag array
      - `featured` (boolean) — show on hero/featured section
      - `published` (boolean)
      - `reading_time` (int) — minutes
      - `created_at` (timestamptz)
      - `updated_at` (timestamptz)
    - `admin_users`
      - `id` (uuid, primary key, references auth.users)
      - `email` (text)
      - `created_at` (timestamptz)

  2. Security
    - Enable RLS on both tables
    - Public can read published articles
    - Only authenticated users can insert/update/delete articles
    - Admin users can only see their own row
*/

CREATE TABLE IF NOT EXISTS articles (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  title text NOT NULL,
  slug text UNIQUE NOT NULL,
  excerpt text DEFAULT '',
  content text DEFAULT '',
  category text NOT NULL DEFAULT 'texnologiya',
  image_url text DEFAULT '',
  tags text[] DEFAULT '{}',
  featured boolean DEFAULT false,
  published boolean DEFAULT true,
  reading_time int DEFAULT 3,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

CREATE TABLE IF NOT EXISTS admin_users (
  id uuid PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
  email text NOT NULL,
  created_at timestamptz DEFAULT now()
);

ALTER TABLE articles ENABLE ROW LEVEL SECURITY;
ALTER TABLE admin_users ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Anyone can read published articles"
  ON articles FOR SELECT
  USING (published = true);

CREATE POLICY "Authenticated users can insert articles"
  ON articles FOR INSERT
  TO authenticated
  WITH CHECK (true);

CREATE POLICY "Authenticated users can update articles"
  ON articles FOR UPDATE
  TO authenticated
  USING (true)
  WITH CHECK (true);

CREATE POLICY "Authenticated users can delete articles"
  ON articles FOR DELETE
  TO authenticated
  USING (true);

CREATE POLICY "Admin users can view own record"
  ON admin_users FOR SELECT
  TO authenticated
  USING (auth.uid() = id);

CREATE POLICY "Admin users can insert own record"
  ON admin_users FOR INSERT
  TO authenticated
  WITH CHECK (auth.uid() = id);

CREATE INDEX IF NOT EXISTS articles_category_idx ON articles (category);
CREATE INDEX IF NOT EXISTS articles_slug_idx ON articles (slug);
CREATE INDEX IF NOT EXISTS articles_created_at_idx ON articles (created_at DESC);
CREATE INDEX IF NOT EXISTS articles_featured_idx ON articles (featured) WHERE featured = true;
