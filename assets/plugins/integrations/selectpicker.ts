import { DatagridPlugin, Selectpicker } from "../../types";
import { Datagrid } from "../..";

export class SelectpickerPlugin implements DatagridPlugin {
	constructor(private selectpicker: Selectpicker) {
	}

	onDatagridInit(datagrid: Datagrid): boolean {
		const elements = datagrid.el.querySelectorAll<HTMLElement>(".selectpicker");

		if (elements.length >= 1) {
			this.selectpicker.initSelectpickers(Array.from(elements), datagrid);
		}

		return true;
	}
}
