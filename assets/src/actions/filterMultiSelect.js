import $ from 'jquery';


// FilterMultiSelect
//
export default function() {
    const select = $('.selectpicker').first();

    if ($.fn.selectpicker) {
        $.fn.selectpicker.defaults = {
            countSelectedText: select.data('i18n-selected'),
            iconBase: '',
            tickIcon: select.data('selected-icon-check')
        };
    }
}