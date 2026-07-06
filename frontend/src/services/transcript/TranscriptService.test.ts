import { describe, expect, it, vi } from "vitest";
import type { TranscriptRepository } from "./TranscriptRepository";
import { TranscriptService } from "./TranscriptService";

function createRepositoryMock(
	overrides: Partial<TranscriptRepository> = {},
): TranscriptRepository {
	return {
		getTranscript: vi.fn().mockResolvedValue(null),
		loadTranscript: vi.fn().mockResolvedValue({ transcript: null }),
		...overrides,
	};
}

describe("TranscriptService", () => {
	it("returns null for invalid video id", async () => {
		const service = new TranscriptService(createRepositoryMock());

		const result = await service.getTranscript("not-a-uuid");

		expect(result).toBeNull();
	});

	it("delegates valid requests to repository", async () => {
		const getTranscript = vi.fn().mockResolvedValue({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			transcriptId: "550e8400-e29b-41d4-a716-446655440010",
			language: "english",
			text: "Hello",
			duration: 2,
			segmentCount: 1,
			segments: [],
		});
		const service = new TranscriptService(
			createRepositoryMock({ getTranscript }),
		);

		await service.getTranscript("550e8400-e29b-41d4-a716-446655440099");

		expect(getTranscript).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440099",
		);
	});
});
