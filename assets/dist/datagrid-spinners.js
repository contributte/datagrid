$.nette.ext('ublaboo-spinners', {
  before: function(xhr, settings) {
    var el, spinner_template;
    if (settings.nette) {
      el = settings.nette.el;
      spinner_template = $('<div class="ublaboo-spinner ublaboo-spinner-small"><i></i><i></i><i></i><i></i></div>');
      if (el.is('.datagrid #group_action_submit')) {
        return el.after(spinner_template);
      } else if (el.is('.datagrid a') && el.data('toggle-detail')) {
        return el.addClass('ublaboo-spinner-icon');
      } else if (el.is('.datagrid .pagination a')) {
        return el.closest('.row-grid-bottom').find('select[name=per_page]').before(spinner_template);
      } else if (el.is('.datagrid .dropdown-menu a')) {
        return el.addClass('ublaboo-spinner-icon');
      }
    }
  },
  complete: function() {
    console.log('s');
    $('.ublaboo-spinner').remove();
    return $('.ublaboo-spinner-icon').removeClass('ublaboo-spinner-icon');
  }
});
