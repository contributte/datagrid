import { Selectpicker } from "../types";
import { window } from "../utils";

export class BootstrapSelect implements Selectpicker {
	initSelectpickers(elements: HTMLElement[]): void {
		if (window().jQuery) {
			const $ = window().jQuery;
			if ($?.fn.selectpicker) {
				$.fn.selectpicker.defaults = {
					countSelectedText: elements[0].getAttribute("i18n-selected") ?? "",
					iconBase: "fa",
					tickIcon: elements[0].getAttribute("selected-icon-check") ?? "fa fa-check",
				};

				elements.forEach(element =>
					$(element)
						.removeClass("form-select form-select-sm")
						.addClass("form-control form-control-sm")
						.selectpicker("destroy")
						.selectpicker({})
				);

				Array.from(elements)
					.filter(element => element.hasAttribute("data-datagrid-multiselect-id"))
					.forEach(element => {
						const $picker = $(element);
						const $parent = $picker.parent();

						$picker.removeAttr("id");
						const id = element.getAttribute("data-datagrid-multiselect-id");

						$picker.on("loaded.bs.select", () => {
							$parent.attr("style", "display: none;");
							$parent.find(".hidden").removeClass("hidden").addClass("btn-default btn-secondary");
						});

						$picker.on("rendered.bs.select", () => $parent.attr("id", id));
					});
			}
		}
	}

}
