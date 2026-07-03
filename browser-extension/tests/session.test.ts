import { describe, expect, it } from "vitest";
import { isBrowserSessionActive } from "../shared/session";

describe("isBrowserSessionActive", () => {
  it("returns true when active and session.state is connected", () => {
    expect(
      isBrowserSessionActive({
        active: true,
        session: { state: "connected" },
      }),
    ).toBe(true);
  });

  it("returns false when active is true but session.state is disconnected", () => {
    expect(
      isBrowserSessionActive({
        active: true,
        session: { state: "disconnected" },
      }),
    ).toBe(false);
  });

  it("returns false when active is true without session payload", () => {
    expect(isBrowserSessionActive({ active: true })).toBe(false);
  });

  it("returns false when session is missing or disconnected", () => {
    expect(isBrowserSessionActive({ active: false })).toBe(false);
    expect(
      isBrowserSessionActive({
        active: false,
        session: { state: "connected" },
      }),
    ).toBe(false);
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
