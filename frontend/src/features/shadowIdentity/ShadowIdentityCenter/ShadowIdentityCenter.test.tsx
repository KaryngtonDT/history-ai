import { render, screen, waitFor } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { ShadowIdentityCenter } from "@/features/shadowIdentity/ShadowIdentityCenter";
import { I18nProvider } from "@/i18n";
import type { ShadowIdentityProfile } from "@/services/shadowIdentity/types";

vi.mock("@/features/shadow/VoiceStudio", () => ({
	VoiceStudio: () => <div>Voice Studio</div>,
}));

vi.mock("@/services/shadowIdentity/ShadowIdentityService", () => ({
	shadowIdentityService: {
		updatePreferences: vi.fn(),
		reset: vi.fn(),
		configure: vi.fn(),
	},
}));

const profile: ShadowIdentityProfile = {
	id: "id-1",
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
			knownSkills: [],
			unknownSkills: [],
			goals: [],
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
			id: "h1",
			label: "Initial profile",
			source: "system",
			recordedAt: "2026-01-01T00:00:00Z",
		},
	],
};

describe("ShadowIdentityCenter", () => {
	it("renders identity sections and teach shadow panel", async () => {
		render(
			<I18nProvider initialLocale="en">
				<ShadowIdentityCenter
					profile={profile}
					onProfileChange={vi.fn()}
					isUpdating={false}
				/>
			</I18nProvider>,
		);

		await waitFor(() => {
			expect(screen.getByText("Shadow DNA")).toBeInTheDocument();
		});
		expect(screen.getAllByText("Teach Shadow").length).toBeGreaterThan(0);
		expect(screen.getByText("Configuration history")).toBeInTheDocument();
	});
});
