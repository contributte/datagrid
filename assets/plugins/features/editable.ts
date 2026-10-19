import { DatagridPlugin } from "../../types";
import { calculateCellLines, isEnter, isEsc } from "../../utils";
import { Datagrid } from "../..";

export const EditableUrlAttribute = "data-datagrid-editable-url";

export const EditableTypeAttribute = "data-datagrid-editable-type";

export const EditableElementAttribute = "data-datagrid-editable-element";

export const EditableValueAttribute = "data-datagrid-editable-value";

export const EditableAttrsAttribute = "data-datagrid-editable-attrs";

export class EditablePlugin implements DatagridPlugin {
	private readonly boundCells = new WeakSet<HTMLElement>();

	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener('success', (event) => {
			this.initEditableCells(datagrid);
		});

		this.initEditableCells(datagrid);

		return true;
	}

	initEditableCells(datagrid: Datagrid) {

		datagrid.el.querySelectorAll<HTMLElement>(`[${EditableUrlAttribute}]`).forEach(cell => {

			if (this.boundCells.has(cell)) return;
			this.boundCells.add(cell);

			cell.addEventListener("click", e => {

				if (cell instanceof HTMLAnchorElement || cell.classList.contains("datagrid-inline-edit")) return;

				if (!cell.classList.contains("editing")) {
					cell.classList.add("editing");
					const originalHtml = cell.innerHTML;
					const originalValue = (originalHtml.replace(/<\/?br>/g, "\n")).trim();
					const valueToEdit = (cell.getAttribute(EditableValueAttribute) ?? originalValue).trim();

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
							cell.innerHTML = cell.getAttribute(EditableElementAttribute) ?? "";
							input = cell.querySelector("select")!;
							input
								.querySelectorAll(`option[value='${valueToEdit}']`)
								.forEach(input => input.setAttribute("selected", "true"));
							break;
						default:
							cell.innerHTML = `<input type='${type}' />`;
							input = cell.querySelector("input")!;
							input.setAttribute("value", valueToEdit);
					}

					const attributes = JSON.parse(cell.getAttribute(EditableAttrsAttribute) ?? "{}");
					for (const key in attributes) {
						const value = attributes[key];
						input.setAttribute(key, value);
					}

					cell.classList.remove("edited", "edited-error");
					let finished = false;

					const submitCell = async (el: HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement) => {
						if (finished) return;
						finished = true;
						let value = el.value;
						if (value !== valueToEdit) {
							if (el instanceof HTMLSelectElement) {
								el.disabled = true;
							} else {
								el.readOnly = true;
							}
							cell.setAttribute("aria-busy", "true");
							try {
								const response = await datagrid.ajax.request({
									url: cell.getAttribute(EditableUrlAttribute) ?? "",
									method: "POST",
									rejectOnCancel: true,
									options: { unique: false, abort: false },
									data: {
										value,
									},
								}) as any;

								if (!cell.isConnected) return;

								if (type === "select") {
									cell.innerHTML = (el instanceof HTMLSelectElement)
										? el.options[el.selectedIndex]?.text ?? value
										: value;
								} else {
									if (response._datagrid_editable_new_value) {
										value = response._datagrid_editable_new_value;
									}
									cell.innerHTML = value;
								}

								// Keep the editable value in sync with the submitted value so
								// that a subsequent edit starts from the current value, not the
								// one rendered on initial page load (see issue #948).
								cell.setAttribute(EditableValueAttribute, el.value);

								cell.classList.add("edited");
							} catch {
								cell.innerHTML = originalHtml;
								cell.classList.add("edited-error");
							} finally {
								cell.removeAttribute("aria-busy");
							}
						} else {
							cell.innerHTML = originalHtml;
						}

						cell.classList.remove("editing");
					};

					cell
						.querySelectorAll<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>(
							"input, textarea, select"
						)
						.forEach(el => {
							if (el instanceof HTMLSelectElement) {
								el.addEventListener("change", () => submitCell(el));
							}
							el.addEventListener("blur", () => submitCell(el));

							el.addEventListener("keydown", e => {
								const keyboardEvent = e as KeyboardEvent;
								if (keyboardEvent.isComposing) return;
								if (isEnter(keyboardEvent) && !(el instanceof HTMLTextAreaElement && keyboardEvent.shiftKey)) {
									e.stopPropagation();
									e.preventDefault();
									return submitCell(el);
								}

								if (isEsc(e as KeyboardEvent)) {
									e.stopPropagation();
									e.preventDefault();
									if (finished) return;
									finished = true;
									cell.classList.remove("editing");
									cell.innerHTML = originalHtml;
								}
							});

						});
					input.focus();
				}
			});
		});
	}
}
