import $ from 'jquery';

import registerExtension from '../utils/registerExtension';

registerExtension('datagrid.redraw-item', {
    success: function(payload) {
        if (payload._datagrid_redraw_item_class) {
            const row = $('tr[data-id=' + payload._datagrid_redraw_item_id + ']');
            return row.attr('class', payload._datagrid_redraw_item_class);
        }
    }
});