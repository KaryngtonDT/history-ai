import type { Artifact } from "@/services/artifact/types";
import { buildMockSemanticResultsFromArtifacts } from "@/services/semantic/types";

export interface ChatSource {
	artifactId: string;
	chunkId: string;
	text: string;
	score: number;
}

export interface ChatCitation {
	number: number;
	artifactId: string;
	chunkId: string;
	score: number;
}

export interface ChatAnswer {
	answer: string;
	sources: ChatSource[];
	citations: ChatCitation[];
}

export interface ChatRequestDto {
	question: string;
}

export interface ChatSourceApiDto {
	artifactId: string;
	chunkId: string;
	text: string;
	score: number;
}

export interface ChatCitationApiDto {
	number: number;
	artifactId: string;
	chunkId: string;
	score: number;
}

export interface ChatAnswerApiDto {
	answer: string;
	sources: ChatSourceApiDto[];
	citations?: ChatCitationApiDto[];
}

export const EMPTY_CHAT_ANSWER: ChatAnswer = {
	answer: "",
	sources: [],
	citations: [],
};

export const MOCK_CHAT_ANSWER = "Mock answer based on retrieved context.";

export interface ChatStreamToken {
	index: number;
	text: string;
}

export interface ChatStreamCallbacks {
	onToken(token: ChatStreamToken): void;
	onDone(): void;
	onError(error: Error): void;
}

export interface ChatStreamTokenApiDto {
	index: number;
	text: string;
}

export const MOCK_STREAM_BASE_TOKENS = [
	"Mock ",
	"answer ",
	"based ",
	"on ",
	"retrieved ",
	"context",
] as const;

function normalizeScore(score: unknown): number | undefined {
	if (typeof score !== "number" || !Number.isFinite(score)) {
		return undefined;
	}

	if (score < 0 || score > 1) {
		return undefined;
	}

	return score;
}

function normalizeCitationNumber(number: unknown): number | undefined {
	if (typeof number !== "number" || !Number.isInteger(number) || number < 1) {
		return undefined;
	}

	return number;
}

export function mapChatSourceFromApi(dto: ChatSourceApiDto): ChatSource | null {
	const score = normalizeScore(dto.score);

	if (score === undefined) {
		return null;
	}

	return {
		artifactId: dto.artifactId,
		chunkId: dto.chunkId,
		text: dto.text,
		score,
	};
}

export function mapChatCitationFromApi(
	dto: ChatCitationApiDto,
): ChatCitation | null {
	const number = normalizeCitationNumber(dto.number);
	const score = normalizeScore(dto.score);

	if (
		number === undefined ||
		score === undefined ||
		typeof dto.artifactId !== "string" ||
		dto.artifactId === "" ||
		typeof dto.chunkId !== "string" ||
		dto.chunkId === ""
	) {
		return null;
	}

	return {
		number,
		artifactId: dto.artifactId,
		chunkId: dto.chunkId,
		score,
	};
}

export function mapChatAnswerFromApi(dto: ChatAnswerApiDto): ChatAnswer {
	const citations = Array.isArray(dto.citations)
		? dto.citations
				.map(mapChatCitationFromApi)
				.filter((citation): citation is ChatCitation => citation !== null)
		: [];

	return {
		answer: typeof dto.answer === "string" ? dto.answer : "",
		sources: dto.sources
			.map(mapChatSourceFromApi)
			.filter((source): source is ChatSource => source !== null),
		citations,
	};
}

export function buildMockChatSourcesFromArtifacts(
	artifacts: Artifact[],
	question: string,
): ChatSource[] {
	return buildMockSemanticResultsFromArtifacts(artifacts, question).map(
		({ artifactId, chunkId, text, score }) => ({
			artifactId,
			chunkId,
			text,
			score,
		}),
	);
}

export function buildMockChatCitationsFromSources(
	sources: ChatSource[],
): ChatCitation[] {
	return sources.map((source, index) => ({
		number: index + 1,
		artifactId: source.artifactId,
		chunkId: source.chunkId,
		score: source.score,
	}));
}

export function buildMockAnswerWithCitationMarkers(
	sourceCount: number,
): string {
	if (sourceCount === 0) {
		return MOCK_CHAT_ANSWER;
	}

	const markers = Array.from({ length: sourceCount }, (_, index) => {
		return `[${index + 1}]`;
	}).join("");

	return `${MOCK_CHAT_ANSWER.replace(/\.$/, "")} ${markers}.`;
}

export function mapChatStreamTokenFromApi(
	dto: ChatStreamTokenApiDto,
): ChatStreamToken | null {
	if (
		typeof dto.index !== "number" ||
		!Number.isInteger(dto.index) ||
		dto.index < 0
	) {
		return null;
	}

	if (typeof dto.text !== "string" || dto.text === "") {
		return null;
	}

	return {
		index: dto.index,
		text: dto.text,
	};
}

export function parseSseEvents(
	content: string,
): Array<{ event: string; data: string }> {
	const events: Array<{ event: string; data: string }> = [];

	for (const block of content.trim().split(/\r?\n\r?\n/)) {
		if (block === "") {
			continue;
		}

		let eventName = "";
		let data = "";

		for (const line of block.split(/\r?\n/)) {
			if (line.startsWith("event: ")) {
				eventName = line.slice(7);
			}

			if (line.startsWith("data: ")) {
				data = line.slice(6);
			}
		}

		if (eventName !== "") {
			events.push({ event: eventName, data });
		}
	}

	return events;
}

export function buildMockStreamTokens(sourceCount: number): string[] {
	const tokens: string[] = [...MOCK_STREAM_BASE_TOKENS];

	if (sourceCount === 0) {
		tokens.push(".");
		return tokens;
	}

	const markers = Array.from(
		{ length: sourceCount },
		(_, index) => `[${index + 1}]`,
	);
	const trailingMarkers = markers.slice(0, -1);
	tokens.push(...trailingMarkers, `${markers[markers.length - 1]}.`);

	return tokens;
}
