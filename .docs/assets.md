Table of contents

- [Assets](#assets)
	- [NPM](#npm)
	- [Example html when not using NPM](#example-html-when-not-using-npm)

# Assets

There are prepare JS/TS and CSS files for precise functionality. The best way is to use some frontend bundler, for example [Vite](https://vitejs.dev).

## Installation

You need to install datagrid's assets.

**Install datagrid from github.**

```json
{
  "dependencies": {
    "@contributte/datagrid": "github:contributte/datagrid#master",
  }
}
```

**Install datagrid from npm.**

```json
{
  "dependencies": {
    "@contributte/datagrid": "^X.Y.Z"
  }
}
```

## Demo

Full example of using bundler.

**package.json**

```json
{
  "name": "datagrid",
  "version": "0.0.0",
  "license": "MIT",
  "engines": {
    "npm": ">=9.0",
    "node": ">=22.0"
  },
  "dependencies": {
    "@contributte/datagrid": "github:contributte/datagrid#master",
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

**vite.config.mjs**

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
