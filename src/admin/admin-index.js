/**
 * Unified admin entry. Picks the right app shell based on dcsCF7Styler.app
 * (set in PHP per submenu page). Replaces the per-page *-index.js files.
 *
 * @package CF7_Mate
 */

import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

import './style.scss';
import { SettingsApp } from './SettingsApp';
import { ResponsesApp } from './ResponsesApp';
import { AnalyticsApp } from './AnalyticsApp';

const APPS = {
	settings: SettingsApp,
	responses: ResponsesApp,
	analytics: AnalyticsApp,
};

domReady(() => {
	const rootElement = document.getElementById('cf7-mate-app-root');
	if (!rootElement) return;
	const appName =
		(typeof dcsCF7Styler !== 'undefined' && dcsCF7Styler.app) || 'settings';
	const App = APPS[appName] || SettingsApp;
	render(<App />, rootElement);
});
