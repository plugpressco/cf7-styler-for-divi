import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Main } from '../components/Layout';
import { PageHeader } from '../components/ui';
import { FeaturesSection } from '../components/FeaturesSection';
import { AISettingsPage } from './AISettingsPage';
import { WebhookPage } from './WebhookPage';
import { SaddlePage } from './SaddlePage';
import { getDashTabFromHash } from '../utils/routing';

export function SettingsPage({ features, onToggle, saving, onSetupSaved }) {
	const [active, setActive] = useState(getDashTabFromHash());
	useEffect(() => {
		const update = () => setActive(getDashTabFromHash());
		window.addEventListener('hashchange', update);
		return () => window.removeEventListener('hashchange', update);
	}, []);

	if (active === 'saddle') return <SaddlePage />;

	if (active === 'tools') {
		return (
			<Main narrow>
				<PageHeader title={__('Tools', 'cf7-styler-for-divi')} />
				<AISettingsPage features={features} onToggle={onToggle} saving={saving} />
				<WebhookPage features={features} onToggle={onToggle} saving={saving} />
			</Main>
		);
	}

	return (
		<Main>
			<FeaturesSection features={features} onToggle={onToggle} saving={saving} onSetupSaved={onSetupSaved} />
		</Main>
	);
}
