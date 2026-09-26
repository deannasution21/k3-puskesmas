import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { defineConfig } from 'vite'

// https://vite.dev/config/
export default defineConfig(({ command }) => ({
  plugins: [vue(), tailwindcss()],
  // Saat production build, aset dilayani dari Laravel di /spa/ (lihat Dockerfile & routes/web.php).
  // Dev server tetap pakai '/' karena Vite yang melayani langsung.
  base: command === 'build' ? '/spa/' : '/',
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
      '/sanctum': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
}))
