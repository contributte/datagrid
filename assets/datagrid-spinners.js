$.nette.ext('ublaboo-spinners', {
  before: function(xhr, settings) {
    var el, id, row_detail, spinner_template;
    if (settings.nette) {
      el = settings.nette.el;
      spinner_template = $('<div class="ublaboo-spinner ublaboo-spinner-small"><i></i><i></i><i></i><i></i></div>');
      if (el.is('.datagrid [name="group_action[submit]"]')) {
        return el.after(spinner_template);
      } else if (el.is('.datagrid a') && el.data('toggle-detail')) {
        id = settings.nette.el.attr('data-toggle-detail');
        row_detail = $('.item-detail-' + id);
        if (!row_detail.hasClass('loaded')) {
          return el.addClass('ublaboo-spinner-icon');
        }
      } else if (el.is('.datagrid .col-pagination a')) {
        return el.closest('.row-grid-bottom').find('.col-per-page').prepend(spinner_template);
      } else if (el.is('.datagrid .datagrid-per-page-submit')) {
        return el.closest('.row-grid-bottom').find('.col-per-page').prepend(spinner_template);
      } else if (el.is('.datagrid .reset-filter')) {
        return el.closest('.row-grid-bottom').find('.col-per-page').prepend(spinner_template);
      }
    }
  },
  complete: function() {
    $('.ublaboo-spinner').remove();
    return $('.ublaboo-spinner-icon').removeClass('ublaboo-spinner-icon');
  }
});
