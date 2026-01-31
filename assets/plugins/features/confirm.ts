/**
 * Datagrid plugin that asks for confirmation before deleting.
 * If there is a modal in the DOM, use it, otherwise use a native confirm window.
 */
import { Datagrid } from "../../datagrid";
import { DatagridPlugin } from "../../types";
import * as bootstrap from "bootstrap";
import naja from "naja";

interface NajaInteractDetail {
	method: string;
	url: string;
	payload: any;
	options: Record<string, any>;
}

export const ConfirmAttribute = "data-datagrid-confirm";

export class ConfirmPlugin implements DatagridPlugin {
	private datagrid!: Datagrid;

	private modalId = 'datagridConfirmModal';
	private messageBoxId = 'datagridConfirmMessage';
	private confirmButtonId = 'datagridConfirmOk';

	/**
	 * Initializes the plugin and registers event handlers.
	 * @param datagrid The datagrid instance that the plugin is connected to.
	 * @returns true if initialization was successful.
	 */

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
			this.showModalConfirm(modal, message, el, e);
		} else {
			if (window.confirm(message)) {
				this.executeConfirmedAction(el, e);
			}
		}
	}

	private getConfirmationMessage(el: HTMLElement): string | null {
		return el.getAttribute(ConfirmAttribute) ?? el.closest('a')?.getAttribute(ConfirmAttribute) ?? null;
	}

	private showModalConfirm(modal: HTMLElement, message: string, el: HTMLElement, e: Event): void {
		const messageBox = this.getElement(this.messageBoxId);
		const confirmButton = this.getElement(this.confirmButtonId);

		if (typeof bootstrap === 'undefined' || !messageBox || !confirmButton) {
			if (window.confirm(message)) {
				this.executeConfirmedAction(el, e);
			}
			return;
		}

		messageBox.textContent = message;

		const newButton = confirmButton.cloneNode(true) as HTMLElement;
		confirmButton.parentNode!.replaceChild(newButton, confirmButton);

		newButton.addEventListener("click", () => {
			bootstrap.Modal.getInstance(modal)?.hide();
			this.executeConfirmedAction(el, e);
		}, { once: true });

		const modalInstance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
		modalInstance.show();
	}

	private executeConfirmedAction(el: HTMLElement, e?: Event): void {
		const detail = e instanceof CustomEvent && e.detail ? e.detail as NajaInteractDetail : null;
		const isAjax = el.classList.contains('ajax');

		if (el instanceof HTMLAnchorElement && el.href && isAjax) {
			if (typeof naja === 'undefined') {
				return;
			}

			if (detail && typeof detail.method === 'string' && typeof detail.url === 'string') {
				const options = { ...detail.options, history: false };
				naja.makeRequest(detail.method, detail.url, detail.payload ?? null, options);
			} else {
				const method = el.getAttribute('data-naja-method') ?? 'GET';
				naja.makeRequest(method, el.href, null, { history: false });
			}
			return;
		}

		this.triggerNativeInteraction(el);
	}

	private getElement(id: string): HTMLElement | null {
		return document.getElementById(id);
	}

	private triggerNativeInteraction(el: HTMLElement): void {
		const confirmValue = el.getAttribute(ConfirmAttribute);

		if (confirmValue !== null) {
			el.removeAttribute(ConfirmAttribute);
		}

		try {
			el.click();
		} finally {
			if (confirmValue !== null) {
				el.setAttribute(ConfirmAttribute, confirmValue);
			}
		}
	}
}
