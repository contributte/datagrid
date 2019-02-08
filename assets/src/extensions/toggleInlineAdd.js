import $ from 'jquery';

import registerExtension from '../utils/registerExtension';


// Inline add

registerExtension('datagrid-toggle-inline-add', {
    success: function (payload) {
        if (payload._datagrid_inline_added) {
            $('.datagrid-row-inline-add').find('textarea').html('');
            $('.datagrid-row-inline-add').find('input[type!=submit]').val('');
            return $('.datagrid-row-inline-add').addClass('datagrid-row-inline-add-hidden');
        }
    }
});