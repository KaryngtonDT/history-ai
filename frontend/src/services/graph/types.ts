import type { Artifact, ArtifactType } from "@/services/artifact/types";
import { resolveArtifactRelationsFromArtifacts } from "@/services/relation/types";

export type GraphEdgeType =
	| "related"
	| "derived_from"
	| "references"
	| "next"
	| "previous";

export interface GraphNode {
	artifactId: string;
	type: ArtifactType;
	title: string;
}

export interface GraphEdge {
	sourceArtifactId: string;
	targetArtifactId: string;
	type: GraphEdgeType;
}

export interface KnowledgeGraph {
	nodes: GraphNode[];
	edges: GraphEdge[];
}

export interface GraphNodeApiDto {
	artifactId: string;
	type: string;
	title: string;
}

export interface GraphEdgeApiDto {
	sourceArtifactId: string;
	targetArtifactId: string;
	type: string;
}

export interface KnowledgeGraphApiDto {
	nodes: GraphNodeApiDto[];
	edges: GraphEdgeApiDto[];
}

export const EMPTY_KNOWLEDGE_GRAPH: KnowledgeGraph = {
	nodes: [],
	edges: [],
};

const ARTIFACT_TYPES = new Set<ArtifactType>([
	"summary",
	"quiz",
	"flashcards",
	"podcast",
	"timeline",
	"transcript",
]);

const EDGE_TYPES = new Set<GraphEdgeType>([
	"related",
	"derived_from",
	"references",
	"next",
	"previous",
]);

const ARTIFACT_TYPE_TITLES: Record<ArtifactType, string> = {
	transcript: "Transcript",
	summary: "Summary",
	quiz: "Quiz",
	flashcards: "Flashcards",
	timeline: "Timeline",
	podcast: "Podcast",
};

function normalizeArtifactType(type: string): ArtifactType {
	if (ARTIFACT_TYPES.has(type as ArtifactType)) {
		return type as ArtifactType;
	}

	return "summary";
}

function normalizeEdgeType(type: string): GraphEdgeType {
	if (EDGE_TYPES.has(type as GraphEdgeType)) {
		return type as GraphEdgeType;
	}

	return "related";
}

export function mapGraphNodeFromApi(dto: GraphNodeApiDto): GraphNode {
	return {
		artifactId: dto.artifactId,
		type: normalizeArtifactType(dto.type),
		title: dto.title,
	};
}

export function mapGraphEdgeFromApi(dto: GraphEdgeApiDto): GraphEdge {
	return {
		sourceArtifactId: dto.sourceArtifactId,
		targetArtifactId: dto.targetArtifactId,
		type: normalizeEdgeType(dto.type),
	};
}

export function mapKnowledgeGraphFromApi(
	dto: KnowledgeGraphApiDto,
): KnowledgeGraph {
	return {
		nodes: dto.nodes.map(mapGraphNodeFromApi),
		edges: dto.edges.map(mapGraphEdgeFromApi),
	};
}

export function buildKnowledgeGraphFromArtifacts(
	artifacts: Artifact[],
): KnowledgeGraph {
	if (artifacts.length === 0) {
		return EMPTY_KNOWLEDGE_GRAPH;
	}

	const nodes: GraphNode[] = [];
	const nodeIds = new Set<string>();

	for (const artifact of artifacts) {
		if (nodeIds.has(artifact.id)) {
			continue;
		}

		nodeIds.add(artifact.id);
		nodes.push({
			artifactId: artifact.id,
			type: artifact.type,
			title: ARTIFACT_TYPE_TITLES[artifact.type],
		});
	}

	const relations = resolveArtifactRelationsFromArtifacts(artifacts);
	const edges: GraphEdge[] = [];
	const edgeKeys = new Set<string>();

	for (const relation of relations) {
		if (
			!nodeIds.has(relation.sourceArtifactId) ||
			!nodeIds.has(relation.targetArtifactId)
		) {
			continue;
		}

		const edgeKey = `${relation.sourceArtifactId}|${relation.targetArtifactId}|${relation.type}`;

		if (edgeKeys.has(edgeKey)) {
			continue;
		}

		edgeKeys.add(edgeKey);
		edges.push({
			sourceArtifactId: relation.sourceArtifactId,
			targetArtifactId: relation.targetArtifactId,
			type: relation.type,
		});
	}

	return { nodes, edges };
}
