import {
	ARTIFACT_TYPE_LABELS,
	getArtifactAnchor,
} from "@/features/processing/ArtifactRelationsPanel/relationLabels";
import type { ArtifactType } from "@/services/artifact/types";
import type { ChatSource } from "@/services/chat/types";
import { CHAT_SOURCES_TITLE, formatChatScore } from "../chatLabels";
import styles from "./SourcesPanel.module.css";

export interface SourcesPanelProps {
	sources: ChatSource[];
	artifactTypesById: Record<string, ArtifactType>;
}

export function SourcesPanel({
	sources,
	artifactTypesById,
}: SourcesPanelProps) {
	if (sources.length === 0) {
		return null;
	}

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
					const label = `${typeLabel} (${formatChatScore(source.score)})`;

					return (
						<li key={source.chunkId} className={styles.sourceItem}>
							{anchor !== undefined ? (
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
