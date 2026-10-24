/**
 * CF7 Mate – Multi-step form (cf7m-step) behaviour.
 * Enqueued on frontend when a CF7 form uses multi-step.
 *
 * Supports progress styles: circles (default), progress-bar, connected.
 *
 * @package CF7_Mate
 * @since 3.0.0
 */

(function () {
  'use strict';

  function init() {
    document.querySelectorAll('.cf7m-multistep-form').forEach(function (form) {
      if (form.dataset.init) return;
      form.dataset.init = '1';

      var steps    = form.querySelectorAll('.cf7m-step');
      var prev     = form.querySelector('.cf7m-prev-step');
      var next     = form.querySelector('.cf7m-next-step');
      var current  = 1;
      var total    = parseInt(form.dataset.totalSteps, 10) || 1;
      var style    = form.dataset.progressStyle || 'circles';

      // Progress elements per style.
      var progress    = form.querySelectorAll('.cf7m-progress-step');
      var barFill     = form.querySelector('.cf7m-progress-bar-fill');
      var barLabel    = form.querySelector('.cf7m-progress-bar-label');
      var items       = form.querySelectorAll('.cf7m-progress-item');
      var connectors  = form.querySelectorAll('.cf7m-progress-connector');

      function show(n) {
        // Toggle active step content.
        steps.forEach(function (s, i) {
          s.classList.toggle('active', i + 1 === n);
          s.setAttribute('aria-hidden', i + 1 === n ? 'false' : 'true');
        });

        // Update progress indicator based on style.
        if (style === 'progress-bar') {
          if (barFill) barFill.style.width = (n / total * 100) + '%';
          if (barLabel) barLabel.textContent = (window.cf7mMultiSteps?.stepLabel || 'Step %1$d/%2$d').replace('%1$d', n).replace('%2$d', total);
        } else if (style === 'connected') {
          items.forEach(function (item, i) {
            item.classList.remove('active', 'completed');
            if (i + 1 < n) item.classList.add('completed');
            if (i + 1 === n) item.classList.add('active');
          });
          connectors.forEach(function (c, i) {
            // Connector i sits between item i and item i+1.
            // It's "completed" when both surrounding steps are done (i+1 < n).
            c.classList.toggle('completed', i + 1 < n);
          });
        } else {
          // Default circles.
          progress.forEach(function (p, i) {
            p.classList.remove('active', 'completed');
            if (i + 1 < n) p.classList.add('completed');
            if (i + 1 === n) p.classList.add('active');
          });
        }

        // Toggle nav buttons.
        if (prev) prev.style.display = n === 1 ? 'none' : 'block';
        if (next) next.style.display = n === total ? 'none' : 'block';
      }

      var contactForm = form.closest('form');
      var busy = false;

      function focusStep() {
        var target = steps[current - 1].querySelector('input:not([type="hidden"]):enabled, select:enabled, textarea:enabled');
        if (target && target.getClientRects().length) target.focus();
        else {
          steps[current - 1].tabIndex = -1;
          steps[current - 1].focus();
        }
      }

      async function advance() {
        if (busy || current >= total) return;
        busy = true;
        if (next) next.disabled = true;
        try {
          var step = steps[current - 1];
          var controls = Array.from(step.querySelectorAll('input:not([type="hidden"]):enabled, select:enabled, textarea:enabled'))
            .filter(function (control) { return control.getClientRects().length; });
          if (window.wpcf7?.validate && contactForm?.wpcf7?.schema) {
            for (var control of controls) {
              await window.wpcf7.validate(contactForm, { target: control, scope: step });
            }
          }
          // Always enforce required fields here too: CF7's schema may still be
          // loading, and CF7 5.x starts validation without returning its promise,
          // so its errors can arrive after this step has already advanced.
          step.querySelectorAll('.wpcf7-validates-as-required, [aria-required="true"], [required]').forEach(function (required) {
            var inputs = required.matches('input,select,textarea') ? [required] : Array.from(required.querySelectorAll('input,select,textarea'));
            inputs = inputs.filter(function (input) { return !input.disabled && input.getClientRects().length; });
            if (!inputs.length) return;
            var filled = inputs.some(function (input) {
              return /^(checkbox|radio)$/.test(input.type) ? input.checked : input.value.trim() !== '';
            });
            inputs[0].setCustomValidity(filled ? '' : window.cf7mMultiSteps.required);
            inputs[0].dataset.cf7mValidity = '1';
          });
          var invalid = controls.find(function (control) {
            return control.getAttribute('aria-invalid') === 'true' || !control.checkValidity();
          });
          if (invalid) {
            invalid.focus();
            invalid.reportValidity();
            return;
          }
          current++;
          show(current);
          focusStep();
        } finally {
          busy = false;
          if (next) next.disabled = false;
        }
      }

      if (prev) prev.onclick = function () {
        if (current > 1) { current--; show(current); focusStep(); }
      };
      if (next) next.onclick = advance;
      form.addEventListener('input', function (event) {
        if (event.target.dataset.cf7mValidity) {
          event.target.setCustomValidity('');
          delete event.target.dataset.cf7mValidity;
        }
      });
      form.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && event.target.matches('input:not([type="submit"]):not([type="button"])') && current < total) {
          event.preventDefault();
          advance();
        }
      });
      contactForm?.closest('.wpcf7')?.addEventListener('wpcf7invalid', function () {
        var invalid = Array.from(form.querySelectorAll('[aria-invalid="true"]:not(:disabled)')).find(function (control) {
          return !control.closest('.cf7m-condition[style*="display: none"]');
        });
        var step = invalid?.closest('.cf7m-step');
        if (step) {
          current = Array.from(steps).indexOf(step) + 1;
          show(current);
          invalid.focus();
        }
      });
      contactForm?.closest('.wpcf7')?.addEventListener('wpcf7mailsent', function () {
        current = 1;
        show(current);
      });
      show(1);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
