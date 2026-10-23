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

	function setValue(container, value) {
		var input = container.querySelector(INPUT_SEL);
		if (input) input.value = value;
		highlight(container, value);
	}

	function highlight(container, upTo) {
		var stars = container.querySelectorAll(STAR_SEL);
		var n = parseInt(upTo, 10) || 0;
		stars.forEach(function (star, i) {
			star.classList.toggle(ON_CLASS, i + 1 <= n);
		});
	}

	function initOne(container) {
		if (container.dataset.cf7mStarInit) return;
		container.dataset.cf7mStarInit = '1';

		var stars = container.querySelectorAll(STAR_SEL);
		var val = getValue(container);
		highlight(container, val);

		stars.forEach(function (star, i) {
			var starValue = i + 1;
			star.addEventListener('click', function () {
				setValue(container, starValue);
			});
			star.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					setValue(container, starValue);
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
