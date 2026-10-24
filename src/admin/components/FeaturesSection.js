import { useEffect, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { Alert, Button, CheckTile, EmptyState, PageHeader, SearchInput } from './ui';
import { SetupCard } from './SetupCard';
import { getConfig } from '../utils/config';

/** Whether the setup card was asked for with ?cf7m_setup=1 (Help, Run setup again). */
function setupRequested() {
	try {
		return new URLSearchParams(window.location.search).get('cf7m_setup') === '1';
	} catch (error) {
		return false;
	}
}

export function FeaturesSection({ features, onToggle, saving, onSetupSaved }) {
	const config = getConfig();
	const [search, setSearch] = useState('');
	const [showSetup, setShowSetup] = useState(() => setupRequested() || !!(config.setup && config.setup.show));

	useEffect(() => {
		if (!setupRequested()) return;
		const url = new URL(window.location.href);
		url.searchParams.delete('cf7m_setup');
		window.history.replaceState(window.history.state, '', url.toString());
	}, []);

	// Builders sit after form features; metadata order is kept otherwise.
	const items = (config.feature_metadata || [])
		.filter((feature) => !feature.builder || (config.builders && config.builders[feature.builder]))
		.sort((a, b) => Number(!!a.builder) - Number(!!b.builder));
	const query = search.trim().toLocaleLowerCase();
	const visible = items.filter((feature) => `${feature.name} ${feature.desc}`.toLocaleLowerCase().includes(query));
	const enabled = items.filter((feature) => features[feature.id]).length;
	const notice = config.free_notice || {};

	return (
		<>
			{showSetup && (
				<SetupCard
					features={features}
					onDone={(saved) => {
						setShowSetup(false);
						if (saved) onSetupSaved(saved);
					}}
				/>
			)}
			{!showSetup && notice.show && (
				<Alert
					action={<a className="text-[13px] font-medium text-primary hover:underline" href={notice.dismiss_url}>{__('Dismiss', 'cf7-styler-for-divi')}</a>}
				>
					{__('Every CF7 Mate feature is now free. Responses, analytics, webhooks and save and continue stay off until you turn them on.', 'cf7-styler-for-divi')}
				</Alert>
			)}
			<PageHeader
				title={__('Features', 'cf7-styler-for-divi')}
				/* translators: %d: number of features that are on. */
				meta={<span className="text-[13px] text-muted-foreground">{sprintf(__('%d on', 'cf7-styler-for-divi'), enabled)}</span>}
				actions={<SearchInput label={__('Search features', 'cf7-styler-for-divi')} placeholder={__('Search', 'cf7-styler-for-divi')} value={search} onChange={setSearch} />}
			/>
			{visible.length > 0 ? (
				<div className="cf7m-stagger grid grid-cols-[repeat(auto-fill,minmax(210px,1fr))] gap-2">
					{visible.map((feature) => (
						<CheckTile
							key={feature.id}
							id={`cf7m-feature-${feature.id}`}
							label={feature.name}
							description={feature.desc}
							checked={!!features[feature.id]}
							disabled={saving}
							onChange={(value) => onToggle(feature.id, value)}
						/>
					))}
				</div>
			) : (
				<EmptyState
					title={__('No features match.', 'cf7-styler-for-divi')}
					action={<Button onClick={() => setSearch('')}>{__('Clear search', 'cf7-styler-for-divi')}</Button>}
				/>
			)}
			<p className="text-xs text-muted-foreground">{__('Changes save automatically.', 'cf7-styler-for-divi')}</p>
		</>
	);
}
