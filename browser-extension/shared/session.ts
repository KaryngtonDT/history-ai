export function isBrowserSessionActive(data: Record<string, unknown>): boolean {
  if (data.active === true) {
    return true;
  }

  const session = data.session;
  if (!session || typeof session !== "object") {
    return false;
  }

  return (session as { state?: string }).state === "connected";
}
