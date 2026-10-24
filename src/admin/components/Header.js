/** Shared navigation for every CF7 Mate admin screen. */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ArrowUpRightIcon } from '@heroicons/react/20/solid';
import { NavTabs, Menu, MenuItem, MenuToggle, MenuSeparator } from './ui';
import { getConfig } from '../utils/config';
import { getDashTabFromHash } from '../utils/routing';
import { setSoundOn, soundOn } from '../utils/sound';

export function Header() {
	const config = getConfig();
	const [tab, setTab] = useState(getDashTabFromHash());
	const [sound, setSound] = useState(soundOn());
	useEffect(() => {
		const update = () => setTab(getDashTabFromHash());
		window.addEventListener('hashchange', update);
		return () => window.removeEventListener('hashchange', update);
	}, []);
	const dash = config.dash_url || 'admin.php?page=cf7-mate';
	const active = config.app === 'responses' ? 'responses' : config.app === 'analytics' ? 'analytics' : tab;
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
	].filter((item) => item.href);
	return (
		// z-20: the blur makes a stacking context; keep the Help menu above the page.
		<header className="relative z-20 border-b border-border bg-card/75 backdrop-blur-md">
			<div className="flex flex-wrap items-center gap-x-[18px] px-5">
				<a href={`${dash}#/features`} className="group flex h-14 items-center gap-2.5 text-base font-semibold tracking-[-0.3px] text-foreground hover:text-foreground">
					{config.pluginUrl && (
						<img
							src={`${config.pluginUrl}assets/images/cf7-mate-mark.png`}
							alt=""
							width="28"
							height="28"
							className="h-7 w-7 rounded-[7px] shadow-box transition-transform duration-300 ease-spring group-hover:-rotate-6 group-hover:scale-105"
						/>
					)}
					<span>CF7&nbsp;<span className="text-primary">Mate</span></span>
				</a>
				<NavTabs
					label={__('CF7 Mate navigation', 'cf7-styler-for-divi')}
					items={items}
					active={active}
					className="max-[900px]:order-3 max-[900px]:w-full max-[900px]:pb-2.5"
				/>
				<div className="ms-auto flex h-14 items-center">
					<Menu label={__('Help', 'cf7-styler-for-divi')}>
						{help.map((item) => (
							<MenuItem key={item.label} href={item.href} target="_blank" rel="noopener noreferrer">
								{item.label}
								<ArrowUpRightIcon aria-hidden="true" />
							</MenuItem>
						))}
						{help.length > 0 && <MenuSeparator />}
						<MenuItem href={`${dash}&cf7m_setup=1#/features`}>{__('Run setup again', 'cf7-styler-for-divi')}</MenuItem>
						<MenuToggle
							label={__('Sounds', 'cf7-styler-for-divi')}
							checked={sound}
							onText={__('On', 'cf7-styler-for-divi')}
							offText={__('Off', 'cf7-styler-for-divi')}
							onChange={(on) => {
								setSoundOn(on);
								setSound(on);
							}}
						/>
					</Menu>
				</div>
			</div>
		</header>
	);
}
