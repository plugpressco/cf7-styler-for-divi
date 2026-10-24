/**
 * Hash-based routing helpers for the settings app.
 * Tabs: features (default), tools, saddle. Older hashes such as #/ai-settings
 * open the matching tab; retired ones such as #/license open features.
 *
 * @package CF7_Mate
 */

const VALID_TABS = ['features', 'tools', 'saddle'];
const ALIASES = { 'ai-settings': 'tools', webhook: 'tools', webhooks: 'tools' };

export function getDashTabFromHash() {
	if (typeof dcsCF7Styler !== 'undefined' && dcsCF7Styler.currentPage === 'saddle') return 'saddle';
	const hash = (window.location.hash || '').replace(/^#\/?/, '');
	const tab = ALIASES[hash] || hash;
	if (VALID_TABS.includes(tab)) return tab;
	return 'features';
}
