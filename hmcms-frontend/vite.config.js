import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost/Healthcare%20&%20Medical%20Camp%20Management%20System/Healthcare-Medical-Camp-Management-System',
        changeOrigin: true,
      }
    }
  }
})
