import { Datepicker as t } from "vanillajs-datepicker";
import "../../node_modules/vanillajs-datepicker/sass/datagrid.mjs";
document.onreadystatechange = function() {
  if (document.readyState === "interactive" || document.readyState === "complete") {
    const e = document.querySelector('input[data-exec="datepicker"]');
    e != null && new t(e, {});
  } else
    throw "document.readyState unsupported value";
};
