import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ArrowUpRightIcon } from '@heroicons/react/20/solid';
import { Main } from '../components/Layout';
import { Button, Card, CardFooter, CardHeader, CardTitle, PageHeader, buttonVariants } from '../components/ui';
import { getConfig } from '../utils/config';

/** Optional Saddle recommendation. Nothing here installs, activates or connects anything. */
export function SaddlePage() {
	const config = getConfig().saddle || {};
	const [copied, setCopied] = useState('');
	const prompt = __('Create a contact form called Project enquiry with name, email, project type and message. Make name and email required. Use a CF7 Mate style preset and give me the shortcode. Keep it off my live pages until I review it.', 'cf7-styler-for-divi');
	const steps = [
		__('Install Saddle and connect your AI app.', 'cf7-styler-for-divi'),
		__('Turn on the CF7 Mate tools in Saddle.', 'cf7-styler-for-divi'),
		__('Ask for a form, then review it in Contact Form 7.', 'cf7-styler-for-divi'),
	];
	const copy = async () => {
		try {
			await navigator.clipboard.writeText(prompt);
			setCopied('done');
		} catch (error) {
			setCopied('failed');
		}
	};
	return (
		<Main narrow>
			<PageHeader title={__('Saddle', 'cf7-styler-for-divi')} description={__('Optional. Let your AI app build forms on this site.', 'cf7-styler-for-divi')} />

			<Card aria-labelledby="cf7m-saddle-setup">
				<CardHeader>
					<CardTitle id="cf7m-saddle-setup">{__('Quick MCP setup', 'cf7-styler-for-divi')}</CardTitle>
				</CardHeader>
				<ol className="flex flex-col gap-2.5 px-[18px] py-4 text-[13px]">
					{steps.map((step, index) => (
						<li key={step} className="flex items-center gap-3">
							<span aria-hidden="true" className="inline-flex h-[26px] w-[26px] shrink-0 items-center justify-center rounded-full bg-sage text-xs font-semibold text-primary">{index + 1}</span>
							{step}
						</li>
					))}
				</ol>
				{!config.abilities_available && (
					<p className="px-[18px] pb-4 text-xs text-muted-foreground">{__('The form tools need WordPress 6.9 or later. Everything else in CF7 Mate works without them.', 'cf7-styler-for-divi')}</p>
				)}
				<CardFooter>
					<a className={buttonVariants({ variant: 'primary' })} href={config.setup_url} target="_blank" rel="noopener noreferrer">
						{config.active ? __('Connect an AI app', 'cf7-styler-for-divi') : __('Get Saddle', 'cf7-styler-for-divi')}
					</a>
					<a className="inline-flex items-center gap-1 text-[13px] font-medium text-primary hover:underline" href={config.docs_url} target="_blank" rel="noopener noreferrer">
						{__('Setup guide', 'cf7-styler-for-divi')}
						<ArrowUpRightIcon aria-hidden="true" className="h-3.5 w-3.5" />
					</a>
				</CardFooter>
			</Card>

			<Card aria-labelledby="cf7m-saddle-prompt">
				<CardHeader>
					<CardTitle id="cf7m-saddle-prompt">{__('Try this prompt', 'cf7-styler-for-divi')}</CardTitle>
					<Button className="min-w-[112px]" onClick={copy}>
						{copied === 'done' ? __('Copied', 'cf7-styler-for-divi') : __('Copy prompt', 'cf7-styler-for-divi')}
					</Button>
				</CardHeader>
				<p className="px-[18px] py-3.5 text-[13px] leading-relaxed">{prompt}</p>
				{copied === 'failed' && <p role="status" className="px-[18px] pb-3.5 text-xs text-muted-foreground">{__('Select the text above and copy it.', 'cf7-styler-for-divi')}</p>}
			</Card>

			<p className="text-[13px]">
				{__('Saddle Analytics shows visitors and AI referrals.', 'cf7-styler-for-divi')}{' '}
				<a className="font-medium text-primary hover:underline" href={config.analytics_url} target="_blank" rel="noopener noreferrer">
					{config.analytics_active ? __('Open Saddle Analytics', 'cf7-styler-for-divi') : __('Get Saddle Analytics', 'cf7-styler-for-divi')}
				</a>
			</p>
			<p className="text-xs text-muted-foreground">{__('Both are optional. CF7 Mate never installs or connects them.', 'cf7-styler-for-divi')}</p>
		</Main>
	);
}
