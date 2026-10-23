/**
 * Analytics app shell: header + Analytics page with optional feature enablement.
 *
 * @package CF7_Mate
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

import { Header } from './components/Header';
import { AnalyticsPage } from './pages/AnalyticsPage';

export function AnalyticsApp() {
	const [features, setFeatures] = useState({ analytics: true });
	const [loading, setLoading] = useState(true);

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

	const showAnalytics = !!features.analytics;

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
				{showAnalytics ? (
					<AnalyticsPage />
				) : (
                    <main className="cf7m-content"><div className="cf7m-disabled-state"><h1>{__('Analytics', 'cf7-styler-for-divi')}</h1><p>{__('Turn on analytics to understand how visitors use your forms.', 'cf7-styler-for-divi')}</p><a className="cf7m-button" href={(typeof dcsCF7Styler !== 'undefined' ? dcsCF7Styler.dash_url : 'admin.php?page=cf7-mate') + '#/features'}>{__('Manage features', 'cf7-styler-for-divi')}</a></div></main>
				)}
			</div>
		</div>
	);
}
