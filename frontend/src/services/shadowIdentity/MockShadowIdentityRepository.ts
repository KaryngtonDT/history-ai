import type { ShadowIdentityRepository } from "./ShadowIdentityRepository";
import type {
	ShadowConfigurationResult,
	ShadowIdentityProfile,
	UpdateShadowIdentityPreferencesRequest,
} from "./types";

const defaultProfile: ShadowIdentityProfile = {
	id: "mock-shadow-identity",
	scopeKey: "default",
	preferences: {
		persona: "teacher",
		conversationStyle: "conversational",
		teachingStyle: "example_first",
		narrationStyle: "neutral",
		answerStyle: "detailed",
		challengeLevel: 3,
		examplesLevel: 8,
		storiesLevel: 5,
		debateLevel: 4,
		humorLevel: "low",
		voiceProfile: {
			voiceId: "browser-default",
			engine: "browser_tts",
			speed: 1,
			warmth: 6,
			energy: 6,
		},
		languageProfile: {
			primaryLanguage: "en",
			secondaryLanguage: null,
			technicalLanguage: "en",
			technicalTermsPolicy: "adaptive",
			pronunciation: "american",
			summaryLanguage: null,
		},
		memoryPolicy: {
			knownSkills: ["video localization"],
			unknownSkills: ["advanced grammar"],
			goals: ["understand Roman history"],
			interests: ["history"],
		},
	},
	dna: {
		curiosity: 6,
		examples: 8,
		stories: 5,
		debate: 4,
		challenge: 6,
		humor: 3,
	},
	history: [
		{
			id: "1",
			label: "Initial profile",
			source: "system",
			recordedAt: new Date().toISOString(),
		},
	],
};

export class MockShadowIdentityRepository implements ShadowIdentityRepository {
	private profile = structuredClone(defaultProfile);

	getProfile(): Promise<ShadowIdentityProfile> {
		return Promise.resolve(structuredClone(this.profile));
	}

	updatePreferences(
		request: UpdateShadowIdentityPreferencesRequest,
	): Promise<ShadowIdentityProfile> {
		if (request.persona) {
			this.profile.preferences.persona = request.persona;
		}
		if (request.challengeLevel) {
			this.profile.preferences.challengeLevel = request.challengeLevel;
		}
		if (request.voiceProfile?.voiceId) {
			this.profile.preferences.voiceProfile.voiceId =
				request.voiceProfile.voiceId;
		}
		return Promise.resolve(structuredClone(this.profile));
	}

	reset(): Promise<ShadowIdentityProfile> {
		this.profile = structuredClone(defaultProfile);
		return Promise.resolve(structuredClone(this.profile));
	}

	configure(
		utterance: string,
		confirmed = false,
	): Promise<ShadowConfigurationResult> {
		const applied =
			confirmed || !utterance.toLowerCase().includes("réinitialise");
		if (utterance.toLowerCase().includes("conteur")) {
			this.profile.preferences.persona = "storyteller";
		}
		return Promise.resolve({
			intent: "change_persona",
			confidence: 0.9,
			explanation: "Mock configuration applied.",
			requiresConfirmation:
				utterance.toLowerCase().includes("réinitialise") && !confirmed,
			confirmationMessage: applied ? "Preference applied." : "Confirm reset?",
			applied,
			profile: structuredClone(this.profile),
		});
	}
}
