/**
 * Module: TYPO3/CMS/Warming/Backend/Modal/CacheWarmupReportModal
 */
import WarmupProgress from '../../../lib/WarmupProgress';

// Modules
import $ from 'jquery';
import ImmediateAction from 'TYPO3/CMS/Backend/ActionButton/ImmediateAction';
import Icons from 'TYPO3/CMS/Backend/Icons';
import Modal from 'TYPO3/CMS/Backend/Modal';

class CacheWarmupReportModal {
  private progress!: WarmupProgress;
  private panelCount = 0;

  public createModalAction(progress: WarmupProgress): { label: string, action: typeof ImmediateAction } {
    return {
      label: TYPO3.lang['cacheWarmup.notification.action.showReport'],
      action: new ImmediateAction(() => this.createModal(progress)),
    };
  }

  public createModal(progress: WarmupProgress): void {
    this.progress = progress;

    Promise.all<string, string>([
      Icons.getIcon('actions-view-page', Icons.sizes.small),
      Icons.getIcon('content-info', Icons.sizes.small),
    ])
      .then(([viewPageIcon, infoIcon]) => {
        // Ensure all other modals are closed
        Modal.dismiss();

        // Reset count of panels in report
        this.panelCount = 0;

        // Build content
        const $content = this.buildModalContent(viewPageIcon, infoIcon);

        // Open modal with crawling report
        Modal.advanced({
          title: TYPO3.lang['cacheWarmup.modal.title'],
          content: $content,
          size: Modal.sizes.large,
        });
      });
  }

  private createPanel(title: string, state: string, urls: string[], viewPageIcon: string) {
    this.panelCount++;

    return $('<div>')
      .addClass(`panel panel-${state} panel-table`)
      .addClass((): string => {
        if (this.panelCount > 1) {
          return 'panel-space';
        }
        return '';
      })
      .append(
        // Add panel header
        $('<div>')
          .addClass('panel-heading')
          .text(`${title} (${urls.length})`),
        // Add panel content
        $('<div>')
          .addClass('table-fit table-fit-wrap')
          .append(
            // Add table
            $('<table>')
              .addClass('table table-striped table-hover')
              .append(
                // Add table body
                $('<tbody>').append(
                  urls.map((url) => {
                    // Add table row for each URL
                    return $('<tr>').append(
                      // Add URL as table cell
                      $('<td>').addClass('col-title').text(url),
                      // Add controls as table cell
                      $('<td>').addClass('col-control').append(
                        $('<a>')
                          .attr('href', url)
                          .attr('target', '_blank')
                          .addClass('btn btn-default btn-sm')
                          .html(`${viewPageIcon} ${TYPO3.lang['cacheWarmup.modal.action.view']}`)
                      )
                    ); // End: table row
                  })
                ) // End: table body
              ) // End: table
          ) // End: panel content
      );
  }

  private buildModalContent(viewPageIcon: string, infoIcon: string): JQuery {
    const $content = $('<div>');

    // Build panels from crawled URLs and the appropriate crawling states
    if (this.progress.getNumberOfFailedUrls() > 0) {
      $content.append(
        this.createPanel(
          TYPO3.lang['cacheWarmup.modal.panel.failed'],
          'danger',
          this.progress.urls.failed,
          viewPageIcon
        )
      );
    }
    if (this.progress.getNumberOfSuccessfulUrls() > 0) {
      $content.append(
        this.createPanel(
          TYPO3.lang['cacheWarmup.modal.panel.successful'],
          'success',
          this.progress.urls.successful,
          viewPageIcon
        )
      );
    }

    // Add number of totally crawled pages
    const totalText = this.progress.progress.total > 0
      ? `${TYPO3.lang['cacheWarmup.modal.total']} ${this.progress.progress.total}`
      : TYPO3.lang['cacheWarmup.modal.message.noUrlsCrawled'];
    $content.append(
      $('<div>').addClass('badge badge-info').html(`${infoIcon} ${totalText}`)
    );

    return $content;
  }
}

export default new CacheWarmupReportModal();
