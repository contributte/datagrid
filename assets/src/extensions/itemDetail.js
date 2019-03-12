import $ from 'jquery';

import registerExtension from '../utils/registerExtension';

registerExtension('datargid.item_detail', {
<<<<<<< HEAD
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
=======
    before: function (xhr, settings) {
        var id, row_detail;
        if (settings.nette && settings.nette.el.attr('data-toggle-detail')) {
            id = settings.nette.el.attr('data-toggle-detail');
            row_detail = $('.item-detail-' + id);
            if (row_detail.hasClass('loaded')) {
                if (!row_detail.find('.item-detail-content').length) {
                    row_detail.removeClass('toggled');
                    return true;
                }
                if (row_detail.hasClass('toggled')) {
                    row_detail.find('.item-detail-content').slideToggle('fast', (function () {
                        return function () {
                            return row_detail.toggleClass('toggled');
                        };
                    })(this));
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
                } else {
                    row_detail.toggleClass('toggled');
                    row_detail.find('.item-detail-content').slideToggle('fast');
                }
<<<<<<< HEAD

                return false;
            } else {
                //row.addClass('detail-loaded')
=======
                return false;
            } else {
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
                return row_detail.addClass('loaded');
            }
        }
    },
<<<<<<< HEAD

    success(payload) {
        if (payload._datagrid_toggle_detail) {
            const id = payload._datagrid_toggle_detail;
            const grid = $(`.datagrid-${payload._datagrid_name}`);
            const row_detail = grid.find(`.item-detail-${id}`);

=======
    success: function (payload) {
        var id, row_detail;
        if (payload._datagrid_toggle_detail) {
            id = payload._datagrid_toggle_detail;
            row_detail = $('.item-detail-' + id);
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
            row_detail.toggleClass('toggled');
            return row_detail.find('.item-detail-content').slideToggle('fast');
        }
    }
<<<<<<< HEAD
});
=======
});
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
