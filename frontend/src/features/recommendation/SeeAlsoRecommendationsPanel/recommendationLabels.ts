import type { RecommendationReason } from "@/services/recommendation/types";

export const REASON_LABELS: Record<RecommendationReason, string> = {
	related: "Related",
	derived_from: "Derived from",
	references: "References",
	next: "Next",
	previous: "Previous",
};
