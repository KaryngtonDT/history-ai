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
