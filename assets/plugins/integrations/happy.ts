import {Datagrid} from "../..";
import {DatagridPlugin} from "../../types";
import {window} from "../../utils";
import type {Happy} from "../../integrations";

export class HappyPlugin implements DatagridPlugin {
	constructor(private happy?: Happy) {
	}

	doInit(datagrid: Datagrid): boolean {
		const happy = this.happy ?? window().happy ?? null;

		if (happy) {
			happy.init();
		}

		return true;
	}

	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener('complete', (event) => {
			this.doInit(datagrid)
		});
		return this.doInit(datagrid);
	}
}
