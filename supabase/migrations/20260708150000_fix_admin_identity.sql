/*
  # Fix admin auth user login (add auth.identities row)

  Newer Supabase GoTrue requires a matching row in `auth.identities` for
  email/password sign-in. The original create-admin migration only inserted
  into `auth.users`, so signInWithPassword returned "invalid credentials".

  This migration is idempotent: it ensures the admin user exists with a
  confirmed email + known password, and ensures the email identity exists.

  Email: admin@alt404.com
  Password: Alt404Admin!
*/

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA extensions;

DO $$
DECLARE
  v_user_id uuid;
BEGIN
  -- Ensure the user exists (confirmed, with password).
  SELECT id INTO v_user_id FROM auth.users WHERE email = 'admin@alt404.com';

  IF v_user_id IS NULL THEN
    v_user_id := gen_random_uuid();
    INSERT INTO auth.users (
      id,
      instance_id,
      email,
      encrypted_password,
      email_confirmed_at,
      role,
      aud,
      created_at,
      updated_at,
      raw_app_meta_data,
      raw_user_meta_data,
      is_super_admin
    )
    VALUES (
      v_user_id,
      '00000000-0000-0000-0000-000000000000',
      'admin@alt404.com',
      extensions.crypt('Alt404Admin!', extensions.gen_salt('bf')),
      now(),
      'authenticated',
      'authenticated',
      now(),
      now(),
      '{"provider":"email","providers":["email"]}',
      '{}',
      false
    );
  ELSE
    UPDATE auth.users
    SET
      encrypted_password = extensions.crypt('Alt404Admin!', extensions.gen_salt('bf')),
      email_confirmed_at = COALESCE(email_confirmed_at, now()),
      updated_at = now(),
      raw_app_meta_data = '{"provider":"email","providers":["email"]}'
    WHERE id = v_user_id;
  END IF;

  -- Ensure the email identity exists (required for email/password login).
  IF NOT EXISTS (
    SELECT 1 FROM auth.identities
    WHERE user_id = v_user_id AND provider = 'email'
  ) THEN
    INSERT INTO auth.identities (
      provider_id,
      user_id,
      identity_data,
      provider,
      last_sign_in_at,
      created_at,
      updated_at
    )
    VALUES (
      v_user_id::text,
      v_user_id,
      jsonb_build_object(
        'sub', v_user_id::text,
        'email', 'admin@alt404.com',
        'email_verified', true,
        'phone_verified', false
      ),
      'email',
      now(),
      now(),
      now()
    );
  END IF;
END $$;
