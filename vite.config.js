import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import symfonyPlugin from 'vite-plugin-symfony';

export default defineConfig({
  base: '/brilize/build/',
  plugins: [
    vue(),
    symfonyPlugin(),
  ],
  build: {
    manifest: true,
    outDir: 'public/build',
    rollupOptions: {
      input: {
          app: './assets/app.js',
      },
    },
  },
});
