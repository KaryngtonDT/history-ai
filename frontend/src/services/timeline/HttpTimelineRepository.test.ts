import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpTimelineRepository } from "./HttpTimelineRepository";

describe("HttpTimelineRepository", () => {
	it("uses GET /api/timeline/{artifactId}", async () => {
		const get = vi.fn().mockResolvedValue({
			sections: [
				{
					title: "Ancient Rome",
					events: [{ text: "753 BC — Foundation of Rome" }],
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpTimelineRepository(httpClient);

		await repository.getTimeline("550e8400-e29b-41d4-a716-446655440000");

		expect(get).toHaveBeenCalledWith(
			"/api/timeline/550e8400-e29b-41d4-a716-446655440000",
		);
	});

	it("maps API DTO to structured timeline", async () => {
		const get = vi.fn().mockResolvedValue({
			sections: [
				{
					title: "Ancient Rome",
					events: [
						{ text: "753 BC — Foundation of Rome" },
						{ text: "Republic established" },
					],
				},
				{
					title: "Empire",
					events: [{ text: "Augustus becomes emperor" }],
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpTimelineRepository(httpClient);

		const timeline = await repository.getTimeline(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(timeline?.sections).toHaveLength(2);
		expect(timeline?.sections[0]?.title).toBe("Ancient Rome");
		expect(timeline?.sections[0]?.events).toEqual([
			{ text: "753 BC — Foundation of Rome" },
			{ text: "Republic established" },
		]);
		expect(timeline?.sections[1]?.events[0]?.text).toBe(
			"Augustus becomes emperor",
		);
	});

	it("returns null when timeline artifact is not found", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 404));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpTimelineRepository(httpClient);

		const timeline = await repository.getTimeline(
			"550e8400-e29b-41d4-a716-446655440099",
		);

		expect(timeline).toBeNull();
	});

	it("returns empty sections when API returns no sections", async () => {
		const get = vi.fn().mockResolvedValue({ sections: [] });
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpTimelineRepository(httpClient);

		const timeline = await repository.getTimeline(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(timeline?.sections).toEqual([]);
	});

	it("propagates non-404 HTTP errors", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 500));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpTimelineRepository(httpClient);

		await expect(
			repository.getTimeline("550e8400-e29b-41d4-a716-446655440000"),
		).rejects.toBeInstanceOf(ApiError);
	});
});
