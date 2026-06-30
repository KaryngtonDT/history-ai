export interface QualityMetric {
	category: string;
	score: number;
	explanation: string;
}

export interface QualityReport {
	id: string;
	videoId?: string;
	overallScore: number;
	recommendation: string;
	metrics: QualityMetric[];
	explanations: string[];
}

export const QUALITY_CATEGORY_LABELS: Record<string, string> = {
	audio: "Audio",
	translation: "Translation",
	voice_clone: "Voice Clone",
	lip_sync: "Lip Sync",
	rendering: "Rendering",
};

export const PUBLICATION_RECOMMENDATION_LABELS: Record<string, string> = {
	ready: "Ready for publishing",
	review_recommended: "Review recommended",
	regenerate_required: "Regenerate required",
};
