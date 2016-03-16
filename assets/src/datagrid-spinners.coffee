$.nette.ext('ublaboo-spinners', {
	before: (xhr, settings) ->
		if settings.nette
			el = settings.nette.el
			spinner_template = $('<div class="ublaboo-spinner ublaboo-spinner-small"><i></i><i></i><i></i><i></i></div>')

			if el.is('.datagrid #group_action_submit')
				el.after(spinner_template)

			else if el.is('.datagrid a') and el.data('toggle-detail')
				id = settings.nette.el.attr('data-toggle-detail')
				row_detail = $('.item-detail-' + id)

				if not row_detail.hasClass('loaded')
					el.addClass('ublaboo-spinner-icon')

			else if el.is('.datagrid .pagination a')
				el.closest('.row-grid-bottom').find('select[name=per_page]').before(spinner_template)

			else if el.is('.datagrid form')
				select = el.find('select[name=per_page]')

				if select.length
					el.closest('.row-grid-bottom').find('select[name=per_page]').before(spinner_template)

	complete: ->
		$('.ublaboo-spinner').remove()
		$('.ublaboo-spinner-icon').removeClass('ublaboo-spinner-icon')
})
