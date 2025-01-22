import { DatagridPlugin } from "../../types";
import { Datagrid } from "../..";

export class TreeViewPlugin implements DatagridPlugin {
	onDatagridInit(datagrid: Datagrid): boolean {
		datagrid.ajax.addEventListener("before", (e) => {
		})

		datagrid.ajax.addEventListener("success", ({detail: {payload}}) => {
			if (payload._datagrid_tree) {
				const id = payload._datagrid_tree;
				const rowBlock = document.querySelector(`.datagrid-tree-item[data-id="${id}"]`);
				const childrenBlock = document.querySelector(`.datagrid-tree-item[data-id="${id}"] .datagrid-tree-item-children`);
				if (childrenBlock) {
					if (childrenBlock.classList.contains('showed')) {
						childrenBlock.innerHTML = '';
						childrenBlock.classList.remove('showed');
						rowBlock.querySelector(`a.chevron`).style.transform = "rotate(0deg)";
						return;
					}

					childrenBlock.classList.add('showed');
					rowBlock.querySelector(`a.chevron`).style.transform = "rotate(90deg)";
					const snippets = payload.snippets;
					for (const snippetName in snippets) {
						const snippet = snippets[snippetName];
						const snippetDocEl = new DOMParser().parseFromString(snippet, "text/html")
							.querySelector("[data-id]");

						const id = snippetDocEl?.getAttribute("data-id") ?? '';
						const hasChildren = snippetDocEl?.hasAttribute("data-has-children") ?? false;

						const template = `\n<div class="datagrid-tree-item" class='${hasChildren ? 'has-children' : ''}' id="${snippetName}" data-id="${id}">${snippet}</div>`;

						childrenBlock.innerHTML = template;
					}
					//children_block.addClass('loaded');
					//children_block.slideToggle('fast');
				}
			}
		})
		return true;
	}
}
