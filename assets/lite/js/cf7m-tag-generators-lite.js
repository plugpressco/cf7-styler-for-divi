/**
 * CF7 Mate - Tag Generator Scripts (Lite: separator, star rating, range slider)
 *
 * @package CF7_Mate
 * @since   3.0.0
 */

(function () {
	'use strict';

	function initTagGenerators() {
		initSeparatorGenerator();
		initRangeGenerator();
		initStarRatingGenerator();
	}

	/**
	 * Find a generator panel: legacy pane markup or CF7 5.x/6.x tag generator form.
	 */
	function findPane(id) {
		return document.querySelector('.wpcf7-tg-pane-' + id) ||
			document.querySelector('form.tag-generator-panel[data-id="' + id + '"]');
	}

	/**
	 * CF7 5.x recomposes every generator tag with its own composer, which drops
	 * CF7 Mate options and closing tags. Register ours for this panel instead.
	 */
	function useOwnComposer(id, updateTag) {
		var taggen = window.wpcf7 && window.wpcf7.taggen;
		if (!taggen || typeof taggen.update !== 'function') return;
		taggen.update[id] = function () { updateTag(); };
		updateTag();
	}

	/**
	 * Separator tag generator.
	 */
	function initSeparatorGenerator() {
		var pane = findPane('cf7m-separator');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var styleSelect = pane.querySelector('#cf7m-sep-style');
		var colorInput = pane.querySelector('#cf7m-sep-color');
		var marginInput = pane.querySelector('#cf7m-sep-margin');
		var tagOutput = pane.querySelector('input.tag');

		function updateTag() {
			var parts = ['[cf7m-separator'];
			var style = styleSelect && styleSelect.value;
			var color = colorInput && colorInput.value;
			var margin = marginInput && marginInput.value;
			if (style && style !== 'solid') parts.push('style:' + style);
			if (color && color !== '#e5e7eb') parts.push('color:' + color);
			if (margin && margin !== '20') parts.push('margin:' + margin);
			if (tagOutput) tagOutput.value = parts.join(' ') + ']';
		}

		[styleSelect, colorInput, marginInput].forEach(function (el) {
			if (el) {
				el.addEventListener('input', updateTag);
				el.addEventListener('change', updateTag);
			}
		});
		useOwnComposer('cf7m-separator', updateTag);
	}

	/**
	 * Range slider tag generator.
	 */
	function initRangeGenerator() {
		var pane = document.querySelector('.wpcf7-tg-pane-cf7m-range');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var nameInput = pane.querySelector('#cf7m-range-name');
		var minInput = pane.querySelector('#cf7m-range-min');
		var maxInput = pane.querySelector('#cf7m-range-max');
		var stepInput = pane.querySelector('#cf7m-range-step');
		var defaultInput = pane.querySelector('#cf7m-range-default');
		var prefixInput = pane.querySelector('#cf7m-range-prefix');
		var suffixInput = pane.querySelector('#cf7m-range-suffix');
		var requiredInput = pane.querySelector('#cf7m-range-required');
		var tagOutput = pane.querySelector('input.tag');

		function updateTag() {
			var isRequired = requiredInput && requiredInput.checked;
			var name = (nameInput && nameInput.value) || 'amount';
			var min = (minInput && minInput.value) || '0';
			var max = (maxInput && maxInput.value) || '100';
			var step = (stepInput && stepInput.value) || '1';
			var defaultVal = (defaultInput && defaultInput.value) || '50';
			var prefix = prefixInput && prefixInput.value;
			var suffix = suffixInput && suffixInput.value;

			var tag = isRequired ? '[cf7m-range* ' : '[cf7m-range ';
			tag += name;
			tag += ' min:' + min;
			tag += ' max:' + max;
			tag += ' step:' + step;
			tag += ' default:' + defaultVal;
			if (prefix) tag += ' prefix:"' + prefix + '"';
			if (suffix) tag += ' suffix:"' + suffix + '"';
			tag += ']';

			if (tagOutput) tagOutput.value = tag;
		}

		[nameInput, minInput, maxInput, stepInput, defaultInput, prefixInput, suffixInput, requiredInput].forEach(function (el) {
			if (el) {
				el.addEventListener('input', updateTag);
				el.addEventListener('change', updateTag);
			}
		});
	}

	/**
	 * Star rating tag generator.
	 */
	function initStarRatingGenerator() {
		var pane = document.querySelector('.wpcf7-tg-pane-cf7m-star');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var nameInput = pane.querySelector('#cf7m-star-name');
		var maxInput = pane.querySelector('#cf7m-star-max');
		var defaultInput = pane.querySelector('#cf7m-star-default');
		var colorInput = pane.querySelector('#cf7m-star-color');
		var requiredInput = pane.querySelector('#cf7m-star-required');
		var tagOutput = pane.querySelector('input.tag');

		function updateTag() {
			var isRequired = requiredInput && requiredInput.checked;
			var name = (nameInput && nameInput.value) || 'rating';
			var max = (maxInput && maxInput.value) || '5';
			var defaultVal = (defaultInput && defaultInput.value) || '0';
			var color = colorInput && colorInput.value;

			var tag = isRequired ? '[cf7m-star* ' : '[cf7m-star ';
			tag += name;
			tag += ' max:' + max;
			tag += ' default:' + defaultVal;
			if (color) tag += ' color:' + color;
			tag += ']';

			if (tagOutput) tagOutput.value = tag;
		}

		[nameInput, maxInput, defaultInput, colorInput, requiredInput].forEach(function (el) {
			if (el) {
				el.addEventListener('input', updateTag);
				el.addEventListener('change', updateTag);
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initTagGenerators);
	} else {
		initTagGenerators();
	}

	var observer = new MutationObserver(function (mutations) {
		mutations.forEach(function (mutation) {
			if (mutation.addedNodes.length) {
				mutation.addedNodes.forEach(function (node) {
					if (node.nodeType === 1 && node.querySelector) {
						var selectors = [
							'.wpcf7-tg-pane-cf7m-separator',
							'.wpcf7-tg-pane-cf7m-range',
							'.wpcf7-tg-pane-cf7m-star'
						];
						for (var i = 0; i < selectors.length; i++) {
							if (node.querySelector(selectors[i]) || (node.matches && node.matches(selectors[i]))) {
								initTagGenerators();
								break;
							}
						}
					}
				});
			}
		});
	});

	observer.observe(document.body, { childList: true, subtree: true });

})();
