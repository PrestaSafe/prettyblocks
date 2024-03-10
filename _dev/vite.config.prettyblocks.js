import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: '../views/js/',
    assetsDir: '',
    emptyOutDir: true, 

    rollupOptions: {
      output: {
        entryFileNames: 'build.js',
        chunkFileNames: 'build.js',
      },
      input: './src/scripts/prettyblocks.js' // Remplacez par le chemin correct vers votre fichier
    }
  }
});

