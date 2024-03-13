import { DatagridPlugin } from "../../types";
import { calculateCellLines, isEnter, isEsc } from "../../utils";
import { Datagrid } from "../..";

export const EditableUrlAttribute = "data-datagrid-editable-url";

export const EditableTypeAttribute = "data-datagrid-editable-type";

export const EditableElementAttribute = "data-datagrid-editable-element";

export const EditableValueAttribute = "data-datagrid-editable-value";

export const EditableAttrsAttribute = "datagrid-editable-attrs";

export class EditablePlugin implements DatagridPlugin {
	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.el.querySelectorAll<HTMLElement>(`[${EditableUrlAttribute}]`).forEach(cell => {
			if (cell instanceof HTMLAnchorElement || cell.classList.contains("datagrid-inline-edit")) return;

			if (!cell.classList.contains("editing")) {
				cell.classList.add("editing");
				const originalValue = cell.innerHTML.replace(/<\/?br>/g, "\n");
				const valueToEdit = cell.getAttribute(EditableValueAttribute) ?? originalValue;

				cell.setAttribute("originalValue", originalValue);
				cell.setAttribute("valueToEdit", valueToEdit);

				const type = cell.getAttribute(EditableTypeAttribute) ?? "text";

				let input: HTMLElement;

				switch (type) {
					case "textarea":
						cell.innerHTML = `<textarea rows="${calculateCellLines(cell)}">${valueToEdit}</textarea>`;
						input = cell.querySelector("textarea")!;
						break;
					case "select":
						input = cell.querySelector(cell.getAttribute(EditableElementAttribute) ?? "")!;
						input
							.querySelectorAll(`option[value='${valueToEdit}']`)
							.forEach(input => input.setAttribute("selected", "true"));
						break;
					default:
						cell.innerHTML = `<input type='${type}' />`;
						input = cell.querySelector("input")!;
				}

				const attributes = JSON.parse(cell.getAttribute(EditableAttrsAttribute) ?? "{}");
				for (const key in attributes) {
					const value = attributes[key];
					input.setAttribute(key, value);
				}

				cell.classList.remove("edited");

				const submitCell = async (el: HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement) => {
					let value = el.value;
					if (value !== valueToEdit) {
						try {
							const response = await datagrid.ajax.request({
								url: cell.getAttribute(EditableUrlAttribute) ?? "",
								method: "POST",
								data: {
									value,
								},
							}) as any;

							if (type === "select") {
								cell.innerHTML = cell.querySelector(`option[value='${value}']`)?.innerHTML ?? "";
							} else {
								if (response._datagrid_editable_new_value) {
									value = response._datagrid_editable_new_value;
								}
								cell.innerHTML = value;
							}
							cell.classList.add("edited");
						} catch {
							cell.innerHTML = originalValue;
							cell.classList.add("edited-error");
						}
					} else {
						cell.innerHTML = originalValue;
					}

					cell.classList.remove("editing");
				};

				cell
					.querySelectorAll<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>(
						"input, textarea, select"
					)
					.forEach(el => {
						el.addEventListener("blur", () => submitCell(el));
						el.addEventListener("keydown", e => {
							if (isEnter(e as KeyboardEvent)) {
								e.stopPropagation();
								e.preventDefault();
								return submitCell(el);
							}

							if (isEsc(e as KeyboardEvent)) {
								e.stopPropagation();
								e.preventDefault();
								cell.classList.remove("editing");
								cell.innerHTML = originalValue;
							}
						});

						if (el instanceof HTMLSelectElement) {
							el.addEventListener("change", () => submitCell(el));
						}
					});
			}
		});

		return true;
	}
}
