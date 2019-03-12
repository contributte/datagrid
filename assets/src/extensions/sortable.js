import registerExtension from '../utils/registerExtension';
import sortable from '../actions/sortable';


registerExtension('datagrid.sortable', {
    success: sortable
});
