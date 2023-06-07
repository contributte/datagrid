Table of contents

- [Assets](#assets)
	- [NPM](#npm)
	- [Example html when not using NPM](#example-html-when-not-using-npm)

# Assets

There are prepare JS/TS and CSS files for precise functionality. The best way is to use some frontend bundler, for example [Vite](https://vitejs.dev).

## Installation

You need to install datagrid's assets. For example this way.

```json
{
  "dependencies": {
    "@contributte/datagrid": "git+ssh://git@github.com:contributte/datagrid.git#next"
  }
}
```

##

## Demo

Full example of using bundler.

**package.json**

```json
{
  "dependencies": {
    "@contributte/datagrid": "git+ssh://git@github.com:contributte/datagrid.git#next",
    "@fortawesome/fontawesome-free": "^6.3.0",
    "bootstrap": "^5.3.0-alpha3",
    "naja": "^2.5.0",
    "nette-forms": "^3.3.1",
    "prismjs": "^1.29.0",
    "sortablejs": "^1.15.0",
    "tom-select": "^2.2.2",
    "vanillajs-datepicker": "^1.3.1"
  },
  "devDependencies": {
    "@types/bootstrap-select": "^1.13.4",
    "@types/jquery": "^3.5.16",
    "@types/jqueryui": "^1.12.16",
    "@types/sortablejs": "^1.15.1",
    "@types/vanillajs-datepicker": "^1.2.1",
    "autoprefixer": "^10.4.0",
    "typescript": "^4.9.5",
    "vite": "^2.6.10"
  },
  "scripts": {
    "watch": "vite build --watch --mode=development",
    "build": "vite build --mode=production"
  }
}
```

**vite.config.js**

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
		base: '/dist/',
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
					chunkFileNames: '[name].js',
					entryFileNames: '[name].js',
					assetFileNames: '[name].[ext]',
				},
				input: {
					app: './assets/js/main.js'
				}
			}
		},
	}
});
```
