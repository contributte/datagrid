import $ from 'jquery';

import registerExtension from '../utils/registerExtension';
import filterMultiSelect from '../actions/filterMultiSelect';

<<<<<<< HEAD

registerExtension('datagrid.fitlerMultiSelect', {
    success() {
        filterMultiSelect();

        if ($.fn.selectpicker) {
            return $('.selectpicker').selectpicker({iconBase: 'fa'});
=======
registerExtension('datagrid.filterMultiSelect', {
    success: function () {
        filterMultiSelect();
        if ($.fn.selectpicker) {
            return $('.selectpicker').selectpicker({
                iconBase: 'fa'
            });
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
        }
    }
});
