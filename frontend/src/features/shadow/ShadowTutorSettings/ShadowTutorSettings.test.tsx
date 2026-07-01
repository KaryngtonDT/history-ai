import { screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { ShadowTutorSettings } from "@/features/shadow/ShadowTutorSettings";
import { renderWithProviders } from "@/test/render";

const enabledPolicy = {
	enabled: true,
	maxInterventionsPerMinute: 2,
	minSecondsBetweenInterventions: 45,
	challengeLevel: "easy" as const,
	explanationStyle: "short" as const,
	autoResume: false,
	allowAutoPause: true,
};

const disabledPolicy = { ...enabledPolicy, enabled: false };

describe("ShadowTutorSettings", () => {
	it("renders proactive settings", () => {
		renderWithProviders(
			<ShadowTutorSettings policy={enabledPolicy} onChange={() => undefined} />,
		);

		expect(screen.getByLabelText("Proactive mode")).toBeChecked();
		expect(screen.getByLabelText("Challenge level")).toBeInTheDocument();
	});

	it("hides challenge controls when proactive mode is off", () => {
		renderWithProviders(
			<ShadowTutorSettings
				policy={disabledPolicy}
				onChange={() => undefined}
			/>,
		);

		expect(screen.queryByLabelText("Challenge level")).not.toBeInTheDocument();
	});

	it("renders tutor settings in French", () => {
		renderWithProviders(
			<ShadowTutorSettings policy={enabledPolicy} onChange={() => undefined} />,
			{ locale: "fr" },
		);

		expect(screen.getByText("Tuteur proactif")).toBeInTheDocument();
		expect(screen.getByLabelText("Mode proactif")).toBeInTheDocument();
	});
});
