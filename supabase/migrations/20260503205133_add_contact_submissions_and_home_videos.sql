/*
  # Add contact_submissions and home_videos tables

  ## New Tables

  ### contact_submissions
  Stores user-submitted contact form messages for admin review.
  - `id` (uuid, primary key)
  - `created_at` (timestamptz)
  - `ad_soyad` (text) - submitter full name
  - `email` (text) - submitter email
  - `mesaj` (text) - message body
  - `status` (text) - 'new' or 'reviewed'

  ### home_videos
  Stores video entries for the Home page video gallery section.
  - `id` (uuid, primary key)
  - `created_at` (timestamptz)
  - `title` (text) - video display title
  - `youtube_url` (text) - full YouTube URL
  - `thumbnail_url` (text) - thumbnail image URL
  - `sort_order` (integer) - display order

  ## Security
  - RLS enabled on both tables
  - contact_submissions: public INSERT (anyone can submit a form), authenticated SELECT/UPDATE for admin
  - home_videos: public SELECT (visible on homepage), authenticated INSERT/UPDATE/DELETE for admin
*/

CREATE TABLE IF NOT EXISTS contact_submissions (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  created_at timestamptz DEFAULT now(),
  ad_soyad text NOT NULL DEFAULT '',
  email text NOT NULL DEFAULT '',
  mesaj text NOT NULL DEFAULT '',
  status text NOT NULL DEFAULT 'new'
);

ALTER TABLE contact_submissions ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Anyone can submit a contact form"
  ON contact_submissions
  FOR INSERT
  TO anon, authenticated
  WITH CHECK (true);

CREATE POLICY "Authenticated admins can view submissions"
  ON contact_submissions
  FOR SELECT
  TO authenticated
  USING (true);

CREATE POLICY "Authenticated admins can update submission status"
  ON contact_submissions
  FOR UPDATE
  TO authenticated
  USING (true)
  WITH CHECK (true);

CREATE TABLE IF NOT EXISTS home_videos (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  created_at timestamptz DEFAULT now(),
  title text NOT NULL DEFAULT '',
  youtube_url text NOT NULL DEFAULT '',
  thumbnail_url text NOT NULL DEFAULT '',
  sort_order integer NOT NULL DEFAULT 0
);

ALTER TABLE home_videos ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Anyone can view home videos"
  ON home_videos
  FOR SELECT
  TO anon, authenticated
  USING (true);

CREATE POLICY "Authenticated admins can insert home videos"
  ON home_videos
  FOR INSERT
  TO authenticated
  WITH CHECK (true);

CREATE POLICY "Authenticated admins can update home videos"
  ON home_videos
  FOR UPDATE
  TO authenticated
  USING (true)
  WITH CHECK (true);

CREATE POLICY "Authenticated admins can delete home videos"
  ON home_videos
  FOR DELETE
  TO authenticated
  USING (true);
