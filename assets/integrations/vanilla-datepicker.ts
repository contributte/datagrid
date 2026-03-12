import { Datepicker as DatepickerInterface } from "../types";
import { Constructor } from "../types";
import { window } from "../utils";
import type { Datepicker as DatepickerType } from "vanillajs-datepicker";
import type { DatepickerOptions } from "vanillajs-datepicker/Datepicker";

export class VanillaDatepicker implements DatepickerInterface {
	constructor(
		private datepicker?: Constructor<DatepickerType>,
		private opts: DatepickerOptions | ((input: HTMLInputElement) => DatepickerOptions) = {}
	) {}

	initDatepickers(elements: HTMLInputElement[]): void {
		const Datepicker = this.datepicker ?? window().Datepicker ?? null;
		if (!Datepicker) return;

		elements.forEach((element) => {
			const options = typeof this.opts === "function" ? this.opts(element) : this.opts;
			const _picker = new Datepicker(element, {
				...options,
				updateOnBlur: false
			});

			element.addEventListener('changeDate', () => {
				const form = element.closest('form');
				if (form) {
					form.submit();
				}
			});
		});
	}
}
