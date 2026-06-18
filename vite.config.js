import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: null,
            includeAssets: ['favicon.ico', 'pwa-icon.svg', 'offline.html'],
            workbox: {
                navigateFallback: '/offline.html',
                additionalManifestEntries: [
                    {
                        url: '/offline.html',
                        revision: '1',
                    },
                ],
                runtimeCaching: [
                    {
                        urlPattern: ({ request }) => request.mode === 'navigate',
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'pages',
                            networkTimeoutSeconds: 3,
                            expiration: {
                                maxEntries: 30,
                                maxAgeSeconds: 60 * 60 * 24,
                            },
                        },
                    },
                    {
                        urlPattern: ({ request }) => ['style', 'script', 'worker'].includes(request.destination),
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'static-assets',
                            expiration: {
                                maxEntries: 60,
                                maxAgeSeconds: 60 * 60 * 24 * 7,
                            },
                        },
                    },
                ],
            },
            manifest: {
                name: 'Agriculture Tractor',
                short_name: 'Agriculture',
                description: 'Agriculture management app that works like a native installable app.',
                start_url: '/',
                scope: '/',
                display: 'standalone',
                background_color: '#f8fafc',
                theme_color: '#166534',
                orientation: 'portrait',
                icons: [
                    {
                        src: '/pwa-icon.svg',
                        sizes: 'any',
                        type: 'image/svg+xml',
                        purpose: 'any maskable',
                    },
                ],
            },
            devOptions: {
                enabled: true,
            },
        }),
    ],
});
