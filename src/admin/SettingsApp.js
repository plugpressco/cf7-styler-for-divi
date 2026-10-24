/**
 * Settings app: Features, Tools and Saddle tabs with immediate saves.
 *
 * @package CF7_Mate
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

import { AppFrame, LoadingScreen, LoadError } from './components/Layout';
import { Toast } from './components/ui';
import { SettingsPage } from './pages/SettingsPage';
import { SetupDone } from './components/SetupCard';

export function SettingsApp() {
	const [features, setFeatures] = useState({});
	const [loading, setLoading] = useState(true);
	const [loadError, setLoadError] = useState('');
	const [saving, setSaving] = useState(false);
	const [toast, setToast] = useState(null);
	const [celebrate, setCelebrate] = useState(false);

	useEffect(() => {
		loadFeatures();
	}, []);

	const loadFeatures = async () => {
		setLoading(true);
		setLoadError('');
		try {
			const response = await apiFetch({ path: '/cf7-styler/v1/settings/features' });
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
			setToast({ message: __('Saved', 'cf7-styler-for-divi'), type: 'success' });
		} catch (error) {
			setFeatures(previous);
			setToast({ message: __('Could not save. Your change was undone.', 'cf7-styler-for-divi'), type: 'error' });
		} finally {
			setSaving(false);
		}
	};

	const handleToggle = (featureId, enabled) => persistFeatures({ ...features, [featureId]: enabled });

	// The setup card saves on its own; this only mirrors what the server stored.
	const handleSetupSaved = (saved) => {
		setFeatures((current) => ({ ...current, ...saved }));
		setToast(null);
		setCelebrate(true);
	};

	return (
		<AppFrame>
			{loading && <LoadingScreen />}
			{!loading && loadError && (
				<LoadError title={__('Could not load settings', 'cf7-styler-for-divi')} message={loadError} onRetry={loadFeatures} />
			)}
			{!loading && !loadError && (
				<SettingsPage features={features} onToggle={handleToggle} saving={saving} onSetupSaved={handleSetupSaved} />
			)}
			{toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
			{celebrate && <SetupDone onClose={() => setCelebrate(false)} />}
		</AppFrame>
	);
}
