import { describe, expect, it } from "vitest";
import { BrowserPlatform } from "../shared/types";
import { detectPlatform, extractHost } from "../shared/platforms";

describe("detectPlatform", () => {
  it.each([
    ["https://www.youtube.com/watch?v=abc", BrowserPlatform.Youtube],
    ["https://youtu.be/abc123", BrowserPlatform.Youtube],
    ["https://en.wikipedia.org/wiki/PHP", BrowserPlatform.Wikipedia],
    ["https://developer.mozilla.org/en-US/docs/Web", BrowserPlatform.Mdn],
    ["https://symfony.com/doc/current/index.html", BrowserPlatform.SymfonyDocs],
    ["https://www.php.net/manual/en/index.php", BrowserPlatform.PhpDocs],
    ["https://github.com/symfony/symfony", BrowserPlatform.Github],
    ["https://gitlab.com/group/project", BrowserPlatform.Gitlab],
    ["https://stackoverflow.com/questions/123", BrowserPlatform.Stackoverflow],
    ["https://www.reddit.com/r/php/", BrowserPlatform.Reddit],
    ["https://example.com/docs/guide.pdf", BrowserPlatform.PdfViewer],
    ["https://example.com/page", BrowserPlatform.Unknown],
  ] as const)("detects %s as %s", (url, expected) => {
    expect(detectPlatform(url)).toBe(expected);
  });

  it("returns unknown for invalid URLs", () => {
    expect(detectPlatform("not-a-url")).toBe(BrowserPlatform.Unknown);
  });

  it("does not match symfony.com outside /doc path", () => {
    expect(detectPlatform("https://symfony.com/blog")).toBe(BrowserPlatform.Unknown);
  });
});

describe("extractHost", () => {
  it("strips www prefix", () => {
    expect(extractHost("https://www.youtube.com/watch?v=abc")).toBe("youtube.com");
  });

  it("returns empty string for invalid URLs", () => {
    expect(extractHost("not-a-url")).toBe("");
  });
});
