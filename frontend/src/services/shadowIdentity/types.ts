export interface ShadowIdentityProfile {
	id: string;
	scopeKey: string;
	preferences: {
		persona: string;
		conversationStyle: string;
		teachingStyle: string;
		narrationStyle: string;
		answerStyle: string;
		challengeLevel: number;
		examplesLevel: number;
		storiesLevel: number;
		debateLevel: number;
		humorLevel: string;
		voiceProfile: {
			voiceId: string;
			engine: string;
			speed: number;
			warmth: number;
			energy: number;
		};
		languageProfile: {
			primaryLanguage: string;
			secondaryLanguage: string | null;
			technicalLanguage: string;
			technicalTermsPolicy: string;
			pronunciation: string;
			summaryLanguage: string | null;
		};
		memoryPolicy: {
			knownSkills: string[];
			unknownSkills: string[];
			goals: string[];
			interests: string[];
		};
	};
	dna: Record<string, number>;
	history: Array<{
		id: string;
		label: string;
		source: string;
		recordedAt: string;
	}>;
}

export interface ShadowConfigurationResult {
	intent: string;
	confidence: number;
	explanation: string;
	requiresConfirmation: boolean;
	confirmationMessage: string;
	applied: boolean;
	profile: ShadowIdentityProfile;
}

export interface UpdateShadowIdentityPreferencesRequest {
	scopeKey?: string;
	persona?: string;
	challengeLevel?: number;
	voiceProfile?: {
		voiceId?: string;
		engine?: string;
		speed?: number;
	};
}
