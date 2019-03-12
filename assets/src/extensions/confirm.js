import registerExtension from '../utils/registerExtension';

// Ajax confirmation
<<<<<<< HEAD
//
registerExtension('datagrid.confirm', {
    before(xhr, settings) {
        if (settings.nette) {
            const confirm_message = settings.nette.el.data('datagrid-confirm');
=======

registerExtension('datagrid.confirm', {
    before: function(xhr, settings) {
        let confirm_message;
        if (settings.nette) {
            confirm_message = settings.nette.el.data('datagrid-confirm');
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
            if (confirm_message) {
                return confirm(confirm_message);
            }
        }
    }
});