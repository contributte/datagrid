import { Datepicker as DatepickerInterface } from "../types";
import { Datepicker } from "vanillajs-datepicker";
import { DatepickerOptions } from "vanillajs-datepicker/Datepicker";

export class VanillaDatepicker implements DatepickerInterface {
	constructor(private opts: DatepickerOptions | ((input: HTMLInputElement) => DatepickerOptions) = {}) {
	}

	initDatepickers(elements: HTMLInputElement[]): void {
		elements.forEach((element) => {
			const options = typeof this.opts === "function" ? this.opts(element) : this.opts;
			const picker = new Datepicker(element, {
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
