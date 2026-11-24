import naja from "naja";
import {NajaAjax} from "../../assets/ajax/naja";
import {EditablePlugin} from "../../assets/plugins/features/editable";

const ajax = new NajaAjax(naja);
ajax.onInit();
naja.initialize();
const plugin = new EditablePlugin();
plugin.onDatagridInit({el: document.querySelector("#grid")!, ajax} as any);
Object.assign(window, {naja, ajax, plugin});
