import { Datagrid } from "../..";
import { DatagridPlugin } from "../../types";

export class ItemDetailPlugin implements DatagridPlugin {
	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.el.querySelectorAll<HTMLElement>("[data-toggle-detail-grid]")
			.forEach((el) => {
				if (el.getAttribute("data-toggle-detail-grid") !== datagrid.name) return;
				const toggleId = el.getAttribute("data-toggle-detail")!;


				el.addEventListener("click", (e) => {
					const contentRow = datagrid.el.querySelector<HTMLElement>(
						`.item-detail-${datagrid.name}-id-${toggleId}`
					);

					const gridRow = el.closest('tr');

					if (contentRow) {
						// const div = contentRow.querySelector<HTMLDivElement>("td > div");
						// if (div && !el.classList.contains("datagrid--slide-toggle")) {
						// 	attachSlideToggle(div, el);
						// } TODO: fix
						contentRow.classList.add("datagrid--content-row")
						contentRow.classList.toggle("is-active");

					}

					datagrid.ajax.addEventListener("before", (e) => {
						if (e.detail.params.url.includes(`do=${datagrid.name}-getItemDetail`) && e.detail.params.url.includes(`grid-id=${toggleId}`)) {
							e.stopPropagation();
							e.preventDefault();
						}
					})
				})
			});

		datagrid.ajax.addEventListener("success", ({detail: {payload}}) => {
			if (payload._datagrid_redraw_item_id && payload._datagrid_redraw_item_class) {
				datagrid.el.querySelector<HTMLTableRowElement>(
					`tr[data-id='${payload._datagrid_redraw_item_id}']`
				)?.setAttribute("class", payload._datagrid_redraw_item_class)
			}
		})

		return true;
	}
}
