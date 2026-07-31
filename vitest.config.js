import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            publicDirectory: 'public',
        }),
    ],
    test: {
        environment: 'jsdom',
        include: ['resources/js/tests/**/*.test.js'],
    },
});
