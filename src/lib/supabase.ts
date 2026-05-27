import { createClient, type SupabaseClient } from '@supabase/supabase-js';

const supabaseUrl = import.meta.env.VITE_SUPABASE_URL;
const supabaseKey =
  import.meta.env.VITE_SUPABASE_PUBLISHABLE_KEY ||
  import.meta.env.VITE_SUPABASE_ANON_KEY;

/** True when Vite baked URL + key at build time (required on Vercel/Netlify). */
export const isSupabaseConfigured = Boolean(supabaseUrl && supabaseKey);

// Missing env vars make createClient throw on import → blank white screen for the whole app.
const PLACEHOLDER_URL = 'https://placeholder.supabase.co';
const PLACEHOLDER_KEY =
  'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InBsYWNlaG9sZGVyIiwicm9sZSI6ImFub24iLCJpYXQiOjE2NDUxOTI4MDAsImV4cCI6MTk2MDc2ODgwMH0.placeholder';

if (!isSupabaseConfigured && import.meta.env.PROD) {
  console.warn(
    '[alt404] Supabase env missing at build time. Set VITE_SUPABASE_URL and VITE_SUPABASE_PUBLISHABLE_KEY on your host, then redeploy.',
  );
}

export const supabase: SupabaseClient = createClient(
  supabaseUrl || PLACEHOLDER_URL,
  supabaseKey || PLACEHOLDER_KEY,
);

export type ArticleVersion = {
  savedAt: string;
  title: string;
  excerpt: string;
  content: string;
};

export type Article = {
  id: string;
  title: string;
  slug: string;
  excerpt: string;
  content: string;
  category: string;
  image_url: string;
  tags: string[];
  featured: boolean;
  published: boolean;
  reading_time: number;
  views: number;
  created_at: string;
  updated_at: string;
  versions?: ArticleVersion[];
};

export const CATEGORIES = [
  { slug: 'texnologiya', label: 'Texnologiya' },
  { slug: 'cihazlar', label: 'Cihazlar' },
  { slug: 'elm-gundem', label: 'Elm/Gündəm' },
  { slug: 'startap', label: 'Startap' },
  { slug: 'avtomobil', label: 'Avtomobil' },
  { slug: 'meqaleler', label: 'Məqalələr' },
];

export const CATEGORY_LABELS: Record<string, string> = {
  texnologiya: 'Texnologiya',
  cihazlar: 'Cihazlar',
  'elm-gundem': 'Elm/Gündəm',
  'elm-sosial': 'Elm/Gündəm',
  startap: 'Startap',
  avtomobil: 'Avtomobil',
  meqaleler: 'Məqalələr',
};
