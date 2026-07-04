import {
  connectBrowser,
  disconnectBrowser,
  getBrowserSession,
  getStorageSettings,
  postBrowserContext,
  postBrowserExplain,
  postBrowserOpenWatch,
  postBrowserPlatform,
  postBrowserSave,
  postBrowserSummarize,
  postBrowserTranslate,
} from "../shared/api";
import { publishShadowConnected, notifyTabsSessionChanged, readShadowConnected } from "../shared/connection-state";
import { isBrowserSessionActive } from "../shared/session";
import type {
  BackgroundMessage,
  BackgroundResponse,
  BrowserActionResult,
  BrowserSession,
  PageContext,
  ShadowAction,
} from "../shared/types";

const sessionState: BrowserSession = {
  connected: false,
  scopeKey: "default",
};

async function setConnected(connected: boolean): Promise<void> {
  sessionState.connected = connected;
  await publishShadowConnected(connected);
  await notifyTabsSessionChanged(connected);
}

async function refreshSession(): Promise<BrowserSession> {
  try {
    const data = (await getBrowserSession()) as Record<string, unknown>;
    await setConnected(isBrowserSessionActive(data));
    sessionState.workspace = data;
    return { ...sessionState };
  } catch {
    return { ...sessionState, connected: sessionState.connected };
  }
}

async function ensureConnected(): Promise<boolean> {
  const session = await refreshSession();
  if (session.connected) {
    return true;
  }

  const reconnect = await handleConnect();
  return reconnect.ok && (reconnect.session?.connected ?? false);
}

async function handleConnect(): Promise<BackgroundResponse> {
  try {
    const workspace = await connectBrowser(sessionState.shadowSessionId);
    const data = workspace as Record<string, unknown>;
    await setConnected(isBrowserSessionActive(normalizeSessionPayload(data)));
    sessionState.workspace = data;
    return { ok: true, session: { ...sessionState } };
  } catch (error) {
    return {
      ok: false,
      error: error instanceof Error ? error.message : "Connect failed",
    };
  }
}

function normalizeSessionPayload(data: Record<string, unknown>): Record<string, unknown> {
  const nested = data.session;
  if (nested && typeof nested === "object" && "active" in nested) {
    return nested as Record<string, unknown>;
  }

  return data;
}

async function handleDisconnect(): Promise<BackgroundResponse> {
  try {
    await disconnectBrowser();
  } catch (error) {
    const message = error instanceof Error ? error.message : "Disconnect failed";
    if (!message.includes("No active browser session")) {
      return { ok: false, error: message };
    }
  }

  await setConnected(false);
  sessionState.workspace = undefined;
  return { ok: true, session: { ...sessionState } };
}

async function resolveTabId(
  context: PageContext,
  sender?: chrome.runtime.MessageSender,
): Promise<string> {
  if (context.tabId) {
    return context.tabId;
  }

  if (sender?.tab?.id !== undefined) {
    return String(sender.tab.id);
  }

  const [tab] = await chrome.tabs.query({ active: true, currentWindow: true });
  if (tab?.id !== undefined) {
    return String(tab.id);
  }

  throw new Error("Could not resolve browser tab id.");
}

async function withTabContext(
  context: PageContext,
  sender?: chrome.runtime.MessageSender,
): Promise<PageContext> {
  return { ...context, tabId: await resolveTabId(context, sender) };
}

async function handlePageDetected(
  context: PageContext,
  sender?: chrome.runtime.MessageSender,
): Promise<BackgroundResponse> {
  if (!(await ensureConnected())) {
    return { ok: true, session: { ...sessionState } };
  }

  try {
    const enriched = await withTabContext(context, sender);
    await postBrowserPlatform(enriched);
    await postBrowserContext(enriched);
    return { ok: true, data: { synced: true } };
  } catch (error) {
    return {
      ok: false,
      error: error instanceof Error ? error.message : "Sync failed",
    };
  }
}

async function openWatchTab(watchPath: string): Promise<void> {
  const { lumenWebBase } = await getStorageSettings();
  const base = lumenWebBase.replace(/\/$/, "");
  await chrome.tabs.create({ url: `${base}${watchPath}` });
}

async function runBrowserAction(
  action: ShadowAction,
  context: PageContext,
  options?: { language?: string; importConfirmed?: boolean },
): Promise<BrowserActionResult> {
  if (action !== "open_watch" || !options?.importConfirmed) {
    await postBrowserContext(context);
  }

  switch (action) {
    case "explain":
      return postBrowserExplain(context);
    case "translate":
      return postBrowserTranslate(context, options?.language ?? "fr");
    case "summarize":
      return postBrowserSummarize(context);
    case "save_to_brain":
      return postBrowserSave(context);
    case "open_watch": {
      const result = await postBrowserOpenWatch(context, {
        importConfirmed: options?.importConfirmed ?? false,
      });

      if (result.watchPath && result.status !== "confirmation_required") {
        await openWatchTab(result.watchPath);
      }

      return result;
    }
    default:
      throw new Error(`Unknown action: ${String(action)}`);
  }
}

async function handleShadowAction(
  action: BackgroundMessage & { type: "SHADOW_ACTION" },
  sender?: chrome.runtime.MessageSender,
): Promise<BackgroundResponse> {
  if (!(await ensureConnected())) {
    return { ok: false, error: "Not connected to Lumen" };
  }

  try {
    const enriched = await withTabContext(action.context, sender);
    const result = await runBrowserAction(action.action, enriched, {
      language: action.language,
      importConfirmed: action.importConfirmed,
    });

    return { ok: true, data: result };
  } catch (error) {
    return {
      ok: false,
      error: error instanceof Error ? error.message : "Action failed",
    };
  }
}

async function routeMessage(
  message: BackgroundMessage,
  sender?: chrome.runtime.MessageSender,
): Promise<BackgroundResponse> {
  switch (message.type) {
    case "GET_SESSION":
      return { ok: true, session: await refreshSession() };
    case "CONNECT":
      return handleConnect();
    case "DISCONNECT":
      return handleDisconnect();
    case "PAGE_DETECTED":
      return handlePageDetected(message.context, sender);
    case "POST_CONTEXT":
      return handlePageDetected(message.context, sender);
    case "POST_PLATFORM":
      return handlePageDetected(message.context, sender);
    case "SHADOW_ACTION":
      return handleShadowAction(message, sender);
    default:
      return { ok: false, error: "Unknown message type" };
  }
}

chrome.runtime.onInstalled.addListener((details) => {
  if (details.reason !== "install") {
    return;
  }

  void handleConnect().catch(async () => {
    await setConnected(false);
  });
});

void readShadowConnected().then((connected) => {
  if (!connected) {
    return;
  }

  void refreshSession();
});

chrome.runtime.onMessage.addListener((message: BackgroundMessage, sender, sendResponse) => {
  void routeMessage(message, sender).then(sendResponse);
  return true;
});

export {};
