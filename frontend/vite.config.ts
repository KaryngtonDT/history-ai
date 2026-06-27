import path from "node:path";
import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";
import { defineConfig } from "vitest/config";

export default defineConfig({
	plugins: [react(), tailwindcss()],
	resolve: {
		alias: {
			"@": path.resolve(__dirname, "./src"),
		},
	},
	server: {
		proxy: {
			"/api": {
				target: "http://localhost:8000",
				changeOrigin: true,
			},
		},
	},
	preview: {
		host: "0.0.0.0",
		port: 5173,
	},
	test: {
		environment: "jsdom",
		environmentMatchGlobs: [["src/architecture/**", "node"]],
		setupFiles: ["./src/test/setup.ts"],
		globals: true,
		env: {
			VITE_USE_MOCK: "true",
		},
	},
});
