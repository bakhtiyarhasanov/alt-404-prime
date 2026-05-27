/*
  # Add increment_article_views RPC function

  Creates a secure server-side function to atomically increment the view
  counter on an article. Called from the public view after each page load.

  - No authentication required (public can increment views)
  - Uses security definer so it can bypass RLS for this single update
*/

CREATE OR REPLACE FUNCTION increment_article_views(article_id uuid)
RETURNS void
LANGUAGE sql
SECURITY DEFINER
AS $$
  UPDATE articles SET views = views + 1 WHERE id = article_id;
$$;
