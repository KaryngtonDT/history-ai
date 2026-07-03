const TOKEN_KEY = "lumen.shadow.desktop.apiBase";

export function saveApiBaseUrl(url: string): void {
	localStorage.setItem(TOKEN_KEY, url);
}

export function loadApiBaseUrl(): string | null {
	return localStorage.getItem(TOKEN_KEY);
}
