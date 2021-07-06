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
class WarmupRequest {
  public readonly requestType: WarmupRequestType;
  private readonly requestId: string;
  private readonly pageId: number;
  private readonly mode: WarmupRequestMode;

  constructor(pageId: number, mode: WarmupRequestMode = WarmupRequestMode.Site) {
    this.requestId = WarmupRequest.generateRequestId();
    this.pageId = pageId;
    this.mode = mode;
    this.requestType = EventSourceRequestHandler.isSupported() ? WarmupRequestType.EventSource : WarmupRequestType.Ajax;
  }

  /**
   * Trigger new cache warmup using a concrete request handler.
   *
   * Uses a concrete request handler to trigger a new cache warmup request on the
   * server. The warmup progress is returned as Promise that resolves to a concrete
   * {@link WarmupProgress}.
   *
   * @returns Promise<WarmupProgress>
   */
  public runWarmup(): Promise<WarmupProgress> {
    if (WarmupRequestType.EventSource === this.requestType) {
      return this.doWarmupWithEventSource();
    }

    return this.doWarmupWithAjax();
  }

  private doWarmupWithEventSource(): Promise<WarmupProgress> {
    const handler = new EventSourceRequestHandler();

    return handler.startRequestWithQueryParams(this.getQueryParams());
  }

  private doWarmupWithAjax(): Promise<WarmupProgress> {
    const handler = new AjaxRequestHandler();

    return handler.startRequestWithQueryParams(this.getQueryParams());
  }

  private getQueryParams(): URLSearchParams {
    return new URLSearchParams({
      pageId: this.pageId.toString(),
      mode: this.mode,
      requestId: this.requestId
    });
  }

  private static generateRequestId(): string {
    return uuidv4();
  }
}

export default WarmupRequest;
