# alt404 — Deploy Guide

This project is a **Vite + React** static SPA with **Supabase** for auth and some backend features.

## Recommended stack

| Layer | Service |
|-------|---------|
| Frontend | [Vercel](https://vercel.com) or [Netlify](https://netlify.com) |
| Database / Auth | [Supabase](https://supabase.com) (already in use) |
| Custom domain | Point DNS to Vercel or Netlify |

---

## 1. Supabase setup

### Create project

1. Go to [supabase.com/dashboard](https://supabase.com/dashboard) and create a project.
2. Note your **Project URL** and **anon public** key (Settings → API).

### Run migrations

From the project root (with [Supabase CLI](https://supabase.com/docs/guides/cli) installed):

```bash
supabase link --project-ref YOUR_PROJECT_REF
supabase db push
```

Or apply SQL files manually in the Supabase SQL editor, in order under `supabase/migrations/`.

### Auth URLs

In Supabase → **Authentication** → **URL configuration**:

- **Site URL**: `https://your-domain.com` (or your `*.vercel.app` URL for testing)
- **Redirect URLs**: add the same URL(s), e.g. `https://your-domain.com/**`

### Environment variables (for the frontend)

| Variable | Where to find it |
|----------|------------------|
| `VITE_SUPABASE_URL` | Supabase → Settings → API → Project URL |
| `VITE_SUPABASE_ANON_KEY` | Supabase → Settings → API → `anon` `public` key |

Never commit `.env` to git. Use Vercel/Netlify environment variables instead.

---

## 2. Deploy on Vercel (recommended)

### Via GitHub

1. Push this repo to GitHub.
2. [vercel.com/new](https://vercel.com/new) → Import the repository.
3. Vercel should detect **Vite** automatically:
   - **Build Command**: `npm run build`
   - **Output Directory**: `dist`
   - **Install Command**: `npm install`
4. **Environment Variables** → add:
   - `VITE_SUPABASE_URL`
   - `VITE_SUPABASE_ANON_KEY`
5. Deploy.

`vercel.json` in the repo already configures SPA routing (all paths → `index.html`).

### Custom domain

Vercel → Project → **Settings** → **Domains** → add `alt404.az` (or your domain) and follow DNS instructions.

Update Supabase auth URLs to match the live domain.

---

## 3. Deploy on Netlify (alternative)

1. [app.netlify.com](https://app.netlify.com) → **Add new site** → Import from Git.
2. Build settings:
   - **Build command**: `npm run build`
   - **Publish directory**: `dist`
3. **Site configuration** → **Environment variables**:
   - `VITE_SUPABASE_URL`
   - `VITE_SUPABASE_ANON_KEY`
4. Deploy.

`public/_redirects` is copied into `dist` on build and enables client-side routing on Netlify.

---

## 4. Local production preview

```bash
npm install
npm run build
npm run preview
```

Open the URL shown (usually `http://localhost:4173`) and test navigation, admin login, and contact form.

---

## 5. Admin access

- Admin panel: `https://your-domain.com/admin`
- Login uses Supabase Auth (see migration `20260503212119_create_admin_auth_user.sql` for demo user setup, or create a user in Supabase → Authentication → Users).

---

## 6. Important: content storage today

Currently, **articles, ads, and categories** are stored in the browser **`localStorage`** (`alt404_state`), not in Supabase for the public site.

| Feature | Storage |
|---------|---------|
| Articles, ads, categories (public feed) | `localStorage` per browser |
| Admin auth | Supabase |
| Contact form | Supabase (`contact_submissions`) |
| Home videos | Supabase (`home_videos`) |

**After deploy:**

- Visitors each see default/mock content unless that browser has local data.
- Admin edits on one device do **not** sync to all users.

For a production news site, migrate articles (and related config) to Supabase so all users see the same content. Migrations under `supabase/migrations/` already include article schema.

---

## 7. Checklist before go-live

- [ ] `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY` set on host
- [ ] Supabase migrations applied
- [ ] Supabase Auth Site URL + Redirect URLs match live domain
- [ ] `/admin` login works on production URL
- [ ] Direct links work (e.g. `/texnologiya/some-slug`) — refresh page, no 404
- [ ] Contact form submits successfully
- [ ] Plan article migration off `localStorage` if you need shared content

---

## 8. Troubleshooting

### 404 on refresh (e.g. `/texnologiya/foo`)

- **Vercel**: ensure `vercel.json` is in the repo root.
- **Netlify**: ensure `public/_redirects` exists (it is included in this repo).

### Blank page after deploy

- Check build logs for errors.
- Confirm env vars are set and names are exactly `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY` (Vite only exposes `VITE_*` at build time).
- Redeploy after adding or changing env vars.

### Admin login fails

- Verify Supabase Auth URLs include your production domain.
- Confirm user exists in Supabase Auth and email/password are correct.

### Env vars not applied

- Rebuild/redeploy after changing variables (they are baked in at build time for Vite).
