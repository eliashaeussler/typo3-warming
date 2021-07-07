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

/**
 * Collection of utility functions.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export default class Util {
  /**
   * Append given query parameters to query parameters of given URL.
   *
   * @param url {URL} URL whose query params should be extended
   * @param queryParams {URLSearchParams} Collection of query parameters to be appended to the given URL
   * @returns {URL} The modified URL
   */
  public static mergeUrlWithQueryParams(url: URL, queryParams: URLSearchParams): URL {
    for (const [name, value] of queryParams.entries()) {
      url.searchParams.append(name, value);
    }

    return url;
  }
}
