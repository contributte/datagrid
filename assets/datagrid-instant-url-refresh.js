var dataGridInstantUrlRefresh = function() {
	var element = document.querySelector('.datagrid');

	if (element !== null) {
		return naja.makeRequest("GET", element.getAttribute('data-refresh-state'), null, {
			history: 'replace'
		});
	}
};

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', dataGridInstantUrlRefresh);
} else {
	dataGridInstantUrlRefresh();
}
