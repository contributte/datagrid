import { DatagridPlugin } from "../../types";
import { debounce, isEnter, isFunctionKey, isInKeyRange } from "../../utils";
import { Datagrid } from "../..";

export const AutosubmitAttribute = "data-autosubmit";

export const AutosubmitPerPageAttribute = "data-autosubmit-per-page";

export const AutosubmitChangeAttribute = "data-autosubmit-change";

export class AutosubmitPlugin implements DatagridPlugin {
	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener('complete', (event) => {
			this.initPerPage(datagrid);
			this.initChange(datagrid);
		});

		this.initPerPage(datagrid);
		this.initChange(datagrid);

		return true;
	};

	// Auto-submit perPage
	initPerPage(datagrid: Datagrid) {
		datagrid.el.querySelectorAll<HTMLSelectElement>(`select[${AutosubmitPerPageAttribute}]`)
			.forEach(pageSelectEl => {
				pageSelectEl.addEventListener("change", () => {
					let inputEl = pageSelectEl.parentElement?.querySelector("input[type=submit]");
					if (!inputEl) {
						inputEl = pageSelectEl.parentElement?.querySelector("button[type=submit]");
					}
					if (!(inputEl instanceof HTMLElement)) return;
					const form = inputEl.closest('form');
					form && datagrid.ajax.submitForm(form);
				});
			});
	}

	// Auto-submit change
	initChange(datagrid: Datagrid) {
		datagrid.el.querySelectorAll<HTMLSelectElement | HTMLInputElement>(`[${AutosubmitAttribute}]`)
			.forEach(submitEl => {
				const form = submitEl.closest("form");
				if (!form) return;

				if (submitEl.dataset.listenersAttached === "true") {
					return; // Skip if listeners are already attached
				}
				submitEl.dataset.listenersAttached = "true";

				// Select auto-submit
				if (submitEl instanceof HTMLSelectElement) {
					submitEl.addEventListener("change", () => datagrid.ajax.submitForm(form));

					return;
				}

				// Input auto-submit
				if (submitEl instanceof HTMLInputElement) {
					// Handle change events
					if (submitEl.hasAttribute(AutosubmitChangeAttribute)) {
						submitEl.addEventListener(
							"change",
							debounce(() => datagrid.ajax.submitForm(form))
						);
					}

					const debouncedSubmit = debounce(() => datagrid.ajax.submitForm(form));
					submitEl.addEventListener("keyup", (e) => {
						// Enter submits immediately, no debounce wait.
						if (isEnter(e)) {
							datagrid.ajax.submitForm(form);
							return;
						}
						// Skip alt/ctrl/etc and F-keys here, not inside debounce — a late
						// modifier keyup would otherwise overwrite the debounced submit's target.
						if (isInKeyRange(e, 9, 40) || isFunctionKey(e)) {
							return;
						}
						debouncedSubmit();
					});
				}
			});
	}
}


