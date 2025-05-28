import { DatagridPlugin, Selectpicker } from "../../types";
import { Datagrid } from "../..";

export class SelectpickerPlugin implements DatagridPlugin {
	constructor(private selectpicker: Selectpicker) {
	}

	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener('complete', (event) => {
			this.initSelectpicker(datagrid);
		});

		this.initSelectpicker(datagrid);

		return true;
	}

	private initSelectpicker(datagrid: Datagrid): void {
		const elements = datagrid.el.querySelectorAll<HTMLElement>("select.selectpicker");

		if (elements.length >= 1) {
			this.selectpicker.initSelectpickers(Array.from(elements), datagrid);
		}
	}
}
