/**
 * Upsell placeholder – shown when a free user navigates to a pro-only page.
 * Clean two-column layout: feature info left, decorative illustration right.
 *
 * @package CF7_Mate
 */

import { __ } from '@wordpress/i18n';
import {
	CheckIcon,
	CircleStackIcon,
	BoltIcon,
	SparklesIcon,
	Squares2X2Icon,
} from '@heroicons/react/24/outline';

const FEATURE_ICONS = {
	database: CircleStackIcon,
	webhook: BoltIcon,
	ai: SparklesIcon,
	module: Squares2X2Icon,
};

const FEATURE_DATA = {
	responses: {
		title: __('Form Responses', 'cf7-styler-for-divi'),
		icon: 'database',
		desc: __('Save every Contact Form 7 submission to your WordPress database. Never lose a lead — view, search, filter, and export responses anytime.', 'cf7-styler-for-divi'),
		features: [
			__('Save all submissions to database', 'cf7-styler-for-divi'),
			__('Search, filter & sort responses', 'cf7-styler-for-divi'),
			__('Export responses to CSV', 'cf7-styler-for-divi'),
		],
	},
	webhook: {
		title: __('Webhook Integration', 'cf7-styler-for-divi'),
		icon: 'webhook',
		desc: __('Send form data to external services like Zapier, Make, or any custom endpoint. Automate your workflow on every form submission.', 'cf7-styler-for-divi'),
		features: [
			__('Connect to Zapier, Make & more', 'cf7-styler-for-divi'),
			__('Send data to any URL on submit', 'cf7-styler-for-divi'),
			__('JSON payload with all form fields', 'cf7-styler-for-divi'),
		],
	},
	'ai-settings': {
		title: __('AI Form Generator', 'cf7-styler-for-divi'),
		icon: 'ai',
		desc: __('Generate Contact Form 7 forms with AI. Describe your form in plain English and get a ready-to-use form in seconds.', 'cf7-styler-for-divi'),
		features: [
			__('Generate forms from a text prompt', 'cf7-styler-for-divi'),
			__('Supports all CF7 field types', 'cf7-styler-for-divi'),
			__('One-click form creation', 'cf7-styler-for-divi'),
		],
	},
};

export function UpsellPlaceholder({ feature }) {
	const data = FEATURE_DATA[feature] || FEATURE_DATA.responses;
	const pricingUrl = (typeof dcsCF7Styler !== 'undefined' && dcsCF7Styler.pricing_url) || '#';
	const settingsUrl = (typeof dcsCF7Styler !== 'undefined' && dcsCF7Styler.dash_url) || 'admin.php?page=cf7-mate';
	const freeVsProUrl = pricingUrl;
	const IconComponent = FEATURE_ICONS[data.icon] || FEATURE_ICONS.module;
	const checkClass = 'cf7m-upsell__check-icon';

	return (
		<div className="cf7m-card cf7m-upsell">
			<div className="cf7m-upsell__content">
				<div className="cf7m-upsell__icon" aria-hidden="true">
					<IconComponent />
				</div>
				<h2 className="cf7m-upsell__title">{data.title}</h2>
				<p className="cf7m-upsell__desc">{data.desc}</p>
				<ul className="cf7m-upsell__list" aria-label={__('Feature highlights', 'cf7-styler-for-divi')}>
					{data.features.map((label) => (
						<li key={label} className="cf7m-upsell__list-item">
							<span className="cf7m-upsell__check" aria-hidden="true"><CheckIcon className={checkClass} /></span>
							{label}
						</li>
					))}
				</ul>
				<div className="cf7m-upsell__actions">
					<a href={pricingUrl} target="_blank" rel="noopener noreferrer" className="cf7m-upsell__cta">
						{__('Get', 'cf7-styler-for-divi')} {data.title}
					</a>
					<a href={freeVsProUrl} className="cf7m-upsell__link">
						{__('See all features', 'cf7-styler-for-divi')}
					</a>
				</div>
			</div>
			<div className="cf7m-upsell__visual" aria-hidden="true">
				<div className="cf7m-upsell__visual-card">
					<div className="cf7m-upsell__visual-bar" />
					<div className="cf7m-upsell__visual-line cf7m-upsell__visual-line--w80" />
					<div className="cf7m-upsell__visual-line cf7m-upsell__visual-line--w60" />
					<div className="cf7m-upsell__visual-line cf7m-upsell__visual-line--w90" />
					<div className="cf7m-upsell__visual-btn" />
				</div>
			</div>
		</div>
	);
}
