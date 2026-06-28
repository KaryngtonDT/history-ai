import type { Artifact } from "@/services/artifact/types";
import { buildMockSemanticResultsFromArtifacts } from "@/services/semantic/types";

export interface ChatSource {
	artifactId: string;
	chunkId: string;
	text: string;
	score: number;
}

export interface ChatAnswer {
	answer: string;
	sources: ChatSource[];
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

export interface ChatAnswerApiDto {
	answer: string;
	sources: ChatSourceApiDto[];
}

export const EMPTY_CHAT_ANSWER: ChatAnswer = {
	answer: "",
	sources: [],
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

export function mapChatAnswerFromApi(dto: ChatAnswerApiDto): ChatAnswer {
	return {
		answer: typeof dto.answer === "string" ? dto.answer : "",
		sources: dto.sources
			.map(mapChatSourceFromApi)
			.filter((source): source is ChatSource => source !== null),
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
