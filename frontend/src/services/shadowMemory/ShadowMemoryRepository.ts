import type {
	LearningJourney,
	MemorySearchRequest,
	MemorySearchResult,
	MemoryTimeline,
} from "./types";

export interface ShadowMemoryRepository {
	getTimeline(scopeKey?: string): Promise<MemoryTimeline>;
	getConcepts(scopeKey?: string): Promise<{
		scopeKey: string;
		concepts: MemoryTimeline["concepts"];
	}>;
	getVocabulary(scopeKey?: string): Promise<{
		scopeKey: string;
		vocabulary: MemoryTimeline["vocabulary"];
	}>;
	getMilestones(scopeKey?: string): Promise<{
		scopeKey: string;
		milestones: MemoryTimeline["milestones"];
	}>;
	getConnections(scopeKey?: string): Promise<{
		scopeKey: string;
		connections: MemoryTimeline["connections"];
	}>;
	getJourney(scopeKey?: string): Promise<LearningJourney>;
	search(request: MemorySearchRequest): Promise<MemorySearchResult>;
	reset(scopeKey?: string): Promise<MemoryTimeline>;
}
