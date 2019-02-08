import $ from 'jquery';

import registerExtension from '../utils/registerExtension';
import filterMultiSelect from '../actions/filterMultiSelect';

registerExtension('datagrid.filterMultiSelect', {
    success: function () {
        filterMultiSelect();
        if ($.fn.selectpicker) {
            return $('.selectpicker').selectpicker({
                iconBase: 'fa'
            });
        }
    }
});
