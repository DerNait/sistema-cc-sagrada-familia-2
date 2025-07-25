import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
            protocol: 'ws',
        },
        /* watch: {
            usePolling: true,
            interval: 100,
        }, */
    },
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    test: {
        environment: 'jsdom'
    },
    resolve: {
        alias: [
            {
                find: "vue",
                replacement: "vue/dist/vue.esm-bundler.js",
            },
            {
                find: /^~(.*)$/,
                replacement: "$1",
            },
        ],
    },
});
