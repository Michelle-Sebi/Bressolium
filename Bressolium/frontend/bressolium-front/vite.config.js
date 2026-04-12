import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'

const srcDir = path.resolve(__dirname, './src')

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), tailwindcss()],

  server: {
    fs: {
      allow: ['..'],
    },
  },

  resolve: {
    alias: {
      '../src': srcDir,
    },
  },

  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: [path.resolve(__dirname, './src/setupTests.js')],
    include: [
      // Tests JSX (con componentes React) en la carpeta hermana /tests
      '../tests/**/*.test.jsx',
      // Tests JS simples (JSDOM vanilla) en la carpeta hermana /tests
      '../tests/**/*.test.js',
      // Tests dentro del propio proyecto Vite
      './src/**/*.test.{js,jsx}',
    ],
    alias: {
      '../src': srcDir,
    },
    server: {
      fs: { allow: ['..'] },
    },
  },
})
