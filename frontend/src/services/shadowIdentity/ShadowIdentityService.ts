import type { ShadowIdentityRepository } from "./ShadowIdentityRepository";
import { createShadowIdentityRepository } from "./ShadowIdentityRepositoryFactory";
import type { UpdateShadowIdentityPreferencesRequest } from "./types";

export class ShadowIdentityService {
	private readonly repository: ShadowIdentityRepository;

	constructor(
		repository: ShadowIdentityRepository = createShadowIdentityRepository(),
	) {
		this.repository = repository;
	}

	getProfile(scopeKey?: string) {
		return this.repository.getProfile(scopeKey);
	}

	updatePreferences(request: UpdateShadowIdentityPreferencesRequest) {
		return this.repository.updatePreferences(request);
	}

	reset(scopeKey?: string) {
		return this.repository.reset(scopeKey);
	}

	configure(utterance: string, confirmed = false, scopeKey?: string) {
		return this.repository.configure(utterance, confirmed, scopeKey);
	}
}

export const shadowIdentityService = new ShadowIdentityService();
