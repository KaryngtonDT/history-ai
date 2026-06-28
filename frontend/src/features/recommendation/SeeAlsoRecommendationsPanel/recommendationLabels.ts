import type { RecommendationReason } from "@/services/recommendation/types";

export const REASON_LABELS: Record<RecommendationReason, string> = {
	related: "Related",
	derived_from: "Derived from",
	references: "References",
	next: "Next",
	previous: "Previous",
};

export function formatRecommendationScoreLabel(
	score: number | undefined,
): string | null {
	if (score === undefined) {
		return null;
	}

	return `${score}% relevant`;
}
