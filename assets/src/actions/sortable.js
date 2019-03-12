import $ from 'jquery';

import ajaxCall from '../utils/ajaxCall';

export default function() {
    if (typeof $.fn.sortable === 'undefined') {
        return;
    }

    return $('.datagrid [data-sortable]').sortable({
        handle: '.handle-sort',
        items: 'tr',
        axis: 'y',
        update(event, ui) {
            const row = ui.item.closest('tr[data-id]');

            const item_id = row.data('id');
            let prev_id = null;
            let next_id = null;

            if (row.prev().length) {
                prev_id = row.prev().data('id');
            }
            if (row.next().length) {
                next_id = row.next().data('id');
            }

            const url = $(this).data('sortable-url');

            const data = {};
            const component_prefix = row.closest('.datagrid').find('tbody').attr('data-sortable-parent-path');

            data[(component_prefix + '-item_id').replace(/^-/, '')] = item_id;

            if (prev_id !== null) {
                data[(component_prefix + '-prev_id').replace(/^-/, '')] = prev_id;
            }

            if (next_id !== null) {
                data[(component_prefix + '-next_id').replace(/^-/, '')] = next_id;
            }

            ajaxCall({
                type: 'GET',
                url,
                data,
                error(jqXHR) {
                    alert(jqXHR.statusText);
                }
            });
        },
        helper(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        }
    });
}

