import { Datagrid } from "../../datagrid";
import { DatagridPlugin, Sortable } from "../../types";

export class SortablePlugin implements DatagridPlugin {
	constructor(private sortable: Sortable) {
	}

	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener('before', (event) => {
			// TODO old ln 694... wtf?
		})

		this.sortable.initSortable(datagrid);

		datagrid.ajax.addEventListener('success', ({detail: {payload}}) => {
			if (payload._datagrid_sort) {
				for (const key in payload._datagrid_sort) {
					const href = payload._datagrid_sort[key];
					const element = datagrid.el.querySelector(`#datagrid-sort-${key}`);

					if (element) {
						// TODO: Only for BC support, to be removed
						element.setAttribute("href", href);

						element.setAttribute("data-href", href);
					}
				}
				this.sortable.initSortable(datagrid);
			}

			if (payload._datagrid_tree) {
				const childrenContainer = datagrid.el.querySelector<HTMLElement>(
					`.datagrid-tree-item[data-id='${payload._datagrid_tree}'] .datagrid-tree-item-children`
				);
				if (childrenContainer && payload.snippets) {
					childrenContainer.classList.add("loaded");
					for (const key in payload.snippets) {
						const snippet = payload.snippets[key];

						const doc = new DOMParser().parseFromString(snippet, 'text/html');
						const element = doc.firstElementChild;
						if (element) {
							const treeItem = document.createElement("div");
							treeItem.id = key;
							treeItem.classList.add("datagrid-tree-item")
							treeItem.setAttribute("data-id", key);
							if (element.hasAttribute("has-children")) {
								treeItem.classList.add("has-children");
							}

							childrenContainer.append(treeItem);
							// attachSlideToggle(childrenContainer);
						}
					}
				}
				this.sortable.initSortableTree(datagrid);
			}
		})
		return true;
	}
}
