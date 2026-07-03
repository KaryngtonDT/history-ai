import {
	SHADOW_BROWSER_CONNECT_PATH,
	SHADOW_BROWSER_CONTEXT_PATH,
	SHADOW_BROWSER_DISCONNECT_PATH,
	SHADOW_BROWSER_EXPLAIN_PATH,
	SHADOW_BROWSER_HISTORY_PATH,
	SHADOW_BROWSER_PERMISSIONS_PATH,
	SHADOW_BROWSER_PLATFORM_PATH,
	SHADOW_BROWSER_SESSION_PATH,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { BrowserRepository } from "./BrowserRepository";
import type {
	BrowserExplain,
	BrowserHistory,
	BrowserPermissionsResponse,
	BrowserPlatformResult,
	BrowserSessionResponse,
	BrowserWorkspace,
	ConnectBrowserRequest,
	DetectBrowserPlatformRequest,
	UpdateBrowserContextRequest,
	UpdateBrowserContextResponse,
	UpdateBrowserPermissionsRequest,
} from "./types";

function scopeQuery(scopeKey?: string): string {
	return scopeKey ? `scopeKey=${encodeURIComponent(scopeKey)}` : "";
}

function appendQuery(base: string, params: string[]): string {
	const filtered = params.filter((param) => param.length > 0);
	if (filtered.length === 0) {
		return base;
	}

	const separator = base.includes("?") ? "&" : "?";

	return `${base}${separator}${filtered.join("&")}`;
}

export class HttpBrowserRepository implements BrowserRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	getSession(scopeKey?: string): Promise<BrowserSessionResponse> {
		const query = scopeQuery(scopeKey);

		return this.httpClient.get<BrowserSessionResponse>(
			appendQuery(SHADOW_BROWSER_SESSION_PATH, query ? [query] : []),
		);
	}

	connect(request: ConnectBrowserRequest): Promise<BrowserWorkspace> {
		return this.httpClient.post<BrowserWorkspace>(
			SHADOW_BROWSER_CONNECT_PATH,
			request,
		);
	}

	disconnect(scopeKey?: string): Promise<BrowserWorkspace> {
		return this.httpClient.post<BrowserWorkspace>(
			SHADOW_BROWSER_DISCONNECT_PATH,
			scopeKey ? { scopeKey } : {},
		);
	}

	updateContext(
		request: UpdateBrowserContextRequest,
	): Promise<UpdateBrowserContextResponse> {
		return this.httpClient.post<UpdateBrowserContextResponse>(
			SHADOW_BROWSER_CONTEXT_PATH,
			request,
		);
	}

	detectPlatform(
		request: DetectBrowserPlatformRequest,
	): Promise<BrowserPlatformResult> {
		return this.httpClient.post<BrowserPlatformResult>(
			SHADOW_BROWSER_PLATFORM_PATH,
			request,
		);
	}

	getPermissions(scopeKey?: string): Promise<BrowserPermissionsResponse> {
		const query = scopeQuery(scopeKey);

		return this.httpClient.get<BrowserPermissionsResponse>(
			appendQuery(SHADOW_BROWSER_PERMISSIONS_PATH, query ? [query] : []),
		);
	}

	updatePermissions(
		request: UpdateBrowserPermissionsRequest,
	): Promise<BrowserPermissionsResponse> {
		return this.httpClient.put<BrowserPermissionsResponse>(
			SHADOW_BROWSER_PERMISSIONS_PATH,
			request,
		);
	}

	getHistory(limit?: number, scopeKey?: string): Promise<BrowserHistory> {
		const params = [scopeQuery(scopeKey)];
		if (typeof limit === "number") {
			params.push(`limit=${limit}`);
		}

		return this.httpClient.get<BrowserHistory>(
			appendQuery(SHADOW_BROWSER_HISTORY_PATH, params),
		);
	}

	getExplain(scopeKey?: string): Promise<BrowserExplain> {
		const query = scopeQuery(scopeKey);

		return this.httpClient.get<BrowserExplain>(
			appendQuery(SHADOW_BROWSER_EXPLAIN_PATH, query ? [query] : []),
		);
	}
}
