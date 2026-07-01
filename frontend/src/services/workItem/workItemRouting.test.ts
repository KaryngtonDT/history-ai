import { describe, expect, it } from "vitest";
import {
	resolveWorkItemRoute,
	workItemFallbackMessage,
} from "./workItemRouting";

describe("workItemRouting", () => {
	it("routes video to overview", () => {
		expect(resolveWorkItemRoute("video", "abc")).toBe("/video/abc");
	});

	it("routes pdf to processing", () => {
		expect(resolveWorkItemRoute("pdf", "1")).toBe("/processing/1");
	});

	it("provides fallback messages", () => {
		expect(workItemFallbackMessage("video")).toContain("video");
	});
});
