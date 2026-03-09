import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    const isProductionBuild = mode === 'production';
    const isExternalMode = env.PUBLIC_PATH_MODE === 'external';

    // Na localhostu nikdy nebuildujeme do externí složky.
    // Detekujeme podle uživatele nebo cesty (vývojářské stroje).
    const isLocalMachine = process.env.USER === 'michalnejedly' ||
                          process.cwd().includes('/Users/michalnejedly/') ||
                          process.cwd().includes('/Users/junie/');

    const isExternal = isProductionBuild && isExternalMode && env.APP_ENV !== 'local' && !isLocalMachine;

    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    'resources/css/filament-auth.css',
                    'resources/js/filament-auth.js',
                    'resources/css/filament-admin.css',
                    'resources/js/filament-error-handler.js',
                    'resources/js/feedback-widget.js'
                ],
                refresh: true,
                // Na produkci (external) buildujeme přímo do subdomény
                publicDirectory: isExternal ? '../subdomains/new' : 'public',
            }),
            tailwindcss(),
        ],
        server: {
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
