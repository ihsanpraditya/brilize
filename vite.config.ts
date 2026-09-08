import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import symfonyPlugin from 'vite-plugin-symfony';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
  base: '/brilize/build/',
  plugins: [
    vue(),
    symfonyPlugin(),
    tailwindcss(),
  ],
  build: {
    manifest: true,
    outDir: 'public/build',
    rollupOptions: {
      input: {
          app: './assets/app.ts',
      },
    },
  },
});
