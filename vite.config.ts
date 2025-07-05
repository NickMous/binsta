import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'
import liveReload from "vite-plugin-live-reload";
import {resolve} from "node:path";

// https://vite.dev/config/
export default defineConfig({
    plugins: [
        vue(),
        liveReload([
            __dirname + '/src/**/*.php'
        ])
    ],
    root: __dirname + '/src/Resources',
    base: process.env.NODE_ENV === 'development'
        ? '/'
        : '/dist/',

    build: {
        outDir: '../../public/dist',
        emptyOutDir: true,
        manifest: true,

        rollupOptions: {
            input: resolve(__dirname, 'src/Resources/main.ts'),
            output: {
                manualChunks(id) {
                    // all third-party code will be in vendor chunk
                    if (id.includes('node_modules')) {
                        return 'vendor'
                    }
                    // example on how to create another chunk
                    // if (id.includes('src/'components')) {
                    //   return 'components'
                    // }
                    // console.log(id)
                },
            },
        }
    },
    server: {
        // we need a strict port to match on PHP side
        // change freely, but update on PHP to match the same port
        // tip: choose a different port per project to run them at the same time
        host: 'localhost',
        port: 5173,
        strictPort: true,
        cors: {
            origin: ['https://binsta.ddev.site', 'http://localhost:5173'],
            credentials: true,
        }
    },

    // required for in-browser template compilation
    // https://vuejs.org/guide/scaling-up/tooling.html#note-on-in-browser-template-compilation
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js'
        }
    }
})
