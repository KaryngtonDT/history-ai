import { detectPlatform, extractHost } from "../shared/platforms";
import type { PageContext } from "../shared/types";

function buildPageContext(): PageContext {
  const url = window.location.href;

  return {
    url,
    title: document.title,
    platform: detectPlatform(url),
    host: extractHost(url),
  };
}

function notifyBackground(context: PageContext): void {
  chrome.runtime.sendMessage({ type: "PAGE_DETECTED", context }).catch(() => {
    // Background may be unavailable during extension reload.
  });
}

const context = buildPageContext();
notifyBackground(context);

let lastUrl = context.url;

const observer = new MutationObserver(() => {
  if (window.location.href === lastUrl) {
    return;
  }

  lastUrl = window.location.href;
  notifyBackground(buildPageContext());
});

observer.observe(document.documentElement, {
  subtree: true,
  childList: true,
});

export {};
