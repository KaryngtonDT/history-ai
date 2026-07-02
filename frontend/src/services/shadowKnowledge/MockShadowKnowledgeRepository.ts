import type { ShadowKnowledgeRepository } from "./ShadowKnowledgeRepository";
import type {
	KnowledgeGapsResponse,
	KnowledgeGraph,
	KnowledgeNodeDetail,
	KnowledgePathResponse,
	KnowledgeRelatedResponse,
	KnowledgeSearchRequest,
	KnowledgeSearchResult,
} from "./types";

const defaultGraph: KnowledgeGraph = {
	id: "44444444-4444-4444-8444-444444444444",
	scopeKey: "default",
	graphEnabled: true,
	nodes: [
		{
			key: "docker",
			label: "Docker",
			type: "technology",
			explanation: "Container packaging and runtime.",
			sources: ["memory"],
		},
		{
			key: "kubernetes",
			label: "Kubernetes",
			type: "technology",
			explanation: "Container orchestration platform.",
			sources: ["teaching"],
		},
		{
			key: "dependency_injection",
			label: "Dependency Injection",
			type: "concept",
			explanation: "Wiring services through a container.",
			sources: ["memory"],
		},
	],
	edges: [
		{
			id: "edge-docker-k8s",
			fromKey: "docker",
			toKey: "kubernetes",
			type: "prerequisite",
			label: "Docker → Kubernetes",
			reason: "Kubernetes orchestrates Docker containers.",
			source: "preset",
			confidence: "high",
		},
	],
	masteries: [
		{
			nodeKey: "docker",
			percent: 45,
			exposureCount: 3,
			exerciseCount: 1,
			explanationCount: 2,
			videoIds: [],
			confidence: "medium",
			mastered: false,
		},
		{
			nodeKey: "kubernetes",
			percent: 10,
			exposureCount: 1,
			exerciseCount: 0,
			explanationCount: 1,
			videoIds: [],
			confidence: "low",
			mastered: false,
		},
	],
	paths: [
		{
			key: "docker_to_kubernetes",
			label: "Docker → Kubernetes",
			steps: [
				{ key: "docker", label: "Docker" },
				{ key: "kubernetes", label: "Kubernetes" },
			],
		},
	],
};

export class MockShadowKnowledgeRepository implements ShadowKnowledgeRepository {
	getGraph(): Promise<KnowledgeGraph> {
		return Promise.resolve(defaultGraph);
	}

	getNode(id: string): Promise<KnowledgeNodeDetail> {
		const node = defaultGraph.nodes.find((item) => item.key === id);

		return Promise.resolve({
			node: node ?? defaultGraph.nodes[0],
			mastery:
				defaultGraph.masteries.find((item) => item.nodeKey === id) ?? null,
			related: defaultGraph.edges.filter(
				(edge) => edge.fromKey === id || edge.toKey === id,
			),
			gaps: [
				{
					conceptKey: "docker",
					label: "Docker",
					masteryPercent: 45,
					missing: true,
					recommended: "Review Docker before continuing.",
					reason: "Kubernetes orchestrates Docker containers.",
				},
			],
		});
	}

	getPath(): Promise<KnowledgePathResponse> {
		return Promise.resolve({
			scopeKey: "default",
			paths: defaultGraph.paths,
		});
	}

	getGaps(): Promise<KnowledgeGapsResponse> {
		return Promise.resolve({
			scopeKey: "default",
			radar: {
				goalKey: "kubernetes",
				goalLabel: "Kubernetes",
				readinessPercent: 0,
				gaps: [
					{
						conceptKey: "docker",
						label: "Docker",
						masteryPercent: 45,
						missing: true,
						recommended: "Review Docker before continuing.",
						reason: "Kubernetes orchestrates Docker containers.",
					},
				],
			},
		});
	}

	getRelated(key: string): Promise<KnowledgeRelatedResponse> {
		const node = defaultGraph.nodes.find((item) => item.key === key) ?? null;

		return Promise.resolve({
			scopeKey: "default",
			key,
			related: defaultGraph.edges.filter(
				(edge) => edge.fromKey === key || edge.toKey === key,
			),
			node,
		});
	}

	search(request: KnowledgeSearchRequest): Promise<KnowledgeSearchResult> {
		const query = request.query.toLowerCase();
		const nodes = defaultGraph.nodes.filter((node) =>
			node.label.toLowerCase().includes(query),
		);

		return Promise.resolve({
			query: request.query,
			nodes: nodes.map((node) => ({
				key: node.key,
				label: node.label,
				type: node.type,
			})),
			edges: [],
			total: nodes.length,
		});
	}

	rebuild(): Promise<KnowledgeGraph> {
		return Promise.resolve(defaultGraph);
	}

	reset(): Promise<KnowledgeGraph> {
		return Promise.resolve(defaultGraph);
	}
}
