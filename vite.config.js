// import dotenv from 'dotenv'
// dotenv.config()

import { resolve } from 'path'
import { defineConfig } from 'vite'
import noBundlePlugin from 'vite-plugin-no-bundle'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        noBundlePlugin({
            root: 'public/src',
            fileNames: '[name].mjs',
            // copy: '**/*.css',
            // internal: 'my-special-node-module'
        })
    ],
    publicDir: resolve(__dirname, 'dist/js/'),
    build: {
        outDir: resolve(__dirname, 'dist/js/'),
        lib: {
            entry: resolve(__dirname, 'public/src/main.js'),
            name: 'DatagridJS',
            // the proper extensions might be added
            fileName: 'datagrid'
        },
        rollupOptions: {
            // make sure to externalize deps that shouldn't be bundled
            // into your library
            // external: [],
            output: {
                // Provide global variables to use in the UMD build
                // for externalized deps
                format: ['es', 'umd'],
                preserveModules: true,
                inlineDynamicImports: false,
                // globals: {
                //     // vue: 'Vue'
                // }
            }
        }
    }
})
