import { Happy } from "../integrations";
import TomSelect from "tom-select";

export interface Nette {
	initForm: (form: HTMLFormElement) => void;
}

export type Constructor<T> = new (...args: any[]) => T;

export type KeysOf<T, TVal = any> = { [P in keyof T]: TVal; }

export interface ExtendedWindow extends Window {
	jQuery?: any;
	Nette?: Nette;
	TomSelect?: Constructor<TomSelect>;
	happy?: Happy;
}

// https://github.com/naja-js/naja/blob/384d298a9199bf778985d1bcf5747fe8de305b22/src/utils.ts
type EventListenerFunction<ET extends EventTarget, E extends Event> = (
	this: ET,
	event: E
) => boolean | void | Promise<void>;

interface EventListenerObject<E extends Event> {
	handleEvent(event: E): void | Promise<void>;
}

export type EventListener<ET extends EventTarget, E extends Event> =
	| EventListenerFunction<ET, E>
	| EventListenerObject<E>
	| null;

export type EventDetail<E = CustomEvent> = E extends CustomEvent<infer D> ? D : never;

export interface EventMap extends Record<string, CustomEvent> {
}

export * from "./datagrid";
export * from "./integrations";
export * from "./ajax";
