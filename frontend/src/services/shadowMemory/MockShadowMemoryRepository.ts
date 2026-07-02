import type { ShadowMemoryRepository } from "./ShadowMemoryRepository";
import type {
	KnowledgeConnection,
	KnowledgeItem,
	LearningJourney,
	MemorySearchRequest,
	MemorySearchResult,
	MemoryTimeline,
	MemoryTimelineEntry,
} from "./types";

const defaultConcepts: KnowledgeItem[] = [
	{
		key: "dependency_injection",
		label: "Dependency Injection",
		category: "concept",
		progress: "learning",
		progressPercent: 45,
		exposureCount: 3,
		questionCount: 2,
		challengeSuccessCount: 0,
		videoIds: ["video-1"],
		sessionIds: ["session-1"],
		explanation: "Observed from Symfony watch sessions.",
		lastSeenAt: "2026-06-01T10:00:00+00:00",
	},
	{
		key: "docker",
		label: "Docker",
		category: "concept",
		progress: "reviewing",
		progressPercent: 72,
		exposureCount: 5,
		questionCount: 1,
		challengeSuccessCount: 1,
		videoIds: ["video-2"],
		sessionIds: ["session-2"],
		explanation: "Container basics from DevOps content.",
		lastSeenAt: "2026-06-10T10:00:00+00:00",
	},
];

const defaultTimeline: MemoryTimelineEntry[] = [
	{
		id: "m1",
		recordedAt: "2026-06-01T10:00:00+00:00",
		category: "milestone",
		importance: "normal",
		confidence: "high",
		label: "Memory timeline started",
		detail: "Initial learner memory timeline created.",
		concepts: [],
		sources: ["system"],
	},
	{
		id: "e1",
		recordedAt: "2026-06-05T14:00:00+00:00",
		category: "question",
		importance: "normal",
		confidence: "medium",
		label: "Dependency Injection",
		detail: "Recorded from question activity.",
		videoId: "video-1",
		sessionId: "session-1",
		concepts: ["dependency_injection"],
		sources: ["shadow"],
	},
];

const defaultConnections: KnowledgeConnection[] = [
	{
		fromKey: "docker",
		toKey: "kubernetes",
		label: "Docker → Kubernetes",
		reason: "Natural next step after containers.",
	},
];

const defaultMemory: MemoryTimeline = {
	id: "22222222-2222-4222-8222-222222222222",
	scopeKey: "default",
	memoryEnabled: true,
	timeline: defaultTimeline,
	concepts: defaultConcepts,
	vocabulary: [],
	milestones: defaultTimeline.filter((entry) => entry.category === "milestone"),
	knowledge: defaultConcepts,
	connections: defaultConnections,
};

export class MockShadowMemoryRepository implements ShadowMemoryRepository {
	private memory: MemoryTimeline = defaultMemory;

	getTimeline(): Promise<MemoryTimeline> {
		return Promise.resolve(this.memory);
	}

	getConcepts(): Promise<{
		scopeKey: string;
		concepts: MemoryTimeline["concepts"];
	}> {
		return Promise.resolve({
			scopeKey: this.memory.scopeKey,
			concepts: this.memory.concepts,
		});
	}

	getVocabulary(): Promise<{
		scopeKey: string;
		vocabulary: MemoryTimeline["vocabulary"];
	}> {
		return Promise.resolve({
			scopeKey: this.memory.scopeKey,
			vocabulary: this.memory.vocabulary,
		});
	}

	getMilestones(): Promise<{
		scopeKey: string;
		milestones: MemoryTimeline["milestones"];
	}> {
		return Promise.resolve({
			scopeKey: this.memory.scopeKey,
			milestones: this.memory.milestones,
		});
	}

	getConnections(): Promise<{
		scopeKey: string;
		connections: MemoryTimeline["connections"];
	}> {
		return Promise.resolve({
			scopeKey: this.memory.scopeKey,
			connections: this.memory.connections,
		});
	}

	getJourney(): Promise<LearningJourney> {
		return Promise.resolve({
			scopeKey: this.memory.scopeKey,
			journey: {
				today: {
					label: "Understand Docker",
					progressPercent: 72,
				},
				nextStep: {
					label: "Kubernetes",
					key: "kubernetes",
				},
				preparation: {
					label: "Review Dependency Injection",
					key: "dependency_injection",
				},
				longTerm: {
					label: "Move from Docker to Kubernetes",
					key: "kubernetes",
				},
			},
		});
	}

	search(request: MemorySearchRequest): Promise<MemorySearchResult> {
		const needle = request.query.toLowerCase();
		const concepts = this.memory.concepts.filter((item) =>
			item.label.toLowerCase().includes(needle),
		);
		const entries = this.memory.timeline.filter(
			(entry) =>
				entry.label.toLowerCase().includes(needle) ||
				entry.detail.toLowerCase().includes(needle),
		);

		return Promise.resolve({
			query: request.query,
			concepts,
			entries,
			total: concepts.length + entries.length,
		});
	}

	reset(): Promise<MemoryTimeline> {
		this.memory = {
			...defaultMemory,
			timeline: [defaultTimeline[0]],
			concepts: [],
			vocabulary: [],
			milestones: [defaultTimeline[0]],
			knowledge: [],
			connections: [],
		};

		return Promise.resolve(this.memory);
	}
}
