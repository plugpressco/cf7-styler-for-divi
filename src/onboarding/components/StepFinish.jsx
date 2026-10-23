/**
 * Step Finish – You're all set. What you can do now and clear next actions.
 * @since 3.0.0
 */

import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const StepFinish = ({ onComplete }) => {
	const cf7Url = typeof dcsOnboarding !== 'undefined' && dcsOnboarding.cf7_admin_url
		? dcsOnboarding.cf7_admin_url
		: '/wp-admin/admin.php?page=wpcf7';
	const dashboardUrl = typeof dcsOnboarding !== 'undefined' && dcsOnboarding.dashboard_url
		? dcsOnboarding.dashboard_url
		: '/wp-admin/admin.php?page=cf7-mate';

	const [saving, setSaving] = useState(false);
	const [error, setError] = useState('');

	const finish = async (url) => {
		if (saving) return;
		setSaving(true);
		setError('');
		try {
			if (onComplete) await onComplete();
			if (url) window.location.href = url;
		} catch (err) {
			setError(__('Could not save your setup. Please try again.', 'cf7-styler-for-divi'));
			setSaving(false);
		}
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


			{error && <p role="alert">{error}</p>}
			<div className="cf7m-finish-actions" aria-busy={saving}>
				<button type="button" className="cf7m-finish-btn cf7m-finish-btn-primary" disabled={saving} onClick={() => finish(dashboardUrl)}>
					{__('Go to CF7 Mate dashboard', 'cf7-styler-for-divi')}
				</button>
				<button type="button" className="cf7m-finish-btn cf7m-finish-btn-secondary" disabled={saving} onClick={() => finish(cf7Url)}>
					{__('Go to Contact Form 7', 'cf7-styler-for-divi')}
				</button>
			</div>
			<button type="button" className="cf7m-finish-close-guide" disabled={saving} onClick={() => finish()}>
				{__('Close guide', 'cf7-styler-for-divi')}
			</button>
		</div>
	);
};

export default StepFinish;
