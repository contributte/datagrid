import { EventDetail, EventListener, EventMap } from ".";
import { Datagrid } from "..";

export interface BaseRequestParams {
	method: "GET" | "HEAD" | "POST" | "PUT" | "DELETE" | "CONNECT" | "OPTIONS" | "TRACE" | "PATCH" | string;
	url: string;
}

export interface RequestParams<D = any> extends BaseRequestParams {
	data: D;
}

export interface DatagridPayload {
	_datagrid_name?: string;
	_datagrid_toggle_detail?: string
	_datagrid_inline_editing?: boolean;
	_datagrid_inline_adding?: boolean;
	_datagrid_inline_edited?: boolean;
	_datagrid_inline_edit_cancel?: boolean;
	_datagrid_url?: boolean;
	_datagrid_sort?: Record<string, string>;
	_datagrid_tree?: string;
	_datagrid_editable_new_value?: string;
	_datagrid_redraw_item_id?: string;
	_datagrid_redraw_item_class?: string;
	_datagrid_init?: boolean;
	non_empty_filters?: string[];
}

export interface DatagridState {
	"grid-page": number | null,
	"grid-perPage": number,
	// TODO
	"grid-sort": any | null,
	"grid-filter": any | null
}

export type Payload<P = DatagridPayload, S = DatagridState> = P & {
	snippets?: Record<string, string>;
	redirect?: string;
	state: S;
};

export interface Response {
	headers: Record<string, string | (string[])> | Headers;
	status: number;
}

export interface BeforeEventDetail<D = {}> {
	params: RequestParams<D>;
}

export interface InteractEventDetail<E extends HTMLElement = HTMLElement> {
	element: E;
}

export interface SuccessEventDetail<P = DatagridPayload, R extends Response = Response> {
	params: BaseRequestParams;
	payload: Payload<P>;
	response: Response;
}

export interface CompleteEventDetail<P = DatagridPayload, R extends Response = Response> {
	params: BaseRequestParams;
	payload: Payload<P>;
	response: Response;
}

export interface ErrorEventDetail<E extends Error = Error, R extends Response = Response> {
	params: BaseRequestParams;
	response?: Response;
	error?: E;
}

export interface AjaxEventMap extends EventMap {
	before: CustomEvent<BeforeEventDetail>;
	interact: CustomEvent<InteractEventDetail>;
	snippetUpdate: CustomEvent<InteractEventDetail>;
	success: CustomEvent<SuccessEventDetail>;
	complete: CustomEvent<CompleteEventDetail>;
	error: CustomEvent<ErrorEventDetail>;
}

export interface Ajax<C extends EventTarget = EventTarget, G extends Datagrid = Datagrid> extends EventTarget {
	client: C;

	/**
	 * Initialization of the Ajax instance, called in createDatagrids().
	 * @return this
	 */
	onInit(): this;

	/**
	 * Initializes a Datagrid instance.
	 * @param grid The Datagrid instance
	 */
	onDatagridInit?(grid: G): void;

	/**
	 * Sends a request to the server.
	 */
	request<D = {}, P = DatagridPayload>(args: RequestParams<D>): Promise<P>;

	/**
	 * Submits a form
	 */
	submitForm<E extends HTMLFormElement = HTMLFormElement, P = Payload>(element: E): Promise<P>;

	/**
	 * Shortcut for dispatchEvent
	 * @internal
	 */
	dispatch<K extends string, M extends AjaxEventMap = AjaxEventMap>(
		type: K,
		detail: K extends keyof M ? EventDetail<M[K]> : any,
		options?: boolean
	): boolean;

	/**
	 * Note: For events dispatched directly from the underlying client, {@see Ajax.client}}
	 **/
	addEventListener<K extends keyof M, M extends AjaxEventMap = AjaxEventMap>(
		type: K,
		listener: EventListener<this, M[K]>,
		options?: boolean | AddEventListenerOptions
	): void;

	/**
	 * Note: For events dispatched directly from the underlying client, {@see Ajax.client}}
	 **/
	removeEventListener<K extends keyof M, M extends AjaxEventMap = AjaxEventMap>(
		type: K,
		listener: EventListener<this, M[K]>,
		options?: boolean | AddEventListenerOptions
	): void;

	/**
	 * @internal
	 */
	dispatchEvent<K extends string, M extends AjaxEventMap = AjaxEventMap>(
		event: K extends keyof M ? M[K] : CustomEvent
	): boolean;
}
