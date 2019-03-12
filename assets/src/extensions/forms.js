import $ from 'jquery';
import nette from 'nette';

import registerExtension from '../utils/registerExtension';

registerExtension('datagrid.forms', {
<<<<<<< HEAD
    success() {
=======
    success: function() {
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
        return $('.datagrid').find('form').each(function() {
            return nette.initForm(this);
        });
    }
});
