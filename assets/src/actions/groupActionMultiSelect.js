import $ from 'jquery';


export default function() {
    if (!$.fn.selectpicker) {
        return;
    }

    const selects = $('[data-datagrid-multiselect-id]');

    selects.each(function() {
        if ($(this).hasClass('selectpicker')) {
            $(this).removeAttr('id');
            const id = $(this).data('datagrid-multiselect-id');

            $(this).on('loaded.bs.select', function() {
                $(this).parent().attr('style', 'display:none;');
                $(this).parent().find('.hidden').removeClass('hidden').addClass('btn-default btn-secondary');
            });

            $(this).on('rendered.bs.select', function() {
                $(this).parent().attr('id', id);
            });
        }
    });
}
