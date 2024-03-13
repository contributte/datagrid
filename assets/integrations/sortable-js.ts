import { Datagrid } from "..";
import { Sortable as SortableInterface } from "../types";
import Sortable from "sortablejs"

export class SortableJS implements SortableInterface {
	initSortable(datagrid: Datagrid): void {
		const sortable = datagrid.el.querySelector<HTMLElement>("[data-sortable]");
		if (sortable) {
			new Sortable(sortable, {
				handle: '.handle-sort',
				draggable: 'tr',
				sort: true,
				direction: 'vertical',
				async onEnd({item}) {
					const itemId = item.getAttribute("data-id");
					if (itemId) {
						const prevId = item.previousElementSibling?.getAttribute("data-id") ?? null;
						const nextId = item.nextElementSibling?.getAttribute("data-id") ?? null;

						const tbody = datagrid.el.querySelector("tbody");

						if (tbody) {
							let componentPrefix = tbody.getAttribute("data-sortable-parent-path") ?? '';
							if (componentPrefix.length) componentPrefix = `${componentPrefix}-`;

							const url = tbody.getAttribute("sortable-ul") ?? "?do=sort";

							const data = {
								[`${componentPrefix}item_id`]: itemId,
								...(prevId ? {[`${componentPrefix}prev_id`]: prevId} : {}),
								...(nextId ? {[`${componentPrefix}next_id`]: nextId} : {}),
							};

							return await datagrid.ajax.request({
								method: "GET",
								url,
								data,
							})
						}
					}
				},
			})
		}
	}

	initSortableTree(datagrid: Datagrid): void {
		datagrid.el.querySelectorAll<HTMLElement>(".datagrid-tree-item-children").forEach((el) => {
			new Sortable(el, {
				handle: '.handle-sort',
				draggable: '.datagrid-tree-item:not(.datagrid-tree-header)',
				async onEnd({item}) {
					// TODO
				},
			})
		})
	}

}
