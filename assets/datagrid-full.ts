import naja from "naja";
import { default as netteForms } from "nette-forms";
import Select from "tom-select";
import Sortable from "sortablejs";
import { Datepicker } from "vanillajs-datepicker";
import {
	AutosubmitPlugin,
	CheckboxPlugin,
	ConfirmPlugin,
	createDatagrids,
	DatepickerPlugin,
	EditablePlugin,
	InlinePlugin,
	ItemDetailPlugin,
	NetteFormsPlugin,
	SelectpickerPlugin,
	SortableJS,
	SortablePlugin,
	TomSelect,
	TreeViewPlugin,
	VanillaDatepicker,
} from "."
import { NajaAjax } from "./ajax";
import { Dropdown } from "bootstrap";

// Datagrid + UI
document.addEventListener("DOMContentLoaded", () => {
	// Initialize dropdowns
	Array.from(document.querySelectorAll('.dropdown'))
		.forEach(el => new Dropdown(el))

	// Initialize Naja (nette ajax)
	naja.defaultOptions.history = false;
	naja.formsHandler.netteForms = netteForms;
	naja.initialize();

	// Initialize datagrids
	createDatagrids(new NajaAjax(naja), {
		datagrid: {
			plugins: [
				new AutosubmitPlugin(),
				new CheckboxPlugin(),
				new ConfirmPlugin(),
				new EditablePlugin(),
				new InlinePlugin(),
				new ItemDetailPlugin(),
				new NetteFormsPlugin(netteForms),
				new SortablePlugin(new SortableJS(Sortable)),
				new DatepickerPlugin(new VanillaDatepicker(Datepicker, { buttonClass: 'btn' })),
				new SelectpickerPlugin(new TomSelect(Select)),
				new TreeViewPlugin(),
			],
		},
	});
});
