import registerExtension from '../utils/registerExtension';

// Ajax confirmation
//
registerExtension('datagrid.confirm', {
    before(xhr, settings) {
        if (settings.nette) {
            const confirm_message = settings.nette.el.data('datagrid-confirm');
            if (confirm_message) {
                return confirm(confirm_message);
            }
        }
    }
});