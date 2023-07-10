import { Datagrid, Datagrids } from "..";
import { EventMap } from ".";

export interface DatagridEventDetail {
	datagrid: Datagrid;
}

export interface DatagridEventMap extends EventMap {
	beforeInit: CustomEvent<DatagridEventDetail>;
	afterInit: CustomEvent<DatagridEventDetail>;
}

export interface DatagridPlugin {
	onInit?(datagrids: Datagrids): void;

	onDatagridInit?(datagrid: Datagrid): boolean;
}

export interface DatagridOptions {
	confirm(this: Datagrid, message: string): boolean;

	// Returning null will skip this datagrid
	resolveDatagridName: (this: Datagrid, datagrid: HTMLElement) => string | null;
	plugins: DatagridPlugin[];
}

export interface DatagridsOptions {
	datagrid: Partial<DatagridOptions>;
	selector: string;
	root: HTMLElement | string;
}
