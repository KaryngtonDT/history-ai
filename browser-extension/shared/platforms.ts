import { BrowserPlatform } from "./types";

function hostEndsWith(host: string, suffix: string): boolean {
  return host.endsWith(suffix);
}

function parseUrlParts(url: string): { host: string; path: string } {
  try {
    const parsed = new URL(url);
    return {
      host: parsed.hostname.toLowerCase(),
      path: parsed.pathname.toLowerCase(),
     };
  } catch {
    return { host: "", path: "" };
  }
}

export function detectPlatform(url: string): BrowserPlatform {
  const { host, path } = parseUrlParts(url);

  if (host === "") {
    return BrowserPlatform.Unknown;
  }

  if (
    hostEndsWith(host, "youtube.com") ||
    host === "youtu.be" ||
    hostEndsWith(host, "youtu.be")
  ) {
    return BrowserPlatform.Youtube;
  }

  if (hostEndsWith(host, "wikipedia.org")) {
    return BrowserPlatform.Wikipedia;
  }

  if (host === "developer.mozilla.org") {
    return BrowserPlatform.Mdn;
  }

  if (hostEndsWith(host, "symfony.com") && path.startsWith("/doc")) {
    return BrowserPlatform.SymfonyDocs;
  }

  if (host === "php.net" || host === "www.php.net") {
    return BrowserPlatform.PhpDocs;
  }

  if (host === "github.com") {
    return BrowserPlatform.Github;
  }

  if (host === "gitlab.com") {
    return BrowserPlatform.Gitlab;
  }

  if (host === "stackoverflow.com") {
    return BrowserPlatform.Stackoverflow;
  }

  if (host === "reddit.com" || hostEndsWith(host, ".reddit.com")) {
    return BrowserPlatform.Reddit;
  }

  if (path.endsWith(".pdf")) {
    return BrowserPlatform.PdfViewer;
  }

  return BrowserPlatform.Unknown;
}

export function extractHost(url: string): string {
  const { host } = parseUrlParts(url);

  if (host === "") {
    return "";
  }

  if (host.startsWith("www.")) {
    return host.slice(4);
  }

  return host;
}
