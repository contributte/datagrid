var q = function() {
  this.name = "datagrid-confirm", this.initialize = function(t) {
    t.uiHandler.addEventListener("interaction", function(e) {
      var i = e.detail.element.dataset.datagridConfirm;
      typeof i < "u" && (confirm(i) || e.preventDefault());
    }.bind(this));
  };
};
naja.registerExtension(new q());
$(document).on("click", "[data-datagrid-confirm]:not(.ajax)", function(t) {
  if (!confirm($(t.target).closest("a").attr("data-datagrid-confirm")))
    return t.stopPropagation(), t.preventDefault();
});
var h = function(t, e) {
  var i = e.init, r = e.success, a = e.before, n = e.complete, d = e.interaction, l = function(s, g) {
    return this.name = g, this.initialize = function(c) {
      i && c.addEventListener("init", function(o) {
        i(o.detail.defaultOptions);
      }), r && c.addEventListener("success", function(o) {
        r(o.detail.payload, o.detail.options);
      }), c.uiHandler.addEventListener("interaction", function(o) {
        o.detail.options.nette = {
          el: $(o.detail.element)
        }, d && (d(o.detail.options) || o.preventDefault());
      }), a && c.addEventListener("before", function(o) {
        a(o.detail.request, o.detail.options) || o.preventDefault();
      }), n && c.addEventListener("complete", function(o) {
        n(o.detail.request, o.detail.options);
      });
    }, this;
  };
  naja.registerExtension(new l(null, t));
}, E = function(t) {
  var e = t.type || "GET", i = t.data || null;
  naja.makeRequest(e, t.url, i, {}).then(t.success).catch(t.error);
}, w = function(t) {
  return naja.uiHandler.submitForm(t.get(0));
}, y, S, M, T, k, C, L = [].indexOf || function(t) {
  for (var e = 0, i = this.length; e < i; e++)
    if (e in this && this[e] === t)
      return e;
  return -1;
};
$(document).on("change", "select[data-autosubmit-per-page]", function() {
  var t;
  return t = $(this).parent().find("input[type=submit]"), t.length === 0 && (t = $(this).parent().find("button[type=submit]")), t.click();
}).on("change", "select[data-autosubmit]", function() {
  return w($(this).closest("form").first());
}).on("change", "input[data-autosubmit][data-autosubmit-change]", function(t) {
  var e;
  return t.which || t.keyCode, clearTimeout(window.datagrid_autosubmit_timer), e = $(this), window.datagrid_autosubmit_timer = setTimeout(function(i) {
    return function() {
      return w(e.closest("form").first());
    };
  }(), 200);
}).on("keyup", "input[data-autosubmit]", function(t) {
  var e, i;
  if (i = t.which || t.keyCode || 0, !(i !== 13 && (i >= 9 && i <= 40 || i >= 112 && i <= 123)))
    return clearTimeout(window.datagrid_autosubmit_timer), e = $(this), window.datagrid_autosubmit_timer = setTimeout(function(r) {
      return function() {
        return w(e.closest("form").first());
      };
    }(), 200);
}).on("keydown", ".datagrid-inline-edit input", function(t) {
  var e;
  if (e = t.which || t.keyCode || 0, e === 13)
    return t.stopPropagation(), t.preventDefault(), $(this).closest("tr").find('.col-action-inline-edit [name="inline_edit[submit]"]').click();
});
$(document).on("keydown", "input[data-datagrid-manualsubmit]", function(t) {
  var e;
  if (e = t.which || t.keyCode || 0, e === 13)
    return t.stopPropagation(), t.preventDefault(), w($(this).closest("form").first());
});
C = function(t) {
  var e, i;
  if (L.call(t, i) >= 0)
    return t.path;
  for (i = [], e = t.target; e !== document.body && e !== null; )
    i.push(e), e = e.parentNode;
  return i;
};
M = function() {
  var t;
  return t = null, document.addEventListener("click", function(e) {
    var i, r, a, n, d, l, f, s, g, c, o, u, p, m, v, x, b, A, _;
    for (v = C(e), d = 0, u = v.length; d < u; d++)
      if (a = v[d], $(a).is(".col-checkbox") && t && e.shiftKey) {
        if (r = $(a).closest("tr"), c = t.closest("tr"), o = c.closest("tbody"), i = o.find("tr").toArray(), r.index() > c.index() ? _ = i.slice(c.index(), r.index()) : r.index() < c.index() && (_ = i.slice(r.index() + 1, c.index())), !_)
          return;
        for (s = 0, p = _.length; s < p; s++)
          A = _[s], f = $(A).find(".col-checkbox input[type=checkbox]")[0], f && (f.checked = !0, l = window.navigator.userAgent.indexOf("MSIE "), l ? (n = document.createEvent("Event"), n.initEvent("change", !0, !0)) : n = new Event("change", {
            bubbles: !0
          }), f.dispatchEvent(n));
      }
    for (x = C(e), b = [], g = 0, m = x.length; g < m; g++)
      a = x[g], $(a).is(".col-checkbox") ? b.push(t = $(a)) : b.push(void 0);
    return b;
  });
};
M();
document.addEventListener("change", function(t) {
  var e, i, r, a, n, d, l, f, s, g, c, o, u;
  if (n = t.target.getAttribute("data-check"), n && (i = document.querySelectorAll("input[data-check-all-" + n + "]:checked"), o = document.querySelector(".datagrid-" + n + ' select[name="group_action[group_action]"]'), e = document.querySelectorAll(".datagrid-" + n + ' .row-group-actions *[type="submit"]'), r = document.querySelector(".datagrid-" + n + " .datagrid-selected-rows-count"), i.length ? (e && e.forEach(function(p) {
    p.disabled = !1;
  }), o && (o.disabled = !1), u = document.querySelectorAll("input[data-check-all-" + n + "]").length, r && (r.innerHTML = i.length + "/" + u)) : (e && e.forEach(function(p) {
    p.disabled = !0;
  }), o && (o.disabled = !0, o.value = ""), r && (r.innerHTML = "")), l = window.navigator.userAgent.indexOf("MSIE "), l ? (a = document.createEvent("Event"), a.initEvent("change", !0, !0)) : a = new Event("change", {
    bubbles: !0
  }), o && o.dispatchEvent(a)), n = t.target.getAttribute("data-check-all"), n) {
    for (s = document.querySelectorAll("input[type=checkbox][data-check-all-" + n + "]"), c = [], d = 0, g = s.length; d < g; d++)
      f = s[d], f.checked = t.target.checked, l = window.navigator.userAgent.indexOf("MSIE "), l ? (a = document.createEvent("Event"), a.initEvent("change", !0, !0)) : a = new Event("change", {
        bubbles: !0
      }), c.push(f.dispatchEvent(a));
    return c;
  }
});
window.datagridSerializeUrl = function(t, e) {
  var i = [];
  for (var r in t)
    if (t.hasOwnProperty(r)) {
      var a = e ? e + "[" + r + "]" : r, n = t[r];
      if (n !== null && n !== "")
        if (typeof n == "object") {
          var d = window.datagridSerializeUrl(n, a);
          d && i.push(d);
        } else
          i.push(encodeURIComponent(a) + "=" + encodeURIComponent(n));
    }
  return i.join("&");
};
T = function() {
  if (!(typeof $.fn.sortable > "u"))
    return $(".datagrid [data-sortable]").sortable({
      handle: ".handle-sort",
      items: "tr",
      axis: "y",
      update: function(t, e) {
        var i, r, a, n, d, l, f;
        return l = e.item.closest("tr[data-id]"), a = l.data("id"), d = null, n = null, l.prev().length && (d = l.prev().data("id")), l.next().length && (n = l.next().data("id")), f = $(this).data("sortable-url"), r = {}, i = l.closest(".datagrid").find("tbody").attr("data-sortable-parent-path"), r[(i + "-item_id").replace(/^-/, "")] = a, d !== null && (r[(i + "-prev_id").replace(/^-/, "")] = d), n !== null && (r[(i + "-next_id").replace(/^-/, "")] = n), E({
          type: "GET",
          url: f,
          data: r,
          error: function(s, g, c) {
            return alert(s.statusText);
          }
        });
      },
      helper: function(t, e) {
        return e.children().each(function() {
          return $(this).width($(this).width());
        }), e;
      }
    });
};
$(function() {
  return T();
});
typeof k > "u" && (k = function() {
  if (!(typeof $(".datagrid-tree-item-children").sortable > "u"))
    return $(".datagrid-tree-item-children").sortable({
      handle: ".handle-sort",
      items: ".datagrid-tree-item:not(.datagrid-tree-header)",
      toleranceElement: "> .datagrid-tree-item-content",
      connectWith: ".datagrid-tree-item-children",
      update: function(t, e) {
        var i, r, a, n, d, l, f, s, g;
        if ($(".toggle-tree-to-delete").remove(), s = e.item.closest(".datagrid-tree-item[data-id]"), a = s.data("id"), f = null, n = null, l = null, s.prev().length && (f = s.prev().data("id")), s.next().length && (n = s.next().data("id")), d = s.parent().closest(".datagrid-tree-item"), d.length && (d.find(".datagrid-tree-item-children").first().css({
          display: "block"
        }), d.addClass("has-children"), l = d.data("id")), g = $(this).data("sortable-url"), !!g)
          return d.find("[data-toggle-tree]").first().removeClass("hidden"), i = s.closest(".datagrid-tree").attr("data-sortable-parent-path"), r = {}, r[(i + "-item_id").replace(/^-/, "")] = a, f !== null && (r[(i + "-prev_id").replace(/^-/, "")] = f), n !== null && (r[(i + "-next_id").replace(/^-/, "")] = n), r[(i + "-parent_id").replace(/^-/, "")] = l, E({
            type: "GET",
            url: g,
            data: r,
            error: function(c, o, u) {
              if (u !== "abort")
                return alert(c.statusText);
            }
          });
      },
      stop: function(t, e) {
        return $(".toggle-tree-to-delete").removeClass("toggle-tree-to-delete");
      },
      start: function(t, e) {
        var i;
        if (i = e.item.parent().closest(".datagrid-tree-item"), i.length && i.find(".datagrid-tree-item").length === 2)
          return i.find("[data-toggle-tree]").addClass("toggle-tree-to-delete");
      }
    });
});
$(function() {
  return k();
});
h("datagrid.happy", {
  success: function() {
    var t, e, i, r, a, n, d, l, f, s, g, c, o, u;
    for (window.happy && window.happy.reset(), d = $(".datagrid"), u = [], l = 0, c = d.length; l < c; l++) {
      for (n = d[l], r = n.classList, i = "", g = 0, o = r.length; g < o; g++)
        t = r[g], i = i + "." + t;
      e = document.querySelectorAll(i + " input[data-check]:checked"), e.length === 1 && e[0].getAttribute("name") === "toggle-all" ? (s = document.querySelector(i + " input[name=toggle-all]"), s ? (s.checked = !1, f = window.navigator.userAgent.indexOf("MSIE "), f ? (a = document.createEvent("Event"), a.initEvent("change", !0, !0)) : a = new Event("change", {
        bubbles: !0
      }), u.push(s.dispatchEvent(a))) : u.push(void 0)) : u.push(void 0);
    }
    return u;
  }
});
h("datagrid.sortable", {
  success: function() {
    return T();
  }
});
h("datagrid.forms", {
  success: function() {
    return $(".datagrid").find("form").each(function() {
      return window.Nette.initForm(this);
    });
  }
});
h("datagrid.url", {
  success: function(t) {
    var e, i, r, a;
    if (t._datagrid_url && window.history.replaceState && (e = window.location.protocol + "//" + window.location.host, i = window.location.pathname, r = window.datagridSerializeUrl(t.state).replace(/&+$/gm, ""), r ? a = e + i + "?" + r.replace(/\&*$/, "") : a = e + i, a += window.location.hash, window.location.href !== a))
      return window.history.replaceState({
        path: a
      }, "", a);
  }
});
h("datagrid.sort", {
  success: function(t) {
    var e, i, r, a;
    if (t._datagrid_sort) {
      r = t._datagrid_sort, a = [];
      for (i in r)
        e = r[i], a.push($("#datagrid-sort-" + i).attr("href", e));
      return a;
    }
  }
});
h("datargid.item_detail", {
  start: function(t, e) {
    var i, r, a;
    if (e.nette && e.nette.el.attr("data-toggle-detail")) {
      if (i = e.nette.el.attr("data-toggle-detail"), a = e.nette.el.attr("data-toggle-detail-grid-fullname"), r = $(".item-detail-" + a + "-id-" + i), r.hasClass("loaded"))
        return r.find(".item-detail-content").length ? (r.hasClass("toggled") ? r.find(".item-detail-content").slideToggle("fast", function(n) {
          return function() {
            return r.toggleClass("toggled");
          };
        }()) : (r.toggleClass("toggled"), r.find(".item-detail-content").slideToggle("fast")), !1) : (r.removeClass("toggled"), !0);
      r.addClass("loaded");
    }
    return !0;
  },
  success: function(t) {
    var e, i, r;
    if (t._datagrid_toggle_detail && t._datagrid_name)
      return e = t._datagrid_toggle_detail, r = t._datagrid_name, i = $(".item-detail-" + r + "-id-" + e), i.toggleClass("toggled"), i.find(".item-detail-content").slideToggle("fast");
  }
});
h("datagrid.tree", {
  before: function(t, e) {
    var i;
    return e.nette && e.nette.el.attr("data-toggle-tree") && (e.nette.el.toggleClass("toggle-rotate"), i = e.nette.el.closest(".datagrid-tree-item").find(".datagrid-tree-item-children").first(), i.hasClass("loaded")) ? (i.slideToggle("fast"), !1) : !0;
  },
  success: function(t) {
    var e, i, r, a, n, d, l;
    if (t._datagrid_tree) {
      r = t._datagrid_tree, e = $('.datagrid-tree-item[data-id="' + r + '"]').find(".datagrid-tree-item-children").first(), e.addClass("loaded"), n = t.snippets;
      for (a in n)
        d = n[a], i = $(d), l = $('<div class="datagrid-tree-item" id="' + a + '">'), l.attr("data-id", i.attr("data-id")), l.append(i), i.data("has-children") && l.addClass("has-children"), e.append(l);
      e.addClass("loaded"), e.slideToggle("fast"), naja.load();
    }
    return k();
  }
});
$(document).on("click", "[data-datagrid-editable-url]", function(t) {
  var e, i, r, a, n, d, l, f, s, g, c, o;
  if (a = $(this), t.target.tagName.toLowerCase() !== "a" && !a.hasClass("datagrid-inline-edit") && !a.hasClass("editing")) {
    a.addClass("editing"), n = a.html().trim().replace("<br>", `
`), a.attr("data-datagrid-editable-value") ? o = a.data("datagrid-editable-value") : o = n, a.data("originalValue", n), a.data("valueToEdit", o), a.data("datagrid-editable-type") === "textarea" ? (s = $("<textarea>" + o + "</textarea>"), f = parseInt(a.css("padding").replace(/[^-\d\.]/g, ""), 10), d = a.outerHeight(), g = Math.round(parseFloat(a.css("line-height"))), l = (d - 2 * f) / g, s.attr("rows", Math.round(l))) : a.data("datagrid-editable-type") === "select" ? (s = $(a.data("datagrid-editable-element")), s.find("option[value='" + o + "']").prop("selected", !0)) : (s = $('<input type="' + a.data("datagrid-editable-type") + '">'), s.val(o)), r = a.data("datagrid-editable-attrs");
    for (e in r)
      i = r[e], s.attr(e, i);
    return a.removeClass("edited"), a.html(s), c = function(u, p) {
      var m;
      return m = p.val(), m !== u.data("valueToEdit") ? E({
        url: u.data("datagrid-editable-url"),
        data: {
          value: m
        },
        type: "POST",
        success: function(v) {
          return u.data("datagrid-editable-type") === "select" ? u.html(s.find("option[value='" + m + "']").html()) : (v._datagrid_editable_new_value && (m = v._datagrid_editable_new_value), u.html(m)), u.addClass("edited");
        },
        error: function() {
          return u.html(u.data("originalValue")), u.addClass("edited-error");
        }
      }) : u.html(u.data("originalValue")), setTimeout(function() {
        return u.removeClass("editing");
      }, 1200);
    }, a.find("input,textarea,select").focus().on("blur", function() {
      return c(a, $(this));
    }).on("keydown", function(u) {
      if (a.data("datagrid-editable-type") !== "textarea" && u.which === 13)
        return u.stopPropagation(), u.preventDefault(), c(a, $(this));
      if (u.which === 27)
        return u.stopPropagation(), u.preventDefault(), a.removeClass("editing"), a.html(a.data("originalValue"));
    }), a.find("select").on("change", function() {
      return c(a, $(this));
    });
  }
});
h("datagrid.after_inline_edit", {
  success: function(t) {
    var e = $(".datagrid-" + t._datagrid_name);
    if (t._datagrid_inline_edited)
      return e.find("tr[data-id=" + t._datagrid_inline_edited + "] > td").addClass("edited"), e.find(".datagrid-inline-edit-trigger").removeClass("hidden");
    if (t._datagrid_inline_edit_cancel)
      return e.find(".datagrid-inline-edit-trigger").removeClass("hidden");
  }
});
$(document).on("mouseup", "[data-datagrid-cancel-inline-add]", function(t) {
  var e = t.which || t.keyCode || 0;
  if (e === 1)
    return t.stopPropagation(), t.preventDefault(), $(".datagrid-row-inline-add").addClass("datagrid-row-inline-add-hidden");
});
h("datagrid-toggle-inline-add", {
  success: function(t) {
    var e = $(".datagrid-" + t._datagrid_name);
    if (t._datagrid_inline_adding) {
      var i = e.find(".datagrid-row-inline-add");
      i.hasClass("datagrid-row-inline-add-hidden") && i.removeClass("datagrid-row-inline-add-hidden"), i.find("input:not([readonly]),textarea:not([readonly])").first().focus();
    }
  }
});
y = function() {
  var t = $(".selectpicker").first();
  if ($.fn.selectpicker)
    return $.fn.selectpicker.defaults = {
      countSelectedText: t.data("i18n-selected"),
      iconBase: "",
      tickIcon: t.data("selected-icon-check")
    };
};
$(function() {
  return y();
});
S = function() {
  var t;
  if (!!$.fn.selectpicker)
    return t = $("[data-datagrid-multiselect-id]"), t.each(function() {
      var e;
      if ($(this).hasClass("selectpicker"))
        return $(this).removeAttr("id"), e = $(this).data("datagrid-multiselect-id"), $(this).on("loaded.bs.select", function(i) {
          return $(this).parent().attr("style", "display:none;"), $(this).parent().find(".hidden").removeClass("hidden").addClass("btn-default btn-secondary");
        }), $(this).on("rendered.bs.select", function(i) {
          return $(this).parent().attr("id", e);
        });
    });
};
$(function() {
  return S();
});
h("datagrid.fitlerMultiSelect", {
  success: function() {
    if (y(), $.fn.selectpicker)
      return $(".selectpicker").selectpicker({
        iconBase: "fa"
      });
  }
});
h("datagrid.groupActionMultiSelect", {
  success: function() {
    return S();
  }
});
h("datagrid.inline-editing", {
  success: function(t) {
    var e;
    if (t._datagrid_inline_editing)
      return e = $(".datagrid-" + t._datagrid_name), e.find(".datagrid-inline-edit-trigger").addClass("hidden");
  }
});
h("datagrid.redraw-item", {
  success: function(t) {
    var e;
    if (t._datagrid_redraw_item_class)
      return e = $("tr[data-id=" + t._datagrid_redraw_item_id + "]"), e.attr("class", t._datagrid_redraw_item_class);
  }
});
h("datagrid.reset-filter-by-column", {
  success: function(t) {
    var e, i, r, a, n, d;
    if (!!t._datagrid_name && (e = $(".datagrid-" + t._datagrid_name), e.find("[data-datagrid-reset-filter-by-column]").addClass("hidden"), t.non_empty_filters && t.non_empty_filters.length)) {
      for (d = t.non_empty_filters, r = 0, n = d.length; r < n; r++)
        a = d[r], e.find("[data-datagrid-reset-filter-by-column=" + a + "]").removeClass("hidden");
      return i = e.find(".reset-filter").attr("href"), e.find("[data-datagrid-reset-filter-by-column]").each(function() {
        var l;
        return a = $(this).attr("data-datagrid-reset-filter-by-column"), l = i.replace("do=" + t._datagrid_name + "-resetFilter", "do=" + t._datagrid_name + "-resetColumnFilter"), l += "&" + t._datagrid_name + "-key=" + a, $(this).attr("href", l);
      });
    }
  }
});
function D() {
  return "magic2";
}
export {
  D as bar
};
