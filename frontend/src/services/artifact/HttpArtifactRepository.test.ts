import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { HttpArtifactRepository } from "./HttpArtifactRepository";

describe("HttpArtifactRepository", () => {
	it("loads artifacts for a content id", async () => {
		const get = vi.fn().mockResolvedValue([
			{
				id: "artifact-1",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "summary",
				content: "Generated summary text",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
		]);
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpArtifactRepository(httpClient);

		const artifacts = await repository.listByContentId("content-1");

		expect(get).toHaveBeenCalledWith("/api/contents/content-1/artifacts");
		expect(artifacts).toEqual([
			{
				id: "artifact-1",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "summary",
				content: "Generated summary text",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
		]);
	});
});
