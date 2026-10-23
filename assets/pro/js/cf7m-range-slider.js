/**
 * CF7 Mate – Range slider [cf7m-range] frontend behaviour.
 * Supports both new (.cf7m-range-slider) and legacy (.cf7m-range-slider) classes.
 */
(function () {
  'use strict';

  function initOne(container) {
    if (container.dataset.cf7mRangeInit) return;
    container.dataset.cf7mRangeInit = '1';

    var input = container.querySelector('.cf7m-range-input');
    var display = container.querySelector('.cf7m-range-value') || container.querySelector('.cf7m-range-value');
    var prefix = container.dataset.prefix || '';
    var suffix = container.dataset.suffix || '';

    if (!input) return;

    function update() {
      if (display) {
        display.textContent = prefix + input.value + suffix;
      }
    }

    input.addEventListener('input', update);
    update();
  }

  function init() {
    // New classes
    document.querySelectorAll('.cf7m-range-slider').forEach(initOne);
    // Legacy classes
    document.querySelectorAll('.cf7m-range-slider').forEach(initOne);
  }

  function onReady() {
    init();
    // Re-init after CF7 events (form reset, etc.)
    if (typeof jQuery !== 'undefined') {
      jQuery(document).on('wpcf7mailsent wpcf7invalid wpcf7spam wpcf7mailfailed wpcf7submit', function () {
        setTimeout(init, 100);
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', onReady);
  } else {
    onReady();
  }
})();
