import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import {
	applyCitationHighlight,
	CITATION_HIGHLIGHT_CLASS,
	CITATION_HIGHLIGHT_DURATION_MS,
	navigateToCitationTarget,
	resolveCitationClickDetails,
} from "./citationNavigation";

describe("citationNavigation", () => {
	beforeEach(() => {
		vi.useFakeTimers();
	});

	afterEach(() => {
		vi.useRealTimers();
	});

	it("resolves citation click details by number", () => {
		expect(
			resolveCitationClickDetails(
				[
					{
						number: 1,
						artifactId: "550e8400-e29b-41d4-a716-446655440002",
						chunkId: "550e8400-e29b-41d4-a716-446655440010",
					},
				],
				1,
			),
		).toEqual({
			artifactId: "550e8400-e29b-41d4-a716-446655440002",
			chunkId: "550e8400-e29b-41d4-a716-446655440010",
		});
	});

	it("returns undefined for unknown citation numbers", () => {
		expect(
			resolveCitationClickDetails(
				[
					{
						number: 1,
						artifactId: "550e8400-e29b-41d4-a716-446655440002",
						chunkId: "550e8400-e29b-41d4-a716-446655440010",
					},
				],
				2,
			),
		).toBeUndefined();
	});

	it("scrolls to target and adds highlight class", () => {
		const element = document.createElement("div");
		element.scrollIntoView = vi.fn();

		applyCitationHighlight(element);

		expect(element.scrollIntoView).toHaveBeenCalledWith({
			behavior: "smooth",
			block: "start",
		});
		expect(element.classList.contains(CITATION_HIGHLIGHT_CLASS)).toBe(true);
	});

	it("removes highlight class after timeout", () => {
		const element = document.createElement("div");
		element.scrollIntoView = vi.fn();

		applyCitationHighlight(element);

		expect(element.classList.contains(CITATION_HIGHLIGHT_CLASS)).toBe(true);

		vi.advanceTimersByTime(CITATION_HIGHLIGHT_DURATION_MS);

		expect(element.classList.contains(CITATION_HIGHLIGHT_CLASS)).toBe(false);
	});

	it("navigates to artifact target by artifact id", () => {
		const root = document.createElement("div");
		const target = document.createElement("section");
		target.id = "artifact-summary";
		target.scrollIntoView = vi.fn();
		root.appendChild(target);

		const cleanup = navigateToCitationTarget(
			"550e8400-e29b-41d4-a716-446655440002",
			[
				{
					id: "550e8400-e29b-41d4-a716-446655440002",
					type: "summary",
				},
			],
			root,
		);

		expect(cleanup).toBeTypeOf("function");
		expect(target.scrollIntoView).toHaveBeenCalled();
		expect(target.classList.contains(CITATION_HIGHLIGHT_CLASS)).toBe(true);

		cleanup?.();
		expect(target.classList.contains(CITATION_HIGHLIGHT_CLASS)).toBe(false);
	});
});
