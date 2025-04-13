import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js','resources/js/codeEditor.js', 'resources/js/AdvanceTinyMCE.js'],
            refresh: true,
        }),
    ]
    ,resolve: {
        alias: {
            // Ensure tinymce can properly load its resources
            'tinymce': 'tinymce',
        },
    }
     ,build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    tinymce: ['tinymce'],
                },
            },
        },
    }, optimizeDeps: {
        include: ['tinymce'],
    },
});
