export interface LearningPreference {
	key: string;
	enabled: boolean;
}

export interface LearningSignal {
	id: string;
	type: string;
	recordedAt: string;
	context: Record<string, unknown>;
}

export interface LearningInsight {
	id: string;
	type: string;
	summary: string;
	sourceSignalIds: string[];
	generatedAt: string;
}

export interface LearningRecommendation {
	id: string;
	type: string;
	explanation: string;
	sourceInsightIds: string[];
	generatedAt: string;
}

export interface LearningProfile {
	id: string;
	scopeKey: string;
	adaptiveRecommendationsEnabled: boolean;
	preferences: LearningPreference[];
	signals: LearningSignal[];
	insights: LearningInsight[];
	recommendations: LearningRecommendation[];
}

export interface LearningAdaptiveHints {
	active: boolean;
	explanationStyle: string | null;
	challengeLevel: string | null;
	voiceLanguage: string | null;
	translationStyle: string | null;
	preferredProvider: string | null;
	appliedRecommendations: string[];
}

export interface LearningRecommendationsResponse {
	scopeKey: string;
	adaptiveRecommendationsEnabled: boolean;
	recommendations: LearningRecommendation[];
	adaptiveHints: LearningAdaptiveHints;
	profile: LearningProfile;
}

export interface UpdateLearningPreferencesRequest {
	scopeKey?: string;
	adaptiveRecommendationsEnabled?: boolean;
}
