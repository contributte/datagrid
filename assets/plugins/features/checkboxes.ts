import {DatagridPlugin} from "../../types";
import {Datagrid} from "../..";

export const CheckboxAttribute = "data-check";

export class CheckboxPlugin implements DatagridPlugin {

	private wasInit: Array<Datagrid> = [];

	loadCheckboxCount(datagrid: Datagrid) {
		const counter = document.querySelector<HTMLElement>(".datagrid-selected-rows-count");
		const total = Array.from(datagrid.el.querySelectorAll<HTMLInputElement>(`input[data-check='${datagrid.name}']`)).filter(c => !c.hasAttribute("data-check-all"));
		const checked = total.filter(e => (e.checked));
		if (counter) {
			counter.innerText = `${checked.length}/${total.length}`;
		}
		document.querySelectorAll<HTMLInputElement | HTMLButtonElement>(
			".row-group-actions *[type='submit']"
		).forEach(button => {
			button.disabled = checked.length === 0;
		});
		const select = datagrid.el.querySelector<HTMLSelectElement>("select[name='group_action[group_action]']");
		if (select) {
			select.disabled = checked.length === 0;
		}
	}

	onDatagridInit(datagrid: Datagrid): boolean {
		if (!this.wasInit.includes(datagrid)) {
			datagrid.ajax.addEventListener('complete', () => {
				this.onDatagridInit(datagrid)
			});
			this.wasInit.push(datagrid);
			this.loadCheckboxCount(datagrid);
		}
		this.loadCheckboxCount(datagrid);

		let lastCheckbox: null | HTMLElement = null;

		datagrid.el.addEventListener("click", e => {
			if (!(e.target instanceof HTMLElement)) {
				return;
			}
			if (e.target.classList.contains("col-checkbox")) {
				if (e.shiftKey && lastCheckbox) {
					const currentCheckboxRow = e.target.closest("tr");
					if (!currentCheckboxRow) return;

					const lastCheckboxRow = lastCheckbox.closest("tr");
					if (!lastCheckboxRow) return;

					const lastCheckboxTbody = lastCheckboxRow.closest("tbody");
					if (!lastCheckboxTbody) return;

					const checkboxesRows = Array.from(lastCheckboxTbody.querySelectorAll<HTMLElement>("tr"));
					const headerRows = Array.from(lastCheckboxTbody.closest('table')?.querySelectorAll<HTMLElement>("thead tr") ?? []).length;

					const [start, end] = [lastCheckboxRow.rowIndex -headerRows, currentCheckboxRow.rowIndex -headerRows].sort();
					const rows = checkboxesRows.slice(start, end + 1);

					rows.forEach(row => {
						const input = row.querySelector<HTMLInputElement>('.col-checkbox input[type="checkbox"]');
						if (input) {
							if (!input.checked) {
								input.checked = true;
								input.dispatchEvent(new Event('change', {bubbles: true}))
							}

						}
					});
				}
				lastCheckbox = e.target;
			}
		});


		let checkboxes = datagrid.el.querySelectorAll<HTMLInputElement>(`input[data-check='${datagrid.name}']`);

		// Handling a checkbox click + select all checkbox
		let notUserInvoked = false;
		checkboxes.forEach(checkEl => {
			checkEl.addEventListener("change", () => {
				// Select all
				const isSelectAll = checkEl.hasAttribute("data-check-all");
				if (isSelectAll) {
					if (notUserInvoked) {
						return;
					}
					if (datagrid.name !== checkEl.getAttribute("data-check-all")) return;
					const targetCheck = checkEl.checked;//this is vital as it gets swithced around due to not all being checked just yet.
					checkboxes.forEach(checkbox => {
						if (checkbox !== checkEl && checkbox.checked !== targetCheck && !checkbox.hasAttribute("data-check-all")) {
							checkbox.checked = targetCheck;
							//this will end up calling this callback a lot. But it needs to eb done as otherwise the happy checkboxes fail horribly.
							//Bubbles is needed as the happy callback catches on document
							notUserInvoked = true;//prevent nesting
							checkbox.dispatchEvent(new Event('change', {bubbles: true}));
							notUserInvoked = false;
						}
					});
					return;
				}

				const selectAll = datagrid.el.querySelectorAll<HTMLInputElement>(`input[data-check='${datagrid.name}'][data-check-all]`);
				if (selectAll.length > 0) {
					const allChecked = Array.from(checkboxes).filter(c => !c.hasAttribute("data-check-all")).every(c => c.checked);
					if (allChecked != selectAll.checked) {
						selectAll.forEach(el => {
							if(el.hasAttribute("data-override-check-all")){
								return;
							}
							notUserInvoked = true;
							el.checked = allChecked;
							el.dispatchEvent(new Event('change', {bubbles: true}));
							notUserInvoked = false;
						})
					}
				}
			});

			checkEl.addEventListener("change", () => {
				this.loadCheckboxCount(datagrid);
			})
		});

		return true;
	}
}
