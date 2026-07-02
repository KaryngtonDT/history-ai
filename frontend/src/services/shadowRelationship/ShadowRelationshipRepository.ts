import type {
	RecordRelationshipSignalRequest,
	RelationshipConfigureResult,
	RelationshipPortrait,
	RelationshipProfile,
	UpdateRelationshipPreferencesRequest,
} from "./types";

export interface ShadowRelationshipRepository {
	getProfile(scopeKey?: string): Promise<RelationshipProfile>;
	getPortrait(scopeKey?: string): Promise<RelationshipPortrait>;
	getTimeline(
		scopeKey?: string,
	): Promise<{ scopeKey: string; timeline: RelationshipProfile["timeline"] }>;
	getInterests(scopeKey?: string): Promise<{
		scopeKey: string;
		interests: Array<{
			key: string;
			label: string;
			strength: string;
			confirmed: boolean;
		}>;
	}>;
	recordSignal(
		request: RecordRelationshipSignalRequest,
	): Promise<RelationshipProfile>;
	updatePreferences(
		request: UpdateRelationshipPreferencesRequest,
	): Promise<RelationshipProfile>;
	reset(scopeKey?: string): Promise<RelationshipProfile>;
	configure(
		utterance: string,
		confirmed?: boolean,
		scopeKey?: string,
	): Promise<RelationshipConfigureResult>;
	approveChange(
		changeId: string,
		scopeKey?: string,
	): Promise<{
		profile: RelationshipProfile;
		portrait: RelationshipPortrait;
	}>;
	rejectChange(
		changeId: string,
		scopeKey?: string,
	): Promise<{
		profile: RelationshipProfile;
		portrait: RelationshipPortrait;
	}>;
}
