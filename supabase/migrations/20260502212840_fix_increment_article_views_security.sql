/*
  # Fix increment_article_views function security

  ## Problems
  1. Mutable search_path: the function did not pin its search_path, making it
     vulnerable to search_path hijacking attacks.
  2. Unrestricted EXECUTE: both anon and authenticated roles inherited EXECUTE
     via the default PUBLIC grant, exposing a SECURITY DEFINER function to all
     callers without restriction.

  ## Changes
  - Recreate the function with `SET search_path = public, pg_temp` to pin the
    search path and prevent schema-injection attacks.
  - Revoke EXECUTE from PUBLIC (covers both anon and authenticated).
  - Grant EXECUTE back to anon and authenticated explicitly, since view counting
    is intentionally public — any page visitor should be able to increment views.

  ## Notes
  - SECURITY DEFINER is kept because the function must bypass RLS to update the
    views counter (articles are only writable by admins under RLS).
  - Pinning the search_path is the required mitigation for mutable search_path
    findings on SECURITY DEFINER functions.
*/

-- Recreate function with a pinned search_path
CREATE OR REPLACE FUNCTION public.increment_article_views(article_id uuid)
RETURNS void
LANGUAGE sql
SECURITY DEFINER
SET search_path = public, pg_temp
AS $$
  UPDATE articles SET views = views + 1 WHERE id = article_id;
$$;

-- Revoke the default PUBLIC grant (removes access from every role by default)
REVOKE EXECUTE ON FUNCTION public.increment_article_views(uuid) FROM PUBLIC;

-- Re-grant only to the roles that legitimately need it
GRANT EXECUTE ON FUNCTION public.increment_article_views(uuid) TO anon;
GRANT EXECUTE ON FUNCTION public.increment_article_views(uuid) TO authenticated;
