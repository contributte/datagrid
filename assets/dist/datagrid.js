var datagridSortable, datagridSortableTree;

$(document).on('change', 'select[data-autosubmit]', function() {
  return $(this).closest('form').submit();
}).on('change', 'input[data-autosubmit][data-autosubmit-change]', function(e) {
  var $this, code;
  code = e.which || e.keyCode || 0;
  clearTimeout(window.datagrid_autosubmit_timer);
  $this = $(this);
  return window.datagrid_autosubmit_timer = setTimeout((function(_this) {
    return function() {
      return $this.closest('form').submit();
    };
  })(this), 200);
}).on('keyup', 'input[data-autosubmit]', function(e) {
  var $this, code;
  code = e.which || e.keyCode || 0;
  if ((code !== 13) && ((code >= 9 && code <= 40) || (code >= 112 && code <= 123))) {
    return;
  }
  clearTimeout(window.datagrid_autosubmit_timer);
  $this = $(this);
  return window.datagrid_autosubmit_timer = setTimeout((function(_this) {
    return function() {
      return $this.closest('form').submit();
    };
  })(this), 200);
});

document.addEventListener('change', function(e) {
  var at_least_one, event, grid, i, input, inputs, len, results, select;
  grid = e.target.getAttribute('data-check');
  if (grid) {
    at_least_one = document.querySelector('.datagrid-' + grid + ' input[data-check]:checked');
    select = document.querySelector('.datagrid-' + grid + ' select[name="group_action[group_action]"]');
    if (select) {
      if (at_least_one) {
        select.disabled = false;
      } else {
        select.disabled = true;
        select.value = "";
      }
    }
    event = new Event('change', {
      'bubbles': true
    });
    if (select) {
      select.dispatchEvent(event);
    }
  }
  grid = e.target.getAttribute('data-check-all');
  if (grid) {
    inputs = document.querySelectorAll('input[type=checkbox][data-check-all-' + grid + ']');
    results = [];
    for (i = 0, len = inputs.length; i < len; i++) {
      input = inputs[i];
      input.checked = e.target.checked;
      event = new Event('change', {
        'bubbles': true
      });
      results.push(input.dispatchEvent(event));
    }
    return results;
  }
});


window.datagridSerializeUrl = function(obj, prefix) {
	var str = [];
	for(var p in obj) {
		if (obj.hasOwnProperty(p)) {
			var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
			if(v) {
				str.push(typeof v == "object" ?
					window.datagridSerializeUrl(v, k) :
					encodeURIComponent(k) + "=" + encodeURIComponent(v));
			}
		}
	}
	return str.join("&");
}
;

datagridSortable = function() {
  if (typeof $('.datagrid [data-sortable]').sortable === 'undefined') {
    return;
  }
  return $('.datagrid [data-sortable]').sortable({
    handle: '.handle-sort',
    items: 'tr',
    update: function(event, ui) {
      var item_id, next_id, prev_id, row, url;
      row = ui.item.closest('tr[data-id]');
      item_id = row.data('id');
      prev_id = null;
      next_id = null;
      if (row.prev().length) {
        prev_id = row.prev().data('id');
      }
      if (row.next().length) {
        next_id = row.next().data('id');
      }
      url = $(this).data('sortable-url');
      return $.nette.ajax({
        type: 'GET',
        url: url,
        data: {
          item_id: item_id,
          prev_id: prev_id,
          next_id: next_id
        },
        error: function(jqXHR, textStatus, errorThrown) {
          return alert(jqXHR.statusText);
        }
      });
    }
  });
};

datagridSortable();

datagridSortableTree = function() {
  if (typeof $('.datagrid-tree-item-children').sortable === 'undefined') {
    return;
  }
  return $('.datagrid-tree-item-children').sortable({
    handle: '.handle-sort',
    items: '.datagrid-tree-item',
    toleranceElement: '> .datagrid-tree-item-content',
    connectWith: '.datagrid-tree-item-children',
    update: function(event, ui) {
      var item_id, next_id, parent, parent_id, prev_id, row, url;
      $('.toggle-tree-to-delete').remove();
      row = ui.item.closest('.datagrid-tree-item[data-id]');
      item_id = row.data('id');
      prev_id = null;
      next_id = null;
      parent_id = null;
      if (row.prev().length) {
        prev_id = row.prev().data('id');
      }
      if (row.next().length) {
        next_id = row.next().data('id');
      }
      parent = row.parent().closest('.datagrid-tree-item');
      if (parent.length) {
        parent.find('.datagrid-tree-item-children').first().css({
          display: 'block'
        });
        parent.addClass('has-children');
        parent_id = parent.data('id');
      }
      url = $(this).data('sortable-url');
      if (!url) {
        return;
      }
      parent.find('[data-toggle-tree]').first().removeClass('hidden');
      return $.nette.ajax({
        type: 'GET',
        url: url,
        data: {
          item_id: item_id,
          prev_id: prev_id,
          next_id: next_id,
          parent_id: parent_id
        },
        error: function(jqXHR, textStatus, errorThrown) {
          if (errorThrown !== 'abort') {
            return alert(jqXHR.statusText);
          }
        }
      });
    },
    stop: function() {
      return $('.toggle-tree-to-delete').removeClass('toggle-tree-to-delete');
    },
    start: function(event, ui) {
      var parent;
      parent = ui.item.parent().closest('.datagrid-tree-item');
      if (parent.length) {
        if (parent.find('.datagrid-tree-item').length === 2) {
          return parent.find('[data-toggle-tree]').addClass('toggle-tree-to-delete');
        }
      }
    }
  });
};

datagridSortableTree();

$.nette.ext('datagrid.happy', {
  success: function() {
    var c, checked_rows, class_selector, classes, event, grid, grids, i, input, j, len, len1, results;
    window.happy.reset();
    grids = $('.datagrid');
    results = [];
    for (i = 0, len = grids.length; i < len; i++) {
      grid = grids[i];
      classes = grid.classList;
      class_selector = '';
      for (j = 0, len1 = classes.length; j < len1; j++) {
        c = classes[j];
        class_selector = class_selector + '.' + c;
      }
      checked_rows = document.querySelectorAll(class_selector + ' ' + 'input[data-check]:checked');
      if (checked_rows.length === 1 && checked_rows[0].getAttribute('name') === 'toggle-all') {
        input = document.querySelector(class_selector + ' input[name=toggle-all]');
        if (input) {
          input.checked = false;
          event = new Event('change', {
            'bubbles': true
          });
          results.push(input.dispatchEvent(event));
        } else {
          results.push(void 0);
        }
      } else {
        results.push(void 0);
      }
    }
    return results;
  }
});

$.nette.ext('datagrid.sortable', {
  success: function() {
    return datagridSortable();
  }
});

$.nette.ext('datagrid.forms', {
  success: function() {
    return $('.datagrid').find('form').each(function() {
      return window.Nette.initForm(this);
    });
  }
});

$.nette.ext('datagrid.url', {
  success: function(payload) {
    var host, path, query, url;
    if (payload._datagrid_url) {
      if (window.history.pushState) {
        host = window.location.protocol + "//" + window.location.host;
        path = window.location.pathname;
        query = window.datagridSerializeUrl(payload.state).replace(/&+$/gm, '');
        if (query) {
          url = host + path + "?" + query.replace(/\&*$/, '');
        } else {
          url = host + path;
        }
        url += window.location.hash;
        return window.history.pushState({
          path: url
        }, '', url);
      }
    }
  }
});

$.nette.ext('datagrid.confirm', {
  before: function(xhr, settings) {
    var confirm_message;
    if (settings.nette) {
      confirm_message = settings.nette.el.data('confirm');
      if (confirm_message) {
        return confirm(confirm_message);
      }
    }
  }
});

$.nette.ext('datargid.item_detail', {
  before: function(xhr, settings) {
    var id, row_detail;
    if (settings.nette && settings.nette.el.attr('data-toggle-detail')) {
      id = settings.nette.el.attr('data-toggle-detail');
      row_detail = $('.item-detail-' + id);
      if (row_detail.hasClass('loaded')) {
        if (!row_detail.find('.item-detail-content').size()) {
          row_detail.removeClass('toggled');
          return true;
        }
        if (row_detail.hasClass('toggled')) {
          row_detail.find('.item-detail-content').slideToggle('fast', (function(_this) {
            return function() {
              return row_detail.toggleClass('toggled');
            };
          })(this));
        } else {
          row_detail.toggleClass('toggled');
          row_detail.find('.item-detail-content').slideToggle('fast');
        }
        return false;
      } else {
        return row_detail.addClass('loaded');
      }
    }
  },
  success: function(payload) {
    var id, row_detail;
    if (payload._datagrid_toggle_detail) {
      id = payload._datagrid_toggle_detail;
      row_detail = $('.item-detail-' + id);
      row_detail.toggleClass('toggled');
      return row_detail.find('.item-detail-content').slideToggle('fast');
    }
  }
});

$.nette.ext('datagrid.tree', {
  before: function(xhr, settings) {
    var children_block;
    if (settings.nette && settings.nette.el.attr('data-toggle-tree')) {
      settings.nette.el.toggleClass('toggle-rotate');
      children_block = settings.nette.el.closest('.datagrid-tree-item').find('.datagrid-tree-item-children').first();
      if (children_block.hasClass('loaded')) {
        children_block.slideToggle('fast');
        return false;
      }
    }
    return true;
  },
  success: function(payload) {
    var children_block, content, id, name, ref, snippet, template;
    if (payload._datagrid_tree) {
      id = payload._datagrid_tree;
      children_block = $('.datagrid-tree-item[data-id=' + id + ']').find('.datagrid-tree-item-children').first();
      children_block.addClass('loaded');
      ref = payload.snippets;
      for (name in ref) {
        snippet = ref[name];
        content = $(snippet);
        template = $('<div class="datagrid-tree-item" id="' + name + '">');
        template.attr('data-id', content.attr('data-id'));
        template.append(content);
        if (content.data('has-children')) {
          template.addClass('has-children');
        }
        children_block.append(template);
      }
      children_block.addClass('loaded');
      children_block.slideToggle('fast');
      $.nette.load();
    }
    return datagridSortableTree();
  }
});

$(document).on('click', '[data-datagrid-editable-url]', function(event) {
  var cell, cell_height, cell_lines, cell_padding, line_height, text_height, textarea, value;
  cell = $(this);
  if (!cell.hasClass('editing')) {
    cell.addClass('editing');
    value = cell.html().trim().replace('<br>', '\n');
    cell.data('value', value);
    textarea = $('<textarea class="form-control">' + value + '</textarea>');
    cell_padding = parseInt(cell.css('padding').replace(/[^-\d\.]/g, ''), 10);
    cell_height = cell.outerHeight();
    text_height = cell_height - cell_padding;
    line_height = Math.round(parseFloat(cell.css('line-height')));
    cell_lines = (cell_height - (2 * cell_padding)) / line_height;
    textarea.attr('rows', Math.round(cell_lines));
    cell.removeClass('edited');
    cell.html(textarea);
    return cell.find('textarea').focus().on('blur', function() {
      value = $(this).val();
      if (value !== cell.data('value')) {
        $.nette.ajax({
          url: cell.data('datagrid-editable-url'),
          data: {
            value: value
          },
          method: 'POST'
        }).success(function() {
          return cell.addClass('edited');
        });
      }
      cell.removeClass('editing');
      return cell.html(value);
    });
  }
});
