# CF7 Mate Update Server

Cloudflare Worker + R2 + KV that serves plugin update metadata and zip
downloads to the CF7 Mate Pro auto-updater.

## Architecture

```
WP plugin (Updater::fetch_update_info)
       │
       │  GET /info.json?slug=cf7-mate-pro
       │
       ▼
Cloudflare Worker  ──► KV (RELEASES)   ◄── version manifest per slug
       │           ──► R2 (PLUGIN_ZIPS) ◄── plugin zip files
       │
       │  { version, download_url, tested, ... }
       ▼
WP plugin

Then WP downloads:
       GET /download/cf7-mate-pro/cf7-mate-pro-1.0.1.zip
```

The plugin's `License_Manager::is_valid()` check is the trust gate. This Worker
does not re-validate licenses — keeping the surface minimal.

## One-time setup

```bash
cd update-server
npm install
npx wrangler login

# Create the R2 bucket
npx wrangler r2 bucket create cf7mate-plugin-zips

# Create the KV namespace, copy the `id` it prints into wrangler.jsonc
npx wrangler kv namespace create RELEASES
```

Open `wrangler.jsonc` and replace `REPLACE_AFTER_FIRST_CREATE` with the KV
namespace `id` from the previous command.

```bash
# Deploy
npx wrangler deploy
```

After the first deploy, in the Cloudflare dashboard, attach the custom domain
`updates.cf7mate.com` to this Worker (Workers & Pages → cf7mate-updates →
Settings → Triggers → Custom Domains → Add Custom Domain). DNS is auto-managed
if cf7mate.com is on Cloudflare.

## Publishing a new release

```bash
# 1. Upload the zip to R2
npx wrangler r2 object put cf7mate-plugin-zips/cf7-mate-pro/cf7-mate-pro-1.0.1.zip \
  --file=../cf7-mate-pro-1.0.1.zip

# 2. Update the manifest in KV
npx wrangler kv key put --binding RELEASES cf7-mate-pro '{
  "version": "1.0.1",
  "zip_key": "cf7-mate-pro/cf7-mate-pro-1.0.1.zip",
  "tested": "6.9",
  "requires": "6.0",
  "requires_php": "7.4",
  "description": "Pro features for CF7 Mate.",
  "changelog": "= 1.0.1 =\n* Fixed: ..."
}'
```

The plugin polls `/info.json` every 12 hours (cached in a WP transient), so
updates show up to admins on the next poll. To force a poll, visit
`Dashboard → Updates` in WP admin or call:

```bash
wp transient delete cf7m_update_info
```

## Local development

```bash
npx wrangler dev
# Then point a test plugin's UPDATE_SERVER constant at
# http://127.0.0.1:8787/info.json
```

## Endpoints

### `GET /info.json?slug=<plugin-slug>`

Returns the latest manifest:

```json
{
  "version": "1.0.1",
  "download_url": "https://updates.cf7mate.com/download/cf7-mate-pro/cf7-mate-pro-1.0.1.zip",
  "tested": "6.9",
  "requires": "6.0",
  "requires_php": "7.4",
  "description": "Pro features for CF7 Mate.",
  "changelog": "..."
}
```

Returns `404` if no release manifest exists for the slug, `400` for an unknown
slug.

### `GET /download/<slug>/<filename>`

Streams the zip from R2. Only the filename matching the current manifest's
`zip_key` is served — old or arbitrary R2 keys return 404. Supports range
requests and `If-None-Match` for conditional GETs.

### `GET /health`

Liveness check.

## Adding more plugins

Add the new slug to `wrangler.jsonc` → `vars.ALLOWED_SLUGS` (comma-separated),
then publish manifest + zip exactly like the steps above with the new slug.
