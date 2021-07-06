/**
 * Module: TYPO3/CMS/Warming/Backend/Modal/CacheWarmupProgressModal
 */
import WarmupProgress from '../../../lib/WarmupProgress';

// Modules
import $ from 'jquery';
import Modal from 'TYPO3/CMS/Backend/Modal';

class CacheWarmupProgressModal {
  private $modal!: JQuery;
  private $progressBar!: JQuery;
  private $allCounter!: JQuery;
  private $failedCounter!: JQuery;

  public createModal(): void {
    const $content = this.buildInitialModalContent();

    // Build initial modal or apply content to existing modal
    if (Modal.currentModal) {
      this.$modal = Modal.currentModal;
      this.$modal.show();
      this.$modal.find('.modal-body').empty().append($content);
    } else {
      this.$modal = this.createModalWithContent($content);
    }

    // Hide footer until cache warmup is finished
    this.$modal.find('.modal-footer').hide();
  }

  public updateProgress(progress: WarmupProgress): void {
    const percent = progress.getProgressInPercent();
    const failedCount = progress.getNumberOfFailedUrls();
    const {current, total} = progress.progress;

    this.$progressBar.attr('aria-valuenow', current);
    this.$progressBar.attr('aria-valuemax', total);
    this.$progressBar.css('width', `${percent}%`);
    this.$progressBar.html(`${percent.toFixed(2)}%`);

    if (failedCount > 0) {
      this.$progressBar.addClass('progress-bar-warning');
      this.$failedCounter.show().html(`Failed: ${failedCount}`);
    }

    this.$allCounter.html(`Processed: <strong>${current}</strong><br>Total: <strong>${total}</strong>`);

    if (current >= total) {
      this.$progressBar.removeClass('progress-bar-warning').addClass(
        failedCount > 0 ? 'progress-bar-danger' : 'progress-bar-success'
      );
    }
  }

  public getModal(): JQuery {
    return this.$modal;
  }

  public getReportButton(): JQuery {
    return this.$modal.find('button[name=tx-warming-open-report]');
  }

  public getRetryButton(): JQuery {
    return this.$modal.find('button[name=tx-warming-retry]');
  }

  public dismiss(): void {
    Modal.dismiss();
  }

  private buildInitialModalContent(): JQuery {
    const $content = $('<div>');

    this.$progressBar = $('<div class="progress-bar progress-bar-striped">')
      .attr('role', 'progressbar')
      .attr('aria-valuemin', 0)
      .attr('aria-valuemax', 0)
      .attr('aria-valuenow', 0);
    this.$allCounter = $('<div>').html('Crawling sitemaps...');
    this.$failedCounter = $('<div class="badge badge-danger">');

    // Hide failed counter until any URL fails to be warmed up
    this.$failedCounter.hide();

    // Append progress bar and counter
    $content
      .append($('<div class="tx-warming-progress progress">').append(this.$progressBar))
      .append($('<div class="tx-warming-counter">').append(this.$allCounter, this.$failedCounter));

    return $content;
  }

  private createModalWithContent($content: JQuery): JQuery {
    return Modal.advanced({
      // @todo use other localization
      title: TYPO3.lang['cacheWarmup.modal.title'],
      content: $content,
      size: Modal.sizes.small,
      buttons: [
        {
          text: 'Show report',
          icon: 'actions-list-alternative',
          btnClass: 'btn-primary',
          name: 'tx-warming-open-report',
        },
        {
          text: 'Run again',
          icon: 'actions-refresh',
          btnClass: 'btn-default',
          name: 'tx-warming-retry',
        },
        {
          text: 'Close',
          btnClass: 'btn-default',
          trigger: () => {
            Modal.dismiss();
          },
        },
      ]
    });
  }
}

export default new CacheWarmupProgressModal();
