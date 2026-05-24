const path = require('path');
const fs = require('fs');
const wpPot = require('wp-pot');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');

const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
	entry: {
		'bundle-4': './src/divi4/index.js',
		lagecy: './src/divi4/lagecy.js',
		'admin-notice': './src/admin/admin-notice.js',
		onboarding: './src/onboarding/index.jsx',
		admin: './src/admin/admin-index.js',
		'admin-pro': './src/admin/admin-pro.js',
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
							postcssOptions: {
								plugins: [
									require('tailwindcss'),
									require('autoprefixer'),
								],
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

	externals: {
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

// POT file generation in production mode
if (isProduction) {
	const langDir = path.resolve(__dirname, 'languages');
	if (!fs.existsSync(langDir)) {
		fs.mkdirSync(langDir, { recursive: true });
	}
	wpPot({
		package: 'CF7 Mate',
		domain: 'cf7-styler-for-divi',
		destFile: 'languages/cf7-styler-for-divi.pot',
		relativeTo: './',
		team: 'PlugPress <support@plugpress.io>',
	});
}
