import { defaultDatagridNameResolver, isEnter } from "./utils";
import type { Ajax, DatagridEventMap, DatagridOptions, EventDetail, EventListener, } from "./types";

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
		let cancelled = !this.dispatch('beforeInit', {datagrid: this})
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

		this.ajax.addEventListener("success", ({detail: {payload}}) => {
			// todo: maybe move?
			if (payload._datagrid_name && payload._datagrid_name === this.name) {
				this.el.querySelector<HTMLElement>("[data-datagrid-reset-filter-by-column]")
					?.classList.add("hidden");

				if (payload.non_empty_filters && payload.non_empty_filters.length >= 1) {
					const resets = Array.from<HTMLElement>(this.el.querySelectorAll(
						`[data-datagrid-reset-filter-by-column]`
					));

					const getColumnName = (el: HTMLElement) => el.getAttribute(
						"data-datagrid-reset-filter-by-column"
					)

					/// tf?
					for (const columnName of payload.non_empty_filters) {
						resets.find(getColumnName)?.classList.remove("hidden");
					}

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

		this.dispatch('afterInit', {datagrid: this});
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
		return this.dispatchEvent(new CustomEvent(type, {detail}));
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
