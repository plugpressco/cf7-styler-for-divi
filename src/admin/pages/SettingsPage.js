import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { CheckCircleIcon } from '@heroicons/react/24/outline';
import { FeaturesSection } from '../components/FeaturesSection';
import { AISettingsPage } from './AISettingsPage';
import { WebhookPage } from './WebhookPage';
import { SaddlePage } from './SaddlePage';
import { getDashTabFromHash } from '../utils/routing';

export function SettingsPage({ features, onToggle, saving }) {
	const [active, setActive] = useState(getDashTabFromHash());
	useEffect(() => {
		const update = () => setActive(getDashTabFromHash());
		window.addEventListener('hashchange', update);
		return () => window.removeEventListener('hashchange', update);
	}, []);
	const pages = {
		features: { title: __('Features', 'cf7-styler-for-divi'), description: __('Choose what your forms can do. Enable only the tools you need.', 'cf7-styler-for-divi') },
		tools: { title: __('Tools', 'cf7-styler-for-divi'), description: __('Connect your AI provider and send submissions to your other tools.', 'cf7-styler-for-divi') },
		saddle: { title: __('Saddle', 'cf7-styler-for-divi'), description: __('Let your AI assistant help build your next form.', 'cf7-styler-for-divi') },
	};
	const page = pages[active] || pages.features;
	return (
		<main className="cf7m-content">
			<div className="cf7m-page-heading"><div><h1>{page.title}</h1><p>{page.description}</p></div>{active === 'features' && <span className="cf7m-save-status" role="status"><CheckCircleIcon aria-hidden="true" />{saving ? __('Saving…', 'cf7-styler-for-divi') : __('Changes save automatically', 'cf7-styler-for-divi')}</span>}</div>
			{active === 'features' && <FeaturesSection features={features} onToggle={onToggle} saving={saving} />}
			{active === 'tools' && <div className="cf7m-settings-stack"><AISettingsPage features={features} onToggle={onToggle} saving={saving} /><WebhookPage features={features} onToggle={onToggle} saving={saving} /></div>}
			{active === 'saddle' && <SaddlePage />}
		</main>
	);
}
