import { DatagridPlugin } from "../../types";
import { isEnter } from "../../utils";
import { Datagrid, Datagrids } from "../..";

export class InlinePlugin implements DatagridPlugin {
	onInit(datagrids: Datagrids) {
	}

	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener('success', ({ detail: { payload } }) => {
			if (!payload._datagrid_name || payload._datagrid_name !== datagrid.name) return;

			// Hide all edit triggers when inline editing starts
			if (payload._datagrid_inline_editing) {
				datagrid.el.querySelectorAll<HTMLElement>(".datagrid-inline-edit-trigger").forEach(trigger => {
					trigger.classList.add("hidden");
				});
			}

			// Show all edit triggers and mark row as edited when editing completes or is cancelled
			if (payload._datagrid_inline_edited || payload._datagrid_inline_edit_cancel) {
				if (payload._datagrid_inline_edited) {
					let rows = datagrid.el.querySelectorAll<HTMLTableCellElement>(
						`tr[data-id='${payload._datagrid_inline_edited}'] > td`
					);

					rows.forEach(row => {
						row.classList.add("edited");
					});
				}

				// Show all edit triggers again
				datagrid.el.querySelectorAll<HTMLElement>(".datagrid-inline-edit-trigger").forEach(trigger => {
					trigger.classList.remove("hidden");
				});

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

			const inlineTypes = [
				{ selector: '.datagrid-inline-edit input', submitName: 'inline_edit[submit]' },
				{ selector: '.datagrid-inline-add input', submitName: 'inline_add[submit]' },
			];

			for (const { selector, submitName } of inlineTypes) {
				datagrid.el.querySelectorAll<HTMLElement>(selector).forEach(inputEl => {
					inputEl.addEventListener("keydown", e => {
						if (!isEnter(e)) return;

						e.stopPropagation();
						e.preventDefault();

						inputEl.closest("tr")
							?.querySelector<HTMLElement>(`.col-action-inline-edit [name='${submitName}']`)
							?.click();
					});
				});
			}

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
