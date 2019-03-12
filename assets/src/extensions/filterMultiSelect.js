import $ from 'jquery';

import registerExtension from '../utils/registerExtension';
import filterMultiSelect from '../actions/filterMultiSelect';


registerExtension('datagrid.fitlerMultiSelect', {
    success() {
        filterMultiSelect();

        if ($.fn.selectpicker) {
            return $('.selectpicker').selectpicker({iconBase: 'fa'});
        }
    }
});
