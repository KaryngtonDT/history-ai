import { describe, expect, it } from "vitest";
import { ContentMapper } from "./ContentMapper";

describe("ContentMapper", () => {
	it("maps API DTO to domain Content", () => {
		const content = ContentMapper.fromApi({
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
		const content = ContentMapper.fromApi({
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

	it("maps domain input to create API DTO", () => {
		expect(
			ContentMapper.toCreateApiDto({
				title: "Roman Empire",
				sourceType: "pdf",
			}),
		).toEqual({
			title: "Roman Empire",
			sourceType: "upload_pdf",
		});
	});

	it("maps source types in both directions", () => {
		expect(ContentMapper.sourceTypeToApi("pdf")).toBe("upload_pdf");
		expect(ContentMapper.sourceTypeToApi("youtube")).toBe("youtube_url");
		expect(ContentMapper.sourceTypeFromApi("upload_pdf")).toBe("pdf");
		expect(ContentMapper.sourceTypeFromApi("youtube_url")).toBe("youtube");
		expect(ContentMapper.sourceTypeFromApi("unknown")).toBe("pdf");
	});
});
