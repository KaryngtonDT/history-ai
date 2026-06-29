import {
	ARTIFACT_TYPE_LABELS,
	EDGE_TYPE_LABELS,
	getArtifactAnchor,
} from "../graphLabels";
import styles from "./InteractiveGraph.module.css";

export interface InteractiveGraphNode {
	artifactId: string;
	type: keyof typeof ARTIFACT_TYPE_LABELS;
	title: string;
}

export interface InteractiveGraphEdge {
	sourceArtifactId: string;
	targetArtifactId: string;
	type: keyof typeof EDGE_TYPE_LABELS;
}

export interface InteractiveGraphData {
	nodes: InteractiveGraphNode[];
	edges: InteractiveGraphEdge[];
}

interface InteractiveGraphProps {
	graph: InteractiveGraphData;
	selectedArtifactId?: string | null;
	neighborArtifactIds?: ReadonlySet<string>;
	highlightedEdgeKeys?: ReadonlySet<string>;
	onNodeSelect?: (artifactId: string) => void;
}

function buildNodeLookup(
	nodes: InteractiveGraphNode[],
): Map<string, InteractiveGraphNode> {
	return new Map(nodes.map((node) => [node.artifactId, node]));
}

export function buildGraphEdgeKey(
	sourceArtifactId: string,
	targetArtifactId: string,
	type: string,
): string {
	return `${sourceArtifactId}|${targetArtifactId}|${type}`;
}

function getNodeChipClassName(
	artifactId: string,
	selectedArtifactId: string | null | undefined,
	neighborArtifactIds: ReadonlySet<string> | undefined,
): string {
	const classes = [styles.nodeChip];

	if (selectedArtifactId === artifactId) {
		classes.push(styles.nodeChipSelected);
	} else if (neighborArtifactIds?.has(artifactId)) {
		classes.push(styles.nodeChipNeighbor);
	}

	return classes.join(" ");
}

function getNodeCardClassName(
	artifactId: string,
	selectedArtifactId: string | null | undefined,
	neighborArtifactIds: ReadonlySet<string> | undefined,
): string {
	const classes = [styles.nodeCard];

	if (selectedArtifactId === artifactId) {
		classes.push(styles.nodeCardSelected);
	} else if (neighborArtifactIds?.has(artifactId)) {
		classes.push(styles.nodeCardNeighbor);
	}

	return classes.join(" ");
}

export function InteractiveGraph({
	graph,
	selectedArtifactId = null,
	neighborArtifactIds,
	highlightedEdgeKeys,
	onNodeSelect,
}: InteractiveGraphProps) {
	const nodesById = buildNodeLookup(graph.nodes);

	const handleNodeSelect = (artifactId: string) => {
		onNodeSelect?.(artifactId);
	};

	return (
		<section
			className={styles.root}
			aria-label="Knowledge graph"
			data-testid="interactive-graph"
		>
			<div className={styles.graphCanvas} aria-hidden="true">
				{graph.nodes.map((node) => (
					<button
						key={node.artifactId}
						type="button"
						className={getNodeChipClassName(
							node.artifactId,
							selectedArtifactId,
							neighborArtifactIds,
						)}
						onClick={() => handleNodeSelect(node.artifactId)}
						aria-pressed={selectedArtifactId === node.artifactId}
						data-testid={`graph-node-chip-${node.artifactId}`}
					>
						{node.title}
					</button>
				))}
			</div>
			<div className={styles.sections}>
				<section aria-labelledby="knowledge-graph-nodes-heading">
					<h3
						id="knowledge-graph-nodes-heading"
						className={styles.sectionHeading}
					>
						Nodes
					</h3>
					<ol className={styles.nodeList}>
						{graph.nodes.map((node) => (
							<li key={node.artifactId} className={styles.nodeItem}>
								<article
									className={getNodeCardClassName(
										node.artifactId,
										selectedArtifactId,
										neighborArtifactIds,
									)}
								>
									<button
										type="button"
										className={styles.nodeSelectButton}
										onClick={() => handleNodeSelect(node.artifactId)}
										aria-pressed={selectedArtifactId === node.artifactId}
										data-testid={`graph-node-select-${node.artifactId}`}
									>
										<h4 className={styles.nodeTitle}>{node.title}</h4>
										<p className={styles.nodeType}>
											{ARTIFACT_TYPE_LABELS[node.type]}
										</p>
									</button>
									<a
										className={styles.nodeLink}
										href={getArtifactAnchor(node.type)}
									>
										View artifact
									</a>
								</article>
							</li>
						))}
					</ol>
				</section>
				<section aria-labelledby="knowledge-graph-edges-heading">
					<h3
						id="knowledge-graph-edges-heading"
						className={styles.sectionHeading}
					>
						Edges
					</h3>
					<ol className={styles.edgeList}>
						{graph.edges.map((edge) => {
							const source = nodesById.get(edge.sourceArtifactId);
							const target = nodesById.get(edge.targetArtifactId);
							const sourceLabel = source?.title ?? edge.sourceArtifactId;
							const targetLabel = target?.title ?? edge.targetArtifactId;
							const edgeKey = buildGraphEdgeKey(
								edge.sourceArtifactId,
								edge.targetArtifactId,
								edge.type,
							);
							const isHighlighted = highlightedEdgeKeys?.has(edgeKey) ?? false;

							return (
								<li
									key={edgeKey}
									className={
										isHighlighted
											? `${styles.edgeItem} ${styles.edgeItemHighlighted}`
											: styles.edgeItem
									}
									data-testid={`graph-edge-${edgeKey}`}
								>
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
