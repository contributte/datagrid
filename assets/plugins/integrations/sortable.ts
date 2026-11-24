import { Datagrid } from "../../datagrid";
import { DatagridPlugin, Sortable } from "../../types";

export class SortablePlugin implements DatagridPlugin {
	constructor(private sortable: Sortable) {
	}

	onDatagridInit(datagrid: Datagrid): boolean {
		this.sortable.initSortable(datagrid);
		this.sortable.initSortableTree(datagrid);

		// The grid may be redrawn by any request, not only by the filter form submit
		datagrid.ajax.addEventListener("complete", () => {
			this.sortable.initSortable(datagrid);
			this.sortable.initSortableTree(datagrid);
		});

		return true;
	}
}
