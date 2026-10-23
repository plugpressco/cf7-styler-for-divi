import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { MagnifyingGlassIcon, AdjustmentsHorizontalIcon } from '@heroicons/react/24/outline';
import { featureGroups as groups } from '../data/feature-groups';
import { Toggle } from './Toggle';



export function FeaturesSection({ features, onToggle, saving }) {
	const [search, setSearch] = useState('');
	const [filter, setFilter] = useState('all');
	const config = typeof dcsCF7Styler !== 'undefined' ? dcsCF7Styler : {};
	const items = (config.feature_metadata || []).filter(feature => !feature.builder || config.builders?.[feature.builder]);
	const query = search.trim().toLocaleLowerCase();
	const visible = items.filter(feature => `${feature.name} ${feature.desc}`.toLocaleLowerCase().includes(query) && (filter === 'all' || !!features[feature.id] === (filter === 'enabled')));
	const enabled = items.filter(feature => features[feature.id]).length;
	const filters = [{ id: 'all', label: __('All', 'cf7-styler-for-divi') }, { id: 'enabled', label: __('Enabled', 'cf7-styler-for-divi') }, { id: 'disabled', label: __('Disabled', 'cf7-styler-for-divi') }];
	return (
		<div className="cf7m-features">
			<div className="cf7m-feature-toolbar">
				<label className="cf7m-feature-search"><MagnifyingGlassIcon aria-hidden="true" /><span className="screen-reader-text">{__('Search features', 'cf7-styler-for-divi')}</span><input type="search" value={search} onChange={event => setSearch(event.target.value)} placeholder={__('Search features…', 'cf7-styler-for-divi')} /></label>
				<div className="cf7m-filter-tabs" aria-label={__('Filter features', 'cf7-styler-for-divi')}>{filters.map(item => <button key={item.id} type="button" aria-pressed={filter === item.id} onClick={() => setFilter(item.id)}>{item.label}</button>)}</div>
				<span className="cf7m-feature-count">{/* translators: %1$d enabled features, %2$d available features. */}{sprintf(__('%1$d of %2$d enabled', 'cf7-styler-for-divi'), enabled, items.length)}</span>
			</div>
			{groups.map(group => {
				const matches = visible.filter(feature => group.keys.includes(feature.id) || (group.id === 'integrations' && !groups.some(item => item.keys.includes(feature.id))));
				if (!matches.length) return null;
				const Icon = group.icon;
				return <section key={group.id} className="cf7m-feature-section" aria-labelledby={`cf7m-group-${group.id}`}><div className="cf7m-feature-section__heading"><Icon aria-hidden="true" /><h2 id={`cf7m-group-${group.id}`}>{group.title}</h2><span>{matches.length}</span></div><div className="cf7m-feature-grid">{matches.map(feature => <div key={feature.id} className="cf7m-feature-item"><div><label id={`cf7m-label-${feature.id}`} htmlFor={`cf7m-toggle-${feature.id}`}>{feature.name}</label><p>{feature.desc}</p></div><Toggle id={`cf7m-toggle-${feature.id}`} checked={!!features[feature.id]} onChange={value => onToggle(feature.id, value)} disabled={saving} label={feature.name} /></div>)}</div></section>;
			})}
			{!visible.length && <div className="cf7m-feature-empty"><AdjustmentsHorizontalIcon aria-hidden="true" /><h2>{__('No features found', 'cf7-styler-for-divi')}</h2><p>{__('Try another search or change your filter.', 'cf7-styler-for-divi')}</p><button type="button" className="cf7m-button" onClick={() => { setSearch(''); setFilter('all'); }}>{__('Clear filters', 'cf7-styler-for-divi')}</button></div>}
			<p className="cf7m-page-note">{__('These settings apply site-wide. Configure individual forms in Contact forms.', 'cf7-styler-for-divi')}</p>
		</div>
	);
}
