/**
 * Settings app shell: header, dashboard overview, settings tabs, toast, modals.
 *
 * @package CF7_Mate
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

import { Header } from './components/Header';
import { Toast } from './components/Toast';
import { SettingsPage } from './pages/SettingsPage';

export function SettingsApp() {
	const [features, setFeatures] = useState({});
	const [loading, setLoading] = useState(true);
	const [loadError, setLoadError] = useState('');
	const [saving, setSaving] = useState(false);
	const [toast, setToast] = useState(null);

	useEffect(() => {
		loadFeatures();
	}, []);

	const loadFeatures = async () => {
		setLoading(true);
		setLoadError('');
		try {
			const response = await apiFetch({
				path: '/cf7-styler/v1/settings/features',
			});
			setFeatures(response.features);
		} catch (error) {
			setLoadError(error?.message || __('Could not load your settings.', 'cf7-styler-for-divi'));
		} finally {
			setLoading(false);
		}
	};

	const persistFeatures = async (newFeatures) => {
		const previous = features;
		// Update the control immediately; confirm only after the server saves.
		setFeatures(newFeatures);
		setSaving(true);
		setToast(null);
		try {
			await apiFetch({
				path: '/cf7-styler/v1/settings/features',
				method: 'POST',
				data: { features: newFeatures },
			});
			setToast({ message: __('Settings saved.', 'cf7-styler-for-divi'), type: 'success' });
		} catch (error) {
			console.error('Error saving features:', error);
			setFeatures(previous);
			setToast({
				message: __('Could not save — change reverted', 'cf7-styler-for-divi'),
				type: 'error',
			});
		} finally {
			setSaving(false);
		}
	};

	const handleToggle = (featureId, enabled) =>
		persistFeatures({ ...features, [featureId]: enabled });

	const handleBulkToggle = (updates) =>
		persistFeatures({ ...features, ...updates });

	const responsesUrl =
		typeof dcsCF7Styler !== 'undefined' && dcsCF7Styler.responses_url
			? dcsCF7Styler.responses_url
			: 'admin.php?page=cf7-mate-responses';

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

	if (loadError) {
        return <div className="cf7m-wrap"><Header /><main className="cf7m-content"><div className="cf7m-disabled-state" role="alert"><h1>{__('Could not load settings', 'cf7-styler-for-divi')}</h1><p>{loadError}</p><button className="cf7m-button" type="button" onClick={loadFeatures}>{__('Try again', 'cf7-styler-for-divi')}</button></div></main></div>;
    }

	return (
		<div className="cf7m-wrap">
			<Header />
			<SettingsPage
				features={features}
				onToggle={handleToggle}
				onBulkToggle={handleBulkToggle}
				saving={saving}
				responsesUrl={responsesUrl}
			/>
			{toast && (
				<Toast
					message={toast.message}
					type={toast.type}
					onClose={() => setToast(null)}
				/>
			)}
		</div>
	);
}
