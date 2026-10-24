/**
 * Responses app shell: header plus the Responses page, or a note when storage is off.
 *
 * @package CF7_Mate
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

import { AppFrame, FeatureOff, LoadingScreen } from './components/Layout';
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
				const response = await apiFetch({ path: '/cf7-styler/v1/settings/features' });
				setFeatures(response.features);
			} catch (error) {
				// Fall back to showing the page; its own requests report errors.
			} finally {
				setLoading(false);
			}
		})();
	}, []);

	return (
		<AppFrame>
			{loading && <LoadingScreen />}
			{!loading && !!features.database_entries && <ResponsesPage entryId={entryId} />}
			{!loading && !features.database_entries && (
				<FeatureOff title={__('Responses', 'cf7-styler-for-divi')} message={__('Turn on Form responses to keep submissions in WordPress.', 'cf7-styler-for-divi')} />
			)}
		</AppFrame>
	);
}
