import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// SPA for TestLink. In dev, /lib/api/* is proxied to the PHP server.
// The production build is served from /ui/dist/. An absolute base (rather
// than './') lets the same index.html be served at the site root without a
// redirect — asset URLs stay correct no matter which path serves the HTML.
export default defineConfig({
  base: '/ui/dist/',
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
