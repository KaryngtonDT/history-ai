import type { Artifact, ArtifactType } from "@/services/artifact/types";

export type ArtifactRelationType =
	| "related"
	| "derived_from"
	| "references"
	| "next"
	| "previous";

export interface ArtifactRelation {
	sourceArtifactId: string;
	targetArtifactId: string;
	type: ArtifactRelationType;
}

export interface ArtifactRelationApiDto {
	sourceArtifactId: string;
	targetArtifactId: string;
	type: string;
}

export interface ArtifactRelationsApiDto {
	relations: ArtifactRelationApiDto[];
}

const RELATION_TYPES = new Set<ArtifactRelationType>([
	"related",
	"derived_from",
	"references",
	"next",
	"previous",
]);

const TYPE_ORDER: ArtifactType[] = [
	"transcript",
	"summary",
	"quiz",
	"flashcards",
	"timeline",
	"podcast",
];

function normalizeRelationType(type: string): ArtifactRelationType {
	if (RELATION_TYPES.has(type as ArtifactRelationType)) {
		return type as ArtifactRelationType;
	}

	return "related";
}

export function mapArtifactRelationFromApi(
	dto: ArtifactRelationApiDto,
): ArtifactRelation {
	return {
		sourceArtifactId: dto.sourceArtifactId,
		targetArtifactId: dto.targetArtifactId,
		type: normalizeRelationType(dto.type),
	};
}

export function mapArtifactRelationsFromApi(
	dto: ArtifactRelationsApiDto,
): ArtifactRelation[] {
	return dto.relations.map(mapArtifactRelationFromApi);
}

export function resolveArtifactRelationsFromArtifacts(
	artifacts: Artifact[],
): ArtifactRelation[] {
	if (artifacts.length === 0) {
		return [];
	}

	const relations: ArtifactRelation[] = [];
	const relationKeys = new Set<string>();
	const connectedPairs = new Set<string>();

	const transcript = findFirstByType(artifacts, "transcript");
	const summary = findFirstByType(artifacts, "summary");
	const quiz = findFirstByType(artifacts, "quiz");
	const flashcards = findFirstByType(artifacts, "flashcards");
	const timeline = findFirstByType(artifacts, "timeline");

	if (summary !== undefined && transcript !== undefined) {
		addRelation(
			relations,
			relationKeys,
			connectedPairs,
			summary.id,
			transcript.id,
			"derived_from",
		);
	}

	if (quiz !== undefined && summary !== undefined) {
		addRelation(
			relations,
			relationKeys,
			connectedPairs,
			quiz.id,
			summary.id,
			"references",
		);
	}

	if (flashcards !== undefined && summary !== undefined) {
		addRelation(
			relations,
			relationKeys,
			connectedPairs,
			flashcards.id,
			summary.id,
			"references",
		);
	}

	if (timeline !== undefined && transcript !== undefined) {
		addRelation(
			relations,
			relationKeys,
			connectedPairs,
			timeline.id,
			transcript.id,
			"references",
		);
	}

	const sortedArtifacts = sortArtifacts(artifacts);

	for (let leftIndex = 0; leftIndex < sortedArtifacts.length; leftIndex += 1) {
		for (
			let rightIndex = leftIndex + 1;
			rightIndex < sortedArtifacts.length;
			rightIndex += 1
		) {
			const left = sortedArtifacts[leftIndex];
			const right = sortedArtifacts[rightIndex];

			if (isPairConnected(connectedPairs, left.id, right.id)) {
				continue;
			}

			addRelation(
				relations,
				relationKeys,
				connectedPairs,
				left.id,
				right.id,
				"related",
			);
		}
	}

	return relations;
}

function findFirstByType(
	artifacts: Artifact[],
	type: ArtifactType,
): Artifact | undefined {
	return sortArtifacts(artifacts.filter((artifact) => artifact.type === type))[0];
}

function sortArtifacts(artifacts: Artifact[]): Artifact[] {
	return [...artifacts].sort((left, right) => {
		const typeComparison =
			typeOrderIndex(left.type) - typeOrderIndex(right.type);

		if (typeComparison !== 0) {
			return typeComparison;
		}

		return left.id.localeCompare(right.id);
	});
}

function typeOrderIndex(type: ArtifactType): number {
	const index = TYPE_ORDER.indexOf(type);

	return index === -1 ? Number.MAX_SAFE_INTEGER : index;
}

function addRelation(
	relations: ArtifactRelation[],
	relationKeys: Set<string>,
	connectedPairs: Set<string>,
	sourceArtifactId: string,
	targetArtifactId: string,
	type: ArtifactRelationType,
): void {
	const relationKey = `${sourceArtifactId}|${targetArtifactId}|${type}`;

	if (relationKeys.has(relationKey)) {
		return;
	}

	relationKeys.add(relationKey);
	connectedPairs.add(pairKey(sourceArtifactId, targetArtifactId));
	relations.push({
		sourceArtifactId,
		targetArtifactId,
		type,
	});
}

function isPairConnected(
	connectedPairs: Set<string>,
	leftArtifactId: string,
	rightArtifactId: string,
): boolean {
	return connectedPairs.has(pairKey(leftArtifactId, rightArtifactId));
}

function pairKey(leftArtifactId: string, rightArtifactId: string): string {
	return leftArtifactId <= rightArtifactId
		? `${leftArtifactId}|${rightArtifactId}`
		: `${rightArtifactId}|${leftArtifactId}`;
}
