/**
 * Find WordPress's lodash before any lodash import is evaluated.
 *
 * Divi 4 copies page scripts into its builder iframe. There the inline
 * `window.lodash = _.noConflict()` can run before lodash has loaded, and
 * Underscore then takes `window._`. The builder's parent frame keeps the
 * real library. Webpack resolves lodash imports through `window.cf7mLodash`.
 */
const isLodash = (candidate) =>
	!!candidate && 'function' === typeof candidate.flowRight && 'function' === typeof candidate.cloneDeep;

const findLodash = () => {
	const frames = [window, window.parent, window.top];
	for (let i = 0; i < frames.length; i++) {
		try {
			if (isLodash(frames[i].lodash)) {
				return frames[i].lodash;
			}
			if (isLodash(frames[i]._)) {
				return frames[i]._;
			}
		} catch (error) {
			// A cross-origin parent cannot be read; keep looking.
		}
	}
	return undefined;
};

if (!isLodash(window.cf7mLodash)) {
	window.cf7mLodash = findLodash();
}
