import type { Content, ContentStatistics } from "./domain/Content";

const ARTIFACTS_PLACEHOLDER = 12;

export function computeStatistics(contents: Content[]): ContentStatistics {
	return {
		contents: contents.length,
		completed: contents.filter((item) => item.status === "completed").length,
		processing: contents.filter((item) => item.status === "processing").length,
		artifacts: ARTIFACTS_PLACEHOLDER,
	};
}
