import { useEffect, useState } from "react";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { InteractiveGraph } from "@/features/graph/InteractiveGraph";
import { graphService } from "@/services/graph/GraphService";
import type { KnowledgeGraph } from "@/services/graph/types";
import styles from "./KnowledgeGraphPanel.module.css";

interface KnowledgeGraphPanelProps {
	contentId: string;
}

type KnowledgeGraphViewState =
	| { status: "loading" }
	| { status: "ready"; graph: KnowledgeGraph }
	| { status: "empty" }
	| { status: "error" };

export function KnowledgeGraphPanel({ contentId }: KnowledgeGraphPanelProps) {
	const [viewState, setViewState] = useState<KnowledgeGraphViewState>({
		status: "loading",
	});

	useEffect(() => {
		let cancelled = false;

		setViewState({ status: "loading" });

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
				<InteractiveGraph graph={viewState.graph} />
			) : null}
		</Card>
	);
}
