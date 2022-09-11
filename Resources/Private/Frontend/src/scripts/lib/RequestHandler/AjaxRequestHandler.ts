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

import RequestHandlerInterface from './RequestHandlerInterface';
import Util from '../Util';
import WarmupProgress from '../WarmupProgress';

// Modules
import AjaxRequest from 'TYPO3/CMS/Core/Ajax/AjaxRequest';
import AjaxResponse from 'TYPO3/CMS/Core/Ajax/AjaxResponse';

/**
 * Cache warmup request handler using AJAX requests.
 *
 * This class represents an request handler for cache warmup requests, handled
 * by an AJAX request. It should only be used if the preferred request handler,
 * {@link EventSourceRequestHandler}, is not available since it is not able to
 * display the current progress of a concrete warmup request.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export default class AjaxRequestHandler implements RequestHandlerInterface {
  public startRequestWithQueryParams(queryParams:URLSearchParams): Promise<WarmupProgress> {
    return (new AjaxRequest(this.getUrl(queryParams).toString()))
      .post({})
      .then(
        async (response: typeof AjaxResponse): Promise<WarmupProgress> => {
          const data = await response.resolve();
          return new WarmupProgress(data);
        }
      );
  }

  public getUrl(queryParams: URLSearchParams): URL {
    const url = new URL(TYPO3.settings.ajaxUrls.tx_warming_cache_warmup_legacy, window.location.origin);

    return Util.mergeUrlWithQueryParams(url, queryParams);
  }
}
