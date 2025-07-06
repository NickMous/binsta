import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'
import liveReload from "vite-plugin-live-reload";
import {resolve} from "node:path";
import fs from 'fs';
import tailwindcss from "@tailwindcss/vite";
import * as path from "node:path";

// https://vite.dev/config/
export default defineConfig({
    plugins: [
        vue(),
        tailwindcss(),
        liveReload([
            __dirname + '/src/**/*.php'
        ]),
        // Plugin to write dev manifest
        {
            name: 'dev-manifest',
            configureServer(server) {
                const manifestPath = resolve(__dirname, 'public/dist/.vite/dev-manifest.json');
                
                const writeDevManifest = () => {
                    const devUrl = 'http://localhost:5173';
                    const manifest = {
                        'main.ts': {
                            file: 'main.ts',
                            isEntry: true,
                            src: 'main.ts',
                            url: `${devUrl}/main.ts`
                        },
                        devServer: {
                            url: devUrl,
                            host: 'localhost',
                            port: 5173
                        }
                    };
                    const manifestDir = resolve(__dirname, 'public/dist/.vite');
                    if (!fs.existsSync(manifestDir)) {
                        fs.mkdirSync(manifestDir, { recursive: true });
                    }
                    fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 2));
                };
                
                const removeDevManifest = () => {
                    if (fs.existsSync(manifestPath)) {
                        fs.unlinkSync(manifestPath);
                    }
                };
                
                // Write manifest on server start
                writeDevManifest();
                
                // Clean up on process exit
                process.on('SIGINT', removeDevManifest);
                process.on('SIGTERM', removeDevManifest);
                process.on('exit', removeDevManifest);

                server.middlewares.use((_req, _res, next) => {
                    next();
                });
            }
        }
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
    define: {
        __DEV_MANIFEST__: process.env.NODE_ENV === 'development'
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
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, './src/Resources'),
        }
    }
})
