/**
 * Compact resources row (docs, video, support).
 *
 * @package CF7_Mate
 */

import { __ } from '@wordpress/i18n';

export function HowToSection() {
	return (
		<div className="cf7m-howto">
			<span className="cf7m-howto__label">{__('Resources', 'cf7-styler-for-divi')}</span>
			<nav className="cf7m-howto__links" aria-label={__('Resources & Support', 'cf7-styler-for-divi')}>
				<a href="https://plugpress.io/docs-category/cf7-mate/" target="_blank" rel="noopener noreferrer" className="cf7m-howto__link">
					{__('Docs', 'cf7-styler-for-divi')}
				</a>
				<span className="cf7m-howto__sep" aria-hidden="true">·</span>
				<a href="https://plugpress.io/support/" target="_blank" rel="noopener noreferrer" className="cf7m-howto__link">
					{__('Support', 'cf7-styler-for-divi')}
				</a>
			</nav>
		</div>
	);
}
