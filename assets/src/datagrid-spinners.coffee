$.nette.ext('ublaboo-spinners', {
	before: (xhr, settings) ->
		if settings.nette
			el = settings.nette.el
			spinner_template = $('<div class="ublaboo-spinner ublaboo-spinner-small"><i></i><i></i><i></i><i></i></div>')

			if el.is('.datagrid [name="group_action[submit]"]')
				el.after(spinner_template)

			else if el.is('.datagrid a') and el.data('toggle-detail')
				id = settings.nette.el.attr('data-toggle-detail')
				row_detail = $('.item-detail-' + id)

				if not row_detail.hasClass('loaded')
					el.addClass('ublaboo-spinner-icon')

			else if el.is('.datagrid .col-pagination a')
				el.closest('.row-grid-bottom').find('.col-per-page').prepend(spinner_template)

			else if el.is('.datagrid .datagrid-per-page-submit')
					el.closest('.row-grid-bottom').find('.col-per-page').prepend(spinner_template)

			else if el.is('.datagrid .reset-filter')
					el.closest('.row-grid-bottom').find('.col-per-page').prepend(spinner_template)

	complete: ->
		$('.ublaboo-spinner').remove()
		$('.ublaboo-spinner-icon').removeClass('ublaboo-spinner-icon')
})
