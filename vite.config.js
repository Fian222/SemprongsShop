import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // Bootstrap 5 masih memakai @import & fungsi global Sass yang sudah deprecated
                quietDeps: true,
                silenceDeprecations: ['import'],
            },
        },
    },
});
