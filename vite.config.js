import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      '/app': 'http://localhost',
    },
  },
  build: {
    outDir: 'public/build',
  },
});