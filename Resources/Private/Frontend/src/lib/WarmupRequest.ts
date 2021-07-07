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

import AjaxRequestHandler from './RequestHandler/AjaxRequestHandler';
import EventSourceRequestHandler from './RequestHandler/EventSourceRequestHandler';
import WarmupRequestMode from './Enums/WarmupRequestMode';
import WarmupRequestType from './Enums/WarmupRequestType';
import WarmupProgress from './WarmupProgress';

/**
 * Request to process a new cache warmup for a given page or site.
 *
 * This class represents a complete request for cache warmup of a given page or site.
 * It uses a concrete {@link RequestHandlerInterface}, depending on the availability
 * of the concrete handlers.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export default class WarmupRequest {
  public readonly requestType: WarmupRequestType;
  private readonly requestId: string;
  private readonly pageId: number;
  private readonly mode: WarmupRequestMode;

  constructor(pageId: number, mode: WarmupRequestMode = WarmupRequestMode.Site) {
    this.requestType = EventSourceRequestHandler.isSupported() ? WarmupRequestType.EventSource : WarmupRequestType.Ajax;
    this.requestId = WarmupRequest.generateRequestId();
    this.pageId = pageId;
    this.mode = mode;
  }

  /**
   * Trigger new cache warmup using a concrete request handler.
   *
   * Uses a concrete request handler to trigger a new cache warmup request on the
   * server. The warmup progress is returned as Promise that resolves to a concrete
   * {@link WarmupProgress}.
   *
   * @returns {Promise<WarmupProgress>} A promise for the the current request that resolves to an instance of {@link WarmupProgress}
   */
  public runWarmup(): Promise<WarmupProgress> {
    if (WarmupRequestType.EventSource === this.requestType) {
      return this.doWarmupWithEventSource();
    }

    return this.doWarmupWithAjax();
  }

  /**
   * Trigger new cache warmup using the {@link EventSourceRequestHandler}.
   *
   * @returns {Promise<WarmupProgress>} A promise for the the current request that resolves to an instance of {@link WarmupProgress}
   * @private
   */
  private doWarmupWithEventSource(): Promise<WarmupProgress> {
    const handler = new EventSourceRequestHandler();

    return handler.startRequestWithQueryParams(this.getQueryParams());
  }

  /**
   * Trigger new cache warmup using the {@link AjaxRequestHandler}.
   *
   * @returns {Promise<WarmupProgress>} A promise for the the current request that resolves to an instance of {@link WarmupProgress}
   * @private
   */
  private doWarmupWithAjax(): Promise<WarmupProgress> {
    const handler = new AjaxRequestHandler();

    return handler.startRequestWithQueryParams(this.getQueryParams());
  }

  /**
   * Return set of query params to be used fo cache warmup requests.
   *
   * @returns {URLSearchParams} Set of query params to be used for cache warmup requests
   * @private
   */
  private getQueryParams(): URLSearchParams {
    return new URLSearchParams({
      pageId: this.pageId.toString(),
      mode: this.mode,
      requestId: this.requestId,
    });
  }

  /**
   * Generate unique request ID.
   *
   * @returns {string} Unique request ID
   * @private
   */
  private static generateRequestId(): string {
    return uuidv4();
  }
}
