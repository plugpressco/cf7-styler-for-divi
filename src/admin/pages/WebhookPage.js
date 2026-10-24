/**
 * Webhook destinations.
 *
 * @package CF7_Mate
 */

import { useState, useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { LinkIcon } from '@heroicons/react/24/outline';
import apiFetch from '@wordpress/api-fetch';
import { Button, Card, CardContent, CardFooter, CardHeader, CardTitle, Checkbox, IconChip, InlineStatus, Input, LoadingState } from '../components/ui';
import { FieldRow, fieldGrid } from './AISettingsPage';

const EXAMPLE_PAYLOAD = {
	form_id: 1,
	form_title: 'Contact Us',
	submitted_at: '2026-05-05T10:30:45Z',
	posted_data: { name: 'John Doe', email: 'john@example.com', message: 'Hello!' },
	ip: '192.168.1.1',
};

export function WebhookPage({ features, onToggle, saving: parentSaving }) {
	const webhookEnabled = !!(features && features.webhook);

	const [urls, setUrls] = useState([]);
	const [savedUrls, setSavedUrls] = useState([]);
	const [newUrl, setNewUrl] = useState('');
	const [loading, setLoading] = useState(true);
	const [saving, setSaving] = useState(false);
	const [status, setStatus] = useState(null);
	const [testingIndex, setTestingIndex] = useState(null);
	const [showPayload, setShowPayload] = useState(false);

	useEffect(() => {
		(async () => {
			try {
				const data = await apiFetch({ path: '/cf7-styler/v1/settings/webhook' });
				const list = Array.isArray(data.urls) ? data.urls : [];
				setUrls(list);
				setSavedUrls(list);
			} catch (err) {
				setStatus({ type: 'error', message: err.message || __('Could not load webhooks.', 'cf7-styler-for-divi') });
			} finally {
				setLoading(false);
			}
		})();
	}, []);

	const show = (type, message) => {
		setStatus({ type, message });
		if (type === 'success') setTimeout(() => setStatus((current) => (current && current.message === message ? null : current)), 3000);
	};

	const addUrl = () => {
		const trimmed = newUrl.trim();
		if (!trimmed) return;
		try { new URL(trimmed); } catch (error) {
			show('error', __('Enter a valid URL.', 'cf7-styler-for-divi'));
			return;
		}
		if (urls.includes(trimmed)) {
			show('error', __('This URL is already added.', 'cf7-styler-for-divi'));
			return;
		}
		setUrls([...urls, trimmed]);
		setNewUrl('');
		setStatus(null);
	};

	const removeUrl = (i) => setUrls(urls.filter((_, idx) => idx !== i));

	const dirty = JSON.stringify(urls) !== JSON.stringify(savedUrls);

	const save = async () => {
		setSaving(true);
		setStatus({ type: 'success', message: __('Saving…', 'cf7-styler-for-divi') });
		try {
			await apiFetch({ path: '/cf7-styler/v1/settings/webhook', method: 'POST', data: { urls } });
			setSavedUrls(urls);
			show('success', __('Saved.', 'cf7-styler-for-divi'));
		} catch (err) {
			show('error', err.message || __('Could not save.', 'cf7-styler-for-divi'));
		} finally {
			setSaving(false);
		}
	};

	const testWebhook = async (index) => {
		setTestingIndex(index);
		try {
			const res = await fetch(urls[index], {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ test: true, ...EXAMPLE_PAYLOAD, submitted_at: new Date().toISOString() }),
			});
			show(
				res.ok ? 'success' : 'error',
				/* translators: %d: HTTP status code. */
				res.ok ? __('Test sent.', 'cf7-styler-for-divi') : sprintf(__('Test failed (status %d).', 'cf7-styler-for-divi'), res.status)
			);
		} catch (err) {
			show('error', __('Test failed. The URL could not be reached from this browser.', 'cf7-styler-for-divi'));
		} finally {
			setTestingIndex(null);
		}
	};

	return (
		<Card aria-labelledby="cf7m-webhook-title">
			<CardHeader>
				<div className="flex items-center gap-2.5">
					<IconChip tone="sand" size="sm"><LinkIcon /></IconChip>
					<CardTitle id="cf7m-webhook-title">{__('Webhooks', 'cf7-styler-for-divi')}</CardTitle>
				</div>
				<Checkbox
					label={<span id="cf7m-webhook-enabled">{__('Enabled', 'cf7-styler-for-divi')}</span>}
					aria-labelledby="cf7m-webhook-title cf7m-webhook-enabled"
					checked={webhookEnabled}
					disabled={!!parentSaving}
					onChange={(value) => onToggle && onToggle('webhook', value)}
				/>
			</CardHeader>

			{!webhookEnabled && (
				<CardContent className="text-[13px] text-muted-foreground">
					{__('Turn this on to send each new submission as JSON to a URL, like Zapier or Make.', 'cf7-styler-for-divi')}
				</CardContent>
			)}

			{webhookEnabled && (
				<>
					<CardContent>
						{loading ? (
							<LoadingState rows={2} />
						) : (
							<div className={fieldGrid}>
								<FieldRow label={__('Webhook URL', 'cf7-styler-for-divi')} htmlFor="cf7m-webhook-url">
									<div className="flex max-w-[520px] gap-2">
										<Input
											id="cf7m-webhook-url"
											type="url"
											value={newUrl}
											onChange={(event) => setNewUrl(event.target.value)}
											onKeyDown={(event) => { if (event.key === 'Enter') { event.preventDefault(); addUrl(); } }}
											placeholder="https://hooks.zapier.com/hooks/catch/..."
										/>
										<Button onClick={addUrl}>{__('Add', 'cf7-styler-for-divi')}</Button>
									</div>
								</FieldRow>
							</div>
						)}
					</CardContent>

					{!loading && urls.length === 0 && (
						<p className="border-t border-divider px-[18px] py-3 text-[13px] text-muted-foreground">{__('No URLs yet.', 'cf7-styler-for-divi')}</p>
					)}
					{!loading && urls.map((url, index) => (
						<div key={url} className="flex items-center gap-4 border-t border-divider px-[18px] py-2.5">
							<code className="min-w-0 flex-1 truncate font-mono text-[12.5px]" title={url}>{url}</code>
							<Button variant="link" onClick={() => testWebhook(index)} disabled={testingIndex === index} aria-busy={testingIndex === index}>
								{__('Test', 'cf7-styler-for-divi')}
							</Button>
							<Button variant="danger" onClick={() => removeUrl(index)}>{__('Remove', 'cf7-styler-for-divi')}</Button>
						</div>
					))}

					{showPayload && (
						<pre className="mx-[18px] mb-3 overflow-auto rounded-tile border border-border bg-hover p-4 font-mono text-xs leading-relaxed text-muted-foreground">
							{JSON.stringify(EXAMPLE_PAYLOAD, null, 2)}
						</pre>
					)}

					<CardFooter>
						<Button variant="primary" onClick={save} disabled={saving || !dirty} aria-busy={saving}>{__('Save', 'cf7-styler-for-divi')}</Button>
						<Button variant="link" onClick={() => setShowPayload(!showPayload)} aria-expanded={showPayload}>{__('Example payload', 'cf7-styler-for-divi')}</Button>
						<InlineStatus status={status} />
					</CardFooter>
				</>
			)}
		</Card>
	);
}
