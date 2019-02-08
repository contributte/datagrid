import $ from 'jquery';

import registerExtension from '../utils/registerExtension';

// Datagrid after big inline edit notification

registerExtension('datagrid.after_inline_edit', {
    success: function(payload) {
        const grid = $('.datagrid-' + payload._datagrid_name);
        if (payload._datagrid_inline_edited) {
            grid.find('tr[data-id=' + payload._datagrid_inline_edited + '] > td').addClass('edited');
            grid.find('.datagrid-inline-edit-trigger').removeClass('hidden');
        } else if (payload._datagrid_inline_edit_cancel) {
            grid.find('.datagrid-inline-edit-trigger').removeClass('hidden');
        }
    }
});