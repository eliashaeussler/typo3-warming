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

/* eslint-disable @typescript-eslint/no-explicit-any */

declare const TYPO3: any;
declare const nothing: any;

declare type NotificationOpenEvent = CustomEvent<void> & {
  target: HTMLElement,
};

declare interface DocumentEventMap {
  'typo3-modal-shown': CustomEvent;
  'typo3-notification-open': NotificationOpenEvent;
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/backend/action-button/immediate-action.ts
 */
declare module '@typo3/backend/action-button/immediate-action.js' {
  export default nothing;
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/backend/element/alert-element.ts
 */
declare module '@typo3/backend/element/alert-element.js' {
  export default nothing;
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/backend/element/progress-bar-element.ts
 */
declare module '@typo3/backend/element/progress-bar-element.js' {
  export default nothing;
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/backend/element/progress-bar-element.ts
 */
declare module '@typo3/backend/enum/severity.js' {
  export enum SeverityEnum {
    notice = -2,
    info = -1,
    ok = 0,
    warning = 1,
    error = 2,
  }
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/backend/icons.ts
 */
declare module '@typo3/backend/icons.js' {
  export default nothing;
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/backend/info-window.ts
 */
declare module '@typo3/backend/info-window.js' {
  export default nothing;
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/backend/modal.ts
 */
declare module '@typo3/backend/modal.js' {
  export default nothing;
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/backend/notification.ts
 */
declare module '@typo3/backend/notification.js' {
  export default nothing;
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/core/ajax/ajax-request.ts
 */
declare module '@typo3/core/ajax/ajax-request.js' {
  export default nothing;
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/core/ajax/ajax-response.ts
 */
declare module '@typo3/core/ajax/ajax-response.js' {
  export default nothing;
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/core/document-service.ts
 */
declare module '@typo3/core/document-service.js' {
  export default nothing;
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/core/event/regular-event.ts
 */
declare module '@typo3/core/event/regular-event.js' {
  export default nothing;
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/Sources/TypeScript/core/lit-helper.ts
 */
declare module '@typo3/core/lit-helper.js' {
  const lll: any;
  export {lll};
}

/**
 * @see https://github.com/TYPO3/typo3/blob/v14.2.0/Build/types/bootstrap-src/index.d.ts
 */
declare module 'bootstrap' {
  const Collapse: any;
  export {Collapse};
}
