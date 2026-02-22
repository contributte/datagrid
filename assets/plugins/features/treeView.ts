import { DatagridPlugin } from "../../types";
import { Datagrid } from "../..";

export class TreeViewPlugin implements DatagridPlugin {
	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener("interact", (event) => {
			const element = event.detail.element;
			if (!element.classList.contains('chevron')) return;

			const rowBlock = element.closest<HTMLElement>('.datagrid-tree-item');
			const childrenBlock = rowBlock?.querySelector<HTMLElement>('.datagrid-tree-item-children');

			if (!childrenBlock) return;

			if (childrenBlock.classList.contains('showed')) {
				childrenBlock.innerHTML = '';
				childrenBlock.classList.remove('showed');
				element.classList.remove('is-expanded');
				event.preventDefault();
				return;
			}

			element.classList.add('is-loading');
		});

		datagrid.ajax.addEventListener("success", ({detail: {payload}}) => {
			if (!payload._datagrid_tree) return;

			const id = payload._datagrid_tree;
			const rowBlock = document.querySelector<HTMLElement>(`.datagrid-tree-item[data-id="${id}"]`);
			const childrenBlock = rowBlock?.querySelector<HTMLElement>('.datagrid-tree-item-children');

			if (!childrenBlock) return;

			const chevron = rowBlock?.querySelector<HTMLElement>('a.chevron');

			childrenBlock.classList.add('showed');

			if (chevron) {
				chevron.classList.remove('is-loading');
				chevron.classList.add('is-expanded');
			}

			const childrenHtml: string[] = [];
			for (const snippetName in payload.snippets) {
				const snippet = payload.snippets[snippetName];
				const snippetDocEl = new DOMParser().parseFromString(snippet, "text/html")
					.querySelector("[data-id]");

				const snippetId = snippetDocEl?.getAttribute("data-id") ?? '';
				const hasChildren = snippetDocEl?.hasAttribute("data-has-children") ?? false;
				const classNames = hasChildren ? 'datagrid-tree-item has-children' : 'datagrid-tree-item';

				childrenHtml.push(`\n<div class="${classNames}" id="${snippetName}" data-id="${snippetId}">${snippet}</div>`);
			}

			childrenBlock.innerHTML = childrenHtml.join('');
		});

		datagrid.ajax.addEventListener("error", () => {
			document.querySelectorAll<HTMLElement>('a.chevron.is-loading').forEach((chevron) => {
				chevron.classList.remove('is-loading');
			});
		});

		return true;
	}
}
