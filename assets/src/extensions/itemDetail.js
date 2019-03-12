import $ from 'jquery';

import registerExtension from '../utils/registerExtension';

registerExtension('datargid.item_detail', {
    before(xhr, settings) {
        if (settings.nette && settings.nette.el.attr('data-toggle-detail')) {
            const id = settings.nette.el.attr('data-toggle-detail');
            const grid = $(`.datagrid-${settings.nette.el.attr('data-toggle-detail-grid')}`);
            const row_detail = grid.find(`.item-detail-${id}`);
            //row = row_detail.closest('.datagrid').find('tr[data-id=' + id + ']')

            //if row_detail.hasClass('loaded') and row.hasClass('detail-loaded')
            if (row_detail.hasClass('loaded')) {
                if (!row_detail.find('.item-detail-content').length) {
                    row_detail.removeClass('toggled');

                    return true;
                }

                if (row_detail.hasClass('toggled')) {
                    row_detail.find('.item-detail-content').slideToggle('fast', () => {
                        return row_detail.toggleClass('toggled');
                    });
                } else {
                    row_detail.toggleClass('toggled');
                    row_detail.find('.item-detail-content').slideToggle('fast');
                }

                return false;
            } else {
                //row.addClass('detail-loaded')
                return row_detail.addClass('loaded');
            }
        }
    },

    success(payload) {
        if (payload._datagrid_toggle_detail) {
            const id = payload._datagrid_toggle_detail;
            const grid = $(`.datagrid-${payload._datagrid_name}`);
            const row_detail = grid.find(`.item-detail-${id}`);

            row_detail.toggleClass('toggled');
            return row_detail.find('.item-detail-content').slideToggle('fast');
        }
    }
});
