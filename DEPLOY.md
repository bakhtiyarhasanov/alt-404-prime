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

From the project root. This repo includes the CLI as a dev dependency — use **`npx`** or **`npm run`** (global `supabase` is not required):

```bash
cd /path/to/alt404-main
npm install
npm run supabase:login
npm run supabase:link
npm run supabase:push
```

(`supabase:link` is already set to project ref `ezabzqsmyllizyttgavv`.)

If you prefer a global CLI: install [Homebrew](https://brew.sh), then `brew install supabase/tap/supabase`.

Or apply SQL files manually in the Supabase SQL editor, in order under `supabase/migrations/`.

### Auth URLs

In Supabase → **Authentication** → **URL configuration**:

- **Site URL**: `https://your-domain.com` (or your `*.vercel.app` URL for testing)
- **Redirect URLs**: add the same URL(s), e.g. `https://your-domain.com/**`

### Environment variables (for the frontend)

| Variable | Where to find it |
|----------|------------------|
| `VITE_SUPABASE_URL` | Supabase → Settings → API → Project URL |
| `VITE_SUPABASE_PUBLISHABLE_KEY` | Supabase → Settings → API → Publishable key (`sb_publishable_…`) |

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
   - `VITE_SUPABASE_PUBLISHABLE_KEY`
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
   - `VITE_SUPABASE_PUBLISHABLE_KEY`
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

## 6. Content storage (shared via Supabase)

All admin-managed content is stored in **Supabase**, so every visitor (incognito,
other devices, other accounts) sees the same content.

| Feature | Storage |
|---------|---------|
| Articles | Supabase (`articles`) |
| Ads | Supabase (`ads`) |
| Categories / sections | Supabase (`categories`) |
| Media library | Supabase (`media_library`) |
| Admin auth | Supabase (`auth.users` + `admin_users`) |
| Contact form | Supabase (`contact_submissions`) |
| Home videos | Supabase (`home_videos`) |

The app reads content from Supabase on load and again when the tab regains focus.
Admin writes (create/edit/delete) go straight to Supabase.

### Migrating older localStorage content

If content was created before this change, it lives in that browser's
`localStorage` (`alt404_state`). To publish it for everyone:

1. Log in to `/admin` on the browser that has the content.
2. In the **Xəbərlər** tab click **"Serverə köçür"** (appears only when local
   content exists). This upserts your articles/ads/categories/media into Supabase.

If the `articles` table is empty, the site shows built-in demo articles as a
placeholder until the first real article is added.

---

## 7. Checklist before go-live

- [ ] `VITE_SUPABASE_URL` and `VITE_SUPABASE_PUBLISHABLE_KEY` set on host
- [ ] Supabase migrations applied
- [ ] Supabase Auth Site URL + Redirect URLs match live domain
- [ ] `/admin` login works on production URL
- [ ] Direct links work (e.g. `/texnologiya/some-slug`) — refresh page, no 404
- [ ] Contact form submits successfully
- [ ] Admin can create an article that shows up in incognito / other devices

---

## 8. Troubleshooting

### 404 on refresh (e.g. `/texnologiya/foo`)

- **Vercel**: ensure `vercel.json` is in the repo root.
- **Netlify**: ensure `public/_redirects` exists (it is included in this repo).

### Blank page after deploy

- Check build logs for errors.
- Confirm env vars are set and names are exactly `VITE_SUPABASE_URL` and `VITE_SUPABASE_PUBLISHABLE_KEY` (Vite only exposes `VITE_*` at build time). Legacy `VITE_SUPABASE_ANON_KEY` still works if set instead.
- Redeploy after adding or changing env vars.

### Admin login fails

- Verify Supabase Auth URLs include your production domain.
- Confirm user exists in Supabase Auth and email/password are correct.

### Env vars not applied

- Rebuild/redeploy after changing variables (they are baked in at build time for Vite).
