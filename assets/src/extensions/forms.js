import $ from 'jquery';
import nette from 'nette';

import registerExtension from '../utils/registerExtension';

registerExtension('datagrid.forms', {
    success: function() {
        return $('.datagrid').find('form').each(function() {
            return nette.initForm(this);
        });
    }
});
