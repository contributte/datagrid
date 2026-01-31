import { Datagrid } from "..";
import { Sortable as SortableInterface } from "../types";
import Sortable from "sortablejs"

export class SortableJS implements SortableInterface {
	private makeSortRequest(
		datagrid: Datagrid,
		item: HTMLElement,
		container: HTMLElement | null,
		parentId: string | null = null
	): Promise<void> | undefined {
		const itemId = item.getAttribute("data-id");
		if (!itemId || !container) return;

		let componentPrefix = container.getAttribute("data-sortable-parent-path") ?? '';
		if (componentPrefix.length) componentPrefix = `${componentPrefix}-`;

		const prevId = item.previousElementSibling?.getAttribute("data-id") ?? null;
		const nextId = item.nextElementSibling?.getAttribute("data-id") ?? null;
		const url = container.getAttribute("data-sortable-url") ?? "?do=sort";

		const data: Record<string, string> = {
			[`${componentPrefix}item_id`]: itemId,
		};
		if (prevId) data[`${componentPrefix}prev_id`] = prevId;
		if (nextId) data[`${componentPrefix}next_id`] = nextId;
		if (parentId) data[`${componentPrefix}parent_id`] = parentId;

		return datagrid.ajax.request({ method: "GET", url, data });
	}

	initSortable(datagrid: Datagrid): void {
		const sortable = datagrid.el.querySelector<HTMLElement>("[data-sortable]");
		if (!sortable) return;

		new Sortable(sortable, {
			handle: '.handle-sort',
			draggable: 'tr',
			sort: true,
			direction: 'vertical',
			onEnd: ({ item }) => {
				return this.makeSortRequest(datagrid, item, datagrid.el.querySelector("tbody"));
			},
		})
	}

	initSortableTree(datagrid: Datagrid): void {
		datagrid.el.querySelectorAll<HTMLElement>(".datagrid-tree-item-children").forEach((el) => {
			new Sortable(el, {
				group: 'datagrid-tree',
				handle: '.handle-sort',
				draggable: '.datagrid-tree-item:not(.datagrid-tree-header)',
				sort: true,
				direction: 'vertical',
				onEnd: ({ item }) => {
					const container = item.parentElement;
					const parentId = container?.parentElement?.getAttribute("data-id") ?? null;
					return this.makeSortRequest(datagrid, item, container, parentId);
				},
			})
		})
	}
}
