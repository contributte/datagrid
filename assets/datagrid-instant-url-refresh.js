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

document.addEventListener('DOMContentLoaded', function () {
	var element = document.querySelector('.datagrid');

	if (element !== null) {
		return dataGridRegisterAjaxCall({
			type: 'GET',
			url: element.getAttribute('data-refresh-state')
		});
	}
});
