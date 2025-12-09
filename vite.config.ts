import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

const devServerHost = process.env.VITE_DEV_SERVER_HOST ?? '0.0.0.0';
const devServerPort = Number(process.env.VITE_DEV_SERVER_PORT ?? 5173);
const devServerHmrHost =
    process.env.VITE_DEV_SERVER_HOSTNAME ?? 'vite.radio-francine.c0chett0.dev';
const devServerHmrProtocol = process.env.VITE_DEV_SERVER_PROTOCOL ?? 'wss';
const devServerHmrClientPort = Number(
    process.env.VITE_DEV_SERVER_CLIENT_PORT ?? 443,
);

export default defineConfig({
    server: {
        host: devServerHost,
        port: devServerPort,
        strictPort: true,
        hmr: {
            host: devServerHmrHost,
            protocol: devServerHmrProtocol,
            clientPort: devServerHmrClientPort,
        },
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: [
                'favicon.ico',
                'favicon.svg',
                'apple-touch-icon.png',
                'pwa-icon-192.png',
                'pwa-icon-512.png',
            ],
            manifest: {
                name: 'Radio Francine',
                short_name: 'Radio Francine',
                start_url: '/',
                scope: '/',
                display: 'standalone',
                background_color: '#020617',
                theme_color: '#10b981',
                description: 'Écoutez Radio Francine en continu, partout.',
                icons: [
                    {
                        src: '/pwa-icon-192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/pwa-icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                    {
                        src: '/pwa-icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any maskable',
                    },
                ],
            },
            workbox: {
                cleanupOutdatedCaches: true,
                runtimeCaching: [
                    {
                        urlPattern: ({ url }) =>
                            url.pathname.startsWith('/api/radio/francine'),
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'radio-francine-api',
                            networkTimeoutSeconds: 5,
                            cacheableResponse: {
                                statuses: [0, 200],
                            },
                        },
                    },
                ],
            },
        }),
    ],
});
