import type { GraphEdge, GraphNode } from "@/services/graph/types";
import {
	ARTIFACT_TYPE_LABELS,
	EDGE_TYPE_LABELS,
	getArtifactAnchor,
} from "../graphLabels";
import styles from "./InteractiveGraph.module.css";

export interface InteractiveGraphData {
	nodes: GraphNode[];
	edges: GraphEdge[];
}

interface InteractiveGraphProps {
	graph: InteractiveGraphData;
}

function buildNodeLookup(nodes: GraphNode[]): Map<string, GraphNode> {
	return new Map(nodes.map((node) => [node.artifactId, node]));
}

export function InteractiveGraph({ graph }: InteractiveGraphProps) {
	const nodesById = buildNodeLookup(graph.nodes);

	return (
		<section
			className={styles.root}
			aria-label="Knowledge graph"
			data-testid="interactive-graph"
		>
			<div className={styles.graphCanvas} aria-hidden="true">
				{graph.nodes.map((node) => (
					<span key={node.artifactId} className={styles.nodeChip}>
						{node.title}
					</span>
				))}
			</div>
			<div className={styles.sections}>
				<section aria-labelledby="knowledge-graph-nodes-heading">
					<h3 id="knowledge-graph-nodes-heading" className={styles.sectionHeading}>
						Nodes
					</h3>
					<ol className={styles.nodeList}>
						{graph.nodes.map((node) => (
							<li key={node.artifactId} className={styles.nodeItem}>
								<article className={styles.nodeCard}>
									<h4 className={styles.nodeTitle}>
										<a
											className={styles.nodeLink}
											href={getArtifactAnchor(node.type)}
										>
											{node.title}
										</a>
									</h4>
									<p className={styles.nodeType}>
										{ARTIFACT_TYPE_LABELS[node.type]}
									</p>
								</article>
							</li>
						))}
					</ol>
				</section>
				<section aria-labelledby="knowledge-graph-edges-heading">
					<h3 id="knowledge-graph-edges-heading" className={styles.sectionHeading}>
						Edges
					</h3>
					<ol className={styles.edgeList}>
						{graph.edges.map((edge) => {
							const source = nodesById.get(edge.sourceArtifactId);
							const target = nodesById.get(edge.targetArtifactId);
							const sourceLabel = source?.title ?? edge.sourceArtifactId;
							const targetLabel = target?.title ?? edge.targetArtifactId;
							const edgeKey = `${edge.sourceArtifactId}-${edge.targetArtifactId}-${edge.type}`;

							return (
								<li key={edgeKey} className={styles.edgeItem}>
									<p className={styles.edgeRow}>
										<span className={styles.edgeEndpoint}>{sourceLabel}</span>
										<span className={styles.edgeType}>
											{EDGE_TYPE_LABELS[edge.type]}
										</span>
										<span className={styles.edgeEndpoint}>{targetLabel}</span>
									</p>
								</li>
							);
						})}
					</ol>
				</section>
			</div>
		</section>
	);
}
