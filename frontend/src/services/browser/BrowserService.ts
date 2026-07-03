import type { BrowserRepository } from "./BrowserRepository";
import { createBrowserRepository } from "./BrowserRepositoryFactory";
import type {
	ConnectBrowserRequest,
	DetectBrowserPlatformRequest,
	UpdateBrowserContextRequest,
	UpdateBrowserPermissionsRequest,
} from "./types";

export class BrowserService {
	private readonly repository: BrowserRepository;

	constructor(repository: BrowserRepository = createBrowserRepository()) {
		this.repository = repository;
	}

	getSession(scopeKey?: string) {
		return this.repository.getSession(scopeKey);
	}

	connect(request: ConnectBrowserRequest) {
		return this.repository.connect(request);
	}

	disconnect(scopeKey?: string) {
		return this.repository.disconnect(scopeKey);
	}

	updateContext(request: UpdateBrowserContextRequest) {
		return this.repository.updateContext(request);
	}

	detectPlatform(request: DetectBrowserPlatformRequest) {
		return this.repository.detectPlatform(request);
	}

	getPermissions(scopeKey?: string) {
		return this.repository.getPermissions(scopeKey);
	}

	updatePermissions(request: UpdateBrowserPermissionsRequest) {
		return this.repository.updatePermissions(request);
	}

	getHistory(limit?: number, scopeKey?: string) {
		return this.repository.getHistory(limit, scopeKey);
	}

	getExplain(scopeKey?: string) {
		return this.repository.getExplain(scopeKey);
	}
}

export const browserService = new BrowserService();
