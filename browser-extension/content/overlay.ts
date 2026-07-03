import { BrowserPlatform } from "../shared/types";
import type { PageContext, ShadowAction } from "../shared/types";
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
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 2147483646;
      font-family: system-ui, -apple-system, sans-serif;
      font-size: 13px;
      color: #e8ecf1;
      background: #1a1f2e;
      border: 1px solid #2d3748;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
      min-width: 200px;
      overflow: hidden;
    }
    #${PANEL_ID} .shadow-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 12px;
      background: #121722;
      border-bottom: 1px solid #2d3748;
      font-weight: 600;
      font-size: 12px;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: #94a3b8;
    }
    #${PANEL_ID} .shadow-toggle {
      all: unset;
      cursor: pointer;
      padding: 2px 6px;
      border-radius: 4px;
      color: #64748b;
    }
    #${PANEL_ID} .shadow-toggle:hover { color: #e8ecf1; }
    #${PANEL_ID} .shadow-actions {
      display: flex;
      flex-direction: column;
      padding: 6px;
      gap: 2px;
    }
    #${PANEL_ID} .shadow-actions.collapsed { display: none; }
    #${PANEL_ID} button.shadow-action {
      all: unset;
      cursor: pointer;
      padding: 8px 10px;
      border-radius: 8px;
      color: #e8ecf1;
      transition: background 0.15s;
    }
    #${PANEL_ID} button.shadow-action:hover { background: #2d3748; }
    #${PANEL_ID} .shadow-platform {
      padding: 4px 12px 8px;
      font-size: 11px;
      color: #64748b;
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

function renderPanel(): void {
  if (document.getElementById(PANEL_ID)) {
    return;
  }

  injectStyles();

  const context = buildContext();
  const panel = document.createElement("div");
  panel.id = PANEL_ID;

  const header = document.createElement("div");
  header.className = "shadow-header";
  header.innerHTML = `<span>Shadow</span>`;

  const toggle = document.createElement("button");
  toggle.className = "shadow-toggle";
  toggle.textContent = "−";
  toggle.setAttribute("aria-label", "Toggle panel");
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
    toggle.textContent = collapsed ? "+" : "−";
  });

  panel.append(header, actions, platform);
  document.body.appendChild(panel);
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", renderPanel);
} else {
  renderPanel();
}

export {};
