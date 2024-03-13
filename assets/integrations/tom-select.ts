import { Constructor, Selectpicker } from "../types";
import { RecursivePartial, TomInput, TomSettings } from "tom-select/dist/types/types";
import type TomSelectType from "tom-select";
import { window } from "../utils";

export class TomSelect implements Selectpicker {
	constructor(
		private select?: Constructor<TomSelectType>,
		private opts: RecursivePartial<TomSettings> | ((input: HTMLElement | TomInput) => RecursivePartial<TomSettings>) = {}
	) {
	}

	initSelectpickers(elements: HTMLElement[]): void {
		const Select = this.select ?? window()?.TomSelect ?? null;

		if (Select) {
			elements.forEach(element => new Select(
				element as TomInput,
				typeof this.opts === "function" ? this.opts(element) : this.opts)
			);
		}
	}
}
