import { useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import {
	ARTIFACT_TYPE_LABELS,
	getArtifactAnchor,
} from "@/features/processing/ArtifactRelationsPanel/relationLabels";
import { recommendationService } from "@/services/recommendation/RecommendationService";
import type { RecommendedArtifact } from "@/services/recommendation/types";
import { REASON_LABELS } from "./recommendationLabels";
import styles from "./SeeAlsoRecommendationsPanel.module.css";

interface SeeAlsoRecommendationsPanelProps {
	contentId: string;
	artifactId: string;
}

type SeeAlsoViewState =
	| { status: "loading" }
	| { status: "ready"; recommendations: RecommendedArtifact[] }
	| { status: "empty" }
	| { status: "error" };

export function SeeAlsoRecommendationsPanel({
	contentId,
	artifactId,
}: SeeAlsoRecommendationsPanelProps) {
	const [viewState, setViewState] = useState<SeeAlsoViewState>({
		status: "loading",
	});

	useEffect(() => {
		let cancelled = false;

		setViewState({ status: "loading" });

		recommendationService
			.getArtifactRecommendations(contentId, artifactId)
			.then((recommendations) => {
				if (cancelled) {
					return;
				}

				if (recommendations.length === 0) {
					setViewState({ status: "empty" });
					return;
				}

				setViewState({ status: "ready", recommendations });
			})
			.catch(() => {
				if (!cancelled) {
					setViewState({ status: "error" });
				}
			});

		return () => {
			cancelled = true;
		};
	}, [contentId, artifactId]);

	return (
		<aside className={styles.panel} aria-label="See also recommendations">
			<p className={styles.label}>See also</p>
			{viewState.status === "loading" ? (
				<div className={styles.loadingState}>
					<Spinner label="Loading recommendations" />
				</div>
			) : null}
			{viewState.status === "empty" ? (
				<EmptyState
					className={styles.emptyState}
					title="No recommendations yet"
					description="Related artifacts will appear here once more content is available."
				/>
			) : null}
			{viewState.status === "error" ? (
				<EmptyState
					className={styles.emptyState}
					title="Unable to load recommendations"
					description="Something went wrong while loading recommendations for this artifact."
				/>
			) : null}
			{viewState.status === "ready" ? (
				<ul className={styles.recommendationsList}>
					{viewState.recommendations.map((recommendation) => {
						const typeLabel = ARTIFACT_TYPE_LABELS[recommendation.type];
						const anchor = getArtifactAnchor(recommendation.type);
						const reasonLabel = REASON_LABELS[recommendation.reason];
						const recommendationKey = `${recommendation.artifactId}-${recommendation.reason}`;

						return (
							<li key={recommendationKey} className={styles.recommendationRow}>
								<a className={styles.recommendationLink} href={anchor}>
									<span className={styles.recommendationTitle}>
										{recommendation.title}
									</span>
									<span className={styles.recommendationMeta}>
										<span className={styles.artifactType}>{typeLabel}</span>
										<span className={styles.reasonLabel}>{reasonLabel}</span>
									</span>
								</a>
							</li>
						);
					})}
				</ul>
			) : null}
		</aside>
	);
}
