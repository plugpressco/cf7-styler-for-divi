/**
 * Features – single flat list, no sections.
 *
 * @package CF7_Mate
 */

import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { InformationCircleIcon } from '@heroicons/react/24/outline';
import { Toggle } from './Toggle';

const ALL_FEATURES = [
	// Pro features
	{ id: 'conditional',      name: __('Conditional logic',   'cf7-styler-for-divi'), desc: __('Show or hide fields based on user answers.',          'cf7-styler-for-divi'), isPro: true },
	{ id: 'multi_step',       name: __('Multi-step forms',    'cf7-styler-for-divi'), desc: __('Split long forms into multiple steps.',                'cf7-styler-for-divi'), isPro: true,  info: 'https://cf7mate.com/docs/multi-step-forms' },
	{ id: 'database_entries', name: __('Form responses',      'cf7-styler-for-divi'), desc: __('Save form submissions to the database.',               'cf7-styler-for-divi'), isPro: true,  info: 'https://cf7mate.com/docs/manage-contact-form-7-submissions' },
	{ id: 'multi_column',     name: __('Multi-column layout', 'cf7-styler-for-divi'), desc: __('Arrange fields side-by-side in columns.',              'cf7-styler-for-divi'), isPro: true },
	{ id: 'presets',          name: __('Style presets',       'cf7-styler-for-divi'), desc: __('Apply pre-built form themes instantly.',               'cf7-styler-for-divi'), isPro: true },
	{ id: 'analytics',        name: __('Form analytics',      'cf7-styler-for-divi'), desc: __('Track views, submissions, and conversion rates.',      'cf7-styler-for-divi'), isPro: true },
	{ id: 'form_scheduling',  name: __('Form scheduling',     'cf7-styler-for-divi'), desc: __('Automatically open and close forms by date.',          'cf7-styler-for-divi'), isPro: true },
	{ id: 'email_routing',    name: __('Email routing',       'cf7-styler-for-divi'), desc: __('Route notifications to different email addresses.',    'cf7-styler-for-divi'), isPro: true },
	{ id: 'partial_save',     name: __('Save & Continue',     'cf7-styler-for-divi'), desc: __('Let users save progress and return to the form later.','cf7-styler-for-divi'), isPro: true },
	// Page builder integrations (filtered by installed builders)
	{ id: 'cf7_module',       name: __('Divi',                'cf7-styler-for-divi'), desc: __('Style CF7 forms inside the Divi Builder.',             'cf7-styler-for-divi'), isPro: false, builder: 'divi', info: 'https://cf7mate.com/docs/style-contact-form-7-in-divi' },
	{ id: 'bricks_module',    name: __('Bricks',              'cf7-styler-for-divi'), desc: __('Style CF7 forms inside Bricks.',                       'cf7-styler-for-divi'), isPro: false, builder: 'bricks' },
	{ id: 'elementor_module', name: __('Elementor',           'cf7-styler-for-divi'), desc: __('Style CF7 forms inside Elementor.',                    'cf7-styler-for-divi'), isPro: false, builder: 'elementor' },
	{ id: 'gutenberg_module', name: __('Block Editor',        'cf7-styler-for-divi'), desc: __('Style CF7 forms in the WordPress block editor.',       'cf7-styler-for-divi'), isPro: false, builder: 'gutenberg' },
	// Advanced fields
	{ id: 'star_rating',      name: __('Star rating',         'cf7-styler-for-divi'), desc: __('Collect ratings with a clickable star widget.',        'cf7-styler-for-divi'), isPro: false },
	{ id: 'range_slider',     name: __('Range slider',        'cf7-styler-for-divi'), desc: __('Let users pick a numeric value with a slider.',        'cf7-styler-for-divi'), isPro: false },
	{ id: 'separator',        name: __('Separator',           'cf7-styler-for-divi'), desc: __('Add a visual divider line between fields.',            'cf7-styler-for-divi'), isPro: false },
	{ id: 'image',            name: __('Image',               'cf7-styler-for-divi'), desc: __('Insert a static image inside the form.',               'cf7-styler-for-divi'), isPro: false },
	{ id: 'icon',             name: __('Icon',                'cf7-styler-for-divi'), desc: __('Add an icon element to the form.',                     'cf7-styler-for-divi'), isPro: false },
	{ id: 'phone_number',     name: __('Phone number',        'cf7-styler-for-divi'), desc: __('International phone input with country flag picker.',  'cf7-styler-for-divi'), isPro: false },
	{ id: 'heading',          name: __('Heading',             'cf7-styler-for-divi'), desc: __('Add a heading or label inside the form.',              'cf7-styler-for-divi'), isPro: false },
];

export function FeaturesSection({ features, isPro, onToggle, saving }) {
	const builderFlags = useMemo(() => {
		const fromCfg =
			typeof dcsCF7Styler !== 'undefined' && dcsCF7Styler.builders
				? dcsCF7Styler.builders
				: {};
		return {
			divi:      !!fromCfg.divi,
			bricks:    !!fromCfg.bricks,
			elementor: !!fromCfg.elementor,
			gutenberg: fromCfg.gutenberg !== false,
		};
	}, []);

	const items = useMemo(
		() => ALL_FEATURES.filter((f) => !f.builder || builderFlags[f.builder]),
		[builderFlags]
	);

	const pricingUrl =
		typeof dcsCF7Styler !== 'undefined' && dcsCF7Styler.pricing_url
			? dcsCF7Styler.pricing_url
			: '';

	return (
		<div className="cf7m-card cf7m-card--flush">
			<div className="cf7m-feat-group">
				{items.map((f) => (
					<FeatureRow
						key={f.id}
						feature={f}
						enabled={!!features[f.id]}
						isPro={isPro}
						saving={saving}
						onToggle={onToggle}
						pricingUrl={pricingUrl}
					/>
				))}
			</div>
		</div>
	);
}

function FeatureRow({ feature, enabled, isPro, saving, onToggle, pricingUrl }) {
	const locked = feature.isPro && !isPro;

	return (
		<div className="cf7m-feat-row" data-enabled={locked ? 'locked' : enabled ? 'true' : 'false'}>
			{locked ? (
				<a
					href={pricingUrl || '#'}
					target={pricingUrl ? '_blank' : undefined}
					rel={pricingUrl ? 'noopener noreferrer' : undefined}
					className="cf7m-feat-row__upgrade"
					title={__('Upgrade to Pro', 'cf7-styler-for-divi')}
				>
					{__('Pro', 'cf7-styler-for-divi')}
				</a>
			) : (
				<Toggle
					checked={!!enabled}
					onChange={(v) => onToggle(feature.id, v)}
					disabled={saving}
				/>
			)}
			<div className="cf7m-feat-row__text">
				<span className="cf7m-feat-row__name">{feature.name}</span>
				<span className="cf7m-feat-row__desc">{feature.desc}</span>
			</div>
			{feature.info && (
				<a
					href={feature.info}
					target="_blank"
					rel="noopener noreferrer"
					className="cf7m-feat-row__info"
					aria-label={__('Learn more', 'cf7-styler-for-divi')}
					title={__('Learn more', 'cf7-styler-for-divi')}
				>
					<InformationCircleIcon aria-hidden="true" />
				</a>
			)}
		</div>
	);
}
