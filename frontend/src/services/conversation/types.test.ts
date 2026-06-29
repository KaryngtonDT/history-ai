import { describe, expect, it } from "vitest";
import {
	mapConversationChatFromApi,
	mapConversationFromApi,
	mapConversationMessageFromApi,
} from "./types";

describe("conversation types", () => {
	it("maps conversation message from API", () => {
		expect(
			mapConversationMessageFromApi({
				role: "user",
				text: "Why did Rome collapse?",
			}),
		).toEqual({
			role: "user",
			text: "Why did Rome collapse?",
		});
	});

	it("rejects invalid conversation message role", () => {
		expect(
			mapConversationMessageFromApi({
				role: "system",
				text: "Ignored",
			}),
		).toBeNull();
	});

	it("maps conversation from API", () => {
		expect(
			mapConversationFromApi({
				id: "550e8400-e29b-41d4-a716-446655440001",
				contentId: "550e8400-e29b-41d4-a716-446655440000",
				messages: [
					{ role: "user", text: "Why did Rome collapse?" },
					{ role: "assistant", text: "Mock answer." },
				],
				documents: [{ contentId: "550e8400-e29b-41d4-a716-446655440000" }],
			}),
		).toEqual({
			id: "550e8400-e29b-41d4-a716-446655440001",
			contentId: "550e8400-e29b-41d4-a716-446655440000",
			messages: [
				{ role: "user", text: "Why did Rome collapse?" },
				{ role: "assistant", text: "Mock answer." },
			],
			documents: [{ contentId: "550e8400-e29b-41d4-a716-446655440000" }],
		});
	});

	it("defaults documents to primary content id when API omits documents", () => {
		expect(
			mapConversationFromApi({
				id: "550e8400-e29b-41d4-a716-446655440001",
				contentId: "550e8400-e29b-41d4-a716-446655440000",
				messages: [],
			})?.documents,
		).toEqual([{ contentId: "550e8400-e29b-41d4-a716-446655440000" }]);
	});

	it("maps conversation chat from API", () => {
		expect(
			mapConversationChatFromApi({
				conversation: {
					id: "550e8400-e29b-41d4-a716-446655440001",
					contentId: "550e8400-e29b-41d4-a716-446655440000",
					messages: [
						{ role: "user", text: "Why did Rome collapse?" },
						{
							role: "assistant",
							text: "Mock answer based on retrieved context [1].",
						},
					],
					documents: [{ contentId: "550e8400-e29b-41d4-a716-446655440000" }],
				},
				answer: {
					answer: "Mock answer based on retrieved context [1].",
					sources: [
						{
							artifactId: "550e8400-e29b-41d4-a716-446655440002",
							chunkId: "550e8400-e29b-41d4-a716-446655440010",
							text: "## Ancient Rome",
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
				},
			}),
		).toEqual({
			conversation: {
				id: "550e8400-e29b-41d4-a716-446655440001",
				contentId: "550e8400-e29b-41d4-a716-446655440000",
				messages: [
					{ role: "user", text: "Why did Rome collapse?" },
					{
						role: "assistant",
						text: "Mock answer based on retrieved context [1].",
					},
				],
				documents: [{ contentId: "550e8400-e29b-41d4-a716-446655440000" }],
			},
			answer: {
				answer: "Mock answer based on retrieved context [1].",
				sources: [
					{
						artifactId: "550e8400-e29b-41d4-a716-446655440002",
						chunkId: "550e8400-e29b-41d4-a716-446655440010",
						text: "## Ancient Rome",
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
			},
		});
	});
});
