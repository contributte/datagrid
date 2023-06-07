import { happyStyles } from "../css/happy.css";

/**
 * Slightly cleaned up & typed version of happy-inputs by paveljanda.
 */
export class Happy {
	private colors: string[] = ["primary", "success", "info", "warning", "danger", "white", "gray"];

	private templates = {
		radio: '<div class="happy-radio"><b></b></div>',
		checkbox:
			'<div class="happy-checkbox"><svg viewBox="0 0 30 30"><rect class="mark-storke" x="15" y="3" rx="1" ry="1" width="10" height="4"/><rect class="mark-storke" x="-7" y="21" rx="1" ry="1" width="19" height="4"/></svg></div>',
		text: "",
		textarea: "",
	};

	init() {
		if (!document.querySelector('[data-happy-stylesheet]')) {
			document.head.append(`<style data-happy-stylesheet>${happyStyles}</style>`)
		}
		this.removeBySelector(".happy-radio");
		this.removeBySelector(".happy-checkbox");

		this.initRadio();
		this.initCheckbox();
	}

	/**
	 * @deprecated
	 */
	reset() {
		this.init();
	}

	addColorToInput(input: HTMLElement, happyInput: HTMLElement, classString: string) {
		if (input.classList.contains(classString)) {
			happyInput.classList.add(classString);
		}

		classString = `${classString}-border`;

		if (input.classList.contains(classString)) {
			happyInput.classList.add(classString);
		}
	}

	// i... you know what, no, let "thinkess" be "thinkess"
	addThinkessToInput(input: HTMLElement, happyInput: HTMLElement) {
		if (input.classList.contains("thin")) {
			happyInput.classList.add("thin");
		}
	}

	setNames(input: HTMLElement, happyInput: HTMLElement) {
		happyInput.setAttribute("data-name", input.getAttribute("name") ?? "");

		var value = input.getAttribute("value");

		if (value !== "undefined" && value !== null) {
			happyInput.setAttribute("data-value", input.getAttribute("value") ?? "");
		}
	}

	removeBySelector(selector: string) {
		document.querySelectorAll(selector).forEach(el => el.parentNode?.removeChild(el));
	}

	initRadio() {
		document.querySelectorAll<HTMLInputElement>("input[type=radio].happy").forEach(input => {
			/**
			 * Paste happy component into html
			 */
			input.insertAdjacentHTML("afterend", this.templates.radio);

			const happyInput = input.nextElementSibling;

			if (happyInput instanceof HTMLElement) {
				/**
				 * Add optional colors
				 */
				this.colors.forEach(color => {
					this.addColorToInput(input, happyInput, color);
					this.setNames(input, happyInput);
				});

				this.addThinkessToInput(input, happyInput);
			}

			/**
			 * Init state
			 */
			this.checkRadioState(input);

			/**
			 * Set aciton functionality for native change
			 */
			document.addEventListener("change", this.radioOnChange.bind(this));
		});
	}

	initCheckbox() {
		document.querySelectorAll<HTMLInputElement>("input[type=checkbox].happy").forEach(input => {
			/**
			 * Paste happy component into html
			 */
			input.insertAdjacentHTML("afterend", this.templates.checkbox);

			const happyInput = input.nextElementSibling;

			/**
			 * Add optional colors
			 */
			if (happyInput instanceof HTMLElement) {
				this.colors.forEach(color => {
					this.addColorToInput(input, happyInput, color);
					this.setNames(input, happyInput);
				});

				this.addThinkessToInput(input, happyInput);
			}

			/**
			 * Init state
			 */
			this.checkCheckboxState(input);

			/**
			 * Set action functionality for click || native change
			 */
			document.addEventListener("click", this.checkCheckboxStateOnClick.bind(this));
			document.addEventListener("change", this.checkCheckboxStateOnChange.bind(this));
		});
	}

	checkCheckboxStateOnClick(event: Event) {
		const target = event.target;

		// When target is SVGSVGElement (<svg>), return parentNode,
		// When target is a SVGGraphicsElement (<rect>,...), find <svg> and return it's parent node
		// otherwise return target itself.
		const happyInput =
			target instanceof SVGSVGElement
				? target.parentNode
				: target instanceof SVGGraphicsElement
					? target.closest("svg")?.parentNode
					: target;

		if (!(happyInput instanceof HTMLElement) || !happyInput.classList.contains("happy-checkbox")) {
			return;
		}

		event.preventDefault();

		const name = happyInput.getAttribute("data-name");
		const value = happyInput.getAttribute("data-value");

		const input = document.querySelector(
			`.happy-checkbox[data-name="${name}"]` + (!!value ? `[value="${value}"]` : "")
		);
		if (!(input instanceof HTMLInputElement)) return;

		const checked = happyInput.classList.contains("active");

		input.checked = !checked;
		checked ? happyInput.classList.remove("active") : happyInput.classList.add("active");
	}

	checkCheckboxStateOnChange({target}: Event) {
		if (!(target instanceof HTMLInputElement)) return;

		if (target.classList.contains("happy")) {
			this.checkCheckboxState(target);
		}
	}

	checkRadioState(input: HTMLInputElement) {
		if (!input.checked || !input.hasAttribute("name")) return;

		const name = input.getAttribute("name");
		const value = input.getAttribute("value");

		const element = document.querySelector(
			`.happy-checkbox[data-name="${name}"]` + (!!value ? `[data-value="${value}"]` : "")
		);

		if (element) {
			element.classList.add("active");
		}
	}

	checkCheckboxState(input: HTMLInputElement) {
		const name = input.getAttribute("name");
		if (!name) return;

		const value = input.getAttribute("value");
		const element = document.querySelector(
			`.happy-checkbox[data-name="${name}"]` + (!!value ? `[data-value="${value}"]` : "")
		);

		if (!element) return;

		input.checked ? element.classList.add("active") : element.classList.remove("active");
	}

	radioOnChange({target}: Event) {
		// Check whether target is <input>, is a happy input (.happy) & has the name attribute
		if (
			!(target instanceof HTMLInputElement) ||
			!target.classList.contains("happy") ||
			!target.hasAttribute("name")
		)
			return;

		const name = target.getAttribute("name")!;

		document
			.querySelectorAll(`.happy-radio[data-name="${name}"]`)
			.forEach(happyRadio => happyRadio.classList.remove("active"));

		this.checkRadioState(target);
	}
}
