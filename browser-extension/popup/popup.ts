import { getLumenBrowserSettingsUrl } from "../shared/api";
import type { BackgroundResponse, BrowserSession } from "../shared/types";

const statusEl = document.getElementById("status") as HTMLDivElement;
const connectBtn = document.getElementById("connect-btn") as HTMLButtonElement;
const disconnectBtn = document.getElementById("disconnect-btn") as HTMLButtonElement;
const settingsLink = document.getElementById("settings-link") as HTMLAnchorElement;

async function loadSettingsLink(): Promise<void> {
  settingsLink.href = await getLumenBrowserSettingsUrl();
}

function renderSession(session: BrowserSession): void {
  if (session.connected) {
    statusEl.textContent = "Connected to Lumen";
    statusEl.className = "status connected";
    connectBtn.style.display = "none";
    disconnectBtn.style.display = "block";
  } else {
    statusEl.textContent = "Not connected";
    statusEl.className = "status disconnected";
    connectBtn.style.display = "block";
    disconnectBtn.style.display = "none";
  }
}

async function sendMessage<T extends BackgroundResponse>(
  message: Record<string, unknown>,
): Promise<T> {
  return chrome.runtime.sendMessage(message) as Promise<T>;
}

async function refreshStatus(): Promise<void> {
  try {
    const response = await sendMessage<BackgroundResponse>({ type: "GET_SESSION" });
    if (response.ok && "session" in response) {
      renderSession(response.session);
    }
  } catch {
    statusEl.textContent = "Extension error";
    statusEl.className = "status disconnected";
  }
}

connectBtn.addEventListener("click", async () => {
  connectBtn.disabled = true;
  const response = await sendMessage<BackgroundResponse>({ type: "CONNECT" });
  connectBtn.disabled = false;

  if (response.ok && "session" in response) {
    renderSession(response.session);
  } else if (!response.ok) {
    statusEl.textContent = response.error;
  }
});

disconnectBtn.addEventListener("click", async () => {
  disconnectBtn.disabled = true;
  const response = await sendMessage<BackgroundResponse>({ type: "DISCONNECT" });
  disconnectBtn.disabled = false;

  if (response.ok && "session" in response) {
    renderSession(response.session);
  }
});

void loadSettingsLink();
void refreshStatus();
