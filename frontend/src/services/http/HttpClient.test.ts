import { afterEach, describe, expect, it, vi } from "vitest";
import { ContentApiError } from "@/services/content/ContentApiError";
import { HttpClient } from "./HttpClient";

describe("HttpClient", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("performs GET requests against the configured base URL", async () => {
		const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response(JSON.stringify([{ id: "1" }]), {
				status: 200,
				headers: { "Content-Type": "application/json" },
			}),
		);

		const client = new HttpClient("http://localhost:8000");
		const data = await client.get<{ id: string }[]>("/api/contents");

		expect(fetchMock).toHaveBeenCalledWith(
			"http://localhost:8000/api/contents",
			{ headers: { Accept: "application/json" } },
		);
		expect(data).toEqual([{ id: "1" }]);
	});

	it("performs POST requests with JSON body", async () => {
		const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response(JSON.stringify({ id: "new-id" }), {
				status: 201,
				headers: { "Content-Type": "application/json" },
			}),
		);

		const client = new HttpClient("http://localhost:8000");
		const data = await client.post<{ id: string }>("/api/contents", {
			title: "Test",
			sourceType: "upload_pdf",
		});

		expect(fetchMock).toHaveBeenCalledWith(
			"http://localhost:8000/api/contents",
			expect.objectContaining({
				method: "POST",
				body: JSON.stringify({
					title: "Test",
					sourceType: "upload_pdf",
				}),
			}),
		);
		expect(data).toEqual({ id: "new-id" });
	});

	it("throws ContentApiError when the response is not ok", async () => {
		vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response(null, { status: 500 }),
		);

		const client = new HttpClient("http://localhost:8000");

		await expect(client.get("/api/contents")).rejects.toBeInstanceOf(
			ContentApiError,
		);
	});
});
