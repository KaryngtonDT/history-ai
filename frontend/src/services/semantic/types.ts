import type { Artifact } from "@/services/artifact/types";

export interface RetrievedChunk {
	artifactId: string;
	chunkId: string;
	position: number;
	text: string;
	score: number;
}

export interface RetrievedChunkApiDto {
	artifactId: string;
	chunkId: string;
	position: number;
	text: string;
	score: number;
}

export interface SemanticSearchApiDto {
	results: RetrievedChunkApiDto[];
}

function normalizeScore(score: unknown): number | undefined {
	if (typeof score !== "number" || !Number.isFinite(score)) {
		return undefined;
	}

	if (score < 0 || score > 1) {
		return undefined;
	}

	return score;
}

export function mapRetrievedChunkFromApi(
	dto: RetrievedChunkApiDto,
): RetrievedChunk | null {
	const score = normalizeScore(dto.score);

	if (score === undefined) {
		return null;
	}

	return {
		artifactId: dto.artifactId,
		chunkId: dto.chunkId,
		position: dto.position,
		text: dto.text,
		score,
	};
}

export function mapSemanticSearchFromApi(
	dto: SemanticSearchApiDto,
): RetrievedChunk[] {
	return dto.results
		.map(mapRetrievedChunkFromApi)
		.filter((chunk): chunk is RetrievedChunk => chunk !== null);
}

const MOCK_SCORES = [0.92, 0.87, 0.8, 0.73, 0.66];

export function buildMockSemanticResultsFromArtifacts(
	artifacts: Artifact[],
	query: string,
): RetrievedChunk[] {
	const normalizedQuery = query.toLowerCase();
	const results: RetrievedChunk[] = [];

	for (const artifact of artifacts) {
		const chunks = splitArtifactContentIntoChunks(artifact.content);

		for (const [position, text] of chunks.entries()) {
			if (!text.toLowerCase().includes(normalizedQuery)) {
				continue;
			}

			results.push({
				artifactId: artifact.id,
				chunkId: buildMockChunkId(artifact.id, position),
				position,
				text,
				score: MOCK_SCORES[results.length] ?? 0.5,
			});
		}
	}

	return results;
}

function splitArtifactContentIntoChunks(content: string): string[] {
	const lines = content.split("\n");
	const hasHeading = lines.some((line) => /^## (?!#)/.test(line));

	if (!hasHeading) {
		const trimmed = content.trim();

		return trimmed === "" ? [] : [trimmed];
	}

	const segments: string[] = [];
	let current: string[] = [];

	for (const line of lines) {
		if (/^## (?!#)/.test(line)) {
			if (current.length > 0) {
				segments.push(current.join("\n").trim());
				current = [];
			}
		}

		current.push(line);
	}

	if (current.length > 0) {
		segments.push(current.join("\n").trim());
	}

	return segments.filter((segment) => segment !== "");
}

function buildMockChunkId(artifactId: string, position: number): string {
	const seed = `${artifactId}#chunk#${position}`;
	let hash = 0;

	for (const character of seed) {
		hash = (hash * 31 + character.charCodeAt(0)) >>> 0;
	}

	const hex = hash.toString(16).padStart(8, "0");

	return `00000000-0000-4000-8000-${hex.padStart(12, "0")}`;
}
