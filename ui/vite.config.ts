import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// SPA for TestLink. In dev, /lib/api/* is proxied to the PHP server.
// The production build is served by the same PHP server from /ui/dist/.
export default defineConfig({
  base: './',
  plugins: [react(), tailwindcss()],
  server: {
    port: 5173,
    proxy: {
      '/lib/api': {
        target: 'http://localhost:8090',
        changeOrigin: true,
      },
    },
  },
})
