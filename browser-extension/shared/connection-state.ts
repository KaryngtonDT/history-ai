export const SHADOW_CONNECTED_STORAGE_KEY = "shadowBrowserConnected";

export const SESSION_CHANGED_MESSAGE = "SESSION_CHANGED" as const;

export interface SessionChangedMessage {
  type: typeof SESSION_CHANGED_MESSAGE;
  connected: boolean;
}

export async function publishShadowConnected(connected: boolean): Promise<void> {
  await chrome.storage.session.set({ [SHADOW_CONNECTED_STORAGE_KEY]: connected });
}

export async function notifyTabsSessionChanged(connected: boolean): Promise<void> {
  const tabs = await chrome.tabs.query({
    url: ["http://*/*", "https://*/*"],
  });

  const message: SessionChangedMessage = {
    type: SESSION_CHANGED_MESSAGE,
    connected,
  };

  await Promise.all(
    tabs.map(async (tab) => {
      if (!tab.id) {
        return;
      }

      try {
        await chrome.tabs.sendMessage(tab.id, message);
      } catch {
        // Tab may not have the content script yet.
      }
    }),
  );
}

export async function readShadowConnected(): Promise<boolean> {
  const stored = await chrome.storage.session.get(SHADOW_CONNECTED_STORAGE_KEY);
  return stored[SHADOW_CONNECTED_STORAGE_KEY] === true;
}
