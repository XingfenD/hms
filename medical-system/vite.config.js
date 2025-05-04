import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  // server: {
  //   proxy: {
  //     '/hms': {
  //       target: 'http://localhost/hms',
  //       changeOrigin: true,
  //       rewrite: (path) => path.replace(/^\/hms/, '')
  //     }
  //   }
  // }
})
