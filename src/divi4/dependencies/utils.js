/* @license
See the license.txt file for licensing information for third-party code that may be used in this file.
Relative to the scripts/ directory, the license.txt file is located at ../license.txt.
*/
/*
	This file is from the submodule-builder repository.
*/

import assign from 'lodash/assign';
import pickBy from 'lodash/pickBy';
import pick from 'lodash/pick';
import get from 'lodash/get';
import isUndefined from 'lodash/isUndefined';
import unescape from 'lodash/unescape';
import isString from 'lodash/isString';
import isObject from 'lodash/isObject';
import isArray from 'lodash/isArray';
import isEmpty from 'lodash/isEmpty';
import forEach from 'lodash/forEach';
import includes from 'lodash/includes';
import now from 'lodash/now';
import _isNaN from 'lodash/isNaN';
import isNull from 'lodash/isNull';
import _replace from 'lodash/replace';
import map from 'lodash/map';
import range from 'lodash/range';
import keys from 'lodash/keys';
import findIndex from 'lodash/findIndex';
import find from 'lodash/find';
import take from 'lodash/take';
import head from 'lodash/head';
import last from 'lodash/last';
import flowRight from 'lodash/flowRight';
import isEqual from 'lodash/isEqual';
import forOwn from 'lodash/forOwn';
import has from 'lodash/has';
import isFunction from 'lodash/isFunction';
import partial from 'lodash/partial';
import cloneDeep from 'lodash/cloneDeep';
import indexOf from 'lodash/indexOf';
import clone from 'lodash/clone';
import omit from 'lodash/omit';
import fromPairs from 'lodash/fromPairs';
import memoize from 'lodash/memoize';
import reduce from 'lodash/reduce';
import mapValues from 'lodash/mapValues';
// import { top_window } from '@core-ui/utils/frame-helpers';

import nothingToSeeHereSprintf from './lib-sprintf';
import {getScrollbarWidth as getScrollbarWidthPure, sanitizedPreviously} from './lib-util';
//import ETBuilderSanitize from './et-builder-sanitize';
import HoverPure from './hover-options-pure';
import * as Pure from './pure';

/**
 * Dev use, un-comment out the areas you want to start logging
 * uncommenting this will overwrite dynamic setup configurred by ET_FB object.
 */
const ET_FB_SETTINGS = {
    // debug: true,
    // enableAllLogAreas: true,
    // enabledLogAreas: [
    //  'general',
    //  'store_action_obj',
    //  'store_emit',
    //  'warning',
    // ],
};

const rowSlugs = ['et_pb_row', 'et_pb_row_inner'];
const columnSlugs = ['et_pb_column', 'et_pb_column_inner'];

const toTextOrientation = function (key) {
    switch (key) {
        case 'force_left':
            return 'left';
        case 'justified':
            return 'justify';
        default:
            return key;
    }
};
const toRTLTextOrientation = (key) => (Utils.condition('is_rtl') && 'left' === key ? 'right' : key);

let SCROLLBAR_WIDTH;

class Cookies {
    postID = get(window.window.ETBuilderBackend, 'currentPage.id');

    path = get(window.window.ETBuilderBackend, 'cookie_path');

    secure(cookieWindow = window) {
        return 'https:' === cookieWindow.location.protocol;
    }

    getName(type, editor) {
        return `et-${type}-post-${this.postID}-${editor}`;
    }

    set(type, editor, value, cookieExpires = 5 * 60, cookieWindow = window) {
        cookieWindow.wpCookies.set(
            this.getName(type, editor),
            isUndefined(value) ? editor : value,
            cookieExpires,
            this.path,
            false,
            this.secure(cookieWindow)
        );
    }

    get(type, editor, cookieWindow = window) {
        return cookieWindow.wpCookies.get(this.getName(type, editor));
    }

    remove(type, editor, cookieWindow = window) {
        cookieWindow.wpCookies.remove(this.getName(type, editor), this.path, false, this.secure(cookieWindow));
    }
}

const cookies = new Cookies();

let _app_window = window;
let _app_document = _app_window.document;
let _is_mobile_device = null;
let has_localStorage = null;

window.jQuery(window).on('et_fb_init', () => {
    _app_window = window.ET_Builder.Frames.app;
    _app_document = _app_window.document;
});

/**
 * Global ET_FB Object, accessible from console.
 */
let Utils = {
    applyMixinsSafely(target, ...mixins) {
        if (isEmpty(mixins)) {
            return;
        }

        forEach(mixins, (mixin) => {
            forOwn(mixin, (value, member) => {
                if (isUndefined(target[member])) {
                    target[member] = isFunction(value) ? value.bind(target) : value;
                    return; // continue
                }

                target[member] = isFunction(value) ? partial(target[member], value.bind(target)) : target[member];
            });
        });

        return target;
    },

    /**
     * Semantical acknowledgement that {@link clone()} usage will not affect performance.
     *
     * @since 3.0.99
     *
     * @param {object} obj
     *
     * @returns {object}
     */
    intentionallyClone(obj) {
        return clone(obj);
    },

    /**
     * Semantical acknowledgement that {@link cloneDeep()} usage will not affect performance.
     *
     * @since 3.0.99
     *
     * @param {object} obj
     *
     * @returns {object}
     */
    intentionallyCloneDeep(obj) {
        return cloneDeep(obj);
    },

    sanitized_previously: sanitizedPreviously,

    log(msg, area, type) {
        if (!window.ET_FB.utils.debug()) {
            return false;
        }

        const _area = area || 'general';
        if (includes(window.ET_FB.utils.debugLogAreas(), _area)) {
            const _type = type || 'log';
            switch (_type) {
                case 'warn':
                    console.warn(msg);
                    break;
                case 'info':
                    console.info(msg);
                    break;
                default:
                    console.log(msg);
                    break;
            }
        }
    },
    sprintf: nothingToSeeHereSprintf,

    /**
     * @alias Pure.isJson
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    isJson: Pure.isJson,

    /**
     * @alias Pure.isValidHtml
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    isValidHtml: Pure.isValidHtml,
    getOS() {
        if (!isUndefined(window.navigator)) {
            if (navigator.appVersion.toLocaleLowerCase().indexOf('win') !== -1) {
                return 'Windows';
            }
            if (navigator.appVersion.toLocaleLowerCase().indexOf('mac') !== -1) {
                return 'MacOS';
            }
            if (navigator.appVersion.toLocaleLowerCase().indexOf('x11') !== -1) {
                return 'UNIX';
            }
            if (navigator.appVersion.toLocaleLowerCase().indexOf('linux') !== -1) {
                return 'Linux';
            }
        }

        return 'Unknown';
    },
    isModuleLocked(module, flattenedSO) {
        const moduleProps = module.props || module;
        const moduleAddress = get(moduleProps, 'address');

        let isLocked = Utils.isOn(get(moduleProps, 'attrs.locked')) || get(moduleProps, 'lockedParent');

        if (!isLocked) {
            const addressSequences = Utils.getModuleAddressSequence(moduleAddress);

            forEach(addressSequences, (addressSequence) => {
                const ancestorProps = find(flattenedSO, {address: addressSequence});
                if (Utils.isOn(get(ancestorProps, 'attrs.locked')) || get(ancestorProps, 'lockedParent')) {
                    isLocked = true;
                    return false; // Break the loop
                }
            });
        }

        return isLocked;
    },
    isModuleDeleted(props, flattenedSO, checkAncestor = true) {
        if (get(props, 'attrs._deleted')) {
            return true;
        }

        if (checkAncestor) {
            const addresses = get(props, 'address', '').split('.');

            if (addresses.length > 1) {
                const addressSequences = Utils.getModuleAddressSequence(addresses);

                let isAncestorModuleDeleted = false;

                forEach(addressSequences, (addressSequence) => {
                    const moduleAncestor = find(flattenedSO, {address: addressSequence});

                    if (get(moduleAncestor, 'attrs._deleted')) {
                        isAncestorModuleDeleted = true;
                    }
                });

                if (isAncestorModuleDeleted) {
                    return true;
                }
            }
        }

        return false;
    },

    /**
     * Returns section/row/column/module based on the Component type.
     *
     * @since 4.4.0
     *
     * @param component
     *
     * @returns {string}
     */
    getComponentType(component) {
        const props = component.props || component;
        const type = get(props, 'type');

        let componentType = 'module';

        switch (true) {
            case 'et_pb_section' === type:
                componentType = 'section';
                break;
            case includes(rowSlugs, type):
                componentType = 'row';
                break;
            case includes(columnSlugs, type):
                componentType = 'column';
                break;
            default:
        }

        return componentType;
    },

    getModuleSectionType(module, flattenedSO) {
        const moduleProps = module.props || module;
        const sectionAddress = head(get(moduleProps, 'address').split('.'));
        const section = find(flattenedSO, {address: sectionAddress});

        if (Utils.isOn(get(section, 'attrs.fullwidth'))) {
            return 'fullwidth';
        }

        if (Utils.isOn(get(section, 'attrs.specialty'))) {
            return 'specialty';
        }

        return 'regular';
    },
    getModuleAncestor(level, module, flattenedSO) {
        let ancestor;

        const moduleProps = module.props || module;
        const sectionType = Utils.getModuleSectionType(moduleProps, flattenedSO);
        const addressSequences = Utils.getModuleAddressSequence(get(moduleProps, 'address', ''));

        forEach(addressSequences, (addressSequence) => {
            const ancestorProps = find(flattenedSO, {address: addressSequence});
            const ancestorType = get(ancestorProps, 'type', '');
            switch (sectionType) {
                case 'specialty':
                    if (0 === ancestorType.replace('et_pb_', '').indexOf(level)) {
                        ancestor = ancestorProps;
                    }
                    break;

                default:
                    if (ancestorType.replace('et_pb_', '') === level) {
                        ancestor = ancestorProps;
                    }
                    break;
            }
        });

        return ancestor;
    },

    /**
     * Returns true/false for a module Type/Attribute.
     *
     * @since 4.4.0
     * @since 4.4.9 Added `removed` check to return non-existent component
     *
     * @param typeOrAttr
     * @param component
     *
     * @returns {boolean}
     */
    is(typeOrAttr, component) {
        const props = component.props || component;

        let bool = false;

        switch (typeOrAttr) {
            case 'section':
                bool = 'section' === getComponentType(props);
                break;
            case 'row':
                bool = 'row' === getComponentType(props);
                break;
            case 'row-inner':
                bool = 'et_pb_row_inner' === get(props, 'type');
                break;
            case 'column':
                bool = 'column' === getComponentType(props);
                break;
            case 'column-inner':
                bool = 'et_pb_column_inner' === get(props, 'type');
                break;
            case 'module':
                bool = 'module' === getComponentType(props) && !get(props, 'is_module_child');
                break;
            case 'fullwidth':
                bool = isOn(get(props, 'attrs.fullwidth'));
                break;
            case 'regular':
                bool =
                    'section' === getComponentType(props) &&
                    !isOn(get(props, 'attrs.fullwidth')) &&
                    !isOn(get(props, 'attrs.specialty'));
                break;
            case 'specialty':
                bool = isOn(get(props, 'attrs.specialty'));
                break;
            case 'disabled':
                bool = isOn(get(props, 'attrs.disabled'));
                break;
            case 'locked':
                bool = isOn(get(props, 'attrs.locked'));
                break;
            case 'removed':
                // Whether it's non-existent component
                bool = 'et-fb-removed-component' === get(props, 'component_path', '');
                break;
            default:
                bool = get(props, typeOrAttr);
        }

        return bool;
    },

    /**
     * @alias Pure.isOn
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    isOn: Pure.isOn,

    /**
     * @alias Pure.isOff
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    isOff: Pure.isOff,

    /**
     * @alias Pure.isOnOff
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    isOnOff: Pure.isOnOff,

    /**
     * @alias Pure.isYes
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    isYes: Pure.isYes,

    /**
     * @alias Pure.isNo
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    isNo: Pure.isNo,

    /**
     * @alias Pure.isDefault
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    isDefault: Pure.isDefault,
    isMobileDevice() {
        if (null === _is_mobile_device) {
            try {
                document.createEvent('TouchEvent');

                _is_mobile_device = this.$appWindow().width() <= 1024;
            } catch (e) {
                _is_mobile_device = false;
            }
        }

        return _is_mobile_device;
    },

    /**
     * @alias Pure.isFileExtension
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    isFileExtension: Pure.isFileExtension,

    isIEOrEdge() {
        return document.documentMode || window.StyleMedia;
    },
    isIE() {
        return this.$appWindow('body').hasClass('ie');
    },
    isBlockEditor() {
        return has(window, 'wp.blocks');
    },
    isRealMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    },
    getConditionalDefault(defaultValue, all_values, setting_resolver, isHover) {
        if (!isArray(defaultValue) || !isObject(get(defaultValue, '1'))) {
            // Single value, return it
            return defaultValue;
        }

        // default value depends on dependField
        let [dependField, multipleDefaults] = defaultValue;
        if (isHover) {
            dependField = HoverPure.getHoverField(dependField);
        }
        let defaultKey = setting_resolver ? setting_resolver.resolve(dependField) : get(all_values, dependField);

        if (isUndefined(defaultKey)) {
            // uh oh... looks like we can't get a value for dependField
            // let's just use the first default then and hope for the best
            defaultKey = keys(multipleDefaults)[0];
        }

        // Return the conditional default
        return get(multipleDefaults, defaultKey);
    },
    getValueOrConditionalDefault(key, all_values, defaults) {
        const value = get(all_values, key);
        if (!isUndefined(value) && value !== '') {
            // We have a value, return it
            return value;
        }
        return Utils.getConditionalDefault(get(defaults, key), all_values);
    },
    condition(conditionalTag) {
        return get(window.ETBuilderBackend, ['conditionalTags', conditionalTag]);
    },

    /**
     * @alias Pure.hasNumericValue
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    hasNumericValue: Pure.hasNumericValue,

    /**
     * @alias Pure.hasValue
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    hasValue: Pure.hasValue,

    /**
     * @alias Pure.get
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    get: Pure.get,

    /**
     * Check responsive value existence of responsive inputs (text/range/text margin) by passing its
     * *_last_edited value.
     *
     * @param string Saved *_last_edited attribute.
     * @param _last_edited
     * @returns Bool.
     */
    getResponsiveStatus(_last_edited) {
        const lastEdited = isString(_last_edited) ? _last_edited.split('|') : ['off', 'desktop'];

        return !isUndefined(lastEdited[0]) ? this.isOn(lastEdited[0]) : false;
    },

    /**
     * Get last edited mode based on *_last_edited attribute.
     *
     * @since 3.19.5
     *
     * @param {string}  _last_edited Attribute.
     * @returns {string} Last edited attribute.
     */
    getResponsiveLastMode(_last_edited) {
        const lastEdited = isString(_last_edited) ? _last_edited.split('|') : ['off', 'desktop'];

        return get(lastEdited, [1], 'desktop');
    },
    parseShortcode(shortcode, callback, shortcodeID) {
        const thisClass = this;
        const msie = document.documentMode;

        // Make sure the iframeID is unique.
        // It's possible that several requests will be started at the same time and now() will return the same value, so append random number.
        const iframeID = `et-fb-preview-${now()}-${Math.floor(Math.random() * 1000 + 1)}`;
        const previewUrl = `${window.ETBuilderBackend.site_url}/?et_pb_preview=true&et_pb_preview_nonce=${window.ETBuilderBackend.nonces.preview}&iframe_id=${iframeID}`;

        // Roll in the next lifecycle to get correct shortcode wrapper's width
        setTimeout(() => {
            const shortcodeWrapper = window.jQuery(`*[data-shortcode-id="${shortcodeID}"]`);
            const shortcodeWidth = shortcodeWrapper.length ? `${shortcodeWrapper.width()}px` : '100%';

            const $iframe = window.jQuery('<iframe />', {
                id: iframeID,
                src: previewUrl,
                style: `position: absolute; bottom: 0; left: 0; opacity: 0; pointer-events: none; width: ${shortcodeWidth}; height: 100%;`,
            });

            let hasRenderPage = false;
            let request_data = {
                et_pb_preview_nonce: window.ETBuilderBackend.nonces.preview,
                is_fb_preview: true,
                shortcode,
            };

            /**
             * Append iframe to body.
             * Component DOM hasn't ready at this point so it needs to be appended to <body>.
             */
            window.jQuery('body').append($iframe);

            /**
             * Load iframe's content into page.
             */
            $iframe.load(() => {
                /**
                 * Prevent unnecessary load.
                 */
                if (hasRenderPage) {
                    return;
                }

                const preview = document.getElementById(iframeID);

                /**
                 * IE9 below fix (They have postMessage, but it has to be in string).
                 */
                if (!isUndefined(msie) && msie < 10) {
                    request_data = JSON.stringify(request_data);
                }

                /**
                 * Pass shortcode structure to iFrame to be displayed.
                 */
                preview.contentWindow.postMessage(request_data, previewUrl);

                /**
                 * Flag to prevent unnecessary load.
                 */
                hasRenderPage = true;

                /**
                 * Create IE compatible event handler.
                 */
                const childListenerMethod = window.addEventListener ? 'addEventListener' : 'attachEvent';
                const childListener = window[childListenerMethod];
                const childListenerEvent = 'attachEvent' === childListenerMethod ? 'onmessage' : 'message';

                /**
                 * Listen to message from child window.
                 */
                childListener(
                    childListenerEvent,
                    (event) => {
                        if (
                            event.data.iframe_id === iframeID &&
                            isString(event.data.html) &&
                            thisClass.hasValue(event.data)
                        ) {
                            callback(event.data);

                            /**
                             * Remove event since the data has been passed to callback.
                             */
                            $iframe.remove();
                        }
                    },
                    false
                );
            });
        }, 0);
    },
    processFontIcon(fontIcon, isFontIconsDown) {
        if (isUndefined(fontIcon)) {
            return null;
        }

        const index = parseInt(fontIcon.replace(/[^0-9]/g, ''));
        const iconSymbols = isFontIconsDown ? window.ETBuilderBackend.fontIconsDown : window.ETBuilderBackend.fontIcons;

        if (null !== fontIcon.trim().match(/^%%/) && !isUndefined(iconSymbols[index])) {
            fontIcon = iconSymbols[index];
        }

        // Return null if empty so that can be used directly in render HTML attributes.
        return fontIcon ? window.jQuery.parseHTML(unescape(fontIcon))[0].nodeValue : null;
    },
    generateResponsiveCss(responsive_values, selector, css_property, additional_css) {
        if (isEmpty(responsive_values)) {
            return '';
        }

        const processed_css = [];

        forEach(responsive_values, (value, device) => {
            if ('' === value || 'undefined' === typeof value) {
                return;
            }

            const current_css = {
                selector,
                declaration: '',
                device,
            };
            const append_css = typeof additional_css !== 'undefined' && '' !== additional_css ? additional_css : ';';

            if (Array.isArray(value) && !isEmpty(value)) {
                forEach(value, (this_value, this_property) => {
                    if ('' === this_value) {
                        return;
                    }

                    current_css.declaration += `${this_property}:${this_value}${append_css}`;
                });
            } else {
                current_css.declaration = `${css_property}:${value}${append_css}`;
            }

            processed_css.push(current_css);
        });

        return processed_css;
    },

    /**
     * @alias Pure.generatePlaceholderCss
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    generatePlaceholderCss: Pure.generatePlaceholderCss,

    /**
     * @alias Pure.replaceCodeContentEntities
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    replaceCodeContentEntities: Pure.replaceCodeContentEntities,

    /**
     * @alias Pure.removeFancyQuotes
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    removeFancyQuotes: Pure.removeFancyQuotes,

    // replication of et_builder_process_range_value()
    processRangeValue(range, option_type) {
        if (isUndefined(range)) {
            return '';
        }

        const range_processed = 'string' === typeof range ? range.trim() : range;
        const range_digit = parseFloat(range_processed);
        let range_string = range_processed.toString().replace(range_digit, '');
        const option_type_processed = typeof option_type !== 'undefined' ? option_type : '';
        let result;

        if ('' === range_string) {
            range_string = 'line_height' === option_type_processed && 3 >= range_digit ? 'em' : 'px';
        }

        if (isNaN(range_digit)) {
            return '';
        }

        result = range_digit.toString() + range_string;

        return result;
    },

    /**
     * @alias Pure.getCorners
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    getCorners: Pure.getCorners,

    /**
     * @alias Pure.getCorner
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    getCorner: Pure.getCorner,

    /**
     * @alias Pure.getSpacing
     *
     * @deprecated
     *
     * @since 4.3 Deprecated
     */
    getSpacing: Pure.getSpacing,

    getBreakpoints() {
        return ['desktop', 'tablet', 'phone'];
    },
    getPrevBreakpoint: (name) => Utils.getBreakpoints()[indexOf(Utils.getBreakpoints(), name) - 1],
    getNextBreakpoint: (name) => Utils.getBreakpoints()[indexOf(Utils.getBreakpoints(), name) + 1],
    getPreviewModes() {
        return ['wireframe', 'zoom', 'desktop', 'tablet', 'phone'];
    },
    /*
  getGradient(args) {
    const defaultArgs = {
      type: window.ETBuilderBackend.defaults.backgroundOptions.type,
      direction: window.ETBuilderBackend.defaults.backgroundOptions.direction,
      radialDirection: window.ETBuilderBackend.defaults.backgroundOptions.radialDirection,
      colorStart: window.ETBuilderBackend.defaults.backgroundOptions.colorStart,
      colorEnd: window.ETBuilderBackend.defaults.backgroundOptions.colorEnd,
      startPosition: window.ETBuilderBackend.defaults.backgroundOptions.startPosition,
      endPosition: window.ETBuilderBackend.defaults.backgroundOptions.endPosition,
    };

    args = assign(defaultArgs, pickBy(args, this.hasValue));

    const direction     = 'linear' === args.type ? args.direction : `circle at ${args.radialDirection}`;
    const startPosition = ETBuilderSanitize.sanitizeInputUnit(args.startPosition, undefined, '%');
    const endPosition   = ETBuilderSanitize.sanitizeInputUnit(args.endPosition, undefined, '%');

    return `${args.type}-gradient(
      ${direction},
      ${args.colorStart} ${startPosition},
      ${args.colorEnd} ${endPosition}
    )`;
  },
  */
    removeClassNameByPrefix(prefix, el) {
        const $element = 'undefined' === typeof el ? window.jQuery('body') : window.jQuery(el);
        const domClasses = $element.attr('class');
        const regex = new RegExp(`${prefix}[^\\s]+`, 'g');

        if (isUndefined(domClasses)) {
            return;
        }

        const domClass = domClasses.replace(regex, '');

        $element.attr('class', window.jQuery.trim(domClass));
    },
    getKeyboardList(name) {
        let list;

        switch (name) {
            case 'sectionLayout':
                list = ['49', '50', '51'];
                break;
            case 'rowLayout':
                list = ['49', '50', '51', '52', '53', '54', '55', '56', '57', '48', '189'];
                break;
            case 'arrowDirections':
                list = [
                    '38', // up
                    '39', // right
                    '40', // down
                    '37', // left
                ];
                break;
            default:
                list = false;
                break;
        }

        return list;
    },
    getRowLayouts(type, layout) {
        let rowLayouts = 'et_pb_row' === type ? window.ETBuilderBackend.columnLayouts.regular : [];

        // Generate row layouts based on specialty layout
        if ('et_pb_row_inner' === type && !isUndefined(layout)) {
            const specialtyRowSpec = window.ETBuilderBackend.columnLayouts.specialty[layout];
            rowLayouts = map(range(specialtyRowSpec.columns), (unformattedColumnIndex) => {
                const columnIndex = unformattedColumnIndex + 1;
                return 1 === columnIndex ? '4_4' : map(range(columnIndex), () => `1_${columnIndex}`).join(',');
            });
        }

        return rowLayouts;
    },

    // Simplified font loading that handles user fonts and Google fonts.
    // Unlike BB's multi-function approach, this single function covers the core use cases.
    maybeLoadFont(font_name, font_option) {
        const $head = this.$topWindow('head').add(window.jQuery('head'));
        const fonts_data = window.ETBuilderBackend.et_builder_fonts_data;
        const user_fonts = window.ETBuilderBackend.customFonts;
        const {removedFonts} = window.ETBuilderBackend;
        const {useGoogleFonts} = window.ETBuilderBackend;
        const websafeFonts = keys(window.ETBuilderBackend.websafeFonts);
        const font_styles =
            typeof fonts_data[font_name] !== 'undefined' && typeof fonts_data[font_name].styles !== 'undefined'
                ? `:${fonts_data[font_name].styles}`
                : '';
        const subset =
            typeof fonts_data[font_name] !== 'undefined' && typeof fonts_data[font_name].character_set !== 'undefined'
                ? `&${fonts_data[font_name].character_set}`
                : '';
        const requestFontName = get(removedFonts, `${font_name}.parent_font`, false)
            ? removedFonts[font_name].parent_font
            : font_name;
        const fontClass = this.fontnameToClass(font_name);

        // load user font or google font
        if (!isUndefined(user_fonts[font_name])) {
            if ($head.find(`style#${fontClass}`).length) {
                return;
            }

            const savedFontFiles = get(user_fonts[font_name], 'font_url', '');
            let fontSrc = isString(savedFontFiles) ? `src: url('${savedFontFiles}');` : '';

            // generate the @font-face src from the uploaded font files
            // all the font formats have to be added in certain order to provide the best browser support
            if ('' === fontSrc && !isString(savedFontFiles)) {
                const allFontFiles = {
                    eot: {
                        url: get(savedFontFiles, 'eot', false),
                        format: 'embedded-opentype',
                    },
                    woff2: {
                        url: get(savedFontFiles, 'woff2', false),
                        format: 'woff2',
                    },
                    woff: {
                        url: get(savedFontFiles, 'woff', false),
                        format: 'woff',
                    },
                    ttf: {
                        url: get(savedFontFiles, 'ttf', false),
                        format: 'truetype',
                    },
                    otf: {
                        url: get(savedFontFiles, 'otf', false),
                        format: 'opentype',
                    },
                };

                if (allFontFiles.eot.url) {
                    fontSrc = `src: url('${allFontFiles.eot.url}'); src: url('${allFontFiles.eot.url}?#iefix') format('embedded-opentype')`;
                }

                forEach(allFontFiles, (fontData, extension) => {
                    if ('eot' !== extension && fontData.url) {
                        fontSrc += '' === fontSrc ? 'src: ' : ', ';
                        fontSrc += `url('${fontData.url}') format('${fontData.format}')`;
                    }
                });
            }

            $head.append(`<style id="${fontClass}">@font-face{font-family:"${font_name}"; ${fontSrc};}</style>`);
        } else {
            if ($head.find(`link#${fontClass}`).length || !useGoogleFonts || includes(websafeFonts, font_name)) {
                return;
            }

            // Convert google font name for URL
            font_name = requestFontName.replace(/ /g, '+');

            $head.append(
                `<link id="${fontClass}" href="//fonts.googleapis.com/css?family=${font_name}${font_styles}${subset}" rel="stylesheet" type="text/css" />`
            );
        }
    },
    fontnameToClass(option_value) {
        return `et_gf_${option_value.replace(/ /g, '_').toLowerCase()}`;
    },
    callWindow(fun, ...args) {
        if (has(window, fun)) {
            get(window, fun)(...args);
        }
    },
    $appDocument(selector = this.appDocument()) {
        return _app_window.window.jQuery(selector);
    },
    $appWindow(selector = this.appWindow()) {
        return _app_window.window.jQuery(selector);
    },
    $topDocument(selector = this.topDocument()) {
        return this.topWindow().window.jQuery(selector);
    },
    $topWindow(selector = this.topWindow()) {
        return this.topWindow().window.jQuery(selector);
    },
    $TBViewport() {
        return this.$topWindow('.et-common-visual-builder:first');
    },
    $TBScrollTarget() {
        return this.$TBViewport().find('#et-fb-app');
    },
    topViewportWidth() {
        return this.isTB() ? this.$TBViewport().width() : this.$topWindow().width();
    },
    topViewportHeight() {
        return this.isTB() ? this.$TBViewport().height() : this.$topWindow().height();
    },
    viewportScrollTop() {
        const mode = this.appWindow().ET_Builder.API.State.View_Mode;

        if (this.isTB()) {
            return this.$TBScrollTarget().scrollTop();
        }

        return this.isBFB() || mode.isPhone() || mode.isTablet()
            ? this.$topWindow().scrollTop()
            : this.$appWindow().scrollTop();
    },

    /**
     * Returns top window width
     * If is BFB, then is not used the actual window width, but container where builder is rendered.
     *
     * @returns {number}
     */
    getTopWindowWidth() {
        return Utils.isBFB() ? Utils.$topWindow('#et_pb_layout').width() : Utils.$topWindow().width();
    },
    appDocument() {
        return _app_document;
    },
    appWindow() {
        return _app_window;
    },
    topDocument() {
        return this.topWindow().document;
    },
    topWindow() {
        return window;
        // return top_window;
    },
    hasFixedHeader() {
        return includes(['fixed', 'absolute'], window.jQuery('header').css('position'));
    },
    isElementInViewport(element) {
        if (element.length > 0) {
            element = element[0];
        }

        if (isEmpty(element)) {
            return;
        }

        const _window = element.ownerDocument ? element.ownerDocument.defaultView : element.defaultView;
        const $window = _window.window.jQuery && _window.window.jQuery(_window);
        const iframe_rect = _window.frameElement ? _window.frameElement.getBoundingClientRect() : {};

        if (!$window) {
            return;
        }

        let {top, height} = element.getBoundingClientRect();

        if (iframe_rect.top) {
            top -= Math.abs(iframe_rect.top);
        }

        const _window_height = $window.height();
        let offset = 0;

        if (this.hasFixedHeader()) {
            offset = window.jQuery('header').height();
        }

        return top <= _window_height && top >= offset;
    },
    getCommentsMarkup(headerLevel, formTitleLevel) {
        const processedHeaderLevel = isUndefined(headerLevel) ? 'h1' : headerLevel;
        let commentsMarkup = window.ETBuilderBackend.commentsModuleMarkup;

        // update header level in comments markup if needed
        if ('h1' !== headerLevel) {
            commentsMarkup = commentsMarkup.replace('<h1', `<${headerLevel}`);
            commentsMarkup = commentsMarkup.replace('</h1>', `</${headerLevel}>`);
        }

        // Update form title heading level in comments markup if needed.
        if ('h3' !== formTitleLevel) {
            // Maybe there is a case when users set same heading level for header and
            // form title element. Uses regex to fetch form title correctly.
            const formTitleRegex = new RegExp('<h3 id="reply-title" class="comment-reply-title">(.*?)</h3>', 'g');
            commentsMarkup = _replace(commentsMarkup, formTitleRegex, (matchedStrings) => {
                matchedStrings = matchedStrings.replace('<h3', `<${formTitleLevel}`);
                matchedStrings = matchedStrings.replace('</h3>', `</${formTitleLevel}>`);
                return matchedStrings;
            });
        }

        return commentsMarkup;
    },
    decodeHtmlEntities(value) {
        value = !isString(value) ? '' : value;

        return value.replace(/&#(\d+);/g, (match, dec) => String.fromCharCode(dec));
    },
    isLimitedMode() {
        return this.condition('is_limited_mode');
    },
    isBFB() {
        return this.condition('is_bfb');
    },
    isTB() {
        return this.condition('is_tb');
    },
    isLB() {
        return this.condition('is_layout_block');
    },
    isFB() {
        return !this.isBFB() && !this.isTB() && !this.isLB();
    },

    /**
     * Get window scroll location based on preview mode & builder type.
     *
     * @since 3.21.1
     * @param previewMode
     * @param {string} preview Mode.
     * @returns {string} App|top.
     */
    getWindowScrollLocation(previewMode) {
        return !this.condition('is_bfb') && includes(['wireframe', 'desktop'], previewMode) ? 'app' : 'top';
    },
    hasBodyMargin() {
        return window.jQuery('#et_pb_root').hasClass('et-fb-has-body-margin');
    },

    /**
     * Fix a slider's container min-height and image size/position via et_fix_slider_height();.
     *
     * @param {window.jQuery} $slider
     */
    fixSliderHeight($slider) {
        setTimeout(() => window.et_fix_slider_height($slider), 600);
    },

    /**
     * Fix broken animated and waypoint module in builder rendered as module content area. This builder content arrives
     * late as string of HTML and need to be reinitialized.
     *
     * @param {window.jQuery} $slider
     */
    fixBuilderContent($slider) {
        setTimeout(() => {
            // load waypoint modules such as counters and animated images
            $slider.find('.et-waypoint, .et_pb_circle_counter, .et_pb_number_counter').each(function () {
                const $waypointModule = window.jQuery(this);

                if ($waypointModule.hasClass('et_pb_circle_counter')) {
                    Utils.appWindow().et_pb_reinit_circle_counters($waypointModule);

                    if (!isUndefined($waypointModule.data('easyPieChart'))) {
                        $waypointModule.data('easyPieChart').update($waypointModule.data('number-value'));
                    }
                }

                if ($waypointModule.hasClass('et_pb_number_counter')) {
                    Utils.appWindow().et_pb_reinit_number_counters($waypointModule);

                    if (!isUndefined($waypointModule.data('easyPieChart'))) {
                        $waypointModule.data('easyPieChart').update($waypointModule.data('number-value'));
                    }
                }

                if ($waypointModule.find('.et_pb_counter_amount').length > 0) {
                    $waypointModule.find('.et_pb_counter_amount').each(function () {
                        Utils.appWindow().et_bar_counters_init(window.jQuery(this));
                    });
                }

                $waypointModule.css({opacity: '1'});
            });

            // Initialize parallax background
            if ($slider.find('.et_parallax_bg').length) {
                $slider.find('.et_parallax_bg').each(function () {
                    window.et_pb_parallax_init(window.jQuery(this));
                });
            }

            Utils.appWindow().et_reinit_waypoint_modules();

            if (!isUndefined(window.et_shortcodes_init)) {
                Utils.appWindow().et_shortcodes_init($slider);
            }

            Utils.$appWindow().trigger('resize');
        }, 0);
    },
    triggerResizeForUIUpdate() {
        clearTimeout(window.ETBuilderFauxResize);
        window.ETBuilderFauxResize = setTimeout(() => {
            const thisClass = this;
            window.jQuery(window).trigger('resize');
            Utils.callWindow('et_fix_page_container_position');

            // Automatically blur if WordPress' main tinyMCE iframe is intentionally focused in BFB to avoid
            // keydown event which triggers builder shortcut not being tracked
            if (thisClass.condition('is_bfb')) {
                // Wait for 200ms. It generally took 200ms or more from any event to unintentional focus state of #content_ifr
                setTimeout(() => {
                    if (window.jQuery(document.activeElement).is('iframe')) {
                        window.jQuery(document.activeElement).trigger('blur');
                    }
                }, 200);
            }
        }, 200);
    },

    /**
     * Retrieve assigned heading level from module attributes.
     *
     * @param Module Props.
     * @param props
     * @param defaultLevel
     * @returns String HTML heading level. `h2` by default.
     */
    getHeadingLevel(props, defaultLevel = 'h2') {
        const {parentAttrs} = props;
        const {attrs} = props;

        if (this.hasValue(attrs.header_level)) {
            return attrs.header_level;
        }
        if (this.hasValue(parentAttrs) && this.hasValue(parentAttrs.header_level)) {
            return parentAttrs.header_level;
        }

        return defaultLevel;
    },

    /**
     * Generates row CSS class which represents the row structure.
     *
     * @param Row Props.
     * @param rowProps
     * @returns String Row spcecial structure class like et_pb_row_1-4_3-4 etc.
     */
    generateRowStructureClass(rowProps) {
        if (isUndefined(rowProps.content) || '' === rowProps.content || isEmpty(rowProps.content)) {
            return '';
        }
        let rowClass = '';

        forEach(rowProps.content, (columnData) => {
            const columnType = get(columnData, 'attrs.type');
            if (columnType && isString(columnType)) {
                rowClass += `_${columnType.replace('_', '-').trim()}`;
            }
        });

        switch (rowClass) {
            case '_4-4':
            case '_1-2_1-2':
            case '_1-3_1-3_1-3':
            case '_2-5_3-5':
            case '_3-5_2-5':
            case '_1-3_2-3':
            case '_2-3_1-3':
            case '_1-5_3-5_1-5':
            case '_3-8_3-8':
            case '_1-3_1-3':
                // intentional fallthrough. These row structure class doesn't need to be printed on VB
                // because these structures are not printed on FE as well
                rowClass = '';
                break;
            case '_1-4_1-4_1-4_1-4':
                rowClass = 'et_pb_row_4col';
                break;
            case '_1-5_1-5_1-5_1-5_1-5':
                rowClass = 'et_pb_row_5col';
                break;
            case '_1-6_1-6_1-6_1-6_1-6_1-6':
                rowClass = 'et_pb_row_6col';
                break;
            default:
                rowClass = `et_pb_row${rowClass}`;
        }

        return rowClass;
    },
    shouldComponentUpdate(component, nextProps, nextState) {
        let nextPropsUpdated = nextProps;
        let currentPropsUpdated = component.props;

        // Use reduced versions of props in wireframe mode to avoid unneeded renders.
        if (component.props.wireframeMode) {
            nextPropsUpdated = this._cleanPropsForWireframeComparison(nextProps);
            currentPropsUpdated = this._cleanPropsForWireframeComparison(component.props);
        }

        return !isEqual(nextPropsUpdated, currentPropsUpdated) || !isEqual(nextState, component.state);
    },

    /**
     * Prepare module props for the comparison omitting all the data which doesn't matter in Wireframe mode.
     *
     * @param props
     */
    _cleanPropsForWireframeComparison(props) {
        if (isUndefined(props)) {
            return props;
        }

        const cleanProps = omit(props, ['attrs', 'children', 'content']);

        if (props.attrs) {
            cleanProps.attrs = pick(props.attrs, [
                'locked',
                'global_module',
                'admin_label',
                'collapsed',
                'ab_subject_id',
                'ab_goal',
                'disabled',
                'disabled_on',
                'column_structure',
                'type',
                '_deleted',
            ]);
        }

        if (props.content && isArray(props.content) && !isEmpty(props.content)) {
            cleanProps.content = [];

            forEach(props.content, (singleProp) => {
                cleanProps.content.push(this._cleanPropsForWireframeComparison(singleProp));
            });
        } else if (!isArray(props.content)) {
            cleanProps.content = '';
        }

        return cleanProps;
    },
    getAdminBarHeight() {
        if (this.isTB()) {
            return 32;
        }

        const $bar = this.$topWindow('#wpadminbar');

        return $bar.length > 0 ? parseInt($bar.innerHeight()) : 0;
    },
    getScrollbarWidth: getScrollbarWidthPure,
    maybeGetScrollbarWidth() {
        if (Utils.isBFB()) {
            return 0;
        }

        const $html = Utils.isTB() ? Utils.$appWindow('html') : Utils.$topWindow('html');

        if (!Utils.isTB() && ($html.hasClass('et-fb-preview--tablet') || $html.hasClass('et-fb-preview--phone'))) {
            return 0;
        }

        const adminbarHeight = this.getAdminBarHeight();
        const hasScrollbar = Utils.$appWindow('html').height() + adminbarHeight > Utils.$topWindow('html').height();

        return hasScrollbar ? Utils.getScrollbarWidth() : 0;
    },
    getScrollTargets() {
        const mode = get(Utils.appWindow(), 'ET_Builder.API.State.View_Mode', {});
        let $targets = Utils.$appWindow('html');

        if (Utils.isTB()) {
            $targets = this.$TBScrollTarget();
        } else if (!Utils.isBlockEditor() && (Utils.isBFB() || !(mode.isDesktop() || mode.isWireframe()))) {
            $targets = Utils.$topWindow('html');
        }

        return $targets;
    },
    getScrollEventTarget() {
        const mode = Utils.appWindow().ET_Builder.API.State.View_Mode;
        let target = Utils.appWindow();

        if (Utils.isTB()) {
            target = this.$TBScrollTarget().get(0);
        } else if (Utils.isBFB() || !(mode.isDesktop() || mode.isWireframe())) {
            target = Utils.topWindow();
        }

        return target;
    },
    enableScrollLock() {
        const $pageSettingsBar = Utils.$topWindow('.et-fb-page-settings-bar');
        const $wpadminbar = Utils.$topWindow('#wpadminbar');
        const $fixedTopHeader = Utils.$topWindow('.et_fixed_nav:not(.et_vertical_nav) #top-header');
        const $fixedMainHeader = Utils.$topWindow('.et_fixed_nav:not(.et_vertical_nav) #main-header');
        const mode = get(Utils.appWindow(), 'ET_Builder.API.State.View_Mode', {});

        // Page Settings Bar Location
        const isPageSettingsCorner = $pageSettingsBar.hasClass('et-fb-page-settings-bar--corner');
        const isPageSettingsRightCorner = $pageSettingsBar.hasClass('et-fb-page-settings-bar--right-corner');
        const isPageSettingsLeftCorner = $pageSettingsBar.hasClass('et-fb-page-settings-bar--left-corner');
        const isPageSettingsRight = $pageSettingsBar.hasClass('et-fb-page-settings-bar--right');
        const isPageSettingsVertical = $pageSettingsBar.hasClass('et-fb-page-settings-bar--vertical');

        const $scrollTargets = this.getScrollTargets();

        $scrollTargets.css({
            overflowY: 'hidden',
            paddingRight: Utils.getScrollbarWidth(),
        });

        if (!Utils.isBFB()) {
            // If page settings bar is top or bottom, subtract scrollbar width from width
            if (!isPageSettingsCorner && !isPageSettingsVertical) {
                $pageSettingsBar.css('width', `calc(100% - ${SCROLLBAR_WIDTH}px)`);
            }

            // If page settings bar is left corner, offset right column by scrollbar width
            if (isPageSettingsLeftCorner) {
                $pageSettingsBar.find('.et-fb-page-settings-bar__column--right').css('right', SCROLLBAR_WIDTH);
            }
        }

        // Offset wpadminbar by scrollbar width
        $wpadminbar.css('width', `calc(100% - ${SCROLLBAR_WIDTH}px)`);

        // Offset header by scrollbar width
        $fixedTopHeader.css('width', `calc(100% - ${SCROLLBAR_WIDTH}px)`);
        $fixedMainHeader.css('width', `calc(100% - ${SCROLLBAR_WIDTH}px)`);
    },
    disableScrollLock() {
        const $pageSettingsBar = Utils.$topWindow('.et-fb-page-settings-bar');
        const $wpadminbar = Utils.$topWindow('#wpadminbar');
        const $fixedTopHeader = Utils.$topWindow('.et_fixed_nav:not(.et_vertical_nav) #top-header');
        const $fixedMainHeader = Utils.$topWindow('.et_fixed_nav:not(.et_vertical_nav) #main-header');
        const mode = get(Utils.appWindow(), 'ET_Builder.API.State.View_Mode', {});

        // Page Settings Bar Location
        const isPageSettingsCorner = $pageSettingsBar.hasClass('et-fb-page-settings-bar--corner');
        const isPageSettingsRightCorner = $pageSettingsBar.hasClass('et-fb-page-settings-bar--right-corner');
        const isPageSettingsLeftCorner = $pageSettingsBar.hasClass('et-fb-page-settings-bar--left-corner');
        const isPageSettingsRight = $pageSettingsBar.hasClass('et-fb-page-settings-bar--right');
        const isPageSettingsVertical = $pageSettingsBar.hasClass('et-fb-page-settings-bar--vertical');

        const $scrollTargets = this.getScrollTargets();

        $scrollTargets.css({
            overflowY: 'auto',
            paddingRight: 0,
        });

        if (!Utils.isBFB() && !Utils.isTB()) {
            // If page settings bar is top or bottom remove width
            if (!isPageSettingsCorner && !isPageSettingsVertical) {
                $pageSettingsBar.css('width', '');
            }

            // If page settings bar is left corner, remove scrollbar width offset from right column
            if (isPageSettingsLeftCorner) {
                $pageSettingsBar.find('.et-fb-page-settings-bar__column--right').css('right', 0);
            }
        }

        if (Utils.condition('is_bfb')) {
            // Remove admin topbar offset
            $wpadminbar.css('width', '100%');
        }

        // Remove header offset
        $fixedTopHeader.css('width', '');
        $fixedMainHeader.css('width', '');
    },

    cookies,

    getEventsTarget(responsiveView) {
        return this.isBFB() || responsiveView ? this.topWindow() : this.appWindow();
    },

    linkRel(savedRel) {
        const rel = [];

        if (savedRel) {
            const relMap = ['bookmark', 'external', 'nofollow', 'noreferrer', 'noopener'];

            const selectedRels = savedRel.split('|');

            selectedRels.forEach((value, index) => {
                if (!value || 'off' === value) {
                    return;
                }

                rel.push(relMap[index]);
            });
        }

        return rel.length ? rel.join(' ') : null;
    },

    // It's a replication of et_builder_set_element_font()
    setElementFont(font_data, use_important, default_values) {
        let style = '';

        if ('' === font_data || isUndefined(font_data)) {
            return '';
        }

        // It's a replication of et_builder_get_websafe_font_stack() function from BB
        function getWebsafeFontStack(font_type) {
            const type = font_type || 'sans-serif';

            let font_stack = type;

            switch (type) {
                case 'sans-serif':
                    font_stack = 'Helvetica, Arial, Lucida, sans-serif';
                    break;
                case 'serif':
                    font_stack = 'Georgia, "Times New Roman", serif';
                    break;
                case 'cursive':
                    font_stack = 'cursive';
                    break;
            }

            return font_stack;
        }

        // It's a replication of et_builder_get_font_family() function from BB
        function setElementFontFamily(font_name, use_important) {
            const fonts = has(window.ETBuilderBackend.customFonts, font_name, false)
                ? window.ETBuilderBackend.customFonts
                : window.ETBuilderBackend.et_builder_fonts_data;
            const important_tag = use_important ? ' !important' : '';
            const {removedFonts} = window.ETBuilderBackend;
            let websafe_font_stack;
            let font_style;
            let font_weight;
            let font_name_ms;
            let style;

            font_name_ms =
                !isUndefined(fonts[font_name]) && !isUndefined(fonts[font_name].add_ms_version)
                    ? `'${font_name} MS', `
                    : '';

            if (get(removedFonts, font_name, false)) {
                font_style = removedFonts[font_name].styles;
                font_name = removedFonts[font_name].parent_font;
            }

            if ('' !== font_style) {
                font_weight = ` font-weight:${font_style};`;
            }

            websafe_font_stack = !isUndefined(fonts[font_name]) ? getWebsafeFontStack(fonts[font_name].type) : 'serif';

            style = `${
                'font-family:' + "'"
            }${font_name}',${font_name_ms}${websafe_font_stack}${important_tag};${font_weight}`;

            return style;
        }

        function setElementFontStyle(property, default_value, value, property_default, property_value, use_important) {
            let style = '';
            const important_tag = use_important ? ' !important' : '';

            if (value && !default_value) {
                style = `${property}:${property_value}${important_tag};`;
            } else if (!value && default_value) {
                style = `${property}:${property_default}${important_tag};`;
            }

            return style;
        }

        const font_values = font_data ? font_data.split('|') : [];
        const default_values_processed = 'undefined' === typeof default_values ? '||||||||' : default_values;
        const font_values_default = default_values_processed.split('|');

        if (!isEmpty(font_values)) {
            const font_name = font_values[0];
            let font_weight = '' !== font_values[1] ? font_values[1] : '';
            const is_font_italic = 'on' === font_values[2];
            const is_font_uppercase = 'on' === font_values[3];
            const is_font_underline = 'on' === font_values[4];
            const is_font_small_caps = 'on' === font_values[5];
            const is_font_line_through = 'on' === font_values[6];
            const font_line_color = !isUndefined(font_values[7]) ? font_values[7] : '';
            const font_line_style = !isUndefined(font_values[8]) ? font_values[8] : '';

            const font_name_default = 'Default';
            let font_weight_default = '' !== font_values_default[1] ? font_values_default[1] : '';
            const is_font_italic_default = 'on' === font_values_default[2];
            const is_font_uppercase_default = 'on' === font_values_default[3];
            const is_font_underline_default = 'on' === font_values_default[4];
            const is_font_small_caps_default = 'on' === font_values_default[5];
            const is_font_line_through_default = 'on' === font_values_default[6];
            const font_line_color_default = '';
            const font_line_style_default = '';

            // transform old values to new format
            font_weight = 'on' === font_weight ? '700' : font_weight;
            font_weight_default = 'on' === font_weight_default ? '700' : font_weight_default;

            if ('' !== font_name && font_name_default !== font_name) {
                this.maybeLoadFont(font_name);

                style += setElementFontFamily(font_name, use_important);
            }

            style += setElementFontStyle(
                'font-weight',
                '' !== font_weight_default,
                '' !== font_weight,
                'normal',
                font_weight,
                use_important
            );

            style += setElementFontStyle(
                'font-style',
                is_font_italic_default,
                is_font_italic,
                'normal',
                'italic',
                use_important
            );

            style += setElementFontStyle(
                'text-transform',
                is_font_uppercase_default,
                is_font_uppercase,
                'none',
                'uppercase',
                use_important
            );

            style += setElementFontStyle(
                'text-decoration',
                is_font_underline_default,
                is_font_underline,
                'none',
                'underline',
                use_important
            );

            style += setElementFontStyle(
                'font-variant',
                is_font_small_caps_default,
                is_font_small_caps,
                'none',
                'small-caps',
                use_important
            );

            style += setElementFontStyle(
                'text-decoration',
                is_font_line_through_default,
                is_font_line_through,
                'none',
                'line-through',
                use_important
            );

            style += setElementFontStyle(
                'text-decoration-style',
                '' !== font_line_style_default,
                '' !== font_line_style,
                'solid',
                font_line_style,
                use_important
            );

            style += setElementFontStyle(
                '-webkit-text-decoration-color',
                '' !== font_line_color_default,
                '' !== font_line_color,
                '',
                font_line_color,
                use_important
            );
            style += setElementFontStyle(
                'text-decoration-color',
                '' !== font_line_color_default,
                '' !== font_line_color,
                '',
                font_line_color,
                use_important
            );

            style = style.trim();
        }

        return style;
    },

    /**
     * Set reset CSS style declaration to normalize the existing font styles value from another font
     * options group.
     *
     * @since 3.23
     *
     * @param  {string}  currentValue  Current font option value.
     * @param  {string}  comparedValue Compared or parent font option value.
     * @param  {boolean} useImportant  Imporant status.
     * @returns {string}                Generated reset font styles.
     */
    setResetFontStyle(currentValue, comparedValue, useImportant = false) {
        // Being save, ensure current and compared values are valid string.
        if (!isString(currentValue) || !isString(comparedValue)) {
            return '';
        }

        const currentPieces = currentValue.split('|');
        const comparedPieces = comparedValue.split('|');
        if (isEmpty(currentPieces) || isEmpty(comparedPieces)) {
            return '';
        }

        // Current value font style status.
        const isCurrentItalic = !isUndefined(currentPieces[2]) && 'on' === currentPieces[2];
        const isCurrentUppercase = !isUndefined(currentPieces[3]) && 'on' === currentPieces[3];
        const isCurrentUnderline = !isUndefined(currentPieces[4]) && 'on' === currentPieces[4];
        const isCurrentSmallCaps = !isUndefined(currentPieces[5]) && 'on' === currentPieces[5];
        const isCurrentLineThrough = !isUndefined(currentPieces[6]) && 'on' === currentPieces[6];

        // Compared value font style status.
        const isComparedItalic = !isUndefined(comparedPieces[2]) && 'on' === comparedPieces[2];
        const isComparedUppercase = !isUndefined(comparedPieces[3]) && 'on' === comparedPieces[3];
        const isComparedUnderline = !isUndefined(comparedPieces[4]) && 'on' === comparedPieces[4];
        const isComparedSmallCaps = !isUndefined(comparedPieces[5]) && 'on' === comparedPieces[5];
        const isComparedLineThrough = !isUndefined(comparedPieces[6]) && 'on' === comparedPieces[6];

        let style = '';
        const important = useImportant ? ' !important' : '';

        // Reset italic.
        if (!isCurrentItalic && isComparedItalic) {
            style += `font-style: normal${important};`;
        }

        // Reset uppercase.
        if (!isCurrentUppercase && isComparedUppercase) {
            style += `text-transform: none${important};`;
        }

        // Reset small caps.
        if (!isCurrentSmallCaps && isComparedSmallCaps) {
            style += `font-variant: none${important};`;
        }

        // Reset underline.
        if (!isCurrentUnderline && isComparedUnderline) {
            const underlineValue = isCurrentLineThrough || isComparedLineThrough ? 'line-through' : 'none';
            style += `text-decoration: ${underlineValue}${important};`;
        }

        // Reset line through.
        if (!isCurrentLineThrough && isComparedLineThrough) {
            const lineThroughValue = isCurrentUnderline || isComparedUnderline ? 'underline' : 'none';
            style += `text-decoration: ${lineThroughValue}${important};`;
        }

        return style;
    },

    /**
     * Decode encoded string of option_list field type into object.
     *
     * @param string Encoded string of saved option_list field type attribute.
     * @param value
     * @returns Object.
     */
    decodeOptionListValue(value) {
        const encodedBrackets = ['&#91;', '&#93;'];
        const decodedBrackets = ['[', ']'];

        return !value
            ? value
            : JSON.parse(
                  _replace(
                      _replace(value, encodedBrackets[0], decodedBrackets[0]),
                      encodedBrackets[1],
                      decodedBrackets[1]
                  )
              );
    },

    /**
     * Check whether current module has background or not.
     *
     * @param {object} module Attributes.
     * @param {Array} background Types to be checked.
     * @param moduleAttrs
     * @param backgroundTypes
     * @returns {bool} Has background or not.
     */
    moduleHasBackground(moduleAttrs, backgroundTypes) {
        const allBackgroundTypes = ['color', 'gradient', 'image', 'video'];
        const types = isUndefined(backgroundTypes) ? allBackgroundTypes : backgroundTypes;

        let hasBackground = false;
        let hasBackgroundVideoMp4;
        let hasBackgroundVideoWebm;

        forEach(types, (type) => {
            switch (type) {
                case 'color':
                    hasBackground = this.hasValue(moduleAttrs.background_color);
                    break;
                case 'gradient':
                    hasBackground = this.isOn(moduleAttrs.use_background_color_gradient);
                    break;
                case 'image':
                    hasBackground = this.hasValue(moduleAttrs.background_image);
                    break;
                case 'video':
                    hasBackgroundVideoMp4 = this.hasValue(moduleAttrs.background_video_mp4);
                    hasBackgroundVideoWebm = this.hasValue(moduleAttrs.background_video_webm);

                    hasBackground = hasBackgroundVideoMp4 || hasBackgroundVideoWebm;
                    break;
            }

            // Stop forEach loop if one of the background has value.
            // Return false stops lodash's forEach loop from continuing
            return !hasBackground;
        });

        return hasBackground;
    },

    /**
     * Normalize video dimension.
     *
     * @param $element window.jQuery element.
     */
    fitVids($element) {
        if ($element.length) {
            $element.fitVids({
                customSelector:
                    "iframe[src^='http://www.hulu.com'], iframe[src^='http://www.dailymotion.com'], iframe[src^='http://www.funnyordie.com'], iframe[src^='https://embed-ssl.ted.com'], iframe[src^='http://embed.revision3.com'], iframe[src^='https://flickr.com'], iframe[src^='http://blip.tv'], iframe[src^='http://www.collegehumor.com']",
            });
        }
    },

    toTextOrientation,
    getTextOrientation: flowRight(toTextOrientation, toRTLTextOrientation),
    isBuilderFocused() {
        return (
            this.$appDocument(window.ETBuilderBackend.css.containerPrefix).is(':hover') ||
            this.$topDocument(window.ETBuilderBackend.css.containerPrefix).is(':hover')
        );
    },

    /**
     * Get valid fixed header height in Divi or Extra theme.
     *
     * @returns Int.
     */
    getFixedHeaderHeight() {
        const $body = Utils.$appWindow('body');

        let height = 0;

        if (
            $body.hasClass('et_divi_theme') &&
            Utils.$topWindow().width() >= 980 &&
            !$body.hasClass('et_vertical_nav')
        ) {
            height += parseInt(Utils.$appWindow('#top-header.et-fixed-header').height());
            height += parseInt(Utils.$appWindow('#main-header.et-fixed-header').height());
        }

        if ($body.hasClass('et_extra')) {
            height += parseInt(Utils.$appWindow('.et-fixed-header #main-header').height());
        }

        return 0;
    },

    /**
     * Parse inline CSS as object pair.
     *
     * @param string Inline CSS.
     * @param inlineCSS
     * @returns Object.
     */
    parseInlineCssIntoObject(inlineCSS) {
        return fromPairs(map(inlineCSS.split('; '), (property) => property.split(': ')));
    },

    /**
     * Get processed tab slug.
     *
     * Convert `advanced` into `design` because in settings modal, we use `design` as tab slug.
     *
     * @since 3.29.3
     *
     * @param  {string} slug
     *
     * @returns {string}
     */
    getProcessedTabSlug(slug) {
        if ('advanced' === slug) {
            return 'design';
        }

        return slug;
    },

    /**
     * Get module address sequences as array.
     *
     * For example: 1.0.1.3.
     *
     * Will returned as ['1', '1.0', '1.0.1', '1.0.1.3'];.
     *
     * @since 4.0.7
     *
     * @param {string | Array} address
     *
     * @returns {Array}
     */
    getModuleAddressSequence(address) {
        const addressArray = isArray(address) ? address : address.split('.');

        return keys(addressArray).map((index) => take(addressArray, parseInt(index, 10) + 1).join('.'));
    },

    /**
     * Get font sub field indexes.
     *
     * Grouped based how it appear in the module settings modal.
     *
     * @since 4.0.7
     *
     * @param {string} subFieldKey
     *
     * @returns {Array}
     */
    getFontFieldIndexes(subFieldKey) {
        const subFieldIndex = {
            font: [0],
            weight: [1],
            style: [2, 3, 4, 5, 6],
            line_style: [7],
            line_color: [8],
        };

        return get(subFieldIndex, subFieldKey, []);
    },
    flattenFields(fields) {
        return reduce(
            fields,
            (accumulator, field, key) => {
                if ('composite' === field.type) {
                    const structure = get(field, 'composite_structure', {});
                    const subFields = map(structure, 'controls').reduce((accumulatorControls, controls) => {
                        const controlsMapped = mapValues(controls, (control, controlKey) => {
                            const controlName = get(control, 'name', controlKey);
                            const tabSlug = get(control, 'tab_slug', get(field, 'tab_slug', ''));
                            const toggleSlug = get(control, 'toggle_slug', get(field, 'toggle_slug', ''));

                            return assign({}, control, {
                                name: controlName,
                                tab_slug: Utils.getProcessedTabSlug(tabSlug),
                                toggle_slug: toggleSlug,
                            });
                        });

                        return {...accumulatorControls, ...controlsMapped};
                    }, {});

                    return {...accumulator, ...subFields};
                }

                return {...accumulator, [key]: field};
            },
            {}
        );
    },

    /**
     * Check if browser has localStorage support or not.
     *
     * @since 3.28
     *
     * @returns {bool}
     */
    hasLocalStorage() {
        if (!isNull(has_localStorage)) {
            return has_localStorage;
        }

        try {
            has_localStorage = !!window.ET_Builder.Frames.top.localStorage;
        } catch (e) {}

        return has_localStorage;
    },

    /**
     * Shows the core modal by helper's ID.
     *
     * @param {string} modalId The modal ID taken from the window.ETBuilderBackend helpers.
     */
    showCoreModal(modalId) {
        const modalSettings = window.ETBuilderBackend[modalId];
        if (modalSettings) {
            const {header} = window.ETBuilderBackend[modalId];
            const {text} = window.ETBuilderBackend[modalId];
            const {buttons} = window.ETBuilderBackend[modalId];
            const modalTemplate = window.ETBuilderBackend.coreModalTemplate;
            const buttonsTemplate = window.ETBuilderBackend.coreModalButtonsTemplate;
            const {classes} = window.ETBuilderBackend[modalId];

            let buttonsHtml = buttons ? reduce(buttons, (acc, button) => acc + button, '') : '';
            buttonsHtml = this.sprintf(buttonsTemplate, buttonsHtml);

            const buttonsClass = keys(buttons).length > 1 ? 'et-core-modal-two-buttons' : '';
            const modalHtml = this.sprintf(modalTemplate, header, text, buttonsHtml);

            this.$topWindow('.et-core-modal-caption').remove();
            this.$topWindow(modalHtml).appendTo(this.$topWindow('body')).addClass(buttonsClass).addClass(classes);
            this.$appWindow().trigger('et-core-modal-active');
        }
    },

    /**
     * Hides the core modal gracefully.
     *
     * @param {string} modalClass The modal unique CSS class.
     */
    hideCoreModal(modalClass) {
        this.$topWindow(`.${modalClass}`)
            .addClass('et-core-closing')
            .delay(600)
            .queue(function () {
                Utils.$topWindow(this).removeClass('et-core-active et-core-closing').dequeue().remove();
            });
    },

    /**
     * DO NOT FOR SECURITY REASONS.
     *
     * Filters the string for any HTML tags.
     *
     * @param string
     *
     * @returns {string}
     */
    stripHTMLTags(string) {
        return string.replace(/(<([^>]+)>)/gi, '');
    },
};

Utils.maybeLoadFont = memoize(Utils.maybeLoadFont.bind(Utils));

window.ET_FB = window.ET_FB || {};
window.ET_FB.utils = {
    log: Utils.log,
    defaultAllLogAreas: ['general', 'store_action_obj', 'store_emit', 'warning'],
    debug() {
        // Hardcoded overwritten output
        if (!isUndefined(ET_FB_SETTINGS.debug)) {
            return ET_FB_SETTINGS.debug;
        }

        try {
            ET_FB_SETTINGS.debug = 'true' === localStorage.getItem('et_fb_debug');
            return ET_FB_SETTINGS.debug;
        } catch (e) {
            // do not use localStorage if it full or any other error occurs
            return false;
        }
    },
    debugOn() {
        try {
            // Update localStorage for persistent saving
            localStorage.setItem('et_fb_debug', 'true');
            ET_FB_SETTINGS.debug = true;

            // Notify user
            return 'Debug mode is activated';
        } catch (e) {
            // do not use localStorage if it full or any other error occurs
            // Notify user
            return 'Debug mode was not activated due to lack of support or other error';
        }
    },
    debugOff() {
        // Update localStorage for persistent saving
        localStorage.setItem('et_fb_debug', 'false');
        ET_FB_SETTINGS.debug = false;

        // Notify user
        return 'Debug mode is deactivated';
    },
    debugSetLogAreas(areas) {
        // Update localStorage for persistent saving
        localStorage.setItem('et_fb_debug_log_areas', areas);

        return `Separate by space to set multiple areas. You are now logging these areas: ${this.debugLogAreas().join(
            ', '
        )}`;
    },
    debugAddLogArea(area) {
        const areas = localStorage.getItem('et_fb_debug_log_areas');

        // Update localStorage for persistent saving
        localStorage.setItem('et_fb_debug_log_areas', `${areas} ${area}`);

        return `Separate by space to set multiple areas. You are now logging these areas: ${this.debugLogAreas().join(
            ', '
        )}`;
    },
    debugSetAllLogAreas() {
        localStorage.setItem('et_fb_debug_log_areas', this.defaultAllLogAreas.join(' '));

        return `You are now logging these areas: ${this.defaultAllLogAreas.join(', ')}`;
    },
    debugLogAreas() {
        const areas = localStorage.getItem('et_fb_debug_log_areas');

        // Hardcoded overwritten output
        if (!isUndefined(ET_FB_SETTINGS.enableAllLogAreas) && ET_FB_SETTINGS.enableAllLogAreas) {
            return this.defaultAllLogAreas;
        }

        if (!isUndefined(ET_FB_SETTINGS.enabledLogAreas)) {
            return ET_FB_SETTINGS.enabledLogAreas;
        }

        return null === areas ? this.defaultAllLogAreas : areas.split(' ');
    },
};

const {
    applyMixinsSafely,
    intentionallyCloneDeep,
    intentionallyClone,
    sanitized_previously,
    log,
    is,
    isOn,
    isOff,
    isOnOff,
    isYes,
    isNo,
    isDefault,
    isMobileDevice,
    isIEOrEdge,
    isIE,
    isBlockEditor,
    condition,
    hasLocalStorage,
    hasNumericValue,
    hasValue,
    getResponsiveStatus,
    parseShortcode,
    processFontIcon,
    generateResponsiveCss,
    generatePlaceholderCss,
    replaceCodeContentEntities,
    removeFancyQuotes,
    processRangeValue,
    getCorners,
    getCorner,
    getSpacing,
    getBreakpoints,
    getViewModeByWidth,
    getPreviewModes,
    getGradient,
    removeClassNameByPrefix,
    getKeyboardList,
    getRowLayouts,
    maybeLoadFont,
    fontnameToClass,
    getCommentsMarkup,
    callWindow,
    decodeHtmlEntities,
    hasBodyMargin,
    fixSliderHeight,
    fixBuilderContent,
    triggerResizeForUIUpdate,
    enableScrollLock,
    disableScrollLock,
    linkRel,
    setElementFont,
    decodeOptionListValue,
    sprintf,
    isJson,
    isValidHtml,
    getNextBreakpoint,
    getPrevBreakpoint,
    appDocument,
    $appDocument,
    appWindow,
    $appWindow,
    topDocument,
    $topDocument,
    topWindow,
    $topWindow,
    getFixedHeaderHeight,
    parseInlineCssIntoObject,
    getOS,
    isModuleLocked,
    isModuleDeleted,
    getComponentType,
    getModuleSectionType,
    getModuleAncestor,
    getScrollbarWidth,
    getProcessedTabSlug,
    getModuleAddressSequence,
    getFontFieldIndexes,
    isRealMobileDevice,
    stripHTMLTags,
} = Utils;

export {
    applyMixinsSafely,
    intentionallyCloneDeep,
    intentionallyClone,
    sanitized_previously,
    log,
    is,
    isOn,
    isOff,
    isOnOff,
    isYes,
    isNo,
    isDefault,
    isMobileDevice,
    isIEOrEdge,
    isIE,
    isBlockEditor,
    condition,
    hasLocalStorage,
    hasNumericValue,
    hasValue,
    getResponsiveStatus,
    parseShortcode,
    processFontIcon,
    generateResponsiveCss,
    generatePlaceholderCss,
    replaceCodeContentEntities,
    removeFancyQuotes,
    processRangeValue,
    getCorners,
    getCorner,
    getSpacing,
    getBreakpoints,
    getViewModeByWidth,
    getPreviewModes,
    getGradient,
    removeClassNameByPrefix,
    getKeyboardList,
    getRowLayouts,
    maybeLoadFont,
    fontnameToClass,
    getCommentsMarkup,
    callWindow,
    decodeHtmlEntities,
    hasBodyMargin,
    fixSliderHeight,
    fixBuilderContent,
    triggerResizeForUIUpdate,
    enableScrollLock,
    disableScrollLock,
    cookies,
    linkRel,
    setElementFont,
    decodeOptionListValue,
    sprintf,
    isJson,
    isValidHtml,
    getNextBreakpoint,
    getPrevBreakpoint,
    appDocument,
    $appDocument,
    topDocument,
    $topDocument,
    appWindow,
    $appWindow,
    topWindow,
    $topWindow,
    getFixedHeaderHeight,
    parseInlineCssIntoObject,
    getOS,
    isModuleLocked,
    isModuleDeleted,
    getComponentType,
    getModuleSectionType,
    getModuleAncestor,
    getScrollbarWidth,
    getProcessedTabSlug,
    getModuleAddressSequence,
    getFontFieldIndexes,
    isRealMobileDevice,
    stripHTMLTags,
};

export default Utils;
