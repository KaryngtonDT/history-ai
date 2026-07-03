import { createMobileRepository } from "./MobileRepositoryFactory";
import type { MobileRepository } from "./MobileRepository";

class MobileService {
	private readonly repository: MobileRepository;

	constructor(repository: MobileRepository) {
		this.repository = repository;
	}

	getProfile(scopeKey?: string) {
		return this.repository.getProfile(scopeKey);
	}

	getToday(scopeKey?: string) {
		return this.repository.getToday(scopeKey);
	}

	getMissions(scopeKey?: string) {
		return this.repository.getMissions(scopeKey);
	}

	getRevisions(scopeKey?: string) {
		return this.repository.getRevisions(scopeKey);
	}

	getServer(scopeKey?: string) {
		return this.repository.getServer(scopeKey);
	}

	getHealth(scopeKey?: string) {
		return this.repository.getHealth(scopeKey);
	}

	getConnections(scopeKey?: string) {
		return this.repository.getConnections(scopeKey);
	}

	registerDevice(
		request: Parameters<MobileRepository["registerDevice"]>[0],
	) {
		return this.repository.registerDevice(request);
	}

	sync(scopeKey?: string) {
		return this.repository.sync(scopeKey);
	}

	updatePreferences(
		request: Parameters<MobileRepository["updatePreferences"]>[0],
	) {
		return this.repository.updatePreferences(request);
	}

	updateConnection(
		request: Parameters<MobileRepository["updateConnection"]>[0],
	) {
		return this.repository.updateConnection(request);
	}

	registerPushToken(token: string, scopeKey?: string) {
		return this.repository.registerPushToken(token, scopeKey);
	}
}

export const mobileService = new MobileService(createMobileRepository());
