/*
  # Fix RLS policies and add views column

  1. Changes
    - Drop the overly-restrictive SELECT policy that only exposes published articles to everyone
    - Add two SELECT policies:
      * Public (anon) can read published articles only
      * Authenticated users (admin) can read ALL articles including drafts
    - Add `views` column (integer, default 0) for view-count tracking

  2. Security
    - Public readers only see published = true rows
    - Authenticated admins can read all rows (needed for dashboard draft listing)
    - INSERT / UPDATE / DELETE remain restricted to authenticated only
*/

-- Drop the single catch-all SELECT policy
DROP POLICY IF EXISTS "Anyone can read published articles" ON articles;

-- Public: only published articles
CREATE POLICY "Public can read published articles"
  ON articles FOR SELECT
  TO anon
  USING (published = true);

-- Authenticated admins: read everything (drafts + published)
CREATE POLICY "Authenticated users can read all articles"
  ON articles FOR SELECT
  TO authenticated
  USING (true);

-- Add views column if it doesn't exist yet
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_name = 'articles' AND column_name = 'views'
  ) THEN
    ALTER TABLE articles ADD COLUMN views integer DEFAULT 0;
  END IF;
END $$;
