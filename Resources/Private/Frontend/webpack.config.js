'use strict'

/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

// noinspection JSUnusedLocalSymbols
const webpack = require('webpack');
const fs = require('fs');
const path = require('path');
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const IgnoreEmitPlugin = require('ignore-emit-webpack-plugin');

const isDev = process.env.NODE_ENV !== 'production';
const modulePath = path.resolve(__dirname, 'src', 'scripts', 'modules');
const stylesPath = path.resolve(__dirname, 'src', 'styles');

/**
 * Recursively walk through given directory and return all contained files.
 *
 * @param directory {string} Root directory where to start file tree traversal
 * @yields {string} Full path of a file entry within the given root directory
 * @see https://gist.github.com/lovasoa/8691344#file-node-walk-es6
 */
async function* walk(directory) {
  for await (const dir of await fs.promises.opendir(directory)) {
    const entry = path.join(directory, dir.name);
    if (dir.isDirectory()) {
      yield* walk(entry);
    } else if (dir.isFile()) {
      yield entry;
    }
  }
}

module.exports = [
  {
    mode: isDev ? 'development' : 'production',
    devtool: isDev ? 'eval-cheap-module-source-map' : false,
    context: modulePath,
    // Dynamic entry points to match all require.js modules
    entry: () => new Promise(async (resolve) => {
      const files = {};
      for await(const file of walk(modulePath)) {
        const moduleName = path.relative(modulePath, file).replace(/\.tsx?$/, '');
        files[moduleName] = {
          import: file,
          library: {
            type: 'amd',
            name: `TYPO3/CMS/Warming/${moduleName}`,
          },
        };
      }
      return resolve(files);
    }),
    // Treat all TYPO3 modules (including jQuery) as external
    externals: /^(jquery$|TYPO3\/CMS\/)/,
    output: {
      libraryTarget: 'amd',
      path: path.resolve(__dirname, '../../Public/JavaScript'),
    },
    plugins: [
      new CleanWebpackPlugin(),
    ],
    module: {
      rules: [
        {
          test: /\.tsx?$/,
          loader: 'babel-loader',
        },
      ],
    },
    resolve: {
      extensions: ['.ts', '.tsx'],
    },
  },
  {
    mode: isDev ? 'development' : 'production',
    devtool: isDev ? 'eval-cheap-module-source-map' : false,
    output: {
      path: path.resolve(__dirname, '../../Public/Css'),
    },
    context: stylesPath,
    // Dynamic entry points to match all SCSS files
    entry: () => new Promise(async (resolve) => {
      const files = {};
      for await(const file of walk(stylesPath)) {
        const targetFile = path.relative(stylesPath, file).replace(/\.scss?$/, '');
        files[targetFile] = {
          import: file,
        };
      }
      return resolve(files);
    }),
    plugins: [
      new CleanWebpackPlugin(),
      new MiniCssExtractPlugin(),
      new IgnoreEmitPlugin(/\.js$/),
    ],
    module: {
      rules: [
        {
          test: /\.scss$/,
          use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
        },
      ],
    },
  },
];
