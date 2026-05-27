(function () {
	'use strict';

	var CONTAINER = 'cf7m-star-rating';
	var INPUT_SEL = '[data-cf7m-star-input]';
	var STAR_SEL = '.cf7m-star';
	var ON_CLASS = 'cf7m-star--on';

	function getValue(container) {
		var input = container.querySelector(INPUT_SEL);
		return input ? parseInt(input.value, 10) || 0 : 0;
	}

	function setValue(container, value, focusIndex) {
		var input = container.querySelector(INPUT_SEL);
		if (input) input.value = value;
		highlight(container, value);
		updateRovingTabindex(container, value);
		if (typeof focusIndex === 'number') {
			var stars = container.querySelectorAll(STAR_SEL);
			if (stars[focusIndex]) stars[focusIndex].focus();
		}
	}

	function highlight(container, upTo) {
		var stars = container.querySelectorAll(STAR_SEL);
		var n = parseInt(upTo, 10) || 0;
		stars.forEach(function (star, i) {
			var on = i + 1 <= n;
			star.classList.toggle(ON_CLASS, on);
			star.setAttribute('aria-checked', i + 1 === n ? 'true' : 'false');
		});
	}

	/**
	 * Roving tabindex: only one button in the group is in the tab order at a
	 * time — the currently-selected one, or the first if nothing selected.
	 */
	function updateRovingTabindex(container, value) {
		var stars = container.querySelectorAll(STAR_SEL);
		var v = parseInt(value, 10) || 0;
		var focusable = v > 0 ? v - 1 : 0;
		stars.forEach(function (star, i) {
			star.tabIndex = i === focusable ? 0 : -1;
		});
	}

	function initOne(container) {
		if (container.dataset.cf7mStarInit) return;
		container.dataset.cf7mStarInit = '1';

		var stars = container.querySelectorAll(STAR_SEL);
		var max = stars.length;
		var val = getValue(container);
		highlight(container, val);
		updateRovingTabindex(container, val);

		stars.forEach(function (star, i) {
			var starValue = i + 1;
			star.addEventListener('click', function () {
				setValue(container, starValue);
			});
			star.addEventListener('keydown', function (e) {
				var current = getValue(container);
				switch (e.key) {
					case 'Enter':
					case ' ':
						e.preventDefault();
						setValue(container, starValue);
						break;
					case 'ArrowRight':
					case 'ArrowUp':
						e.preventDefault();
						setValue(container, Math.min(max, current + 1), Math.min(max, current + 1) - 1);
						break;
					case 'ArrowLeft':
					case 'ArrowDown':
						e.preventDefault();
						if (current > 0) {
							var next = current - 1;
							setValue(container, next, next > 0 ? next - 1 : 0);
						}
						break;
					case 'Home':
						e.preventDefault();
						setValue(container, 0, 0);
						break;
					case 'End':
						e.preventDefault();
						setValue(container, max, max - 1);
						break;
				}
			});
			star.addEventListener('mouseenter', function () {
				highlight(container, starValue);
			});
		});

		container.addEventListener('mouseleave', function () {
			highlight(container, getValue(container));
		});
	}

	function init() {
		document.querySelectorAll('.' + CONTAINER).forEach(initOne);
	}

	function onReady() {
		init();
		if (typeof jQuery !== 'undefined') {
			jQuery(document).on(
				'wpcf7mailsent wpcf7invalid wpcf7spam wpcf7mailfailed wpcf7submit',
				function () {
					setTimeout(init, 100);
				}
			);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', onReady);
	} else {
		onReady();
	}
})();
