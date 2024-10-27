import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    sourcemap: true,
    chunkSizeWarningLimit: 100000    
  },
  define: {
    global: 'globalThis'
  },
  server: {
    cors: true,
    port: 81
  }
})
