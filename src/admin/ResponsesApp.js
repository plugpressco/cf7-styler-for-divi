/**
 * Responses app shell: header + Responses page with optional feature enablement.
 *
 * @package CF7_Mate
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

import { Header } from './components/Header';
import { ResponsesPage } from './pages/ResponsesPage';

function getEntryIdFromHash() {
	const hash = (window.location.hash || '').replace(/^#\/?/, '');
	const m = hash.match(/^(?:responses|entries)\/(\d+)$/);
	return m ? parseInt(m[1], 10) : null;
}

export function ResponsesApp() {
	const [features, setFeatures] = useState({ database_entries: true });
	const [loading, setLoading] = useState(true);
	const [entryId, setEntryId] = useState(getEntryIdFromHash());

	useEffect(() => {
		const onHashChange = () => setEntryId(getEntryIdFromHash());
		window.addEventListener('hashchange', onHashChange);
		return () => window.removeEventListener('hashchange', onHashChange);
	}, []);

	useEffect(() => {
		(async () => {
			try {
				const response = await apiFetch({
					path: '/cf7-styler/v1/settings/features',
				});
				setFeatures(response.features);
			} catch (error) {
				console.error('Error loading features:', error);
			} finally {
				setLoading(false);
			}
		})();
	}, []);

	const dashUrl =
		typeof dcsCF7Styler !== 'undefined' && dcsCF7Styler.dash_url
			? dcsCF7Styler.dash_url
			: 'admin.php?page=cf7-mate';

	const showResponses = !!features.database_entries;

	if (loading) {
		return (
			<div className="cf7m-wrap">
				<Header />
				<div className="cf7m-resp__loading">
					<Spinner />
					<span>{__('Loading…', 'cf7-styler-for-divi')}</span>
				</div>
			</div>
		);
	}

	return (
		<div className="cf7m-wrap">
			<Header />
			<div className="cf7m-responses">
				{showResponses ? (
					<ResponsesPage
						entryId={entryId}
						onBack={() => {
							window.location.href = dashUrl;
						}}
					/>
				) : (
                    <main className="cf7m-content"><div className="cf7m-disabled-state"><h1>{__('Responses', 'cf7-styler-for-divi')}</h1><p>{__('Turn on response storage to keep and manage submissions in WordPress.', 'cf7-styler-for-divi')}</p><a className="cf7m-button" href={(typeof dcsCF7Styler !== 'undefined' ? dcsCF7Styler.dash_url : 'admin.php?page=cf7-mate') + '#/features'}>{__('Manage features', 'cf7-styler-for-divi')}</a></div></main>
				)}
			</div>
		</div>
	);
}
