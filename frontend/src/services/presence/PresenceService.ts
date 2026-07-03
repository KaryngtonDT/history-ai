import type { PresenceRepository } from "./PresenceRepository";
import { createPresenceRepository } from "./PresenceRepositoryFactory";
import type {
	ConnectPresenceRequest,
	UpdatePresencePreferencesRequest,
} from "./types";

export class PresenceService {
	private readonly repository: PresenceRepository;

	constructor(repository: PresenceRepository = createPresenceRepository()) {
		this.repository = repository;
	}

	getSession(scopeKey?: string) {
		return this.repository.getSession(scopeKey);
	}

	connect(request: ConnectPresenceRequest) {
		return this.repository.connect(request);
	}

	disconnect(scopeKey?: string) {
		return this.repository.disconnect(scopeKey);
	}

	getContext(surface?: string, scopeKey?: string) {
		return this.repository.getContext(surface, scopeKey);
	}

	updatePreferences(request: UpdatePresencePreferencesRequest) {
		return this.repository.updatePreferences(request);
	}

	getHistory(limit?: number, scopeKey?: string) {
		return this.repository.getHistory(limit, scopeKey);
	}

	getExplain(scopeKey?: string) {
		return this.repository.getExplain(scopeKey);
	}
}

export const presenceService = new PresenceService();
