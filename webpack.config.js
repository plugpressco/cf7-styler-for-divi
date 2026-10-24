const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');

const isProduction = process.env.NODE_ENV === 'production';

// Admin styles are the only Tailwind consumers.
const isAdminStyle = (file) => /[\\/]src[\\/](admin|components)[\\/]/.test(file || '');

// Tailwind writes its --tw-* defaults on `*, ::before, ::after` and
// `::backdrop`; keep even those inert variables inside the app root.
const scopeTailwindDefaults = () => ({
	postcssPlugin: 'cf7m-scope-tailwind-defaults',
	Rule(rule) {
		if (rule.selector.startsWith('#cf7-mate-app-root')) {
			return;
		}
		if (rule.parent && rule.parent.type === 'atrule' && /keyframes$/.test(rule.parent.name)) {
			return;
		}
		rule.selectors = rule.selectors.map((selector) => {
			const part = selector.trim();
			if (part === '*') {
				return '#cf7-mate-app-root, #cf7-mate-app-root *';
			}
			return `#cf7-mate-app-root ${part}`;
		});
	},
});
scopeTailwindDefaults.postcss = true;

module.exports = {
	entry: {
		'bundle-4': './src/divi4/index.js',
		lagecy: './src/divi4/lagecy.js',
		admin: './src/admin/admin-index.js',
		blocks: './src/gutenberg/index.js',
		utils: './src/utils/index.js',
	},
	watch: !isProduction,
	performance: {
		hints: false,
		maxEntrypointSize: 512000,
		maxAssetSize: 512000,
	},
	resolve: {
		extensions: ['.js', '.jsx'],
		alias: {
			'@Dependencies': path.resolve(__dirname, 'src/divi4/dependencies'),
		},
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: 'css/[name].css',
		}),
	],

	module: {
		rules: [
			{
				test: /\.(js|jsx)$/,
				exclude: /node_modules/,
				use: [
					{
						loader: 'babel-loader',
						options: {
							presets: [
								'@babel/preset-env',
								[
									'@babel/preset-react',
									{
										runtime: 'classic',
									},
								],
							],
						},
					},
				],
			},
			{
				test: /\.(css|scss)$/,
				use: [
					MiniCssExtractPlugin.loader,
					'css-loader',
					{
						loader: 'postcss-loader',
						options: {
							postcssOptions: (loaderContext) => {
								const admin = isAdminStyle(loaderContext.resourcePath);
								return {
									config: false,
									plugins: [
										...(admin ? [require('tailwindcss'), scopeTailwindDefaults] : []),
										require('autoprefixer'),
										...(admin && isProduction ? [require('cssnano')({ preset: 'default' })] : []),
									],
								};
							},
						},
					},
					{
						loader: 'sass-loader',
						options: {
							sourceMap: !isProduction,
							sassOptions: {
								outputStyle: isProduction
									? 'compressed'
									: 'expanded',
								includePaths: [path.resolve(__dirname, 'src')],
							},
						},
					},
				],
			},
			{
				test: /\.svg$/,
				use: [
					{
						loader: 'url-loader',
						options: {
							limit: 8192,
							name: '[name].[ext]',
							outputPath: 'imgs/',
							publicPath: '../imgs/',
							esModule: false,
						},
					},
				],
			},
			{
				test: /\.(png|jpg|gif)$/,
				use: [
					{
						loader: 'file-loader',
						options: {
							name: '[name].[ext]',
							outputPath: 'imgs/',
							publicPath: '../imgs/',
							esModule: false,
						},
					},
				],
			},
		],
	},

	externals: [
		function ({ request }, callback) {
			if (request.startsWith('lodash/')) {
				return callback(null, `(window.lodash || window._)[${JSON.stringify(request.slice(7))}]`);
			}
			callback();
		},
		{
		$: 'jQuery',
		jquery: 'jQuery',
		lodash: '(window.lodash || window._)',
		'@wordpress/element': ['wp', 'element'],
		'@wordpress/i18n': ['wp', 'i18n'],
		'@wordpress/components': ['wp', 'components'],
		'@wordpress/dom-ready': ['wp', 'domReady'],
		'@wordpress/api-fetch': ['wp', 'apiFetch'],
		'@wordpress/data': ['wp', 'data'],
		'@wordpress/blocks': ['wp', 'blocks'],
		'@wordpress/block-editor': ['wp', 'blockEditor'],
		'@wordpress/server-side-render': ['wp', 'serverSideRender'],
		react: 'React',
		'react-dom': 'ReactDOM',
		},
	],

	optimization: {
		minimizer: [new TerserPlugin({ extractComments: false })],
	},

	output: {
		filename: 'js/[name].js',
		path: path.resolve(__dirname, 'dist'),
		clean: true,
	},

	mode: isProduction ? 'production' : 'development',

	stats: {
		errorDetails: true,
	},
};
