import { Datepicker as DatepickerInterface } from "../types";
import { Datepicker } from "vanillajs-datepicker";
import { DatepickerOptions } from "vanillajs-datepicker/Datepicker";

export class VanillaDatepicker implements DatepickerInterface {
	constructor(private opts: DatepickerOptions | ((input: HTMLInputElement) => DatepickerOptions) = {}) {
	}


	initDatepickers(elements: HTMLInputElement[]): void {
		elements.forEach((element) => new Datepicker(element, typeof this.opts === "function" ? this.opts(element) : this.opts));
	}

}
