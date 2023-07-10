import { DatagridPlugin } from "../../types";
import { Datagrid } from "../..";

export const CheckboxAttribute = "data-check";

export class CheckboxPlugin implements DatagridPlugin {
	onDatagridInit(datagrid: Datagrid): boolean {
		let lastCheckbox = null;

		datagrid.el.addEventListener("click", e => {
			if (!(e.target instanceof HTMLElement)) return;

			if (e.target.classList.contains("col-checkbox")) {
				lastCheckbox = e.target;
				if (e.shiftKey && lastCheckbox) {
					const currentCheckboxRow = lastCheckbox.closest("tr");
					if (!currentCheckboxRow) return;

					const lastCheckboxRow = lastCheckbox.closest("tr");
					if (!lastCheckboxRow) return;

					const lastCheckboxTbody = lastCheckboxRow.closest("tbody");
					if (!lastCheckboxTbody) return;

					const checkboxesRows = Array.from(lastCheckboxTbody.querySelectorAll<HTMLElement>("tr"));
					const [start, end] = [lastCheckboxRow.rowIndex, currentCheckboxRow.rowIndex].sort();
					const rows = checkboxesRows.slice(start, end + 1);

					rows.forEach(row => {
						const input = row.querySelector<HTMLInputElement>('.col-checkbox input[type="checkbox"]');
						if (input) {
							input.checked = true;
						}
					});
				}
			}
		});

		let checkboxes = datagrid.el.querySelectorAll<HTMLInputElement>(`input[data-check='${datagrid.name}']`);
		const select = datagrid.el.querySelector<HTMLSelectElement>("select[name='group_action[group_action]']");
		const actionButtons = document.querySelectorAll<HTMLInputElement | HTMLButtonElement>(
			".row-group-actions *[type='submit']"
		);
		const counter = document.querySelector<HTMLElement>(".datagrid-selected-rows-count");

		// Handling a checkbox click + select all checkbox
		checkboxes.forEach(checkEl => {
			checkEl.addEventListener("change", () => {
				// Select all
				const isSelectAll = checkEl.hasAttribute("data-check-all");
				if (isSelectAll) {
					if (datagrid.name !== checkEl.getAttribute("data-check-all")) return;

					checkboxes.forEach(checkbox => (checkbox.checked = checkEl.checked));

					// todo: refactor not to repeat this code twice
					actionButtons.forEach(button => (button.disabled = !checkEl.checked));

					if (select) {
						select.disabled = !checkEl.checked;
					}

					if (counter) {
						const total = Array.from(checkboxes).filter(c => !c.hasAttribute("data-check-all")).length;
						counter.innerText = `${checkEl.checked ? total : 0}/${total}`;
					}
					return;
				} else {
					const selectAll = datagrid.el.querySelector<HTMLInputElement>(`input[data-check='${datagrid.name}'][data-check-all]`);
					if (selectAll) {
						selectAll.checked = Array.from(checkboxes).filter(c => !c.hasAttribute("data-check-all")).every(c => c.checked);
					}
				}

				const checkedBoxes = Array.from(checkboxes).filter(checkbox => checkbox.checked && !checkEl.hasAttribute("data-check-all"));
				const hasChecked = checkedBoxes.length >= 1;

				actionButtons.forEach(button => (button.disabled = !hasChecked));

				if (select) {
					select.disabled = !hasChecked;
				}

				if (counter) {
					counter.innerText = `${checkedBoxes.length}/${checkboxes.length}`;
				}
			});
		});

		return true;
	}
}
