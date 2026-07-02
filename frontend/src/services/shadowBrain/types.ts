export type KnowledgeSourceType =
	| "video"
	| "pdf"
	| "audio"
	| "youtube"
	| "conversation"
	| "mission"
	| "exercise"
	| "teaching";

export interface KnowledgeEntry {
	id: string;
	conceptKey: string;
	label: string;
	summary: string;
	masteryPercent: number;
	firstSeenAt: string;
	lastSeenAt: string;
	exposureCount: number;
	exerciseCount: number;
	explanationCount: number;
	relatedKeys: string[];
	recommendations: string[];
}

export interface KnowledgeSource {
	id: string;
	type: KnowledgeSourceType;
	label: string;
	resourceId: string;
	resourceLabel: string;
	conceptKey: string | null;
	occurredAt: string | null;
	detail: string | null;
	linkHint: string | null;
}

export interface KnowledgeBookmark {
	id: string;
	label: string;
	tags: string[];
	conceptKey: string | null;
	resourceType: KnowledgeSourceType | null;
	resourceId: string | null;
}

export interface KnowledgeNote {
	id: string;
	body: string;
	createdAt: string;
	conceptKey: string | null;
}

export interface KnowledgeInsight {
	id: string;
	kind: string;
	label: string;
	detail: string;
	conceptKey: string | null;
}

export interface KnowledgeRevision {
	conceptKey: string;
	dueAt: string;
	reason: string;
}

export interface KnowledgeTimelineEvent {
	id: string;
	label: string;
	occurredAt: string;
	conceptKey: string | null;
	sourceType: KnowledgeSourceType | null;
	resourceId: string | null;
}

export interface KnowledgeDomainHeatmapEntry {
	key: string;
	label: string;
	percent: number;
}

export interface KnowledgeStatistics {
	videoCount: number;
	pdfCount: number;
	conversationCount: number;
	exerciseCount: number;
	missionCount: number;
	conceptCount: number;
	domainHeatmap: KnowledgeDomainHeatmapEntry[];
}

export interface KnowledgeWorkspace {
	id: string;
	scopeKey: string;
	workspaceEnabled: boolean;
	lastSyncedAt: string | null;
	entries: KnowledgeEntry[];
	bookmarks: KnowledgeBookmark[];
	notes: KnowledgeNote[];
	timeline: KnowledgeTimelineEvent[];
	statistics: KnowledgeStatistics;
}

export interface BrainDashboard {
	scopeKey: string;
	workspace: KnowledgeWorkspace;
	insights: KnowledgeInsight[];
	revisions: KnowledgeRevision[];
}

export interface KnowledgeTreeNode {
	id: string;
	label: string;
	conceptKey: string | null;
	entryCount: number;
	children: KnowledgeTreeNode[];
}

export interface ConceptTreeResponse {
	scopeKey: string;
	tree: KnowledgeTreeNode[];
}

export interface ConceptEvolution {
	conceptKey: string;
	firstSeenAt: string;
	explanationCount: number;
	videoCount: number;
	exerciseCount: number;
	lastRevisionAt: string | null;
	masteryPercent: number;
}

export interface ConceptDetail {
	scopeKey: string;
	entry: KnowledgeEntry;
	sources: KnowledgeSource[];
	related: KnowledgeEntry[];
	notes: KnowledgeNote[];
	evolution: ConceptEvolution;
}

export interface KnowledgeSearchHit {
	conceptKey: string;
	label: string;
	summary: string;
	masteryPercent: number;
	sourceCount: number;
}

export interface KnowledgeSearchResponse {
	scopeKey: string;
	query: string;
	hits: KnowledgeSearchHit[];
	total: number;
}

export interface TimelineResponse {
	scopeKey: string;
	events: KnowledgeTimelineEvent[];
}

export type KnowledgeDiffRedundancy = "low" | "medium" | "high";

export interface KnowledgeDiff {
	scopeKey: string;
	resourceType: string;
	resourceId: string;
	resourceLabel: string;
	newConcepts: number;
	knownConcepts: number;
	revisionDue: number;
	redundancyPercent: number;
	redundancy: KnowledgeDiffRedundancy;
	novelConceptKeys: string[];
	knownConceptKeys: string[];
	revisionConceptKeys: string[];
}

export interface AddBookmarkRequest {
	scopeKey?: string;
	label: string;
	tags?: string[];
	conceptKey?: string;
	resourceType?: KnowledgeSourceType;
	resourceId?: string;
}

export interface AddNoteRequest {
	scopeKey?: string;
	body: string;
	conceptKey?: string;
}

export interface RebuildWorkspaceRequest {
	scopeKey?: string;
}
