/**
 * Webpack configuration for Divi 5 module bundle.
 *
 * @since 3.0.0
 */

const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const ConversionOutlineJsonPlugin = require('./webpack/config/plugins/conversion-outline-json-plugin');

const isProduction = process.env.NODE_ENV === 'production';

module.exports = {
  entry: {
    bundle: './src/divi5/index.js',
  },
  watch: !isProduction,
  resolve: {
    extensions: ['.js', '.jsx', '.json'],
  },

  // Divi Visual Builder provides these as globals - don't bundle them.
  // Use a function to handle @divi/* packages dynamically.
  externals: [
    // Handle all @divi/* packages.
    function ({ request }, callback) {
      // Map @divi packages to their global variables.
      const diviExternals = {
        '@divi/rest': ['divi', 'rest'],
        '@divi/data': ['divi', 'data'],
        '@divi/module': ['divi', 'module'],
        '@divi/module-utils': ['divi', 'moduleUtils'],
        '@divi/modal': ['divi', 'modal'],
        '@divi/field-library': ['divi', 'fieldLibrary'],
        '@divi/icon-library': ['divi', 'iconLibrary'],
        '@divi/module-library': ['divi', 'moduleLibrary'],
        '@divi/style-library': ['divi', 'styleLibrary'],
        '@divi/types': ['divi', 'types'],
      };

      if (diviExternals[request]) {
        return callback(null, diviExternals[request]);
      }

      // Handle any other @divi/* package not explicitly listed.
      if (request.startsWith('@divi/')) {
        const packageName = request.replace('@divi/', '');
        // Convert kebab-case to camelCase for the global.
        const camelName = packageName.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
        return callback(null, ['divi', camelName]);
      }

      callback();
    },
    // Static externals for other libraries.
    {
      jquery: 'jQuery',
      underscore: '_',
      lodash: '_',
      react: ['vendor', 'React'],
      'react-dom': ['vendor', 'ReactDOM'],
      '@wordpress/i18n': ['vendor', 'wp', 'i18n'],
      '@wordpress/hooks': ['vendor', 'wp', 'hooks'],
    },
  ],

  module: {
    rules: [
      // Handle .js and .jsx files.
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: [
          {
            loader: 'babel-loader',
            options: {
              compact: false,
              presets: [
                ['@babel/preset-env', {
                  modules: false,
                  targets: '> 5%',
                }],
                '@babel/preset-react',
              ],
              plugins: [
                '@babel/plugin-proposal-class-properties',
              ],
              cacheDirectory: false,
            },
          },
        ],
      },

      // Handle .css and .scss files.
      {
        test: /\.s?css$/i,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              url: false,
              importLoaders: 1,
            },
          },
          'sass-loader',
        ],
      },
    ],
  },

  plugins: [
    // Generate conversion-outline.json from conversion-outline.js (runs before compile).
    new ConversionOutlineJsonPlugin(),

    // Extract CSS into dist/css (same layout as divi4 build).
    new MiniCssExtractPlugin({
      filename: 'css/[name].css',
    }),

    // Copy module.json, conversion-outline.json and design-presets.json to modules-json in the plugin root.
    new CopyWebpackPlugin({
      patterns: [
        {
          from: '**/module.json',
          context: 'src/divi5/modules',
          to: path.resolve(__dirname, 'modules-json'),
        },
        {
          from: '**/conversion-outline.json',
          context: 'src/divi5/modules',
          to: path.resolve(__dirname, 'modules-json'),
        },
        // The frontend renderer applies the same design presets as the builder.
        {
          from: '**/design-presets.json',
          context: 'src/divi5/modules',
          to: path.resolve(__dirname, 'modules-json'),
        },
      ],
    }),
  ],

  optimization: {
    minimizer: [
      new TerserPlugin({ extractComments: false }),
    ],
  },

  output: {
    filename: 'js/[name].js',
    path: path.resolve(__dirname, 'dist'),
  },

  mode: isProduction ? 'production' : 'development',

  stats: {
    errorDetails: true,
  },
};
