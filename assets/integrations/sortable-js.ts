import { Datagrid } from "..";
import { Constructor, Sortable as SortableInterface } from "../types";
import type SortableType from "sortablejs";

export class SortableJS implements SortableInterface {
	constructor(private sortable?: Constructor<SortableType>) {}

	private getSortable(): Constructor<SortableType> | null {
		return this.sortable ?? (window as any).Sortable ?? null;
	}

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

		const Sortable = this.getSortable();
		if (!Sortable) return;

		new Sortable(sortable, {
			handle: '.handle-sort',
			draggable: 'tr',
			sort: true,
			direction: 'vertical',
			onEnd: ({ item }: { item: HTMLElement }) => {
				return this.makeSortRequest(datagrid, item, datagrid.el.querySelector("tbody"));
			},
		})
	}

	initSortableTree(datagrid: Datagrid): void {
		const Sortable = this.getSortable();
		if (!Sortable) return;

		datagrid.el.querySelectorAll<HTMLElement>(".datagrid-tree-item-children").forEach((el) => {
			new Sortable(el, {
				group: 'datagrid-tree',
				handle: '.handle-sort',
				draggable: '.datagrid-tree-item:not(.datagrid-tree-header)',
				sort: true,
				direction: 'vertical',
				onEnd: ({ item }: { item: HTMLElement }) => {
					const container = item.parentElement;
					const parentId = container?.parentElement?.getAttribute("data-id") ?? null;
					return this.makeSortRequest(datagrid, item, container, parentId);
				},
			})
		})
	}
}
