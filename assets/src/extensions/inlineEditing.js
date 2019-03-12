import $ from 'jquery';

import registerExtension from '../utils/registerExtension';

<<<<<<< HEAD

registerExtension('datagrid.inline-editing', {
    success(payload) {
        if (payload._datagrid_inline_editing) {
            const grid = $(`.datagrid-${payload._datagrid_name}`);
=======
registerExtension('datagrid.inline-editing', {
    success: function (payload) {
        if (payload._datagrid_inline_editing) {
            const grid = $('.datagrid-' + payload._datagrid_name);
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
            grid.find('.datagrid-inline-edit-trigger').addClass('hidden');
        }
    }
});