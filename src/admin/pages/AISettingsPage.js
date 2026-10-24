/**
 * AI form generator: provider, model and key.
 *
 * @package CF7_Mate
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { SparklesIcon } from '@heroicons/react/24/outline';
import { ArrowUpRightIcon } from '@heroicons/react/20/solid';
import apiFetch from '@wordpress/api-fetch';
import { Button, Card, CardContent, CardFooter, CardHeader, CardTitle, Checkbox, IconChip, InlineStatus, Input, LoadingState, NativeSelect } from '../components/ui';
import { getConfig } from '../utils/config';

export function FieldRow({ label, htmlFor, children }) {
	return (
		<>
			<label htmlFor={htmlFor} className="text-[13px] text-foreground max-[782px]:mt-2">{label}</label>
			<div className="min-w-0">{children}</div>
		</>
	);
}

export const fieldGrid = 'grid grid-cols-[160px_minmax(0,1fr)] items-center gap-x-5 gap-y-3 max-[782px]:grid-cols-1 max-[782px]:gap-y-1';

export function AISettingsPage({ features, onToggle, saving: parentSaving }) {
	const aiEnabled = !!(features && features.ai_form_generator);
	const providers = getConfig().aiProviders || {};

	const [loading, setLoading] = useState(true);
	const [saving, setSaving] = useState(false);
	const [testing, setTesting] = useState(false);
	const [status, setStatus] = useState(null);
	const [settings, setSettings] = useState({
		provider: 'openai',
		openai_key: '',
		openai_model: 'gpt-4o-mini',
		anthropic_key: '',
		anthropic_model: 'claude-sonnet-4-20250514',
		grok_key: '',
		grok_model: 'grok-3-mini',
		kimi_key: '',
		kimi_model: 'moonshot-v1-32k',
	});

	useEffect(() => {
		if (!aiEnabled) { setLoading(false); return; }
		setLoading(true);
		(async () => {
			try {
				const data = await apiFetch({ path: '/cf7-styler/v1/ai-settings' });
				setSettings((prev) => ({ ...prev, ...data }));
			} catch (err) {
				setStatus({ type: 'error', message: err.message || __('Could not load settings.', 'cf7-styler-for-divi') });
			} finally {
				setLoading(false);
			}
		})();
	}, [aiEnabled]);

	const show = (type, message) => {
		setStatus({ type, message });
		if (type === 'success') setTimeout(() => setStatus((current) => (current && current.message === message ? null : current)), 3000);
	};

	const reload = async () => {
		const data = await apiFetch({ path: '/cf7-styler/v1/ai-settings' });
		setSettings((prev) => ({ ...prev, ...data }));
	};

	const saveSettings = async () => {
		setSaving(true);
		setStatus({ type: 'success', message: __('Saving…', 'cf7-styler-for-divi') });
		try {
			await apiFetch({ path: '/cf7-styler/v1/ai-settings', method: 'POST', data: settings });
			show('success', __('Saved.', 'cf7-styler-for-divi'));
			await reload();
		} catch (err) {
			show('error', err.message || __('Could not save.', 'cf7-styler-for-divi'));
		} finally {
			setSaving(false);
		}
	};

	const testConnection = async () => {
		setTesting(true);
		setStatus({ type: 'success', message: __('Testing…', 'cf7-styler-for-divi') });
		try {
			const result = await apiFetch({
				path: '/cf7-styler/v1/ai-settings/test',
				method: 'POST',
				data: { provider: settings.provider },
			});
			show(
				result.success ? 'success' : 'error',
				result.message || (result.success ? __('Connection works.', 'cf7-styler-for-divi') : __('Test failed.', 'cf7-styler-for-divi'))
			);
		} catch (err) {
			show('error', err.message || __('Test failed.', 'cf7-styler-for-divi'));
		} finally {
			setTesting(false);
		}
	};

	const removeKey = async () => {
		const keyField = `${settings.provider}_key`;
		setSaving(true);
		try {
			await apiFetch({ path: '/cf7-styler/v1/ai-settings', method: 'POST', data: { ...settings, [keyField]: '' } });
			show('success', __('API key removed.', 'cf7-styler-for-divi'));
			await reload();
		} catch (err) {
			show('error', err.message || __('Could not remove the key.', 'cf7-styler-for-divi'));
		} finally {
			setSaving(false);
		}
	};

	const update = (key, value) => setSettings((prev) => ({ ...prev, [key]: value }));

	const providerIsEmpty = Object.keys(providers).length === 0;
	const provider = providers[settings.provider] || {};
	const keyField = `${settings.provider}_key`;
	const modelField = `${settings.provider}_model`;
	const isKeySet = !!settings[`${keyField}_set`];
	const maskedKey = settings[`${keyField}_masked`] || '••••-••••-••••';
	const providerOptions = Object.entries(providers).map(([value, cfg]) => ({ label: cfg.name, value }));
	const modelOptions = Object.entries(provider.models || {}).map(([value, label]) => ({ label, value }));

	return (
		<Card aria-labelledby="cf7m-ai-title">
			<CardHeader>
				<div className="flex items-center gap-2.5">
					<IconChip tone="sage" size="sm"><SparklesIcon /></IconChip>
					<CardTitle id="cf7m-ai-title">{__('AI form generator', 'cf7-styler-for-divi')}</CardTitle>
				</div>
				<Checkbox
					label={<span id="cf7m-ai-enabled">{__('Enabled', 'cf7-styler-for-divi')}</span>}
					aria-labelledby="cf7m-ai-title cf7m-ai-enabled"
					checked={aiEnabled}
					disabled={!!parentSaving}
					onChange={(value) => onToggle && onToggle('ai_form_generator', value)}
				/>
			</CardHeader>

			{(providerIsEmpty || !aiEnabled) && (
				<CardContent className="text-[13px] text-muted-foreground">
					{aiEnabled
						? __('Reload this page to finish turning on AI settings.', 'cf7-styler-for-divi')
						: __('Turn this on to connect a provider. Your provider may charge for use.', 'cf7-styler-for-divi')}
				</CardContent>
			)}

			{!providerIsEmpty && aiEnabled && (
				<>
					<CardContent>
						{loading ? (
							<LoadingState rows={3} />
						) : (
							<div className={fieldGrid}>
								<FieldRow label={__('Provider', 'cf7-styler-for-divi')} htmlFor="cf7m-ai-provider">
									<NativeSelect id="cf7m-ai-provider" className="w-full max-w-[380px]" value={settings.provider} options={providerOptions} onChange={(event) => update('provider', event.target.value)} />
								</FieldRow>
								<FieldRow label={__('Model', 'cf7-styler-for-divi')} htmlFor="cf7m-ai-model">
									<NativeSelect id="cf7m-ai-model" className="w-full max-w-[380px]" value={settings[modelField] || ''} options={modelOptions} onChange={(event) => update(modelField, event.target.value)} />
								</FieldRow>
								<FieldRow label={__('API key', 'cf7-styler-for-divi')} htmlFor="cf7m-ai-key">
									{isKeySet ? (
										<div className="flex min-h-[34px] flex-wrap items-center gap-x-4 gap-y-1">
											<code className="font-mono text-[12.5px] text-foreground">{maskedKey}</code>
											<Button variant="danger" onClick={removeKey} disabled={saving}>{__('Remove API key', 'cf7-styler-for-divi')}</Button>
										</div>
									) : (
										<div className="flex max-w-[380px] flex-col items-start gap-1.5">
											<Input
												id="cf7m-ai-key"
												type="password"
												autoComplete="off"
												value={settings[keyField] || ''}
												onChange={(event) => update(keyField, event.target.value)}
												placeholder={provider.key_placeholder || 'sk-...'}
											/>
											{provider.key_url && (
												<a href={provider.key_url} target="_blank" rel="noopener noreferrer" className="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline">
													{__('Get an API key', 'cf7-styler-for-divi')}
													<ArrowUpRightIcon aria-hidden="true" className="h-3.5 w-3.5" />
												</a>
											)}
										</div>
									)}
								</FieldRow>
							</div>
						)}
					</CardContent>
					<CardFooter>
						<Button variant="primary" onClick={saveSettings} disabled={saving || loading} aria-busy={saving}>{__('Save', 'cf7-styler-for-divi')}</Button>
						{isKeySet && (
							<Button onClick={testConnection} disabled={testing || saving} aria-busy={testing}>{__('Test connection', 'cf7-styler-for-divi')}</Button>
						)}
						<InlineStatus status={status} />
					</CardFooter>
				</>
			)}
		</Card>
	);
}
