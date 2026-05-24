/**
 * Step Help – support at key points (SaaS: contextual help, never feel stuck)
 * @since 3.0.0
 */

import { __ } from '@wordpress/i18n';

const RESOURCES = [
	{
		id: 'docs',
		title: __('Documentation', 'cf7-styler-for-divi'),
		desc: __('Step-by-step guides and how-tos', 'cf7-styler-for-divi'),
		url: 'https://cf7mate.com/docs',
		icon: (
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
				<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
				<polyline points="14 2 14 8 20 8" />
				<line x1="16" y1="13" x2="8" y2="13" />
				<line x1="16" y1="17" x2="8" y2="17" />
				<polyline points="10 9 9 9 8 9" />
			</svg>
		),
	},
	{
		id: 'support',
		title: __('Support', 'cf7-styler-for-divi'),
		desc: __('Get help from our team when you need it', 'cf7-styler-for-divi'),
		url: 'https://cf7mate.com/support',
		icon: (
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
				<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
			</svg>
		),
	},
	{
		id: 'pricing',
		title: __('Pro & Pricing', 'cf7-styler-for-divi'),
		desc: __('Upgrade for 14 powerful modules', 'cf7-styler-for-divi'),
		url: 'https://cf7mate.com/pricing',
		icon: (
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
				<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
			</svg>
		),
	},
];

const StepHelp = () => {
	const isPro = typeof dcsOnboarding !== 'undefined' && !!dcsOnboarding.is_pro;
	const resources = isPro ? RESOURCES.filter((r) => r.id !== 'pricing') : RESOURCES;

	return (
	<div className="cf7m-onboarding-step cf7m-step-help">
		<div className="cf7m-step-header">
			<span className="cf7m-step-label">{__('Step 3 of 4', 'cf7-styler-for-divi')}</span>
			<h2 className="cf7m-onboarding-title">
				{__('Help when you need it', 'cf7-styler-for-divi')}
			</h2>
			<p className="cf7m-onboarding-description">
				{isPro
					? __('Docs and support are always one click away.', 'cf7-styler-for-divi')
					: __('Docs, support, and pricing are always one click away.', 'cf7-styler-for-divi')}
			</p>
		</div>
		<div className="cf7m-help-list">
			{resources.map((r) => (
				<a
					key={r.id}
					href={r.url}
					target="_blank"
					rel="noopener noreferrer"
					className="cf7m-help-item"
				>
					<span className="cf7m-help-icon" aria-hidden="true">
						{r.icon}
					</span>
					<div className="cf7m-help-item-text">
						<span className="cf7m-help-item-title">{r.title}</span>
						<span className="cf7m-help-item-desc">{r.desc}</span>
					</div>
					<span className="cf7m-help-arrow">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
							<line x1="5" y1="12" x2="19" y2="12" />
							<polyline points="12 5 19 12 12 19" />
						</svg>
					</span>
				</a>
			))}
		</div>
	</div>
	);
};

export default StepHelp;
