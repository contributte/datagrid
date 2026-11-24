import {rollup} from 'rollup';
import resolve from '@rollup/plugin-node-resolve';
import typescript from '@rollup/plugin-typescript';
import {fileURLToPath} from 'node:url';

const bundles = {
	'fixture.ts': 'editable.js',
	'autosubmit-fixture.ts': 'autosubmit.js',
};

export default async function () {
	for (const [input, output] of Object.entries(bundles)) {
		const bundle = await rollup({
			input: fileURLToPath(new URL(input, import.meta.url)),
			plugins: [resolve(), typescript({noEmit: false, sourceMap: false, outDir: 'tests/temp/browser', include: ['assets/**/*.ts', `tests/browser/${input}`]})],
		});
		try {
			await bundle.write({
				file: fileURLToPath(new URL(`../temp/browser/${output}`, import.meta.url)),
				format: 'iife',
			});
		} finally {
			await bundle.close();
		}
	}
}
