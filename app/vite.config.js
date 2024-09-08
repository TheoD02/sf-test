import {defineConfig} from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import react from "@vitejs/plugin-react";
import {TanStackRouterVite} from "@tanstack/router-vite-plugin";
import {watch} from "vite-plugin-watch";
import path from "path";

export default defineConfig({
  plugins: [
    react(),
    symfonyPlugin(),
    TanStackRouterVite({
      routesDirectory: "./assets/routes",
      generatedRouteTree: "./assets/routeTree.gen.ts",
    }),
    // Add watch on src directory and run command npx openapi-typescript http://mantine-starter-kit.web.localhost/api/docs.json -o ./src/api/schema.d.ts
    watch({
      pattern: ["src/Entity/*.php", "config/routes.yaml", "src/ApiResource/*.php", "src/ApiResource/**/*.php"],
      command: "npx openapi-typescript http://mantine-starter-kit.web.localhost/api/docs.json -o ./assets/api/schema.d.ts",
      silent: true,
    }),
  ],
  build: {
    rollupOptions: {
      input: {
        app: "./assets/main.tsx",
      },
    },
  },
  resolve: {
    alias: {
      "@api": path.resolve(__dirname, "./assets/api"),
      "@components": path.resolve(__dirname, "./assets/components"),
      "@hooks": path.resolve(__dirname, "./assets/hooks"),
    },
  },
  server: {
    // watch: {
    //     usePolling: true,
    // },
    host: true,
    port: 3151,
    hmr: {
      protocol: "ws",
      host: "localhost",
      port: 3151,
      clientPort: 3151,
    },
  },
});
