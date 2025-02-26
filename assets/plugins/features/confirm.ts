import { Datagrid } from "../../datagrid";
import { DatagridPlugin } from "../../types";

export const ConfirmAttribute = "data-datagrid-confirm";

export class ConfirmPlugin implements DatagridPlugin {
	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.el
			.querySelectorAll<HTMLElement>(`[${ConfirmAttribute}]:not(.ajax)`)
			.forEach(confirmEl =>
				confirmEl.addEventListener("click", e => this.confirmEventHandler.bind(datagrid)(e.target as HTMLElement, e))
			);

		datagrid.ajax.addEventListener("success", e =>
			datagrid.el
				.querySelectorAll<HTMLElement>(`[${ConfirmAttribute}]:not(.ajax)`)
				.forEach(confirmEl =>
					confirmEl.addEventListener("click", e => this.confirmEventHandler.bind(datagrid)(e.target as HTMLElement, e))
				)
		);
		datagrid.ajax.addEventListener("interact", e => this.confirmEventHandler.bind(datagrid)(e.detail.element, e));

		return true;
	}

	confirmEventHandler(this: Datagrid, el: HTMLElement, e: Event) {
		const message = el.closest('a')?.getAttribute(ConfirmAttribute)!;
		if (!message) return;

		if (!window.confirm.bind(window)(message)) {
			e.stopPropagation();
			e.preventDefault();
		}
	}
}
