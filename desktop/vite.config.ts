import { defineConfig } from 'vite';

export default defineConfig({
  // Do not inherit the Laravel frontend's PostCSS/Tailwind configuration.
  css: { postcss: { plugins: [] } },
});
