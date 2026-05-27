import { createClient } from '@supabase/supabase-js';

const supabaseUrl = import.meta.env.VITE_SUPABASE_URL;
const supabaseAnonKey = import.meta.env.VITE_SUPABASE_ANON_KEY;

export const supabase = createClient(supabaseUrl, supabaseAnonKey);

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
