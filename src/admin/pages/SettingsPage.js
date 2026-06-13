import { useEffect, useState, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ArrowUpRightIcon, CheckIcon } from '@heroicons/react/24/outline';

import { FeaturesSection } from '../components/FeaturesSection';
import { AISettingsPage } from './AISettingsPage';
import { getDashTabFromHash } from '../utils/routing';

const cfg = (key, fallback = '') => {
	if (typeof dcsCF7Styler === 'undefined') return fallback;
	return dcsCF7Styler[key] || fallback;
};

const getProPage = (name) => window.cf7mProPages && window.cf7mProPages[name];

export function SettingsPage({
	features,
	isPro,
	onToggle,
	onBulkToggle,
	saving,
}) {
	const [active, setActive] = useState(getDashTabFromHash());

	useEffect(() => {
		const onHash = () => setActive(getDashTabFromHash());
		window.addEventListener('hashchange', onHash);
		return () => window.removeEventListener('hashchange', onHash);
	}, []);

	const navItems = useMemo(() => {
		const items = [
			{ id: 'features', label: __('Features', 'cf7-styler-for-divi') },
			{ id: 'tools', label: __('Tools', 'cf7-styler-for-divi') },
		];
		if (isPro) {
			items.push({
				id: 'license',
				label: __('License', 'cf7-styler-for-divi'),
			});
		}
		return items;
	}, [isPro]);

	const navigate = (id) => {
		const hash = `#/${id}`;
		if (window.location.hash !== hash) {
			window.history.pushState(null, '', hash);
		}
		setActive(id);
	};

	return (
		<>
			<div className="cf7m-page-layout">
				<div className="cf7m-page-layout__main">
					<div className="cf7m-page-card">
						<nav
							className="cf7m-tab-nav"
							aria-label={__(
								'CF7 Mate sections',
								'cf7-styler-for-divi',
							)}
						>
							{navItems.map((item) => {
								const isActive = active === item.id;
								if (item.href) {
									return (
										<a
											key={item.id}
											href={item.href}
											className="cf7m-tab-nav__item"
										>
											{item.label}
										</a>
									);
								}
								return (
									<button
										key={item.id}
										type="button"
										className={`cf7m-tab-nav__item${isActive ? ' is-active' : ''}`}
										onClick={() => navigate(item.id)}
										aria-current={
											isActive ? 'page' : undefined
										}
									>
										{item.label}
									</button>
								);
							})}
						</nav>

						<div className="cf7m-tab-content">
							{active === 'features' && (
								<FeaturesSection
									features={features}
									isPro={isPro}
									onToggle={onToggle}
									onBulkToggle={onBulkToggle}
									saving={saving}
								/>
							)}
							{active === 'tools' && (
								<AISettingsPage
									features={features}
									isPro={isPro}
									onToggle={onToggle}
									saving={saving}
								/>
							)}
							{active === 'license' && isPro && <LicenseTab />}
						</div>
					</div>
				</div>

				{!isPro && (
					<aside className="cf7m-page-sidebar">
						<UpgradeCard />
						<ServiceCard />
						<ProductsCard />
						<ReviewCard />
					</aside>
				)}
			</div>
		</>
	);
}

// ===== Sidebar cards (free plan only) =====

// Launch discount — kept in sync with the onboarding flow (StepFeatures.jsx).
const DISCOUNT_CODE = 'NEW2026';

const PRO_FEATURES = [
	__('Conditional Logic', 'cf7-styler-for-divi'),
	__('Multi-Step Forms', 'cf7-styler-for-divi'),
	__('Form Entries & Export', 'cf7-styler-for-divi'),
	__('Form Scheduling', 'cf7-styler-for-divi'),
	__('Email Routing', 'cf7-styler-for-divi'),
	__('Analytics', 'cf7-styler-for-divi'),
];

function UpgradeCard() {
	const pricingUrl = cfg('pricing_url', '');
	const [copied, setCopied] = useState(false);
	if (!pricingUrl) return null;

	const sep = pricingUrl.includes('?') ? '&' : '?';
	const proUrl = `${pricingUrl}${sep}coupon=${DISCOUNT_CODE}`;
	const lifetimeUrl = `${pricingUrl}${sep}plan=lifetime&coupon=${DISCOUNT_CODE}`;

	const copyCode = () => {
		if (navigator.clipboard) {
			navigator.clipboard.writeText(DISCOUNT_CODE).then(() => {
				setCopied(true);
				setTimeout(() => setCopied(false), 2000);
			});
		}
	};

	return (
		<div className="cf7m-sidebar-card cf7m-upgrade-card">
			<div className="cf7m-upgrade-card__head">
				<span className="cf7m-upgrade-card__badge">
					{__('Pro', 'cf7-styler-for-divi')}
				</span>
				<h3 className="cf7m-upgrade-card__title">
					{__(
						'Unlock the full power of CF7 Mate',
						'cf7-styler-for-divi',
					)}
				</h3>
			</div>

			<ul
				className="cf7m-upgrade-card__features"
				aria-label={__('Pro features', 'cf7-styler-for-divi')}
			>
				{PRO_FEATURES.map((feat) => (
					<li key={feat} className="cf7m-upgrade-card__feature">
						<CheckIcon
							className="cf7m-upgrade-card__check"
							aria-hidden="true"
						/>
						{feat}
					</li>
				))}
			</ul>

			<p className="cf7m-upgrade-card__promo">
				{__('Save 50% on the lifetime deal with code', 'cf7-styler-for-divi')}{' '}
				<button
					type="button"
					onClick={copyCode}
					className="cf7m-upgrade-card__code"
					title={__('Copy code', 'cf7-styler-for-divi')}
				>
					{DISCOUNT_CODE}
					{copied && (
						<span className="cf7m-upgrade-card__copied">
							{__('Copied!', 'cf7-styler-for-divi')}
						</span>
					)}
				</button>
			</p>

			<a
				href={proUrl}
				target="_blank"
				rel="noopener noreferrer"
				className="cf7m-sidebar-card__cta"
			>
				{__('Get CF7 Mate Pro', 'cf7-styler-for-divi')}
				<ArrowUpRightIcon aria-hidden="true" />
			</a>

			<a
				href={lifetimeUrl}
				target="_blank"
				rel="noopener noreferrer"
				className="cf7m-upgrade-card__lifetime"
			>
				{__('Lifetime deal — pay once, 50% off', 'cf7-styler-for-divi')}
			</a>
		</div>
	);
}

function ServiceCard() {
	return (
		<div className="cf7m-sidebar-card cf7m-service-card">
			<span className="cf7m-service-card__eyebrow">
				{__('Quick service', 'cf7-styler-for-divi')}
			</span>
			<h3 className="cf7m-sidebar-card__title">
				{__('Need custom work done?', 'cf7-styler-for-divi')}
			</h3>
			<p className="cf7m-service-card__desc">
				{__(
					'dotyard is our lean product studio — custom WordPress plugins and SaaS MVPs. Fixed scope, fixed price, one commission at a time.',
					'cf7-styler-for-divi',
				)}
			</p>
			<a
				href="https://dotyard.co/work?utm_source=cf7mate&utm_medium=sidebar&utm_campaign=dotyard"
				target="_blank"
				rel="noopener noreferrer"
				className="cf7m-sidebar-card__cta"
			>
				{__('Start a project', 'cf7-styler-for-divi')}
				<ArrowUpRightIcon aria-hidden="true" />
			</a>
		</div>
	);
}

const PRODUCTS = [
	{
		name: 'DiviPeople',
		logo: 'assets/images/products/divi-people.svg',
		desc: __('Premium plugins for Divi.', 'cf7-styler-for-divi'),
		tag: 'Divi',
		url: 'https://divipeople.com/?utm_source=cf7mate&utm_medium=sidebar&utm_campaign=products',
	},
	{
		name: 'DiviTorque',
		logo: 'assets/images/products/divi-torque.svg',
		desc: __('Toolkit for Divi designers & agencies.', 'cf7-styler-for-divi'),
		tag: 'Divi',
		url: 'https://divitorque.com/?utm_source=cf7mate&utm_medium=sidebar&utm_campaign=products',
	},
];

function ProductsCard() {
	const pluginUrl = cfg('pluginUrl', '');
	return (
		<div className="cf7m-sidebar-card cf7m-sidebar-card--products">
			<div className="cf7m-sidebar-card__header">
				<h3 className="cf7m-sidebar-card__title">
					{__('More from PlugPress', 'cf7-styler-for-divi')}
				</h3>
				<a
					href="https://plugpress.co/?utm_source=cf7mate&utm_medium=sidebar&utm_campaign=products"
					target="_blank"
					rel="noopener noreferrer"
					className="cf7m-sidebar-card__see-all"
				>
					{__('See all', 'cf7-styler-for-divi')}
					<ArrowUpRightIcon aria-hidden="true" />
				</a>
			</div>
			<div className="cf7m-products-list">
				{PRODUCTS.map(({ name, logo, tag, desc, url }) => (
					<a
						key={name}
						href={url}
						target="_blank"
						rel="noopener noreferrer"
						className="cf7m-product-item"
					>
						<span className="cf7m-product-item__avatar" aria-hidden="true">
							<img
								src={pluginUrl + logo}
								alt=""
								width="30"
								height="30"
								loading="lazy"
							/>
						</span>
						<span className="cf7m-product-item__body">
							<span className="cf7m-product-item__head">
								<span className="cf7m-product-item__name">
									{name}
								</span>
								<span className="cf7m-product-item__tag">
									{tag}
								</span>
							</span>
							<span className="cf7m-product-item__desc">
								{desc}
							</span>
						</span>
					</a>
				))}
			</div>
		</div>
	);
}

function ReviewCard() {
	const reviewUrl = cfg(
		'review_url',
		'https://wordpress.org/support/plugin/cf7-styler-for-divi/reviews/#new-post',
	);

	return (
		<div className="cf7m-sidebar-card">
			<h3 className="cf7m-sidebar-card__title">
				{__('Write a review for CF7 Mate', 'cf7-styler-for-divi')}
			</h3>
			<p className="cf7m-sidebar-card__body">
				{__(
					'If you like CF7 Mate, please write a review on WordPress.org to help us spread the word. We really appreciate that!',
					'cf7-styler-for-divi',
				)}
			</p>
			<a
				href={reviewUrl}
				target="_blank"
				rel="noopener noreferrer"
				className="cf7m-sidebar-card__link"
			>
				{__('Write a review', 'cf7-styler-for-divi')}
				<ArrowUpRightIcon aria-hidden="true" />
			</a>
		</div>
	);
}

function LicenseTab() {
	const LicensePage = getProPage('license');
	if (LicensePage) return <LicensePage />;
	return (
		<div className="cf7m-dash__upsell">
			<p>
				{__(
					'License management is available in CF7 Mate Pro.',
					'cf7-styler-for-divi',
				)}
			</p>
		</div>
	);
}
