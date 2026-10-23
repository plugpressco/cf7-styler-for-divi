/**
 * Step Welcome – value early, quick win (SaaS onboarding principles)
 * @see https://www.userflow.com/blog/saas-onboarding-flow-a-complete-guide
 * @see https://www.paddle.com/resources/saas-onboarding
 *
 * @since 3.0.0
 */

import { __ } from '@wordpress/i18n';

const StepWelcome = () => {

	const highlights = [
		__('Style Contact Form 7 forms — no code, just point and click', 'cf7-styler-for-divi'),
		__('Works with Divi, Elementor, Bricks, and more', 'cf7-styler-for-divi'),
		__('Includes multi-step forms, conditional logic, responses and more — all free', 'cf7-styler-for-divi'),
	];


	return (
		<div className="cf7m-onboarding-step cf7m-step-welcome">
			<div className="cf7m-step-header">
				<span className="cf7m-step-label">{__('Step 1 of 4', 'cf7-styler-for-divi')}</span>
				<h2 className="cf7m-onboarding-title">
					{__('Welcome to CF7 Mate', 'cf7-styler-for-divi')}
				</h2>
				<p className="cf7m-onboarding-description">
					{__('Four short steps to get you started. Style your forms, choose features, get help when you need it — about a minute.', 'cf7-styler-for-divi')}
				</p>
			</div>
			<ul className="cf7m-welcome-list">
				{highlights.map((text, i) => (
					<li key={i}>
						<span className="cf7m-check-icon" aria-hidden="true">
							<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3">
								<polyline points="20 6 9 17 4 12" />
							</svg>
						</span>
						{text}
					</li>
				))}
			</ul>

		</div>
	);
};

export default StepWelcome;
