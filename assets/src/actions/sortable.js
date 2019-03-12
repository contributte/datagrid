import $ from 'jquery';

import ajaxCall from '../utils/ajaxCall';

<<<<<<< HEAD
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
=======
export default function () {
    if (typeof $.fn.sortable === 'undefined') {
        return;
    }
    $('.datagrid [data-sortable]').sortable({
        handle: '.handle-sort',
        items: 'tr',
        axis: 'y',
        update: function (event, ui) {
            const row = ui.item.closest('tr[data-id]');
            const item_id = row.data('id');

            let prev_id, next_id = null;
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568

            if (row.prev().length) {
                prev_id = row.prev().data('id');
            }
<<<<<<< HEAD

=======
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
            if (row.next().length) {
                next_id = row.next().data('id');
            }

            const url = $(this).data('sortable-url');
<<<<<<< HEAD

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
=======
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
                url: url,
                data: data,
                error: jqXHR => alert(jqXHR.statusText)
            });
        },
        helper: function (e, ui) {
            ui.children().each(function () {
                return $(this).width($(this).width());
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
            });
            return ui;
        }
    });
}

