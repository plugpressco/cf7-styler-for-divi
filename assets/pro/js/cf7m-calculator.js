/**
 * CF7 Mate – Calculator / Price Estimator
 *
 * Live calculation with currency, percentage, and unit formatting.
 *
 * @package CF7_Mate
 * @since   3.0.0
 */
(function() {
  'use strict';

  /**
   * Number formatting utilities.
   */
  const Formatter = {
    /**
     * Format number with thousand separators.
     */
    addCommas(num, decimals = 2) {
      const fixed = num.toFixed(decimals);
      const parts = fixed.split('.');
      parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      return decimals > 0 ? parts.join('.') : parts[0];
    },

    /**
     * Format as currency.
     */
    currency(value, prefix = '$', suffix = '', decimals = 2) {
      const formatted = this.addCommas(Math.abs(value), decimals);
      const sign = value < 0 ? '-' : '';
      return sign + prefix + formatted + suffix;
    },

    /**
     * Format as percentage.
     */
    percent(value, decimals = 1, suffix = '%') {
      return this.addCommas(value, decimals) + suffix;
    },

    /**
     * Format as plain number with optional suffix.
     */
    number(value, decimals = 0, prefix = '', suffix = '') {
      return prefix + this.addCommas(value, decimals) + suffix;
    }
  };

  /**
   * Calculator class.
   */
  class CF7MCalculator {
    constructor(form) {
      this.form = form;
      this.formulas = new Map();
      this.fields = new Map();
      this.totals = new Map();

      this.init();
    }

    /**
     * Initialize calculator.
     */
    init() {
      // Find all formula definitions.
      this.form.querySelectorAll('.cf7m-calc-formula').forEach(el => {
        const id = el.dataset.calcId;
        const formula = el.dataset.formula;
        if (id && formula) {
          this.formulas.set(id, formula);
        }
      });

      // Find all input fields.
      this.form.querySelectorAll('.cf7m-calc-input').forEach(el => {
        const name = el.dataset.calcField;
        if (name) {
          this.fields.set(name, el);
          el.addEventListener('input', () => this.calculate());
          el.addEventListener('change', () => this.calculate());
        }
      });

      // Find select dropdowns that might feed calculations.
      this.form.querySelectorAll('select').forEach(el => {
        if (el.name) {
          el.addEventListener('change', () => this.calculate());
        }
      });

      // Find all total displays.
      this.form.querySelectorAll('.cf7m-total').forEach(el => {
        const id = el.dataset.calcId;
        if (id) {
          this.totals.set(id, {
            element: el,
            display: el.querySelector('.cf7m-total-value'),
            prefixEl: el.querySelector('.cf7m-total-prefix'),
            suffixEl: el.querySelector('.cf7m-total-suffix'),
            input: this.form.querySelector(`.cf7m-total-input[data-calc-id="${id}"]`),
            format: el.dataset.format || 'number',
            prefix: el.dataset.prefix || '',
            suffix: el.dataset.suffix || '',
            decimals: parseInt(el.dataset.decimals, 10) || 2
          });
        }
      });

      // [cf7m-button] with data-cf7m-action="calculate" – click recalculates, does not submit.
      this.form.querySelectorAll('.cf7m-calc-trigger[data-cf7m-action="calculate"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          this.calculate();
        });
      });

      // Initial calculation.
      this.calculate();
    }

    /**
     * Get field value by name.
     */
    getFieldValue(name) {
      // Check cf7m-number fields first.
      if (this.fields.has(name)) {
        return parseFloat(this.fields.get(name).value) || 0;
      }

      // Check select with pipe-separated value (Label|value).
      const select = this.form.querySelector(`select[name="${name}"]`);
      if (select) {
        const val = select.value;
        // If value contains |, take the part after it.
        if (val.includes('|')) {
          return parseFloat(val.split('|')[1]) || 0;
        }
        return parseFloat(val) || 0;
      }

      // Check for checkboxes.
      const checkboxes = this.form.querySelectorAll(
        `[name="${name}"]:checked, [name="${name}[]"]:checked`
      );
      if (checkboxes.length > 0) {
        let sum = 0;
        checkboxes.forEach(el => {
          const val = el.value;
          if (val.includes('|')) {
            sum += parseFloat(val.split('|')[1]) || 0;
          } else {
            sum += parseFloat(val) || 0;
          }
        });
        return sum;
      }

      // Check any other input.
      const input = this.form.querySelector(`[name="${name}"]`);
      if (input) {
        return parseFloat(input.value) || 0;
      }

      return 0;
    }

    /**
     * Safely evaluate a formula.
     */
    evaluate(formula) {
      let expression = formula;

      // Find all word tokens (field names; allow hyphens e.g. interest-rate).
      const tokens = formula.match(/[a-zA-Z_][a-zA-Z0-9_-]*/g) || [];
      const uniqueTokens = [...new Set(tokens)];

      uniqueTokens.forEach(token => {
        // Skip math keywords.
        if (['Math', 'min', 'max', 'round', 'floor', 'ceil', 'abs', 'pow'].includes(token)) {
          return;
        }

        const value = this.getFieldValue(token);
        const escaped = token.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp('\\b' + escaped + '\\b', 'g');
        expression = expression.replace(regex, value);
      });

      // Support ^ for exponentiation (e.g. mortgage formula).
      expression = expression.replace(/\^/g, '**');

      // Replace ** with Math.pow for exponentiation (numeric cases).
      expression = expression.replace(/(\d+(?:\.\d+)?)\s*\*\*\s*(\d+(?:\.\d+)?)/g, 'Math.pow($1,$2)');
      expression = expression.replace(/\(([^)]+)\)\s*\*\*\s*(\d+(?:\.\d+)?)/g, 'Math.pow($1,$2)');

      // Allow only safe characters.
      if (!/^[\d\s+\-*/().Math,pow]+$/.test(expression)) {
        console.warn('CF7M Calculator: Invalid expression', expression);
        return 0;
      }

      try {
        const result = Function('"use strict"; return (' + expression + ')')();
        return isFinite(result) ? result : 0;
      } catch (e) {
        console.warn('CF7M Calculator: Evaluation error', e);
        return 0;
      }
    }

    /**
     * Format a value based on config.
     */
    formatValue(value, config) {
      const { format, prefix, suffix, decimals } = config;

      switch (format) {
        case 'currency':
          return Formatter.currency(value, prefix, suffix, decimals);

        case 'percent':
          return Formatter.percent(value, decimals, suffix || '%');

        default:
          return Formatter.number(value, decimals, prefix, suffix);
      }
    }

    /**
     * Run all calculations.
     */
    calculate() {
      // Calculate each formula.
      const results = new Map();

      this.formulas.forEach((formula, id) => {
        const result = this.evaluate(formula);
        results.set(id, result);
      });

      // Update all total displays.
      this.totals.forEach((config, id) => {
        const result = results.get(id) || 0;
        const formatted = this.formatValue(result, config);

        // Update display.
        if (config.display) {
          // Just the number part without prefix/suffix.
          config.display.textContent = Formatter.addCommas(
            Math.abs(result),
            config.decimals
          );
        }

        // Update prefix/suffix elements if they exist.
        if (config.prefixEl && config.prefix) {
          config.prefixEl.textContent = (result < 0 ? '-' : '') + config.prefix;
        }
        if (config.suffixEl && config.suffix) {
          config.suffixEl.textContent = config.suffix;
        }

        // Update hidden input.
        if (config.input) {
          config.input.value = result.toFixed(config.decimals);
        }

        // Add animation class.
        if (config.element) {
          config.element.classList.add('cf7m-total--updated');
          setTimeout(() => {
            config.element.classList.remove('cf7m-total--updated');
          }, 300);
        }

        // Trigger event.
        this.form.dispatchEvent(new CustomEvent('cf7m:calculated', {
          detail: { id, value: result, formatted }
        }));
      });
    }
  }

  /**
   * Initialize calculators on all forms.
   */
  function init() {
    document.querySelectorAll('.wpcf7-form').forEach(form => {
      if (form.querySelector('.cf7m-calc-formula') || form.querySelector('.cf7m-calc-input')) {
        if (!form._cf7mCalculator) {
          form._cf7mCalculator = new CF7MCalculator(form);
        }
      }
    });
  }

  // Init on DOM ready.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Re-init after CF7 events.
  document.addEventListener('wpcf7mailsent', init);
  document.addEventListener('wpcf7invalid', init);

  // Expose.
  window.CF7MCalculator = CF7MCalculator;
  window.CF7MFormatter = Formatter;
})();
