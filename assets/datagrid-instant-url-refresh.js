var dataGridRegisterAjaxCall;

if (typeof naja !== "undefined") {
	dataGridRegisterAjaxCall = function (params) {
        var method = params.type || 'GET';
        var data = params.data || null;

		naja.makeRequest(method, params.url, data, {})
			.then(params.success)
			.catch(params.error);
	};

} else {
	dataGridRegisterAjaxCall = $.nette.ajax;
}

$(function() {
	if ($('.datagrid').length) {
		return dataGridRegisterAjaxCall({
			type: 'GET',
			url: $('.datagrid').first().data('refresh-state')
		});
	}
});