import { fileURLToPath, URL } from 'node:url'
import fs from 'node:fs'

// Charger manuellement les variables depuis front.env
const envPath = fileURLToPath(new URL('../env/front.env', import.meta.url));
if (fs.existsSync(envPath)) {
  process.loadEnvFile(envPath);
}

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
  server: {
    watch: {
      usePolling: true
    }
  }
})
