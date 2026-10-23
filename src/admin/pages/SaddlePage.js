import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { SaddleIntro } from '../../components/SaddleIntro';

export function SaddlePage() {
	const config = (typeof dcsCF7Styler !== 'undefined' && dcsCF7Styler.saddle) || {};
	const [copyStatus, setCopyStatus] = useState('');
	const prompt = __('Create a contact form called Project enquiry with labelled name, email, project type and message fields. Make name and email required. Use an enabled CF7 Mate style preset and give me the shortcode. Keep it off my live pages until I review it.', 'cf7-styler-for-divi');
	const copy = async () => {
		try { await navigator.clipboard.writeText(prompt); setCopyStatus(__('Prompt copied. Paste it into your connected AI app.', 'cf7-styler-for-divi')); }
		catch (error) { setCopyStatus(__('Select the prompt below and copy it manually.', 'cf7-styler-for-divi')); }
	};
	return <div className="cf7m-saddle cf7m-saddle-page">
		<section className="cf7m-saddle-card"><SaddleIntro config={config} /><p className="cf7m-saddle-note">{__('Optional companion plugins. CF7 Mate works without either one, and this page never installs, activates or connects them for you.', 'cf7-styler-for-divi')}</p></section>
		<section className="cf7m-saddle-card"><h2>{__('Quick MCP setup', 'cf7-styler-for-divi')}</h2><p>{__('MCP is the connection that lets your AI app use tools on your website.', 'cf7-styler-for-divi')}</p>
			<ol className="cf7m-saddle-steps">
				<li><strong>{__('Install Saddle and connect your app', 'cf7-styler-for-divi')}</strong><p>{__('In Saddle → Agent setup, choose your AI app and follow its generated connection instructions. Sign in to your site when prompted.', 'cf7-styler-for-divi')}</p></li>
				<li><strong>{__('Allow the form tools', 'cf7-styler-for-divi')}</strong><p>{__('Enable the CF7 Mate integration in Saddle’s settings. Give your app Edit content access when you want it to create forms. Saddle starts read-only; existing-form changes require confirmation.', 'cf7-styler-for-divi')}</p></li>
				<li><strong>{__('Describe your form, then review it', 'cf7-styler-for-divi')}</strong><p>{__('Ask for fields, labels, required answers and a style. Review the form and its Mail tab in Contact Form 7 before adding the shortcode to a live page.', 'cf7-styler-for-divi')}</p></li>
			</ol>
			<div className="cf7m-saddle-actions"><a className="cf7m-button" href={config.setup_url} target="_blank" rel="noopener noreferrer">{config.active ? __('Connect an AI app', 'cf7-styler-for-divi') : __('Get Saddle', 'cf7-styler-for-divi')}</a><a href={config.docs_url} target="_blank" rel="noopener noreferrer">{__('Connection guide', 'cf7-styler-for-divi')}</a></div>
			{!config.abilities_available && <p className="cf7m-saddle-note" role="note">{__('The form tools need the WordPress Abilities API, included in WordPress 6.9 and later. Your existing CF7 Mate features still work on this site.', 'cf7-styler-for-divi')}</p>}
		</section>
		<section className="cf7m-saddle-card"><div className="cf7m-saddle-card-heading"><h2>{__('Try this with your agent', 'cf7-styler-for-divi')}</h2><button type="button" className="cf7m-button" onClick={copy}>{__('Copy prompt', 'cf7-styler-for-divi')}</button></div><blockquote className="cf7m-saddle-prompt">{prompt}</blockquote>{copyStatus && <p className="cf7m-saddle-note" role="status">{copyStatus}</p>}<p className="cf7m-saddle-note">{__('Your agent uses its own AI account. You do not need to add a provider key to CF7 Mate for Saddle. Its form tools do not read stored submissions or API keys.', 'cf7-styler-for-divi')}</p></section>
		<section className="cf7m-saddle-card"><div className="cf7m-saddle-card-heading"><h2>{__('Saddle Analytics', 'cf7-styler-for-divi')}</h2><span className="cf7m-saddle-badge">{config.analytics_active ? __('Active', 'cf7-styler-for-divi') : __('Optional', 'cf7-styler-for-divi')}</span></div><p>{__('See visitors, popular pages, traffic sources and referrals from AI apps. Reports stay in your WordPress database. Connect it through Saddle to ask questions like “Which pages get visits from ChatGPT?”', 'cf7-styler-for-divi')}</p><p className="cf7m-saddle-note">{__('Site traffic reports complement CF7 Mate’s form views and saved-submission counts. Saddle Analytics requires Saddle, WordPress 6.9+ and PHP 8.1+.', 'cf7-styler-for-divi')}</p><a className="cf7m-button" href={config.analytics_url} target="_blank" rel="noopener noreferrer">{config.analytics_active ? __('Open Saddle Analytics', 'cf7-styler-for-divi') : __('Get Saddle Analytics', 'cf7-styler-for-divi')}</a></section>
		<section className="cf7m-saddle-card"><h2>{__('Help and support', 'cf7-styler-for-divi')}</h2><div className="cf7m-saddle-support"><a href="https://wordpress.org/support/plugin/cf7-styler-for-divi/" target="_blank" rel="noopener noreferrer">{__('CF7 Mate support', 'cf7-styler-for-divi')}</a><a href={config.docs_url} target="_blank" rel="noopener noreferrer">{__('Saddle documentation', 'cf7-styler-for-divi')}</a><a href={config.support_url} target="_blank" rel="noopener noreferrer">{__('Saddle connection support', 'cf7-styler-for-divi')}</a></div></section>
	</div>;
}
