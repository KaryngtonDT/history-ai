import { useEffect, useMemo, useState } from "react";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import type { Artifact } from "@/services/artifact/types";
import { relationService } from "@/services/relation/RelationService";
import type { ArtifactRelation } from "@/services/relation/types";
import styles from "./ArtifactRelationsPanel.module.css";
import {
	ARTIFACT_TYPE_LABELS,
	getArtifactAnchor,
	RELATION_TYPE_LABELS,
} from "./relationLabels";

interface ArtifactRelationsPanelProps {
	contentId: string;
	artifacts: Artifact[];
}

type ArtifactRelationsViewState =
	| { status: "loading" }
	| { status: "ready"; relations: ArtifactRelation[] }
	| { status: "empty" }
	| { status: "error" };

function resolveArtifactLabel(
	artifactsById: Map<string, Artifact>,
	artifactId: string,
): string {
	const artifact = artifactsById.get(artifactId);

	if (artifact === undefined) {
		return artifactId;
	}

	return ARTIFACT_TYPE_LABELS[artifact.type];
}

function resolveArtifactAnchor(
	artifactsById: Map<string, Artifact>,
	artifactId: string,
): string | null {
	const artifact = artifactsById.get(artifactId);

	if (artifact === undefined) {
		return null;
	}

	return getArtifactAnchor(artifact.type);
}

export function ArtifactRelationsPanel({
	contentId,
	artifacts,
}: ArtifactRelationsPanelProps) {
	const [viewState, setViewState] = useState<ArtifactRelationsViewState>({
		status: "loading",
	});

	const artifactsById = useMemo(
		() => new Map(artifacts.map((artifact) => [artifact.id, artifact])),
		[artifacts],
	);

	useEffect(() => {
		let cancelled = false;

		setViewState({ status: "loading" });

		relationService
			.getArtifactRelations(contentId)
			.then((relations) => {
				if (cancelled) {
					return;
				}

				if (relations.length === 0) {
					setViewState({ status: "empty" });
					return;
				}

				setViewState({ status: "ready", relations });
			})
			.catch(() => {
				if (!cancelled) {
					setViewState({ status: "error" });
				}
			});

		return () => {
			cancelled = true;
		};
	}, [contentId]);

	return (
		<Card className={styles.artifactRelationsPanel}>
			<p className={styles.label}>Artifact Relations</p>
			{viewState.status === "loading" ? (
				<div className={styles.loadingState}>
					<Spinner label="Loading artifact relations" />
				</div>
			) : null}
			{viewState.status === "empty" ? (
				<EmptyState
					className={styles.emptyState}
					title="No relations yet"
					description="Relations between generated artifacts will appear here once multiple artifacts are available."
				/>
			) : null}
			{viewState.status === "error" ? (
				<EmptyState
					className={styles.emptyState}
					title="Unable to load relations"
					description="Something went wrong while loading artifact relations for this content."
				/>
			) : null}
			{viewState.status === "ready" ? (
				<ul className={styles.relationsList}>
					{viewState.relations.map((relation) => {
						const sourceLabel = resolveArtifactLabel(
							artifactsById,
							relation.sourceArtifactId,
						);
						const targetLabel = resolveArtifactLabel(
							artifactsById,
							relation.targetArtifactId,
						);
						const sourceAnchor = resolveArtifactAnchor(
							artifactsById,
							relation.sourceArtifactId,
						);
						const targetAnchor = resolveArtifactAnchor(
							artifactsById,
							relation.targetArtifactId,
						);
						const relationKey = `${relation.sourceArtifactId}-${relation.targetArtifactId}-${relation.type}`;

						return (
							<li key={relationKey} className={styles.relationRow}>
								{sourceAnchor !== null ? (
									<a className={styles.relationLink} href={sourceAnchor}>
										{sourceLabel}
									</a>
								) : (
									<span className={styles.relationFallback}>{sourceLabel}</span>
								)}
								<span className={styles.relationType}>
									{RELATION_TYPE_LABELS[relation.type]}
								</span>
								{targetAnchor !== null ? (
									<a className={styles.relationLink} href={targetAnchor}>
										{targetLabel}
									</a>
								) : (
									<span className={styles.relationFallback}>{targetLabel}</span>
								)}
							</li>
						);
					})}
				</ul>
			) : null}
		</Card>
	);
}
