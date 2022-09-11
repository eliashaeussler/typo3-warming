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

import CrawlingProgress from './Crawler/CrawlingProgress';
import CrawlingResponse from './Crawler/CrawlingResponse';
import CrawlingState from './Crawler/CrawlingState';
import WarmupState from './Enums/WarmupState';

/**
 * Interface describing the data object from a server-sent event handling cache warmup.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
interface WarmupProgressDataObject {
  state?: WarmupState;
  title?: string;
  message?: string;
  progress?: CrawlingProgress;
  urls?: CrawlingState;
}

/**
 * Progress of a running or finished cache warmup request.
 *
 * This class displays the current progress of a running or finished cache
 * warmup request that has been triggered by a concrete instance of
 * {@link RequestHandlerInterface}. Each time the server replies with an
 * update of the request, the event data can be passed over to this class
 * in order to update the internal state.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export default class WarmupProgress {
  public state: WarmupState = WarmupState.Unknown;
  public progress: CrawlingProgress = {current: 0, total: 0};
  public urls: CrawlingState = {failed: [], successful: []};
  public response: CrawlingResponse = {title: '', message: ''};

  constructor(data?: WarmupProgressDataObject) {
    if (data) {
      this.update(data);
    }
  }

  /**
   * Update internal state of a cache warmup request progress from given data.
   *
   * @param data {WarmupProgressDataObject} An object that displays the current progress of a cache warmup request
   * @returns {WarmupProgress} This instance
   */
  public update(data: WarmupProgressDataObject): this {
    if (data.state && Object.values(WarmupState).includes(data.state as WarmupState)) {
      this.state = data.state;
    }
    if (data.progress) {
      this.progress = data.progress;
    }
    if (data.urls) {
      this.urls = data.urls;
    }
    if (data.title) {
      this.response.title = data.title;
    }
    if (data.message) {
      this.response.message = data.message;
    }

    return this;
  }

  /**
   * Get number of URLs that failed to be warmed up.
   *
   * @returns {number} Number of URLs that failed to be warmed up
   */
  public getNumberOfFailedUrls(): number {
    return this.urls.failed.length;
  }

  /**
   * Get number of successfully warmed up URLs.
   *
   * @returns {number} Number of successfully warmed up URLs.
   */
  public getNumberOfSuccessfulUrls(): number {
    return this.urls.successful.length;
  }

  /**
   * Get number of current progress in percent.
   *
   * @returns {number} A number object that displays the current progress in percent
   */
  public getProgressInPercent(): number {
    if (0 !== this.progress.total) {
      return Number((this.progress.current / this.progress.total) * 100);
    }

    return Number(0);
  }

  /**
   * Test whether the cache warmup is finished.
   *
   * @returns {boolean} `true` if cache warmup is finished, `false` otherwise
   */
  public isFinished(): boolean {
    return this.progress.current >= this.progress.total;
  }
}
