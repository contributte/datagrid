import $ from 'jquery';

import ajaxCall from '../utils/ajaxCall';

export default function () {
    if (typeof $('.datagrid-tree-item-children').sortable === 'undefined') {
        return;
    }
    return $('.datagrid-tree-item-children').sortable({
        handle: '.handle-sort',
        items: '.datagrid-tree-item:not(.datagrid-tree-header)',
        toleranceElement: '> .datagrid-tree-item-content',
        connectWith: '.datagrid-tree-item-children',
        update: function (event, ui) {
<<<<<<< HEAD
            $('.toggle-tree-to-delete').remove();
            const row = ui.item.closest('.datagrid-tree-item[data-id]');
            let item_id = row.data('id');
            let prev_id = null;
            let next_id = null;
            let parent_id = null;
=======
            var component_prefix, data, item_id, next_id, parent, parent_id, prev_id, row, url;
            $('.toggle-tree-to-delete').remove();
            row = ui.item.closest('.datagrid-tree-item[data-id]');
            item_id = row.data('id');
            prev_id = null;
            next_id = null;
            parent_id = null;
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
            if (row.prev().length) {
                prev_id = row.prev().data('id');
            }
            if (row.next().length) {
                next_id = row.next().data('id');
            }
<<<<<<< HEAD
            const parent = row.parent().closest('.datagrid-tree-item');
=======
            parent = row.parent().closest('.datagrid-tree-item');
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
            if (parent.length) {
                parent.find('.datagrid-tree-item-children').first().css({
                    display: 'block'
                });
                parent.addClass('has-children');
                parent_id = parent.data('id');
            }
<<<<<<< HEAD
            const url = $(this).data('sortable-url');
=======
            url = $(this).data('sortable-url');
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
            if (!url) {
                return;
            }
            parent.find('[data-toggle-tree]').first().removeClass('hidden');
<<<<<<< HEAD
            const component_prefix = row.closest('.datagrid-tree').attr('data-sortable-parent-path');
            const data = {};
=======
            component_prefix = row.closest('.datagrid-tree').attr('data-sortable-parent-path');
            data = {};
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
            data[(component_prefix + '-item_id').replace(/^-/, '')] = item_id;
            if (prev_id !== null) {
                data[(component_prefix + '-prev_id').replace(/^-/, '')] = prev_id;
            }
            if (next_id !== null) {
                data[(component_prefix + '-next_id').replace(/^-/, '')] = next_id;
            }
            data[(component_prefix + '-parent_id').replace(/^-/, '')] = parent_id;
            ajaxCall({
                type: 'GET',
                url: url,
                data: data,
<<<<<<< HEAD
                error(jqXHR, textStatus, errorThrown) {
                    if (errorThrown !== 'abort') {
                        alert(jqXHR.statusText);
=======
                error: function (jqXHR, textStatus, errorThrown) {
                    if (errorThrown !== 'abort') {
                        return alert(jqXHR.statusText);
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
                    }
                }
            });
        },
<<<<<<< HEAD
        stop() {
            $('.toggle-tree-to-delete').removeClass('toggle-tree-to-delete');
        },
        start(event, ui) {
            const parent = ui.item.parent().closest('.datagrid-tree-item');
            if (parent.length) {
                if (parent.find('.datagrid-tree-item').length === 2) {
                    parent.find('[data-toggle-tree]').addClass('toggle-tree-to-delete');
=======
        stop: function () {
            return $('.toggle-tree-to-delete').removeClass('toggle-tree-to-delete');
        },
        start: function (event, ui) {
            var parent;
            parent = ui.item.parent().closest('.datagrid-tree-item');
            if (parent.length) {
                if (parent.find('.datagrid-tree-item').length === 2) {
                    return parent.find('[data-toggle-tree]').addClass('toggle-tree-to-delete');
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
                }
            }
        }
    });
}
