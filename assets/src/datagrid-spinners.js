import $ from 'jquery';
import registerExtension from './utils/registerExtension';

import './datagrid-spinners.scss';

registerExtension('ublaboo-spinners', {
    before(xhr, settings) {
        if (settings.nette) {
            const { el } = settings.nette;
            const spinner_template = $('<div class="ublaboo-spinner ublaboo-spinner-small"><i></i><i></i><i></i><i></i></div>');

            if (el.is('.datagrid [name="group_action[submit]"]')) {
                el.after(spinner_template);

            } else if (el.is('.datagrid a') && el.data('toggle-detail')) {
                const id = settings.nette.el.attr('data-toggle-detail');
                const row_detail = $(`.item-detail-${id}`);

                if (!row_detail.hasClass('loaded')) {
                    el.addClass('ublaboo-spinner-icon');
                }

            } else if (el.is('.datagrid .col-pagination a')) {
                el.closest('.row-grid-bottom').find('.col-per-page').prepend(spinner_template);
            } else if (el.is('.datagrid .datagrid-per-page-submit')) {
                el.closest('.row-grid-bottom').find('.col-per-page').prepend(spinner_template);
            } else if (el.is('.datagrid .reset-filter')) {
                el.closest('.row-grid-bottom').find('.col-per-page').prepend(spinner_template);
            }
        }
    },
    complete() {
        $('.ublaboo-spinner').remove();
        $('.ublaboo-spinner-icon').removeClass('ublaboo-spinner-icon');
    }
});
