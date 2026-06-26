import { afterEach, describe, expect, it, vi } from "vitest";
import { ContentApiError } from "./ContentApiError";
import { HttpContentRepository } from "./HttpContentRepository";

describe("HttpContentRepository", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("lists contents from GET /api/contents", async () => {
		const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response(
				JSON.stringify([
					{
						id: "1",
						title: "The Roman Empire",
						sourceType: "upload_pdf",
						status: "processing",
						createdAt: "2026-06-26T12:00:00+00:00",
						updatedAt: "2026-06-26T12:00:00+00:00",
					},
				]),
				{ status: 200, headers: { "Content-Type": "application/json" } },
			),
		);

		const repository = new HttpContentRepository("/api");
		const contents = await repository.listContents();

		expect(fetchMock).toHaveBeenCalledWith("/api/contents", {
			headers: { Accept: "application/json" },
		});
		expect(contents[0]?.title).toBe("The Roman Empire");
		expect(contents[0]?.sourceType).toBe("pdf");
	});

	it("creates content via POST /api/contents", async () => {
		const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response(JSON.stringify({ id: "new-id" }), {
				status: 201,
				headers: { "Content-Type": "application/json" },
			}),
		);

		const repository = new HttpContentRepository("/api");
		const result = await repository.createContent({
			title: "French Revolution",
			sourceType: "pdf",
		});

		expect(fetchMock).toHaveBeenCalledWith("/api/contents", {
			method: "POST",
			headers: {
				Accept: "application/json",
				"Content-Type": "application/json",
			},
			body: JSON.stringify({
				title: "French Revolution",
				sourceType: "upload_pdf",
			}),
		});
		expect(result).toEqual({ id: "new-id" });
	});

	it("throws ContentApiError on HTTP failure", async () => {
		vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response(null, { status: 500 }),
		);

		const repository = new HttpContentRepository("/api");

		await expect(repository.listContents()).rejects.toBeInstanceOf(
			ContentApiError,
		);
	});
});
