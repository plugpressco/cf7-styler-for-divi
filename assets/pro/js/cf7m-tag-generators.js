/**
 * CF7 Mate - Tag Generator Scripts
 *
 * Handles dynamic tag generation for all CF7 Mate fields in CF7 editor.
 *
 * @package CF7_Mate
 * @since   3.0.0
 */

(function () {
	'use strict';

	/**
	 * Initialize all tag generators.
	 */
	function initTagGenerators() {
		initNumberGenerator();
		initCalcGenerator();
		initTotalGenerator();
		initButtonGenerator();
		initHeadingGenerator();
		initPhoneGenerator();
		initColumnGenerator();
		initConditionalGenerator();
		initStepGenerator();
		initRowGenerator();
		initPresetsGenerator();
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
	 * Find a generator panel: legacy pane markup or CF7 5.x/6.x tag generator form.
	 */
	function findPane(id) {
		return document.querySelector('.wpcf7-tg-pane-' + id) ||
			document.querySelector('form.tag-generator-panel[data-id="' + id + '"]');
	}

	/**
	 * Number field tag generator.
	 */
	function initNumberGenerator() {
		var pane = document.querySelector('.wpcf7-tg-pane-cf7m-number');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var fields = {
			name: pane.querySelector('#cf7m-number-name'),
			label: pane.querySelector('#cf7m-number-label'),
			value: pane.querySelector('#cf7m-number-value'),
			min: pane.querySelector('#cf7m-number-min'),
			max: pane.querySelector('#cf7m-number-max'),
			step: pane.querySelector('#cf7m-number-step'),
			prefix: pane.querySelector('#cf7m-number-prefix'),
			suffix: pane.querySelector('#cf7m-number-suffix'),
			required: pane.querySelector('#cf7m-number-required'),
			tag: pane.querySelector('input.tag')
		};

		function updateTag() {
			var tag = fields.required && fields.required.checked ? '[cf7m-number* ' : '[cf7m-number ';
			tag += (fields.name && fields.name.value) || 'qty';

			if (fields.label && fields.label.value) {
				tag += ' label:"' + fields.label.value + '"';
			}

			tag += ' value:' + ((fields.value && fields.value.value) || '1');
			tag += ' min:' + ((fields.min && fields.min.value) || '0');
			tag += ' max:' + ((fields.max && fields.max.value) || '100');

			if (fields.step && fields.step.value && fields.step.value !== '1') {
				tag += ' step:' + fields.step.value;
			}
			if (fields.prefix && fields.prefix.value) {
				tag += ' prefix:"' + fields.prefix.value + '"';
			}
			if (fields.suffix && fields.suffix.value) {
				tag += ' suffix:"' + fields.suffix.value + '"';
			}

			tag += ']';

			if (fields.tag) {
				fields.tag.value = tag;
			}
		}

		bindEvents(fields, updateTag);
	}

	/**
	 * Calculator formula tag generator.
	 */
	function initCalcGenerator() {
		var pane = document.querySelector('.wpcf7-tg-pane-cf7m-calc');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var idEl = pane.querySelector('#cf7m-calc-id');
		var formulaEl = pane.querySelector('#cf7m-calc-formula');
		var tagEl = pane.querySelector('input.tag');

		function updateTag() {
			if (!tagEl) return;
			var id = (idEl && idEl.value) || 'total';
			var formula = (formulaEl && formulaEl.value) || 'qty * price';
			tagEl.value = '[cf7m-calc id:' + id + ' formula:"' + formula + '"]';
		}

		if (idEl) {
			idEl.addEventListener('input', updateTag);
		}
		if (formulaEl) {
			formulaEl.addEventListener('input', updateTag);
		}
	}

	/**
	 * Total display tag generator.
	 */
	function initTotalGenerator() {
		var pane = document.querySelector('.wpcf7-tg-pane-cf7m-total');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var fields = {
			id: pane.querySelector('#cf7m-total-id'),
			label: pane.querySelector('#cf7m-total-label'),
			format: pane.querySelector('#cf7m-total-format'),
			prefix: pane.querySelector('#cf7m-total-prefix'),
			suffix: pane.querySelector('#cf7m-total-suffix'),
			decimals: pane.querySelector('#cf7m-total-decimals'),
			tag: pane.querySelector('input.tag')
		};

		function updateTag() {
			var tag = '[cf7m-total id:' + ((fields.id && fields.id.value) || 'total');

			if (fields.label && fields.label.value) {
				tag += ' label:"' + fields.label.value + '"';
			}

			tag += ' format:' + ((fields.format && fields.format.value) || 'number');

			if (fields.prefix && fields.prefix.value) {
				tag += ' prefix:"' + fields.prefix.value + '"';
			}
			if (fields.suffix && fields.suffix.value) {
				tag += ' suffix:"' + fields.suffix.value + '"';
			}

			tag += ' decimals:' + ((fields.decimals && fields.decimals.value) || '2');
			tag += ']';

			if (fields.tag) {
				fields.tag.value = tag;
			}
		}

		bindEvents(fields, updateTag);
	}

	/**
	 * Button (no submit) tag generator.
	 */
	function initButtonGenerator() {
		var pane = document.querySelector('.wpcf7-tg-pane-cf7m-button');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var fields = {
			label: pane.querySelector('#cf7m-button-label'),
			tag: pane.querySelector('input.tag')
		};

		function updateTag() {
			var label = (fields.label && fields.label.value) ? fields.label.value : 'Calculate';
			var tag = '[cf7m-button label:"' + String(label).replace(/"/g, '\\"') + '"]';
			if (fields.tag) {
				fields.tag.value = tag;
			}
		}

		bindEvents(fields, updateTag);
	}

	/**
	 * Heading tag generator.
	 */
	function initHeadingGenerator() {
		var pane = findPane('cf7m-heading');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var textInput = pane.querySelector('#cf7m-heading-text');
		var tagSelect = pane.querySelector('#cf7m-heading-tag');
		var colorInput = pane.querySelector('#cf7m-heading-color');
		var tagOutput = pane.querySelector('input.tag');

		function updateTag() {
			var text = (textInput && textInput.value) || 'Your Heading';
			var tag = (tagSelect && tagSelect.value) || 'h3';
			var color = colorInput && colorInput.value;
			var out = '[cf7m-heading text:"' + text.replace(/"/g, '') + '" tag:' + tag;
			if (color) out += ' color:' + color;
			out += ']';
			if (tagOutput) tagOutput.value = out;
		}

		[textInput, tagSelect, colorInput].forEach(function (el) {
			if (el) {
				el.addEventListener('input', updateTag);
				el.addEventListener('change', updateTag);
			}
		});
		useOwnComposer('cf7m-heading', updateTag);
	}

	/**
	 * Phone number tag generator.
	 */
	function initPhoneGenerator() {
		var pane = findPane('cf7m-phone');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var nameInput = pane.querySelector('#cf7m-phone-name');
		var defaultInput = pane.querySelector('#cf7m-phone-default');
		var labelInput = pane.querySelector('#cf7m-phone-label');
		var descriptionInput = pane.querySelector('#cf7m-phone-description');
		var placeholderInput = pane.querySelector('#cf7m-phone-placeholder');
		var requiredInput = pane.querySelector('#cf7m-phone-required');
		var tagOutput = pane.querySelector('input.tag');

		function updateTag() {
			var isRequired = requiredInput && requiredInput.checked;
			var name = (nameInput && nameInput.value) || 'phone';
			var defaultCountry = (defaultInput && defaultInput.value) || 'US';
			var label = labelInput && labelInput.value;
			var description = descriptionInput && descriptionInput.value;
			var placeholder = placeholderInput && placeholderInput.value;

			var tag = isRequired ? '[cf7m-phone* ' : '[cf7m-phone ';
			tag += name;
			tag += ' default:' + defaultCountry;
			if (label) tag += ' label:"' + label.replace(/"/g, '') + '"';
			if (description) tag += ' description:"' + description.replace(/"/g, '') + '"';
			if (placeholder) tag += ' placeholder:"' + placeholder.replace(/"/g, '') + '"';
			tag += ']';

			if (tagOutput) tagOutput.value = tag;
		}

		[nameInput, defaultInput, labelInput, descriptionInput, placeholderInput, requiredInput].forEach(function (el) {
			if (el) {
				el.addEventListener('input', updateTag);
				el.addEventListener('change', updateTag);
			}
		});
		useOwnComposer('cf7m-phone', updateTag);
	}

	/**
	 * Column tag generator.
	 */
	function initColumnGenerator() {
		var pane = findPane('cf7m-col');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var widthSelect = pane.querySelector('#cf7m-col-width');
		var valignSelect = pane.querySelector('#cf7m-col-valign');
		var tagOutput = pane.querySelector('input.tag');

		function updateTag() {
			var width = (widthSelect && widthSelect.value) || '50';
			var valign = valignSelect && valignSelect.value;
			var open = '[cf7m-col width:' + width + (valign ? ' valign:' + valign : '') + ']';
			if (tagOutput) tagOutput.value = open + '\n\n[/cf7m-col]';
		}

		[widthSelect, valignSelect].forEach(function (el) {
			if (el) {
				el.addEventListener('change', updateTag);
			}
		});
		useOwnComposer('cf7m-col', updateTag);
	}

	/**
	 * Row tag generator.
	 */
	function initRowGenerator() {
		var pane = findPane('cf7m-row');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var gapInput = pane.querySelector('#cf7m-row-gap');
		var alignSelect = pane.querySelector('#cf7m-row-align');
		var tagOutput = pane.querySelector('input.tag');

		function updateTag() {
			var gap = (gapInput && gapInput.value) || '20';
			var align = alignSelect && alignSelect.value;
			var open = '[cf7m-row gap:' + gap + (align && align !== 'start' ? ' align:' + align : '') + ']';
			if (tagOutput) tagOutput.value = open + '\n\n[/cf7m-row]';
		}

		[gapInput, alignSelect].forEach(function (el) {
			if (el) {
				el.addEventListener('input', updateTag);
				el.addEventListener('change', updateTag);
			}
		});
		useOwnComposer('cf7m-row', updateTag);
	}

	/**
	 * Style presets tag generator.
	 */
	function initPresetsGenerator() {
		var pane = findPane('cf7m-presets');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var styleSelect = pane.querySelector('#cf7m-preset-style');
		var tagOutput = pane.querySelector('input.tag');

		function updateTag() {
			var style = (styleSelect && styleSelect.value) || 'sky';
			if (tagOutput) tagOutput.value = '[cf7m-presets style="' + style + '"]\n\n[/cf7m-presets]';
		}

		if (styleSelect) {
			styleSelect.addEventListener('change', updateTag);
		}
		useOwnComposer('cf7m-presets', updateTag);
	}

	/**
	 * Conditional logic tag generator.
	 */
	function initConditionalGenerator() {
		var pane = findPane('cf7m-if');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var fieldInput = pane.querySelector('#cf7m-if-field');
		var operatorSelect = pane.querySelector('#cf7m-if-operator');
		var valueInput = pane.querySelector('#cf7m-if-value');
		var tagOutput = pane.querySelector('input.tag');

		function updateTag() {
			var field = (fieldInput && fieldInput.value) || 'fieldname';
			var op = (operatorSelect && operatorSelect.value) || 'is';
			var val = (valueInput && valueInput.value) || 'value';
			if (tagOutput) tagOutput.value = '[cf7m-if field:"' + field + '" ' + op + ':"' + val + '"]...[/cf7m-if]';
		}

		[fieldInput, operatorSelect, valueInput].forEach(function (el) {
			if (el) {
				el.addEventListener('input', updateTag);
				el.addEventListener('change', updateTag);
			}
		});
		useOwnComposer('cf7m-if', updateTag);
	}

	/**
	 * Step tag generator.
	 */
	function initStepGenerator() {
		var pane = findPane('cf7m-step');
		if (!pane || pane.dataset.cf7mInit) return;
		pane.dataset.cf7mInit = '1';

		var titleInput = pane.querySelector('input[name="title"]');
		var tagOutput = pane.querySelector('input.tag');

		function updateTag() {
			var title = (titleInput && titleInput.value) || 'Step 1';
			if (tagOutput) tagOutput.value = '[cf7m-step title:"' + title.replace(/"/g, '') + '"]\n\n[/cf7m-step]';
		}

		if (titleInput) {
			titleInput.addEventListener('input', updateTag);
			titleInput.addEventListener('change', updateTag);
		}
		useOwnComposer('cf7m-step', updateTag);
	}

	/**
	 * Helper to bind events to fields.
	 */
	function bindEvents(fields, callback) {
		Object.keys(fields).forEach(function (key) {
			var el = fields[key];
			if (el && key !== 'tag') {
				el.addEventListener('input', callback);
				el.addEventListener('change', callback);
			}
		});
	}

	// Initialize when DOM is ready.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initTagGenerators);
	} else {
		initTagGenerators();
	}

	// Re-initialize when ThickBox content loads (for CF7 tag generator modals).
	var observer = new MutationObserver(function (mutations) {
		mutations.forEach(function (mutation) {
			if (mutation.addedNodes.length) {
				mutation.addedNodes.forEach(function (node) {
					if (node.nodeType === 1 && node.querySelector) {
						var selectors = [
							'.wpcf7-tg-pane-cf7m-number',
							'.wpcf7-tg-pane-cf7m-calc',
							'.wpcf7-tg-pane-cf7m-total',
							'.wpcf7-tg-pane-cf7m-button',
							'.wpcf7-tg-pane-cf7m-heading',
							'.wpcf7-tg-pane-cf7m-phone',
							'.wpcf7-tg-pane-cf7m-col',
							'.wpcf7-tg-pane-cf7m-if',
							'.wpcf7-tg-pane-cf7m-step'
						];
						for (var i = 0; i < selectors.length; i++) {
							if (node.querySelector(selectors[i]) || node.matches && node.matches(selectors[i])) {
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
