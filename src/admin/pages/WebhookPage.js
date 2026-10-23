/**
 * Webhook – flat tool section.
 *
 * @package CF7_Mate
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, TextControl } from '@wordpress/components';
import { TrashIcon } from '@heroicons/react/24/outline';
import apiFetch from '@wordpress/api-fetch';
import { Toggle } from '../components/Toggle';

const EXAMPLE_PAYLOAD = {
	form_id: 1,
	form_title: 'Contact Us',
	submitted_at: '2026-05-05T10:30:45Z',
	posted_data: { name: 'John Doe', email: 'john@example.com', message: 'Hello!' },
	ip: '192.168.1.1',
};

export function WebhookPage({ features, onToggle, saving: parentSaving }) {
	const webhookEnabled = !!(features && features.webhook);

	const [urls, setUrls]           = useState([]);
	const [savedUrls, setSavedUrls] = useState([]);
	const [newUrl, setNewUrl]       = useState('');
	const [loading, setLoading]     = useState(true);
	const [saving, setSaving]       = useState(false);
	const [notice, setNotice]       = useState(null);
	const [testingIndex, setTestingIndex] = useState(null);
	const [showPayload, setShowPayload]   = useState(false);

	useEffect(() => {
		(async () => {
			try {
				const data = await apiFetch({ path: '/cf7-styler/v1/settings/webhook' });
				const list = Array.isArray(data.urls) ? data.urls : [];
				setUrls(list);
				setSavedUrls(list);
			} catch (err) {
				setNotice({ type: 'error', message: err.message || __('Failed to load.', 'cf7-styler-for-divi') });
			} finally {
				setLoading(false);
			}
		})();
	}, []);

	const showNotice = (type, message) => {
		setNotice({ type, message });
		if (type === 'success') setTimeout(() => setNotice(null), 3000);
	};

	const addUrl = () => {
		const trimmed = newUrl.trim();
		if (!trimmed) return;
		try { new URL(trimmed); } catch {
			showNotice('error', __('Enter a valid URL.', 'cf7-styler-for-divi'));
			return;
		}
		if (urls.includes(trimmed)) {
			showNotice('error', __('URL already in list.', 'cf7-styler-for-divi'));
			return;
		}
		setUrls([...urls, trimmed]);
		setNewUrl('');
	};

	const removeUrl = (i) => setUrls(urls.filter((_, idx) => idx !== i));

	const dirty = JSON.stringify(urls) !== JSON.stringify(savedUrls);

	const save = async () => {
		setSaving(true);
		setNotice(null);
		try {
			await apiFetch({ path: '/cf7-styler/v1/settings/webhook', method: 'POST', data: { urls } });
			setSavedUrls(urls);
			showNotice('success', __('Saved.', 'cf7-styler-for-divi'));
		} catch (err) {
			showNotice('error', err.message || __('Save failed.', 'cf7-styler-for-divi'));
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
			showNotice(
				res.ok ? 'success' : 'error',
				res.ok
					? __('Test delivered.', 'cf7-styler-for-divi')
					: `${__('Server returned', 'cf7-styler-for-divi')} ${res.status}`
			);
		} catch (err) {
			showNotice('error', err.message || __('Network error.', 'cf7-styler-for-divi'));
		} finally {
			setTestingIndex(null);
		}
	};

	return (
		<div className="cf7m-tool-section">

			{/* Section header */}
			<div className="cf7m-tool-section__head">
				<div className="cf7m-tool-section__meta">
					<span className="cf7m-tool-section__name">{__('Webhook', 'cf7-styler-for-divi')}</span>
					<span className="cf7m-tool-section__desc">
						{__('POST submissions as JSON to Zapier, Make, Slack, or any endpoint.', 'cf7-styler-for-divi')}
					</span>
				</div>
				<Toggle
					label={__('Webhooks', 'cf7-styler-for-divi')}
					checked={webhookEnabled}
					onChange={(v) => onToggle && onToggle('webhook', v)}
					disabled={!!parentSaving}
				/>
			</div>

			{!webhookEnabled && <p className="cf7m-tool-section__locked">{__('Enable webhooks to add a destination for new submissions.', 'cf7-styler-for-divi')}</p>}
			{webhookEnabled && (
				<div className="cf7m-tool-section__body">

					{notice && (
						<div className={`cf7m-tool-notice cf7m-tool-notice--${notice.type}`} role={notice.type === 'error' ? 'alert' : 'status'}>
							{notice.message}
							<button
								type="button"
								onClick={() => setNotice(null)}
								aria-label={__('Dismiss', 'cf7-styler-for-divi')}
							>×</button>
						</div>
					)}

					{loading ? (
						<p className="cf7m-tool-empty">{__('Loading…', 'cf7-styler-for-divi')}</p>
					) : (
						<>
							<div className="cf7m-tool-add">
								<TextControl
									__nextHasNoMarginBottom
									value={newUrl}
									onChange={setNewUrl}
									onKeyDown={(e) => { if (e.key === 'Enter') { e.preventDefault(); addUrl(); } }}
									placeholder="https://hooks.zapier.com/hooks/catch/…"
									aria-label={__('Webhook URL', 'cf7-styler-for-divi')}
								/>
								<Button variant="secondary" onClick={addUrl}>
									{__('Add', 'cf7-styler-for-divi')}
								</Button>
							</div>

							{urls.length === 0 ? (
								<p className="cf7m-tool-empty">
									{__('No URLs yet. Add one above.', 'cf7-styler-for-divi')}
								</p>
							) : (
								<div className="cf7m-tool-urls">
									{urls.map((url, index) => (
										<div key={index} className="cf7m-tool-url">
											<code className="cf7m-tool-url__text" title={url}>{url}</code>
											<button
												type="button"
												className="cf7m-tool-url__btn"
												onClick={() => testWebhook(index)}
												disabled={testingIndex === index}
											>
												{testingIndex === index
													? __('Testing…', 'cf7-styler-for-divi')
													: __('Test', 'cf7-styler-for-divi')}
											</button>
											<button
												type="button"
												className="cf7m-tool-url__btn cf7m-tool-url__btn--danger"
												onClick={() => removeUrl(index)}
												aria-label={__('Remove', 'cf7-styler-for-divi')}
											>
												<TrashIcon aria-hidden="true" />
											</button>
										</div>
									))}
								</div>
							)}

							<div className="cf7m-tool-section__actions">
								<button
									type="button"
									className="cf7m-tool-link"
									onClick={() => setShowPayload(!showPayload)}
								>
									{showPayload
										? __('Hide payload', 'cf7-styler-for-divi')
										: __('Example payload', 'cf7-styler-for-divi')}
								</button>
								<Button variant="primary" onClick={save} disabled={saving || !dirty}>
									{saving ? __('Saving…', 'cf7-styler-for-divi') : __('Save', 'cf7-styler-for-divi')}
								</Button>
							</div>

							{showPayload && (
								<pre className="cf7m-tool-code">
									{JSON.stringify(EXAMPLE_PAYLOAD, null, 2)}
								</pre>
							)}
						</>
					)}

				</div>
			)}

		</div>
	);
}
