import { resolve } from "node:path";
import { build } from "vite";

const root = resolve(import.meta.dirname, "..");
const contentScripts = ["content/detector", "content/overlay"];

for (const name of contentScripts) {
  await build({
    configFile: false,
    build: {
      outDir: resolve(root, "dist"),
      emptyOutDir: false,
      rollupOptions: {
        input: {
          [name]: resolve(root, `${name}.ts`),
        },
        output: {
          format: "iife",
          inlineDynamicImports: true,
          entryFileNames: "[name].js",
        },
      },
    },
  });
}
