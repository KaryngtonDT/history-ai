export const SHADOW_CONNECTED_STORAGE_KEY = "shadowBrowserConnected";

export async function publishShadowConnected(connected: boolean): Promise<void> {
  await chrome.storage.session.set({ [SHADOW_CONNECTED_STORAGE_KEY]: connected });
}

export async function readShadowConnected(): Promise<boolean> {
  const stored = await chrome.storage.session.get(SHADOW_CONNECTED_STORAGE_KEY);
  return stored[SHADOW_CONNECTED_STORAGE_KEY] === true;
}
