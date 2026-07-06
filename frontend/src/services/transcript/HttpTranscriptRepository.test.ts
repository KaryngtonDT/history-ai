import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpTranscriptRepository } from "./HttpTranscriptRepository";

describe("HttpTranscriptRepository", () => {
	it("loads transcript from GET /api/videos/{videoId}/transcript", async () => {
		const get = vi.fn().mockResolvedValue({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			transcriptId: "550e8400-e29b-41d4-a716-446655440010",
			language: "english",
			text: "Hello world",
			duration: 3,
			segmentCount: 1,
			segments: [{ index: 0, startTime: 0, endTime: 3, text: "Hello world" }],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpTranscriptRepository(httpClient);

		const transcript = await repository.getTranscript(
			"550e8400-e29b-41d4-a716-446655440099",
		);

		expect(get).toHaveBeenCalledWith(
			"/api/videos/550e8400-e29b-41d4-a716-446655440099/transcript",
		);
		expect(transcript?.text).toBe("Hello world");
	});

	it("returns unavailable detail when API responds with 400", async () => {
		const get = vi.fn().mockRejectedValue(
			new ApiError("GET failed", 400, {
				error: "transcript_not_found",
				message: "Transcript missing",
				videoStatus: "failed",
				failureMessage: "Speech process returned no output.",
				failedStage: "speech_to_text",
			}),
		);
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpTranscriptRepository(httpClient);

		const result = await repository.loadTranscript(
			"550e8400-e29b-41d4-a716-446655440099",
		);

		expect(result.transcript).toBeNull();
		expect(result.unavailableDetail).toMatchObject({
			failureMessage: "Speech process returned no output.",
		});
	});
});
