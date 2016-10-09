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

# Datagrid auto submit
#
$(document).on('change', 'select[data-autosubmit-per-page]', ->
	$(this).parent().find('input[type=submit]').click()
).on('change', 'select[data-autosubmit]', ->
	$(this).closest('form').submit()
).on('change', 'input[data-autosubmit][data-autosubmit-change]', (e) ->
	code = e.which || e.keyCode || 0

	clearTimeout(window.datagrid_autosubmit_timer)
	$this = $(this)
	window.datagrid_autosubmit_timer = setTimeout =>
		$this.closest('form').submit()
	, 200
).on('keyup', 'input[data-autosubmit]', (e) ->
	code = e.which || e.keyCode || 0

	if (code != 13) and ((code >= 9 and code <= 40) or (code >= 112 and code <= 123))
		return

	clearTimeout(window.datagrid_autosubmit_timer)
	$this = $(this)
	window.datagrid_autosubmit_timer = setTimeout =>
		$this.closest('form').submit()
	, 200
).on('keydown', '.datagrid-inline-edit input', (e) ->
	code = e.which || e.keyCode || 0

	if (code == 13)
		e.stopPropagation()
		e.preventDefault()

		$(this).closest('tr').find('.col-action-inline-edit [name="inline_edit[submit]"]').click()
)

# Datagrid manual submit
#
$(document).on('keydown', 'input[data-datagrid-manualsubmit]', (e) ->
	code = e.which || e.keyCode || 0

	if (code == 13)
		e.stopPropagation()
		e.preventDefault()

		
		$(this).closest('form').submit()
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
			if (v !== null && v !== "") {
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
	if typeof $.fn.sortable == 'undefined'
		return

	$('.datagrid [data-sortable]').sortable({
		handle: '.handle-sort',
		items: 'tr',
		axis: 'y'
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

			data = {};
			component_prefix = row.closest('.datagrid').find('tbody').attr('data-sortable-parent-path')

			data[(component_prefix + '-item_id').replace(/^-/, '')] = item_id
			data[(component_prefix + '-prev_id').replace(/^-/, '')] = prev_id
			data[(component_prefix + '-next_id').replace(/^-/, '')] = next_id

			console.log(data)

			$.nette.ajax({
				type: 'GET',
				url: url,
				data: data,
				error: (jqXHR, textStatus, errorThrown) ->
					alert(jqXHR.statusText)
			})
		,
		helper: (e, ui) ->
			ui.children().each ->
				$(this).width($(this).width())

			return ui;
	})

$ ->
	datagridSortable()

if typeof datagridSortableTree == 'undefined'
	datagridSortableTree = ->
		if typeof $('.datagrid-tree-item-children').sortable == 'undefined'
			return

		$('.datagrid-tree-item-children').sortable({
			handle: '.handle-sort',
			items: '.datagrid-tree-item:not(.datagrid-tree-item:first-child)',
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

				component_prefix = row.closest('.datagrid-tree').attr('data-sortable-parent-path')
				data = {}

				data[(component_prefix + '-item_id').replace(/^-/, '')] = item_id
				data[(component_prefix + '-prev_id').replace(/^-/, '')] = prev_id
				data[(component_prefix + '-next_id').replace(/^-/, '')] = next_id
				data[(component_prefix + '-parent_id').replace(/^-/, '')] = parent_id

				$.nette.ajax({
					type: 'GET',
					url: url,
					data: data,
					error: (jqXHR, textStatus, errorThrown) ->
						if errorThrown != 'abort'
							alert(jqXHR.statusText)
				})
		, stop: (event, ui) ->
			$('.toggle-tree-to-delete').removeClass('toggle-tree-to-delete')
		, start: (event, ui) ->
			parent = ui.item.parent().closest('.datagrid-tree-item')

			if parent.length
				if parent.find('.datagrid-tree-item').length == 2
					parent.find('[data-toggle-tree]').addClass('toggle-tree-to-delete')
		})

$ ->
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

				if window.location.href != url
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
			#row = row_detail.closest('.datagrid').find('tr[data-id=' + id + ']')

			#if row_detail.hasClass('loaded') and row.hasClass('detail-loaded')
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
				#row.addClass('detail-loaded')
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

	if cell.hasClass('datagrid-inline-edit')
		return

	if !cell.hasClass('editing')
		cell.addClass('editing')
		value = cell.html().trim().replace('<br>', '\n')
		cell.data('value', value)

		if cell.data('datagrid-editable-type') == 'textarea'
			input = $('<textarea>' + value + '</textarea>')

			cell_padding = parseInt(cell.css('padding').replace(/[^-\d\.]/g, ''), 10)
			cell_height = cell.outerHeight()
			line_height = Math.round(parseFloat(cell.css('line-height')))

			cell_lines = (cell_height - (2 * cell_padding)) / line_height

			input.attr('rows', Math.round((cell_lines)))

		else if cell.data('datagrid-editable-type') == 'select'
			input = $(cell.data('datagrid-editable-element'));

			input.find('option').each ->
				if $(this).text() == value
					input.find('option[value=' + $(this).val() + ']').prop('selected', true)
		else
			input = $('<input type="' + cell.data('datagrid-editable-type') + '">')
			input.val(value)

		attrs = cell.data('datagrid-editable-attrs')

		for attr_name, attr_value of attrs
			input.attr(attr_name, attr_value)

		cell.removeClass('edited')
		cell.html(input)

		submit = (cell, el) ->
			value = el.val()

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

		cell.find('input,textarea,select').focus().on('blur', ->
			submit(cell, $(this))
		).on('keydown', (e) ->
			if cell.data('datagrid-editable-type') != 'textarea'
				if e.which == 13
					e.stopPropagation()
					e.preventDefault()

					return submit(cell, $(this))

			if e.which == 27
				e.stopPropagation()
				e.preventDefault()

				cell.removeClass('editing');
				cell.html(cell.data('value'));
		)
		cell.find('select').on('change', ->
			submit(cell, $(this))
		)
)

# Datagrid after big inline edit notification
#
$.nette.ext('datagrid.after_inline_edit', {
	success: (payload) ->
		grid = $('.datagrid-' + payload._datagrid_name)

		if payload._datagrid_inline_edited
			grid.find('tr[data-id=' + payload._datagrid_inline_edited + '] > td').addClass('edited')
			grid.find('.datagrid-inline-edit-trigger').removeClass('hidden')
		else if payload._datagrid_inline_edit_cancel
			grid.find('.datagrid-inline-edit-trigger').removeClass('hidden')
})


# Inline add
#
$(document).on('click', '[data-datagrid-toggle-inline-add]', (e) ->
	e.stopPropagation()
	e.preventDefault()

	row = $(this).closest('.datagrid').find('.datagrid-row-inline-add')

	if row.hasClass('datagrid-row-inline-add-hidden')
		row.removeClass('datagrid-row-inline-add-hidden')

	row.find('input:not([readonly]),textarea:not([readonly])').first().focus()
)

$(document).on('mousedown', '[data-datagrid-cancel-inline-add]', (e) ->
	e.stopPropagation()
	e.preventDefault()

	$('.datagrid-row-inline-add').addClass('datagrid-row-inline-add-hidden')
)

$.nette.ext('datagrid-toggle-inline-add', {
	success: (payload) ->
		if payload._datagrid_inline_added
			$('.datagrid-row-inline-add').find('textarea').html('')
			$('.datagrid-row-inline-add').find('input[type!=submit]').val('')

			$('.datagrid-row-inline-add').addClass('datagrid-row-inline-add-hidden')
})


# FilterMultiSelect
#

datagridFitlerMultiSelect = ->
	select = $('.selectpicker').first()

	if $.fn.selectpicker
		$.fn.selectpicker.defaults = {
			countSelectedText: select.data('i18n-selected'),
			iconBase: '',
			tickIcon: select.data('selected-icon-check')
		}

$ ->
	datagridFitlerMultiSelect()


$.nette.ext('datagrid.fitlerMultiSelect', {
	success: ->
		datagridFitlerMultiSelect()

		if $.fn.selectpicker
			$('.selectpicker').selectpicker()
})


$.nette.ext('datagrid.inline-editing', {
	success: (payload) ->
		if payload._datagrid_inline_editing
			$('.datagrid-inline-edit-trigger').addClass('hidden')
})


$.nette.ext('datagrid.redraw-item', {
	success: (payload) ->
		if payload._datagrid_redraw_item_class
			row = $('tr[data-id=' + payload._datagrid_redraw_item_id + ']')
			row.attr('class', payload._datagrid_redraw_item_class)
})
