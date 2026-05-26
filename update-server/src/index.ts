/**
 * CF7 Mate update server.
 *
 * Responds to two routes:
 *   GET /info.json?slug=<plugin-slug>   – release manifest for the plugin
 *   GET /download/<plugin-slug>/<file>  – streams the matching zip from R2
 *
 * The plugin's License_Manager.is_valid() gate is the trust boundary: only
 * licensed sites reach this endpoint at all, so we don't re-validate the
 * license here. Keep the surface area small.
 *
 * To publish a release, write a manifest to KV and the zip to R2:
 *   wrangler kv key put --binding RELEASES cf7-mate-pro '{"version":"1.0.1","zip_key":"cf7-mate-pro/cf7-mate-pro-1.0.1.zip","tested":"6.9","requires":"6.0","requires_php":"7.4","description":"Pro features for CF7 Mate.","changelog":"= 1.0.1 =\\n* Fixed: ..."}'
 *   wrangler r2 object put cf7mate-plugin-zips/cf7-mate-pro/cf7-mate-pro-1.0.1.zip --file=cf7-mate-pro-1.0.1.zip
 */

export interface Env {
	PLUGIN_ZIPS: R2Bucket;
	RELEASES: KVNamespace;
	ALLOWED_SLUGS: string;
}

interface ReleaseManifest {
	version: string;
	zip_key: string;
	tested?: string;
	requires?: string;
	requires_php?: string;
	description?: string;
	changelog?: string;
}

const DEFAULT_SLUG = 'cf7-mate-pro';
const SLUG_RE = /^[a-z0-9][a-z0-9-]{1,63}$/;

export default {
	async fetch(req: Request, env: Env, ctx: ExecutionContext): Promise<Response> {
		const url = new URL(req.url);

		if (req.method !== 'GET' && req.method !== 'HEAD') {
			return new Response('Method Not Allowed', { status: 405, headers: { Allow: 'GET, HEAD' } });
		}

		if (url.pathname === '/info.json') {
			return handleInfo(url, env);
		}

		if (url.pathname.startsWith('/download/')) {
			return handleDownload(url, env, req);
		}

		if (url.pathname === '/' || url.pathname === '/health') {
			return Response.json({ ok: true, name: 'cf7mate-updates' });
		}

		return new Response('Not Found', { status: 404 });
	},
};

function allowedSlugs(env: Env): Set<string> {
	return new Set(
		env.ALLOWED_SLUGS.split(',')
			.map((s) => s.trim())
			.filter(Boolean)
	);
}

function pickSlug(url: URL, env: Env): string | null {
	const requested = url.searchParams.get('slug') || DEFAULT_SLUG;
	if (!SLUG_RE.test(requested)) return null;
	if (!allowedSlugs(env).has(requested)) return null;
	return requested;
}

async function handleInfo(url: URL, env: Env): Promise<Response> {
	const slug = pickSlug(url, env);
	if (!slug) {
		return json({ error: 'invalid_slug' }, 400);
	}

	const manifest = await env.RELEASES.get<ReleaseManifest>(slug, 'json');
	if (!manifest) {
		return json({ error: 'no_release', slug }, 404);
	}
	if (!manifest.version || !manifest.zip_key) {
		return json({ error: 'invalid_manifest', slug }, 500);
	}

	const downloadUrl = `${url.origin}/download/${slug}/${encodeURIComponent(basename(manifest.zip_key))}`;

	return json({
		version: manifest.version,
		download_url: downloadUrl,
		tested: manifest.tested ?? '',
		requires: manifest.requires ?? '6.0',
		requires_php: manifest.requires_php ?? '7.4',
		description: manifest.description ?? '',
		changelog: manifest.changelog ?? '',
	});
}

async function handleDownload(url: URL, env: Env, req: Request): Promise<Response> {
	// /download/<slug>/<filename>
	const parts = url.pathname.split('/').filter(Boolean);
	if (parts.length !== 3) {
		return new Response('Not Found', { status: 404 });
	}
	const [, slug, filename] = parts;

	if (!SLUG_RE.test(slug) || !allowedSlugs(env).has(slug)) {
		return new Response('Not Found', { status: 404 });
	}
	if (filename.includes('..') || filename.includes('/') || filename.includes('\\')) {
		return new Response('Bad Request', { status: 400 });
	}

	// The manifest holds the canonical zip_key; verify the requested filename
	// matches the current release so old / arbitrary R2 keys aren't readable.
	const manifest = await env.RELEASES.get<ReleaseManifest>(slug, 'json');
	if (!manifest || !manifest.zip_key) {
		return new Response('Not Found', { status: 404 });
	}
	if (basename(manifest.zip_key) !== filename) {
		return new Response('Not Found', { status: 404 });
	}

	const obj = await env.PLUGIN_ZIPS.get(manifest.zip_key, {
		onlyIf: req.headers,
		range: req.headers,
	});
	if (!obj) {
		return new Response('Not Found', { status: 404 });
	}

	const headers = new Headers();
	obj.writeHttpMetadata(headers);
	headers.set('etag', obj.httpEtag);
	headers.set('cache-control', 'public, max-age=3600');
	headers.set('content-disposition', `attachment; filename="${filename}"`);
	headers.set('content-type', 'application/zip');

	// HEAD: no body.
	if (req.method === 'HEAD') {
		return new Response(null, { status: 200, headers });
	}

	return new Response(obj.body, { status: 200, headers });
}

function json(data: unknown, status = 200): Response {
	return new Response(JSON.stringify(data), {
		status,
		headers: { 'content-type': 'application/json' },
	});
}

function basename(p: string): string {
	const i = p.lastIndexOf('/');
	return i < 0 ? p : p.slice(i + 1);
}
