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

import WarmupProgress from '../WarmupProgress';

/**
 * Request handler that is able to handle cache warmup requests.
 *
 * This interface represents a request handler for cache warmup requests. It is
 * able to trigger a cache warmup request using a given set of {@link URLSearchParams}.
 * Each request returns a {@link Promise} that resolves to a concrete object of
 * {@link WarmupProgress}.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
interface RequestHandlerInterface {
  /**
   * Trigger cache warmup request with given query parameters.
   *
   * Starts a server request that triggers a new cache warmup. Each request resolves
   * to a concrete {@link WarmupProgress} that displays the request progress and
   * result.
   *
   * @param queryParams {URLSearchParams} Set of query parameters to be appended to the request URL
   * @returns Promise<WarmupProgress> A promise for the started request that resolves to an instance of {@link WarmupProgress}
   */
  startRequestWithQueryParams(queryParams: URLSearchParams): Promise<WarmupProgress>;

  /**
   * Build request URL using given query parameters.
   *
   * Returns a complete URL that can be used for requests that trigger a cache warmup
   * on server-side.
   *
   * @param queryParams {URLSearchParams} Set of query parameters to be appended to the request URL
   * @returns URL The complete request URL
   */
  getUrl(queryParams: URLSearchParams): URL;
}

export default RequestHandlerInterface;
