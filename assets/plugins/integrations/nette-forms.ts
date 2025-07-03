import { DatagridPlugin, Nette } from "../../types";
import { Datagrid } from "../..";
import { window } from "../../utils";

export class NetteFormsPlugin implements DatagridPlugin {
	constructor(private nette?: Nette) {
	}

	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener('complete', (event) => {
			this.initNetteForms(datagrid);
		});

		this.initNetteForms(datagrid);

		return true;
	}

	private initNetteForms(datagrid: Datagrid): void {
		const nette = this.nette ?? window().Nette ?? null;

		if (nette) {
			datagrid.el.querySelectorAll<HTMLFormElement>("form").forEach(form => nette.initForm(form));
		}
	}
}
