/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2025 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

import commonjs from '@rollup/plugin-commonjs';
import del from 'rollup-plugin-delete';
import multiInput from 'rollup-plugin-multi-input';
import nodeResolve from '@rollup/plugin-node-resolve';
import noEmit from 'rollup-plugin-no-emit';
import postcss from 'rollup-plugin-postcss';
import terser from '@rollup/plugin-terser';
import typescript from '@rollup/plugin-typescript';

// eslint-disable-next-line no-undef
const isDev = process.env.NODE_ENV !== 'production';

// Options for cssnano on production builds
const minimizeOptions = {
  preset: [
    'default',
    {
      discardComments: {
        removeAll: true,
      },
    },
  ],
};

export default [
  {
    input: [
      'src/scripts/backend/context-menu-action.ts',
      'src/scripts/backend/toolbar-menu.ts',
    ],
    output: {
      // Avoid hashes in chunks
      chunkFileNames: '[name].js',
      dir: '../../Public/JavaScript',
      format: 'esm',
      sourcemap: isDev ? 'inline' : false,
    },
    plugins: [
      del({
        targets: '../../Public/JavaScript/*',
        force: true,
      }),
      multiInput({
        relative: 'src/scripts',
      }),
      nodeResolve(),
      terser({
        format: {
          comments: false,
        },
      }),
      typescript({
        outputToFilesystem: true,
      }),
    ],
    external: [
      'lit',
      /^@typo3\//,
    ],
  },
  {
    input: 'src/scripts/backend/extension-configuration.ts',
    output: {
      dir: '../../Public/JavaScript/backend',
      format: 'esm',
      sourcemap: isDev ? 'inline' : false,
      inlineDynamicImports: true,
    },
    context: 'window',
    plugins: [
      commonjs(),
      nodeResolve(),
      terser({
        format: {
          comments: false,
        },
      }),
      typescript({
        outputToFilesystem: true,
      }),
    ],
    external: [
      /^@codemirror\//,
      /^@typo3\//,
    ],
    onwarn: (warning, warn) => {
      // Suppress warning for circular dependency in external module
      if (warning.code !== 'CIRCULAR_DEPENDENCY') {
        warn(warning);
      }
    }
  },
  {
    input: [
      'src/styles/modal.scss',
      '@yaireo/tagify/dist/tagify.css',
    ],
    output: {
      dir: '../../Public/Css',
    },
    plugins: [
      del({
        targets: '../../Public/Css/*',
        force: true,
      }),
      nodeResolve({
        extensions: ['.css'],
      }),
      postcss({
        extract: 'backend.css',
        minimize: isDev ? false : minimizeOptions,
        sourceMap: isDev ? 'inline' : false,
        use: ['sass'],
      }),
      noEmit({
        match: (fileName) => fileName.match(/\.js$/),
      }),
    ],
  }
];
