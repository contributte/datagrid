import naja from "naja";
import {NajaAjax} from "../../assets/ajax/naja";
import {AutosubmitPlugin} from "../../assets/plugins/features/autosubmit";

const ajax = new NajaAjax(naja);
ajax.onInit();
naja.initialize();
const plugin = new AutosubmitPlugin(300);
plugin.onDatagridInit({el: document.querySelector("#grid")!, ajax} as any);
Object.assign(window, {naja, ajax, plugin});
