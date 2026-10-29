/* @license
See the license.txt file for licensing information for third-party code that may be used in this file.
Relative to the scripts/ directory, the license.txt file is located at ../license.txt.
This file (or the corresponding source JS file) has been modified by Jonathan Hall and/or others.
This file (or the corresponding source JS file) was last modified 2020-11-25
*/
/*
	This file is from the submodule-builder repository.
*/

import getFn from 'lodash/get';
import isEmpty from 'lodash/isEmpty';
import forEach from 'lodash/forEach';
import times from 'lodash/times';
import flowRight from 'lodash/flowRight';
import HoverPure from './hover-options-pure';
// import ETBuilderStore from '../stores/et-builder-store';
// import ETBuilderActions from '../actions/et-builder-actions';


// WordPress's lodash has no lodash/fp, so its curried helpers are written out.
const hover        = '__hover';
const enabled      = '__hover_enabled';
const getHoverProp = field => getFn(field, 'hover');
const toBool       = a => !! a;
const emptyValue   = v => ('' === v ? undefined : v);
const get          = (k, o, d) => emptyValue(getFn(o, k, d)) || d;

/**
 * Returns the option suffix used for hover options.
 *
 * @returns {string}
 */
export const { hoverSuffix } = HoverPure;

/**
 * Returns the option enabled suffix used for hover options.
 *
 * @returns {string}
 */
export const { enabledSuffix } = HoverPure;

/**
 * Tells if the field does support hover options.
 *
 * @param {object} field
 * @returns {boolean}
 */
export const doesSupport = flowRight(toBool, getHoverProp);

/**
 * Tells if the field does support hover options tabs.
 *
 * @param {object} field
 * @returns {boolean}
 */
export const hasTabs = flowRight(value => 'tabs' === value, getHoverProp);

/**
 * Gets the base name of the field without the "__hover" or "__hover_enabled" suffix.
 *
 * @returns String.
 */
export const { getFieldBaseName } = HoverPure;

/**
 * Tells if the module option has hover options enabled.
 *
 * @param {string} setting
 * @param {object} props
 * @returns {boolean}
 */
export const { isEnabled } = HoverPure;

/**
 * Tells if the hover tabs are in hover state.
 *
 * @returns {boolean}
 */
// export const isHoverMode = () => !! ETBuilderStore.getHoverMode();
export const isHoverMode = () => false;

/**
 * Returns field name suffixed with `__hover`.
 *
 * @param {string} field
 * @returns {string}
 */
export const { getHoverField } = HoverPure;

/**
 * Returns field name suffixed with `__hover_enabled`.
 *
 * @param {string} field
 * @returns {string}
 */
export const { getHoverEnabledField } = HoverPure;

/**
 * Return the field suffixed with `hover` only the the hover mode it active.
 *
 * @param {string} field
 * @param {object} props
 * @returns {string}
 */
export const getHoverFieldOnHover = (field, props) => (isHoverMode() && isEnabled(field, props)
  ? getHoverField(field)
  : getFieldBaseName(field));

/**
 * Returns the field hover value.
 *
 * @param {string} field
 * @param {object} props
 * @param defaultValue
 * @returns {*}
 */
export const getValue = (field, props, defaultValue) => ((1 === getFn(props, 'hover_enabled')) && isEnabled(field, props)
  ? get(getHoverField(field), props, get(field, props, defaultValue))
  : defaultValue);

/**
 * Returns the field hover value only if hover tabs are active.
 *
 * @param {string} setting
 * @param {object} props
 * @param defaultValue
 * @returns {*}
 */
export const getValueOnHover = (setting, props, defaultValue) => (isHoverMode()
  ? getValue(setting, props, defaultValue)
  : defaultValue);

/**
 * Return the hover value or the normal values if hover values is not enabled or ie empty
 * Is the equivalent of `getValue(setting, props, props[setting])`.
 *
 * @param {string} setting
 * @param {object} props
 * @returns {*}
 */
export const getHoverOrNormal = (setting, props) => getValue(setting, props, get(getFieldBaseName(setting), props));

/**
 * Same as getHoverOrNormal, but checks if is hover mode state.
 *
 * @param setting
 * @param props
 * @returns {*}
 */
export const getHoverOrNormalOnHover = (setting, props) => getValueOnHover(setting, props, get(getFieldBaseName(setting), props));

/**
 * Sets setting hover enabled or disabled. `true` enables setting's hover, false disables.
 *
 * @param {boolean} state
 * @param {string} setting
 * @param {object} module
 */
// export const setFieldHover = (state, setting, module) => ETBuilderActions.moduleSettingsChange(module, getHoverEnabledField(setting), state ? 'on' : '');

/**
 * Same as getValue, but for values that needs to be split like margin, padding.
 * The methods takes care that if n-th split value is missing from hover value, it is inherited
 * from normal value.
 *
 * !!! Note: This method is useful only when you need the hover value to inherit from normal.
 *           It should not be used on saving hover value, as it will add redundancy.
 *
 * @param {string} setting
 * @param {object} props
 * @param {string} sep
 * @returns {*}
 */
export const getSplitValue = (setting, props, sep = '|') => {
  if (! isEnabled(setting, props)) {
    return;
  }

  const value = String(get(setting, props, '')).split(sep);
  const hover = String(getValue(setting, props, '')).split(sep);

  // Get the longest length
  const len = Math.max(value.length, hover.length);

  // Fill the hover value empty values with value's ones
  return times(len, i => get(i, hover, value[i])).join(sep);
};

/**
 * Works same way as getSplitValue but only when isHoverMode.
 *
 * @param {string} setting
 * @param {object} props
 * @param {string} sep
 * @returns {*}
 */
export const getSplitValueOnHover = (setting, props, sep = undefined) => (isHoverMode()
  ? getSplitValue(setting, props, sep)
  : undefined);

/**
 * Return the hover value of composite option.
 *
 * @param {string} field Field name.
 * @param {string} option Composite option name.
 * @param {object} props
 * @returns {*}
 */
export const getCompositeValue = (field, option, props) => ((1 === getFn(props, 'hover_enabled')) && isEnabled(option, props)
  ? get(getHoverField(field), props, get(field, props))
  : undefined);

/**
 * Similar to `getCompositeValue`, but returns the value only when is hover mode.
 *
 * @param {string} field Field name.
 * @param {string} option Composite option name.
 * @param {object} props
 * @returns {*}
 */
export const getCompositeValueOnHover = (field, option, props) => (isHoverMode()
  ? getCompositeValue(field, option, props)
  : undefined);
export const getCompositeFieldOnHover = (field, option, props) => (isHoverMode() && isEnabled(option, props)
  ? getHoverField(field)
  : getFieldBaseName(field))
;

/**
 * Generate hover fields based on current source fields.
 *
 * @since 3.24
 *
 * @param {object} fields Source fields object.
 *
 * @returns {object} Generated hover fields.
 */
export const getHoverFieldsDefinition = fields => {
  // Ensure fields exist.
  if (isEmpty(fields)) {
    return {};
  }

  const hoverFields = {};

  forEach(fields, field => {
    // Create hover and hover enabled field if current field support Hover.
    if (doesSupport(field)) {
      const fieldName         = getFn(field, 'name', '');
      const hoverField        = getHoverField(fieldName);
      const hoverEnabledField = getHoverEnabledField(fieldName);
      const defaultHoverField = {
        tab_slug: get(field, 'tab_slug', ''),
        toggle_slug: get(field, 'toggle_slug', ''),
        type: 'skip',
      };

      hoverFields[hoverField]        = { ...defaultHoverField, ...{ name: hoverField } };
      hoverFields[hoverEnabledField] = { ...defaultHoverField, ...{ name: hoverEnabledField } };
    }
  });

  return hoverFields;
};

export default {
  hoverSuffix,
  enabledSuffix,
  hasTabs,
  getFieldBaseName,
  isEnabled,
  doesSupport,
  isHoverMode,
  getHoverField,
  getHoverFieldOnHover,
  getHoverEnabledField,
  getValue,
  getValueOnHover,
  // setFieldHover,
  getSplitValue,
  getSplitValueOnHover,
  getHoverOrNormal,
  getHoverOrNormalOnHover,
  getCompositeValue,
  getCompositeValueOnHover,
  getCompositeFieldOnHover,
  getHoverFieldsDefinition,
};
