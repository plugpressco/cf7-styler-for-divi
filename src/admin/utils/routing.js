/**
 * Hash-based routing helpers for the Mate Dash app.
 * Tabs: features (default), tools, saddle. Retired hashes such as #/license open features.
 *
 * @package CF7_Mate
 */

const VALID_TABS = ['features', 'tools', 'saddle'];

export function getDashTabFromHash() {
	if (typeof dcsCF7Styler !== 'undefined' && dcsCF7Styler.currentPage === 'saddle') return 'saddle';
	const hash = (window.location.hash || '').replace(/^#\/?/, '');
	if (VALID_TABS.includes(hash)) return hash;
	return 'features';
}
