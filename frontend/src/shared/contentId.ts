const CONTENT_ID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export function isValidContentUuid(contentId: string): boolean {
	const normalized = contentId.trim();

	return normalized !== "" && CONTENT_ID_PATTERN.test(normalized);
}

export function resolveChatContentId(
	contentId: string,
	artifacts: ReadonlyArray<{ contentId: string }>,
): string | null {
	if (isValidContentUuid(contentId)) {
		return contentId.trim();
	}

	for (const artifact of artifacts) {
		if (isValidContentUuid(artifact.contentId)) {
			return artifact.contentId.trim();
		}
	}

	return null;
}
