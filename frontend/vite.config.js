import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import { resolve } from "path";
import tailwindcss from "tailwindcss";
import autoprefixer from "autoprefixer";

export default defineConfig(({ mode }) => {
  // Load env file based on `mode` in the current directory
  const env = loadEnv(mode, process.cwd());

  // Get API URL from env variable or use default
  const apiUrl = env.VITE_API_URL || "http://localhost:8080";

  return {
    plugins: [vue()],
    css: {
      postcss: {
        plugins: [tailwindcss, autoprefixer],
      },
    },
    resolve: {
      alias: {
        "@": resolve(__dirname, "src"),
      },
    },
    build: {
      outDir: "../public",
      emptyOutDir: false,
      manifest: true,
      rollupOptions: {
        input: resolve(__dirname, "src/main.js"),
      },
    },
    publicDir: "../public",
    server: {
      proxy: {
        "/api": {
          target: apiUrl,
          changeOrigin: true,
        },
      },
    },
  };
});
