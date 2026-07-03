export const BrowserPlatform = {
  Youtube: "youtube",
  Wikipedia: "wikipedia",
  Mdn: "mdn",
  SymfonyDocs: "symfony_docs",
  PhpDocs: "php_docs",
  Github: "github",
  Gitlab: "gitlab",
  Stackoverflow: "stackoverflow",
  Reddit: "reddit",
  PdfViewer: "pdf_viewer",
  Unknown: "unknown",
} as const;

export type BrowserPlatform = (typeof BrowserPlatform)[keyof typeof BrowserPlatform];

export type ShadowAction =
  | "explain"
  | "translate"
  | "summarize"
  | "save_to_brain"
  | "open_watch";

export interface PageContext {
  url: string;
  title: string;
  platform: BrowserPlatform;
  host: string;
}

export interface BrowserSession {
  connected: boolean;
  scopeKey: string;
  shadowSessionId?: string;
  workspace?: Record<string, unknown>;
}

export interface StorageSettings {
  lumenApiBase: string;
  scopeKey: string;
}

export type BackgroundMessage =
  | { type: "GET_SESSION" }
  | { type: "CONNECT" }
  | { type: "DISCONNECT" }
  | { type: "PAGE_DETECTED"; context: PageContext }
  | { type: "SHADOW_ACTION"; action: ShadowAction; context: PageContext }
  | { type: "POST_CONTEXT"; context: PageContext }
  | { type: "POST_PLATFORM"; context: PageContext };

export type BackgroundResponse =
  | { ok: true; session: BrowserSession }
  | { ok: true; data: unknown }
  | { ok: false; error: string };
