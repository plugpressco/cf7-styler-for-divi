module.exports = function (grunt) {
	'use strict';

	const pkg = grunt.file.readJSON('package.json');

	const commonExcludes = [
		'!node_modules/**',
		'!build/**',
		'!css/sourcemap/**',
		'!.git/**',
		'!.github/**',
		'!.wordpress-org/**',
		'!.claude/**',
		'!.vscode/**',
		'!bin/**',
		'!docs/**',
		'!sass/**',
		'!src/**',
		'!tests/**',
		'!update-server/**',
		'!webpack/**',
		'!.gitlab-ci.yml',
		'!cghooks.lock',
		'!*.sh',
		'!*.map',
		'!*.zip',
		'!**/*.LICENSE.txt',
		'!.DS_Store',
		'!.cursorrules',
		'!.distignore',
		'!.editorconfig',
		'!.env',
		'!.eslintignore',
		'!.eslintrc.json',
		'!.gitattributes',
		'!.gitignore',
		'!.babelrc.json',
		'!.prettierignore',
		'!.prettierrc.json',
		'!composer.json',
		'!composer.lock',
		'!Gruntfile.js',
		'!package.json',
		'!package-lock.json',
		'!phpcs.xml.dist',
		'!phpunit.xml',
		'!postcss.config.js',
		'!README.md',
		'!tailwind.config.js',
		'!webpack.config.js',
		'!webpack.divi5.config.js',
		'!wp-textdomain.js',
	];

	const wp_src = [
		'**',
		...commonExcludes,
		'!includes/pro/**',
		'!assets/pro/**',
		'!dist/js/admin-pro.js',
		'!cf7-mate-pro.php',
		'!changelog-pro.txt',
	];
	const pro_src = ['**', ...commonExcludes, '!cf7-styler.php'];

	grunt.initConfig({
		copy: {
			wp: {
				options: { mode: true },
				src: wp_src,
				dest: 'package/cf7-styler-for-divi/',
			},
			pro: {
				options: { mode: true },
				src: pro_src,
				dest: 'package/cf7-mate-pro/',
			},
		},

		bumpup: {
			options: { updateProps: { pkg: 'package.json' } },
			file: 'package.json',
		},

		replace: {
			plugin_const: {
				src: ['cf7-styler.php'],
				overwrite: true,
				replacements: [
					{
						from: /CF7M_VERSION', '.*?'/g,
						to: "CF7M_VERSION', '<%= pkg.version %>'",
					},
				],
			},
			plugin_main: {
				src: ['cf7-styler.php'],
				overwrite: true,
				replacements: [
					{
						from: /Version: \bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?(?:\+[\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\b/g,
						to: 'Version: <%= pkg.version %>',
					},
				],
			},
		},

		compress: {
			wp: {
				options: {
					archive: `cf7-styler-for-divi-${pkg.version}.zip`,
					mode: 'zip',
					level: 5,
				},
				files: [
					{
						expand: true,
						cwd: 'package/',
						src: ['cf7-styler-for-divi/**'],
						dest: '/',
					},
				],
			},
			pro: {
				options: {
					archive: `cf7-mate-pro-${pkg.proVersion || pkg.version}.zip`,
					mode: 'zip',
					level: 5,
				},
				files: [
					{
						expand: true,
						cwd: 'package/',
						src: ['cf7-mate-pro/**'],
						dest: '/',
					},
				],
			},
		},

		clean: {
			main: ['package'],
			zip: ['*.zip'],
		},
	});

	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-bumpup');
	grunt.loadNpmTasks('grunt-text-replace');

	grunt.registerTask('bump-version', function () {
		const ver = grunt.option('ver');
		if (ver) {
			grunt.task.run([
				'bumpup:' + (ver || 'patch'),
				'replace:plugin_const',
				'replace:plugin_main',
			]);
		}
	});

	grunt.registerTask('write_pro_main', function () {
		const pkg = grunt.config('pkg') || grunt.file.readJSON('package.json');
		const v = pkg.proVersion || pkg.version || '1.0.0';
		const content = `<?php
/*
Plugin Name: CF7 Mate Pro
Plugin URI: https://cf7mate.com
Description: Pro features for CF7 Mate for Divi.
Version: ${v}
Author: PlugPress
Author URI: https://plugpress.co
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: cf7-styler-for-divi
Domain Path: /languages

@fs_premium_only /includes/pro/, /assets/pro/
*/

if (!defined('ABSPATH')) exit;

define('CF7M_VERSION', '${v}');
define('CF7M_IS_PRO_VERSION', true);
define('CF7M_BASENAME', plugin_basename(__FILE__));
define('CF7M_BASENAME_DIR', plugin_basename(__DIR__));
define('CF7M_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CF7M_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CF7M_MODULES_JSON_PATH', CF7M_PLUGIN_PATH . 'modules-json/');
define('CF7M_URL_MAIN', 'https://cf7mate.com');
define('CF7M_URL_DOCS', 'https://cf7mate.com/docs');
define('CF7M_URL_SUPPORT', 'https://cf7mate.com/support');
define('CF7M_URL_COMMUNITY', 'https://facebook.com/groups/plugpress');
define('CF7M_URL_PRICING', 'https://cf7mate.com/pricing');
define('CF7M_LS_PRODUCT_ID', '');
define('CF7M_LS_STORE_ID', '');
require_once CF7M_PLUGIN_PATH . 'includes/plugin.php';
`;
		grunt.file.write('package/cf7-mate-pro/cf7-mate-pro.php', content);
		grunt.log.writeln(
			'Written package/cf7-mate-pro/cf7-mate-pro.php (v' + v + ')',
		);
	});

	// Remove pro-only JS from the free package directory.
	grunt.registerTask('strip_pro_js_from_wp', function () {
		const f = 'package/cf7-styler-for-divi/dist/js/admin-pro.js';
		if (grunt.file.exists(f)) {
			grunt.file.delete(f);
			grunt.log.writeln('Removed ' + f);
		}
	});

	// WP repo zip (free)
	grunt.registerTask('package:wp', [
		'clean:main',
		'clean:zip',
		'copy:wp',
		'compress:wp',
		'clean:main',
	]);

	// Pro zip (cf7-mate-pro.php)
	grunt.registerTask('package:pro', [
		'clean:main',
		'clean:zip',
		'copy:pro',
		'write_pro_main',
		'compress:pro',
		'clean:main',
	]);
};
