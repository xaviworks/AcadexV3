import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/guest-entry.css',
                'resources/css/components/select-academic-period.css',
                'resources/js/app.js',
                'resources/js/pages/shared/select-academic-period.js'
            ],
            refresh: true,
        }),
    ],
});
