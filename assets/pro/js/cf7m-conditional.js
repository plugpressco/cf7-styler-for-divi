/**
 * CF7 Mate – Conditional Logic
 *
 * Show/hide fields based on other field values.
 *
 * @package CF7_Mate
 * @since   3.0.0
 */
(function() {
  'use strict';

  /**
   * Conditional Logic handler.
   */
  class CF7MConditional {
    /**
     * Initialize on a form.
     *
     * @param {HTMLElement} form Form element.
     */
    constructor(form) {
      this.form = form;
      this.conditions = [];
      this.fields = new Map();

      this.init();
    }

    /**
     * Setup conditions and listeners.
     */
    init() {
      // Find all conditional blocks.
      this.form.querySelectorAll('.cf7m-condition').forEach(el => {
        const condition = {
          element: el,
          field: el.dataset.cf7mIf,
          operator: el.dataset.cf7mOperator || 'is',
          value: el.dataset.cf7mValue || '',
          and: el.dataset.cf7mAnd || null,
          or: el.dataset.cf7mOr || null
        };

        this.conditions.push(condition);

        // Track which fields affect conditions.
        this.trackField(condition.field);
      });

      // Bind listeners to all tracked fields.
      this.fields.forEach((_, name) => {
        this.bindField(name);
      });

      this.form.querySelectorAll('.cf7m-condition input, .cf7m-condition select, .cf7m-condition textarea').forEach(input => {
        input.dataset.cf7mOriginallyDisabled = input.disabled ? '1' : '0';
      });

      // Initial evaluation.
      this.evaluateAll();
      this.form.addEventListener('reset', () => setTimeout(() => this.evaluateAll(), 0));
    }

    /**
     * Track a field for changes.
     *
     * @param {string} name Field name.
     */
    trackField(name) {
      if (!name || this.fields.has(name)) return;
      this.fields.set(name, true);
    }

    /**
     * Bind change listeners to a field.
     *
     * @param {string} name Field name.
     */
    bindField(name) {
      const selectors = [
        `[name="${name}"]`,
        `[name="${name}[]"]`,
        `[data-name="${name}"]`
      ];

      selectors.forEach(sel => {
        this.form.querySelectorAll(sel).forEach(input => {
          const events = ['change', 'input'];
          events.forEach(evt => {
            input.addEventListener(evt, () => this.evaluateAll());
          });
        });
      });
    }

    /**
     * Get field value by name.
     *
     * @param {string} name Field name.
     * @return {string|number|boolean} Field value.
     */
    getFieldValue(name) {
      const inputs = Array.from(this.form.querySelectorAll('input,select,textarea')).filter(input => input.name === name || input.name === name + '[]');
      if (!inputs.length) return '';
      if (inputs.some(input => /^(checkbox|radio)$/.test(input.type))) {
        return inputs.filter(input => input.checked).map(input => input.value);
      }
      if (inputs[0].multiple) return Array.from(inputs[0].selectedOptions, option => option.value);
      return inputs[0].value;
    }

    /**
     * Evaluate a single condition.
     *
     * @param {Object} condition Condition object.
     * @return {boolean} Whether condition is met.
     */
    evaluate(condition) {
      const fieldValue = this.getFieldValue(condition.field);
      const targetValue = condition.value;
      const operator = condition.operator;

      return this.compare(fieldValue, operator, targetValue);
    }

    /**
     * Compare values with operator.
     *
     * @param {*} fieldValue  Actual field value.
     * @param {string} operator Comparison operator.
     * @param {string} target   Target value to compare.
     * @return {boolean} Comparison result.
     */
    compare(fieldValue, operator, target) {
      const values = [].concat(fieldValue).map(value => String(value).trim()).filter(value => value !== '');
      target = String(target).trim();
      if (operator === 'empty' || operator === 'checked') {
        const match = operator === 'empty' ? values.length === 0 : values.length > 0;
        return target === 'true' ? match : !match;
      }
      if (operator === 'not') return !(values.length ? values : ['']).includes(target);
      return (values.length ? values : ['']).some(value => {
        const number = parseFloat(value) || 0;
        const targetNumber = parseFloat(target) || 0;
        switch (operator) {
          case 'gt': return number > targetNumber;
          case 'lt': return number < targetNumber;
          case 'gte': return number >= targetNumber;
          case 'lte': return number <= targetNumber;
          case 'contains': return value.toLowerCase().includes(target.toLowerCase());
          case 'any': return target.split(',').map(item => item.trim()).includes(value);
          default: return value === target;
        }
      });
    }

    /**
     * Evaluate all conditions and update visibility.
     */
    evaluateAll() {
      this.conditions.forEach(condition => {
        const result = this.evaluate(condition);
        this.toggle(condition.element, result);
      });
    }

    /**
     * Toggle element visibility.
     *
     * @param {HTMLElement} element Element to toggle.
     * @param {boolean} show Whether to show.
     */
    toggle(element, show) {
      const parent = element.parentElement.closest('.cf7m-condition');
      show = show && (!parent || parent.classList.contains('cf7m-condition--visible'));
      if (show) {
        element.style.display = '';
        element.classList.add('cf7m-condition--visible');

        // Enable inputs inside.
        element.querySelectorAll('input, select, textarea').forEach(input => {
          input.disabled = input.dataset.cf7mOriginallyDisabled === '1' || !!input.closest('.cf7m-condition:not(.cf7m-condition--visible)');
        });
      } else {
        element.style.display = 'none';
        element.classList.remove('cf7m-condition--visible');

        // Disable inputs to exclude from submission.
        element.querySelectorAll('input, select, textarea').forEach(input => {
          input.disabled = true;
        });
      }
    }
  }

  /**
   * Initialize on all forms.
   */
  function init() {
    document.querySelectorAll('.wpcf7-form').forEach(form => {
      if (form.querySelector('.cf7m-condition') && !form._cf7mConditional) {
        form._cf7mConditional = new CF7MConditional(form);
      }
    });
  }

  // Init on ready.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Re-init after AJAX.
  document.addEventListener('wpcf7mailsent', init);

  // Expose class.
  window.CF7MConditional = CF7MConditional;
})();
