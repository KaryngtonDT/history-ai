import type { ArtifactType } from "@/services/artifact/types";

export interface CitationClickDetails {
	chunkId: string;
	artifactId: string;
}

export interface ChatCitationRef {
	number: number;
	artifactId: string;
	chunkId: string;
}

export const CITATION_MARKER_PATTERN = /\[(\d+)\]/g;
export const CITATION_HIGHLIGHT_CLASS = "history-ai-highlight";
export const CITATION_HIGHLIGHT_DURATION_MS = 3000;

export function resolveCitationClickDetails(
	citations: ChatCitationRef[] | undefined,
	number: number,
): CitationClickDetails | undefined {
	const citation = citations?.find((item) => item.number === number);

	if (citation === undefined) {
		return undefined;
	}

	return {
		chunkId: citation.chunkId,
		artifactId: citation.artifactId,
	};
}

export function resolveArtifactElementId(type: ArtifactType): string {
	return `artifact-${type}`;
}

export function applyCitationHighlight(element: HTMLElement): () => void {
	element.scrollIntoView({ behavior: "smooth", block: "start" });
	element.classList.add(CITATION_HIGHLIGHT_CLASS);

	const timeoutId = window.setTimeout(() => {
		element.classList.remove(CITATION_HIGHLIGHT_CLASS);
	}, CITATION_HIGHLIGHT_DURATION_MS);

	return () => {
		window.clearTimeout(timeoutId);
		element.classList.remove(CITATION_HIGHLIGHT_CLASS);
	};
}

export function navigateToCitationTarget(
	artifactId: string,
	artifacts: ReadonlyArray<{ id: string; type: ArtifactType }>,
	root: ParentNode = document,
): (() => void) | undefined {
	const artifact = artifacts.find((item) => item.id === artifactId);

	if (artifact === undefined) {
		return undefined;
	}

	const target = root.querySelector<HTMLElement>(
		`#${resolveArtifactElementId(artifact.type)}`,
	);

	if (target === null) {
		return undefined;
	}

	return applyCitationHighlight(target);
}
