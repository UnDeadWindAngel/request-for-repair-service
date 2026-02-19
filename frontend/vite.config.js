import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  server: {
    host: true, // чтобы слушать все интерфейсы (необходимо для Docker)
    port: 5173, // порт по умолчанию
    proxy: {
      '/api': {
        target: 'http://nginx', // имя контейнера nginx в сети Docker
        changeOrigin: true,
        // secure: false, // если https не используется
      }
    }
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
})
