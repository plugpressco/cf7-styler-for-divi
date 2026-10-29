import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';

import { ModuleStyles } from './styles';
import { moduleClassnames } from './module-classnames';

const { ModuleContainer } = window?.divi?.module ?? {};

/**
 * Resolve header icon for Visual Builder: Divi 5 icon-picker stores an object
 * { unicode, type, weight }; Divi 4 / legacy may store a string. Process via
 * Divi APIs when available so the icon renders correctly.
 */
function resolveHeaderIcon(value) {
  if (value == null || value === '') return null;
  const processD5 = window?.divi?.iconLibrary?.processFontIcon;
  const processD4 = window?.ET_Builder?.API?.Utils?.processFontIcon;
  if (typeof value === 'object' && value.unicode != null) {
    if (typeof processD5 === 'function') return processD5(value);
    const unicode = value.unicode;
    const decoded =
      typeof unicode === 'string' && /^&#x[0-9a-f]+;$/i.test(unicode)
        ? String.fromCodePoint(parseInt(unicode.replace(/^&#x|;/gi, ''), 16))
        : unicode;
    const fontFamily = value.type === 'fa' ? 'FontAwesome' : 'ETmodules';
    return (
      <span className="et-pb-icon" style={{ fontFamily }} aria-hidden>
        {decoded}
      </span>
    );
  }
  if (typeof value === 'string') {
    if (typeof processD4 === 'function') return processD4(value);
    // Divi 4 saved "unicode||type||weight"; converted modules keep it.
    const [unicode, type, weight] = value.split('||');
    if (type !== undefined) return resolveHeaderIcon({ unicode, type, weight });
    return value;
  }
  return null;
}

function getFormPreviewUrl(formId) {
  const root =
    typeof window !== 'undefined' && window.wp?.apiFetch?.root
      ? window.wp.apiFetch.root
      : (typeof window !== 'undefined' ? window.location.origin : '') + '/wp-json';
  return `${root}/cf7-styler/v1/form-preview?id=${encodeURIComponent(formId)}`;
}

const CF7StylerEdit = (props) => {
  const { attrs: attrsProp, id, name, elements } = props;
  const attrs = attrsProp ?? props?.attributes ?? {};

  const [formHtml, setFormHtml] = useState('');
  const [formLoading, setFormLoading] = useState(false);
  const [formError, setFormError] = useState(false);

  // Check module.advanced first, fall back to old cf7.advanced for pre-migration modules.
  const adv = attrs?.module?.advanced ?? attrs?.cf7?.advanced;
  const formIdRaw =
    adv?.formId?.desktop?.value ??
    adv?.formId?.value ??
    adv?.formId;
  const formId = formIdRaw !== undefined && formIdRaw !== null ? String(formIdRaw) : '0';
  const formIdNum = formId === '0' || formId === '' ? 0 : parseInt(formId, 10);

  useEffect(() => {
    if (!formIdNum) {
      setFormHtml('');
      setFormError(false);
      return;
    }
    let cancelled = false;
    setFormLoading(true);
    setFormError(false);
    const url = getFormPreviewUrl(formIdNum);
    const headers = {};
    if (typeof window !== 'undefined' && window.wpApiSettings?.nonce) {
      headers['X-WP-Nonce'] = window.wpApiSettings.nonce;
    }
    fetch(url, { credentials: 'include', headers })
      .then((res) =>
        res.json().then((data) => ({ ok: res.ok, status: res.status, data }))
      )
      .then(({ ok, data }) => {
        if (cancelled) return;
        const isError =
          !ok ||
          (data &&
            (data.code === 'rest_forbidden' || data.code === 'missing_id' || data.code === 'invalid_id'));
        if (isError) {
          setFormError(true);
          setFormHtml('');
        } else {
          setFormHtml(data?.html ?? '');
        }
        setFormLoading(false);
      })
      .catch(() => {
        if (!cancelled) {
          setFormError(true);
          setFormHtml('');
          setFormLoading(false);
        }
      });
    return () => {
      cancelled = true;
    };
  }, [formIdNum]);

  const useFormHeader = adv?.useFormHeader?.desktop?.value === 'on';
  const formHeaderTitle = adv?.formHeaderTitle?.desktop?.value || '';
  const formHeaderText = adv?.formHeaderText?.desktop?.value || '';
  const useIcon = adv?.useIcon?.desktop?.value === 'on';
  const headerIconRaw = adv?.headerIcon?.desktop?.value;
  const headerIconResolved = resolveHeaderIcon(headerIconRaw);
  const hasHeaderIcon =
    headerIconResolved != null ||
    (headerIconRaw && (typeof headerIconRaw === 'string' ? headerIconRaw : headerIconRaw?.unicode));
  const headerImage = adv?.headerImage?.desktop?.value || '';
  const buttonFullwidth = adv?.useFormButtonFullwidth?.desktop?.value === 'on';
  const buttonAlignment = adv?.buttonAlignment?.desktop?.value || 'left';
  const crCustomStyles = adv?.crCustomStyles?.desktop?.value === 'on';

  if (!ModuleContainer) {
    return null;
  }

  const buttonClass = buttonFullwidth ? 'fullwidth' : buttonAlignment;
  const containerClasses = `dipe-cf7-container dipe-cf7-button-${buttonClass} cf7m-cf7-container cf7m-cf7-button-${buttonClass}`;
  const wrapperClasses = `dipe-cf7 dipe-cf7-styler cf7m-cf7 cf7m-cf7-styler${crCustomStyles ? ' dipe-cf7-cr cf7m-cf7-cr' : ''}`;

  const noForm = formId === '0' || !formId;

  return (
    <ModuleContainer
      attrs={attrs}
      id={id}
      name={name}
      elements={elements}
      stylesComponent={ModuleStyles}
      classnamesFunction={moduleClassnames}
    >
      <div className={containerClasses}>
        {useFormHeader && (formHeaderTitle || formHeaderText || hasHeaderIcon || headerImage) && (
          <div className="dipe-form-header-container cf7m-form-header-container cf7m-cf7-header">
            <div className="dipe-form-header cf7m-form-header">
              {useIcon && hasHeaderIcon && (
                <div className="dipe-form-header-icon cf7m-form-header-icon">
                  {headerIconResolved != null ? (
                    typeof headerIconResolved === 'string' ? (
                      <span className="et-pb-icon">{headerIconResolved}</span>
                    ) : (
                      headerIconResolved
                    )
                  ) : (
                    <span className="et-pb-icon">{typeof headerIconRaw === 'string' ? headerIconRaw : ''}</span>
                  )}
                </div>
              )}
              {!useIcon && headerImage && (
                <div className="dipe-form-header-image cf7m-form-header-image">
                  <img src={headerImage} alt="" />
                </div>
              )}
              {(formHeaderTitle || formHeaderText) && (
                <div className="dipe-form-header-info cf7m-form-header-info">
                  {formHeaderTitle && (
                    <h2 className="dipe-form-header-title cf7m-form-header-title cf7m-cf7-header__title">
                      {formHeaderTitle}
                    </h2>
                  )}
                  {formHeaderText && (
                    <div className="dipe-form-header-text cf7m-form-header-text cf7m-cf7-header__text">
                      {formHeaderText}
                    </div>
                  )}
                </div>
              )}
            </div>
          </div>
        )}

        <div className={wrapperClasses}>
          {noForm ? (
            <div className="cf7m-cf7-placeholder">
              <div className="cf7m-cf7-placeholder__icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" strokeWidth="2"/>
                  <line x1="7" y1="8" x2="17" y2="8" stroke="currentColor" strokeWidth="2"/>
                  <line x1="7" y1="12" x2="17" y2="12" stroke="currentColor" strokeWidth="2"/>
                  <line x1="7" y1="16" x2="12" y2="16" stroke="currentColor" strokeWidth="2"/>
                </svg>
              </div>
              <h3 className="cf7m-cf7-placeholder__title">
                {__('CF7 Styler', 'cf7-styler-for-divi')}
              </h3>
              <p className="cf7m-cf7-placeholder__text">
                {__('Select a Contact Form 7 form to display.', 'cf7-styler-for-divi')}
              </p>
            </div>
          ) : formLoading ? (
            <div className="cf7m-cf7-form-preview cf7m-cf7-form-preview--loading">
              <p className="cf7m-cf7-styler__placeholder">{__('Loading form…', 'cf7-styler-for-divi')}</p>
            </div>
          ) : formError ? (
            <div className="cf7m-cf7-form-preview cf7m-cf7-form-preview--error">
              <p className="cf7m-cf7-styler__placeholder">{__('Could not load form preview.', 'cf7-styler-for-divi')}</p>
            </div>
          ) : formHtml ? (
            <div
              className="cf7m-cf7-form-preview wpcf7 cf7m-cf7-form-preview--live"
              dangerouslySetInnerHTML={{ __html: formHtml }}
            />
          ) : (
            <div className="cf7m-cf7-form-preview">
              <p className="cf7m-cf7-styler__placeholder">{__('No form content.', 'cf7-styler-for-divi')}</p>
            </div>
          )}
        </div>
      </div>
    </ModuleContainer>
  );
};

export { CF7StylerEdit };
