export type RelationshipTraitType =
	| "interest"
	| "habit"
	| "motivator"
	| "communication";

export type RelationshipStrength = "low" | "medium" | "high" | "very_high";

export interface RelationshipTrait {
	type: RelationshipTraitType;
	key: string;
	label: string;
	strength: RelationshipStrength;
	source: string;
	confirmed: boolean;
	enabled: boolean;
	explanation: string;
}

export interface RelationshipTimelineEntry {
	id: string;
	type: string;
	label: string;
	detail: string;
	recordedAt: string;
}

export interface RelationshipPendingChange {
	id: string;
	label: string;
	status: string;
	createdAt?: string;
	trait: {
		type: RelationshipTraitType;
		key: string;
		label: string;
		strength?: RelationshipStrength;
	};
}

export interface RelationshipProfile {
	id: string;
	scopeKey: string;
	relationshipScore: number;
	preferences: {
		adaptiveEnabled: boolean;
		rememberRelationship: boolean;
		requireApprovalForInferences: boolean;
	};
	settings: {
		showHypotheses: boolean;
		showTimeline: boolean;
		allowConversationalUpdates: boolean;
	};
	traits: RelationshipTrait[];
	timeline: RelationshipTimelineEntry[];
	sharedReferences: Array<{
		id: string;
		kind: string;
		label: string;
		detail: string;
		recordedAt: string;
	}>;
	pendingChanges: RelationshipPendingChange[];
}

export interface RelationshipPortrait {
	relationshipScore: number;
	confirmed: Array<{
		type: RelationshipTraitType;
		key: string;
		label: string;
		strength: RelationshipStrength;
		explanation: string;
	}>;
	hypotheses: Array<{
		type: RelationshipTraitType;
		key: string;
		label: string;
		strength: RelationshipStrength;
		explanation: string;
	}>;
	questions: Array<{ id: string; text: string }>;
	pendingChanges: RelationshipPendingChange[];
}

export interface RelationshipConfigureResult {
	intent: string;
	previewLabel: string;
	requiresConfirmation: boolean;
	confirmationMessage: string;
	applied: boolean;
	profile: RelationshipProfile;
	portrait: RelationshipPortrait;
}

export interface RecordRelationshipSignalRequest {
	scopeKey?: string;
	source: string;
	kind: string;
	data?: Record<string, unknown>;
}

export interface UpdateRelationshipPreferencesRequest {
	scopeKey?: string;
	adaptiveEnabled?: boolean;
	rememberRelationship?: boolean;
	trait?: {
		type: RelationshipTraitType;
		key: string;
		label: string;
		strength: RelationshipStrength;
		explanation?: string;
	};
	removeTrait?: {
		type: RelationshipTraitType;
		key: string;
	};
}
