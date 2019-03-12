import $ from 'jquery';

import registerExtension from '../utils/registerExtension';

<<<<<<< HEAD

// Datagrid after big inline edit notification
//
registerExtension('datagrid.after_inline_edit', {
    success(payload) {
        const grid = $(`.datagrid-${payload._datagrid_name}`);

        if (payload._datagrid_inline_edited) {
            grid.find(`tr[data-id=${payload._datagrid_inline_edited}] > td`).addClass('edited');
            return grid.find('.datagrid-inline-edit-trigger').removeClass('hidden');
        } else if (payload._datagrid_inline_edit_cancel) {
            return grid.find('.datagrid-inline-edit-trigger').removeClass('hidden');
=======
// Datagrid after big inline edit notification

registerExtension('datagrid.after_inline_edit', {
    success: function(payload) {
        const grid = $('.datagrid-' + payload._datagrid_name);
        if (payload._datagrid_inline_edited) {
            grid.find('tr[data-id=' + payload._datagrid_inline_edited + '] > td').addClass('edited');
            grid.find('.datagrid-inline-edit-trigger').removeClass('hidden');
        } else if (payload._datagrid_inline_edit_cancel) {
            grid.find('.datagrid-inline-edit-trigger').removeClass('hidden');
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
        }
    }
});