const { getAttrValue } = window?.divi?.moduleUtils ?? {};

export const isVisibleFields = (args) => {
	if (!args || typeof args !== 'object') {
		return true;
	}
	const attrs = args.attrs ?? args.attributes;
	const breakpoint = args.breakpoint;
	const baseBreakpoint = args.baseBreakpoint;
	const breakpointNames = args.breakpointNames;
	const state = args.state;
	const attrName = args.attrName ?? args.attributeName ?? '';
	const subName = args.subName;

	if (!getAttrValue) {
		return true;
	}

	const attrNameWithSubName = subName ? `${attrName}.*.${subName}` : attrName;
	const ctx = { breakpoint, baseBreakpoint, breakpointNames, state };
	const adv = attrs?.module?.advanced ?? attrs?.cf7?.advanced;

	const getUseFormHeader = () =>
		getAttrValue({ attr: adv?.useFormHeader, ...ctx }) ?? 'off';

	const getUseIcon = () =>
		getAttrValue({ attr: adv?.useIcon, ...ctx }) ?? 'off';

	const getUseFormButtonFullwidth = () =>
		getAttrValue({ attr: adv?.useFormButtonFullwidth, ...ctx }) ?? 'off';

	const getCrCustomStyles = () =>
		getAttrValue({ attr: adv?.crCustomStyles, ...ctx }) ?? 'off';

	// Spacing controls report their side group (padding or margin) as subName.
	switch (['padding', 'margin'].includes(subName) ? attrName : attrNameWithSubName) {
		// Form header dependent fields - show when useFormHeader is "on"
		case 'module.advanced.formHeaderTitle':
		case 'module.advanced.formHeaderText':
		case 'module.advanced.useIcon': {
			const useFormHeader = getUseFormHeader();
			return useFormHeader === 'on';
		}

		// Header icon - show when useFormHeader is "on" AND useIcon is "on"
		case 'module.advanced.headerIcon': {
			const useFormHeader = getUseFormHeader();
			const useIcon = getUseIcon();
			return useFormHeader === 'on' && useIcon === 'on';
		}

		// Header image - show when useFormHeader is "on" AND useIcon is "off"
		case 'module.advanced.headerImage': {
			const useFormHeader = getUseFormHeader();
			const useIcon = getUseIcon();
			return useFormHeader === 'on' && useIcon === 'off';
		}

		// Button alignment - show when useFormButtonFullwidth is "off"
		case 'module.advanced.buttonAlignment': {
			const useFormButtonFullwidth = getUseFormButtonFullwidth();
			return useFormButtonFullwidth === 'off';
		}

		// Radio/Checkbox styling fields - show when crCustomStyles is "on"
		case 'module.advanced.crSize':
		case 'module.advanced.crBackgroundColor':
		case 'module.advanced.crSelectedColor':
		case 'module.advanced.crBorderColor':
		case 'module.advanced.crBorderSize':
		case 'module.advanced.crLabelColor': {
			const crCustomStyles = getCrCustomStyles();
			return crCustomStyles === 'on';
		}

		// Form header styling - show when useFormHeader is "on"
		case 'module.advanced.formHeaderBg':
		case 'module.advanced.formHeaderPadding':
		case 'module.advanced.formHeaderBottom':
		case 'module.advanced.formHeaderImgBg': {
			const useFormHeader = getUseFormHeader();
			return useFormHeader === 'on';
		}

		// Header icon color - show when useFormHeader and useIcon are "on"
		case 'module.advanced.formHeaderIconColor': {
			const useFormHeader = getUseFormHeader();
			const useIcon = getUseIcon();
			return useFormHeader === 'on' && useIcon === 'on';
		}

		// Legacy decoration paths (if used elsewhere)
		case 'module.decoration.formHeaderBg':
		case 'module.decoration.formHeaderPadding':
		case 'module.decoration.formHeaderBottom':
		case 'module.decoration.formHeaderImgBg':
		case 'module.decoration.formHeaderIconColor': {
			const useFormHeader = getUseFormHeader();
			return useFormHeader === 'on';
		}

		default: {
			return true;
		}
	}
};
