/* eslint-disable @typescript-eslint/no-explicit-any */

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

import {v4 as uuidv4} from 'uuid';

/**
 * Collection of string utility functions.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export class StringHelper {
  /**
   * Get formatted string.
   *
   * Formats the given string with given values and returns the formatted string.
   *
   * @param format {string} String to be formatted with given values
   * @param values Values to be used as replacements in formatted string
   * @returns {string} Formatted string
   * @see https://stackoverflow.com/a/31007976
   */
  public static formatString(format: string, ...values: any[]): string {
    return values.reduce((p: string, c: any, index: number): string => p.replace(new RegExp(`\\{${index}}`), c), format);
  }

  /**
   * Generate unique ID.
   *
   * @returns {string} Unique ID
   * @private
   */
  public static generateUniqueId(): string {
    return uuidv4();
  }
}
