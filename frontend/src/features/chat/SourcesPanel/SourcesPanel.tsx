import {
	ARTIFACT_TYPE_LABELS,
	getArtifactAnchor,
} from "@/features/processing/ArtifactRelationsPanel/relationLabels";
import type { ArtifactType } from "@/services/artifact/types";
import type { ChatCitation, ChatSource } from "@/services/chat/types";
import { CHAT_SOURCES_TITLE, formatChatScore } from "../chatLabels";
import type { CitationClickDetails } from "../citationNavigation";
import styles from "./SourcesPanel.module.css";

export interface SourcesPanelProps {
	sources: ChatSource[];
	citations?: ChatCitation[];
	artifactTypesById: Record<string, ArtifactType>;
	onCitationClick?: (details: CitationClickDetails) => void;
}

function buildCitationNumberByChunkId(
	citations: ChatCitation[] | undefined,
): Map<string, number> {
	const citationNumbersByChunkId = new Map<string, number>();

	if (citations === undefined) {
		return citationNumbersByChunkId;
	}

	for (const citation of citations) {
		citationNumbersByChunkId.set(citation.chunkId, citation.number);
	}

	return citationNumbersByChunkId;
}

export function SourcesPanel({
	sources,
	citations,
	artifactTypesById,
	onCitationClick,
}: SourcesPanelProps) {
	if (sources.length === 0) {
		return null;
	}

	const citationNumbersByChunkId = buildCitationNumberByChunkId(citations);

	return (
		<section className={styles.sourcesPanel} aria-label={CHAT_SOURCES_TITLE}>
			<h3 className={styles.title}>{CHAT_SOURCES_TITLE}</h3>
			<ul className={styles.sourcesList}>
				{sources.map((source) => {
					const artifactType = artifactTypesById[source.artifactId];
					const typeLabel =
						artifactType !== undefined
							? ARTIFACT_TYPE_LABELS[artifactType]
							: "Artifact";
					const anchor =
						artifactType !== undefined
							? getArtifactAnchor(artifactType)
							: undefined;
					const citationNumber = citationNumbersByChunkId.get(source.chunkId);
					const citationPrefix =
						citationNumber !== undefined ? `[${citationNumber}] ` : "";
					const label = `${citationPrefix}${typeLabel} (${formatChatScore(source.score)})`;
					const citationDetails: CitationClickDetails = {
						chunkId: source.chunkId,
						artifactId: source.artifactId,
					};

					return (
						<li key={source.chunkId} className={styles.sourceItem}>
							{onCitationClick !== undefined ? (
								<button
									type="button"
									className={styles.sourceButton}
									onClick={() => {
										onCitationClick(citationDetails);
									}}
								>
									{label}
								</button>
							) : anchor !== undefined ? (
								<a className={styles.sourceLink} href={anchor}>
									{label}
								</a>
							) : (
								<span className={styles.sourceLabel}>{label}</span>
							)}
						</li>
					);
				})}
			</ul>
		</section>
	);
}
