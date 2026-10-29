import React from 'react';
import { CssStyle, StyleContainer } from '@divi/module';

import designPresetsData from './design-presets.json';
import metadata from './module.json';

const presets = designPresetsData?.presets ?? [];
const schemaDefaults = metadata?.attributes?.module?.default?.advanced ?? {};

const getAttrValue = (attrs, path, breakpoint = 'desktop') => {
  const parts = path.split('.');
  let node = attrs;
  for (const part of parts) {
    if (!node || typeof node !== 'object' || !(part in node)) {
      return '';
    }
    node = node[part];
  }
  if (typeof node === 'string' || typeof node === 'number') {
    return node;
  }
  if (!node || typeof node !== 'object') {
    return '';
  }
  if (node[breakpoint]) {
    const bpValue = node[breakpoint];
    if (typeof bpValue === 'object' && 'value' in bpValue) {
      const value = bpValue.value;
      if (typeof value === 'boolean') {
        return value ? 'on' : 'off';
      }
      return value ?? '';
    }
    return bpValue ?? '';
  }
  if ('top' in node || 'right' in node || 'bottom' in node || 'left' in node) {
    return node;
  }
  if ('value' in node) {
    const value = node.value;
    if (typeof value === 'boolean') {
      return value ? 'on' : 'off';
    }
    return value ?? '';
  }
  return '';
};

const paddingToCss = (value) => {
  if (!value) return '';
  if (typeof value === 'object' && value !== null) {
    // Divi's spacing control saves { padding: {...} } or { margin: {...} }.
    if (value.padding || value.margin) {
      value = value.padding || value.margin;
    } else if (value.side && 'value' in value) {
      value = { [value.side]: value.value };
    }
    const t = value.top || '0px';
    const r = value.right || '0px';
    const b = value.bottom || '0px';
    const l = value.left || '0px';
    return `${t} ${r} ${b} ${l}`;
  }
  if (typeof value !== 'string') return '';
  if (value.includes('|')) {
    const parts = value.split('|').map(p => p.trim() || '0px');
    while (parts.length < 4) parts.push('0px');
    return parts.slice(0, 4).join(' ');
  }
  return value;
};

const ModuleStyles = ({
  attrs,
  settings,
  orderClass,
  mode,
  state,
  noStyleTag,
  elements,
}) => {
  // Backward compat: if module.advanced is empty, fall back to cf7.advanced (pre-migration data).
  const hasModuleAdv = attrs?.module?.advanced && Object.keys(attrs.module.advanced).length > 0;
  const effectiveAttrs = hasModuleAdv ? attrs : {
    ...attrs,
    module: { ...attrs?.module, advanced: attrs?.cf7?.advanced ?? {} },
  };

  const baseSelector = orderClass;

  const designPresetSlug = getAttrValue(effectiveAttrs, 'module.advanced.designPreset');
  const preset = designPresetSlug ? (presets.find((p) => p.slug === designPresetSlug) ?? null) : null;
  const getEffective = (attrKey, bp = 'desktop') => {
    const fromAttr = getAttrValue(effectiveAttrs, 'module.advanced.' + attrKey, bp);
    // Tablet and phone rules only print values set for that breakpoint.
    if (bp !== 'desktop') return fromAttr;
    const presetValue = preset?.styles?.[attrKey];
    const isDefault = JSON.stringify(fromAttr) === JSON.stringify(schemaDefaults?.[attrKey]?.desktop?.value);
    if (fromAttr !== '' && fromAttr !== undefined && !(presetValue !== undefined && isDefault)) return fromAttr;
    return presetValue ?? '';
  };

  const buildCss = (bp) => {
    let customCss = '';
    const formBg = getEffective('formBg', bp);
    const formPaddingVal = getEffective('formPadding', bp) || getAttrValue(effectiveAttrs, 'module.advanced.formPadding', bp);
    const formPadding = paddingToCss(formPaddingVal);
  
    if (formBg) {
      customCss += `${baseSelector} .cf7m-cf7-styler,${baseSelector} .dipe-cf7-styler{background-color:${formBg};}`;
    }
    if (formPadding && formPadding !== '0 0 0 0' && formPadding !== '0px 0px 0px 0px') {
      customCss += `${baseSelector} .cf7m-cf7-styler,${baseSelector} .dipe-cf7-styler{padding:${formPadding};}`;
    }

    const formHeaderBg = getEffective('formHeaderBg', bp);
    const formHeaderPaddingVal = getEffective('formHeaderPadding', bp) || getAttrValue(effectiveAttrs, 'module.advanced.formHeaderPadding', bp);
    const formHeaderPadding = paddingToCss(formHeaderPaddingVal);
    const formHeaderBottom = getEffective('formHeaderBottom', bp);
    const formHeaderImgBg = getEffective('formHeaderImgBg', bp);
    const formHeaderIconColor = getEffective('formHeaderIconColor', bp);
  
    if (formHeaderBg) {
      customCss += `${baseSelector} .cf7m-form-header-container,${baseSelector} .dipe-form-header-container,${baseSelector} .cf7m-cf7-header{background-color:${formHeaderBg};}`;
    }
    if (formHeaderPadding && formHeaderPadding !== '0 0 0 0' && formHeaderPadding !== '0px 0px 0px 0px') {
      customCss += `${baseSelector} .cf7m-form-header-container,${baseSelector} .dipe-form-header-container,${baseSelector} .cf7m-cf7-header{padding:${formHeaderPadding};}`;
    }
    if (formHeaderBottom && formHeaderBottom !== '0px' && formHeaderBottom !== '0') {
      customCss += `${baseSelector} .cf7m-form-header-container,${baseSelector} .dipe-form-header-container,${baseSelector} .cf7m-cf7-header{margin-bottom:${formHeaderBottom};}`;
    }
    if (formHeaderImgBg) {
      customCss += `${baseSelector} .cf7m-form-header-icon,${baseSelector} .dipe-form-header-icon,${baseSelector} .cf7m-form-header-image,${baseSelector} .dipe-form-header-image{background-color:${formHeaderImgBg};}`;
    }
    if (formHeaderIconColor) {
      customCss += `${baseSelector} .cf7m-form-header-icon span,${baseSelector} .dipe-form-header-icon span{color:${formHeaderIconColor};}`;
    }

    const fieldBg = getEffective('formBackgroundColor', bp);
    const fieldPaddingVal = getEffective('formFieldPadding', bp) || getAttrValue(effectiveAttrs, 'module.advanced.formFieldPadding', bp);
    const fieldPadding = paddingToCss(fieldPaddingVal);
    const fieldHeight = getEffective('formFieldHeight', bp);
    const fieldSpacing = getEffective('formFieldSpacing', bp);
    const fieldActiveColor = getEffective('formFieldActiveColor', bp);
    const fieldBorderColor = getEffective('formFieldBorderColor', bp);
    const fieldBorderWidth = getEffective('formFieldBorderWidth', bp);
    const fieldBorderRadius = getEffective('formFieldBorderRadius', bp);
    const fieldTextColor = getEffective('formFieldTextColor', bp);
    const fieldSelectors = `${baseSelector} .cf7m-cf7-styler input:not([type=submit]),${baseSelector} .cf7m-cf7-styler select,${baseSelector} .cf7m-cf7-styler textarea,${baseSelector} .dipe-cf7 input:not([type=submit]),${baseSelector} .dipe-cf7 select,${baseSelector} .dipe-cf7 textarea,${baseSelector} .cf7m-cf7-form-preview input,${baseSelector} .cf7m-cf7-form-preview textarea`;
  
    if (fieldBg) {
      customCss += `${fieldSelectors}{background-color:${fieldBg} !important;}`;
    }
    if (fieldPadding && fieldPadding !== '0 0 0 0') {
      customCss += `${fieldSelectors}{padding:${fieldPadding} !important;}`;
    }
    if (fieldHeight && fieldHeight !== '0px' && fieldHeight !== '0') {
      customCss += `${fieldSelectors}{height:${fieldHeight} !important;}`;
    }
    if (fieldBorderColor) {
      customCss += `${fieldSelectors}{border-color:${fieldBorderColor} !important;}`;
    }
    if (fieldBorderWidth && fieldBorderWidth !== '0px' && fieldBorderWidth !== '0') {
      customCss += `${fieldSelectors}{border-width:${fieldBorderWidth} !important;border-style:solid;}`;
    }
    if (fieldBorderRadius && fieldBorderRadius !== '0px' && fieldBorderRadius !== '0') {
      customCss += `${fieldSelectors}{border-radius:${fieldBorderRadius} !important;}`;
    }
    if (fieldTextColor) {
      customCss += `${fieldSelectors}{color:${fieldTextColor} !important;}`;
    }
    if (fieldActiveColor) {
      customCss += `${baseSelector} .dipe-cf7 input:not([type=submit]):focus,${baseSelector} .dipe-cf7 select:focus,${baseSelector} .dipe-cf7 textarea:focus,${baseSelector} .cf7m-cf7-form-preview input:focus,${baseSelector} .cf7m-cf7-form-preview textarea:focus{border-color:${fieldActiveColor} !important;}`;
    }
    if (fieldSpacing && fieldSpacing !== '0px' && fieldSpacing !== '0') {
      customCss += `${baseSelector} .dipe-cf7 .wpcf7 form>p,${baseSelector} .dipe-cf7 .wpcf7 form>div,${baseSelector} .dipe-cf7 .wpcf7 form>label,${baseSelector} .cf7m-cf7-form-preview__field{margin-bottom:${fieldSpacing} !important;}`;
    }

    const labelSpacing = getEffective('formLabelSpacing', bp);
    const labelColor = getEffective('formLabelColor', bp);
  
    if (labelSpacing && labelSpacing !== '0px' && labelSpacing !== '0') {
      customCss += `${baseSelector} .dipe-cf7-container .wpcf7-form-control:not(.wpcf7-submit),${baseSelector} .cf7m-cf7-form-preview input,${baseSelector} .cf7m-cf7-form-preview textarea{margin-top:${labelSpacing} !important;}`;
    }
    if (labelColor) {
      customCss += `${baseSelector} .dipe-cf7 label,${baseSelector} .cf7m-cf7-styler label,${baseSelector} .cf7m-cf7-form-preview label{color:${labelColor} !important;}`;
    }

    // Field Font Styles
    const fieldFontSize = getAttrValue(effectiveAttrs, 'module.advanced.fieldFontSize', bp);
    const fieldFontWeight = getAttrValue(effectiveAttrs, 'module.advanced.fieldFontWeight', bp);
    const fieldLineHeight = getAttrValue(effectiveAttrs, 'module.advanced.fieldLineHeight', bp);
    const fieldLetterSpacing = getAttrValue(effectiveAttrs, 'module.advanced.fieldLetterSpacing', bp);
    const fieldTextTransform = getAttrValue(effectiveAttrs, 'module.advanced.fieldTextTransform', bp);

    if (fieldFontSize) {
      customCss += `${fieldSelectors}{font-size:${fieldFontSize} !important;}`;
    }
    if (fieldFontWeight) {
      customCss += `${fieldSelectors}{font-weight:${fieldFontWeight} !important;}`;
    }
    if (fieldLineHeight) {
      customCss += `${fieldSelectors}{line-height:${fieldLineHeight} !important;}`;
    }
    if (fieldLetterSpacing) {
      customCss += `${fieldSelectors}{letter-spacing:${fieldLetterSpacing} !important;}`;
    }
    if (fieldTextTransform && fieldTextTransform !== 'none') {
      customCss += `${fieldSelectors}{text-transform:${fieldTextTransform} !important;}`;
    }

    // Label Font Styles
    const labelSelectors = `${baseSelector} .dipe-cf7 label,${baseSelector} .cf7m-cf7-styler label,${baseSelector} .cf7m-cf7-form-preview label`;
    const labelFontSize = getAttrValue(effectiveAttrs, 'module.advanced.labelFontSize', bp);
    const labelFontWeight = getAttrValue(effectiveAttrs, 'module.advanced.labelFontWeight', bp);
    const labelLineHeight = getAttrValue(effectiveAttrs, 'module.advanced.labelLineHeight', bp);
    const labelLetterSpacing = getAttrValue(effectiveAttrs, 'module.advanced.labelLetterSpacing', bp);
    const labelTextTransform = getAttrValue(effectiveAttrs, 'module.advanced.labelTextTransform', bp);

    if (labelFontSize) {
      customCss += `${labelSelectors}{font-size:${labelFontSize} !important;}`;
    }
    if (labelFontWeight) {
      customCss += `${labelSelectors}{font-weight:${labelFontWeight} !important;}`;
    }
    if (labelLineHeight) {
      customCss += `${labelSelectors}{line-height:${labelLineHeight} !important;}`;
    }
    if (labelLetterSpacing) {
      customCss += `${labelSelectors}{letter-spacing:${labelLetterSpacing} !important;}`;
    }
    if (labelTextTransform && labelTextTransform !== 'none') {
      customCss += `${labelSelectors}{text-transform:${labelTextTransform} !important;}`;
    }

    // Placeholder Color
    const placeholderColor = getAttrValue(effectiveAttrs, 'module.advanced.placeholderColor', bp);
    const placeholderSelectors = `${baseSelector} .cf7m-cf7-styler input::placeholder,${baseSelector} .cf7m-cf7-styler textarea::placeholder,${baseSelector} .dipe-cf7 input::placeholder,${baseSelector} .dipe-cf7 textarea::placeholder,${baseSelector} .cf7m-cf7-form-preview input::placeholder,${baseSelector} .cf7m-cf7-form-preview textarea::placeholder`;

    if (placeholderColor) {
      customCss += `${placeholderSelectors}{color:${placeholderColor} !important;}`;
    }

    // Header Title Font Styles
    const headerTitleSelectors = `${baseSelector} .dipe-form-header-title,${baseSelector} .cf7m-form-header-title`;
    const headerTitleFontSize = getAttrValue(effectiveAttrs, 'module.advanced.headerTitleFontSize', bp);
    const headerTitleFontWeight = getAttrValue(effectiveAttrs, 'module.advanced.headerTitleFontWeight', bp);
    const headerTitleLineHeight = getAttrValue(effectiveAttrs, 'module.advanced.headerTitleLineHeight', bp);
    const headerTitleLetterSpacing = getAttrValue(effectiveAttrs, 'module.advanced.headerTitleLetterSpacing', bp);
    const headerTitleTextTransform = getAttrValue(effectiveAttrs, 'module.advanced.headerTitleTextTransform', bp);
    const headerTitleTextColor = getAttrValue(effectiveAttrs, 'module.advanced.headerTitleTextColor', bp);

    if (headerTitleFontSize) {
      customCss += `${headerTitleSelectors}{font-size:${headerTitleFontSize} !important;}`;
    }
    if (headerTitleFontWeight) {
      customCss += `${headerTitleSelectors}{font-weight:${headerTitleFontWeight} !important;}`;
    }
    if (headerTitleLineHeight) {
      customCss += `${headerTitleSelectors}{line-height:${headerTitleLineHeight} !important;}`;
    }
    if (headerTitleLetterSpacing) {
      customCss += `${headerTitleSelectors}{letter-spacing:${headerTitleLetterSpacing} !important;}`;
    }
    if (headerTitleTextTransform && headerTitleTextTransform !== 'none') {
      customCss += `${headerTitleSelectors}{text-transform:${headerTitleTextTransform} !important;}`;
    }
    if (headerTitleTextColor) {
      customCss += `${headerTitleSelectors}{color:${headerTitleTextColor} !important;}`;
    }

    // Header Text Font Styles
    const headerTextSelectors = `${baseSelector} .dipe-form-header-text,${baseSelector} .cf7m-form-header-text`;
    const headerTextFontSize = getAttrValue(effectiveAttrs, 'module.advanced.headerTextFontSize', bp);
    const headerTextFontWeight = getAttrValue(effectiveAttrs, 'module.advanced.headerTextFontWeight', bp);
    const headerTextLineHeight = getAttrValue(effectiveAttrs, 'module.advanced.headerTextLineHeight', bp);
    const headerTextLetterSpacing = getAttrValue(effectiveAttrs, 'module.advanced.headerTextLetterSpacing', bp);
    const headerTextTextTransform = getAttrValue(effectiveAttrs, 'module.advanced.headerTextTextTransform', bp);
    const headerTextTextColor = getAttrValue(effectiveAttrs, 'module.advanced.headerTextTextColor', bp);

    if (headerTextFontSize) {
      customCss += `${headerTextSelectors}{font-size:${headerTextFontSize} !important;}`;
    }
    if (headerTextFontWeight) {
      customCss += `${headerTextSelectors}{font-weight:${headerTextFontWeight} !important;}`;
    }
    if (headerTextLineHeight) {
      customCss += `${headerTextSelectors}{line-height:${headerTextLineHeight} !important;}`;
    }
    if (headerTextLetterSpacing) {
      customCss += `${headerTextSelectors}{letter-spacing:${headerTextLetterSpacing} !important;}`;
    }
    if (headerTextTextTransform && headerTextTextTransform !== 'none') {
      customCss += `${headerTextSelectors}{text-transform:${headerTextTextTransform} !important;}`;
    }
    if (headerTextTextColor) {
      customCss += `${headerTextSelectors}{color:${headerTextTextColor} !important;}`;
    }

    const buttonBg = getEffective('buttonBg', bp);
    const buttonColor = getEffective('buttonColor', bp);
    const buttonPaddingVal = getEffective('buttonPadding', bp) || getAttrValue(effectiveAttrs, 'module.advanced.buttonPadding', bp);
    const buttonPadding = paddingToCss(buttonPaddingVal);
    const buttonBorderColor = getEffective('buttonBorderColor', bp);
    const buttonBorderWidth = getEffective('buttonBorderWidth', bp);
    const buttonBorderRadius = getEffective('buttonBorderRadius', bp);
    const buttonAlignment = getAttrValue(effectiveAttrs, 'module.advanced.buttonAlignment', bp);
    const buttonFullwidth = getAttrValue(effectiveAttrs, 'module.advanced.useFormButtonFullwidth', bp);
  
    const buttonSelectors = `${baseSelector} .dipe-cf7 input[type=submit],${baseSelector} .cf7m-cf7-styler input[type=submit],${baseSelector} .dipe-cf7 .cf7m-button,${baseSelector} .cf7m-cf7-styler .cf7m-button,${baseSelector} .cf7m-cf7-form-preview__submit,${baseSelector} .cf7m-cf7-form-preview button:not(.cf7m-star)`;
  
    if (buttonBg) {
      customCss += `${buttonSelectors}{background-color:${buttonBg} !important;}`;
    }
    if (buttonColor) {
      customCss += `${buttonSelectors}{color:${buttonColor} !important;}`;
    }
    if (buttonPadding && buttonPadding !== '0 0 0 0') {
      customCss += `${buttonSelectors}{padding:${buttonPadding} !important;}`;
    }
    if (buttonBorderColor) {
      customCss += `${buttonSelectors}{border-color:${buttonBorderColor} !important;}`;
    }
    if (buttonBorderWidth && buttonBorderWidth !== '0px' && buttonBorderWidth !== '0') {
      customCss += `${buttonSelectors}{border-width:${buttonBorderWidth} !important;border-style:solid;}`;
    }
    if (buttonBorderRadius && buttonBorderRadius !== '0px' && buttonBorderRadius !== '0') {
      customCss += `${buttonSelectors}{border-radius:${buttonBorderRadius} !important;}`;
    }
    if (buttonFullwidth === 'on') {
      customCss += `${buttonSelectors}{width:100% !important;}`;
    }
    if (buttonAlignment && buttonAlignment !== 'left') {
      const flexAlign = buttonAlignment === 'center' ? 'center' : 'flex-end';
      customCss += `${baseSelector} .cf7m-cf7-form-preview__fields,${baseSelector} .dipe-cf7 p:has(input[type=submit]){display:flex;flex-direction:column;align-items:${flexAlign};}`;
    }

    // Button Font Styles
    const buttonFontSize = getAttrValue(effectiveAttrs, 'module.advanced.buttonFontSize', bp);
    const buttonFontWeight = getAttrValue(effectiveAttrs, 'module.advanced.buttonFontWeight', bp);
    const buttonLineHeight = getAttrValue(effectiveAttrs, 'module.advanced.buttonLineHeight', bp);
    const buttonLetterSpacing = getAttrValue(effectiveAttrs, 'module.advanced.buttonLetterSpacing', bp);
    const buttonTextTransform = getAttrValue(effectiveAttrs, 'module.advanced.buttonTextTransform', bp);

    if (buttonFontSize) {
      customCss += `${buttonSelectors}{font-size:${buttonFontSize} !important;}`;
    }
    if (buttonFontWeight) {
      customCss += `${buttonSelectors}{font-weight:${buttonFontWeight} !important;}`;
    }
    if (buttonLineHeight) {
      customCss += `${buttonSelectors}{line-height:${buttonLineHeight} !important;}`;
    }
    if (buttonLetterSpacing) {
      customCss += `${buttonSelectors}{letter-spacing:${buttonLetterSpacing} !important;}`;
    }
    if (buttonTextTransform && buttonTextTransform !== 'none') {
      customCss += `${buttonSelectors}{text-transform:${buttonTextTransform} !important;}`;
    }

    const crCustomStyles = getEffective('crCustomStyles', bp) || getAttrValue(effectiveAttrs, 'module.advanced.crCustomStyles', bp);
  
    if (crCustomStyles === 'on') {
      const crSize = getEffective('crSize', bp) || getAttrValue(effectiveAttrs, 'module.advanced.crSize', bp);
      const crBorderSize = getEffective('crBorderSize', bp) || getAttrValue(effectiveAttrs, 'module.advanced.crBorderSize', bp);
      const crBg = getEffective('crBackgroundColor', bp);
      const crSelected = getEffective('crSelectedColor', bp);
      const crBorder = getEffective('crBorderColor', bp);
      const crLabel = getEffective('crLabelColor', bp);
    
      const crSelector = `${baseSelector} .dipe-cf7.dipe-cf7-cr .wpcf7-checkbox input[type="checkbox"] + span:before,${baseSelector} .dipe-cf7.dipe-cf7-cr .wpcf7-acceptance input[type="checkbox"] + span:before,${baseSelector} .dipe-cf7.dipe-cf7-cr .wpcf7-radio input[type="radio"] + span:before`;
    
      if (crSize || crBorderSize) {
        const w = crSize || '14px';
        const b = crBorderSize || '1px';
        customCss += `${crSelector}{width:${w} !important;height:${w} !important;border-width:${b} !important;}`;
      }
      if (crBg) {
        customCss += `${crSelector}{background-color:${crBg} !important;}`;
        customCss += `${baseSelector} .dipe-cf7.dipe-cf7-cr .wpcf7-radio input[type="radio"]:checked + span:before{box-shadow:inset 0 0 0 4px ${crBg} !important;}`;
      }
      if (crSelected) {
        customCss += `${baseSelector} .dipe-cf7.dipe-cf7-cr .wpcf7-checkbox input[type="checkbox"]:checked + span:before,${baseSelector} .dipe-cf7.dipe-cf7-cr .wpcf7-acceptance input[type="checkbox"]:checked + span:before{color:${crSelected} !important;}`;
        customCss += `${baseSelector} .dipe-cf7.dipe-cf7-cr .wpcf7-radio input[type="radio"]:checked + span:before{background-color:${crSelected} !important;}`;
      }
      if (crBorder) {
        customCss += `${crSelector}{border-color:${crBorder} !important;}`;
      }
      if (crLabel) {
        customCss += `${baseSelector} .dipe-cf7.dipe-cf7-cr .wpcf7-checkbox label,${baseSelector} .dipe-cf7.dipe-cf7-cr .wpcf7-radio label,${baseSelector} .dipe-cf7.dipe-cf7-cr .wpcf7-list-item-label{color:${crLabel} !important;}`;
      }
    }

    const msgPadding = getEffective('cf7MessagePadding', bp);
    const msgMarginTop = getEffective('cf7MessageMarginTop', bp);
    const msgAlign = getAttrValue(effectiveAttrs, 'module.advanced.cf7MessageAlignment', bp);
    const msgColor = getEffective('cf7MessageColor', bp);
    const msgBg = getEffective('cf7MessageBgColor', bp);
    const msgBorderHl = getEffective('cf7BorderHighlightColor', bp);
    const successColor = getEffective('cf7SuccessMessageColor', bp);
    const successBg = getEffective('cf7SuccessMessageBgColor', bp);
    const successBorder = getEffective('cf7SuccessBorderColor', bp);
    const errorColor = getEffective('cf7ErrorMessageColor', bp);
    const errorBg = getEffective('cf7ErrorMessageBgColor', bp);
    const errorBorder = getEffective('cf7ErrorBorderColor', bp);

    if (msgAlign) {
      customCss += `${baseSelector} .wpcf7 form .wpcf7-response-output,${baseSelector} .wpcf7 form span.wpcf7-not-valid-tip{text-align:${msgAlign};}`;
    }
    if (msgColor) {
      customCss += `${baseSelector} .dipe-cf7 span.wpcf7-not-valid-tip{color:${msgColor} !important;}`;
    }
    if (msgBg) {
      customCss += `${baseSelector} .dipe-cf7 span.wpcf7-not-valid-tip{background-color:${msgBg} !important;}`;
    }
    if (msgBorderHl) {
      customCss += `${baseSelector} .dipe-cf7 span.wpcf7-not-valid-tip{border:2px solid ${msgBorderHl} !important;}`;
    }
    if (msgPadding && msgPadding !== '0px' && msgPadding !== '0') {
      customCss += `${baseSelector} span.wpcf7-not-valid-tip{padding:${msgPadding} !important;}`;
    }
    if (msgMarginTop && msgMarginTop !== '0px' && msgMarginTop !== '0') {
      customCss += `${baseSelector} span.wpcf7-not-valid-tip{margin-top:${msgMarginTop} !important;}`;
    }
    if (successColor) {
      customCss += `${baseSelector} .wpcf7 form.sent .wpcf7-response-output{color:${successColor} !important;}`;
    }
    if (successBg) {
      customCss += `${baseSelector} .wpcf7 form.sent .wpcf7-response-output{background-color:${successBg} !important;}`;
    }
    if (successBorder) {
      customCss += `${baseSelector} .wpcf7 form.sent .wpcf7-response-output{border-color:${successBorder} !important;}`;
    }
    if (errorColor) {
      customCss += `${baseSelector} .wpcf7 form .wpcf7-response-output{color:${errorColor} !important;}`;
    }
    if (errorBg) {
      customCss += `${baseSelector} .wpcf7 form .wpcf7-response-output{background-color:${errorBg} !important;}`;
    }
    if (errorBorder) {
      customCss += `${baseSelector} .wpcf7 form .wpcf7-response-output{border-color:${errorBorder} !important;}`;
    }

    const proHeadingFontSize = getAttrValue(effectiveAttrs, 'module.advanced.proHeadingFontSize', bp);
    const proHeadingFontWeight = getAttrValue(effectiveAttrs, 'module.advanced.proHeadingFontWeight', bp);
    const proHeadingTextColor = getAttrValue(effectiveAttrs, 'module.advanced.proHeadingTextColor', bp);
    const proHeadingMargin = paddingToCss(getAttrValue(effectiveAttrs, 'module.advanced.proHeadingMargin', bp));
  
    if (proHeadingFontSize || proHeadingFontWeight || proHeadingTextColor || (proHeadingMargin && proHeadingMargin !== '0px 0px 0px 0px')) {
      const headingSelector = `${baseSelector} .cf7m-heading`;
      if (proHeadingFontSize) customCss += `${headingSelector}{font-size:${proHeadingFontSize} !important;}`;
      if (proHeadingFontWeight) customCss += `${headingSelector}{font-weight:${proHeadingFontWeight} !important;}`;
      if (proHeadingTextColor) customCss += `${headingSelector}{color:${proHeadingTextColor} !important;}`;
      if (proHeadingMargin && proHeadingMargin !== '0px 0px 0px 0px') customCss += `${headingSelector}{margin:${proHeadingMargin} !important;}`;
    }

    const proIconSize = getAttrValue(effectiveAttrs, 'module.advanced.proIconSize', bp);
    const proIconColor = getAttrValue(effectiveAttrs, 'module.advanced.proIconColor', bp);
    const proIconMargin = paddingToCss(getAttrValue(effectiveAttrs, 'module.advanced.proIconMargin', bp));
  
    if (proIconSize || proIconColor || (proIconMargin && proIconMargin !== '0px 0px 0px 0px')) {
      const iconSelector = `${baseSelector} .cf7m-icon`;
      if (proIconSize) customCss += `${iconSelector}{font-size:${proIconSize} !important;}`;
      if (proIconColor) customCss += `${iconSelector}{color:${proIconColor} !important;}`;
      if (proIconMargin && proIconMargin !== '0px 0px 0px 0px') customCss += `${iconSelector}{margin:${proIconMargin} !important;}`;
    }

    const proImageWidth = getAttrValue(effectiveAttrs, 'module.advanced.proImageWidth', bp);
    const proImageHeight = getAttrValue(effectiveAttrs, 'module.advanced.proImageHeight', bp);
    const proImageBorderColor = getAttrValue(effectiveAttrs, 'module.advanced.proImageBorderColor', bp);
    const proImageBorderWidth = getAttrValue(effectiveAttrs, 'module.advanced.proImageBorderWidth', bp);
    const proImageBorderRadius = getAttrValue(effectiveAttrs, 'module.advanced.proImageBorderRadius', bp);
    const proImagePadding = paddingToCss(getAttrValue(effectiveAttrs, 'module.advanced.proImagePadding', bp));
    const proImageMargin = paddingToCss(getAttrValue(effectiveAttrs, 'module.advanced.proImageMargin', bp));
  
    if (proImageWidth || proImageHeight || proImageBorderColor || proImageBorderWidth || proImageBorderRadius || proImagePadding || proImageMargin) {
      const imageSelector = `${baseSelector} .cf7m-image`;
      if (proImageWidth) customCss += `${imageSelector}{width:${proImageWidth} !important;}`;
      if (proImageHeight) customCss += `${imageSelector}{height:${proImageHeight} !important;object-fit:cover;}`;
      if (proImageBorderColor) customCss += `${imageSelector}{border-color:${proImageBorderColor} !important;}`;
      if (proImageBorderWidth && proImageBorderWidth !== '0px') customCss += `${imageSelector}{border-width:${proImageBorderWidth} !important;border-style:solid;}`;
      if (proImageBorderRadius && proImageBorderRadius !== '0px') customCss += `${imageSelector}{border-radius:${proImageBorderRadius} !important;}`;
      if (proImagePadding && proImagePadding !== '0px 0px 0px 0px') customCss += `${imageSelector}{padding:${proImagePadding} !important;}`;
      if (proImageMargin && proImageMargin !== '0px 0px 0px 0px') customCss += `${imageSelector}{margin:${proImageMargin} !important;}`;
    }

    const proRangeTrackColor = getAttrValue(effectiveAttrs, 'module.advanced.proRangeTrackColor', bp);
    const proRangeThumbColor = getAttrValue(effectiveAttrs, 'module.advanced.proRangeThumbColor', bp);
    const proRangeHeight = getAttrValue(effectiveAttrs, 'module.advanced.proRangeHeight', bp);
    const proRangeMargin = paddingToCss(getAttrValue(effectiveAttrs, 'module.advanced.proRangeMargin', bp));
  
    if (proRangeTrackColor || proRangeThumbColor || proRangeHeight || (proRangeMargin && proRangeMargin !== '0px 0px 0px 0px')) {
      const rangeInput = `${baseSelector} .cf7m-range-input`;
      // As specific as the form field rule, which also matches range inputs.
      const rangeField = `${baseSelector} .dipe-cf7 input.cf7m-range-input,${baseSelector} .cf7m-cf7-styler input.cf7m-range-input`;
      if (proRangeTrackColor) customCss += `${rangeField}{background:${proRangeTrackColor} !important;}${rangeInput}{--cf7m-range-track:${proRangeTrackColor};}`;
      if (proRangeHeight) customCss += `${rangeField}{height:${proRangeHeight} !important;padding-top:0 !important;padding-bottom:0 !important;}`;
      if (proRangeThumbColor) {
        customCss += `${rangeInput}::-webkit-slider-thumb{background:${proRangeThumbColor} !important;}`;
        customCss += `${rangeInput}::-moz-range-thumb{background:${proRangeThumbColor} !important;}`;
        customCss += `${baseSelector} .cf7m-range-slider, ${baseSelector} .cf7m-range-slider{--cf7m-range-thumb:${proRangeThumbColor};}`;
      }
      if (proRangeMargin && proRangeMargin !== '0px 0px 0px 0px') {
        customCss += `${baseSelector} .cf7m-range-slider, ${baseSelector} .cf7m-range-slider{margin:${proRangeMargin} !important;}`;
      }
    }

    const proStarColor = getAttrValue(effectiveAttrs, 'module.advanced.proStarColor', bp);
    const proStarActiveColor = getAttrValue(effectiveAttrs, 'module.advanced.proStarActiveColor', bp);
    const proStarSize = getAttrValue(effectiveAttrs, 'module.advanced.proStarSize', bp);
    const proStarMargin = paddingToCss(getAttrValue(effectiveAttrs, 'module.advanced.proStarMargin', bp));
  
    if (proStarColor || proStarActiveColor || proStarSize || (proStarMargin && proStarMargin !== '0px 0px 0px 0px')) {
      const starSel = `${baseSelector} .cf7m-star-rating .cf7m-star, ${baseSelector} .cf7m-star-rating .cf7m-star`;
      const activeSel = `${baseSelector} .cf7m-star-rating .cf7m-star.cf7m-star--on, ${baseSelector} .cf7m-star-rating .cf7m-star.filled, ${baseSelector} .cf7m-star-rating .cf7m-star.hover`;
      // Stars are masked shapes drawn from these variables.
      const ratingSel = `${baseSelector} .cf7m-star-rating`;
      if (proStarColor) customCss += `${starSel}{color:${proStarColor} !important;}${ratingSel}{--cf7m-star-off:${proStarColor} !important;}`;
      if (proStarActiveColor) customCss += `${activeSel}{color:${proStarActiveColor} !important;}${ratingSel}{--cf7m-star-on:${proStarActiveColor} !important;}`;
      if (proStarSize) customCss += `${starSel}{font-size:${proStarSize} !important;} ${baseSelector} .cf7m-star-rating .cf7m-star-svg{width:1em;height:1em;} ${ratingSel}{--cf7m-star-size:${proStarSize} !important;}`;
      if (proStarMargin && proStarMargin !== '0px 0px 0px 0px') {
        customCss += `${baseSelector} .cf7m-star-rating, ${baseSelector} .cf7m-star-rating{margin:${proStarMargin} !important;}`;
      }
    }

    return customCss;
  };

  let customCss = buildCss('desktop');
  [['tablet', 980], ['phone', 767]].forEach(([bp, maxWidth]) => {
    const breakpointCss = buildCss(bp);
    if (breakpointCss) {
      customCss += `@media only screen and (max-width:${maxWidth}px){${breakpointCss}}`;
    }
  });

  return (
    <StyleContainer mode={mode} state={state} noStyleTag={noStyleTag}>
      {elements.style({
        attrName: 'module',
        styleProps: {
          disabledOn: {
            disabledModuleVisibility: settings?.disabledModuleVisibility,
          },
        },
      })}
      <CssStyle
        selector={orderClass}
        attr={attrs?.css}
        cssFields={elements?.moduleMetadata?.customCssFields}
      />
      {customCss && <style>{customCss}</style>}
    </StyleContainer>
  );
};

export { ModuleStyles };
