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
import { publishShadowConnected, notifyTabsSessionChanged } from "../shared/connection-state";
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
    await setConnected(false);
    return { ...sessionState };
  }
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

async function handlePageDetected(context: PageContext): Promise<BackgroundResponse> {
  if (!sessionState.connected) {
    return { ok: true, session: { ...sessionState } };
  }

  try {
    await postBrowserPlatform(context);
    await postBrowserContext(context);
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
  await postBrowserContext(context);

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
): Promise<BackgroundResponse> {
  if (!sessionState.connected) {
    return { ok: false, error: "Not connected to Lumen" };
  }

  try {
    const result = await runBrowserAction(action.action, action.context, {
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

async function routeMessage(message: BackgroundMessage): Promise<BackgroundResponse> {
  switch (message.type) {
    case "GET_SESSION":
      return { ok: true, session: await refreshSession() };
    case "CONNECT":
      return handleConnect();
    case "DISCONNECT":
      return handleDisconnect();
    case "PAGE_DETECTED":
      return handlePageDetected(message.context);
    case "POST_CONTEXT":
      return handlePageDetected(message.context);
    case "POST_PLATFORM":
      return handlePageDetected(message.context);
    case "SHADOW_ACTION":
      return handleShadowAction(message);
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

chrome.runtime.onMessage.addListener((message: BackgroundMessage, _sender, sendResponse) => {
  void routeMessage(message).then(sendResponse);
  return true;
});

export {};
