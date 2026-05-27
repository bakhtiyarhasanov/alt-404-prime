/*
  # Create admin auth user

  Creates a confirmed Supabase Auth user for admin panel access.
  Email: admin@alt404.com
  Password: Alt404Admin!
*/

DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM auth.users WHERE email = 'admin@alt404.com') THEN
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
      is_super_admin,
      confirmation_token,
      recovery_token,
      email_change_token_new,
      email_change
    )
    VALUES (
      gen_random_uuid(),
      '00000000-0000-0000-0000-000000000000',
      'admin@alt404.com',
      crypt('Alt404Admin!', gen_salt('bf')),
      now(),
      'authenticated',
      'authenticated',
      now(),
      now(),
      '{"provider":"email","providers":["email"]}',
      '{}',
      false,
      '',
      '',
      '',
      ''
    );
  ELSE
    UPDATE auth.users
    SET
      encrypted_password = crypt('Alt404Admin!', gen_salt('bf')),
      email_confirmed_at = now(),
      updated_at = now()
    WHERE email = 'admin@alt404.com';
  END IF;
END $$;
