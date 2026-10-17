import {rollup} from 'rollup';
import resolve from '@rollup/plugin-node-resolve';
import typescript from '@rollup/plugin-typescript';
import {fileURLToPath} from 'node:url';

export default async function () {
	const bundle = await rollup({
		input: fileURLToPath(new URL('fixture.ts', import.meta.url)),
		plugins: [resolve(), typescript({noEmit: false, sourceMap: false, outDir: 'tests/temp/browser', include: ['assets/**/*.ts', 'tests/browser/fixture.ts']})],
	});
	try {
		await bundle.write({
			file: fileURLToPath(new URL('../temp/browser/editable.js', import.meta.url)),
			format: 'iife',
		});
	} finally {
		await bundle.close();
	}
}
