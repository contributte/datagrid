import $ from 'jquery';

import registerExtension from '../utils/registerExtension';


registerExtension('datagrid.inline-editing', {
    success(payload) {
        if (payload._datagrid_inline_editing) {
            const grid = $(`.datagrid-${payload._datagrid_name}`);
            grid.find('.datagrid-inline-edit-trigger').addClass('hidden');
        }
    }
});