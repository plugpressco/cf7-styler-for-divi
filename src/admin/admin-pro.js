/**
 * Unified Pro registrations. Loaded after admin.js so window.cf7mProPages /
 * cf7mProWidgets are populated before the free shells look them up.
 *
 * @package CF7_Mate
 */

import LicensePage from './pages/LicensePage';
import { ResponsesPage } from './pages/ResponsesPage';
import { AnalyticsPage } from './pages/AnalyticsPage';
import { ResponsesOverviewWidget } from './components/ResponsesOverviewWidget';

window.cf7mProPages = window.cf7mProPages || {};
window.cf7mProPages.license = LicensePage;
window.cf7mProPages.responses = ResponsesPage;
window.cf7mProPages.analytics = AnalyticsPage;

window.cf7mProWidgets = window.cf7mProWidgets || {};
window.cf7mProWidgets.responsesOverview = ResponsesOverviewWidget;
