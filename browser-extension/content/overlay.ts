import {
  SESSION_CHANGED_MESSAGE,
  SHADOW_CONNECTED_STORAGE_KEY,
  type SessionChangedMessage,
} from "../shared/connection-state";
import {
  applyPanelPosition,
  clampPanelPosition,
  enablePanelDrag,
  loadPanelPosition,
} from "../shared/panel-position";
import { BrowserPlatform } from "../shared/types";
import type { BackgroundResponse, PageContext, ShadowAction } from "../shared/types";
import { detectPlatform } from "../shared/platforms";

const PANEL_ID = "historyai-shadow-panel";

const ACTIONS: { action: ShadowAction; label: string; youtubeOnly?: boolean }[] = [
  { action: "explain", label: "Explain" },
  { action: "translate", label: "Translate" },
  { action: "summarize", label: "Summarize" },
  { action: "save_to_brain", label: "Save to Brain" },
  { action: "open_watch", label: "Open Watch", youtubeOnly: true },
];

function buildContext(): PageContext {
  const url = window.location.href;
  return {
    url,
    title: document.title,
    platform: detectPlatform(url),
    host: new URL(url).hostname.replace(/^www\./, ""),
  };
}

function injectStyles(): void {
  if (document.getElementById(`${PANEL_ID}-styles`)) {
    return;
  }

  const style = document.createElement("style");
  style.id = `${PANEL_ID}-styles`;
  style.textContent = `
    #${PANEL_ID} {
      all: initial;
      box-sizing: border-box;
      position: fixed;
      right: max(16px, env(safe-area-inset-right));
      bottom: max(16px, env(safe-area-inset-bottom));
      z-index: 2147483646;
      width: min(280px, calc(100vw - 32px - env(safe-area-inset-left) - env(safe-area-inset-right)));
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      font-size: 14px;
      line-height: 1.4;
      color: #e8ecf1;
      background: #1a1f2e;
      border: 1px solid #2d3748;
      border-radius: 14px;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
      overflow: hidden;
      -webkit-tap-highlight-color: transparent;
      touch-action: manipulation;
    }
    #${PANEL_ID}.shadow-panel-positioned {
      right: auto;
      bottom: auto;
      width: min(280px, calc(100vw - 32px - env(safe-area-inset-left) - env(safe-area-inset-right)));
    }
    #${PANEL_ID}.is-dragging {
      box-shadow: 0 16px 48px rgba(0, 0, 0, 0.45);
      user-select: none;
    }
    #${PANEL_ID} * {
      box-sizing: border-box;
    }
    #${PANEL_ID} .shadow-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      padding: 12px 14px;
      background: #121722;
      border-bottom: 1px solid #2d3748;
      font-weight: 600;
      font-size: 12px;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: #94a3b8;
      cursor: grab;
      touch-action: none;
      user-select: none;
    }
    #${PANEL_ID} .shadow-header.is-dragging {
      cursor: grabbing;
    }
    #${PANEL_ID} .shadow-toggle {
      all: unset;
      cursor: pointer;
      min-width: 36px;
      min-height: 36px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      color: #64748b;
      font-size: 18px;
      line-height: 1;
    }
    #${PANEL_ID} .shadow-toggle:hover,
    #${PANEL_ID} .shadow-toggle:focus-visible {
      color: #e8ecf1;
      background: #2d3748;
      outline: none;
    }
    #${PANEL_ID} .shadow-actions {
      display: flex;
      flex-direction: column;
      padding: 8px;
      gap: 4px;
    }
    #${PANEL_ID} .shadow-actions.collapsed { display: none; }
    #${PANEL_ID} button.shadow-action {
      all: unset;
      cursor: pointer;
      display: block;
      width: 100%;
      min-height: 44px;
      padding: 10px 12px;
      border-radius: 10px;
      color: #e8ecf1;
      font-size: 14px;
      text-align: left;
      transition: background 0.15s;
    }
    #${PANEL_ID} button.shadow-action:hover,
    #${PANEL_ID} button.shadow-action:focus-visible {
      background: #2d3748;
      outline: none;
    }
    #${PANEL_ID} .shadow-platform {
      padding: 6px 14px 12px;
      font-size: 11px;
      color: #64748b;
      word-break: break-word;
    }
    #${PANEL_ID} .shadow-platform.collapsed { display: none; }
    @media (max-width: 768px), (pointer: coarse) {
      #${PANEL_ID}:not(.shadow-panel-positioned) {
        left: max(12px, env(safe-area-inset-left));
        right: max(12px, env(safe-area-inset-right));
        bottom: max(12px, env(safe-area-inset-bottom));
        width: auto;
        max-width: none;
        border-radius: 16px;
      }
      #${PANEL_ID} button.shadow-action {
        min-height: 48px;
        font-size: 15px;
        padding: 12px 14px;
      }
    }
  `;
  document.head.appendChild(style);
}

function sendAction(action: ShadowAction): void {
  chrome.runtime
    .sendMessage({ type: "SHADOW_ACTION", action, context: buildContext() })
    .catch(() => {
      // Background unavailable.
    });
}

function removePanel(): void {
  document.getElementById(PANEL_ID)?.remove();
}

function showPanel(): void {
  renderPanel();
}

function hidePanel(): void {
  removePanel();
}

async function isConnectedToLumen(): Promise<boolean> {
  try {
    const response = (await chrome.runtime.sendMessage({
      type: "GET_SESSION",
    })) as BackgroundResponse;

    return Boolean(response.ok && "session" in response && response.session.connected);
  } catch {
    return false;
  }
}

async function syncPanelVisibility(): Promise<void> {
  if (await isConnectedToLumen()) {
    showPanel();
    return;
  }

  hidePanel();
}

function isSessionChangedMessage(message: unknown): message is SessionChangedMessage {
  return (
    typeof message === "object" &&
    message !== null &&
    "type" in message &&
    (message as SessionChangedMessage).type === SESSION_CHANGED_MESSAGE &&
    typeof (message as SessionChangedMessage).connected === "boolean"
  );
}

function renderPanel(): void {
  if (document.getElementById(PANEL_ID)) {
    return;
  }

  injectStyles();

  const context = buildContext();
  const panel = document.createElement("div");
  panel.id = PANEL_ID;
  panel.setAttribute("role", "region");
  panel.setAttribute("aria-label", "HistoryAI Shadow actions");

  const header = document.createElement("div");
  header.className = "shadow-header";
  header.innerHTML = `<span>Shadow</span>`;

  const toggle = document.createElement("button");
  toggle.className = "shadow-toggle";
  toggle.type = "button";
  toggle.textContent = "−";
  toggle.setAttribute("aria-label", "Toggle Shadow panel");
  toggle.setAttribute("aria-expanded", "true");
  header.appendChild(toggle);

  const actions = document.createElement("div");
  actions.className = "shadow-actions";

  for (const item of ACTIONS) {
    if (item.youtubeOnly && context.platform !== BrowserPlatform.Youtube) {
      continue;
    }

    const button = document.createElement("button");
    button.className = "shadow-action";
    button.type = "button";
    button.textContent = item.label;
    button.addEventListener("click", () => sendAction(item.action));
    actions.appendChild(button);
  }

  const platform = document.createElement("div");
  platform.className = "shadow-platform";
  platform.textContent = context.platform.replace(/_/g, " ");

  toggle.addEventListener("click", () => {
    const collapsed = actions.classList.toggle("collapsed");
    platform.classList.toggle("collapsed", collapsed);
    toggle.textContent = collapsed ? "+" : "−";
    toggle.setAttribute("aria-expanded", collapsed ? "false" : "true");
  });

  panel.append(header, actions, platform);
  document.body.appendChild(panel);

  enablePanelDrag(panel, header);

  void loadPanelPosition().then((position) => {
    if (!position || !document.getElementById(PANEL_ID)) {
      return;
    }

    const clamped = clampPanelPosition(panel, position.x, position.y);
    applyPanelPosition(panel, clamped);
  });
}

function applySessionChanged(connected: boolean): void {
  if (connected) {
    showPanel();
    return;
  }

  hidePanel();
}

chrome.runtime.onMessage.addListener((message) => {
  if (!isSessionChangedMessage(message)) {
    return;
  }

  applySessionChanged(message.connected);
});

chrome.storage.session.onChanged.addListener((changes) => {
  if (!(SHADOW_CONNECTED_STORAGE_KEY in changes)) {
    return;
  }

  const nextValue = changes[SHADOW_CONNECTED_STORAGE_KEY]?.newValue;
  applySessionChanged(nextValue === true);
});

function startOverlay(): void {
  void syncPanelVisibility();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", startOverlay);
} else {
  startOverlay();
}

export {};
