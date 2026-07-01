import { screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it } from "vitest";
import { LearningCenter } from "@/features/learning/LearningCenter";
import { LearningSettingsPage } from "@/pages/LearningSettings/LearningSettingsPage";
import type { LearningRecommendationsResponse } from "@/services/learning/types";
import { renderWithProviders } from "@/test/render";

const mockData = (): LearningRecommendationsResponse => ({
	scopeKey: "default",
	adaptiveRecommendationsEnabled: false,
	recommendations: [],
	adaptiveHints: {
		active: false,
		explanationStyle: null,
		challengeLevel: null,
		voiceLanguage: null,
		translationStyle: null,
		preferredProvider: null,
		appliedRecommendations: [],
	},
	profile: {
		id: "550e8400-e29b-41d4-a716-446655440010",
		scopeKey: "default",
		adaptiveRecommendationsEnabled: false,
		preferences: [],
		signals: [],
		insights: [],
		recommendations: [],
	},
});

const enabledData = (): LearningRecommendationsResponse => ({
	...mockData(),
	adaptiveRecommendationsEnabled: true,
	recommendations: [
		{
			id: "550e8400-e29b-41d4-a716-446655440013",
			type: "show_vocabulary_before_playback",
			explanation:
				"Repeated vocabulary questions suggest previewing key terms before playback.",
			sourceInsightIds: ["550e8400-e29b-41d4-a716-446655440012"],
			generatedAt: "2026-06-26T10:01:00+00:00",
		},
	],
	adaptiveHints: {
		active: true,
		explanationStyle: "short",
		challengeLevel: "easy",
		voiceLanguage: "fr",
		translationStyle: null,
		preferredProvider: null,
		appliedRecommendations: ["show_vocabulary_before_playback"],
	},
	profile: {
		...mockData().profile,
		adaptiveRecommendationsEnabled: true,
		insights: [
			{
				id: "550e8400-e29b-41d4-a716-446655440012",
				type: "vocabulary_gap",
				summary: "Observed 3 vocabulary-related signals.",
				sourceSignalIds: ["550e8400-e29b-41d4-a716-446655440011"],
				generatedAt: "2026-06-26T10:01:00+00:00",
			},
		],
		recommendations: [
			{
				id: "550e8400-e29b-41d4-a716-446655440013",
				type: "show_vocabulary_before_playback",
				explanation:
					"Repeated vocabulary questions suggest previewing key terms before playback.",
				sourceInsightIds: ["550e8400-e29b-41d4-a716-446655440012"],
				generatedAt: "2026-06-26T10:01:00+00:00",
			},
		],
	},
});

describe("LearningCenter", () => {
	it("renders disabled adaptive state", () => {
		renderWithProviders(
			<LearningCenter
				data={mockData()}
				onToggleAdaptive={async () => undefined}
				onReset={async () => undefined}
				isUpdating={false}
			/>,
		);

		expect(screen.getByText("Learning profile")).toBeInTheDocument();
		expect(screen.getByText("Adaptive recommendations")).toBeInTheDocument();
		expect(screen.getByRole("checkbox")).not.toBeChecked();
	});

	it("shows insight and recommendation reasons when enabled", () => {
		renderWithProviders(
			<LearningCenter
				data={enabledData()}
				onToggleAdaptive={async () => undefined}
				onReset={async () => undefined}
				isUpdating={false}
			/>,
		);

		expect(
			screen.getByText(/Generated from 1 source signal/),
		).toBeInTheDocument();
		expect(screen.getByText(/Based on 1 source insight/)).toBeInTheDocument();
		expect(
			screen.getByText(/previewing key terms before playback/),
		).toBeInTheDocument();
	});

	it("calls adaptive toggle handler", async () => {
		const user = userEvent.setup();
		let toggled = false;

		renderWithProviders(
			<LearningCenter
				data={mockData()}
				onToggleAdaptive={async () => {
					toggled = true;
				}}
				onReset={async () => undefined}
				isUpdating={false}
			/>,
		);

		await user.click(screen.getByRole("checkbox"));
		expect(toggled).toBe(true);
	});

	it("calls reset handler", async () => {
		const user = userEvent.setup();
		let reset = false;

		renderWithProviders(
			<LearningCenter
				data={mockData()}
				onToggleAdaptive={async () => undefined}
				onReset={async () => {
					reset = true;
				}}
				isUpdating={false}
			/>,
		);

		await user.click(
			screen.getByRole("button", { name: "Reset learning profile" }),
		);
		expect(reset).toBe(true);
	});

	it("renders French i18n keys", () => {
		renderWithProviders(
			<LearningCenter
				data={mockData()}
				onToggleAdaptive={async () => undefined}
				onReset={async () => undefined}
				isUpdating={false}
			/>,
			{ locale: "fr" },
		);

		expect(screen.getByText("Profil d'apprentissage")).toBeInTheDocument();
		expect(screen.getByText("Recommandations adaptatives")).toBeInTheDocument();
	});
});

describe("LearningSettingsPage", () => {
	it("renders learning center from mock service", async () => {
		renderWithProviders(<LearningSettingsPage />);

		await waitFor(() => {
			expect(screen.getByText("Learning Center")).toBeInTheDocument();
		});
	});
});
