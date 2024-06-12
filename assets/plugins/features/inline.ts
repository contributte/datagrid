import { DatagridPlugin } from "../../types";
import { isEnter } from "../../utils";
import { Datagrid, Datagrids } from "../..";

export class InlinePlugin implements DatagridPlugin {
	onInit(datagrids: Datagrids) {
	}

	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener('success', ({ detail: { payload } }) => {
			if (!payload._datagrid_name || payload._datagrid_name !== datagrid.name) return;

			if (payload._datagrid_inline_edited || payload._datagrid_inline_edit_cancel) {
				const trigger = datagrid.el.querySelector('.datagrid-inline-edit-trigger');

				if (payload._datagrid_inline_edited) {
					let rows = datagrid.el.querySelectorAll<HTMLTableCellElement>(
						`tr[data-id='${payload._datagrid_inline_edited}'] > td`
					);

					rows.forEach(row => {
						row.classList.add("edited");
					})
				}

				trigger?.classList.remove("hidden");
				return;
			}

			if (payload._datagrid_inline_adding) {
				const row = datagrid.el.querySelector<HTMLElement>(".datagrid-row-inline-add");
				if (row) {
					row.classList.remove("datagrid-row-inline-add-hidden");
					row.querySelector<HTMLInputElement | HTMLTextAreaElement>(
						"input:not([readonly]), textarea:not([readonly])"
					)?.focus();
				}
			}

			if (payload._datagrid_inline_editing) {
				datagrid.el.querySelector<HTMLElement>(".datagrid-inline-edit-trigger")
					?.classList.add("hidden");
			}

			datagrid.el.querySelectorAll<HTMLElement>(".datagrid-inline-edit input").forEach(inputEl => {
				inputEl.addEventListener("keydown", e => {
					if (!isEnter(e)) return;

					e.stopPropagation();
					e.preventDefault();

					return inputEl
						.closest("tr")
						?.querySelector<HTMLElement>(".col-action-inline-edit [name='inline_edit[submit]']")
						?.click();
				});
			});

			datagrid.el.querySelectorAll<HTMLElement>(".datagrid-inline-add input").forEach(inputEl => {
				inputEl.addEventListener("keydown", e => {
					if (!isEnter(e)) return;

					e.stopPropagation();
					e.preventDefault();

					return inputEl
						.closest("tr")
						?.querySelector<HTMLElement>(".col-action-inline-edit [name='inline_add[submit]']")
						?.click();
				});
			});

			datagrid.el.querySelectorAll<HTMLElement>("[data-datagrid-cancel-inline-add]").forEach(cancel => {
				cancel.addEventListener("mouseup", e => {
					if (e.button === 0) {
						e.stopPropagation();
						e.preventDefault();
						const inlineAdd = cancel.closest<HTMLElement>(".datagrid-row-inline-add");
						if (inlineAdd) {
							inlineAdd.classList.add("datagrid-row-inline-add-hidden");
						}
					}
				});
			});
		})

		return true;
	}
}
