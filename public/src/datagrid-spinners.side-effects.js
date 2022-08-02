var DataGridSpinnersExtension = function() {
	this.name = "datagrid-spinners";

	this.initialize = function (naja) {
		naja.uiHandler.addEventListener("interaction", function (event) {
			this.element = event.detail.element;
		}.bind(this));

		naja.addEventListener("before", function (event) {
			if (event.detail.options.nette) {
				let el = this.element;
				let template = document.createElement("div");

				template.classList.add("ublaboo-spinner");
				template.classList.add("ublaboo-spinner-small");
				template.append(
					document.createElement("i"),
					document.createElement("i"),
					document.createElement("i"),
					document.createElement("i")
				);

				let bottomSpin = function(el) {
					let perPage = el.closest(".row-grid-bottom").querySelector(".col-per-page");
					if (perPage) {
						perPage.prepend(template);
					}
				};

				if (el.isEqualNode(document.querySelector('.datagrid [name="group_action[submit]"]'))) {
					el.after(template);
				} else if ("toggleDetail" in el.dataset) {
					let id = event.detail.options.nette.el.attr("data-toggle-detail");
					let grid_fullname = event.detail.options.nette.el.attr("data-toggle-detail-grid-fullname");
					let row_detail = $(".item-detail-" + grid_fullname + "-id-" + id);

					if (!row_detail.hasClass("loaded")) {
						el.classList.add("ublaboo-spinner-icon");
					}
				} else if (el.classList.contains("datagrid-paginator-button")) {
					bottomSpin(el);
				} else if (el.isEqualNode(document.querySelector(".datagrid .datagrid-per-page-submit"))) {
					bottomSpin(el);
				} else if (el.isEqualNode(document.querySelector(".datagrid .reset-filter"))) {
					bottomSpin(el);
				}
			}
		}.bind(this));

		naja.addEventListener("complete", function (event) {
			if (typeof event.detail.response != "undefined") {
				const spinners = document.getElementsByClassName("ublaboo-spinner");
				while(spinners.length > 0){
					spinners[0].remove();
				}

				const spinnerIcon = document.getElementsByClassName("ublaboo-spinner-icon");
				for (let i = 0; i < spinnerIcon.length; i++) {
					spinnerIcon[i].classList.remove("ublaboo-spinner-icon");
				}
			}
		});
	}
}

naja.registerExtension(new DataGridSpinnersExtension());
