import { Datagrid } from "./datagrid";
import {
	AutosubmitPlugin,
	CheckboxPlugin,
	ConfirmPlugin,
	HappyPlugin,
	InlinePlugin,
	NetteFormsPlugin,
	SelectpickerPlugin,
	SortablePlugin
} from "./plugins";
import { Ajax, DatagridsOptions } from "./types";
import { SortableJS } from "./integrations/sortable-js";
import { DatepickerPlugin } from "./plugins/integrations/datepicker";
import { BootstrapSelect, Happy, VanillaDatepicker } from "./integrations";

export class Datagrids {
	private datagrids: Datagrid[] = [];

	readonly options: DatagridsOptions;

	readonly root: HTMLElement;

	constructor(readonly ajax: Ajax, options: Partial<DatagridsOptions> = {}) {
		this.options = {
			selector: "div[data-datagrid-name]",
			datagrid: {},
			root: document.body,
			...options,
		};

		const root = typeof this.options.root === "string"
			? document.querySelector(this.options.root)
			: this.options.root;

		if (!root || !(root instanceof HTMLElement)) {
			throw new Error("Root element not found or is not an HTMLElement");
		}

		this.root = root;

		this.init();
	}

	init() {
		this.ajax.onInit();
		(this.options.datagrid?.plugins ?? []).forEach((plugin) => plugin.onInit?.(this));

		this.initDatagrids();
	}

	initDatagrids() {
		this.datagrids = Array.from(this.root.querySelectorAll<HTMLElement>(this.options.selector)).map(
			datagrid => new Datagrid(datagrid, this.ajax, this.options.datagrid)
		);
	}
}

export const createDatagrids = (ajax: Ajax, _options: Partial<DatagridsOptions> = {}) => {
	return new Datagrids(ajax, _options);
};

export const createFullDatagrids = (ajax: Ajax, _options: Partial<DatagridsOptions> = {}) => {
	return createDatagrids(ajax, {
		datagrid: {
			plugins: [
				new AutosubmitPlugin(),
				new CheckboxPlugin(),
				new ConfirmPlugin(),
				new InlinePlugin(),
				new NetteFormsPlugin(),
				new HappyPlugin(new Happy()),
				new SortablePlugin(new SortableJS()),
				new DatepickerPlugin(new VanillaDatepicker()),
				new SelectpickerPlugin(new BootstrapSelect())
			],
		},
		..._options,
	})
};
