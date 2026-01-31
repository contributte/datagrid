import { DatagridPlugin } from "../../types";
import { Datagrid } from "../..";

export class TreeViewPlugin implements DatagridPlugin {
	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener("success", ({detail: {payload}}) => {
			if (!payload._datagrid_tree) return;

			const id = payload._datagrid_tree;
			const rowBlock = document.querySelector<HTMLElement>(`.datagrid-tree-item[data-id="${id}"]`);
			const childrenBlock = rowBlock?.querySelector<HTMLElement>('.datagrid-tree-item-children');

			if (!childrenBlock) return;

			const isExpanded = childrenBlock.classList.contains('showed');
			const chevron = rowBlock?.querySelector<HTMLAnchorElement>('a.chevron');

			if (isExpanded) {
				childrenBlock.innerHTML = '';
				childrenBlock.classList.remove('showed');
				if (chevron) chevron.style.transform = "rotate(0deg)";
				return;
			}

			childrenBlock.classList.add('showed');
			if (chevron) chevron.style.transform = "rotate(90deg)";

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
		})
		return true;
	}
}
