/**
 * Analytics app shell: header plus the Analytics page, or a note when the feature is off.
 *
 * @package CF7_Mate
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

import { AppFrame, FeatureOff, LoadingScreen } from './components/Layout';
import { AnalyticsPage } from './pages/AnalyticsPage';

export function AnalyticsApp() {
	const [features, setFeatures] = useState({ analytics: true });
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		(async () => {
			try {
				const response = await apiFetch({ path: '/cf7-styler/v1/settings/features' });
				setFeatures(response.features);
			} catch (error) {
				// Fall back to showing the page; its own request reports errors.
			} finally {
				setLoading(false);
			}
		})();
	}, []);

	return (
		<AppFrame>
			{loading && <LoadingScreen />}
			{!loading && !!features.analytics && <AnalyticsPage />}
			{!loading && !features.analytics && (
				<FeatureOff title={__('Analytics', 'cf7-styler-for-divi')} message={__('Turn on Form analytics to count form views.', 'cf7-styler-for-divi')} />
			)}
		</AppFrame>
	);
}
