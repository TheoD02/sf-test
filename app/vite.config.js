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
      pattern: ["config/routes.yaml", "src/**.php"],
      command: "npx openapi-typescript http://<app-name-placeholder>.web.localhost/api/docs.json -o ./assets/api/schema.d.ts",
      silent: true,
    }),
    watch({
      pattern: ["config/routes.yaml", "src/*/Infrastructure/ApiPlatform/**.php", "src/Shared/Controller/**.php"],
      command: "npx @digitak/esrun \"./assets/security/roles-fetcher.ts\"",
      silent: false,
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
    alias: [
      {find: "@", replacement: path.resolve(__dirname, "./assets")},
      {find: "@api", replacement: path.resolve(__dirname, "./assets/api")},
      {find: "@components", replacement: path.resolve(__dirname, "./assets/components")},
      {find: "@hooks", replacement: path.resolve(__dirname, "./assets/hooks")},
      {find: "@routes", replacement: path.resolve(__dirname, "./assets/routes")},
      {find: "@security", replacement: path.resolve(__dirname, "./assets/security")},
    ]
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
