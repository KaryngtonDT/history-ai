export function formatApiErrorBody(body: unknown): string {
	if (null === body || undefined === body) {
		return "";
	}

	if (typeof body === "string") {
		return body.trim();
	}

	if (typeof body !== "object") {
		return String(body);
	}

	const record = body as Record<string, unknown>;
	const parts: string[] = [];

	if (typeof record.message === "string" && record.message.trim() !== "") {
		parts.push(record.message.trim());
	}

	if (
		typeof record.failureMessage === "string" &&
		record.failureMessage.trim() !== ""
	) {
		parts.push(`failure: ${record.failureMessage.trim()}`);
	}

	if (typeof record.failedStage === "string" && record.failedStage.trim() !== "") {
		parts.push(`stage: ${record.failedStage.trim()}`);
	}

	if (typeof record.videoStatus === "string" && record.videoStatus.trim() !== "") {
		parts.push(`videoStatus: ${record.videoStatus.trim()}`);
	}

	if (
		typeof record.lastProcessingDurationSeconds === "number" &&
		Number.isFinite(record.lastProcessingDurationSeconds)
	) {
		parts.push(
			`duration: ${Math.round(record.lastProcessingDurationSeconds)}s`,
		);
	}

	if (record.networkError === true) {
		parts.push("network: unreachable");
	}

	if (typeof record.status === "number" && Number.isFinite(record.status)) {
		parts.push(`httpStatus: ${record.status}`);
	}

	if (typeof record.error === "string" && record.error.trim() !== "") {
		parts.push(`error: ${record.error.trim()}`);
	}

	if (parts.length > 0) {
		return parts.join(" | ");
	}

	try {
		return JSON.stringify(body);
	} catch {
		return "Unknown API error";
	}
}

export async function readApiErrorResponse(
	response: Response,
): Promise<{ body?: unknown; detail: string }> {
	const contentType = response.headers.get("content-type") ?? "";

	try {
		if (contentType.includes("application/json")) {
			const body = (await response.json()) as unknown;

			return {
				body,
				detail: formatApiErrorBody(body) || response.statusText,
			};
		}

		const text = (await response.text()).trim();
		const detail = truncateErrorText(text || response.statusText);

		return {
			body: text || undefined,
			detail,
		};
	} catch {
		return { detail: response.statusText };
	}
}

const MAX_ERROR_TEXT_LENGTH = 500;

function truncateErrorText(text: string): string {
	if (text.length <= MAX_ERROR_TEXT_LENGTH) {
		return text;
	}

	return `${text.slice(0, MAX_ERROR_TEXT_LENGTH)}…`;
}
