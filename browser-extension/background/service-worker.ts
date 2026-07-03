import {
  connectBrowser,
  disconnectBrowser,
  getBrowserSession,
  postBrowserContext,
  postBrowserPlatform,
} from "../shared/api";
import { isBrowserSessionActive } from "../shared/session";
import type {
  BackgroundMessage,
  BackgroundResponse,
  BrowserSession,
  PageContext,
} from "../shared/types";

const sessionState: BrowserSession = {
  connected: false,
  scopeKey: "default",
};

async function refreshSession(): Promise<BrowserSession> {
  try {
    const data = (await getBrowserSession()) as Record<string, unknown>;
    sessionState.connected = isBrowserSessionActive(data);
    sessionState.workspace = data;
    return { ...sessionState };
  } catch {
    sessionState.connected = false;
    return { ...sessionState };
  }
}

async function handleConnect(): Promise<BackgroundResponse> {
  try {
    const workspace = await connectBrowser(sessionState.shadowSessionId);
    const data = workspace as Record<string, unknown>;
    sessionState.connected = isBrowserSessionActive(normalizeSessionPayload(data));
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

  sessionState.connected = false;
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

async function handleShadowAction(
  action: BackgroundMessage & { type: "SHADOW_ACTION" },
): Promise<BackgroundResponse> {
  if (!sessionState.connected) {
    return { ok: false, error: "Not connected to Lumen" };
  }

  try {
    await postBrowserContext(action.context);
    return { ok: true, data: { action: action.action, queued: true } };
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

  void handleConnect().catch(() => {
    sessionState.connected = false;
  });
});

chrome.runtime.onMessage.addListener((message: BackgroundMessage, _sender, sendResponse) => {
  void routeMessage(message).then(sendResponse);
  return true;
});

export {};
