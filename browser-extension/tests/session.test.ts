import { describe, expect, it } from "vitest";
import { isBrowserSessionActive } from "../shared/session";

describe("isBrowserSessionActive", () => {
  it("returns true when active is true", () => {
    expect(isBrowserSessionActive({ active: true })).toBe(true);
  });

  it("returns true when session.state is connected", () => {
    expect(
      isBrowserSessionActive({
        active: false,
        session: { state: "connected" },
      }),
    ).toBe(true);
  });

  it("returns false when session is missing or disconnected", () => {
    expect(isBrowserSessionActive({ active: false })).toBe(false);
    expect(
      isBrowserSessionActive({
        session: { state: "disconnected" },
      }),
    ).toBe(false);
  });

  it("returns false for legacy connected field only", () => {
    expect(isBrowserSessionActive({ connected: true })).toBe(false);
  });
});
