import { defaultDatagridNameResolver, isEnter } from "./utils";
import type { Ajax, DatagridEventMap, DatagridOptions, DatagridsOptions, EventDetail, EventListener, } from "./types";
import Select from "tom-select";
import {
	AutosubmitPlugin,
	CheckboxPlugin,
	ConfirmPlugin,
	EditablePlugin,
	InlinePlugin,
	NetteFormsPlugin,
	SelectpickerPlugin,
	SortablePlugin
} from "./plugins";
import { SortableJS } from "./integrations";
import { DatepickerPlugin } from "./plugins";
import { TomSelect, VanillaDatepicker } from "./integrations";

export class Datagrid extends EventTarget {
	private static readonly defaultOptions: DatagridOptions = {
		confirm: confirm,
		resolveDatagridName: defaultDatagridNameResolver,
		plugins: [],
	};

	public readonly name: string;

	public readonly ajax: Ajax;

	private readonly options: DatagridOptions;

	constructor(
		public readonly el: HTMLElement,
		ajax: Ajax | ((grid: Datagrid) => Ajax),
		options: Partial<DatagridOptions>
	) {
		super();

		this.options = {
			...Datagrid.defaultOptions,
			...options,
		};

		const name = this.resolveDatagridName();

		if (!name) {
			throw new Error("Cannot resolve name of a datagrid!");
		}

		this.name = name;

		this.ajax = typeof ajax === "function" ? ajax(this) : ajax;

		this.ajax.addEventListener("success", e => {
			if (e.detail.payload?._datagrid_name === this.name && e.detail.payload?._datagrid_init) {
				this.init();
			}
		});

		this.init();
	}

	public init() {
		let cancelled = !this.dispatch('beforeInit', { datagrid: this })
		if (!cancelled) {
			this.options.plugins.forEach((plugin) => {
				plugin.onDatagridInit?.(this)
			})
		}

		// Uncheck toggle-all
		const checkedRows = this.el.querySelectorAll<HTMLInputElement>("input[data-check]:checked");
		if (checkedRows.length === 1 && checkedRows[0].getAttribute("name") === "toggle-all") {
			const input = checkedRows[0];
			if (input) {
				input.checked = false;
			}
		}

		this.el.querySelectorAll<HTMLInputElement>("input[data-datagrid-manualsubmit]").forEach(inputEl => {
			const form = inputEl.closest("form");
			if (!form) return;

			inputEl.addEventListener("keydown", e => {
				if (!isEnter(e)) return;

				e.stopPropagation();
				e.preventDefault();
				return this.ajax.submitForm(form);
			});
		});

		this.ajax.addEventListener("success", ({ detail: { payload } }) => {
			// todo: maybe move?
			if (payload._datagrid_name && payload._datagrid_name === this.name) {
				const getColumnName = (el: HTMLElement) => el.getAttribute(
					"data-datagrid-reset-filter-by-column"
				)

				const resets = Array.from<HTMLElement>(this.el.querySelectorAll(
					`[data-datagrid-reset-filter-by-column]`
				));

				const nonEmptyFilters = payload.non_empty_filters ? payload.non_empty_filters : [] as string[];

				resets.forEach((el) => {
					const columnName = getColumnName(el);

					if (columnName && nonEmptyFilters.includes(columnName)) {
						el.classList.remove("hidden");
					} else {
						el.classList.add("hidden");
					}
				});

				if (nonEmptyFilters.length > 0) {
					const href = this.el.querySelector(".reset-filter")
						?.getAttribute("href");

					if (href) {
						resets.forEach((el) => {
							const columnName = getColumnName(el);

							const newHref = href.replace("-resetFilter", "-resetColumnFilter");
							el.setAttribute("href", `${newHref}&${this.name}-key=${columnName}`);
						})
					}
				}
			}
		})

		this.dispatch('afterInit', { datagrid: this });
	}

	public confirm(message: string): boolean {
		return this.options.confirm.bind(this)(message);
	}

	public resolveDatagridName(): string | null {
		return this.options.resolveDatagridName.bind(this)(this.el);
	}

	dispatch<
		K extends string, M extends DatagridEventMap = DatagridEventMap
	>(type: K, detail: K extends keyof M ? EventDetail<M[K]> : any, options?: boolean): boolean {
		return this.dispatchEvent(new CustomEvent(type, { detail }));
	}

	declare addEventListener: <K extends keyof M, M extends DatagridEventMap = DatagridEventMap>(
		type: K,
		listener: EventListener<this, M[K]>,
		options?: boolean | AddEventListenerOptions
	) => void;

	declare removeEventListener: <K extends keyof M, M extends DatagridEventMap = DatagridEventMap>(
		type: K,
		listener: EventListener<this, M[K]>,
		options?: boolean | AddEventListenerOptions
	) => void;

	declare dispatchEvent: <K extends string, M extends DatagridEventMap = DatagridEventMap>(
		event: K extends keyof M ? M[K] : CustomEvent
	) => boolean;
}

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
				new EditablePlugin(),
				new InlinePlugin(),
				new NetteFormsPlugin(),
				new SortablePlugin(new SortableJS()),
				new DatepickerPlugin(new VanillaDatepicker()),
				new SelectpickerPlugin(new TomSelect(Select))
			],
		},
		..._options,
	})
};
