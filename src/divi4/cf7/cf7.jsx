import $ from 'jquery';
import React, {Component} from 'react';
import { generateStyles } from '@Dependencies/styles';
import { renderDiviFont } from '@Dependencies/et-render-font';

class CF7Styler extends Component {
    static slug = "dvppl_cf7_styler";

    static css(props) {
        let additionalCss = [];
        let address = props.address;

        let iconFont = renderDiviFont(
            props, 
            'header_icon', 
            '%%order_class%% .dipe-form-header-icon .et-pb-icon'
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'form_label_spacing',
                selector: 'div%%order_class%% .dipe-cf7-container .wpcf7-form-control:not(.wpcf7-submit)',
                cssProperty: 'margin-top',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'form_field_spacing',
                selector: '%%order_class%% .dipe-cf7 .wpcf7 form>p, %%order_class%% .dipe-cf7 .wpcf7 form>div, %%order_class%% .dipe-cf7 .wpcf7 form>label, %%order_class%% .dipe-cf7 .wpcf7 form .dp-col>p, %%order_class%% .dipe-cf7 .wpcf7 form .dp-col>div, %%order_class%% .dipe-cf7 .wpcf7 form .dp-col>label',
                cssProperty: 'margin-bottom',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'form_field_height',
                selector: '%%order_class%% .wpcf7-form-control-wrap select, %%order_class%% .wpcf7-form-control-wrap input[type=text], %%order_class%% .wpcf7-form-control-wrap input[type=email], %%order_class%% .wpcf7-form-control-wrap input[type=number], %%order_class%% .wpcf7-form-control-wrap input[type=tel]',
                cssProperty: 'height',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'form_field_padding',
                type: 'padding',
                selector: '%%order_class%% .dipe-cf7-container .wpcf7 input:not([type="submit"]):not([type="checkbox"]):not([type="radio"]), %%order_class%% .dipe-cf7-container .wpcf7 select, %%order_class%% .dipe-cf7-container .wpcf7 textarea',
                cssProperty: 'padding',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'form_header_bg',
                selector: 'div%%order_class%% .dipe-form-header-container',
                cssProperty: 'background-color',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'form_bg',
                selector: '%%order_class%% .dipe-cf7-styler',
                cssProperty: 'background-color',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'form_header_bottom',
                selector: '%%order_class%% .dipe-form-header-container',
                cssProperty: 'margin-bottom',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'form_header_img_bg',
                selector: 'div%%order_class%% .dipe-form-header-icon, div%%order_class%% .dipe-form-header-image',
                cssProperty: 'background-color',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'form_header_icon_color',
                selector: 'div%%order_class%% .dipe-form-header-icon span',
                cssProperty: 'color',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
              address,
              attrs: props,
              name: 'form_header_padding',
              type: 'padding',
              selector: 'div%%order_class%% .dipe-form-header-container',
              cssProperty: 'padding',
              important: true,
            })
        );

        additionalCss.push(
            generateStyles({
              address,
              attrs: props,
              name: 'form_padding',
              type: 'padding',
              selector: 'div%%order_class%% .dipe-cf7-styler',
              cssProperty: 'padding',
              important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'form_background_color',
                selector: 'div%%order_class%% input:not([type=submit]), %%order_class%% select, %%order_class%% .dipe-cf7 textarea, %%order_class%% .wpcf7-checkbox input[type=checkbox] + span:before, %%order_class%% .wpcf7-acceptance input[type=checkbox] + span:before, %%order_class%% .wpcf7-radio input[type=radio]:not(:checked) + span:before',
                cssProperty: 'background-color',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'form_field_active_color',
                selector: 'div%%order_class%% .dipe-cf7 .wpcf7 input:not([type=submit]):focus, %%order_class%% .dipe-cf7 .wpcf7 select:focus, %%order_class%% .dipe-cf7 .wpcf7 textarea:focus',
                cssProperty: 'border-color',
                important: true,
            })
        );

        if ("on" === props.cr_custom_styles) {

            additionalCss.push(
                generateStyles({
                    address,
                    attrs: props,
                    name: 'cr_background_color',
                    selector: '%%order_class%% .wpcf7-checkbox input[type=checkbox] + span:before, %%order_class%% .wpcf7-acceptance input[type=checkbox] + span:before, %%order_class%% .wpcf7-radio input[type=radio]:not(:checked) + span:before',
                    cssProperty: 'background-color',
                    important: true,
                })
            );

            if (props.cr_background_color) {
                additionalCss.push([
                    {
                        selector: '%%order_class%% .wpcf7-radio input[type=radio]:checked + span:before',
                        declaration: `box-shadow: inset 0px 0px 0px 4px ${props.cr_background_color} !important;`,
                    },
                ]);
            }

            additionalCss.push(
                generateStyles({
                    address,
                    attrs: props,
                    name: 'cr_size',
                    selector: '%%order_class%% .wpcf7-checkbox input[type=checkbox] + span:before, %%order_class%% .wpcf7-acceptance input[type=checkbox] + span:before, %%order_class%% .wpcf7-radio input[type=radio] + span:before',
                    cssProperty: ['width', 'height'],
                    important: true,
                })
            );

            const crSize = parseFloat(props.cr_size);
            if (crSize) {
                additionalCss.push([
                    {
                        selector: '%%order_class%% .wpcf7-acceptance input[type=checkbox]:checked + span:before, %%order_class%% .wpcf7-checkbox input[type=checkbox]:checked + span:before',
                        declaration: `font-size: ${crSize / 1.2}${String(props.cr_size).replace(/^[\d.]+/, '') || 'px'} !important;`,
                    },
                ]);
            }

            additionalCss.push(
                generateStyles({
                    address,
                    attrs: props,
                    name: 'cr_border_size',
                    selector: '%%order_class%% .wpcf7-checkbox input[type=checkbox] + span:before, %%order_class%% .wpcf7-acceptance input[type=checkbox] + span:before, %%order_class%% .wpcf7-radio input[type=radio] + span:before',
                    cssProperty: 'border-width',
                    important: true,
                })
            );

            additionalCss.push(
                generateStyles({
                    address,
                    attrs: props,
                    name: 'cr_selected_color',
                    selector: '%%order_class%% .wpcf7-checkbox input[type=checkbox]:checked + span:before, %%order_class%% .wpcf7-acceptance input[type=checkbox]:checked + span:before',
                    cssProperty: 'color',
                    important: true,
                })
            );

            additionalCss.push(
                generateStyles({
                    address,
                    attrs: props,
                    name: 'cr_selected_color',
                    selector: '%%order_class%% .wpcf7-radio input[type=radio]:checked + span:before',
                    cssProperty: 'background-color',
                    important: true,
                })
            );

            additionalCss.push(
                generateStyles({
                    address,
                    attrs: props,
                    name: 'cr_border_color',
                    selector: '%%order_class%% .wpcf7-checkbox input[type=checkbox] + span:before, %%order_class%% .wpcf7-radio input[type=radio] + span:before, %%order_class%% .wpcf7-acceptance input[type=checkbox] + span:before',
                    cssProperty: 'border-color',
                    important: true,
                })
            );

            additionalCss.push(
                generateStyles({
                    address,
                    attrs: props,
                    name: 'cr_label_color',
                    selector: '%%order_class%% .wpcf7-checkbox label, %%order_class%% .wpcf7-radio label, %%order_class%% .wpcf7-acceptance label, %%order_class%% .wpcf7-list-item-label',
                    cssProperty: 'color',
                    important: true,
                })
            );
        }

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'cf7_message_alignment',
                selector: '%%order_class%% .wpcf7 form .wpcf7-response-output, %%order_class%% .wpcf7 form span.wpcf7-not-valid-tip',
                cssProperty: 'text-align',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'cf7_message_color',
                selector: 'div%%order_class%% span.wpcf7-not-valid-tip',
                cssProperty: 'color',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'cf7_message_bg_color',
                selector: 'div%%order_class%% span.wpcf7-not-valid-tip',
                cssProperty: 'background-color',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'cf7_message_margin_top',
                selector: 'div%%order_class%% span.wpcf7-not-valid-tip',
                cssProperty: 'margin-top',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'cf7_border_highlight_color',
                selector: 'div%%order_class%% span.wpcf7-not-valid-tip',
                cssProperty: 'border-color',
                important: true,
            })
        );

        if (props.cf7_border_highlight_color) {
            additionalCss.push([
                {
                    selector: 'div%%order_class%% span.wpcf7-not-valid-tip',
                    declaration: 'border-style: solid !important; border-width: 2px !important;',
                },
            ]);
        }

        additionalCss.push(
            generateStyles({
              address,
              attrs: props,
              name: 'cf7_message_padding',
              selector: 'div%%order_class%% span.wpcf7-not-valid-tip',
              cssProperty: 'padding',
              important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'cf7_success_message_color',
                selector: '%%order_class%% .wpcf7 form.sent .wpcf7-response-output',
                cssProperty: 'color',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'cf7_success_message_bg_color',
                selector: '%%order_class%% .wpcf7 form.sent .wpcf7-response-output',
                cssProperty: 'background-color',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'cf7_success_border_color',
                selector: '%%order_class%% .wpcf7 form.sent .wpcf7-response-output',
                cssProperty: 'border-color',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'cf7_error_message_color',
                selector: '%%order_class%% .wpcf7 form .wpcf7-response-output',
                cssProperty: 'color',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'cf7_error_message_bg_color',
                selector: '%%order_class%% .wpcf7 form .wpcf7-response-output',
                cssProperty: 'background-color',
                important: true,
            })
        );

        additionalCss.push(
            generateStyles({
                address,
                attrs: props,
                name: 'cf7_error_border_color',
                selector: '%%order_class%% .wpcf7 form .wpcf7-response-output',
                cssProperty: 'border-color',
                important: true,
            })
        );

        return additionalCss
            .concat(iconFont);

    }

    renderHeader({ use_form_header, header_img, header_icon, use_icon, form_header_title, form_header_text }) {
        if (use_form_header !== "on") {
            return null;
        }
    
        const utils = window.ET_Builder.API.Utils;
    
        const resolvedHeaderImg = header_img ?? false;
        const image = resolvedHeaderImg && (
            <div className='dipe-form-header-image'>
                <img src={resolvedHeaderImg} alt='' />
            </div>
        );
    
        const resolvedHeaderIcon = utils.processFontIcon(header_icon);
        const icon = (
            <div className='dipe-form-header-icon'>
                <span className='et-pb-icon'>{resolvedHeaderIcon}</span>
            </div>
        );
    
        const iconOrImage = use_icon === "on" ? icon : image;
    
        const titleElement = form_header_title && (
            <h2 className='dipe-form-header-title'>
                {form_header_title}
            </h2>
        );
    
        const textElement = form_header_text && (
            <div className='dipe-form-header-text'>
                {form_header_text}
            </div>
        );
    
        const headerInfo = (titleElement || textElement) && (
            <div className='dipe-form-header-info'>
                {titleElement} {textElement}
            </div>
        );
    
        return (
            <div className='dipe-form-header-container'>
                <div className='dipe-form-header'>
                    {iconOrImage}
                    {headerInfo}
                </div>
            </div>
        );
    }
    
    render() {

        const props = this.props;

        let cr_custom_class = "";
        if ("on" === props.cr_custom_styles) {
            cr_custom_class = "dipe-cf7-cr";
        }

        let button_alignment = props.button_alignment;
        if ("on" === props.use_form_button_fullwidth) {
            button_alignment = "fullwidth";
        }

        return (
            <div
                className={`dipe-cf7-container dipe-cf7-button-${button_alignment}`}
            >
                {this.renderHeader(props)}
                <div
                    className={`dipe-cf7 dipe-cf7-styler ${cr_custom_class}`}
                    dangerouslySetInnerHTML={{ __html: props.__cf7form }}
                />
            </div>
        );
    }

}

export default CF7Styler;
