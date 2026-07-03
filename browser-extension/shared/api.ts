import type { PageContext, StorageSettings } from "./types";

export const DEFAULT_LUMEN_API_BASE = "http://localhost:8000";

const STORAGE_KEYS = {
  lumenApiBase: "lumenApiBase",
  scopeKey: "scopeKey",
} as const;

export async function getStorageSettings(): Promise<StorageSettings> {
  const stored = await chrome.storage.sync.get([
    STORAGE_KEYS.lumenApiBase,
    STORAGE_KEYS.scopeKey,
  ]);

  return {
    lumenApiBase:
      typeof stored[STORAGE_KEYS.lumenApiBase] === "string"
        ? stored[STORAGE_KEYS.lumenApiBase]
        : DEFAULT_LUMEN_API_BASE,
    scopeKey:
      typeof stored[STORAGE_KEYS.scopeKey] === "string"
        ? stored[STORAGE_KEYS.scopeKey]
        : "default",
  };
}

export async function setLumenApiBase(lumenApiBase: string): Promise<void> {
  await chrome.storage.sync.set({ [STORAGE_KEYS.lumenApiBase]: lumenApiBase });
}

function apiUrl(base: string, path: string, scopeKey: string): string {
  const url = new URL(path, base.endsWith("/") ? base : `${base}/`);
  url.searchParams.set("scopeKey", scopeKey);
  return url.toString();
}

async function apiFetch<T>(
  path: string,
  init?: RequestInit,
  body?: Record<string, unknown>,
): Promise<T> {
  const { lumenApiBase, scopeKey } = await getStorageSettings();
  const payload = body ? { ...body, scopeKey } : undefined;

  const response = await fetch(apiUrl(lumenApiBase, path, scopeKey), {
    ...init,
    headers: {
      "Content-Type": "application/json",
      ...(init?.headers ?? {}),
    },
    body: payload ? JSON.stringify(payload) : init?.body,
  });

  if (!response.ok) {
    const errorBody = (await response.json().catch(() => null)) as
      | { error?: string }
      | null;
    throw new Error(errorBody?.error ?? `API request failed (${response.status})`);
  }

  return (await response.json()) as T;
}

export async function connectBrowser(shadowSessionId?: string): Promise<unknown> {
  return apiFetch("/api/shadow/browser/connect", { method: "POST" }, {
    shadowSessionId,
  });
}

export async function disconnectBrowser(): Promise<unknown> {
  return apiFetch("/api/shadow/browser/disconnect", { method: "POST" });
}

export async function getBrowserSession(): Promise<unknown> {
  const { lumenApiBase, scopeKey } = await getStorageSettings();
  const response = await fetch(
    apiUrl(lumenApiBase, "/api/shadow/browser/session", scopeKey),
  );

  if (!response.ok) {
    throw new Error(`Session request failed (${response.status})`);
  }

  return response.json();
}

export async function postBrowserContext(context: PageContext): Promise<unknown> {
  return apiFetch("/api/shadow/browser/context", { method: "POST" }, {
    url: context.url,
    title: context.title,
    platform: context.platform,
    host: context.host,
  });
}

export async function postBrowserPlatform(context: PageContext): Promise<unknown> {
  return apiFetch("/api/shadow/browser/platform", { method: "POST" }, {
    url: context.url,
    platform: context.platform,
    host: context.host,
  });
}

export async function getBrowserExplain(): Promise<unknown> {
  const { lumenApiBase, scopeKey } = await getStorageSettings();
  const response = await fetch(
    apiUrl(lumenApiBase, "/api/shadow/browser/explain", scopeKey),
  );

  if (!response.ok) {
    throw new Error(`Explain request failed (${response.status})`);
  }

  return response.json();
}
