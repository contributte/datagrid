import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig(({ mode }) => {
	const DEV = mode === 'development';

	return {
		publicDir: './styles/public',
		resolve: {
			alias: {
				'@': resolve(__dirname, 'styles/js'),
				'~': resolve(__dirname, 'node_modules'),
				'@datagrid': resolve(__dirname, 'styles/datagrid'),
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
					chunkFileNames: '[name].js', // DEV ? '[name].js' : '[name]-[hash].js',
					entryFileNames: '[name].js', // DEV ? '[name].js' : '[name].[hash].js',
					assetFileNames: '[name].[ext]', // DEV ? '[name].[ext]' : '[name].[hash].[ext]',
				},
				input: {
					app: './styles/js/main.js'
				}
			}
		},
	}
});
