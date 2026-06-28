export function contentArtifactRecommendationsPath(
	contentId: string,
	artifactId: string,
): string {
	return `${CONTENTS_PATH}/${contentId}/artifacts/${artifactId}/recommendations`;
}
