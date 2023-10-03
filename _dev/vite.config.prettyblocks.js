import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: '../views/js/',
    assetsDir: '',

    rollupOptions: {
      output: {
        entryFileNames: 'build.js',
        chunkFileNames: 'build.js',
      },
      input: '../views/js/prettyblocks.js' // Remplacez par le chemin correct vers votre fichier
    }
  }
});
