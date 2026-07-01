import { describe, expect, it } from "vitest";
import { COMMAND_ITEMS, filterCommandItems } from "./commandItems";

const identityTranslate = (key: string) => key;

describe("commandItems", () => {
	it("includes core navigation commands", () => {
		const ids = COMMAND_ITEMS.map((item) => item.id);

		expect(ids).toContain("upload");
		expect(ids).toContain("workspace");
		expect(ids).toContain("transcript");
		expect(ids).toContain("pipeline");
		expect(ids).toContain("analytics");
	});

	it("filters commands by keyword", () => {
		const results = filterCommandItems("telemetry", identityTranslate);

		expect(results.some((item) => item.id === "analytics")).toBe(true);
	});
});
