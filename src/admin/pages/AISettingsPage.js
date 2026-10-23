/**
 * AI Generator – flat tool section.
 *
 * @package CF7_Mate
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, SelectControl, TextControl } from '@wordpress/components';
import { ArrowUpRightIcon, KeyIcon, XMarkIcon } from '@heroicons/react/24/outline';
import apiFetch from '@wordpress/api-fetch';
import { Toggle } from '../components/Toggle';

export function AISettingsPage({ features, onToggle, saving: parentSaving }) {
	const aiEnabled  = !!(features && features.ai_form_generator);
	const providers  = (typeof dcsCF7Styler !== 'undefined' && dcsCF7Styler.aiProviders) || {};

	const [loading, setLoading] = useState(true);
	const [saving, setSaving]   = useState(false);
	const [testing, setTesting] = useState(false);
	const [notice, setNotice]   = useState(null);
	const [settings, setSettings] = useState({
		provider:        'openai',
		openai_key:      '',
		openai_model:    'gpt-4o-mini',
		anthropic_key:   '',
		anthropic_model: 'claude-sonnet-4-20250514',
		grok_key:        '',
		grok_model:      'grok-3-mini',
		kimi_key:        '',
		kimi_model:      'moonshot-v1-32k',
	});

	useEffect(() => {
		if (!aiEnabled) { setLoading(false); return; }
		setLoading(true);
		(async () => {
			try {
				const data = await apiFetch({ path: '/cf7-styler/v1/ai-settings' });
				setSettings((prev) => ({ ...prev, ...data }));
			} catch (err) {
				setNotice({ type: 'error', message: err.message || __('Failed to load settings.', 'cf7-styler-for-divi') });
			} finally {
				setLoading(false);
			}
		})();
	}, [aiEnabled]);

	const showNotice = (type, message) => {
		setNotice({ type, message });
		if (type === 'success') setTimeout(() => setNotice(null), 3000);
	};

	const reload = async () => {
		const data = await apiFetch({ path: '/cf7-styler/v1/ai-settings' });
		setSettings((prev) => ({ ...prev, ...data }));
	};

	const saveSettings = async () => {
		setSaving(true);
		setNotice(null);
		try {
			await apiFetch({ path: '/cf7-styler/v1/ai-settings', method: 'POST', data: settings });
			showNotice('success', __('Saved.', 'cf7-styler-for-divi'));
			await reload();
		} catch (err) {
			showNotice('error', err.message || __('Save failed.', 'cf7-styler-for-divi'));
		} finally {
			setSaving(false);
		}
	};

	const testConnection = async () => {
		setTesting(true);
		setNotice(null);
		try {
			const result = await apiFetch({
				path: '/cf7-styler/v1/ai-settings/test',
				method: 'POST',
				data: { provider: settings.provider },
			});
			showNotice(
				result.success ? 'success' : 'error',
				result.message || (result.success
					? __('Connection OK.', 'cf7-styler-for-divi')
					: __('Test failed.', 'cf7-styler-for-divi'))
			);
		} catch (err) {
			showNotice('error', err.message || __('Test failed.', 'cf7-styler-for-divi'));
		} finally {
			setTesting(false);
		}
	};

	const removeKey = async () => {
		const keyField = `${settings.provider}_key`;
		setSaving(true);
		try {
			await apiFetch({
				path: '/cf7-styler/v1/ai-settings',
				method: 'POST',
				data: { ...settings, [keyField]: '' },
			});
			showNotice('success', __('API key removed.', 'cf7-styler-for-divi'));
			await reload();
		} catch (err) {
			showNotice('error', err.message || __('Failed.', 'cf7-styler-for-divi'));
		} finally {
			setSaving(false);
		}
	};

	const update = (key, value) => setSettings((prev) => ({ ...prev, [key]: value }));

	const providerIsEmpty = Object.keys(providers).length === 0;

	const provider       = providers[settings.provider] || {};
	const keyField       = `${settings.provider}_key`;
	const modelField     = `${settings.provider}_model`;
	const isKeySet       = !!settings[`${keyField}_set`];
	const maskedKey      = settings[`${keyField}_masked`] || '••••-••••-••••';
	const providerOptions = Object.entries(providers).map(([key, cfg]) => ({ label: cfg.name, value: key }));
	const modelOptions    = Object.entries(provider.models || {}).map(([value, label]) => ({ label, value }));

	return (
		<div className="cf7m-tool-section">

			{/* Section header */}
			<div className="cf7m-tool-section__head">
				<div className="cf7m-tool-section__meta">
					<div className="cf7m-tool-section__name-row">
						<span className="cf7m-tool-section__name">
							{__('AI Generator', 'cf7-styler-for-divi')}
						</span>
						{isKeySet && aiEnabled && (
							<span className="cf7m-tool-badge">
								{__('Key saved', 'cf7-styler-for-divi')}
							</span>
						)}
					</div>
					<span className="cf7m-tool-section__desc">
						{__('Generate CF7 forms from a plain-English prompt in the form editor.', 'cf7-styler-for-divi')}
					</span>
				</div>
				<Toggle
					label={__('AI form generator', 'cf7-styler-for-divi')}
					checked={aiEnabled}
					onChange={(v) => onToggle && onToggle('ai_form_generator', v)}
					disabled={!!parentSaving}
				/>
			</div>

			{(providerIsEmpty || !aiEnabled) && (
				<p className="cf7m-tool-section__locked">
					{aiEnabled ? __('Reload this page to finish enabling AI settings.', 'cf7-styler-for-divi') : __('Enable AI generation to connect a provider. Provider charges may apply.', 'cf7-styler-for-divi')}
				</p>
			)}

			{!providerIsEmpty && aiEnabled && (
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
							<div className="cf7m-tool-fields">

								<div className="cf7m-tool-field">
									<span className="cf7m-tool-field__label">
										{__('Provider', 'cf7-styler-for-divi')}
									</span>
									<div className="cf7m-tool-field__control">
										<SelectControl
											__nextHasNoMarginBottom
											aria-label={__('Provider', 'cf7-styler-for-divi')}
											value={settings.provider}
											options={providerOptions}
											onChange={(v) => update('provider', v)}
										/>
									</div>
								</div>

								<div className="cf7m-tool-field">
									<span className="cf7m-tool-field__label">
										{__('Model', 'cf7-styler-for-divi')}
									</span>
									<div className="cf7m-tool-field__control">
										<SelectControl
											__nextHasNoMarginBottom
											aria-label={__('Model', 'cf7-styler-for-divi')}
											value={settings[modelField] || ''}
											options={modelOptions}
											onChange={(v) => update(modelField, v)}
										/>
									</div>
								</div>

								<div className="cf7m-tool-field">
									<span className="cf7m-tool-field__label">
										{__('API key', 'cf7-styler-for-divi')}
									</span>
									<div className="cf7m-tool-field__control">
										{isKeySet ? (
											<div className="cf7m-ai-key">
												<KeyIcon className="cf7m-ai-key__icon" aria-hidden="true" />
												<code className="cf7m-ai-key__masked">{maskedKey}</code>
												<button
													type="button"
													className="cf7m-ai-key__remove"
													onClick={removeKey}
													disabled={saving}
													aria-label={__('Remove API key', 'cf7-styler-for-divi')}
												>
													<XMarkIcon aria-hidden="true" />
												</button>
											</div>
										) : (
											<>
												<TextControl
													__nextHasNoMarginBottom
													aria-label={__('API key', 'cf7-styler-for-divi')}
												type="password"
													value={settings[keyField] || ''}
													onChange={(v) => update(keyField, v)}
													placeholder={provider.key_placeholder || 'sk-…'}
												/>
												{provider.key_url && (
													<a
														href={provider.key_url}
														target="_blank"
														rel="noopener noreferrer"
														className="cf7m-tool-link"
													>
														{__('Get an API key', 'cf7-styler-for-divi')}
														<ArrowUpRightIcon aria-hidden="true" />
													</a>
												)}
											</>
										)}
									</div>
								</div>

							</div>

							<div className="cf7m-tool-section__actions">
								{isKeySet && (
									<Button
										variant="tertiary"
										onClick={testConnection}
										disabled={testing || saving}
									>
										{testing
											? __('Testing…', 'cf7-styler-for-divi')
											: __('Test connection', 'cf7-styler-for-divi')}
									</Button>
								)}
								<Button
									variant="primary"
									onClick={saveSettings}
									disabled={saving}
								>
									{saving
										? __('Saving…', 'cf7-styler-for-divi')
										: __('Save', 'cf7-styler-for-divi')}
								</Button>
							</div>
						</>
					)}

				</div>
			)}

		</div>
	);
}
