import $ from 'jquery';

import registerExtension from '../utils/registerExtension';


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
        }
    }
});