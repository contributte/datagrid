import commonjs from '@rollup/plugin-commonjs';
import resolve from '@rollup/plugin-node-resolve';
import typescript from '@rollup/plugin-typescript';
import postcss from 'rollup-plugin-postcss';
import terser from '@rollup/plugin-terser';
import autoprefixer from 'autoprefixer';
import postcssImport from 'postcss-import';
import postcssUrl from 'postcss-url';

export default [
	{
		input: 'assets/datagrid-full.ts',
		output: {
			file: 'dist/datagrid-full.js',
			format: 'es',
			sourcemap: true,
		},
		plugins: [
			resolve(),
			commonjs(),
			typescript(),
			terser({
				mangle: {
					reserved: ['$', 'jQuery'],
				},
			}),
		],
	},
	{
		input: 'assets/css/datagrid-full.css',
		output: {
			file: 'dist/datagrid-full.css',
		},
		plugins: [
			postcss({
				extract: true,
				minimize: true,
				modules: false,
				inject: false,
				plugins: [
					postcssImport,
					autoprefixer,
					postcssUrl,
				],
			}),
		],
	},
];
