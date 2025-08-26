import { Datagrid } from "../../datagrid";
import { DatagridPlugin } from "../../types";

export const ConfirmAttribute = "data-datagrid-confirm";

export class ConfirmPlugin implements DatagridPlugin {
	onDatagridInit(datagrid: Datagrid): boolean {
        datagrid.el
            .querySelectorAll<HTMLElement>(`[${ConfirmAttribute}]:not(.ajax)`)
            .forEach(confirmEl =>
                confirmEl.addEventListener("click", e => this.confirmEventHandler.call(datagrid, confirmEl, e))
            );

        datagrid.ajax.addEventListener("interact", e => {
            if (datagrid.el.contains(e.detail.element)) {
                this.confirmEventHandler.call(datagrid, e.detail.element, e);
            }
        });

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
