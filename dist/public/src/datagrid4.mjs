var t = function() {
  var e = document.querySelector(".datagrid");
  if (e !== null)
    return naja.makeRequest("GET", e.getAttribute("data-refresh-state"), null, {
      history: "replace"
    });
};
document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", t) : t();
function a() {
  return "magic";
}
export {
  a as foo
};
