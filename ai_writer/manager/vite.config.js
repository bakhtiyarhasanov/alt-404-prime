import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  const proxyTarget = env.VITE_PROXY_TARGET || 'https://aiapi.alt404.com'
  const port = parseInt(env.VITE_PORT || '5174', 10)

  return {
    plugins: [vue()],
    css: {
      postcss: {}
    },
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url))
      }
    },
    server: {
      port,
      proxy: {
        '/ai_writer/api': {
          target: proxyTarget,
          changeOrigin: true,
          rewrite: (path) => {
            // If query string starts right after /ai_writer/api
            const query = path.includes('?') ? '?' + path.split('?')[1] : ''
            return '/api/index.php' + query
          }
        },
        '/api': {
          target: proxyTarget,
          changeOrigin: true,
          rewrite: (path) => {
            if (path.includes('index.php')) return path
            const query = path.includes('?') ? '?' + path.split('?')[1] : ''
            return '/api/index.php' + query
          }
        }
      }
    }
  }
})
