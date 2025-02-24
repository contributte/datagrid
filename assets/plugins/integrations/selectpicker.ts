import { DatagridPlugin, Selectpicker } from "../../types";
import { Datagrid } from "../..";

export class SelectpickerPlugin implements DatagridPlugin {
	constructor(private selectpicker: Selectpicker) {
	}

	doInit(datagrid:Datagrid){
		const elements = datagrid.el.querySelectorAll<HTMLElement>(".selectpicker");
		if (elements.length >= 1) {
			const filtered = Array.from(elements).filter(el => el instanceof HTMLInputElement || el instanceof HTMLSelectElement);
			if(filtered.length >= 1){
				this.selectpicker.initSelectpickers(filtered, datagrid);
			}
		}

	}
	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener('complete', (event) => {
			this.doInit(datagrid);
		});
		this.doInit(datagrid);
		return true;
	}
}
