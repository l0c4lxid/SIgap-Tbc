import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devServerHost = env.VITE_DEV_SERVER_HOST;
    const devServerPort = Number(env.VITE_DEV_SERVER_PORT || 5173);
    let appHost;

    if (!devServerHost && env.APP_URL) {
        try {
            appHost = new URL(env.APP_URL).hostname;
        } catch {
            appHost = undefined;
        }
    }

    const hmrHost = devServerHost || appHost;

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
        ],
        server: {
            host: '0.0.0.0',
            port: devServerPort,
            strictPort: true,
            hmr: hmrHost ? { host: hmrHost, clientPort: devServerPort } : undefined,
        },
    };
});
