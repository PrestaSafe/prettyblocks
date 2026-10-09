import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  build:{
    manifest: 'manifest.json',
    outDir: "../build/",
    emptyOutDir: true, 
  },
  server: {
    // hmr: {
    //   host: 'vue.prestasafe.com',
    //   clientPort: 443,
    //   protocol: 'wss'
    // } 
    hmr: {
      host: 'localhost',
      protocol: 'ws'
    }
  }
})
