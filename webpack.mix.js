const webpack = require('webpack');
const mix = require('laravel-mix');
const ESLintPlugin = require('eslint-webpack-plugin');

// Set up project folders
const srcFolder = 'client/src';
const distFolder = 'client/dist';

if (process.env.NODE_ENV === 'development') {
  mix.webpackConfig({
    plugins: [
      new ESLintPlugin(),
    ],
    devtool: 'inline-source-map',
  });

  mix.sourceMaps();
}

// Disable auto-generated <type>.LICENSE file
mix.options({
  terser: {
    extractComments: false,
  },
  // Leave relative URLs alone
  processCssUrls: false,
});

mix.js(`${srcFolder}/js/AjaxCompositeValidator.js`, distFolder);
