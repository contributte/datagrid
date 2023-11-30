import { Datagrid } from "..";

export interface Sortable {
	initSortable(datagrid: Datagrid): void;

	initSortableTree(datagrid: Datagrid): void;
}

export interface Selectpicker {
	initSelectpickers(elements: HTMLElement[], datagrid: Datagrid): void;
}
