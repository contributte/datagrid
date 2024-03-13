import { Datagrid } from "./datagrid";
import { ExtendedWindow } from "./types";

export function isPromise<T = any>(p: any): p is Promise<T> {
	return typeof p === "object" && typeof p.then === "function";
}

export function isInKeyRange(e: KeyboardEvent, min: number, max: number): boolean {
	const code = e.key.length === 1 ? e.key.charCodeAt(0) : 0;
	return code >= min && code <= max;
}

export function isEnter(e: KeyboardEvent): boolean {
	return e.key === "Enter";
}

export function isEsc(e: KeyboardEvent): boolean {
	return e.key === "Escape";
}

export function isFunctionKey(e: KeyboardEvent): boolean {
	return e.key.length === 2 && e.key.startsWith("F");
}

export function window(): ExtendedWindow {
	return (window ?? {}) as unknown as ExtendedWindow;
}

export function slideDown(element: HTMLElement, cb?: (nextStateShown: boolean) => unknown) {
	element.style.height = 'auto';

	let height = element.clientHeight + "px";

	element.style.height = '0px';

	setTimeout(function () {
		element.style.height = height;
		cb?.(true);
	}, 0);
}

export function slideUp(element: HTMLElement, cb?: (nextStateShown: boolean) => unknown) {
	element.style.height = '0px';

	setTimeout(() => {
		cb?.(false);
	}, 250); // TODO
}

export function slideToggle(element: HTMLElement, isVisible: boolean, cb?: (nextStateShown: boolean) => unknown) {
	if (!isVisible) {
		slideDown(element, cb);
	} else {
		slideUp(element, cb);
	}
}

export function attachSlideToggle(element: HTMLElement, control: HTMLElement, cb?: (nextStateShown: boolean) => unknown) {
	if (!control.classList.contains("datagrid--slide-toggle")) {
		let sliding = false;
		control.classList.add("datagrid--slide-toggle");

		slideDown(element, cb);

		control.addEventListener('click', () => {
			if (sliding) return;
			sliding = true;
			slideToggle(element, control.classList.contains('is-active'), (active) => {
				sliding = false
				if (active) {
					control.classList.add("is-active");
				} else {
					control.classList.remove("is-active");
				}
			});
		});
	}
}

export function qs(params: Record<string, any | any[]>, prefix: string = ""): string {
	const encodedParams = [];

	for (const _key in params) {
		const value = params[_key];
		// Cannot do !value as that would also exclude valid negative values such as 0 or false
		if (value === null || value === undefined) continue;

		const key = prefix ? `${prefix}[${_key}]` : _key;

		// Skip empty strings
		if (typeof value === "string" && value.trim().length < 1) continue;

		if (typeof value === "object") {
			const nestedParams = qs(value, key);
			// Don't include if object is empty
			if (nestedParams.length >= 1) {
				encodedParams.push(nestedParams);
			}

			continue;
		}

		encodedParams.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`)
	}

	return encodedParams.join("&").replace(/&+$/gm, "").replace(/&*$/, "");
}

export function calculateCellLines(el: HTMLElement) {
	const cellPadding = el.style.padding ? parseInt(el.style.padding.replace(/[^-\d\.]/g, ""), 10) : 0;
	const cellHeight = el.getBoundingClientRect().height;
	const lineHeight = Math.round(parseFloat(el.style.lineHeight ?? "0"));
	const cellLines = Math.round((cellHeight - 2 * cellPadding) / lineHeight);

	return cellLines;
}

// A little better debounce ;)
export function debounce<TArgs, TFun extends (...args: TArgs[]) => unknown | Promise<unknown>>(
	fn: TFun,
	slowdown: number = 200
): (...args: TArgs[]) => void {
	let timeout: ReturnType<typeof setTimeout> | null = null;
	let blockedByPromise: boolean = false;

	return (...args) => {
		if (blockedByPromise) return;

		timeout && clearTimeout(timeout);
		timeout = setTimeout(() => {
			const result = fn(...args);

			if (isPromise(result)) {
				blockedByPromise = true;
				result.finally(() => {
					blockedByPromise = false;
				});
			}
		}, slowdown);
	};
}

export function defaultDatagridNameResolver(this: Datagrid, datagrid: HTMLElement) {
	// This attribute is not present by default, though if you're going to use this library
	// it's recommended to add it, because when not present, the fallback way is to parse the datagrid-<name> class,
	// which is definitely far from reliable. Alternatively (mainly in case of a custom datagrid class),
	// you can pass your own resolveDatagridName function to the option.
	const attrName = datagrid.getAttribute("data-datagrid-name");
	if (attrName) return attrName;

	console.warn(
		"Deprecated name resolution for datagrid",
		datagrid,
		": Please add a data-datagrid-name attribute instead!\n" +
		"Currently, the Datagrid library relies on matching the name from the 'datagrid-[name]' class, which is unreliable " +
		"and may cause bugs if the default class names are not used (eg. if you add a datagrid-xx class, or change the name class completely!)\n" +
		"Alternatively, you can customize the name resolution with the `resolveDatagridName` option. See TBD for more info." // TODO
	);

	const classes = datagrid.classList.value.split(" ");

	// Returns the first datagrid-XXX match
	for (const className of classes) {
		if (!className.startsWith("datagrid-")) continue;

		const [, ...split] = className.split("-");
		const name = split.join("-");

		// In case nothing actually follows the prefix (className = "datagrid-")
		if (name.length < 1) {
			console.error(`Failed to resolve datagrid name - ambigious class name '${className}'`);
			return null;
		}

		return name;
	}

	return null;
}
