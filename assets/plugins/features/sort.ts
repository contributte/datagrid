import { DatagridPlugin } from "../../types";
import { Datagrid } from "../..";

export class SortPlugin implements DatagridPlugin {
	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener("success", ({ detail: { payload } }) => {
			if (!payload._datagrid_sort) return;

			for (const key in payload._datagrid_sort) {
				const href = payload._datagrid_sort[key];
				const element = datagrid.el.querySelector(`#datagrid-sort-${key}`);

				if (element) {
					// TODO: Only for BC support, to be removed
					element.setAttribute("href", href);

					element.setAttribute("data-href", href);
				}
			}
		});

		return true;
	}
}
