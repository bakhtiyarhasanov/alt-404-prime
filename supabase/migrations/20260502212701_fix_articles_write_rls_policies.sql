/*
  # Fix articles write RLS policies

  ## Problem
  The INSERT, UPDATE, and DELETE policies on the `articles` table used `true` as their
  conditions, meaning any authenticated user could write to the table — not just admins.

  ## Changes
  - Drop the three overly-permissive write policies
  - Recreate them with a check that the authenticated user exists in `admin_users`,
    ensuring only registered admins can insert, update, or delete articles

  ## Security
  - INSERT: only users present in admin_users can add articles
  - UPDATE: only users present in admin_users can edit articles
  - DELETE: only users present in admin_users can delete articles
*/

-- Drop the always-true write policies
DROP POLICY IF EXISTS "Authenticated users can insert articles" ON articles;
DROP POLICY IF EXISTS "Authenticated users can update articles" ON articles;
DROP POLICY IF EXISTS "Authenticated users can delete articles" ON articles;

-- Recreate with admin-only constraint
CREATE POLICY "Admin users can insert articles"
  ON articles FOR INSERT
  TO authenticated
  WITH CHECK (
    EXISTS (
      SELECT 1 FROM admin_users WHERE admin_users.id = auth.uid()
    )
  );

CREATE POLICY "Admin users can update articles"
  ON articles FOR UPDATE
  TO authenticated
  USING (
    EXISTS (
      SELECT 1 FROM admin_users WHERE admin_users.id = auth.uid()
    )
  )
  WITH CHECK (
    EXISTS (
      SELECT 1 FROM admin_users WHERE admin_users.id = auth.uid()
    )
  );

CREATE POLICY "Admin users can delete articles"
  ON articles FOR DELETE
  TO authenticated
  USING (
    EXISTS (
      SELECT 1 FROM admin_users WHERE admin_users.id = auth.uid()
    )
  );
