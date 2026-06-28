import { describe, expect, it } from "vitest";
import {
	buildMockStreamTokens,
	mapChatAnswerFromApi,
	mapChatCitationFromApi,
	mapChatSourceFromApi,
	mapChatStreamTokenFromApi,
	parseSseEvents,
} from "./types";

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

	it("maps citation fields from API response", () => {
		const result = mapChatCitationFromApi({
			number: 1,
			artifactId: "550e8400-e29b-41d4-a716-446655440002",
			chunkId: "550e8400-e29b-41d4-a716-446655440010",
			score: 0.91,
		});

		expect(result).toEqual({
			number: 1,
			artifactId: "550e8400-e29b-41d4-a716-446655440002",
			chunkId: "550e8400-e29b-41d4-a716-446655440010",
			score: 0.91,
		});
	});

	it("omits invalid citation numbers from API response", () => {
		expect(
			mapChatCitationFromApi({
				number: 0,
				artifactId: "550e8400-e29b-41d4-a716-446655440002",
				chunkId: "550e8400-e29b-41d4-a716-446655440010",
				score: 0.91,
			}),
		).toBeNull();
		expect(
			mapChatCitationFromApi({
				number: 1.5,
				artifactId: "550e8400-e29b-41d4-a716-446655440002",
				chunkId: "550e8400-e29b-41d4-a716-446655440010",
				score: 0.91,
			}),
		).toBeNull();
	});

	it("omits invalid citation scores from API response", () => {
		const result = mapChatCitationFromApi({
			number: 1,
			artifactId: "550e8400-e29b-41d4-a716-446655440002",
			chunkId: "550e8400-e29b-41d4-a716-446655440010",
			score: 2,
		});

		expect(result).toBeNull();
	});

	it("maps chat answer and filters invalid sources and citations", () => {
		const result = mapChatAnswerFromApi({
			answer: "Mock answer based on retrieved context [1].",
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
			citations: [
				{
					number: 1,
					artifactId: "550e8400-e29b-41d4-a716-446655440002",
					chunkId: "550e8400-e29b-41d4-a716-446655440010",
					score: 0.87,
				},
				{
					number: 0,
					artifactId: "550e8400-e29b-41d4-a716-446655440004",
					chunkId: "550e8400-e29b-41d4-a716-446655440011",
					score: 0.5,
				},
			],
		});

		expect(result).toEqual({
			answer: "Mock answer based on retrieved context [1].",
			sources: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440002",
					chunkId: "550e8400-e29b-41d4-a716-446655440010",
					text: "Valid source",
					score: 0.87,
				},
			],
			citations: [
				{
					number: 1,
					artifactId: "550e8400-e29b-41d4-a716-446655440002",
					chunkId: "550e8400-e29b-41d4-a716-446655440010",
					score: 0.87,
				},
			],
		});
	});

	it("defaults missing citations to an empty array", () => {
		const result = mapChatAnswerFromApi({
			answer: "Mock answer based on retrieved context.",
			sources: [],
		});

		expect(result.citations).toEqual([]);
	});

	it("preserves citation order from API response", () => {
		const result = mapChatAnswerFromApi({
			answer: "Mock answer based on retrieved context [1][2].",
			sources: [],
			citations: [
				{
					number: 1,
					artifactId: "550e8400-e29b-41d4-a716-446655440002",
					chunkId: "550e8400-e29b-41d4-a716-446655440010",
					score: 0.91,
				},
				{
					number: 2,
					artifactId: "550e8400-e29b-41d4-a716-446655440003",
					chunkId: "550e8400-e29b-41d4-a716-446655440011",
					score: 0.89,
				},
			],
		});

		expect(result.citations.map((citation) => citation.number)).toEqual([1, 2]);
	});

	it("maps stream token from API dto", () => {
		expect(mapChatStreamTokenFromApi({ index: 0, text: "Mock " })).toEqual({
			index: 0,
			text: "Mock ",
		});
	});

	it("rejects invalid stream token dto values", () => {
		expect(mapChatStreamTokenFromApi({ index: -1, text: "Mock " })).toBeNull();
		expect(mapChatStreamTokenFromApi({ index: 0, text: "" })).toBeNull();
	});

	it("parses SSE events from response body", () => {
		expect(
			parseSseEvents(
				'event: token\ndata: {"index":0,"text":"Mock "}\n\nevent: done\ndata: {}\n\n',
			),
		).toEqual([
			{ event: "token", data: '{"index":0,"text":"Mock "}' },
			{ event: "done", data: "{}" },
		]);
	});

	it("builds deterministic mock stream tokens", () => {
		expect(buildMockStreamTokens(1)).toEqual([
			"Mock ",
			"answer ",
			"based ",
			"on ",
			"retrieved ",
			"context",
			"[1].",
		]);
	});
});
