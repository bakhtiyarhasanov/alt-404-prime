import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
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
    port: 5174,
    proxy: {
      '/ai_writer/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        rewrite: (path) => {
          // If query string starts right after /ai_writer/api
          const query = path.includes('?') ? '?' + path.split('?')[1] : '';
          return '/api/index.php' + query;
        }
      },
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        rewrite: (path) => {
          if (path.includes('index.php')) return path;
          const query = path.includes('?') ? '?' + path.split('?')[1] : '';
          return '/api/index.php' + query;
        }
      }
    }
  }
})
