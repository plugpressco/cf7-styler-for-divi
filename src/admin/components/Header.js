/** Shared navigation for every CF7 Mate admin screen. */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ChevronDownIcon, ArrowUpRightIcon } from '@heroicons/react/24/outline';
import CF7MateLogo from '../../components/CF7MateLogo';
import { getDashTabFromHash } from '../utils/routing';

export function Header() {
	const config = typeof dcsCF7Styler !== 'undefined' ? dcsCF7Styler : {};
	const [tab, setTab] = useState(getDashTabFromHash());
	useEffect(() => {
		const update = () => setTab(getDashTabFromHash());
		window.addEventListener('hashchange', update);
		return () => window.removeEventListener('hashchange', update);
	}, []);
	const dash = config.dash_url || 'admin.php?page=cf7-mate';
	const active = config.app === 'responses' ? 'responses' : config.app === 'analytics' ? 'analytics' : tab;
	const name = 'CF7 Mate';
	const items = [
		{ id: 'features', label: __('Features', 'cf7-styler-for-divi'), href: `${dash}#/features` },
		{ id: 'responses', label: __('Responses', 'cf7-styler-for-divi'), href: config.responses_url || 'admin.php?page=cf7-mate-responses' },
		{ id: 'analytics', label: __('Analytics', 'cf7-styler-for-divi'), href: config.analytics_url || 'admin.php?page=cf7-mate-analytics' },
		{ id: 'tools', label: __('Tools', 'cf7-styler-for-divi'), href: `${dash}#/tools` },
		{ id: 'saddle', label: __('Saddle', 'cf7-styler-for-divi'), href: config.saddle_url || 'admin.php?page=cf7-mate-saddle' },
	];
	const help = [
		{ label: __('Documentation', 'cf7-styler-for-divi'), href: config.docs_url },
		{ label: __('Support', 'cf7-styler-for-divi'), href: config.support_url },
		{ label: __('Community', 'cf7-styler-for-divi'), href: config.community_url },
	].filter(item => item.href);
	return (
		<header className="cf7m-header">
			<div className="cf7m-header__inner">
				<a href={dash} className="cf7m-header__brand">
					<CF7MateLogo width={28} height={28} />
					<span>{name}</span>
				</a>
				<nav className="cf7m-header__nav" aria-label={__('CF7 Mate navigation', 'cf7-styler-for-divi')}>
					{items.map(item => <a key={item.id} href={item.href} className={`cf7m-header__nav-item${active === item.id ? ' is-active' : ''}`} aria-current={active === item.id ? 'page' : undefined}>{item.label}</a>)}
				</nav>
				<div className="cf7m-header__actions">
					{help.length > 0 && <details className="cf7m-help"><summary>{__('Help', 'cf7-styler-for-divi')}<ChevronDownIcon aria-hidden="true" /></summary><div className="cf7m-help__menu">{help.map(item => <a key={item.label} href={item.href} target="_blank" rel="noopener noreferrer">{item.label}<ArrowUpRightIcon aria-hidden="true" /></a>)}</div></details>}
					<a className="cf7m-button" href={config.cf7_admin_url || 'admin.php?page=wpcf7'}>{__('Contact forms', 'cf7-styler-for-divi')}<ArrowUpRightIcon aria-hidden="true" /></a>
				</div>
			</div>
		</header>
	);
}
