/** Form-editor presentation only. Hidden controls retain their names and values. */
(function () {
    'use strict';
    const strings = window.cf7mEditor || {};

    function init() {
        const root = document.querySelector('.cf7m-panel-wrap');
        if (root && !root.dataset.enhanced) {
            root.dataset.enhanced = '1';
            const sections = Array.from(root.querySelectorAll('[data-cf7m-section]'));
            const nav = root.querySelector('.cf7m-editor-nav');
            const storageKey = 'cf7m_editor_section_' + root.dataset.formId;
            let current = '';
            try { current = sessionStorage.getItem(storageKey) || ''; } catch (error) { /* Storage is optional. */ }
            if (!sections.some(section => section.dataset.cf7mSection === current)) current = sections[0]?.dataset.cf7mSection;

            function select(id, focus) {
                sections.forEach(section => { section.hidden = section.dataset.cf7mSection !== id; });
                nav.querySelectorAll('button').forEach(button => {
                    if (button.dataset.section === id) button.setAttribute('aria-current', 'true');
                    else button.removeAttribute('aria-current');
                });
                try { sessionStorage.setItem(storageKey, id); } catch (error) { /* Storage is optional. */ }
                if (focus) { const title = sections.find(section => section.dataset.cf7mSection === id)?.querySelector('h3'); if (title) { title.tabIndex = -1; title.focus(); } }
            }

            sections.forEach(section => {
                const id = section.dataset.cf7mSection;
                section.id = 'cf7m-form-' + id;
                const heading = section.querySelector('.cf7m-feat__title');
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.section = id;
                button.setAttribute('aria-controls', section.id);
                const label = document.createElement('span');
                label.textContent = heading.textContent;
                const status = document.createElement('span');
                status.className = 'cf7m-editor-nav__status';
                button.append(label, status);
                button.addEventListener('click', () => select(id, true));
                nav.appendChild(button);

                const toggles = { responses: 'cf7m_save_to_db', scheduling: 'cf7m_schedule_enabled', redirect: 'cf7m_redirect_enabled', 'partial-save': 'cf7m_partial_save_enabled' };
                const toggle = section.querySelector('[name="' + toggles[id] + '"]');
                function update() {
                    const enabled = toggle ? toggle.checked : !!section.querySelector('tbody tr');
                    status.textContent = enabled ? strings.on : strings.off;
                    status.dataset.enabled = enabled ? 'true' : 'false';
                    if (toggle) {
                        const controlled = section.querySelector('[data-cf7m-dependent]');
                        if (controlled) controlled.hidden = !enabled;
                        section.classList.toggle('is-enabled', enabled);
                    }
                }
                if (toggle) {
                    const label = toggle.closest('label');
                    const enableRow = label.closest('.cf7m-feat-row');
                    label.classList.add('cf7m-editor-enable');
                    section.querySelector('.cf7m-feat__header').appendChild(label);
                    if (enableRow) enableRow.remove();
                    if (id === 'redirect' || id === 'scheduling') section.querySelector('.cf7m-feat__body').setAttribute('data-cf7m-dependent', '');
                    toggle.classList.add('cf7m-editor-switch');
                    toggle.setAttribute('role', 'switch');
                    toggle.addEventListener('change', update);
                }
                update();
                const body = section.querySelector('tbody');
                if (body) {
                    const table = body.closest('table');
                    const empty = document.createElement('p');
                    empty.className = 'cf7m-rules-empty';
                    empty.textContent = id === 'routing' ? strings.routingEmpty : strings.redirectEmpty;
                    table.before(empty);
                    function updateRows() {
                        const rows = Array.from(body.querySelectorAll('tr'));
                        table.hidden = rows.length === 0;
                        empty.hidden = rows.length > 0;
                        const labels = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
                        rows.forEach(row => Array.from(row.cells).forEach((cell, index) => {
                            cell.dataset.label = labels[index] || '';
                            const input = cell.querySelector('input,select');
                            if (input) input.setAttribute('aria-label', labels[index]);
                            if (input?.name === 'cf7m_route_field[]' || input?.name === 'cf7m_redirect_field[]') input.setAttribute('list', 'cf7m-form-fields');
                        }));
                        update();
                    }
                    updateRows();
                    new MutationObserver(updateRows).observe(body, { childList: true });
                }
            });
            root.querySelector('.cf7m-editor-sidebar').hidden = sections.length === 0;
            root.querySelector('.cf7m-editor-empty').hidden = sections.length > 0;
            select(current, false);
            root.addEventListener('change', () => { root.querySelector('.cf7m-editor-save-status').textContent = strings.unsaved; root.querySelector('.cf7m-editor-save-status').classList.add('is-dirty'); });
            root.addEventListener('input', () => { root.querySelector('.cf7m-editor-save-status').textContent = strings.unsaved; root.querySelector('.cf7m-editor-save-status').classList.add('is-dirty'); });
        }

    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
