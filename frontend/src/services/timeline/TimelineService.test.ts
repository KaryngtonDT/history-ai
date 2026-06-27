import { describe, expect, it, vi } from "vitest";
import type { TimelineRepository } from "./TimelineRepository";
import { TimelineService } from "./TimelineService";

const timeline = {
	sections: [
		{
			title: "Ancient Rome",
			events: [{ text: "753 BC — Foundation of Rome" }],
		},
	],
};

function createRepositoryMock(
	overrides: Partial<TimelineRepository> = {},
): TimelineRepository {
	return {
		getTimeline: vi.fn().mockResolvedValue(null),
		...overrides,
	};
}

describe("TimelineService", () => {
	it("returns timeline from repository", async () => {
		const getTimeline = vi.fn().mockResolvedValue(timeline);
		const service = new TimelineService(createRepositoryMock({ getTimeline }));

		const result = await service.getTimeline(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(getTimeline).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
		);
		expect(result).toEqual(timeline);
	});

	it("returns null when repository returns null", async () => {
		const getTimeline = vi.fn().mockResolvedValue(null);
		const service = new TimelineService(createRepositoryMock({ getTimeline }));

		const result = await service.getTimeline(
			"550e8400-e29b-41d4-a716-446655440001",
		);

		expect(result).toBeNull();
	});

	it("returns null for empty artifact id without calling repository", async () => {
		const getTimeline = vi.fn();
		const service = new TimelineService(createRepositoryMock({ getTimeline }));

		const result = await service.getTimeline("");

		expect(getTimeline).not.toHaveBeenCalled();
		expect(result).toBeNull();
	});

	it("returns null for whitespace-only artifact id without calling repository", async () => {
		const getTimeline = vi.fn();
		const service = new TimelineService(createRepositoryMock({ getTimeline }));

		const result = await service.getTimeline("   ");

		expect(getTimeline).not.toHaveBeenCalled();
		expect(result).toBeNull();
	});

	it("returns null for invalid artifact id without calling repository", async () => {
		const getTimeline = vi.fn();
		const service = new TimelineService(createRepositoryMock({ getTimeline }));

		const result = await service.getTimeline("artifact-4");

		expect(getTimeline).not.toHaveBeenCalled();
		expect(result).toBeNull();
	});

	it("trims artifact id before delegating to repository", async () => {
		const getTimeline = vi.fn().mockResolvedValue(timeline);
		const service = new TimelineService(createRepositoryMock({ getTimeline }));

		await service.getTimeline("  550e8400-e29b-41d4-a716-446655440000  ");

		expect(getTimeline).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
		);
	});
});
