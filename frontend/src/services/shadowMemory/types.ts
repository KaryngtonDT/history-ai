export type MemoryCategory =
	| "concept"
	| "vocabulary"
	| "milestone"
	| "question"
	| "session"
	| "challenge";

export type KnowledgeProgress = "new" | "learning" | "reviewing" | "mastered";

export interface MemoryTimelineEntry {
	id: string;
	recordedAt: string;
	category: MemoryCategory;
	importance: string;
	confidence: string;
	label: string;
	detail: string;
	videoId?: string | null;
	segmentIndex?: number | null;
	sessionId?: string | null;
	conversationId?: string | null;
	concepts: string[];
	sources: string[];
}

export interface KnowledgeItem {
	key: string;
	label: string;
	category: MemoryCategory;
	progress: KnowledgeProgress;
	progressPercent: number;
	exposureCount: number;
	questionCount: number;
	challengeSuccessCount: number;
	videoIds: string[];
	sessionIds: string[];
	explanation: string;
	lastSeenAt?: string | null;
}

export interface KnowledgeConnection {
	fromKey: string;
	toKey: string;
	label: string;
	reason: string;
}

export interface MemoryTimeline {
	id: string;
	scopeKey: string;
	memoryEnabled: boolean;
	timeline: MemoryTimelineEntry[];
	concepts: KnowledgeItem[];
	vocabulary: KnowledgeItem[];
	milestones: MemoryTimelineEntry[];
	knowledge: KnowledgeItem[];
	connections: KnowledgeConnection[];
}

export interface LearningJourneyStep {
	label: string;
	key?: string;
	reason?: string;
	progressPercent?: number;
}

export interface LearningJourney {
	scopeKey: string;
	journey: {
		today: LearningJourneyStep | null;
		nextStep: LearningJourneyStep | null;
		preparation: LearningJourneyStep | null;
		longTerm: LearningJourneyStep | null;
	};
}

export interface MemorySearchResult {
	query: string;
	concepts: KnowledgeItem[];
	entries: Array<{
		id: string;
		label: string;
		detail: string;
		category: MemoryCategory;
		recordedAt: string;
		videoId?: string | null;
	}>;
	total: number;
}

export interface MemorySearchRequest {
	query: string;
	scopeKey?: string;
}
