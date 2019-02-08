import registerExtension from '../utils/registerExtension';

// Ajax confirmation

registerExtension('datagrid.confirm', {
    before: function(xhr, settings) {
        let confirm_message;
        if (settings.nette) {
            confirm_message = settings.nette.el.data('datagrid-confirm');
            if (confirm_message) {
                return confirm(confirm_message);
            }
        }
    }
});