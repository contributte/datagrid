import registerExtension from '../utils/registerExtension';
import groupActionMultiSelect from '../actions/groupActionMultiSelect';


registerExtension('datagrid.groupActionMultiSelect', {
    success: groupActionMultiSelect
});