import $ from 'jquery';

import ajaxCall from './utils/ajaxCall';

// On page load - check whether the url shoud be changed using history API

<<<<<<< HEAD
$(function() {
    if ($('.datagrid').length) {
        ajaxCall({
=======
$(function () {
    if ($('.datagrid').length) {
        return ajaxCall({
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
            type: 'GET',
            url: $('.datagrid').first().data('refresh-state')
        });
    }
});
<<<<<<< HEAD
  
=======
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
