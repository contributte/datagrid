import $ from 'jquery';

export default function () {
    var select;
    select = $('.selectpicker').first();
    if ($.fn.selectpicker) {
        return $.fn.selectpicker.defaults = {
            countSelectedText: select.data('i18n-selected'),
            iconBase: '',
            tickIcon: select.data('selected-icon-check')
        };
    }
}