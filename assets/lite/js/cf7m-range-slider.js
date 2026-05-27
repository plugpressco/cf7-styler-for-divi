/**
 * CF7 Mate — Range slider [cf7m-range] frontend behaviour.
 * Mirrors the displayed value to the live `.cf7m-range-value` text as the
 * user drags; the text node has aria-live="polite" on it (set in PHP) so
 * screen readers announce the change.
 */
(function () {
	'use strict';

	function initOne(container) {
		if (container.dataset.cf7mRangeInit) return;
		container.dataset.cf7mRangeInit = '1';

		var input   = container.querySelector('.cf7m-range-input');
		var display = container.querySelector('.cf7m-range-value');
		if (!input) return;

		var prefix = container.dataset.prefix || '';
		var suffix = container.dataset.suffix || '';

		function update() {
			if (display) display.textContent = prefix + input.value + suffix;
		}

		input.addEventListener('input', update);
		input.addEventListener('change', update);
		update();
	}

	function init() {
		document.querySelectorAll('.cf7m-range-slider').forEach(initOne);
	}

	function onReady() {
		init();
		if (typeof jQuery !== 'undefined') {
			jQuery(document).on(
				'wpcf7mailsent wpcf7invalid wpcf7spam wpcf7mailfailed wpcf7submit',
				function () { setTimeout(init, 100); }
			);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', onReady);
	} else {
		onReady();
	}
})();
