# On page load - check whether the url shoud be changed using history API
#
$ ->
	if $('.datagrid').length
		$.nette.ajax({
			type: 'GET',
			url: $('.datagrid').first().data('refresh-state')
		})
