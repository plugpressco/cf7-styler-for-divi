/**
 * CF7 Mate — Redirect after successful submission.
 *
 * Reads the redirect config that PHP injects inside each form as a
 * <script type="application/json" class="cf7m-redirect-config">, listens to
 * the standard wpcf7mailsent event, evaluates conditional rules, substitutes
 * [field_name] placeholders, and performs the redirect.
 *
 * @package CF7_Mate
 * @since 3.1.0
 */
(function () {
	'use strict';

	// Collect configs keyed by CF7 form ID on DOMContentLoaded.
	var configs = {};

	function loadConfigs() {
		var nodes = document.querySelectorAll('script.cf7m-redirect-config');
		for (var i = 0; i < nodes.length; i++) {
			try {
				var cfg = JSON.parse(nodes[i].textContent || '{}');
				if (cfg && cfg.form_id) {
					configs[String(cfg.form_id)] = cfg;
				}
			} catch (e) {
				// Bad JSON — skip silently.
			}
		}
	}

	/**
	 * Build a map of submitted inputs keyed by name.
	 * CF7 hands us event.detail.inputs = [{ name, value }, …].
	 */
	function inputsToMap(inputs) {
		var map = {};
		if (!inputs || !inputs.length) return map;
		for (var i = 0; i < inputs.length; i++) {
			var name = inputs[i].name;
			if (!name) continue;
			// Strip trailing "[]" so checkbox/radio arrays look up by base name.
			var clean = name.replace(/\[\]$/, '');
			if (map[clean] === undefined) {
				map[clean] = inputs[i].value;
			} else {
				// Multi-value: comma-join in submission order.
				map[clean] = map[clean] + ',' + inputs[i].value;
			}
		}
		return map;
	}

	/**
	 * Replace [field_name] tokens in a URL with the user's submitted value
	 * (URL-encoded). Unknown fields leave the placeholder intact.
	 */
	function substitute(url, fields) {
		return url.replace(/\[([^\]\s]+)\]/g, function (match, name) {
			if (fields.hasOwnProperty(name)) {
				return encodeURIComponent(fields[name]);
			}
			return match;
		});
	}

	/**
	 * Case-insensitive comparison helpers.
	 */
	function evalRule(fieldVal, op, ruleVal) {
		var a = String(fieldVal == null ? '' : fieldVal).toLowerCase();
		var b = String(ruleVal == null ? '' : ruleVal).toLowerCase();
		switch (op) {
			case 'is':       return a === b;
			case 'is_not':   return a !== b;
			case 'contains': return b !== '' && a.indexOf(b) !== -1;
			default:         return false;
		}
	}

	function pickUrl(cfg, fields) {
		var rules = cfg.rules || [];
		for (var i = 0; i < rules.length; i++) {
			var r = rules[i];
			if (!r || !r.field || !r.url) continue;
			var fv = fields[r.field];
			if (evalRule(fv, r.operator || 'is', r.value || '')) {
				return r.url;
			}
		}
		return cfg.url || '';
	}

	function doRedirect(cfg, detail) {
		var fields = inputsToMap(detail && detail.inputs);
		var url    = pickUrl(cfg, fields);
		if (!url) return;
		url = substitute(url, fields);

		var go = function () {
			if (cfg.new_tab) {
				window.open(url, '_blank', 'noopener,noreferrer');
			} else {
				window.location.href = url;
			}
		};

		var delay = parseInt(cfg.delay_ms, 10) || 0;
		if (delay > 0) {
			setTimeout(go, delay);
		} else {
			go();
		}
	}

	function init() {
		loadConfigs();
		document.addEventListener('wpcf7mailsent', function (e) {
			var id = String(e && e.detail && e.detail.contactFormId);
			if (!id || !configs[id]) return;
			doRedirect(configs[id], e.detail);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
