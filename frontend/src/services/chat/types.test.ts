import { describe, expect, it } from "vitest";
import { mapChatAnswerFromApi, mapChatSourceFromApi } from "./types";

describe("chat types", () => {
	it("maps score from API response", () => {
		const result = mapChatSourceFromApi({
			artifactId: "550e8400-e29b-41d4-a716-446655440002",
			chunkId: "550e8400-e29b-41d4-a716-446655440010",
			text: "## Ancient Rome",
			score: 0.87,
		});

		expect(result).toEqual({
			artifactId: "550e8400-e29b-41d4-a716-446655440002",
			chunkId: "550e8400-e29b-41d4-a716-446655440010",
			text: "## Ancient Rome",
			score: 0.87,
		});
	});

	it("omits invalid score values from API response", () => {
		const result = mapChatSourceFromApi({
			artifactId: "550e8400-e29b-41d4-a716-446655440002",
			chunkId: "550e8400-e29b-41d4-a716-446655440010",
			text: "## Ancient Rome",
			score: 1.5,
		});

		expect(result).toBeNull();
	});

	it("maps chat answer and filters invalid sources", () => {
		const result = mapChatAnswerFromApi({
			answer: "Mock answer based on retrieved context.",
			sources: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440002",
					chunkId: "550e8400-e29b-41d4-a716-446655440010",
					text: "Valid source",
					score: 0.87,
				},
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440004",
					chunkId: "550e8400-e29b-41d4-a716-446655440011",
					text: "Invalid source",
					score: 2,
				},
			],
		});

		expect(result).toEqual({
			answer: "Mock answer based on retrieved context.",
			sources: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440002",
					chunkId: "550e8400-e29b-41d4-a716-446655440010",
					text: "Valid source",
					score: 0.87,
				},
			],
		});
	});
});
