import { Datagrid } from "../..";
import { DatagridPlugin, Datepicker } from "../../types";

export class DatepickerPlugin implements DatagridPlugin {
	constructor(private datepicker: Datepicker) {
	}

	onDatagridInit(datagrid: Datagrid): boolean {
		const elements = datagrid.el.querySelectorAll<HTMLInputElement>("input[data-provide='datepicker']");

		if (elements.length >= 1) {
			this.datepicker.initDatepickers(Array.from(elements), datagrid);
		}

		return true;
	}
}
