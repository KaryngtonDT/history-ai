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

export interface BrowserRepository {
	getSession(scopeKey?: string): Promise<BrowserSessionResponse>;

	connect(request: ConnectBrowserRequest): Promise<BrowserWorkspace>;

	disconnect(scopeKey?: string): Promise<BrowserWorkspace>;

	updateContext(
		request: UpdateBrowserContextRequest,
	): Promise<UpdateBrowserContextResponse>;

	detectPlatform(
		request: DetectBrowserPlatformRequest,
	): Promise<BrowserPlatformResult>;

	getPermissions(scopeKey?: string): Promise<BrowserPermissionsResponse>;

	updatePermissions(
		request: UpdateBrowserPermissionsRequest,
	): Promise<BrowserPermissionsResponse>;

	getHistory(limit?: number, scopeKey?: string): Promise<BrowserHistory>;

	getExplain(scopeKey?: string): Promise<BrowserExplain>;
}
