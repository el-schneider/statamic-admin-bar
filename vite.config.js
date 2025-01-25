import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import path from 'path'
import { defineConfig } from 'vite'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/admin-bar.ts', 'resources/css/admin-bar.css'],
            publicDirectory: 'resources/dist',
        }),
        vue(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@types': path.resolve(__dirname, './resources/js/types'),
        },
    },
})
