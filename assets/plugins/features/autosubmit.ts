import { DatagridPlugin } from "../../types";
import { debounce, Debounced, isEnter } from "../../utils";
import { Datagrid } from "../..";

export const AutosubmitAttribute = "data-autosubmit";

export const AutosubmitPerPageAttribute = "data-autosubmit-per-page";

export const AutosubmitChangeAttribute = "data-autosubmit-change";

export class AutosubmitPlugin implements DatagridPlugin {
	// One debounced submit per form, shared by all its inputs and triggers, so that they collapse
	// into a single request and Enter in any input cancels whatever is pending.
	private debouncedSubmits = new WeakMap<HTMLFormElement, Debounced>();

	constructor(private delay?: number) {
	}

	private getDebouncedSubmit(datagrid: Datagrid, form: HTMLFormElement): Debounced {
		let debounced = this.debouncedSubmits.get(form);
		if (!debounced) {
			debounced = debounce(() => datagrid.ajax.submitForm(form), this.delay);
			this.debouncedSubmits.set(form, debounced);
		}
		return debounced;
	}

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
					const debouncedSubmit = this.getDebouncedSubmit(datagrid, form);

					if (submitEl.hasAttribute(AutosubmitChangeAttribute)) {
						submitEl.addEventListener("change", () => debouncedSubmit());
					}

					// Enter submits right away, exactly once: handled on keydown so the browser's
					// implicit form submission can be suppressed, and the pending debounced submit is dropped.
					submitEl.addEventListener("keydown", (e) => {
						if (!isEnter(e) || e.isComposing) {
							return;
						}
						e.preventDefault();
						if (e.repeat) {
							return;
						}
						debouncedSubmit.cancel();
						datagrid.ajax.submitForm(form);
					});

					// Submit on every value change, whatever caused it: typing, paste or cut (also via
					// mouse/context menu), drag & drop, autofill, undo, the search clear button or
					// a virtual keyboard. Keys that don't change the value (modifiers, arrows, ...) don't fire it.
					// Input during IME composition submits too: Android keyboards keep the word being typed
					// in a composition, so waiting for compositionend would stop live filtering there.
					submitEl.addEventListener("input", () => debouncedSubmit());
				}
			});
	}
}


