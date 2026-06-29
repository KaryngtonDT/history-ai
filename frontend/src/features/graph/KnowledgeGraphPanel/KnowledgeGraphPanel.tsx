import { useCallback, useEffect, useMemo, useState } from "react";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import {
	buildGraphEdgeKey,
	InteractiveGraph,
} from "@/features/graph/InteractiveGraph";
import { graphService } from "@/services/graph/GraphService";
import type { GraphNeighborhood, KnowledgeGraph } from "@/services/graph/types";
import styles from "./KnowledgeGraphPanel.module.css";

interface KnowledgeGraphPanelProps {
	contentId: string;
}

type KnowledgeGraphViewState =
	| { status: "loading" }
	| { status: "ready"; graph: KnowledgeGraph }
	| { status: "empty" }
	| { status: "error" };

type NeighborhoodViewState =
	| { status: "idle" }
	| { status: "loading"; artifactId: string }
	| { status: "ready"; artifactId: string; neighborhood: GraphNeighborhood }
	| { status: "not_found"; artifactId: string }
	| { status: "error"; artifactId: string };

export function KnowledgeGraphPanel({ contentId }: KnowledgeGraphPanelProps) {
	const [viewState, setViewState] = useState<KnowledgeGraphViewState>({
		status: "loading",
	});
	const [neighborhoodState, setNeighborhoodState] =
		useState<NeighborhoodViewState>({ status: "idle" });

	useEffect(() => {
		let cancelled = false;

		setViewState({ status: "loading" });
		setNeighborhoodState({ status: "idle" });

		graphService
			.getKnowledgeGraph(contentId)
			.then((graph) => {
				if (cancelled) {
					return;
				}

				if (graph.nodes.length === 0) {
					setViewState({ status: "empty" });
					return;
				}

				setViewState({ status: "ready", graph });
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

	const handleNodeSelect = useCallback(
		(artifactId: string) => {
			setNeighborhoodState({ status: "loading", artifactId });

			graphService
				.getGraphNeighborhood(contentId, artifactId)
				.then((neighborhood) => {
					if (neighborhood === null) {
						setNeighborhoodState({ status: "not_found", artifactId });
						return;
					}

					setNeighborhoodState({
						status: "ready",
						artifactId,
						neighborhood,
					});
				})
				.catch(() => {
					setNeighborhoodState({ status: "error", artifactId });
				});
		},
		[contentId],
	);

	const selectedArtifactId =
		neighborhoodState.status === "idle" ? null : neighborhoodState.artifactId;

	const neighborArtifactIds = useMemo(() => {
		if (neighborhoodState.status !== "ready") {
			return undefined;
		}

		return new Set(
			neighborhoodState.neighborhood.neighbors.map(
				(neighbor) => neighbor.artifactId,
			),
		);
	}, [neighborhoodState]);

	const highlightedEdgeKeys = useMemo(() => {
		if (neighborhoodState.status !== "ready") {
			return undefined;
		}

		return new Set(
			neighborhoodState.neighborhood.edges.map((edge) =>
				buildGraphEdgeKey(
					edge.sourceArtifactId,
					edge.targetArtifactId,
					edge.type,
				),
			),
		);
	}, [neighborhoodState]);

	return (
		<Card className={styles.knowledgeGraphPanel}>
			<p className={styles.label}>Knowledge Graph</p>
			{viewState.status === "loading" ? (
				<div className={styles.loadingState}>
					<Spinner label="Loading knowledge graph" />
				</div>
			) : null}
			{viewState.status === "empty" ? (
				<EmptyState
					className={styles.emptyState}
					title="No graph yet"
					description="Artifact nodes and relations will appear here once multiple learning artifacts are available."
				/>
			) : null}
			{viewState.status === "error" ? (
				<EmptyState
					className={styles.emptyState}
					title="Unable to load graph"
					description="Something went wrong while loading the knowledge graph for this content."
				/>
			) : null}
			{viewState.status === "ready" ? (
				<>
					{neighborhoodState.status === "loading" ? (
						<div className={styles.neighborhoodLoadingState}>
							<Spinner label="Loading neighborhood" />
						</div>
					) : null}
					{neighborhoodState.status === "not_found" ? (
						<p className={styles.neighborhoodMessage} role="status">
							Selected artifact is not part of this graph.
						</p>
					) : null}
					{neighborhoodState.status === "error" ? (
						<p className={styles.neighborhoodMessage} role="status">
							Unable to load neighborhood for the selected artifact.
						</p>
					) : null}
					{neighborhoodState.status === "ready" &&
					neighborhoodState.neighborhood.neighbors.length === 0 ? (
						<p className={styles.neighborhoodMessage} role="status">
							No direct neighbors for the selected artifact.
						</p>
					) : null}
					<InteractiveGraph
						graph={viewState.graph}
						selectedArtifactId={selectedArtifactId}
						neighborArtifactIds={neighborArtifactIds}
						highlightedEdgeKeys={highlightedEdgeKeys}
						onNodeSelect={handleNodeSelect}
					/>
				</>
			) : null}
		</Card>
	);
}
