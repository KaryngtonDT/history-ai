import { describe, expect, it, vi } from "vitest";
import type { MapRepository } from "./MapRepository";
import { MapService } from "./MapService";

const places = [
	{
		name: "Rome",
		coordinates: { latitude: 41.9028, longitude: 12.4964 },
		description: "753 BC — Foundation of Rome",
	},
];

function createRepositoryMock(
	overrides: Partial<MapRepository> = {},
): MapRepository {
	return {
		getTimelineMap: vi.fn().mockResolvedValue(null),
		...overrides,
	};
}

describe("MapService", () => {
	it("returns places from repository", async () => {
		const getTimelineMap = vi.fn().mockResolvedValue(places);
		const service = new MapService(createRepositoryMock({ getTimelineMap }));

		const result = await service.getTimelineMap(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(getTimelineMap).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
		);
		expect(result).toEqual(places);
	});

	it("returns null when repository returns null", async () => {
		const getTimelineMap = vi.fn().mockResolvedValue(null);
		const service = new MapService(createRepositoryMock({ getTimelineMap }));

		const result = await service.getTimelineMap(
			"550e8400-e29b-41d4-a716-446655440001",
		);

		expect(result).toBeNull();
	});

	it("returns empty array when repository returns no places", async () => {
		const getTimelineMap = vi.fn().mockResolvedValue([]);
		const service = new MapService(createRepositoryMock({ getTimelineMap }));

		const result = await service.getTimelineMap(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(result).toEqual([]);
	});

	it("returns null for empty artifact id without calling repository", async () => {
		const getTimelineMap = vi.fn();
		const service = new MapService(createRepositoryMock({ getTimelineMap }));

		const result = await service.getTimelineMap("");

		expect(getTimelineMap).not.toHaveBeenCalled();
		expect(result).toBeNull();
	});

	it("returns null for whitespace-only artifact id without calling repository", async () => {
		const getTimelineMap = vi.fn();
		const service = new MapService(createRepositoryMock({ getTimelineMap }));

		const result = await service.getTimelineMap("   ");

		expect(getTimelineMap).not.toHaveBeenCalled();
		expect(result).toBeNull();
	});

	it("returns null for invalid artifact id without calling repository", async () => {
		const getTimelineMap = vi.fn();
		const service = new MapService(createRepositoryMock({ getTimelineMap }));

		const result = await service.getTimelineMap("artifact-4");

		expect(getTimelineMap).not.toHaveBeenCalled();
		expect(result).toBeNull();
	});

	it("trims artifact id before delegating to repository", async () => {
		const getTimelineMap = vi.fn().mockResolvedValue(places);
		const service = new MapService(createRepositoryMock({ getTimelineMap }));

		await service.getTimelineMap("  550e8400-e29b-41d4-a716-446655440000  ");

		expect(getTimelineMap).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
		);
	});
});
