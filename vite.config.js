// import dotenv from 'dotenv'
// dotenv.config()

import { resolve } from 'path'
import { defineConfig } from 'vite'
import noBundlePlugin from 'vite-plugin-no-bundle'

// https://vitejs.dev/config/
export default defineConfig({

    publicDir: resolve(__dirname, 'dist/'),
    build: {
        outDir: resolve(__dirname, 'dist/'),
        lib: {
            entry: resolve(__dirname, 'public/src/main.js'),
            name: 'DatagridJS',
            // the proper extensions might be added
            fileName: 'datagrid',
            formats: ['es']
        },
        rollupOptions: {
            // make sure to externalize deps that shouldn't be bundled
            // into your library
            external: ['vanillajs-datepicker'],
            output: {
                // Provide global variables to use in the UMD build
                // for externalized deps
                preserveModules: true,
                inlineDynamicImports: false,
                globals: {
                    datepicker: 'vanillajs-datepicker'
                }
            }
        }
    }
})
