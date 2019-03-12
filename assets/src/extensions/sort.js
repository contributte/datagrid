import $ from 'jquery';

import registerExtension from '../utils/registerExtension';

<<<<<<< HEAD

registerExtension('datagrid.sort', {
    success(payload) {
        if (payload._datagrid_sort) {
            return (() => {
                const result = [];
                for (let key in payload._datagrid_sort) {
                    const href = payload._datagrid_sort[key];
                    result.push($(`#datagrid-sort-${key}`).attr('href', href));
                }
                return result;
            })();
=======
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
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
        }
    }
});