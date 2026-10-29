// Divi 4 builder entry: registers legacy FF/GF modules with Visual Builder
import './lodash';
import $ from 'jquery';

import './lagecy.scss';

import FFStyler from './ff/ff.jsx';
import GFStyler from './gf/gf.jsx';

const modules = [FFStyler, GFStyler];

$(window).on('et_builder_api_ready', (_event, API) => {
	API.registerModules(modules);
});
