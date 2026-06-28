import {
	ARTIFACT_TYPE_LABELS,
	getArtifactAnchor,
} from "@/features/processing/ArtifactRelationsPanel/relationLabels";
import type { ArtifactType } from "@/services/artifact/types";
import type { RetrievedChunk } from "@/services/semantic/types";
import { formatSemanticScore } from "../semanticSearchLabels";
import styles from "./SemanticSearchResults.module.css";

export interface SemanticSearchResultsProps {
	results: RetrievedChunk[];
	artifactTypesById: Record<string, ArtifactType>;
}

export function SemanticSearchResults({
	results,
	artifactTypesById,
}: SemanticSearchResultsProps) {
	return (
		<ul className={styles.resultsList}>
			{results.map((result) => {
				const artifactType = artifactTypesById[result.artifactId];
				const typeLabel =
					artifactType !== undefined
						? ARTIFACT_TYPE_LABELS[artifactType]
						: "Artifact";
				const anchor =
					artifactType !== undefined
						? getArtifactAnchor(artifactType)
						: undefined;
				const resultKey = `${result.chunkId}-${result.position}`;

				return (
					<li key={resultKey} className={styles.resultRow}>
						{anchor !== undefined ? (
							<a className={styles.resultLink} href={anchor}>
								<span className={styles.resultHeader}>
									<span className={styles.scoreBadge}>
										{formatSemanticScore(result.score)}
									</span>
									<span className={styles.artifactType}>{typeLabel}</span>
								</span>
								<span className={styles.chunkText}>{result.text}</span>
							</a>
						) : (
							<div className={styles.resultContent}>
								<span className={styles.resultHeader}>
									<span className={styles.scoreBadge}>
										{formatSemanticScore(result.score)}
									</span>
									<span className={styles.artifactType}>{typeLabel}</span>
								</span>
								<span className={styles.chunkText}>{result.text}</span>
							</div>
						)}
					</li>
				);
			})}
		</ul>
	);
}
