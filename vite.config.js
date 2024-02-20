import { defineConfig } from 'vite'
import { resolve } from 'path'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue({
    template: {
      transformAssetUrls: {
        base: null,
        includeAbsolute: false,
      },
    },
  }),],
  build: {
    lib: {
      // Could also be a dictionary or array of multiple entry points
      entry: {
        // main: resolve(__dirname, 'src/chat.html'),
        chat: resolve(__dirname, 'src/chat.js'),
      },
      name: 'ilExtChat',
      // the proper extensions will be added
      fileName: 'my-il-ext-chat',
      formats: ['iife']
    },
    minify: 'terser',
    terserOptions: {
      compress: {
        keep_fnames: /^.*/,
      },
    },
    rollupOptions: {
      // input: {
      //   main: resolve(__dirname, 'src/chat.html'),
      //   chat: resolve(__dirname, 'src/chat.js'),
      // },
      external: ['Vue'],
      output: {
        entryFileNames: `assets/[name].js`,
        chunkFileNames: `assets/[name].js`,
        assetFileNames: `assets/[name].[ext]`,
        dir: resolve(__dirname, 'app/'),
        globals: {
          Vue: 'Vue',
        },
      },
    },
  },
})
