import $ from 'jquery';

<<<<<<< HEAD

// FilterMultiSelect
//
export default function() {
    const select = $('.selectpicker').first();

    if ($.fn.selectpicker) {
        $.fn.selectpicker.defaults = {
=======
export default function () {
    var select;
    select = $('.selectpicker').first();
    if ($.fn.selectpicker) {
        return $.fn.selectpicker.defaults = {
>>>>>>> 6737a30dff783a4a24094c93b30bb51e7177d568
            countSelectedText: select.data('i18n-selected'),
            iconBase: '',
            tickIcon: select.data('selected-icon-check')
        };
    }
}