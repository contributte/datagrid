import $ from 'jquery';

import registerExtension from '../utils/registerExtension';

registerExtension('datargid.item_detail', {
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
                } else {
                    row_detail.toggleClass('toggled');
                    row_detail.find('.item-detail-content').slideToggle('fast');
                }
                return false;
            } else {
                return row_detail.addClass('loaded');
            }
        }
    },
    success: function (payload) {
        var id, row_detail;
        if (payload._datagrid_toggle_detail) {
            id = payload._datagrid_toggle_detail;
            row_detail = $('.item-detail-' + id);
            row_detail.toggleClass('toggled');
            return row_detail.find('.item-detail-content').slideToggle('fast');
        }
    }
});
