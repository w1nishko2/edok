import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/recipes.css',
                'resources/css/navbar.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
