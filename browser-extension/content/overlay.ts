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
import {
  BrowserPlatform,
  type BackgroundResponse,
  type BrowserActionResult,
  type PageContext,
  type ShadowAction,
} from "../shared/types";
import { detectPlatform } from "../shared/platforms";

const PANEL_ID = "historyai-shadow-panel";
const RESULT_ID = "historyai-shadow-result";
const TOAST_HOST_ID = "historyai-shadow-toast-host";
const DIALOG_HOST_ID = "historyai-shadow-dialog-host";

const TRANSLATE_LANGUAGES = [
  { code: "en", label: "English", flag: "🇬🇧" },
  { code: "fr", label: "Français", flag: "🇫🇷" },
  { code: "de", label: "Deutsch", flag: "🇩🇪" },
] as const;

const ACTIONS: { action: ShadowAction; label: string; youtubeOnly?: boolean }[] = [
  { action: "explain", label: "Explain" },
  { action: "translate", label: "Translate" },
  { action: "summarize", label: "Summarize" },
  { action: "save_to_brain", label: "Save to Brain" },
  { action: "open_watch", label: "Open Watch", youtubeOnly: true },
];

let pendingAction: ShadowAction | null = null;

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
      width: min(320px, calc(100vw - 32px - env(safe-area-inset-left) - env(safe-area-inset-right)));
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
      width: min(320px, calc(100vw - 32px - env(safe-area-inset-left) - env(safe-area-inset-right)));
    }
    #${PANEL_ID}.is-dragging {
      box-shadow: 0 16px 48px rgba(0, 0, 0, 0.45);
      user-select: none;
    }
    #${PANEL_ID} * { box-sizing: border-box; }
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
    #${PANEL_ID} .shadow-header.is-dragging { cursor: grabbing; }
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
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      min-height: 44px;
      padding: 10px 12px;
      border-radius: 10px;
      color: #e8ecf1;
      font-size: 14px;
      text-align: left;
      transition: background 0.15s;
    }
    #${PANEL_ID} button.shadow-action:hover:not(:disabled),
    #${PANEL_ID} button.shadow-action:focus-visible:not(:disabled) {
      background: #2d3748;
      outline: none;
    }
    #${PANEL_ID} button.shadow-action:disabled {
      opacity: 0.65;
      cursor: wait;
    }
    #${PANEL_ID} button.shadow-action .shadow-spinner {
      width: 14px;
      height: 14px;
      border: 2px solid #64748b;
      border-top-color: #e8ecf1;
      border-radius: 50%;
      animation: shadow-spin 0.7s linear infinite;
    }
    @keyframes shadow-spin { to { transform: rotate(360deg); } }
    #${PANEL_ID} .shadow-result {
      display: none;
      padding: 0 12px 12px;
      border-top: 1px solid #2d3748;
      max-height: 240px;
      overflow-y: auto;
    }
    #${PANEL_ID} .shadow-result.is-visible { display: block; }
    #${PANEL_ID} .shadow-result-title {
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: #94a3b8;
      margin: 10px 0 6px;
    }
    #${PANEL_ID} .shadow-result-body {
      font-size: 13px;
      color: #cbd5e1;
      white-space: pre-wrap;
      word-break: break-word;
    }
    #${PANEL_ID} .shadow-result-meta {
      margin-top: 8px;
      font-size: 11px;
      color: #64748b;
    }
    #${PANEL_ID} .shadow-result-close {
      all: unset;
      cursor: pointer;
      margin-top: 8px;
      font-size: 12px;
      color: #94a3b8;
    }
    #${PANEL_ID} .shadow-result-close:hover { color: #e8ecf1; }
    #${PANEL_ID} .shadow-platform {
      padding: 6px 14px 12px;
      font-size: 11px;
      color: #64748b;
      word-break: break-word;
    }
    #${PANEL_ID} .shadow-platform.collapsed { display: none; }
    #${TOAST_HOST_ID} {
      position: fixed;
      right: max(16px, env(safe-area-inset-right));
      bottom: calc(max(16px, env(safe-area-inset-bottom)) + 80px);
      z-index: 2147483647;
      display: flex;
      flex-direction: column;
      gap: 8px;
      pointer-events: none;
    }
    #${DIALOG_HOST_ID} {
      position: fixed;
      inset: 0;
      z-index: 2147483647;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0, 0, 0, 0.45);
      padding: 16px;
    }
    #${DIALOG_HOST_ID}:empty { display: none; }
    .shadow-dialog {
      width: min(360px, 100%);
      background: #1a1f2e;
      border: 1px solid #2d3748;
      border-radius: 14px;
      padding: 16px;
      color: #e8ecf1;
      font-family: system-ui, sans-serif;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
    }
    .shadow-dialog h3 {
      margin: 0 0 8px;
      font-size: 15px;
    }
    .shadow-dialog p {
      margin: 0 0 14px;
      font-size: 13px;
      color: #94a3b8;
    }
    .shadow-dialog-actions {
      display: flex;
      gap: 8px;
      justify-content: flex-end;
    }
    .shadow-dialog-actions button {
      all: unset;
      cursor: pointer;
      padding: 8px 14px;
      border-radius: 8px;
      font-size: 13px;
    }
    .shadow-dialog-actions .primary {
      background: #3b82f6;
      color: #fff;
    }
    .shadow-dialog-actions .secondary {
      background: #2d3748;
      color: #e8ecf1;
    }
    .shadow-language-list {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-bottom: 12px;
    }
    .shadow-language-list button {
      all: unset;
      cursor: pointer;
      padding: 10px 12px;
      border-radius: 8px;
      background: #121722;
      border: 1px solid #2d3748;
    }
    .shadow-language-list button:hover { background: #2d3748; }
    .shadow-toast {
      padding: 10px 14px;
      border-radius: 10px;
      font-family: system-ui, sans-serif;
      font-size: 13px;
      color: #e8ecf1;
      background: #121722;
      border: 1px solid #2d3748;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
      animation: shadow-toast-in 0.2s ease;
    }
    .shadow-toast.success { border-color: #22c55e; }
    .shadow-toast.warning { border-color: #f59e0b; }
    .shadow-toast.error { border-color: #ef4444; }
    @keyframes shadow-toast-in {
      from { opacity: 0; transform: translateY(8px); }
      to { opacity: 1; transform: translateY(0); }
    }
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

function ensureToastHost(): HTMLElement {
  let host = document.getElementById(TOAST_HOST_ID);
  if (!host) {
    host = document.createElement("div");
    host.id = TOAST_HOST_ID;
    document.body.appendChild(host);
  }
  return host;
}

function ensureDialogHost(): HTMLElement {
  let host = document.getElementById(DIALOG_HOST_ID);
  if (!host) {
    host = document.createElement("div");
    host.id = DIALOG_HOST_ID;
    document.body.appendChild(host);
  }
  return host;
}

function showToast(message: string, variant: "success" | "warning" | "error" = "success"): void {
  const host = ensureToastHost();
  const toast = document.createElement("div");
  toast.className = `shadow-toast ${variant}`;
  toast.textContent = message;
  host.appendChild(toast);

  window.setTimeout(() => {
    toast.remove();
  }, 3500);
}

function clearDialog(): void {
  ensureDialogHost().replaceChildren();
}

function showImportDialog(message: string): Promise<boolean> {
  return new Promise((resolve) => {
    const host = ensureDialogHost();
    host.replaceChildren();

    const dialog = document.createElement("div");
    dialog.className = "shadow-dialog";
    dialog.setAttribute("role", "dialog");
    dialog.setAttribute("aria-modal", "true");

    const title = document.createElement("h3");
    title.textContent = "Import this YouTube video?";

    const body = document.createElement("p");
    body.textContent = message;

    const actions = document.createElement("div");
    actions.className = "shadow-dialog-actions";

    const cancel = document.createElement("button");
    cancel.className = "secondary";
    cancel.type = "button";
    cancel.textContent = "Cancel";
    cancel.addEventListener("click", () => {
      clearDialog();
      resolve(false);
    });

    const confirm = document.createElement("button");
    confirm.className = "primary";
    confirm.type = "button";
    confirm.textContent = "Import";
    confirm.addEventListener("click", () => {
      clearDialog();
      resolve(true);
    });

    actions.append(cancel, confirm);
    dialog.append(title, body, actions);
    host.appendChild(dialog);
  });
}

function showLanguagePicker(): Promise<string | null> {
  return new Promise((resolve) => {
    const host = ensureDialogHost();
    host.replaceChildren();

    const dialog = document.createElement("div");
    dialog.className = "shadow-dialog";
    dialog.setAttribute("role", "dialog");
    dialog.setAttribute("aria-modal", "true");

    const title = document.createElement("h3");
    title.textContent = "Choose language";

    const list = document.createElement("div");
    list.className = "shadow-language-list";

    for (const lang of TRANSLATE_LANGUAGES) {
      const button = document.createElement("button");
      button.type = "button";
      button.textContent = `${lang.flag} ${lang.label}`;
      button.addEventListener("click", () => {
        clearDialog();
        resolve(lang.code);
      });
      list.appendChild(button);
    }

    const actions = document.createElement("div");
    actions.className = "shadow-dialog-actions";

    const cancel = document.createElement("button");
    cancel.className = "secondary";
    cancel.type = "button";
    cancel.textContent = "Cancel";
    cancel.addEventListener("click", () => {
      clearDialog();
      resolve(null);
    });

    actions.appendChild(cancel);
    dialog.append(title, list, actions);
    host.appendChild(dialog);
  });
}

function setActionLoading(action: ShadowAction, loading: boolean): void {
  const button = document.querySelector<HTMLButtonElement>(
    `#${PANEL_ID} button.shadow-action[data-action="${action}"]`,
  );

  if (!button) {
    return;
  }

  button.disabled = loading;
  const existing = button.querySelector(".shadow-spinner");
  existing?.remove();

  if (loading) {
    const spinner = document.createElement("span");
    spinner.className = "shadow-spinner";
    spinner.setAttribute("aria-hidden", "true");
    button.appendChild(spinner);
  }
}

function getResultPanel(): HTMLElement | null {
  return document.getElementById(RESULT_ID);
}

function hideResult(): void {
  const panel = getResultPanel();
  if (!panel) {
    return;
  }

  panel.classList.remove("is-visible");
  panel.replaceChildren();
}

function showResult(title: string, body: string, meta?: string): void {
  const panel = getResultPanel();
  if (!panel) {
    return;
  }

  panel.replaceChildren();

  const heading = document.createElement("div");
  heading.className = "shadow-result-title";
  heading.textContent = title;

  const content = document.createElement("div");
  content.className = "shadow-result-body";
  content.textContent = body;

  const close = document.createElement("button");
  close.className = "shadow-result-close";
  close.type = "button";
  close.textContent = "Close";
  close.addEventListener("click", hideResult);

  panel.append(heading, content);

  if (meta) {
    const metaEl = document.createElement("div");
    metaEl.className = "shadow-result-meta";
    metaEl.textContent = meta;
    panel.appendChild(metaEl);
  }

  panel.appendChild(close);
  panel.classList.add("is-visible");
}

async function sendAction(
  action: ShadowAction,
  options?: { language?: string; importConfirmed?: boolean },
): Promise<BackgroundResponse> {
  return chrome.runtime.sendMessage({
    type: "SHADOW_ACTION",
    action,
    context: buildContext(),
    language: options?.language,
    importConfirmed: options?.importConfirmed,
  }) as Promise<BackgroundResponse>;
}

function isActionResult(data: unknown): data is BrowserActionResult {
  return (
    typeof data === "object" &&
    data !== null &&
    "action" in data &&
    "status" in data
  );
}

function presentActionResult(result: BrowserActionResult): void {
  switch (result.action) {
    case "save_to_brain":
      showToast(result.message ?? "✓ Saved to Second Brain", "success");
      return;
    case "open_watch":
      if (result.status === "processing") {
        showToast(result.message ?? "✓ Shadow imported this video", "success");
        return;
      }
      if (result.status === "completed") {
        showToast(result.message ?? "✓ Watch ready", "success");
        return;
      }
      if (result.status === "unavailable" || result.status === "error") {
        showToast(result.message ?? "Open Watch unavailable", "error");
        return;
      }
      return;
    default:
      break;
  }

  const body = result.body ?? result.summary ?? result.message ?? "Done.";
  const metaParts: string[] = [];

  if (result.estimatedLevel) {
    metaParts.push(`Level: ${result.estimatedLevel}`);
  }

  if (result.concepts?.length) {
    metaParts.push(`Concepts: ${result.concepts.slice(0, 3).join(", ")}`);
  }

  if (result.language) {
    metaParts.push(`Language: ${result.language.toUpperCase()}`);
  }

  const titles: Record<ShadowAction, string> = {
    explain: "Explanation",
    translate: "Translation",
    summarize: "Summary",
    save_to_brain: "Saved",
    open_watch: "Watch",
  };

  showResult(titles[result.action], body, metaParts.join(" · ") || undefined);
}

async function handleAction(action: ShadowAction): Promise<void> {
  if (pendingAction) {
    return;
  }

  pendingAction = action;
  setActionLoading(action, true);
  showResult("Loading...", "Processing your request with Shadow.");

  try {
    if (action === "translate") {
      hideResult();
      const language = await showLanguagePicker();
      if (!language) {
        return;
      }

      setActionLoading(action, true);
      showResult("Loading...", "Translating...");

      const response = await sendAction(action, { language });
      if (!response.ok) {
        showToast(response.error, "error");
        hideResult();
        return;
      }

      if (isActionResult(response.data)) {
        presentActionResult(response.data);
      }
      return;
    }

    if (action === "open_watch") {
      hideResult();
      showToast("Checking import status...", "warning");

      const initial = await sendAction(action);
      if (!initial.ok) {
        showToast(initial.error, "error");
        return;
      }

      if (!isActionResult(initial.data)) {
        return;
      }

      if (initial.data.status === "confirmation_required") {
        const confirmed = await showImportDialog(
          initial.data.message ?? "Import this YouTube video into Lumen?",
        );

        if (!confirmed) {
          showToast("Import cancelled", "warning");
          return;
        }

        setActionLoading(action, true);
        showToast("Importing... Preparing Shadow Watch.", "warning");

        const imported = await sendAction(action, { importConfirmed: true });
        if (!imported.ok) {
          showToast(imported.error, "error");
          return;
        }

        if (isActionResult(imported.data)) {
          presentActionResult(imported.data);
        }
        return;
      }

      presentActionResult(initial.data);
      return;
    }

    const response = await sendAction(action);
    if (!response.ok) {
      showToast(response.error, "error");
      hideResult();
      return;
    }

    if (isActionResult(response.data)) {
      presentActionResult(response.data);
    }
  } finally {
    setActionLoading(action, false);
    pendingAction = null;
  }
}

function removePanel(): void {
  document.getElementById(PANEL_ID)?.remove();
  document.getElementById(TOAST_HOST_ID)?.remove();
  clearDialog();
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
    button.dataset.action = item.action;
    button.textContent = item.label;
    button.addEventListener("click", () => {
      void handleAction(item.action);
    });
    actions.appendChild(button);
  }

  const result = document.createElement("div");
  result.id = RESULT_ID;
  result.className = "shadow-result";

  const platform = document.createElement("div");
  platform.className = "shadow-platform";
  platform.textContent = context.platform.replace(/_/g, " ");

  toggle.addEventListener("click", () => {
    const collapsed = actions.classList.toggle("collapsed");
    platform.classList.toggle("collapsed", collapsed);
    result.classList.toggle("collapsed", collapsed);
    toggle.textContent = collapsed ? "+" : "−";
    toggle.setAttribute("aria-expanded", collapsed ? "false" : "true");
  });

  panel.append(header, actions, result, platform);
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

document.addEventListener("keydown", (event) => {
  if (event.key !== "Escape") {
    return;
  }

  clearDialog();
  hideResult();
});

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
