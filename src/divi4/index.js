import './lodash';
import $ from 'jquery';

import './index.scss';

import CF7Styler from './cf7/cf7.jsx';

const modules = [CF7Styler];

$(window).on('et_builder_api_ready', (_event, API) => {
	API.registerModules(modules);
});
