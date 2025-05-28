import { Datagrid } from "../..";
import { DatagridPlugin, Datepicker } from "../../types";

export class DatepickerPlugin implements DatagridPlugin {
	constructor(private datepicker: Datepicker) {
	}

	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener('complete', (event) => {
			this.initDatepicker(datagrid);
		});

		this.initDatepicker(datagrid);

		return true;
	}

	private initDatepicker(datagrid: Datagrid): void {
		const elements = datagrid.el.querySelectorAll<HTMLInputElement>("input[data-provide='datepicker']");

		if (elements.length >= 1) {
			this.datepicker.initDatepickers(Array.from(elements), datagrid);
		}
	}
}
