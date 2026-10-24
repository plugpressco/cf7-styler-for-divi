/** Localized admin data (dcsCF7Styler), or an empty object outside the app. */
export function getConfig() {
	return typeof dcsCF7Styler !== 'undefined' ? dcsCF7Styler : {};
}
