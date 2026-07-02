import {
	SHADOW_RELATIONSHIP_APPROVE_CHANGE_PATH,
	SHADOW_RELATIONSHIP_CONFIGURE_PATH,
	SHADOW_RELATIONSHIP_INTERESTS_PATH,
	SHADOW_RELATIONSHIP_PORTRAIT_PATH,
	SHADOW_RELATIONSHIP_PREFERENCES_PATH,
	SHADOW_RELATIONSHIP_PROFILE_PATH,
	SHADOW_RELATIONSHIP_REJECT_CHANGE_PATH,
	SHADOW_RELATIONSHIP_RESET_PATH,
	SHADOW_RELATIONSHIP_SIGNALS_PATH,
	SHADOW_RELATIONSHIP_TIMELINE_PATH,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { ShadowRelationshipRepository } from "./ShadowRelationshipRepository";
import type {
	RecordRelationshipSignalRequest,
	RelationshipConfigureResult,
	RelationshipPortrait,
	RelationshipProfile,
	UpdateRelationshipPreferencesRequest,
} from "./types";

export class HttpShadowRelationshipRepository
	implements ShadowRelationshipRepository
{
	constructor(private readonly httpClient: HttpClient) {}

	getProfile(scopeKey?: string): Promise<RelationshipProfile> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<RelationshipProfile>(
			`${SHADOW_RELATIONSHIP_PROFILE_PATH}${query}`,
		);
	}

	getPortrait(scopeKey?: string): Promise<RelationshipPortrait> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<RelationshipPortrait>(
			`${SHADOW_RELATIONSHIP_PORTRAIT_PATH}${query}`,
		);
	}

	getTimeline(scopeKey?: string) {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get(`${SHADOW_RELATIONSHIP_TIMELINE_PATH}${query}`);
	}

	getInterests(scopeKey?: string) {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get(`${SHADOW_RELATIONSHIP_INTERESTS_PATH}${query}`);
	}

	recordSignal(
		request: RecordRelationshipSignalRequest,
	): Promise<RelationshipProfile> {
		return this.httpClient.post<RelationshipProfile>(
			SHADOW_RELATIONSHIP_SIGNALS_PATH,
			request,
		);
	}

	updatePreferences(
		request: UpdateRelationshipPreferencesRequest,
	): Promise<RelationshipProfile> {
		return this.httpClient.post<RelationshipProfile>(
			SHADOW_RELATIONSHIP_PREFERENCES_PATH,
			request,
		);
	}

	reset(scopeKey?: string): Promise<RelationshipProfile> {
		return this.httpClient.post<RelationshipProfile>(
			SHADOW_RELATIONSHIP_RESET_PATH,
			scopeKey ? { scopeKey } : {},
		);
	}

	configure(
		utterance: string,
		confirmed = false,
		scopeKey?: string,
	): Promise<RelationshipConfigureResult> {
		return this.httpClient.post<RelationshipConfigureResult>(
			SHADOW_RELATIONSHIP_CONFIGURE_PATH,
			{ utterance, confirmed, scopeKey },
		);
	}

	approveChange(changeId: string, scopeKey?: string) {
		return this.httpClient.post(
			SHADOW_RELATIONSHIP_APPROVE_CHANGE_PATH(changeId),
			scopeKey ? { scopeKey } : {},
		);
	}

	rejectChange(changeId: string, scopeKey?: string) {
		return this.httpClient.post(
			SHADOW_RELATIONSHIP_REJECT_CHANGE_PATH(changeId),
			scopeKey ? { scopeKey } : {},
		);
	}
}
