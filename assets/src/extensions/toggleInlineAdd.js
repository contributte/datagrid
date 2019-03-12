import $ from 'jquery';

import registerExtension from '../utils/registerExtension';


// Inline add

registerExtension('datagrid-toggle-inline-add', {
<<<<<<< HEAD
    success(payload) {
        if (payload._datagrid_inline_added) {
            $('.datagrid-row-inline-add').find('textarea').html('');
            $('.datagrid-row-inline-add').find('input[type!=submit]').val('');

            return $('.datagrid-row-inline-add').addClass('datagrid-row-inline-add-hidden');
        }
    }
});
=======
    success: function (payload) {
        if (payload._datagrid_inline_added) {
            $('.datagrid-row-inline-add').find('textarea').html('');
            $('.datagrid-row-inline-add').find('input[type!=submit]').val('');
            return $('.datagrid-row-inline-add').addClass('datagrid-row-inline-add-hidden');
        }
    }
});
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
