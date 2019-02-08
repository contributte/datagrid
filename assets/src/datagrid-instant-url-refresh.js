import $ from 'jquery';

import ajaxCall from './utils/ajaxCall';

// On page load - check whether the url shoud be changed using history API

$(function () {
    if ($('.datagrid').length) {
        return ajaxCall({
            type: 'GET',
            url: $('.datagrid').first().data('refresh-state')
        });
    }
});
