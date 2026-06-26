import { describe, expect, it, vi } from "vitest";
import type { ArtifactRepository } from "./ArtifactRepository";
import { ArtifactService } from "./ArtifactService";
import type { Artifact } from "./types";

const summaryArtifact: Artifact = {
	id: "artifact-1",
	contentId: "content-1",
	processingJobId: "job-1",
	type: "summary",
	content: "Generated summary text",
	createdAt: "2026-06-26T12:00:00+00:00",
};

function createRepositoryMock(
	listByContentId: ArtifactRepository["listByContentId"],
): ArtifactRepository {
	return {
		listByContentId,
	};
}

describe("ArtifactService", () => {
	it("returns artifacts for a content id", async () => {
		const listByContentId = vi
			.fn<ArtifactRepository["listByContentId"]>()
			.mockResolvedValue([summaryArtifact]);
		const service = new ArtifactService(createRepositoryMock(listByContentId));

		const artifacts = await service.listByContentId("content-1");

		expect(listByContentId).toHaveBeenCalledWith("content-1");
		expect(artifacts).toEqual([summaryArtifact]);
	});

	it("returns summary artifact when present", async () => {
		const listByContentId = vi
			.fn<ArtifactRepository["listByContentId"]>()
			.mockResolvedValue([
				summaryArtifact,
				{
					...summaryArtifact,
					id: "artifact-2",
					type: "quiz",
					content: "Quiz content",
				},
			]);
		const service = new ArtifactService(createRepositoryMock(listByContentId));

		const summary = await service.getSummaryArtifact("content-1");

		expect(summary).toEqual(summaryArtifact);
	});

	it("returns null when no summary artifact exists", async () => {
		const listByContentId = vi
			.fn<ArtifactRepository["listByContentId"]>()
			.mockResolvedValue([]);
		const service = new ArtifactService(createRepositoryMock(listByContentId));

		await expect(service.getSummaryArtifact("content-1")).resolves.toBeNull();
	});

	it("returns transcript artifact when present", async () => {
		const transcriptArtifact: Artifact = {
			...summaryArtifact,
			id: "artifact-3",
			type: "transcript",
			content: "Extracted transcript text",
		};
		const listByContentId = vi
			.fn<ArtifactRepository["listByContentId"]>()
			.mockResolvedValue([transcriptArtifact, summaryArtifact]);
		const service = new ArtifactService(createRepositoryMock(listByContentId));

		const transcript = await service.getTranscriptArtifact("content-1");

		expect(transcript).toEqual(transcriptArtifact);
	});
});
