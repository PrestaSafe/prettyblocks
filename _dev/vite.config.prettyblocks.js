import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  build: {
    minify: false,
    outDir: '../views/js/build/',
    assetsDir: '',
    emptyOutDir: true,
    lib: {
      entry: './src/scripts/prettyblocks.js',
      name: 'Module2',
      formats: ['iife'],
    },
    rollupOptions: {
      output: {
        entryFileNames: 'build.js',
        chunkFileNames: 'build.js',
        format: 'iife',
        name: 'Module2',
      },
      input: './src/scripts/prettyblocks.js'
    }
  },
  esbuild: {
    keepNames: true,
  },
  define: {
    'process.env': {}
  }
});
