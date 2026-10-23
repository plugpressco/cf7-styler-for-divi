/**
 * CF7 Mate – Phone Number Field
 *
 * Country selector with flag emoji, dial code prefix, and search.
 *
 * @package CF7_Mate\Pro\PhoneNumber
 * @since   3.0.0
 */

(function () {
	'use strict';

	/* ── Country data ─────────────────────────────────────────────── */

	var COUNTRIES = [
		{ iso2: 'AF', name: 'Afghanistan', dial: '+93' },
		{ iso2: 'AL', name: 'Albania', dial: '+355' },
		{ iso2: 'DZ', name: 'Algeria', dial: '+213' },
		{ iso2: 'AR', name: 'Argentina', dial: '+54' },
		{ iso2: 'AU', name: 'Australia', dial: '+61' },
		{ iso2: 'AT', name: 'Austria', dial: '+43' },
		{ iso2: 'BD', name: 'Bangladesh', dial: '+880' },
		{ iso2: 'BE', name: 'Belgium', dial: '+32' },
		{ iso2: 'BR', name: 'Brazil', dial: '+55' },
		{ iso2: 'CA', name: 'Canada', dial: '+1' },
		{ iso2: 'CL', name: 'Chile', dial: '+56' },
		{ iso2: 'CN', name: 'China', dial: '+86' },
		{ iso2: 'CO', name: 'Colombia', dial: '+57' },
		{ iso2: 'CU', name: 'Cuba', dial: '+53' },
		{ iso2: 'CZ', name: 'Czech Republic', dial: '+420' },
		{ iso2: 'DK', name: 'Denmark', dial: '+45' },
		{ iso2: 'DO', name: 'Dominican Republic', dial: '+1' },
		{ iso2: 'EC', name: 'Ecuador', dial: '+593' },
		{ iso2: 'EG', name: 'Egypt', dial: '+20' },
		{ iso2: 'ES', name: 'Spain', dial: '+34' },
		{ iso2: 'FI', name: 'Finland', dial: '+358' },
		{ iso2: 'FR', name: 'France', dial: '+33' },
		{ iso2: 'DE', name: 'Germany', dial: '+49' },
		{ iso2: 'GR', name: 'Greece', dial: '+30' },
		{ iso2: 'GT', name: 'Guatemala', dial: '+502' },
		{ iso2: 'HK', name: 'Hong Kong', dial: '+852' },
		{ iso2: 'HU', name: 'Hungary', dial: '+36' },
		{ iso2: 'IN', name: 'India', dial: '+91' },
		{ iso2: 'ID', name: 'Indonesia', dial: '+62' },
		{ iso2: 'IE', name: 'Ireland', dial: '+353' },
		{ iso2: 'IL', name: 'Israel', dial: '+972' },
		{ iso2: 'IT', name: 'Italy', dial: '+39' },
		{ iso2: 'JM', name: 'Jamaica', dial: '+1' },
		{ iso2: 'JP', name: 'Japan', dial: '+81' },
		{ iso2: 'KR', name: 'South Korea', dial: '+82' },
		{ iso2: 'MY', name: 'Malaysia', dial: '+60' },
		{ iso2: 'MX', name: 'Mexico', dial: '+52' },
		{ iso2: 'NL', name: 'Netherlands', dial: '+31' },
		{ iso2: 'NZ', name: 'New Zealand', dial: '+64' },
		{ iso2: 'NG', name: 'Nigeria', dial: '+234' },
		{ iso2: 'NO', name: 'Norway', dial: '+47' },
		{ iso2: 'PK', name: 'Pakistan', dial: '+92' },
		{ iso2: 'PE', name: 'Peru', dial: '+51' },
		{ iso2: 'PH', name: 'Philippines', dial: '+63' },
		{ iso2: 'PL', name: 'Poland', dial: '+48' },
		{ iso2: 'PT', name: 'Portugal', dial: '+351' },
		{ iso2: 'PR', name: 'Puerto Rico', dial: '+1' },
		{ iso2: 'RO', name: 'Romania', dial: '+40' },
		{ iso2: 'RU', name: 'Russia', dial: '+7' },
		{ iso2: 'SA', name: 'Saudi Arabia', dial: '+966' },
		{ iso2: 'SG', name: 'Singapore', dial: '+65' },
		{ iso2: 'ZA', name: 'South Africa', dial: '+27' },
		{ iso2: 'SE', name: 'Sweden', dial: '+46' },
		{ iso2: 'CH', name: 'Switzerland', dial: '+41' },
		{ iso2: 'TW', name: 'Taiwan', dial: '+886' },
		{ iso2: 'TH', name: 'Thailand', dial: '+66' },
		{ iso2: 'TR', name: 'Turkey', dial: '+90' },
		{ iso2: 'UA', name: 'Ukraine', dial: '+380' },
		{ iso2: 'AE', name: 'United Arab Emirates', dial: '+971' },
		{ iso2: 'GB', name: 'United Kingdom', dial: '+44' },
		{ iso2: 'US', name: 'United States', dial: '+1' },
		{ iso2: 'UY', name: 'Uruguay', dial: '+598' },
		{ iso2: 'VE', name: 'Venezuela', dial: '+58' },
		{ iso2: 'VN', name: 'Vietnam', dial: '+84' },
	];

	/* ── Helpers ───────────────────────────────────────────────────── */

	function flagEmoji(iso2) {
		if (!iso2 || iso2.length !== 2) return '';
		return Array.from(iso2.toUpperCase())
			.map(function (ch) { return String.fromCodePoint(127397 + ch.charCodeAt(0)); })
			.join('');
	}

	function findCountry(iso2) {
		return (
			COUNTRIES.find(function (c) { return c.iso2 === iso2; }) ||
			COUNTRIES.find(function (c) { return c.iso2 === 'US'; })
		);
	}

	function escapeHtml(str) {
		var el = document.createElement('div');
		el.textContent = str;
		return el.innerHTML;
	}

	/* ── Per-field initializer ────────────────────────────────────── */

	function initPhoneField(root) {
		if (root.dataset.cf7mPhoneInit) return;
		root.dataset.cf7mPhoneInit = '1';

		var wrap    = root.querySelector('.cf7m-phone-wrap');
		var trigger = root.querySelector('.cf7m-phone-trigger');
		var input   = root.querySelector('.cf7m-phone-input');
		var hidden  = root.querySelector('.cf7m-phone-hidden');

		if (!wrap || !trigger || !input || !hidden) return;

		var selectedIso = (hidden.dataset.defaultCountry || 'US').toUpperCase();
		var dropdown    = null;
		var searchInput = null;
		var listEl      = null;

		/* Country selector */
		function getSelected() {
			return findCountry(selectedIso);
		}

		function syncHidden() {
			var val  = (input.value || '').trim().replace(/\s/g, '');
			var dial = getSelected().dial;
			hidden.value = val ? dial + ' ' + val : dial;
		}

		function closeDropdown() {
			trigger.setAttribute('aria-expanded', 'false');
			if (dropdown) dropdown.classList.add('cf7m-phone-dropdown--hidden');
		}

		function selectCountry(iso2) {
			var country = findCountry(iso2);
			if (!country) return;
			selectedIso = country.iso2;
			trigger.querySelector('.cf7m-phone-flag').textContent = flagEmoji(country.iso2);
			trigger.querySelector('.cf7m-phone-dial').textContent = country.dial;
			syncHidden();
			closeDropdown();
		}

		function renderOptions(query) {
			var q       = (query || '').toLowerCase().trim();
			var matches = q
				? COUNTRIES.filter(function (c) {
					return (
						c.name.toLowerCase().indexOf(q) !== -1 ||
						c.dial.indexOf(q) !== -1 ||
						c.iso2.toLowerCase().indexOf(q) !== -1
					);
				})
				: COUNTRIES.slice();

			listEl.innerHTML = '';

			matches.forEach(function (c) {
				var btn       = document.createElement('button');
				btn.type      = 'button';
				btn.className = 'cf7m-phone-option' + (c.iso2 === selectedIso ? ' cf7m-phone-option--selected' : '');
				btn.setAttribute('role', 'option');
				btn.dataset.iso2 = c.iso2;
				btn.innerHTML =
					'<span class="cf7m-phone-option-flag">' + flagEmoji(c.iso2) + '</span>' +
					'<span class="cf7m-phone-option-label">' + escapeHtml(c.name) + '</span>' +
					'<span class="cf7m-phone-option-dial">' + escapeHtml(c.dial) + '</span>';
				btn.addEventListener('click', function () { selectCountry(c.iso2); });
				listEl.appendChild(btn);
			});
		}

		function openDropdown() {
			trigger.setAttribute('aria-expanded', 'true');

			if (dropdown) {
				dropdown.classList.remove('cf7m-phone-dropdown--hidden');
				if (searchInput) {
					searchInput.value = '';
					searchInput.focus();
				}
				renderOptions('');
				return;
			}

			// Build dropdown DOM
			dropdown = document.createElement('div');
			dropdown.className = 'cf7m-phone-dropdown';
			dropdown.setAttribute('role', 'listbox');

			var searchWrap = document.createElement('div');
			searchWrap.className = 'cf7m-phone-search-wrap';

			searchInput = document.createElement('input');
			searchInput.type = 'text';
			searchInput.className = 'cf7m-phone-search';
			searchInput.placeholder = 'Search';
			searchInput.setAttribute('aria-label', 'Search country');
			searchWrap.appendChild(searchInput);

			listEl = document.createElement('div');
			listEl.className = 'cf7m-phone-list';
			listEl.setAttribute('role', 'list');

			dropdown.appendChild(searchWrap);
			dropdown.appendChild(listEl);

			var combo = root.querySelector('.cf7m-phone-combo');
			if (combo) combo.appendChild(dropdown);

			searchInput.addEventListener('input', function () { renderOptions(searchInput.value); });
			searchInput.addEventListener('keydown', function (e) {
				if (e.key === 'Escape') closeDropdown();
			});

			renderOptions('');
			dropdown.classList.remove('cf7m-phone-dropdown--hidden');
			searchInput.focus();
		}

		/* Events */
		trigger.addEventListener('click', function (e) {
			e.preventDefault();
			if (!dropdown || dropdown.classList.contains('cf7m-phone-dropdown--hidden')) {
				openDropdown();
			} else {
				closeDropdown();
			}
		});

		input.addEventListener('input', syncHidden);
		input.addEventListener('blur', syncHidden);

		document.addEventListener('click', function (e) {
			if (!root.contains(e.target)) closeDropdown();
		});

		// Set initial state
		var initial = getSelected();
		trigger.querySelector('.cf7m-phone-flag').textContent = flagEmoji(initial.iso2);
		trigger.querySelector('.cf7m-phone-dial').textContent = initial.dial;
		syncHidden();
	}

	/* ── Bootstrap ─────────────────────────────────────────────────── */

	function initAll() {
		document.querySelectorAll('.cf7m-phone-number').forEach(initPhoneField);
	}

	function boot() {
		initAll();

		// Re-init after CF7 AJAX events (form reset, validation, etc.)
		if (window.jQuery) {
			window.jQuery(document).on(
				'wpcf7mailsent wpcf7invalid wpcf7spam wpcf7mailfailed wpcf7submit',
				function () { setTimeout(initAll, 100); }
			);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
