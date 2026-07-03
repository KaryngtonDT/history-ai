import { cpSync, mkdirSync } from "node:fs";
import { resolve } from "node:path";
import { defineConfig } from "vite";

const root = resolve(import.meta.dirname);
const staticPages = ["popup/popup.html", "options/options.html"] as const;

function copyExtensionAssets(): import("vite").Plugin {
  return {
    name: "copy-extension-assets",
    closeBundle() {
      cpSync(resolve(root, "manifest.json"), resolve(root, "dist/manifest.json"));

      for (const page of staticPages) {
        const targetDir = resolve(root, "dist", page.split("/")[0] ?? "");
        mkdirSync(targetDir, { recursive: true });
        cpSync(resolve(root, page), resolve(root, "dist", page));
      }
    },
  };
}

export default defineConfig({
  build: {
    outDir: "dist",
    emptyOutDir: true,
    rollupOptions: {
      input: {
        "background/service-worker": resolve(root, "background/service-worker.ts"),
        "content/overlay": resolve(root, "content/overlay.ts"),
        "content/detector": resolve(root, "content/detector.ts"),
        "popup/popup": resolve(root, "popup/popup.ts"),
        "options/options": resolve(root, "options/options.ts"),
      },
      output: {
        entryFileNames: "[name].js",
        chunkFileNames: "chunks/[name]-[hash].js",
        assetFileNames: "assets/[name][extname]",
      },
    },
  },
  plugins: [copyExtensionAssets()],
  test: {
    environment: "node",
    include: ["tests/**/*.test.ts"],
  },
});
