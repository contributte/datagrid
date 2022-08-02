var s = function() {
  this.name = "datagrid-spinners", this.initialize = function(l) {
    l.uiHandler.addEventListener("interaction", function(i) {
      this.element = i.detail.element;
    }.bind(this)), l.addEventListener("before", function(i) {
      if (i.detail.options.nette) {
        let e = this.element, t = document.createElement("div");
        t.classList.add("ublaboo-spinner"), t.classList.add("ublaboo-spinner-small"), t.append(
          document.createElement("i"),
          document.createElement("i"),
          document.createElement("i"),
          document.createElement("i")
        );
        let n = function(o) {
          let a = o.closest(".row-grid-bottom").querySelector(".col-per-page");
          a && a.prepend(t);
        };
        if (e.isEqualNode(document.querySelector('.datagrid [name="group_action[submit]"]')))
          e.after(t);
        else if ("toggleDetail" in e.dataset) {
          let o = i.detail.options.nette.el.attr("data-toggle-detail"), a = i.detail.options.nette.el.attr("data-toggle-detail-grid-fullname");
          $(".item-detail-" + a + "-id-" + o).hasClass("loaded") || e.classList.add("ublaboo-spinner-icon");
        } else
          (e.classList.contains("datagrid-paginator-button") || e.isEqualNode(document.querySelector(".datagrid .datagrid-per-page-submit")) || e.isEqualNode(document.querySelector(".datagrid .reset-filter"))) && n(e);
      }
    }.bind(this)), l.addEventListener("complete", function(i) {
      if (typeof i.detail.response < "u") {
        const e = document.getElementsByClassName("ublaboo-spinner");
        for (; e.length > 0; )
          e[0].remove();
        const t = document.getElementsByClassName("ublaboo-spinner-icon");
        for (let n = 0; n < t.length; n++)
          t[n].classList.remove("ublaboo-spinner-icon");
      }
    });
  };
};
naja.registerExtension(new s());
