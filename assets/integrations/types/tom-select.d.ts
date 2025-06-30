declare module 'tom-select/dist/types/types' {
	export type RecursivePartial<T> = {
		[P in keyof T]?: RecursivePartial<T[P]>;
	};
	export type TomInput = HTMLInputElement | HTMLSelectElement;
	export interface TomSettings { /* minimal needed interface */ }
}
