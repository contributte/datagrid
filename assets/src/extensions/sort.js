import $ from 'jquery';

import registerExtension from '../utils/registerExtension';

registerExtension('datagrid.sort', {
    success: function (payload) {
        if (payload._datagrid_sort) {
            const ref = payload._datagrid_sort;
            const results = [];
            for (let key in ref) {
                let href = ref[key];
                results.push($('#datagrid-sort-' + key).attr('href', href));
            }
            return results;
        }
    }
});