import type { Artifact, ArtifactType } from "@/services/artifact/types";
import type { GraphEdge, KnowledgeGraph } from "@/services/graph/types";
import { buildKnowledgeGraphFromArtifacts } from "@/services/graph/types";

export type RecommendationReason =
	| "related"
	| "derived_from"
	| "references"
	| "next"
	| "previous";

export interface RecommendedArtifact {
	artifactId: string;
	type: ArtifactType;
	title: string;
	reason: RecommendationReason;
	score?: number;
}

export interface RecommendedArtifactApiDto {
	artifactId: string;
	type: string;
	title: string;
	reason: string;
	score?: number;
}

export interface ArtifactRecommendationsApiDto {
	recommendations: RecommendedArtifactApiDto[];
}

const ARTIFACT_TYPES = new Set<ArtifactType>([
	"summary",
	"quiz",
	"flashcards",
	"podcast",
	"timeline",
	"transcript",
]);

const RECOMMENDATION_REASONS = new Set<RecommendationReason>([
	"related",
	"derived_from",
	"references",
	"next",
	"previous",
]);

function normalizeArtifactType(type: string): ArtifactType {
	if (ARTIFACT_TYPES.has(type as ArtifactType)) {
		return type as ArtifactType;
	}

	return "summary";
}

function normalizeRecommendationReason(reason: string): RecommendationReason {
	if (RECOMMENDATION_REASONS.has(reason as RecommendationReason)) {
		return reason as RecommendationReason;
	}

	return "related";
}

function normalizeRecommendationScore(score: unknown): number | undefined {
	if (typeof score !== "number" || !Number.isInteger(score)) {
		return undefined;
	}

	if (score < 0 || score > 100) {
		return undefined;
	}

	return score;
}

export function mapRecommendedArtifactFromApi(
	dto: RecommendedArtifactApiDto,
): RecommendedArtifact {
	const normalizedScore = normalizeRecommendationScore(dto.score);

	return {
		artifactId: dto.artifactId,
		type: normalizeArtifactType(dto.type),
		title: dto.title,
		reason: normalizeRecommendationReason(dto.reason),
		...(normalizedScore !== undefined ? { score: normalizedScore } : {}),
	};
}

export function mapArtifactRecommendationsFromApi(
	dto: ArtifactRecommendationsApiDto,
): RecommendedArtifact[] {
	return dto.recommendations.map(mapRecommendedArtifactFromApi);
}

export function buildRecommendationsFromArtifacts(
	artifacts: Artifact[],
	currentArtifactId: string,
): RecommendedArtifact[] {
	if (artifacts.length === 0) {
		return [];
	}

	const graph = buildKnowledgeGraphFromArtifacts(artifacts);

	return resolveRecommendationsFromGraph(graph, currentArtifactId);
}

export function resolveRecommendationsFromGraph(
	graph: KnowledgeGraph,
	currentArtifactId: string,
): RecommendedArtifact[] {
	if (graph.nodes.length === 0) {
		return [];
	}

	const neighbourReasons = resolveNeighbourReasons(graph, currentArtifactId);

	if (Object.keys(neighbourReasons).length === 0) {
		return [];
	}

	const recommendations: RecommendedArtifact[] = [];

	for (const node of graph.nodes) {
		if (node.artifactId === currentArtifactId) {
			continue;
		}

		const reason = neighbourReasons[node.artifactId];

		if (reason === undefined) {
			continue;
		}

		recommendations.push({
			artifactId: node.artifactId,
			type: node.type,
			title: node.title,
			reason,
		});
	}

	return recommendations;
}

function resolveNeighbourReasons(
	graph: KnowledgeGraph,
	currentArtifactId: string,
): Record<string, RecommendationReason> {
	const neighbourReasons: Record<string, RecommendationReason> = {};

	for (const edge of graph.edges) {
		const neighbourId = resolveNeighbourId(edge, currentArtifactId);

		if (neighbourId === null) {
			continue;
		}

		if (neighbourReasons[neighbourId] !== undefined) {
			continue;
		}

		neighbourReasons[neighbourId] = edge.type;
	}

	return neighbourReasons;
}

function resolveNeighbourId(
	edge: GraphEdge,
	currentArtifactId: string,
): string | null {
	if (edge.sourceArtifactId === currentArtifactId) {
		return edge.targetArtifactId;
	}

	if (edge.targetArtifactId === currentArtifactId) {
		return edge.sourceArtifactId;
	}

	return null;
}
