/**
 * Two-step setup shown inline above Features. It never covers the page,
 * saves through the onboarding AJAX handler and stays open until the save
 * succeeds, so a failed request can be retried.
 *
 * @package CF7_Mate
 */

import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { XMarkIcon, ArrowUpRightIcon } from '@heroicons/react/20/solid';
import { Button, CheckTile, buttonVariants } from './ui';
import { FujiArt, SunriseArt } from './Art';
import { cn } from '../lib/utils';
import { getConfig } from '../utils/config';
import { play } from '../utils/sound';

/** Shown once after setup is saved: the sun rises over Fuji, then it fades away. */
export function SetupDone({ onClose }) {
	const [leaving, setLeaving] = useState(false);
	const close = useRef(onClose);
	close.current = onClose;
	useEffect(() => {
		play('chime');
		const fade = setTimeout(() => setLeaving(true), 6000);
		const done = setTimeout(() => close.current(), 6200);
		return () => {
			clearTimeout(fade);
			clearTimeout(done);
		};
	}, []);
	const formsUrl = getConfig().cf7_admin_url || 'admin.php?page=wpcf7';
	return (
		<div className="pointer-events-none fixed inset-x-0 bottom-8 z-[100000] flex justify-center px-4">
			<div
				role="status"
				className={cn(
					'pointer-events-auto relative flex w-[360px] max-w-full items-center gap-4 overflow-hidden rounded-box border border-sage-border bg-card p-3.5 pe-10 shadow-toast',
					leaving ? 'animate-toast-out' : 'animate-popup-in'
				)}
			>
				<SunriseArt className="h-16 w-[100px] shrink-0 overflow-hidden rounded-tile" />
				<div className="min-w-0">
					<p className="text-sm font-semibold">{__('All set', 'cf7-styler-for-divi')}</p>
					<p className="text-[13px] text-muted-foreground">{__('Your choices are saved.', 'cf7-styler-for-divi')}</p>
					<a href={formsUrl} className="mt-1 inline-block text-[13px] font-medium text-primary hover:underline">{__('Open your forms', 'cf7-styler-for-divi')}</a>
				</div>
				<Button variant="ghost" size="icon" className="absolute end-1.5 top-1.5 h-7 w-7" aria-label={__('Close', 'cf7-styler-for-divi')} onClick={() => close.current()}>
					<XMarkIcon aria-hidden="true" />
				</Button>
			</div>
		</div>
	);
}

async function post(action, fields) {
	const config = getConfig();
	const body = new URLSearchParams({ action, nonce: (config.setup && config.setup.nonce) || '', ...fields });
	const response = await fetch(config.ajax_url, {
		method: 'POST',
		credentials: 'same-origin',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body,
	});
	const data = await response.json();
	if (!data || !data.success) throw new Error('Request failed');
	return data;
}

function Pips({ step }) {
	return (
		<span aria-hidden="true" className="inline-flex gap-1">
			{[1, 2].map((item) => (
				<span key={item} className={cn('block h-1.5 w-[18px] rounded-full transition-colors', item <= step ? 'bg-primary' : 'bg-sage-line')} />
			))}
		</span>
	);
}

export function SetupCard({ features, onDone }) {
	const config = getConfig();
	const saddle = config.saddle || {};
	const optional = (config.feature_metadata || []).filter((feature) => feature.default === false);
	const [step, setStep] = useState(1);
	const [draft, setDraft] = useState({});
	const [busy, setBusy] = useState('');
	const [error, setError] = useState('');

	// Unchanged choices follow the list below, so both always agree.
	const choice = (id) => (id in draft ? draft[id] : !!features[id]);

	const finish = async () => {
		if (busy) return;
		const saved = {};
		optional.forEach((feature) => { saved[feature.id] = choice(feature.id); });
		setBusy('finish');
		setError('');
		try {
			await post('cf7m_complete_onboarding', { features: JSON.stringify(saved) });
			onDone(saved);
		} catch (err) {
			setError(__('Could not save your setup. Try again.', 'cf7-styler-for-divi'));
			setBusy('');
		}
	};

	const skip = async () => {
		if (busy) return;
		setBusy('skip');
		setError('');
		try {
			await post('cf7m_skip_onboarding', {});
			onDone(null);
		} catch (err) {
			setError(__('Could not skip setup. Try again.', 'cf7-styler-for-divi'));
			setBusy('');
		}
	};

	const title = step === 1 ? __('What should CF7 Mate collect?', 'cf7-styler-for-divi') : __('Build forms with your AI', 'cf7-styler-for-divi');

	return (
		<section aria-labelledby="cf7m-setup-title" className="relative overflow-hidden rounded-box border border-sage-border bg-sage px-5 py-[18px]">
			<FujiArt className="absolute end-6 top-0 h-[132px] w-[250px] max-[700px]:opacity-40" />

			<div className="relative flex items-center justify-between gap-3">
				<p className="flex items-center gap-2.5 text-xs font-medium text-primary">
					{__('Quick setup', 'cf7-styler-for-divi')}
					<Pips step={step} />
					<span className="sr-only">{step === 1 ? __('Step 1 of 2', 'cf7-styler-for-divi') : __('Step 2 of 2', 'cf7-styler-for-divi')}</span>
				</p>
				<Button variant="ghost" size="icon" className="hover:bg-white/50" aria-label={__('Skip setup', 'cf7-styler-for-divi')} onClick={skip} disabled={!!busy}>
					<XMarkIcon aria-hidden="true" />
				</Button>
			</div>

			<h2 id="cf7m-setup-title" className="relative mt-1.5 text-[17px] font-semibold tracking-[-0.2px]">{title}</h2>

			{step === 1 && (
				<div className="relative mt-3.5 grid max-w-[920px] grid-cols-[repeat(auto-fill,minmax(210px,1fr))] gap-2">
					{optional.map((feature) => (
						<CheckTile
							key={feature.id}
							id={`cf7m-setup-${feature.id}`}
							label={feature.name}
							description={feature.desc}
							checked={choice(feature.id)}
							onChange={(value) => setDraft((current) => ({ ...current, [feature.id]: value }))}
						/>
					))}
				</div>
			)}

			{step === 2 && (
				<div className="relative">
					<p className="mt-1 text-[13px] text-muted-foreground">{__('Optional. Saddle lets Claude or ChatGPT create forms on this site.', 'cf7-styler-for-divi')}</p>
					<div className="mt-3.5 flex flex-wrap items-center gap-x-4 gap-y-2">
						<a className={buttonVariants({ variant: 'secondary' })} href={saddle.setup_url || 'https://wordpress.org/plugins/saddle/'} target="_blank" rel="noopener noreferrer">
							{saddle.active ? __('Open Saddle', 'cf7-styler-for-divi') : __('Get Saddle', 'cf7-styler-for-divi')}
							<ArrowUpRightIcon aria-hidden="true" />
						</a>
						<a className="text-[13px] font-medium text-primary hover:underline" href={saddle.analytics_url || 'https://wordpress.org/plugins/saddle-analytics/'} target="_blank" rel="noopener noreferrer">
							{__('Saddle Analytics', 'cf7-styler-for-divi')}
						</a>
					</div>
				</div>
			)}

			{error && <p role="alert" className="relative mt-3 text-[13px] text-destructive">{error}</p>}

			<div className={cn('relative mt-3.5 flex gap-2', step === 1 ? 'justify-end' : 'justify-between')}>
				{step === 1 ? (
					<>
						<Button variant="ghost" onClick={skip} disabled={!!busy}>{__('Skip', 'cf7-styler-for-divi')}</Button>
						<Button variant="primary" onClick={() => setStep(2)} disabled={!!busy}>{__('Continue', 'cf7-styler-for-divi')}</Button>
					</>
				) : (
					<>
						<Button variant="ghost" onClick={() => setStep(1)} disabled={!!busy}>{__('Back', 'cf7-styler-for-divi')}</Button>
						<Button variant="primary" onClick={finish} disabled={!!busy} aria-busy={busy === 'finish'}>{__('Finish', 'cf7-styler-for-divi')}</Button>
					</>
				)}
			</div>
		</section>
	);
}
