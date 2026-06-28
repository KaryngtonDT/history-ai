import { describe, expect, it } from "vitest";
import { isValidContentUuid, resolveChatContentId } from "./contentId";

describe("contentId", () => {
	it("accepts valid UUID content ids", () => {
		expect(isValidContentUuid("550e8400-e29b-41d4-a716-446655440000")).toBe(
			true,
		);
	});

	it("rejects non-uuid content ids", () => {
		expect(isValidContentUuid("1")).toBe(false);
		expect(isValidContentUuid("content-1")).toBe(false);
	});

	it("resolves chat content id from prop when it is a uuid", () => {
		expect(
			resolveChatContentId("550e8400-e29b-41d4-a716-446655440000", []),
		).toBe("550e8400-e29b-41d4-a716-446655440000");
	});

	it("derives chat content id from artifacts when prop is invalid", () => {
		expect(
			resolveChatContentId("1", [
				{
					contentId: "550e8400-e29b-41d4-a716-446655440001",
				},
			]),
		).toBe("550e8400-e29b-41d4-a716-446655440001");
	});

	it("returns null when no uuid is available", () => {
		expect(resolveChatContentId("1", [{ contentId: "1" }])).toBeNull();
	});
});
