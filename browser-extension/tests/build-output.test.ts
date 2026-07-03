import { readFileSync } from "node:fs";
import { resolve } from "node:path";
import { describe, expect, it } from "vitest";

const distRoot = resolve(import.meta.dirname, "..", "dist");

describe("content script bundles", () => {
  it.each(["content/detector.js", "content/overlay.js"])(
    "%s is a self-contained script without ES module imports",
    (relativePath) => {
      const source = readFileSync(resolve(distRoot, relativePath), "utf8");
      expect(source).not.toMatch(/^\s*import\s/m);
      expect(source).not.toMatch(/^\s*export\s/m);
    },
  );
});
