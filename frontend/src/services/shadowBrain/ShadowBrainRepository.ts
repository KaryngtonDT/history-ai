import type {
	AddBookmarkRequest,
	AddNoteRequest,
	BrainDashboard,
	ConceptDetail,
	ConceptTreeResponse,
	KnowledgeBookmark,
	KnowledgeDiff,
	KnowledgeNote,
	KnowledgeSearchResponse,
	RebuildWorkspaceRequest,
	TimelineResponse,
} from "./types";

export interface ShadowBrainRepository {
	getDashboard(scopeKey?: string): Promise<BrainDashboard>;
	getConceptTree(scopeKey?: string): Promise<ConceptTreeResponse>;
	getConcept(id: string, scopeKey?: string): Promise<ConceptDetail>;
	search(query: string, scopeKey?: string): Promise<KnowledgeSearchResponse>;
	getTimeline(
		from?: string,
		to?: string,
		scopeKey?: string,
	): Promise<TimelineResponse>;
	getDiff(
		resourceType: string,
		resourceId: string,
		scopeKey?: string,
	): Promise<KnowledgeDiff>;
	addBookmark(request: AddBookmarkRequest): Promise<KnowledgeBookmark>;
	addNote(request: AddNoteRequest): Promise<KnowledgeNote>;
	removeBookmark(id: string, scopeKey?: string): Promise<void>;
	rebuild(request?: RebuildWorkspaceRequest): Promise<BrainDashboard>;
}
