import { describe, expect, it } from "vitest";
import { mapContentFromApi } from "./mapContentFromApi";
import { mapSourceTypeToApi } from "./mapSourceType";

describe("mapSourceTypeToApi", () => {
	it("maps frontend source types to Symfony enum values", () => {
		expect(mapSourceTypeToApi("pdf")).toBe("upload_pdf");
		expect(mapSourceTypeToApi("youtube")).toBe("youtube_url");
	});
});

describe("mapContentFromApi", () => {
	it("maps API list items to domain Content", () => {
		const content = mapContentFromApi({
			id: "abc",
			title: "The Roman Empire",
			sourceType: "youtube_url",
			status: "draft",
			createdAt: "2026-06-26T12:00:00+00:00",
			updatedAt: "2026-06-26T12:00:00+00:00",
		});

		expect(content).toEqual({
			id: "abc",
			title: "The Roman Empire",
			sourceType: "youtube",
			status: "processing",
			progress: 0,
		});
	});

	it("maps completed status with full progress", () => {
		const content = mapContentFromApi({
			id: "1",
			title: "Done",
			sourceType: "upload_pdf",
			status: "completed",
			createdAt: "2026-06-26T12:00:00+00:00",
			updatedAt: "2026-06-26T12:00:00+00:00",
		});

		expect(content.status).toBe("completed");
		expect(content.progress).toBe(100);
		expect(content.sourceType).toBe("pdf");
	});
});
