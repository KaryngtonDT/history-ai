export type ReviewCategory =
	| "overall"
	| "translation"
	| "voice_clone"
	| "lip_sync"
	| "rendering";

export type ReviewScores = Record<ReviewCategory, number>;

export interface Review {
	id: string;
	videoId: string;
	executionVersionNumber: number;
	scores: ReviewScores;
	comment: string;
	createdAt: string;
}

export interface PreferenceProfile {
	translationStyle: string;
	voiceStability: string;
	renderingPreset: string;
	lipSyncStrength: string;
	latestComment: string;
	reviewCount: number;
	explanationLines: string[];
}

export interface SaveReviewInput {
	executionVersionNumber: number;
	scores: ReviewScores;
	comment: string;
}

export interface ReviewApiDto {
	id: string;
	videoId: string;
	executionVersionNumber: number;
	scores: ReviewScores;
	comment: string;
	createdAt: string;
}

export interface PreferenceProfileApiDto {
	translationStyle: string;
	voiceStability: string;
	renderingPreset: string;
	lipSyncStrength: string;
	latestComment: string;
	reviewCount: number;
	explanationLines: string[];
}

export const REVIEW_CATEGORY_LABELS: Record<ReviewCategory, string> = {
	overall: "Overall",
	translation: "Translation",
	voice_clone: "Voice",
	lip_sync: "Lip Sync",
	rendering: "Rendering",
};

export const REVIEW_CATEGORIES: ReviewCategory[] = [
	"overall",
	"translation",
	"voice_clone",
	"lip_sync",
	"rendering",
];

export function mapReviewFromApi(dto: ReviewApiDto): Review {
	return {
		id: dto.id,
		videoId: dto.videoId,
		executionVersionNumber: dto.executionVersionNumber,
		scores: { ...dto.scores },
		comment: dto.comment,
		createdAt: dto.createdAt,
	};
}

export function mapPreferenceProfileFromApi(
	dto: PreferenceProfileApiDto,
): PreferenceProfile {
	return {
		translationStyle: dto.translationStyle,
		voiceStability: dto.voiceStability,
		renderingPreset: dto.renderingPreset,
		lipSyncStrength: dto.lipSyncStrength,
		latestComment: dto.latestComment,
		reviewCount: dto.reviewCount,
		explanationLines: [...dto.explanationLines],
	};
}

export const DEFAULT_REVIEW_SCORES: ReviewScores = {
	overall: 4,
	translation: 5,
	voice_clone: 3,
	lip_sync: 5,
	rendering: 5,
};
