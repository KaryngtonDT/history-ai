import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpMapRepository } from "./HttpMapRepository";

describe("HttpMapRepository", () => {
	it("uses GET /api/maps/timeline/{artifactId}", async () => {
		const get = vi.fn().mockResolvedValue({
			places: [
				{
					name: "Rome",
					coordinates: { latitude: 41.9028, longitude: 12.4964 },
					description: "753 BC — Foundation of Rome",
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpMapRepository(httpClient);

		await repository.getTimelineMap("550e8400-e29b-41d4-a716-446655440000");

		expect(get).toHaveBeenCalledWith(
			"/api/maps/timeline/550e8400-e29b-41d4-a716-446655440000",
		);
	});

	it("maps API DTO to historical places", async () => {
		const get = vi.fn().mockResolvedValue({
			places: [
				{
					name: "Rome",
					coordinates: { latitude: 41.9028, longitude: 12.4964 },
					description: "753 BC — Foundation of Rome",
				},
				{
					name: "Athens",
					coordinates: { latitude: 37.9838, longitude: 23.7275 },
					description: "Trade with Athens",
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpMapRepository(httpClient);

		const places = await repository.getTimelineMap(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(places).toHaveLength(2);
		expect(places?.[0]).toEqual({
			name: "Rome",
			coordinates: { latitude: 41.9028, longitude: 12.4964 },
			description: "753 BC — Foundation of Rome",
		});
		expect(places?.[1]?.name).toBe("Athens");
	});

	it("returns null when timeline artifact is not found", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 404));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpMapRepository(httpClient);

		const places = await repository.getTimelineMap(
			"550e8400-e29b-41d4-a716-446655440099",
		);

		expect(places).toBeNull();
	});

	it("returns empty array when API returns no places", async () => {
		const get = vi.fn().mockResolvedValue({ places: [] });
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpMapRepository(httpClient);

		const places = await repository.getTimelineMap(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(places).toEqual([]);
	});

	it("propagates non-404 HTTP errors", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 500));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpMapRepository(httpClient);

		await expect(
			repository.getTimelineMap("550e8400-e29b-41d4-a716-446655440000"),
		).rejects.toBeInstanceOf(ApiError);
	});
});
