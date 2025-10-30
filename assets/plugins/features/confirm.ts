/**
* Datagrid plugin that asks for confirmation before deleting.
* If there is a modal in the DOM, use it, otherwise use a native confirm window.
*/
import { Datagrid } from "../../datagrid";
import { DatagridPlugin } from "../../types";

export const ConfirmAttribute = "data-datagrid-confirm";

export class ConfirmPlugin implements DatagridPlugin {
  /**
	* Initializes the plugin and registers event handlers.
	* @param datagrid The datagrid instance that the plugin is connected to.
	* @returns true if initialization was successful.
	*/
    private datagrid!: Datagrid;

    private modalId = 'datagridConfirmModal';
    private messageBoxId = 'datagridConfirmMessage';
    private confirmButtonId = 'datagridConfirmOk';

    onDatagridInit(datagrid: Datagrid): boolean {
        this.datagrid = datagrid;

        const confirmElements = datagrid.el.querySelectorAll<HTMLElement>(`[${ConfirmAttribute}]:not(.ajax)`);
        confirmElements.forEach(el => el.addEventListener("click", e => this.handleClick(el, e)));

        datagrid.ajax.addEventListener("interact", e => {
            const target = e.detail.element;
            if (datagrid.el.contains(target)) {
                this.handleClick(target, e);
            }
        });

        return true;
    }

    private handleClick(el: HTMLElement, e: Event): void {
        const message = this.getConfirmationMessage(el);
        if (!message) return;

        e.preventDefault();
        e.stopPropagation();

        const modal = this.getElement(this.modalId);
        if (modal) {
            this.showModalConfirm(modal, message, el);
        } else {
            if (!window.confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            } else {
                this.executeConfirmedAction(el);
            }
        }
    }

    private getConfirmationMessage(el: HTMLElement): string | null {
        return el.closest('a')?.getAttribute(ConfirmAttribute) ?? null;
    }

    private showModalConfirm(modal: HTMLElement, message: string, el: HTMLElement): void {
        const messageBox = this.getElement(this.messageBoxId);
        const confirmButton = this.getElement(this.confirmButtonId);

        if (!messageBox || !confirmButton) return;

        messageBox.textContent = message;

        const newButton = confirmButton.cloneNode(true) as HTMLElement;
        confirmButton.parentNode!.replaceChild(newButton, confirmButton);

        newButton.addEventListener("click", () => {
            bootstrap.Modal.getInstance(modal)?.hide();
            this.executeConfirmedAction(el);
        }, { once: true });

        new bootstrap.Modal(modal).show();
    }

    private executeConfirmedAction(el: HTMLElement): void {
        if (el instanceof HTMLAnchorElement && el.href) {
            const isAjax = el.classList.contains('ajax');
            isAjax
                ? naja.makeRequest('GET', el.href, null, { history: false })
                : window.location.href = el.href;
        } else {
            el.click();
        }
    }

    private getElement(id: string): HTMLElement | null {
        return document.getElementById(id);
    }
}
