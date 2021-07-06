/**
 * Module: TYPO3/CMS/Warming/Backend/Toolbar/CacheWarmupMenu
 */
import WarmupProgress from '../../../lib/WarmupProgress';
import WarmupRequest from '../../../lib/WarmupRequest';
import WarmupRequestMode from '../../../lib/Enums/WarmupRequestMode';
import WarmupRequestType from '../../../lib/Enums/WarmupRequestType';
import WarmupState from '../../../lib/Enums/WarmupState';

// Modules
import $ from 'jquery';
import Icons from 'TYPO3/CMS/Backend/Icons';
import Notification from 'TYPO3/CMS/Backend/Notification';
import Viewport from 'TYPO3/CMS/Backend/Viewport';
import AjaxRequest from 'TYPO3/CMS/Core/Ajax/AjaxRequest';
import AjaxResponse from 'TYPO3/CMS/Core/Ajax/AjaxResponse';
import CacheWarmupProgressModal from '../Modal/CacheWarmupProgressModal';
import CacheWarmupReportModal from '../Modal/CacheWarmupReportModal';

enum CacheWarmupMenuSelectors {
  container = '#eliashaeussler-typo3warming-backend-toolbaritems-cachewarmuptoolbaritem',
  dropdownTable = '.dropdown-table',
  menuItem = 'a.toolbar-cache-warmup-action',
  toolbarIcon = '.toolbar-item-icon .t3js-icon',
  useragentCopy = 'button.toolbar-cache-warmup-useragent-copy-action',
  useragentCopyIcon = '.t3js-icon',
  useragentCopyText = '.toolbar-cache-warmup-useragent-copy-text'
}


export class CacheWarmupMenu {
  private notificationDuration = 15;

  constructor() {
    Viewport.Topbar.Toolbar.registerEvent(() => this.initializeEvents());
  }

  public initializeEvents(): void {
    $(CacheWarmupMenuSelectors.container).ready(() => this.fetchSites());

    $(CacheWarmupMenuSelectors.container).on('click', CacheWarmupMenuSelectors.menuItem, (event: JQuery.TriggeredEvent) => {
      event.preventDefault();
      const pageId = $(event.currentTarget).attr('data-page-id');
      if (pageId) {
        this.warmupCache(Number(pageId));
      }
    });

    $(CacheWarmupMenuSelectors.container).on('click', CacheWarmupMenuSelectors.useragentCopy, (event: JQuery.TriggeredEvent) => {
      event.preventDefault();
      const userAgent = $(event.currentTarget).attr('data-text');
      if (userAgent) {
        this.copyUserAgentToClipboard(userAgent);
      }
    });
  }

  public warmupCache(pageId: number, mode: WarmupRequestMode = WarmupRequestMode.Site): void {
    const $toolbarItemIcon = $(CacheWarmupMenuSelectors.toolbarIcon, CacheWarmupMenuSelectors.container);
    const $existingIcon = $toolbarItemIcon.clone();

    // Close dropdown menu
    $(CacheWarmupMenuSelectors.container).removeClass('open');

    // Show spinner during cache warmup
    Icons.getIcon('spinner-circle-light', Icons.sizes.small).then((spinner: string) => {
      $toolbarItemIcon.replaceWith(spinner);
    });

    const request = new WarmupRequest(pageId, mode);
    request.runWarmup()
      .then(
        // Success
        (data: WarmupProgress) => {
          this.showNotification(data);

          // Apply trigger function to "retry" button of progress modal
          if (WarmupRequestType.EventSource === request.requestType) {
            CacheWarmupProgressModal.getRetryButton()
              .off('button.clicked')
              .on('button.clicked', () => {
                this.warmupCache(pageId, mode)
              });
          }
        },
        // Error
        this.errorNotification,
      )
      .finally(() => {
        $(CacheWarmupMenuSelectors.toolbarIcon, CacheWarmupMenuSelectors.container).replaceWith($existingIcon);
      });
  }

  private fetchSites(): void {
    const $toolbarItemIcon = $(CacheWarmupMenuSelectors.toolbarIcon, CacheWarmupMenuSelectors.container);
    const $existingIcon = $toolbarItemIcon.clone();

    // Close dropdown menu
    $(CacheWarmupMenuSelectors.container).removeClass('open');

    // Show spinner during cache warmup
    Icons.getIcon('spinner-circle-light', Icons.sizes.small).then((spinner: string) => {
      $toolbarItemIcon.replaceWith(spinner);
    });

    // Fetch rendered sites
    (new AjaxRequest(TYPO3.settings.ajaxUrls.tx_warming_fetch_sites))
      .get()
      .then(
        async (response: typeof AjaxResponse) => {
          const data = await response.resolve();
          const $table = $(CacheWarmupMenuSelectors.dropdownTable, CacheWarmupMenuSelectors.container);

          $table.html(data);
        }
      )
      .finally(() => {
        $(CacheWarmupMenuSelectors.toolbarIcon, CacheWarmupMenuSelectors.container).replaceWith($existingIcon);
      });
  }

  private copyUserAgentToClipboard(userAgent: string): void {
    const $copyIcon = $(CacheWarmupMenuSelectors.useragentCopyIcon, CacheWarmupMenuSelectors.useragentCopy);
    const $existingIcon = $copyIcon.clone();

    // Show spinner when copying user agent
    Icons.getIcon('spinner-circle-light', Icons.sizes.small).then((spinner: string) => {
      $copyIcon.replaceWith(spinner);
    });

    // Copy user agent to clipboard
    Promise.all([
      navigator.clipboard.writeText(userAgent),
      Icons.getIcon('actions-check', Icons.sizes.small),
    ])
      .then(
        async ([, icon]) => {
          $(CacheWarmupMenuSelectors.useragentCopyText).text(TYPO3.lang['cacheWarmup.toolbar.copy.successful']);
          $(CacheWarmupMenuSelectors.useragentCopyIcon, CacheWarmupMenuSelectors.useragentCopy).replaceWith(icon);
        },
        () => {
          $(CacheWarmupMenuSelectors.useragentCopyIcon, CacheWarmupMenuSelectors.useragentCopy).replaceWith($existingIcon);
        }
      );
  }

  private showNotification(progress: WarmupProgress): void {
    const {title, message} = progress.response;

    // Create action to open full report as modal
    const modalAction = CacheWarmupReportModal.createModalAction(progress);

    // Show notification
    switch (progress.state) {
      case WarmupState.Failed:
        Notification.error(title, message, this.notificationDuration, [modalAction]);
        break;
      case WarmupState.Warning:
        Notification.warning(title, message, this.notificationDuration, [modalAction]);
        break;
      case WarmupState.Success:
        Notification.success(title, message, this.notificationDuration, [modalAction]);
        break;
      case WarmupState.Unknown:
        Notification.notice(title, message, this.notificationDuration);
        break;
      default:
        this.errorNotification();
        break;
    }
  }

  private errorNotification(): void {
    Notification.error(TYPO3.lang['cacheWarmup.notification.error.title'], TYPO3.lang['cacheWarmup.notification.error.message']);
  }
}

export default new CacheWarmupMenu();
