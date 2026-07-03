export const SHADOW_PANEL_POSITION_KEY = "shadowPanelPosition";

export interface PanelPosition {
  x: number;
  y: number;
}

export async function loadPanelPosition(): Promise<PanelPosition | null> {
  const stored = await chrome.storage.local.get(SHADOW_PANEL_POSITION_KEY);
  const value = stored[SHADOW_PANEL_POSITION_KEY];

  if (
    typeof value !== "object" ||
    value === null ||
    typeof (value as PanelPosition).x !== "number" ||
    typeof (value as PanelPosition).y !== "number"
  ) {
    return null;
  }

  return value as PanelPosition;
}

export async function savePanelPosition(position: PanelPosition): Promise<void> {
  await chrome.storage.local.set({ [SHADOW_PANEL_POSITION_KEY]: position });
}

export function clampPanelPosition(
  panel: HTMLElement,
  x: number,
  y: number,
): PanelPosition {
  const margin = 8;
  const safeLeft = readSafeInset("env(safe-area-inset-left)", 0);
  const safeTop = readSafeInset("env(safe-area-inset-top)", 0);
  const safeRight = readSafeInset("env(safe-area-inset-right)", 0);
  const safeBottom = readSafeInset("env(safe-area-inset-bottom)", 0);

  const maxX = window.innerWidth - panel.offsetWidth - margin - safeRight;
  const maxY = window.innerHeight - panel.offsetHeight - margin - safeBottom;
  const minX = margin + safeLeft;
  const minY = margin + safeTop;

  return {
    x: Math.min(Math.max(x, minX), Math.max(minX, maxX)),
    y: Math.min(Math.max(y, minY), Math.max(minY, maxY)),
  };
}

function readSafeInset(variable: string, fallback: number): number {
  const probe = document.createElement("div");
  probe.style.position = "fixed";
  probe.style.visibility = "hidden";
  probe.style.paddingLeft = `calc(0px + ${variable})`;
  document.body.appendChild(probe);
  const value = Number.parseFloat(getComputedStyle(probe).paddingLeft) || fallback;
  probe.remove();
  return value;
}

export function applyPanelPosition(panel: HTMLElement, position: PanelPosition): void {
  panel.classList.add("shadow-panel-positioned");
  panel.style.left = `${position.x}px`;
  panel.style.top = `${position.y}px`;
  panel.style.right = "auto";
  panel.style.bottom = "auto";
}

export function enablePanelDrag(panel: HTMLElement, handle: HTMLElement): void {
  let dragging = false;
  let offsetX = 0;
  let offsetY = 0;

  const onPointerMove = (event: PointerEvent): void => {
    if (!dragging) {
      return;
    }

    const next = clampPanelPosition(panel, event.clientX - offsetX, event.clientY - offsetY);
    applyPanelPosition(panel, next);
  };

  const stopDragging = (event: PointerEvent): void => {
    if (!dragging) {
      return;
    }

    dragging = false;
    handle.classList.remove("is-dragging");
    panel.classList.remove("is-dragging");

    const left = Number.parseFloat(panel.style.left);
    const top = Number.parseFloat(panel.style.top);
    if (Number.isFinite(left) && Number.isFinite(top)) {
      void savePanelPosition({ x: left, y: top });
    }

    handle.releasePointerCapture?.(event.pointerId);
    window.removeEventListener("pointermove", onPointerMove);
    window.removeEventListener("pointerup", stopDragging);
    window.removeEventListener("pointercancel", stopDragging);
  };

  handle.addEventListener("pointerdown", (event: PointerEvent) => {
    if (event.button !== 0 || (event.target as HTMLElement).closest(".shadow-toggle")) {
      return;
    }

    const rect = panel.getBoundingClientRect();
    if (!panel.classList.contains("shadow-panel-positioned")) {
      applyPanelPosition(panel, { x: rect.left, y: rect.top });
    }

    dragging = true;
    offsetX = event.clientX - rect.left;
    offsetY = event.clientY - rect.top;
    handle.classList.add("is-dragging");
    panel.classList.add("is-dragging");
    handle.setPointerCapture(event.pointerId);

    window.addEventListener("pointermove", onPointerMove);
    window.addEventListener("pointerup", stopDragging);
    window.addEventListener("pointercancel", stopDragging);
    event.preventDefault();
  });
}
