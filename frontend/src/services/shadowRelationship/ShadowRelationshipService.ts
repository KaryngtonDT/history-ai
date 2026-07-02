import type { ShadowRelationshipRepository } from "./ShadowRelationshipRepository";
import { createShadowRelationshipRepository } from "./ShadowRelationshipRepositoryFactory";
import type {
	RecordRelationshipSignalRequest,
	UpdateRelationshipPreferencesRequest,
} from "./types";

export class ShadowRelationshipService {
	private readonly repository: ShadowRelationshipRepository;

	constructor(
		repository: ShadowRelationshipRepository = createShadowRelationshipRepository(),
	) {
		this.repository = repository;
	}

	getProfile(scopeKey?: string) {
		return this.repository.getProfile(scopeKey);
	}

	getPortrait(scopeKey?: string) {
		return this.repository.getPortrait(scopeKey);
	}

	getTimeline(scopeKey?: string) {
		return this.repository.getTimeline(scopeKey);
	}

	getInterests(scopeKey?: string) {
		return this.repository.getInterests(scopeKey);
	}

	recordSignal(request: RecordRelationshipSignalRequest) {
		return this.repository.recordSignal(request);
	}

	updatePreferences(request: UpdateRelationshipPreferencesRequest) {
		return this.repository.updatePreferences(request);
	}

	reset(scopeKey?: string) {
		return this.repository.reset(scopeKey);
	}

	configure(utterance: string, confirmed = false, scopeKey?: string) {
		return this.repository.configure(utterance, confirmed, scopeKey);
	}

	approveChange(changeId: string, scopeKey?: string) {
		return this.repository.approveChange(changeId, scopeKey);
	}

	rejectChange(changeId: string, scopeKey?: string) {
		return this.repository.rejectChange(changeId, scopeKey);
	}
}

export const shadowRelationshipService = new ShadowRelationshipService();
