# Non-ajax confirmation
#
$(document).on('click', '[data-datagrid-confirm]', (e) ->
	if not confirm($(e.target).closest('a').attr('data-datagrid-confirm'))
		e.stopPropagation()
		e.preventDefault()
)

# Ajax confirmation
#
$.nette.ext('datagrid.confirm', {
	before: (xhr, settings) ->
		if settings.nette
			confirm_message = settings.nette.el.data('datagrid-confirm')
			if confirm_message
				return confirm(confirm_message)
})

# Datagrid autosubmit
#
$(document).on('change', 'select[data-autosubmit]', ->
	$(this).closest('form').submit();
).on('change', 'input[data-autosubmit][data-autosubmit-change]', (e) ->
	code = e.which || e.keyCode || 0

	clearTimeout(window.datagrid_autosubmit_timer)
	$this = $(this)
	window.datagrid_autosubmit_timer = setTimeout =>
		$this.closest('form').submit()
	, 200
).on('keyup', 'input[data-autosubmit]', (e) ->
	code = e.which || e.keyCode || 0

	if (code !=13) and ((code >= 9 and code <= 40) or (code >= 112 and code <= 123))
		return

	clearTimeout(window.datagrid_autosubmit_timer)
	$this = $(this)
	window.datagrid_autosubmit_timer = setTimeout =>
		$this.closest('form').submit()
	, 200
)

document.addEventListener 'change', (e) ->
	grid = e.target.getAttribute('data-check')

	if grid
		at_least_one = document.querySelector('.datagrid-' + grid + ' input[data-check]:checked')

		select = document.querySelector('.datagrid-' + grid + ' select[name="group_action[group_action]"]')

		if select
			if at_least_one
				select.disabled = false
			else
				select.disabled = true
				select.value = ""

		ie = window.navigator.userAgent.indexOf("MSIE ")

		if ie
			event = document.createEvent('Event')
			event.initEvent('change', true, true);
		else
			event = new Event('change', {'bubbles': true})

		if select
			select.dispatchEvent(event)


	grid = e.target.getAttribute('data-check-all')

	if grid
		inputs = document.querySelectorAll('input[type=checkbox][data-check-all-' + grid + ']')

		for input in inputs
			input.checked = e.target.checked

			ie = window.navigator.userAgent.indexOf("MSIE ")

			if ie
				event = document.createEvent('Event')
				event.initEvent('change', true, true);
			else
				event = new Event('change', {'bubbles': true})

			input.dispatchEvent(event)

`
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
`

datagridSortable = ->
	if typeof $('.datagrid [data-sortable]').sortable == 'undefined'
		return

	$('.datagrid [data-sortable]').sortable({
		handle: '.handle-sort',
		items: 'tr',
		update: (event, ui) ->
			row = ui.item.closest('tr[data-id]')

			item_id = row.data('id')
			prev_id = null
			next_id = null

			if row.prev().length
				prev_id = row.prev().data('id')

			if row.next().length
				next_id = row.next().data('id')

			url = $(this).data('sortable-url')

			$.nette.ajax({
				type: 'GET',
				url: url,
				data: {item_id: item_id, prev_id: prev_id, next_id: next_id},
				error: (jqXHR, textStatus, errorThrown) ->
					alert(jqXHR.statusText)
			})
	})

datagridSortable()

datagridSortableTree = ->
	if typeof $('.datagrid-tree-item-children').sortable == 'undefined'
		return

	$('.datagrid-tree-item-children').sortable({
		handle: '.handle-sort',
		items: '.datagrid-tree-item',
		toleranceElement: '> .datagrid-tree-item-content',
		connectWith: '.datagrid-tree-item-children',
		update: (event, ui) ->
			$('.toggle-tree-to-delete').remove()

			row = ui.item.closest('.datagrid-tree-item[data-id]')

			item_id = row.data('id')
			prev_id = null
			next_id = null
			parent_id = null

			if row.prev().length
				prev_id = row.prev().data('id')

			if row.next().length
				next_id = row.next().data('id')

			parent = row.parent().closest('.datagrid-tree-item')

			if parent.length
				parent.find('.datagrid-tree-item-children').first().css({display: 'block'})
				parent.addClass('has-children')

				parent_id = parent.data('id')

			url = $(this).data('sortable-url')

			if !url
				return

			parent.find('[data-toggle-tree]').first().removeClass('hidden')

			$.nette.ajax({
				type: 'GET',
				url: url,
				data: {item_id: item_id, prev_id: prev_id, next_id: next_id, parent_id: parent_id},
				error: (jqXHR, textStatus, errorThrown) ->
					if errorThrown != 'abort'
						alert(jqXHR.statusText)
			})
	, stop: ->
		$('.toggle-tree-to-delete').removeClass('toggle-tree-to-delete')
	, start: (event, ui) ->
		parent = ui.item.parent().closest('.datagrid-tree-item')
		if parent.length
			if parent.find('.datagrid-tree-item').length == 2
				parent.find('[data-toggle-tree]').addClass('toggle-tree-to-delete')

	})

datagridSortableTree();

$.nette.ext('datagrid.happy', {
	success: ->
		if window.happy
			window.happy.reset()

		grids = $('.datagrid')

		for grid in grids
			classes = grid.classList
			class_selector = '';

			for c in classes
				class_selector = class_selector + '.' + c


			checked_rows = document.querySelectorAll(class_selector + ' ' + 'input[data-check]:checked')

			if checked_rows.length == 1 and checked_rows[0].getAttribute('name') == 'toggle-all'
				input = document.querySelector(class_selector + ' input[name=toggle-all]')

				if input
					input.checked = false
					ie = window.navigator.userAgent.indexOf("MSIE ")

					if ie
						event = document.createEvent('Event')
						event.initEvent('change', true, true);
					else
						event = new Event('change', {'bubbles': true})

					input.dispatchEvent(event)
})

$.nette.ext('datagrid.sortable', {
	success: ->
		datagridSortable()
})

$.nette.ext('datagrid.forms', {
	success: ->
		$('.datagrid').find('form').each ->
			window.Nette.initForm(this)
})

$.nette.ext('datagrid.url', {
	success: (payload) ->
		if payload._datagrid_url
			if window.history.pushState
				host = window.location.protocol + "//" + window.location.host
				path = window.location.pathname
				query = window.datagridSerializeUrl(payload.state).replace(/&+$/gm,'')

				if query
					url = host + path + "?"+ query.replace(/\&*$/, '')
				else
					url = host + path

				url += window.location.hash

				window.history.pushState({path: url}, '', url)
})

$.nette.ext('datagrid.sort', {
	success: (payload) ->
		if payload._datagrid_sort
			for key, href of payload._datagrid_sort
				$('#datagrid-sort-' + key).attr('href', href)
})

$.nette.ext('datargid.item_detail', {
	before: (xhr, settings) ->
		if settings.nette and settings.nette.el.attr('data-toggle-detail')
			id = settings.nette.el.attr('data-toggle-detail')
			row_detail = $('.item-detail-' + id)

			if row_detail.hasClass('loaded')
				if not row_detail.find('.item-detail-content').size()
					row_detail.removeClass('toggled')

					return true

				if row_detail.hasClass('toggled')
					row_detail.find('.item-detail-content').slideToggle('fast', =>
						row_detail.toggleClass('toggled')
					)
				else
					row_detail.toggleClass('toggled')
					row_detail.find('.item-detail-content').slideToggle('fast')

				return false
			else
				row_detail.addClass('loaded')

	success: (payload) ->
		if payload._datagrid_toggle_detail
			id = payload._datagrid_toggle_detail
			row_detail = $('.item-detail-' + id)

			row_detail.toggleClass('toggled')
			row_detail.find('.item-detail-content').slideToggle('fast')
})

$.nette.ext('datagrid.tree', {
	before: (xhr, settings) ->
		if settings.nette and settings.nette.el.attr('data-toggle-tree')
			settings.nette.el.toggleClass('toggle-rotate')
			children_block = settings.nette.el.closest('.datagrid-tree-item').find('.datagrid-tree-item-children').first()

			if children_block.hasClass('loaded')
				children_block.slideToggle('fast')

				return false

		return true

	success: (payload) ->
		if payload._datagrid_tree
			id = payload._datagrid_tree
			children_block = $('.datagrid-tree-item[data-id=' + id + ']').find('.datagrid-tree-item-children').first()
			children_block.addClass('loaded')

			for name, snippet of payload.snippets
				content = $(snippet)
				template = $('<div class="datagrid-tree-item" id="' + name + '">')
				template.attr('data-id', content.attr('data-id'))
				template.append(content)

				if content.data('has-children')
					template.addClass('has-children')
				

				children_block.append(template)

			children_block.addClass('loaded')
			children_block.slideToggle('fast')

			$.nette.load();

		datagridSortableTree()
})

$(document).on('click', '[data-datagrid-editable-url]', (event) ->
	cell = $(this)

	if !cell.hasClass('editing')
		cell.addClass('editing')
		value = cell.html().trim().replace('<br>', '\n')
		cell.data('value', value)
		textarea = $('<textarea class="form-control">' + value + '</textarea>');

		cell_padding = parseInt(cell.css('padding').replace(/[^-\d\.]/g, ''), 10)
		cell_height = cell.outerHeight()
		text_height = cell_height - cell_padding
		line_height = Math.round(parseFloat(cell.css('line-height')))

		cell_lines = (cell_height - (2 * cell_padding)) / line_height

		textarea.attr('rows', Math.round((cell_lines)))

		cell.removeClass('edited')
		cell.html(textarea)

		cell.find('textarea').focus().on('blur', ->
			value = $(this).val()

			if value != cell.data('value')
				$.nette.ajax({
					url: cell.data('datagrid-editable-url'),
					data: {
						value: value
					},
					method: 'POST'
				}).success ->
					cell.addClass('edited')

			cell.removeClass('editing')
			cell.html(value)
		)
)

# Datagrid after big inline edit notification
#
$.nette.ext('datagrid.after_inline_edit', {
	success: (payload) ->
		if payload._datagrid_inline_edited
			$('tr[data-id=' + payload._datagrid_inline_edited + '] > td').addClass('edited')
})
