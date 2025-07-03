# Assets

- [Architecture](#architecture)
  - [Plugins](#plugins)
- [Installation](#installation)
  - [CDN](#cdn)
  - [Bundler](#bundler)
- [Example](#example)

-----

# Architecture

The front-end part of the datagrid is completely rewritten in TypeScript, supports modern browsers and is fully customizable via plugins.

## Plugins

Datagrid has a lot of plugins. You can use them all or just some of them.

**Plugins (features)**

- AutosubmitPlugin
  - Sends form on change
- CheckboxPlugin
  - Check/uncheck rows
- ConfirmPlugin
  - Confirmation dialog before action
- InlinePlugin (advanced)
  - Inline editing
- EditablePlugin (advanced)
  - Inline editing
- ItemDetailPlugin
  - Item detail view
- TreeViewPlugin (advanced)
  - Tree view

**Plugins (integrations)**

- DatepickerPlugin
  - Abstraction for datepickers
- NetteFormsPlugin
  - Abstraction for Nette Forms
- SelectpickerPlugin
  - Abstraction for selectpickers
- SortablePlugin
  - Abstraction for sorting

**Integrations**

- [SortableJS](https://sortablejs.github.io/Sortable/) (+SortablePlugin)
  - Implementation for sorting
- [TomSelect](https://tom-select.js.org/) (+SelectpickerPlugin)
  - Implementation of selectpicker
- [VanillaDatepicker](https://github.com/mymth/vanillajs-datepicker) (+DatepickerPlugin)
  - Implementation of datepicker

**Example**

```js
import {
	AutosubmitPlugin,
	CheckboxPlugin,
	ConfirmPlugin,
	createDatagrids,
	DatepickerPlugin,
	InlinePlugin,
	ItemDetailPlugin,
	NetteFormsPlugin,
	SelectpickerPlugin,
	SortableJS,
	SortablePlugin,
	TomSelect,
	TreeViewPlugin,
	VanillaDatepicker
} from "../../vendor/ublaboo/datagrid/assets"

document.addEventListener("DOMContentLoaded", () => {
	createDatagrids(new NajaAjax(naja), {
		datagrid: {
			plugins: [
				new AutosubmitPlugin(),
				new CheckboxPlugin(),
				new ConfirmPlugin(),
				new InlinePlugin(),
				new ItemDetailPlugin(),
				new NetteFormsPlugin(netteForms),
				new SortablePlugin(new SortableJS()),
				new DatepickerPlugin(new VanillaDatepicker({ buttonClass: 'btn' })),
				new SelectpickerPlugin(new TomSelect(Select)),
				new TreeViewPlugin(),
			],
		},
	});
});
```

# Installation

There are prepare JS/TS and CSS files for precise functionality.

You have 2 ways to use datagrid's assets:

2. Use CDN assets.
1. Use frontend bundler.

## CDN

Assets are available as NPM package [@contributte/datagrid](https://www.npmjs.com/package/@contributte/datagrid).

You can use CDN assets like this:

```latte
<!-- Datagrid (CDN) -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@contributte/datagrid@master/dist/datagrid-full.css">
<script defer src="https://cdn.jsdelivr.net/npm/@contributte/datagrid@master/dist/datagrid-full.js"></script>
```

By default, `datagrid-full.ts` and `datagrid-full.css` are bundled and accessible via CDN.

1. Take a look how [`datagrid-full.ts`](https://github.com/contributte/datagrid/blob/master/assets/datagrid-full.ts) looks.

It contains these plugins:

- AutosubmitPlugin
- CheckboxPlugin
- ConfirmPlugin
- InlinePlugin
- ItemDetailPlugin
- NetteFormsPlugin
- SortablePlugin
- DatepickerPlugin
- SelectpickerPlugin
- TreeViewPlugin

2. Take a look how [`datagrid-full.css`](https://github.com/contributte/datagrid/blob/master/assets/css/datagrid-full.css) looks.

> [!NOTE]
> CDN assets are not ideal for customization and optimization. Use bundler instead.

## Bundler

This example uses [Vite](https://vitejs.dev). You can see example of using bundler in [datagrid-skeleton](https://github.com/contributte/datagrid-skeleton).

You need to have these files in your project:

- [package.json](https://github.com/contributte/datagrid-skeleton/blob/master/package.json)
- [assets/js/main.js](https://github.com/contributte/datagrid-skeleton/blob/master/assets/js/main.js)
- [assets/css/main.css](https://github.com/contributte/datagrid-skeleton/blob/master/assets/css/main.css)
- [vite.config.mjs](https://github.com/contributte/datagrid-skeleton/blob/master/vite.config.mjs)

Follow these steps:

1. Install dependencies
2. Create `assets/js/main.js` and `assets/css/main.css`.
3. Create `vite.config.mjs`.
4. Run `npm run build`.
5. See `www` and `www/dist` directory.

  ```
  ➜ tree www
  www
  ├── dist
  │   ├── app.css
  │   ├── app.js
  │   ├── fa-brands-400.ttf
  │   ├── fa-brands-400.woff2
  │   ├── fa-regular-400.ttf
  │   ├── fa-regular-400.woff2
  │   ├── fa-solid-900.ttf
  │   ├── fa-solid-900.woff2
  │   ├── fa-v4compatibility.ttf
  │   ├── fa-v4compatibility.woff2
  │   └── favicon.ico
  └── index.php
  ```

6. Include `www/dist/datagrid.js` and `www/dist/datagrid.css` in your `@layout.latte`.

  ```latte
  <!-- Datagrid (bundled) -->
  <link rel="stylesheet" type="text/css" href="{$basePath}/dist/app.css">
  <script defer src="{$basePath}/dist/app.js"></script>
  ```

# Example

This example uses [Vite](https://vitejs.dev). You can see example of using bundler in [datagrid-skeleton](https://github.com/contributte/datagrid-skeleton).

<details>
<summary>package.json</summary>

```json
{
  "dependencies": {
    "@fortawesome/fontawesome-free": "^6.7.2",
    "bootstrap": "^5.3.6",
    "naja": "^2.6.1",
    "nette-forms": "^3.5.3",
    "sortablejs": "^1.15.6",
    "tom-select": "^2.4.3",
    "vanillajs-datepicker": "^1.3.4"
  },
  "devDependencies": {
    "@types/bootstrap-select": "^1.13.7",
    "@types/jquery": "^3.5.32",
    "@types/jqueryui": "^1.12.24",
    "@types/sortablejs": "^1.15.8",
    "@types/vanillajs-datepicker": "^1.3.5",
    "autoprefixer": "^10.4.21",
    "typescript": "^5.8.3",
    "vite": "^6.3.5"
  },
  "scripts": {
    "watch": "vite build --watch --mode=development",
    "build": "vite build --mode=production"
  }
}
```

</details>

<details>
<summary>assets/js/main.js</summary>

```js
import naja from "naja";
import netteForms from "nette-forms";
import {
	AutosubmitPlugin,
	CheckboxPlugin,
	ConfirmPlugin,
	createDatagrids,
	DatepickerPlugin,
	InlinePlugin,
	ItemDetailPlugin,
	NetteFormsPlugin,
	SelectpickerPlugin,
	SortableJS,
	SortablePlugin,
	TomSelect,
	TreeViewPlugin,
	VanillaDatepicker
} from "../../vendor/ublaboo/datagrid/assets"
import { NajaAjax } from "../../vendor/ublaboo/datagrid/assets/ajax";
import Select from "tom-select";
import { Dropdown } from "bootstrap";

// Styles
import '../css/main.css';

// Datagrid + UI
document.addEventListener("DOMContentLoaded", () => {
	// Initialize dropdowns
	Array.from(document.querySelectorAll('.dropdown'))
		.forEach(el => new Dropdown(el))

	// Initialize Naja (nette ajax)
	naja.formsHandler.netteForms = netteForms;
	naja.initialize();

	// Initialize datagrids
	createDatagrids(new NajaAjax(naja), {
		datagrid: {
			plugins: [
				new AutosubmitPlugin(),
				new CheckboxPlugin(),
				new ConfirmPlugin(),
				new InlinePlugin(),
				new ItemDetailPlugin(),
				new NetteFormsPlugin(netteForms),
				new SortablePlugin(new SortableJS()),
				new DatepickerPlugin(new VanillaDatepicker({ buttonClass: 'btn' })),
				new SelectpickerPlugin(new TomSelect(Select)),
				new TreeViewPlugin(),
			],
		},
	});
});
```

</details>

<details>
<summary>assets/css/main.css</summary>

```css
/* Datagrid styles */
@import "@fortawesome/fontawesome-free/css/all.css";
@import 'bootstrap/dist/css/bootstrap.css';
@import 'vanillajs-datepicker/css/datepicker-bs5.css';
@import "tom-select/dist/css/tom-select.css";
@import "tom-select/dist/css/tom-select.bootstrap5.css";
@import '../../vendor/ublaboo/datagrid/assets/css/datagrid.css';
@import '../../vendor/ublaboo/datagrid/assets/css/tom-select.css';

/* Your styles */
```

</details>

<details>
<summary>vite.config.mjs</summary>

```js
import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig(({ mode }) => {
	const DEV = mode === 'development';

	return {
		publicDir: './assets/public',
		resolve: {
			alias: {
				'@': resolve(__dirname, 'assets/js'),
				'~': resolve(__dirname, 'node_modules'),
			},
		},
		base: process.env.VITE_BASE ?? '/dist/',
		server: {
			open: false,
			hmr: false,
		},
		css: {
			postcss: [
				"autoprefixer"
			]
		},
		build: {
			manifest: true,
			assetsDir: '',
			outDir: './www/dist/',
			emptyOutDir: true,
			minify: DEV ? false : 'esbuild',
			rollupOptions: {
				output: {
					manualChunks: undefined,
					chunkFileNames: '[name].js', // DEV ? '[name].js' : '[name]-[hash].js',
					entryFileNames: '[name].js', // DEV ? '[name].js' : '[name].[hash].js',
					assetFileNames: '[name].[ext]', // DEV ? '[name].[ext]' : '[name].[hash].[ext]',
				},
				input: {
					app: './assets/js/main.js'
				}
			}
		},
	}
});
```

</details>
