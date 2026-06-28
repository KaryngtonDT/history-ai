import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpRecommendationRepository } from "./HttpRecommendationRepository";

describe("HttpRecommendationRepository", () => {
	it("uses GET /api/contents/{contentId}/artifacts/{artifactId}/recommendations", async () => {
		const get = vi.fn().mockResolvedValue({ recommendations: [] });
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpRecommendationRepository(httpClient);

		await repository.getArtifactRecommendations(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(get).toHaveBeenCalledWith(
			"/api/contents/550e8400-e29b-41d4-a716-446655440000/artifacts/550e8400-e29b-41d4-a716-446655440002/recommendations",
		);
	});

	it("maps API DTO to recommended artifacts", async () => {
		const get = vi.fn().mockResolvedValue({
			recommendations: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "transcript",
					title: "Transcript",
					reason: "derived_from",
					score: 100,
				},
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440003",
					type: "quiz",
					title: "Quiz",
					reason: "references",
					score: 80,
				},
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440004",
					type: "timeline",
					title: "Timeline",
					reason: "related",
					score: 60,
				},
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440005",
					type: "flashcards",
					title: "Flashcards",
					reason: "next",
					score: 40,
				},
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440006",
					type: "podcast",
					title: "Podcast",
					reason: "previous",
					score: 40,
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpRecommendationRepository(httpClient);

		const result = await repository.getArtifactRecommendations(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(result).toEqual([
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440001",
				type: "transcript",
				title: "Transcript",
				reason: "derived_from",
				score: 100,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440003",
				type: "quiz",
				title: "Quiz",
				reason: "references",
				score: 80,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440004",
				type: "timeline",
				title: "Timeline",
				reason: "related",
				score: 60,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440005",
				type: "flashcards",
				title: "Flashcards",
				reason: "next",
				score: 40,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440006",
				type: "podcast",
				title: "Podcast",
				reason: "previous",
				score: 40,
			},
		]);
	});

	it("maps API DTO without score when score is absent", async () => {
		const get = vi.fn().mockResolvedValue({
			recommendations: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "summary",
					title: "Summary",
					reason: "related",
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpRecommendationRepository(httpClient);

		const result = await repository.getArtifactRecommendations(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(result).toEqual([
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440001",
				type: "summary",
				title: "Summary",
				reason: "related",
			},
		]);
	});

	it("returns empty array when API returns no recommendations", async () => {
		const get = vi.fn().mockResolvedValue({ recommendations: [] });
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpRecommendationRepository(httpClient);

		const result = await repository.getArtifactRecommendations(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(result).toEqual([]);
	});

	it("returns empty array when ids are invalid on the server", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 400));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpRecommendationRepository(httpClient);

		const result = await repository.getArtifactRecommendations(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(result).toEqual([]);
	});

	it("propagates non-400 HTTP errors", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 500));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpRecommendationRepository(httpClient);

		await expect(
			repository.getArtifactRecommendations(
				"550e8400-e29b-41d4-a716-446655440000",
				"550e8400-e29b-41d4-a716-446655440002",
			),
		).rejects.toBeInstanceOf(ApiError);
	});
});
