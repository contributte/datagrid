$.nette.ext('ublaboo-spinners', {
  before: function(xhr, settings) {
    var el, id, row_detail, select, spinner_template;
    if (settings.nette) {
      el = settings.nette.el;
      spinner_template = $('<div class="ublaboo-spinner ublaboo-spinner-small"><i></i><i></i><i></i><i></i></div>');
      if (el.is('.datagrid #group_action_submit')) {
        return el.after(spinner_template);
      } else if (el.is('.datagrid a') && el.data('toggle-detail')) {
        id = settings.nette.el.attr('data-toggle-detail');
        row_detail = $('.item-detail-' + id);
        if (!row_detail.hasClass('loaded')) {
          return el.addClass('ublaboo-spinner-icon');
        }
      } else if (el.is('.datagrid .pagination a')) {
        return el.closest('.row-grid-bottom').find('select[name=per_page]').before(spinner_template);
      } else if (el.is('.datagrid form')) {
        select = el.find('select[name=per_page]');
        if (select.length) {
          return el.closest('.row-grid-bottom').find('select[name=per_page]').before(spinner_template);
        }
      }
    }
  },
  complete: function() {
    $('.ublaboo-spinner').remove();
    return $('.ublaboo-spinner-icon').removeClass('ublaboo-spinner-icon');
  }
});
