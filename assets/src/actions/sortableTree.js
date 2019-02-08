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
            var component_prefix, data, item_id, next_id, parent, parent_id, prev_id, row, url;
            $('.toggle-tree-to-delete').remove();
            row = ui.item.closest('.datagrid-tree-item[data-id]');
            item_id = row.data('id');
            prev_id = null;
            next_id = null;
            parent_id = null;
            if (row.prev().length) {
                prev_id = row.prev().data('id');
            }
            if (row.next().length) {
                next_id = row.next().data('id');
            }
            parent = row.parent().closest('.datagrid-tree-item');
            if (parent.length) {
                parent.find('.datagrid-tree-item-children').first().css({
                    display: 'block'
                });
                parent.addClass('has-children');
                parent_id = parent.data('id');
            }
            url = $(this).data('sortable-url');
            if (!url) {
                return;
            }
            parent.find('[data-toggle-tree]').first().removeClass('hidden');
            component_prefix = row.closest('.datagrid-tree').attr('data-sortable-parent-path');
            data = {};
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
                error: function (jqXHR, textStatus, errorThrown) {
                    if (errorThrown !== 'abort') {
                        return alert(jqXHR.statusText);
                    }
                }
            });
        },
        stop: function () {
            return $('.toggle-tree-to-delete').removeClass('toggle-tree-to-delete');
        },
        start: function (event, ui) {
            var parent;
            parent = ui.item.parent().closest('.datagrid-tree-item');
            if (parent.length) {
                if (parent.find('.datagrid-tree-item').length === 2) {
                    return parent.find('[data-toggle-tree]').addClass('toggle-tree-to-delete');
                }
            }
        }
    });
}
