/*
 * This file is part of the TYPO3 CMS extension "warming".
 *
 * Copyright (C) 2021-2026 Elias Häußler <elias@haeussler.dev>
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

import {InvalidSiteSelectionException} from '@eliashaeussler/typo3-warming/exception/invalid-site-selection-exception';

/**
 * Site group or site group item within sites modal.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-2.0-or-later
 */
export class SiteSelection {
  constructor(
    private readonly site: string,
    private readonly language: number|null,
    private readonly group: string|null,
  ) {
  }

  /**
   * @throws InvalidSiteSelectionException
   */
  public static fromJson(json: string): SiteSelection {
    const values = JSON.parse(json);

    if (typeof values !== 'object') {
      throw InvalidSiteSelectionException.create();
    }
    if (!('site' in values)) {
      throw InvalidSiteSelectionException.create();
    }

    return new SiteSelection(
      values.site,
      values.language ?? null,
      values.group ?? null,
    )
  }

  public getSiteIdentifier(): string {
    return this.site;
  }

  public getLanguageId(): number|null {
    return this.language;
  }

  public getGroupName(): string|null {
    return this.group;
  }

  public isWithinGroup(): boolean {
    return this.group !== null;
  }

  public isGroupRoot(): boolean {
    return this.isWithinGroup() && this.language === null;
  }

  public isGroupItem(): boolean {
    return this.isWithinGroup() && this.language !== null;
  }
}
