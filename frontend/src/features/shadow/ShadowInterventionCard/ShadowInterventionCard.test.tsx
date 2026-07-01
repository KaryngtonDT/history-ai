import { screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import { ShadowInterventionCard } from "@/features/shadow/ShadowInterventionCard";
import { renderWithProviders } from "@/test/render";

vi.mock("@/features/shadow/shadowVoice", async () => {
	const actual = await vi.importActual<typeof import("@/features/shadow/shadowVoice")>(
		"@/features/shadow/shadowVoice",
	);

	return {
		...actual,
		speakShadowAnswer: vi.fn(() => ({ spoken: true, fallbackUsed: false })),
	};
});

import { speakShadowAnswer } from "@/features/shadow/shadowVoice";

const intervention = {
	id: "550e8400-e29b-41d4-a716-446655440030",
	type: "vocabulary_check",
	trigger: "unknown_vocabulary",
	reason: "Shadow noticed uncommon vocabulary.",
	videoTimestamp: 12,
	expectedUserAction: "Answer the challenge",
	allowAutoPause: true,
	challenge: {
		questionText: "What does compound interest mean?",
	},
	skipped: false,
	answered: false,
};

describe("ShadowInterventionCard", () => {
	it("renders intervention reason and challenge", () => {
		renderWithProviders(
			<ShadowInterventionCard
				intervention={intervention}
				answer=""
				reply={null}
				isBusy={false}
				onAnswerChange={() => undefined}
				onSubmitAnswer={() => undefined}
				onSkip={() => undefined}
			/>,
		);

		expect(screen.getByText("Shadow learning moment")).toBeInTheDocument();
		expect(
			screen.getByText("Shadow noticed uncommon vocabulary."),
		).toBeInTheDocument();
		expect(
			screen.getByText("What does compound interest mean?"),
		).toBeInTheDocument();
	});

	it("submits answer and skip actions", async () => {
		const onSubmit = vi.fn();
		const onSkip = vi.fn();
		const user = userEvent.setup();

		renderWithProviders(
			<ShadowInterventionCard
				intervention={intervention}
				answer="It grows on prior interest."
				reply={null}
				isBusy={false}
				onAnswerChange={() => undefined}
				onSubmitAnswer={onSubmit}
				onSkip={onSkip}
			/>,
		);

		await user.click(screen.getByRole("button", { name: "Send answer" }));
		await user.click(screen.getByRole("button", { name: "Skip" }));

		expect(onSubmit).toHaveBeenCalled();
		expect(onSkip).toHaveBeenCalled();
	});

	it("speaks the challenge in the selected language", () => {
		renderWithProviders(
			<ShadowInterventionCard
				intervention={intervention}
				answer=""
				reply={null}
				isBusy={false}
				speechLanguage="fr"
				onAnswerChange={() => undefined}
				onSubmitAnswer={() => undefined}
				onSkip={() => undefined}
			/>,
		);

		expect(speakShadowAnswer).toHaveBeenCalledWith(
			"What does compound interest mean?",
			"fr",
		);
	});
});
