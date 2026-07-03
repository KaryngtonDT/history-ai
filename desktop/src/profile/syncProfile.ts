import { API_BASE_URL } from "../app/config";

export interface ShadowProfileSummary {
	identityLabel: string;
	conceptCount: number;
}

export async function syncShadowProfile(
	apiBaseUrl: string = API_BASE_URL,
): Promise<ShadowProfileSummary> {
	const contextResponse = await fetch(
		`${apiBaseUrl}/api/shadow/presence/context?surface=desktop`,
		{ headers: { Accept: "application/json" } },
	);

	if (!contextResponse.ok) {
		throw new Error("Could not sync Shadow profile");
	}

	const context = (await contextResponse.json()) as {
		identityLabel: string;
		conceptCount: number;
	};

	return {
		identityLabel: context.identityLabel,
		conceptCount: context.conceptCount,
	};
}

export async function connectDesktop(apiBaseUrl: string = API_BASE_URL) {
	const response = await fetch(`${apiBaseUrl}/api/shadow/presence/connect`, {
		method: "POST",
		headers: {
			Accept: "application/json",
			"Content-Type": "application/json",
		},
		body: JSON.stringify({ surface: "desktop" }),
	});

	if (!response.ok) {
		throw new Error("Could not connect desktop presence");
	}

	return response.json();
}
