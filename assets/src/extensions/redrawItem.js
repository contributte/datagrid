import $ from 'jquery';

import registerExtension from '../utils/registerExtension';

registerExtension('datagrid.redraw-item', {
<<<<<<< HEAD
    success(payload) {
        if (payload._datagrid_redraw_item_class) {
            const row = $(`tr[data-id=${payload._datagrid_redraw_item_id}]`);
            row.attr('class', payload._datagrid_redraw_item_class);
        }
    }
});
=======
    success: function(payload) {
        if (payload._datagrid_redraw_item_class) {
            const row = $('tr[data-id=' + payload._datagrid_redraw_item_id + ']');
            return row.attr('class', payload._datagrid_redraw_item_class);
        }
    }
});
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
