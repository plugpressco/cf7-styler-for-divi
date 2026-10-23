import { __ } from '@wordpress/i18n';
import { SparklesIcon, ChartBarIcon, ArrowUpRightIcon } from '@heroicons/react/24/outline';

export function SaddleIntro({ config = {}, compact = false }) {
	return <div className="cf7m-saddle">
		<div className="cf7m-saddle-intro">
			<span className="cf7m-saddle-icon" aria-hidden="true"><SparklesIcon /></span>
			<div><h3>{__('Your AI, connected to your website', 'cf7-styler-for-divi')}</h3>
				<p>{__('Saddle connects Claude, ChatGPT, Codex, Cursor and other MCP-capable agents to WordPress. Ask your agent to create a Contact Form 7 form, add CF7 Mate styling, and place it on a page.', 'cf7-styler-for-divi')}</p>
				<a className="cf7m-saddle-link" href={config.setup_url || 'https://wordpress.org/plugins/saddle/'} target="_blank" rel="noopener noreferrer">{config.active ? __('Open Saddle agent setup', 'cf7-styler-for-divi') : __('Get Saddle', 'cf7-styler-for-divi')}<ArrowUpRightIcon aria-hidden="true" /></a>
			</div>
		</div>
		{compact ? <>
			<div className="cf7m-saddle-mini"><ChartBarIcon aria-hidden="true" /><div><strong>{__('Want site and AI traffic reports too?', 'cf7-styler-for-divi')}</strong><p>{__('Saddle Analytics keeps visitor reports on your site and lets your agent answer questions about AI referrals.', 'cf7-styler-for-divi')}</p><a href={config.analytics_url || 'https://wordpress.org/plugins/saddle-analytics/'} target="_blank" rel="noopener noreferrer">{__('Explore Saddle Analytics', 'cf7-styler-for-divi')}</a></div></div>
			<p className="cf7m-saddle-note">{__('Both are optional. Nothing is installed or connected by this guide. CF7 Mate works on its own.', 'cf7-styler-for-divi')}</p>
			<a className="cf7m-saddle-link" href={config.page_url || 'admin.php?page=cf7-mate-saddle'} target="_blank" rel="noopener noreferrer">{__('Quick MCP setup, examples and support', 'cf7-styler-for-divi')}<ArrowUpRightIcon aria-hidden="true" /></a>
		</> : null}
	</div>;
}
