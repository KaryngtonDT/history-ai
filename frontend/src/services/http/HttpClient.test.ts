import { afterEach, describe, expect, it, vi } from "vitest";
import { ApiError, NetworkError } from "@/shared/errors";
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

	it("performs PUT requests with JSON body", async () => {
		const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response(
				JSON.stringify({
					conversation: {
						id: "550e8400-e29b-41d4-a716-446655440001",
						contentId: "550e8400-e29b-41d4-a716-446655440000",
						messages: [],
						documents: [{ contentId: "550e8400-e29b-41d4-a716-446655440000" }],
					},
				}),
				{
					status: 200,
					headers: { "Content-Type": "application/json" },
				},
			),
		);

		const client = new HttpClient("http://localhost:8000");
		const data = await client.put<{ conversation: { id: string } }>(
			"/api/conversations/550e8400-e29b-41d4-a716-446655440001/documents",
			{ contentIds: ["550e8400-e29b-41d4-a716-446655440000"] },
		);

		expect(fetchMock).toHaveBeenCalledWith(
			"http://localhost:8000/api/conversations/550e8400-e29b-41d4-a716-446655440001/documents",
			expect.objectContaining({
				method: "PUT",
				body: JSON.stringify({
					contentIds: ["550e8400-e29b-41d4-a716-446655440000"],
				}),
			}),
		);
		expect(data.conversation.id).toBe("550e8400-e29b-41d4-a716-446655440001");
	});

	it("performs POST multipart uploads with progress callbacks", async () => {
		class MockXMLHttpRequest {
			static instances: MockXMLHttpRequest[] = [];
			upload = { addEventListener: vi.fn() };
			status = 201;
			responseText = JSON.stringify({
				videoId: "550e8400-e29b-41d4-a716-446655440099",
				status: "queued",
			});
			responseType = "text";
			private listeners = new Map<string, Array<() => void>>();

			constructor() {
				MockXMLHttpRequest.instances.push(this);
			}

			open = vi.fn();
			setRequestHeader = vi.fn();
			send = vi.fn(() => {
				for (const listener of this.listeners.get("load") ?? []) {
					listener();
				}
			});

			addEventListener(type: string, listener: () => void) {
				const listeners = this.listeners.get(type) ?? [];
				listeners.push(listener);
				this.listeners.set(type, listeners);
			}
		}

		vi.stubGlobal("XMLHttpRequest", MockXMLHttpRequest);

		const client = new HttpClient("http://localhost:8000");
		const formData = new FormData();
		formData.append(
			"video",
			new File(["video"], "clip.mp4", { type: "video/mp4" }),
		);
		const onProgress = vi.fn();

		const data = await client.postFormData<{
			videoId: string;
			status: string;
		}>("/api/videos", formData, { onProgress });

		const xhr = MockXMLHttpRequest.instances[0];
		expect(xhr.open).toHaveBeenCalledWith(
			"POST",
			"http://localhost:8000/api/videos",
		);
		expect(xhr.setRequestHeader).toHaveBeenCalledWith(
			"Accept",
			"application/json",
		);
		expect(xhr.send).toHaveBeenCalledWith(formData);
		expect(data).toEqual({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			status: "queued",
		});
	});

	it("throws ApiError when the response is not ok", async () => {
		vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response(null, { status: 500 }),
		);

		const client = new HttpClient("http://localhost:8000");

		await expect(client.get("/api/contents")).rejects.toBeInstanceOf(ApiError);
	});

	it("throws NetworkError when fetch fails", async () => {
		vi.spyOn(globalThis, "fetch").mockRejectedValue(new TypeError("offline"));

		const client = new HttpClient("http://localhost:8000");

		await expect(client.get("/api/contents")).rejects.toBeInstanceOf(
			NetworkError,
		);
	});
});
