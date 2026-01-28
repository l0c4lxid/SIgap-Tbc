import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const envDevServerHost = env.VITE_DEV_SERVER_HOST;
    const devServerPort = Number(env.VITE_DEV_SERVER_PORT || 5173);
    let appHost;

    if (!envDevServerHost && env.APP_URL) {
        try {
            appHost = new URL(env.APP_URL).hostname;
        } catch {
            appHost = undefined;
        }
    }

    const devServerHost = envDevServerHost || appHost || '127.0.0.1';
    const hmrHost = devServerHost;
    const devServerOrigin = `http://${devServerHost}:${devServerPort}`;

    return {
        plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/assets/scss/soft-ui-dashboard.scss',
                    'resources/js/app.js',
                    'resources/css/landing-animations.css',
                    'resources/js/landing-animations.js',
                ],
                refresh: true,
            }),
        ],
        css: {
            preprocessorOptions: {
                scss: {
                    quietDeps: true,
                    silenceDeprecations: ['import', 'global-builtin', 'color-functions'],
                },
            },
        },
        server: {
            host: devServerHost,
            port: devServerPort,
            strictPort: true,
            origin: devServerOrigin,
            hmr: hmrHost ? { host: hmrHost, clientPort: devServerPort } : undefined,
        },
    };
});
