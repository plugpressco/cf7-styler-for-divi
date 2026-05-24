/**
 * Step Finish – You're all set. What you can do now, Pro benefits, and clear next actions.
 * @since 3.0.0
 */

import { __ } from '@wordpress/i18n';

const StepFinish = ({ onComplete }) => {
	const isPro = typeof dcsOnboarding !== 'undefined' && !!dcsOnboarding.is_pro;
	const cf7Url = typeof dcsOnboarding !== 'undefined' && dcsOnboarding.cf7_admin_url
		? dcsOnboarding.cf7_admin_url
		: '/wp-admin/admin.php?page=wpcf7';
	const dashboardUrl = typeof dcsOnboarding !== 'undefined' && dcsOnboarding.dashboard_url
		? dcsOnboarding.dashboard_url
		: '/wp-admin/admin.php?page=cf7-mate';
	const pricingUrl = typeof dcsOnboarding !== 'undefined' && dcsOnboarding.pricing_url
		? dcsOnboarding.pricing_url
		: 'https://cf7mate.com/pricing';

	const goToCf7 = () => {
		if (onComplete) onComplete();
		window.location.href = cf7Url;
	};

	const goToDashboard = () => {
		if (onComplete) onComplete();
		window.location.href = dashboardUrl;
	};

	const closeGuide = () => {
		if (onComplete) onComplete();
	};

	return (
		<div className="cf7m-onboarding-step cf7m-step-finish">
			<div className="cf7m-step-header">
				<span className="cf7m-step-label">{__('Step 4 of 4', 'cf7-styler-for-divi')}</span>
				<div className="cf7m-finish-icon" aria-hidden="true">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
						<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
						<polyline points="22 4 12 14.01 9 11.01" />
					</svg>
				</div>
				<h2 className="cf7m-onboarding-title">
					{__("You're all set with CF7 Mate", 'cf7-styler-for-divi')}
				</h2>
				<p className="cf7m-onboarding-description">
					{__('Style your Contact Form 7 forms, manage features from the dashboard, and get help from docs and the community whenever you need it.', 'cf7-styler-for-divi')}
				</p>
			</div>

			<div className="cf7m-finish-what-now">
				<p className="cf7m-finish-what-now-title">{__('What you can do now', 'cf7-styler-for-divi')}</p>
				<ul className="cf7m-finish-what-now-list">
					<li>{__('Add the CF7 Mate module in your page builder and pick a form to style', 'cf7-styler-for-divi')}</li>
					<li>{__('Turn features on or off from the CF7 Mate dashboard', 'cf7-styler-for-divi')}</li>
					<li>{__('Create and edit forms in Contact Form 7, then style them with CF7 Mate', 'cf7-styler-for-divi')}</li>
				</ul>
			</div>

			{!isPro && (
				<div className="cf7m-finish-pro-teaser">
					<p className="cf7m-finish-pro-teaser-title">{__('With Pro you get', 'cf7-styler-for-divi')}</p>
					<p className="cf7m-finish-pro-teaser-text">
						{__('Form entries, multi-step forms, AI form generator, conditional logic, and 14 powerful modules.', 'cf7-styler-for-divi')}{' '}
						<a href={pricingUrl} target="_blank" rel="noopener noreferrer">{__('Unlock Pro', 'cf7-styler-for-divi')}</a>
					</p>
				</div>
			)}

			<div className="cf7m-finish-actions">
				<button type="button" className="cf7m-finish-btn cf7m-finish-btn-primary" onClick={goToDashboard}>
					{__('Go to CF7 Mate dashboard', 'cf7-styler-for-divi')}
				</button>
				<button type="button" className="cf7m-finish-btn cf7m-finish-btn-secondary" onClick={goToCf7}>
					{__('Go to Contact Form 7', 'cf7-styler-for-divi')}
				</button>
			</div>
			<button type="button" className="cf7m-finish-close-guide" onClick={closeGuide}>
				{__('Close guide', 'cf7-styler-for-divi')}
			</button>
		</div>
	);
};

export default StepFinish;
